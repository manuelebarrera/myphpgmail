<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GmailService;
use Google\Service\Gmail; //
use Google_Client;


//use Google\Auth\Credentials\
















// Importa las clases necesarias

use Google_Service;  //x

//include_once 'ext/standard/header.php';


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




  /**
     * @Route("/email2", name="send_email2")
     */
    public function sendEmail2()
    {
        $client = new Google_Client();
$client->setApplicationName('myphpgmail');
$client->setClientId('653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com'); // Reemplaza 'your-client-id' con tu ID de cliente
$client->setClientSecret('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'); // Reemplaza 'your-client-secret' con tu secreto de cliente
                            
$client->setRedirectUri('https://127.0.0.1:8001/index.php'); // Reemplaza 'your-redirect-uri' con tu URI de redirección
$client->addScope('email');
$client->addScope('profile');
//$client->setAccessType('offline');
//$client->setPrompt('select_account consent');



// Solicitar un token de acceso
if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
        //define('STDIN', fopen('php://stdin', 'r'));
        // Solicitar autorización del usuario
        $authUrl = $client->createAuthUrl();
        printf("Abre el siguiente enlace en tu navegador:\n%s\n", $authUrl);
        print 'Ingresa el código de verificación: ';
        //$authCode = trim(fgets(STDIN));
        $authCode = trim(fgets(fopen('php://stdin', 'r')));

        header('Location: ' . $authUrl);
        exit; 


        if (isset($_GET['code'])) {
            // Captura el código de autenticación desde la URL
            $authCode = $_GET['code'];
        
            // Intercambia el código de autenticación por un token de acceso
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        
            // Establece el token de acceso en el cliente
            $client->setAccessToken($accessToken);
        
            // Ahora puedes realizar acciones con el cliente autenticado
        
            // Por ejemplo, obtén información del usuario autenticado
            $userInfo = $client->verifyIdToken();
            print_r($userInfo);
        } else {
            // Si no se recibió el código, muestra un mensaje de error o realiza alguna acción adecuada
            echo "Error: No se recibió el código de autenticación.";
        }






        // Intercambiar código de autorización por un token de acceso
        //$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        //$client->setAccessToken($accessToken);

        // Verificar si hubo errores
        if (array_key_exists('error', $accessToken)) {
            $response = new Response('error '. $accessToken);
        return $response;

        //    throw new Exception(join(', ', $accessToken));
        }
    }
    // Guardar el token en un archivo para futuras ejecuciones
    //file_put_contents($tokenPath, json_encode($client->getAccessToken()));


    $service = new GmailService($client);
















        $response = new Response('email enviado');
        return $response; }


    }





    /**
     * @Route("/email3", name="send_email3")
     */
    public function sendEmail3()
    {
        


// Configura las credenciales
$client_id = "653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com";
$client_secret = "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";
$redirect_uri = 'https://127.0.0.1:8001';

//$redirect_uri = 'http://localhost:8081/oauth2callback';


// Crea el cliente de Google
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
//$client->addScope(Google_Service::SCOPES_GMAIL_READONLY);
//$client->addScope(['https://www.googleapis.com/auth/gmail.readonly']);
$client->addScope('email');


// Genera la URL de autenticación
$auth_url = $client->createAuthUrl();


// Redirige al usuario a la URL de autenticación
//header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
header('Location: ' . $auth_url);

exit();
//redirect($auth_url);


// Captura la URL de devolución
//if (isset($_GET['code'])) {
if (isset($_GET['code']) && $_SERVER['REQUEST_URI'] == $redirect_uri) {
    print("######################codigo ok");
    $redirect_uri2 = $_GET['code'];
    print("######################codigo ok");
    // Intercambia el código de autorización por un token de acceso
    $access_token = $client->fetchAccessTokenWithAuthCode($redirect_uri2);
    print("######################token ok");
    // Usa el token de acceso para realizar una solicitud a la API
    $service = new Google_Service($client);
    $response = new Response('email ok  '. $auth_url);
        return $response; 
   
}else{
    $response = new Response('email NOOO ok  ' .  $auth_url );
    return $response; 

}
      
       
    }















    /**
     * @Route("/email4", name="send_email4")
     */
    public function sendEmail4()
    {
        $credentialspath = (__DIR__ . '/token.json');
        // Configura las credenciales
        $client_id = "653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com";
        $client_secret = "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";
        $redirect_uri = 'https://127.0.0.1:8001/email4';

        // Crea el cliente de Google
        $client = new Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);
        $client->addScope([Gmail::MAIL_GOOGLE_COM]);

        $client->setAccessType('offline');




        if (file_exists($credentialspath)) {
            $access_token = json_decode(file_get_contents($credentialspath), true);
        } else {



            // Genera la URL de autenticación
            $auth_url = $client->createAuthUrl();

            // Redirige al usuario a la URL de autenticación
            header('Location: ' . $auth_url);

            // Captura la URL de devolución
            if (isset($_GET['code'])) {
                $code = $_GET['code'];

                // Intercambia el código de autorización por un token de acceso
                $access_token = $client->fetchAccessTokenWithAuthCode($code);
                //$xxx=$client->getAccessToken();

                //guardar en disco el token
                //$credentialspath = (__DIR__.'/token.json');


                if (!file_exists(dirname($credentialspath))) {
                    mkdir(dirname($credentialspath), 0700, true);
                }
                file_put_contents($credentialspath, json_encode($access_token));



                $client->setAccessToken($access_token);
                if ($client->isAccessTokenExpired()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($credentialspath, json_encode($client->getAccessToken()));
                }

                // Usa el token de acceso para realizar una solicitud a la API
                $service = new Gmail($client);


                // Enviar correo electrónico de prueba
                $to = 'destinatario@example.com';
                $subject = 'Asunto del correo electrónico';
                $body = 'Este es el cuerpo del correo electrónico';

                //$service->users->messages->send($to, $subject, $body);

                // $service->users_messages->send($to, $subject, $body);

                $result = $service->users_messages->listUsersMessages('me', ['maxResults' => 100]);


                print("##########   CONECTADO API GMAIL   ##########" . "<br><br>");



                foreach ($result->getMessages() as $msg) {
                    $messageId = $msg->getId();
                    $message = $service->users_messages->get('me', $messageId);
                    $headers = $message->getPayload()->getHeaders();
                    $subject = "";
                    $date = "";
                    $snippet = $message->getSnippet();

                    foreach ($headers as $header) {
                        if ($header->getName() == 'Subject') {
                            $subject = $header->getValue();
                        }
                        if ($header->getName() == 'Date') {
                            $date = $header->getValue();
                        }
                    }

                    print("ID del mensaje: " . $messageId . "<br>");
                    print("Asunto: " . $subject . "<br>");
                    print("Fecha: " . $date . "<br>");
                    print("Extracto: " . $snippet . "<br><br>");
                }


                // usuario se ha conectado con exito la primera vez






                // Mensaje de éxito
                $response = new Response('Conectado a la api de gmail  ');
                return $response;
            } else {
                // Mensaje de error


                $auth_url = $client->createAuthUrl();

                // Redirige al usuario a la URL de autenticación
                header('Location: ' . $auth_url);
                $response = new Response('ERROR en api de gmail  ');
                exit();
                return $response;
            }
        }
        $client->setAccessToken($access_token);
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialspath, json_encode($client->getAccessToken()));
        }
        $service = new Gmail($client);
        $result = $service->users_messages->listUsersMessages('me', ['maxResults' => 100]);
        foreach ($result->getMessages() as $msg) {
            $messageId = $msg->getId();
            $message = $service->users_messages->get('me', $messageId);
            $headers = $message->getPayload()->getHeaders();
            $subject = "";
            $date = "";
            $snippet = $message->getSnippet();

            foreach ($headers as $header) {
                if ($header->getName() == 'Subject') {
                    $subject = $header->getValue();
                }
                if ($header->getName() == 'Date') {
                    $date = $header->getValue();
                }
            }

            print("ID del mensaje: " . $messageId . "<br>");
            print("Asunto: " . $subject . "<br>");
            print("Fecha: " . $date . "<br>");
            print("Extracto: " . $snippet . "<br><br>");
        }





        $response = new Response('Conectado a la api de gmail final ');
            return $response;
    }
}
