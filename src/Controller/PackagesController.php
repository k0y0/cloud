<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Package;
use App\Entity\Share;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @Route("/packages/share/{pId}", name="packageShare")
     */
    public function switchShare($pId): Response
    {
        if(!ctype_digit($pId)){
            $this->addFlash('success', 'Coś poszło nie tak');
            return $this->redirectToRoute("shares");
        }

        $em = $this->getDoctrine()->getManager();
        $package = $em->getRepository(Package::class)->find($pId);
        /** @var $package Package */
        $user = $this->getUser();

        if($package->getOwnerId() === $user){
            $package->setIsShared(!$package->getIsShared());
            $em->persist($package);
            $em->flush();
        }else{
            $this->addFlash('success', 'Coś poszło nie tak');
        }
        return $this->redirectToRoute("shares");
    }


    /**
     * @Route("/packages/delete/{pId}", name="packageRemove")
     */
    public function deletePackage($pId): Response{
        if(!ctype_digit($pId)){
            return new Response("", "400");
        }
        $em = $this->getDoctrine()->getManager();
        $package = $em->getRepository(Package::class)->find($pId);
        /** @var $package Package */
        if($package->getOwnerId() !== $this->getUser()){
            return new Response("","401");
        }
        $em->remove($package);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/packages/upadte/{id}", name="packageUpdate")
     */
    public function packageUpdate(Request $request , $id): Response
    {

        if(!ctype_digit($id)){
            return new Response("","400");
        }
        $json = $request->getContent();
        $jsonData = json_decode($json);
        $em = $this->getDoctrine()->getManager();
        $package = $em->getRepository(Package::class)->find($id);
        /** @var $package Package */

        if($package->getOwnerId() !== $this->getUser()){
            return new Response("","401");
        }

        $files = $package->getFiles();
        $checked = array();
        foreach ($jsonData as $item){
            $itemId = $item->id;
            $status = false;

            foreach ($files as $f){
                if($f->getId() == $item->id){
                    $status = true;
                    break;
                }
            }

            if(!$status){
                $file = $em->getRepository(File::class)->findOneBy(["id" => $itemId]);
                $package->addFile($file);
                $checked[] = $file;
            }else{
                if(!empty($f)){
                    $checked[] = $f;
                }
            }
        }
        foreach ($files as $file){
            foreach ($checked as $check){
                if($file == $check){
                    continue 2;
                }
            }
            $package->removeFile($file);
        }

        $em->flush();
        $response = new Response();
        return $response->send();
    }



    /**
     * @Route("/packages/quickShare/{fId}", name="quickShare")
     */
    public function quickShare($fId): Response
    {

        if(empty($fId) && !ctype_digit($fId)){
            return new Response("", "400");
        }

        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->find($fId);
        /** @var $file File */
        if(empty($file)){
            return new Response("", "401");
        }
        if($file->getOwner() === $this->getUser()){
            $package = new Package();
            $package->setOwnerId($this->getUser());
            $package->setIsShared(1);
            $share = new Share();

            $uid = bin2hex(random_bytes(16));
            $t = $em->getRepository(Share::class)->findOneBy(["uId"=>$uid]);

            while(!empty($t)){
                $uid = bin2hex(random_bytes(16));
                $t = $em->getRepository(Share::class)->findOneBy(["uId"=>$uid]);
            }

            $share->setUId($uid);
            $package->setShareLink($share);

            $package->addFile($file);

            $em->persist($share);
            $em->persist($package);

            $em->flush();

            return $this->redirectToRoute("openShare", ["uId" => $share->getUId()]);
        }else{
            return new Response("", "401");
        }

    }

}
