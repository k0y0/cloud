<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FileUploadType;
use App\Helper\SizesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(): Response
    {
        $form = $this->createForm(FileUploadType::class, null , ['action' => $this->generateUrl("upload")]);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        /** @var User $user  */
        $files = $user->getFiles();
        $allFilesSize = 0;
        foreach ($files as $file){
            $allFilesSize += (int)$file->getFilesize();
        }
        $limit = $user->getDiskLimit();
        if($limit === "no_limit"){
            $spaceLeft = 0;
            $percent = 0;
        }else{
            if($allFilesSize < $limit){
                $spaceLeft = $limit - $allFilesSize;
                $percent = number_format(($allFilesSize / $limit) * 100, 0);
            }else{
                $percent = (string)100;
                $spaceLeft = "Przekroczono limit, możliwość przesyłania plików wyłączona.";
            }
        }

        $userSpace = array(
            "limit" => round($limit, 3),
            "files" => round($allFilesSize, 3),
            "left" => round($spaceLeft, 3),
            "percent" => $percent,
            "format" => "B",
        );

        return $this->render('dashboard/index.html.twig', [
            'userSpaceInfo' => $userSpace,
            'form' => $form->createView(),
        ]);
    }

}
