<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $form->handleRequest($request);

        if($user = $this->getUser()){
            if($form->isSubmitted()){
                //get file
                $files = $form->get('filename')->getData();
                $manager = $this->getDoctrine()->getManager();
                foreach ($files as $file){
                    /** @var UploadedFile $file */
                    $en = new File();

                    $en->setFilename($file->getClientOriginalName());
                    $en->setFilesize($file->getSize());
                    $en->setMimeType($file->guessExtension());
                    $en->setOwner($user);
                    $en->setIsShared(false);
                    $en->setPath("newpath");
                    $en->setUploadetAt(new \DateTimeImmutable());

                    $manager->persist($en);
                    $manager->flush();
                }
                $this->addFlash("message", "Pomyślnie przesłano plik");
                return $this->redirectToRoute('dashboard');
            }
        }
        return new RedirectResponse($this->generateUrl('app_login'));
    }
}
