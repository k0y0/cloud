<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\User;
use App\Form\FileUploadType;
use App\Form\NewFolderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\throwException;

class FolderController extends AbstractController
{
    /**
     * @Route("create/new/folder/", name="createFolder")
     * @param Request $request
     * @return Response
     */
    public function createFolder(Request $request): Response
    {
        $form = $this->createForm(NewFolderType::class);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());

        if($user){
            if($form->isSubmitted()){
                $manager = $this->getDoctrine()->getManager();
                $folder = new Folder();
                $folder->setFolderName($form->get("folderName")->getData());
                if($form->get("hasParent")->getData()){
                    if(!empty($parent = $form->get("parentId")->getData())){
                        $folder->setHasParent(true);
                        $t = $em->getRepository(Folder::class)->findOneBy(["id" => $parent]);
                        $folder->setParent($t);
                    }else{
                        //todo ?
                    }
                }else{
                    $folder->setHasParent(false);
                    $folder->setParent(null);
                }
                $folder->setOwner($user);
                $manager->persist($folder);
                $manager->flush();

                $this->addFlash("message", "Pomyślnie utworzono folder");

                //todo: redirect do nowego folderu :thinking:
                return $this->redirectToRoute('folder', ["id" => $folder->getId()]);
            }
        }
        return new RedirectResponse($this->generateUrl('app_login'));
    }


    /**
     * @Route("delete/folder/{id}", name="deleteFolder")
     * @param int $id
     * @return Response
     */
    public function deleteFolder(int $id): Response
    {
        if(!empty($id)){
            $em = $this->getDoctrine()->getManager();
            $folder = $em->getRepository(Folder::class)->find($id);
            /** @var $folder Folder */
            if(!empty($folder)){
                if($folder->getOwner() === $this->getUser()){

                    $this->del($folder);
                    $em->flush();
                }
            }
        }

        return new RedirectResponse($this->generateUrl("folder"));
    }

    private function del(Folder $folder){
        $em = $this->getDoctrine()->getManager();
        $children = $em->getRepository(Folder::class)->findBy(["parent" => $folder->getId()]);

        $files = $folder->getFiles();

        if(!empty($children)){
            foreach ($children as $child){
                $this->del($child);
            }
        }
        if(!empty($files)){
            foreach ($files as $file){
                $em->remove($file);
            }
        }
        $em->remove($folder);
    }
}
