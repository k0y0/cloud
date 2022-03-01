<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/settings", name="admin_settings")
     */
    public function env(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository(Setting::class)->findAll();


        return $this->render('admin/settings.twig', [
            'controller_name' => 'AdminController',
            'settings' => $settings
        ]);
    }

    /**
     * @Route("/admin/stats", name="admin_stats")
     */
    public function stats(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/stats.twig', [
            'site' => 'review',
            'title' => 'Admin - Przegląd',
            'users' => $users,

        ]);
    }
}
