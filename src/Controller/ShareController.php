<?php

namespace App\Controller;

use App\Entity\Share;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ShareController extends AbstractController
{
    /**
     * @Route("/share/{uId}", name="openShare")
     */
    public function show(string $uId): Response
    {
        if(strlen($uId) != 32){
            return $this->redirectToRoute("dashboard");
        }
        $em = $this->getDoctrine()->getManager();
        $share = $em->getRepository(Share::class)->findOneBy(["uId" => $uId]);
        /** @var $share Share */
        if(empty($share)){
            return $this->redirectToRoute("dashboard");
        }

        $package = $share->getPackage();
        if(!$package->getIsShared()){
            if($package->getOwnerId() != $this->getUser()){
                return $this->redirectToRoute("dashboard");
            }
        }


        return $this->render('share/index.html.twig', [
            'controller_name' => 'ShareController',
            'package' => $package,

        ]);
    }

    /**
     * @Route("/share/{uId}/download", name="downloadShare")
     */
    public function downloadPackage(string $uId): Response
    {
        if(strlen($uId) != 32){
            return $this->redirectToRoute("dashboard");
        }
        $em = $this->getDoctrine()->getManager();
        $share = $em->getRepository(Share::class)->findOneBy(["uId" => $uId]);
        /** @var $share Share */
        if(empty($share)){
            return $this->redirectToRoute("dashboard");
        }
        $package = $share->getPackage();

        $package = $share->getPackage();
        if(!$package->getIsShared()){
            if($package->getOwnerId() != $this->getUser()){
                return $this->redirectToRoute("dashboard");
            }
        }

        $files = $package->getFiles();

        $zip = new \ZipArchive();
        $zipName = date("Y-M-d")."-paczka.zip";
        $zip->open($zipName, \ZipArchive::CREATE);
        foreach ($files as $file){
            $zip->addFile($file->getPath(), $file->getFilename());
        }
        $zip->close();

        $response = new Response(file_get_contents($zipName));

        $response->headers->set('Content-Type', 'application.zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', filesize($zipName));

        @unlink($zipName);

        return $response;
    }

}
