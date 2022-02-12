<?php

namespace App\Controller;

use App\Entity\Package;
use App\Entity\Share;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PackagesController extends AbstractController
{
    /**
     * @Route("/packages/create", name="packagesCreate")
     */
    public function create(): Response
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $package = new Package();

        $package->setIsShared(false);
        $package->setOwnerId($user);

        $share = new Share();

        $uid = bin2hex(random_bytes(16));
        $t = $em->getRepository(Share::class)->findOneBy(["uId"=>$uid]);

        while(!empty($t)){
            $uid = bin2hex(random_bytes(16));
            $t = $em->getRepository(Share::class)->findOneBy(["uId"=>$uid]);
        }

        $share->setUId($uid);
        $share->setPackage($package);

        $em->persist($share);
        $em->persist($package);
        $em->flush();

        return $this->redirectToRoute("shares");
    }

}
