<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use App\Form\FileUploadType;
use App\Form\NewFolderType;
use App\Form\PasswordChangeType;
use App\Helper\SizesHelper;
use App\Service\UsedSpaceChecker;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{

    /**
     * @Route("/", name="dashboard")
     */
    public function index(UsedSpaceChecker $usc): Response
    {
        $form = $this->createForm(FileUploadType::class, null , ['action' => $this->generateUrl("upload")]);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        $e = $this->getUser();



        $recentFiles = $em->getRepository(File::class)->findRecentFiles($user);
        $favourite = $em->getRepository(File::class)->findBy(["isFavourite" => true, "owner" => $user]);

        $userSpace = $usc->getUsedSpace($user);
        return $this->render('dashboard/index.html.twig', [
            'userSpaceInfo' => $userSpace,
            'form' => $form->createView(),
            'recentFiles' => $recentFiles,
            'site' => "dashboard",
            'favourite' => $favourite
        ]);
    }

    /**
     * @Route("/files", name="my_files")
     */
    public function files(UsedSpaceChecker $usc): Response
    {
        $formFolder = $this->createForm(NewFolderType::class, null, ['action' => $this->generateUrl("createFolder")]);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        /** @var $user User */



        //todo: change folder dynamically
        $formFolder->get("hasParent")->setData(false);
        $formFolder->get("parentId")->setData(null);

        //get user used space array.
        $userSpace = $usc->getUsedSpace($user);
        return $this->render('dashboard/myfiles.html.twig', [
            'userSpaceInfo' => $userSpace,
//            'files' =>  $onlyFilesInFolder,
//            'folders' => $dirFolders,
            'form' => $formFolder->createView(),
        ]);
    }

    /**
     * @param $id
     * @param UsedSpaceChecker $usc
     * @Route("/folder/{id}", name="folder")
     * *
     */
    public function folder(UsedSpaceChecker $usc, $id = false): Response
    {
        $formUpload = $this->createForm(FileUploadType::class, null, ['action' => $this->generateUrl("upload")]);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        if(empty($id)){
            $mainFolder = "mainFolder";
        }else{
            $mainFolder = $em->getRepository(Folder::class)->findOneBy(["id" => $id]);
        }

        /** @var $user User
         *  @var $mainFolder Folder
         */
        if(!empty($mainFolder)){
            $formFolder = $this->createForm(NewFolderType::class, null, ['action' => $this->generateUrl("createFolder")]);
            if($mainFolder === "mainFolder"){

                //todo: change folder dynamically
                $formFolder->get("hasParent")->setData(false);
                $formFolder->get("parentId")->setData(null);

                $files = $user->getFiles();
                $onlyFilesInFolder = array();
                if(!empty($files)){
                    foreach ($files as $file){
                        /** @var $file File */
                        if(is_null($file->getFolder())){
                            $onlyFilesInFolder[] = $file;
                        }
                    }
                }
                $folders = $user->getFolders();
                $dirFolders = array();

                foreach ($folders as $folder){
                    if(!$folder->getHasParent()){
                        $dirFolders[] = $folder;
                    }
                }
            }else{


                $formUpload->add('folderId', HiddenType::class, [
                    'data' => $mainFolder->getId(),
                ]);
                if($user != $mainFolder->getOwner()){
                    //todo: message?
                    return new RedirectResponse($this->generateUrl("folder"));
                }
                $formFolder->get("hasParent")->setData(true);
                $formFolder->get("parentId")->setData($mainFolder->getId());

                $onlyFilesInFolder = $mainFolder->getFiles();

                $folders = $user->getFolders();
                $dirFolders = array();


                foreach ($folders as $single){
                    if($single->getParent() == $mainFolder){
                        $dirFolders[] = $single;
                    }
                }
            }

            $userSpace = $usc->getUsedSpace($user);
            return $this->render('dashboard/folder.html.twig', [
                'current' => $mainFolder,
                'userSpaceInfo' => $userSpace,
                'files' =>  $onlyFilesInFolder,
                'folders' => $dirFolders,
                'site' => "folder",
                'form' => $formFolder->createView(),
                'formUpload' => $formUpload->createView()
            ]);

        }
        return new RedirectResponse($this->generateUrl("folder"));
    }


    /**
     * @Route("/shared", name="shares")
     */
    public function shared(UsedSpaceChecker $usc): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        /** @var $user User */
        $packages = $user->getPackages();
        //first - get user used space array.
        $userSpace = $usc->getUsedSpace($user);
        //loop through items, keep shared

        return $this->render('dashboard/shares.html.twig', [
            'userSpaceInfo' => $userSpace,
            'packages' =>  $packages,
            'site' => "shares",
        ]);
    }

    /**
     * @Route("/settings", name="settings")
     */
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $form = $this->createForm(PasswordChangeType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $first = $form->get("plainPassword")->get("first")->getData();
            $sec = $form->get("plainPassword")->get("second")->getData();

            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $user = $this->getDoctrine()->getManager()->getRepository(User::class)->find($user);
            if(!empty($user)){
                /** @var $user User */

                $user->setPassword($userPasswordHasherInterface->hashPassword($user, $first));
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Pomyślnie zmieniono hasło.');
            }
        }

        return $this->render('dashboard/settings.html.twig', [
            'form' => $form->createView(),
            'site' => "settings",
        ]);

    }
}
