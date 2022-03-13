<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\Setting;
use App\Form\FileUploadType;
use App\Helper\SizesHelper;
use App\Service\UsedSpaceChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController
{
    private const FILES_DEFAULT_DIR = __DIR__."\\..\\..\\files\\";

    /**
     * @Route("/action/upload", name="upload")
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request): Response
    {
        $form = $this->createForm(FileUploadType::class);
        $form->add('folderId', HiddenType::class);

        $form->handleRequest($request);

        if($user = $this->getUser()){
            if($form->isSubmitted()){
                //get file
                $files = $form->get('filename')->getData();
                $manager = $this->getDoctrine()->getManager();
                $folderId = $form->get('folderId')->getData();
                $setting = $manager->getRepository(Setting::class)->findOneBy(["settingName" => "custom_files_dir"]);

                $diskFolderName = $user->getId();
                if(!empty($setting)){
                    /** @var $setting Setting */
                    $dir = $setting->getSettingValue();
                    if($dir == null){
                        $dir = self::FILES_DEFAULT_DIR;
                    }
                }else{
                    $dir = self::FILES_DEFAULT_DIR;
                }

                if(!is_dir($dir.$diskFolderName)){
                    mkdir($dir.$diskFolderName);
                }
                $newPath = $dir.$diskFolderName;
                $fileCounter = 0;
                $usc = new UsedSpaceChecker();
                $spaceLeft = $usc->getUsedSpace($user)['leftRaw'];
                foreach ($files as $file){
                    if($spaceLeft < $file->getSize()){
                        $this->addFlash("message", "Brak dostępnego miejsca.");
                        break;
                    }
                    $spaceLeft = $spaceLeft - $file->getSize();
                    /** @var UploadedFile $file */
                    $en = new File();

                    $en->setFilename($file->getClientOriginalName());
                    $en->setFilesize($file->getSize());

                    if($file->guessExtension() === null){
                        $this->addFlash("message", "Plik który próbujesz przesłać posiada złe rozszerzenie!");
                        break;
                    }
                    $en->setMimeType($file->guessExtension());
                    $en->setOwner($user);
                    $en->setIsShared(false);

                    $filename = bin2hex(random_bytes(32));


                    while(file_exists($newPath."\\".$filename.".".$file->getClientOriginalExtension())){
                        $filename = bin2hex(random_bytes(32));
                    }
                    $file->move($newPath , $filename.".".$file->getClientOriginalExtension());

                    $realpath = realpath($newPath."\\".$filename.".".$file->getClientOriginalExtension());

                    $en->setPath($realpath);
                    $en->setIsFavourite(false);


                    $en->setUploadetAt(new \DateTimeImmutable());
                    if(!empty($folderId)){
                        $folder = $manager->getRepository(Folder::class)->find($folderId);
                        /** @var $folder Folder */
                        if(!empty($folder)){
                            if($folder->getOwner() == $user){
                                $en->setFolder($folder);
                            }
                        }
                    }
                    $manager->persist($en);
                    $fileCounter++;
                }
                $manager->flush();
                if($fileCounter != 0 ){
                    if($fileCounter === 1){
                        $this->addFlash("message", "Pomyślnie przesłano plik");
                    }else if($fileCounter <= 4){
                        $this->addFlash("message", "Pomyślnie przesłano $fileCounter pliki");
                    }else{
                        $this->addFlash("message", "Pomyślnie przesłano $fileCounter plików");
                    }
                }
                return $this->redirectToRoute('dashboard');
            }
        }
        return new RedirectResponse($this->generateUrl('app_login'));
    }

    /**
     * @Route("/action/rename", name="renameFile")
     * @return Response
     */
    public function fileRename(): Response
    {
        if(!ctype_digit($_POST['fileId'])){
            return new RedirectResponse($this->generateUrl('dashboard'));
        }
        $id = $_POST['fileId'];
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);
        /** @var $file File */
        if(!empty($file)){
            if($file->getOwner() === $this->getUser()){
                $file->setFilename($_POST['newName']);

                $em->flush();
            }
        }
        return new RedirectResponse($this->generateUrl('dashboard'));
    }

    /**
     * @Route("/action/remove", name="removeFile")
     * @return Response
     */
    public function removeFile(): Response
    {
        if(!ctype_digit($_POST['fileId'])){
            return new RedirectResponse($this->generateUrl('dashboard'));
        }
        $id = $_POST['fileId'];
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);
        /** @var $file File */
        if(!empty($file)){
            if($file->getOwner() === $this->getUser()){
                unlink($file->getPath());
                $em->remove($file);
                $em->flush();
            }
        }
        return new RedirectResponse($this->generateUrl('dashboard'));
    }

    /**
     * @Route("/action/favourite/{id}", name="addToFavourite")
     * @param int $id
     * @return Response
     */
    public function addToFavourite(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);
        /** @var $file File */
        if(!empty($file)){
            if($file->getOwner() === $this->getUser()){
                $file->setIsFavourite(true);
                $em->flush();
            }
        }
        return new RedirectResponse($this->generateUrl('dashboard'));
    }

    /**
     * @Route("/action/removeFromFavourite/{id}", name="removeFromFavourite")
     * @param int $id
     * @return Response
     */
    public function removeFromFavourite(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);
        /** @var $file File */
        if(!empty($file)){
            if($file->getOwner() === $this->getUser()){
                $file->setIsFavourite(false);
                $em->flush();
            }
        }
        return new RedirectResponse($this->generateUrl('dashboard'));
    }

    /**
     * @Route("/action/downloadFile/{id}", name="downloadFile")
     * @param int $id
     * @return Response
     */
    public function downloadFile(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($id);
        /** @var $file File */
        if(!empty($file)){
            if($file->getOwner() === $this->getUser()){
                $response = new BinaryFileResponse($file->getPath());
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $file->getFilename()
                );
                return $response;
            }

            //todo: if file is shared for single person :P
        }

        return new RedirectResponse($this->generateUrl('dashboard'));
    }
}
