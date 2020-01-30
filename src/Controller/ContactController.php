<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, \Swift_Mailer $mailer )
    {
      
        $contact = new Contact;

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = (new \Swift_Message('Hello Email'))
            ->setFrom($contact->getEmail())
            ->setTo('svenmarcos@outlook.com')
            ->setBody($contact->getMessage(),'text/html')
            ;

            if ($mailer->send($message)){
               
                return $this->redirectToRoute('sucessContact');
            }else{
                return $this->redirectToRoute('failedContact');
            }

    
        }
         


        return $this->render('contact/contact.html.twig',[
            'form' => $form->createView()]);
    }

    /**
     * @Route("/sucessContact", name="sucessContact")
     */
    public function sucessContact(){

        return $this->render('contact/contatc-sucess.html.twig');

    }

    /**
     * @Route("/failedContact", name="failedContact")
     */
    public function failedContact(){

        return $this->render('contact/contatc-sucess.html.twig');

    }


}
