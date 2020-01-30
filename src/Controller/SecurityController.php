<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_inscription")
     */
    public function inscription(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User;
        
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);



        if($form->isSubmitted() && $form->isValid()){
            dump($user);

            $user->setDate(new \DateTime());
            $user->setRole('user');

            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('security_connexion');
        }

        return $this->render('security/inscription.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_connexion")
     */
    public function connexion(){

        return $this->render('security/connexion.html.twig');
     }

    /**
     * @Route("/deconnexion", name="security_deconnexion")
     */
     public function deconnexion(){

     }
}
