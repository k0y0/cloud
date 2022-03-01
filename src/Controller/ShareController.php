<?php

namespace App\Controller;

use App\Entity\Share;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

        return $this->render('share/index.html.twig', [
            'controller_name' => 'ShareController',
            'package' => $package,

        ]);
    }

}
