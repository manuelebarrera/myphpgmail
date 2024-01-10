<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GmailService;




class enviarController extends AbstractController
{
    /**
     * @Route("/email", name="send_email")
     */
    public function sendEmail(MailerInterface $mailer, GmailService $gmailService)
    {
         // Autenticarse con la API de Gmail
         $gmailService->authenticate();

         // Realizar operaciones con la API de Gmail, enviar correos, etc.



         $email = (new Email())
            ->from('pruebamanuelebarrera@gmail.com')
            ->to('manuelebarrera@gmail.com')
            ->subject('Prueba de correo')
            ->text('¡Hola! Este es un correo de prueba.');

        $mailer->send($email);

        //return $this->redirectToRoute('home');
        $response = new Response('email enviado');
        return $response; }
}
