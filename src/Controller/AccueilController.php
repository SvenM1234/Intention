<?php

namespace App\Controller;

use App\Entity\Annonce;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil0")
     * @Route("/accueil", name="accueil")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Annonce::class);
        $annonces = $repo->findAll();

        return $this->render('accueil/accueil.html.twig',[
            'annonces' => $annonces
        ]);
    }

}
