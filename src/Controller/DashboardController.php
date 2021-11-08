<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FileUploadType;
use App\Helper\SizesHelper;
use App\Service\UsedSpaceChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

        $userSpace = $usc->getUsedSpace($user);
        return $this->render('dashboard/index.html.twig', [
            'userSpaceInfo' => $userSpace,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/files", name="my_files")
     */
    public function files(UsedSpaceChecker $usc): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        /** @var $user User */
        $files = $user->getFiles();

        //get user used space array.
        $userSpace = $usc->getUsedSpace($user);
        return $this->render('dashboard/myfiles.html.twig', [
            'userSpaceInfo' => $userSpace,
            'files' =>  $files,
        ]);
    }

    /**
     * @Route("/shared", name="shares")
     */
    public function shared(UsedSpaceChecker $usc): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        /** @var $user User */
        $files = $user->getFiles();
        //first - get user used space array.
        $userSpace = $usc->getUsedSpace($user);
        //loop through items, keep shared
        foreach ($files as $file)
        {
            if(!$file->getIsShared()){
                $files->remove($files->key());
            }
        }
        return $this->render('dashboard/shares.html.twig', [
            'userSpaceInfo' => $userSpace,
            'files' =>  $files,
        ]);
    }

}
