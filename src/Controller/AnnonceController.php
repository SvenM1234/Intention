<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Annonce;
use App\Entity\Candidat;
use App\Form\AnnonceType;
use App\Form\PostulerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnnonceController extends AbstractController
{
    /**
     * @Route("/annonce", name="annonce")
     */
    public function annonce()
    {
        $repo = $this->getDoctrine()->getRepository(Annonce::class);
        $annonces = $repo->findAll();

        return $this->render('annonce/annonce.html.twig', [
            'annonces' => $annonces
        ]);
    }

    /**
     * @Route("/mes-annonces", name="annonce_mes_annonces")
     */

    public function mesAnnonce()
    {
        $repo = $this->getDoctrine()->getRepository(Annonce::class);
        $annonces = $repo->findBy(
            ['fkUser' => $this->getUser()]
        );

        $repo = $this->getDoctrine()->getRepository(Candidat::class);

        $candidatures = [];
        
       
        foreach ($annonces as $key => $value) {
            
            $candidatures[] = $repo->findBy(
                ['fkAnnonce' => $value->getId()]
            );   
        }
        dump( $candidatures);
        

        return $this->render('annonce/mes_annonce.html.twig', [
            'annonces' => $annonces,
            'candidatures' => $candidatures
        ]);
    }

    /**
     * @Route("/postuler/{id}", name="postuler")
     */
    public function postulerAnnonce($id, Request $request, EntityManagerInterface $entityManager, \Swift_Mailer $mailer){

        $repo = $this->getDoctrine()->getRepository(Annonce::class);
        $annonces = $repo->findBy(
            ['id' => $id]
        );

        dump($annonces[0]->getFkUser());
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->findBy(
            ['id' => $annonces[0]->getFkUser()]
        );

        $candidat = new Candidat;

        $form = $this->createForm(PostulerType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $candidat->setFkAnnonce($annonces[0]);

            $cv = $form['cv']->getData();
  
            if ($cv) {
                $originalFilename = pathinfo($cv->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$cv->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $cv->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'cvname' property to store the PDF file name
                // instead of its contents
                $candidat->setCv($newFilename);
            }

            $entityManager->persist($candidat);
            $entityManager->flush();

            $message = (new \Swift_Message('Hello Email'))
            ->setFrom($candidat->getMail())
            ->setTo($user[0]->getEmail())
            ->setBody($candidat->getMessage(),'text/html')->attach(\Swift_Attachment::fromPath('uploads/cv-candidature/'.$candidat->getCv()), "application/pdf");
            ;
            // $message->attach(Swift_Attachment::fromPath('%kernel.project_dir%/public/uploads/cv-candidature/'.$candidat->getCv()));


            $mailer->send($message);

            return $this->render('annonce/candidature-sucess.html.twig');
        }
       
        

        return $this->render('annonce/postuler.html.twig',[
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/nouvelle-annonce", name="creer_annonce")
     */
    public function createAnnonce( Request $request, EntityManagerInterface $entityManager){
        $user = $this->getUser();
        $id = $user->getId();
   
        $annonce = new Annonce;

        $form = $this->createForm(AnnonceType::class, $annonce);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $annonce->setDateCreation(new \DateTime());
            $annonce->setFkUser($user);

            $photo = $form['photo']->getData();
  
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'photoname' property to store the PDF file name
                // instead of its contents
                $annonce->setPhoto($newFilename);
            }

            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('creer_annonce');
        }


        return $this->render('annonce/create_annonce.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/modifier-annonce/{id}", name="modifier_annonce")
     */
    public function modifyAnnonce($id, Request $request, EntityManagerInterface $entityManager){

        $repo = $this->getDoctrine()->getRepository(Annonce::class);
        $annonce = $repo->find($id);
   

        $form = $this->createFormBuilder($annonce)
            ->add('description')
            ->add('titre')
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $annonce->setDateCreation(new \DateTime());

            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('creer_annonce');
        }

        return $this->render('annonce/modify_annonce.html.twig',[
            'form' => $form->createView()
        ]);


    }


    /**
     * @Route("/delete-annonce/{id}", name="supprimer_annonce")
     */
    public function deleteAnnonce($id, EntityManagerInterface $entityManager){

        $repository = $this->getDoctrine()->getRepository(Annonce::class);

        $annonce = $repository->find($id);

        dump($annonce);
        
        $entityManager->remove($annonce);
        $entityManager->flush();

        return $this->redirectToRoute('annonce');
    }

    /**
     * @Route("/delete-annonce-accueil/{id}", name="accueil_supprimer_annonce")
     */
    public function deleteAnnonceAccueil($id, EntityManagerInterface $entityManager){

        $repository = $this->getDoctrine()->getRepository(Annonce::class);

        $annonce = $repository->find($id);

        dump($annonce);
        
        $entityManager->remove($annonce);
        $entityManager->flush();

        return $this->redirectToRoute('accueil');
    }
}
