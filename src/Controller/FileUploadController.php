<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Folder;
use App\Form\FileUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController
{
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

                foreach ($files as $file){
                    /** @var UploadedFile $file */
                    $en = new File();

                    $en->setFilename($file->getClientOriginalName());
                    $en->setFilesize($file->getSize());
                    $en->setMimeType($file->guessExtension());
                    $en->setOwner($user);
                    $en->setIsShared(false);
                    $en->setPath("newpath");
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
                    $manager->flush();
                }
                $this->addFlash("message", "Pomyślnie przesłano plik");
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
    public function addToFavourite($id): Response
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

}
