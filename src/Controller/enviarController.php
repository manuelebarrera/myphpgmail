<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GmailService;
use Google\Service\Gmail;
//use Google\Service\Gmail\Message;
use Google_Client;
use Symfony\Component\HttpFoundation\Request;

use Google\Service\Gmail\Resource\UsersMessagesAttachments;  //probar


use Google\Client as GC;


use Google_Service;  //x



class enviarController extends AbstractController
{



    /**
     * @Route("/send", name="send", methods={"POST"})
     */
    public function sendEmail(Request $request)
    {
        $to = $request->request->get('to');
        if (!$to) {
            $to = 'manuelebarrera@gmail.com';
        }

        $file = $request->files->get('file');
        if (!$file || $file->getSize() === 0) {
            return $this->json([
                'error' => 'No se ha recibido ningún archivo.'
            ], 400);
        }

        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json([
                'error' => 'El tipo de archivo no es válido.'
            ], 400);
        }

        if ($file->getSize() > 10000000) { // 10MB
            return $this->json([
                'error' => 'El archivo es demasiado grande.'
            ], 400);
        }

        $fileData = file_get_contents($file->getPathname());
        $encoded_data = base64_encode($fileData);

        $token = (__DIR__ . '/token.json');
        $credentials = (__DIR__ . '/credentials.json');


        if (file_exists($token) && file_exists($credentials)) {
            $client = new Google_Client();
            $client->addScope([Gmail::MAIL_GOOGLE_COM]); //
            $client->setAuthConfig($credentials); //
            $client->setAccessType('offline'); //


            $access_token = json_decode(file_get_contents($token), true);

            $client->setAccessToken($access_token);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($token, json_encode($client->getAccessToken()));
            }

            $service = new Gmail($client);
            // Crear un mensaje
            $message = new Gmail\Message();
            
            $headers = "From: pruebamanuelebarrera@gmail.com\r\n";
            $headers .= "To: " . $to . "\r\n";
            $headers .= "Subject: Asunto del correo electrónico php\r\n";

            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"=A_GMAIL_BOUNDARY\"\r\n\r\n";

            $body = "--=A_GMAIL_BOUNDARY\r\n";
            $body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

            $body = 'Este es el cuerpo del correo electrónico ';

            $body .= "\r\n\r\n";

            $filePath = $file->getPathName();
            $fileName = $file->getClientOriginalName(); 
            $fileType = mime_content_type($filePath);


            // Agregar el archivo adjunto
$body .= "--=A_GMAIL_BOUNDARY\r\n";
$body .= "Content-Type: {$fileType}; name=\"{$fileName}\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n\r\n";
$body .= chunk_split(base64_encode($fileData)) . "\r\n";
$body .= "--=A_GMAIL_BOUNDARY--";

$rawMessage = base64_encode($headers . $body);

          
                
               /*  $headers .= "Content-Type: {$fileType}; name={$fileName}\r\n";
                $headers .= "Content-Transfer-Encoding: base64\r\n";
                $headers .= "Content-Disposition: attachment; filename={$fileName}\r\n";
 */
                $rm = $headers . "\r\n\r\n" . $body . "\r\n\r\n" . $fileName; 
               // $rawMessage = base64_encode($headers . "\r\n\r\n" . $body . "\r\n\r\n" . $fileData);     //$encoded_data

         
            $message->setRaw($rawMessage);
            
          
            $msg = $service->users_messages->send('me', $message);

            $response = [
                'Mensaje enviado' => 'Ok',
                'headers' => $headers,
                'mensaje' => $body,
                'to' => $to,
                'raw' => $message,
                'file' => $fileName,
            ];

            $json = json_encode($response);

            $response = new Response($json, 200);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $response = [
                'mensaje' => 'Error en Autentificacion',
            ];

            $json = json_encode($response);

            $response = new Response($json, 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }




    /**
     * @Route("/list", name="list")
     */
    public function list()
    {
        $token = (__DIR__ . '/token.json');
        $credentials = (__DIR__ . '/credentials.json');


        if (file_exists($token) && file_exists($credentials)) {
            $client = new Google_Client();
            $client->addScope([Gmail::MAIL_GOOGLE_COM]); //
            $client->setAuthConfig($credentials); //
            $client->setAccessType('offline'); //


            $access_token = json_decode(file_get_contents($token), true);

            $client->setAccessToken($access_token);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($token, json_encode($client->getAccessToken()));
            }




            $service = new Gmail($client);
            $result = $service->users_messages->listUsersMessages('me', ['maxResults' => 100, 'labelIds' => 'INBOX']); //SENT  TRASH  INBOX

            $correos = [];
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

                $correos[] = [
                    'id' => $messageId,
                    'asunto' => $subject,
                    'fecha' => $date,
                    'extracto' => $snippet
                ];
            }


            $response = new Response(json_encode($correos));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $response = [
                'mensaje' => 'Error en Autentificacion',
            ];

            $json = json_encode($response);

            $response = new Response($json, 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }



    /**
     * @Route("/autenticar", name="autenticar")
     */
    public function autenticar()
    {
        $token = (__DIR__ . '/token.json');
        $credentials = (__DIR__ . '/credentials.json');
        if (!file_exists($credentials)) {
            $response = [
                'mensaje' => 'Falta archivo de credenciales',
            ];

            $json = json_encode($response);

            $response = new Response($json, 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        $client = new Google_Client();
        //$client->setClientId($client_id);
        //$client->setClientSecret($client_secret);
        //$client->setRedirectUri($redirect_uri);
        //$redirect_uri = 'https://127.0.0.1:8001/autenticar';
        $client->addScope([Gmail::MAIL_GOOGLE_COM]); //
        $client->setAuthConfig($credentials); //
        $client->setAccessType('offline'); //
        //$client->setRedirectUri($redirect_uri);$this->client

        if (file_exists($token)) {
            $access_token = json_decode(file_get_contents($token), true);
        } else {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . $auth_url);

            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                $access_token = $client->fetchAccessTokenWithAuthCode($code);
                if (!file_exists(dirname($token))) {
                    mkdir(dirname($token), 0700, true);
                }
                file_put_contents($token, json_encode($access_token));
                $client->setAccessToken($access_token);
                if ($client->isAccessTokenExpired()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($token, json_encode($client->getAccessToken()));
                }
                $response = [
                    'mensaje' => 'Autenticado correctamente',
                    'A-T' => $access_token,
                ];

                $json = json_encode($response);

                $response = new Response($json, 200);
                $response->headers->set('Content-Type', 'application/json');
                // header('Location: ' . $redirect_uri);
                return $response;
            } else {
                $auth_url = $client->createAuthUrl();
                header('Location: ' . $auth_url);
                exit();

                $response = [
                    'mensaje' => 'Error en Autentificacion',
                ];

                $json = json_encode($response);

                $response = new Response($json, 400);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        $client->setAccessToken($access_token);
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($token, json_encode($client->getAccessToken()));
        }

        $response = [
            'mensaje' => 'Autenticado correctamente',
            'A-T' => $access_token,
        ];

        $json = json_encode($response);

        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


   




































    /**
     * @Route("/email4", name="send_email4")
     */
    public function sendEmail4()
    {
        $credentialspath = (__DIR__ . '/token.json');
        $credentials = (__DIR__ . '/credentials.json');
        // Configura las credenciales
        // $client_id = "653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com";
        // $client_secret = "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";
        // $redirect_uri = 'https://127.0.0.1:8001/email4';

        // Crea el cliente de Google
        $client = new Google_Client();


        //$client->setClientId($client_id);
        //$client->setClientSecret($client_secret);
        //$client->setRedirectUri($redirect_uri);
        $client->addScope([Gmail::MAIL_GOOGLE_COM]);
        $client->setAuthConfig($credentials);
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



                // Crear un mensaje
                $message = new Gmail\Message();

                // Definir los encabezados del correo electrónico
                $headers = "From: pruebamanuelebarrera@gmail.com\r\n";
                $headers .= "To: manuelebarrera@gmail.com\r\n";
                $headers .= "Subject: Asunto del correo electrónico php\r\n";

                // Definir el cuerpo del correo electrónico
                $body = 'Este es el cuerpo del correo electrónico ';

                // Combinar los encabezados y el cuerpo del correo electrónico
                $rawMessage = base64_encode($headers . "\r\n\r\n" . $body);     //base64_encode
                $message->setRaw($rawMessage);

                // Enviar el correo electrónico
                $service->users_messages->send('me', $message);






               




           











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
                $response = new Response('caso1 - Conectado a la api de gmail  ');
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



        $response = new Response('caso2 - Conectado a la api de gmail final ');
        return $response;
    }


      /**
     * @Route("/autenticar2", name="autenticar2")
     */
    public function autenticar2()
    {
      
        $token = (__DIR__ . '/token.json');
        
        $client_id='653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com';
        $client_secret='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';
       // $refresh_token = '1//04SkchBUkQhXsCgYIARAAGAQSNwF-L9IrFYifcvqc5Y_kakop8CD8ZDrYGXm6OyFSWxIR6sg8n7a4LcQm837jfXc2x2NH0xl4jLc';
        //$refresh_token = '1//04YjhCDMEVxyRCgYIARAAGAQSNwF-L9IrTqSRkyQ5sadcb2WzvvrlGXXVRZcCLG1RkflZnGgzlnsf9tiFQYMUOIVOsSS_Cq3fbGk';
        $refresh_token = '1//04nlDlPyXb8X4CgYIARAAGAQSNwF-L9IrqItBH6BrZXUc1Uwx5Td2DKBbsdcJy8casq2fPGdLje3HHYDEjeFA-f8V1kmpLGFfZs0';
        

        
        $redirect_uri = 'https://127.0.0.1:8001/autenticar';

        $client = new Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);
        $client->addScope([Gmail::MAIL_GOOGLE_COM]); //
       
        $client->setAccessType('offline'); //
      

        if (file_exists($token)) {
            print("accediendo al archivo");
            $access_token = json_decode(file_get_contents($token), true);
        } else {
            //print("####################   obtener el token");
            $access_token = $client->fetchAccessTokenWithRefreshToken($refresh_token);


            //$r = json_encode($access_token);
            if (isset($access_token['error'])) {
            //print("se ha producido un error" . $r);
            $response = [
                $access_token
            ];

            $json = json_encode($response);

            $response = new Response($json, 400);
            $response->headers->set('Content-Type', 'application/json');
           
            return $response;

            }
            
            //$client->setAccessToken($access_token);
            //if (!file_exists($token)) {
                print("-------------------creando archivo--------------------");
               // mkdir(dirname($token), 0700, true);
            //}
            print("escribiendo en archivo");
            file_put_contents($token, json_encode($access_token));
        }

        print("Token obtenido--> " . json_encode($access_token));
       // $client->setAccessToken($access_token);
                if ($client->isAccessTokenExpired()) {
                    print("-----token expirado-----");
                    $client->fetchAccessTokenWithRefreshToken($refresh_token);
                    $access_token = $client->getAccessToken();
                    file_put_contents($token, json_encode($access_token));
                }
           
               // $client->setAccessToken($access_token);
               
                $response = [
                    'mensaje' => 'Autenticado correctamente',
                    'A-T' => $access_token,
                ];

                $json = json_encode($response);

                $response = new Response($json, 200);
                $response->headers->set('Content-Type', 'application/json');
               
                return $response;
        }








      /**
     * @Route("/autenticar3", name="autenticar3")
     */
    public function autenticar3()
    {
        $client_id='653487830785-9tei5cco9g1oivhjs4c26tv2kjlv8m51.apps.googleusercontent.com';
        $client_secret='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';
       // $refresh_token = '1//04SkchBUkQhXsCgYIARAAGAQSNwF-L9IrFYifcvqc5Y_kakop8CD8ZDrYGXm6OyFSWxIR6sg8n7a4LcQm837jfXc2x2NH0xl4jLc';
       // $refresh_token = '1//04YjhCDMEVxyRCgYIARAAGAQSNwF-L9IrTqSRkyQ5sadcb2WzvvrlGXXVRZcCLG1RkflZnGgzlnsf9tiFQYMUOIVOsSS_Cq3fbGk';
        $refresh_token = '1//04nlDlPyXb8X4CgYIARAAGAQSNwF-L9IrqItBH6BrZXUc1Uwx5Td2DKBbsdcJy8casq2fPGdLje3HHYDEjeFA-f8V1kmpLGFfZs0';
        
        $redirect_uri = 'https://127.0.0.1:8001/autenticar';

        $client = new Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);
        $client->addScope([Gmail::MAIL_GOOGLE_COM, Gmail::GMAIL_READONLY]);      
        $client->setAccessType('offline'); 

        $access_token = $client->fetchAccessTokenWithRefreshToken($refresh_token);

            if (isset($access_token['error'])) {
            $response = [
                $access_token
            ];

            $json = json_encode($response);
            $response = new Response($json, 400);
            $response->headers->set('Content-Type', 'application/json');
           
            return $response;
            }
                $response = [
                    $access_token
                ];

                $json = json_encode($response);

                $response = new Response($json, 200);
                $response->headers->set('Content-Type', 'application/json');
               
                return $response;
        }









    /**
     * @Route("/rawbase64", name="rawbase64", methods={"POST"})
     */
    public function rawbase64(Request $request)
    {
        $dataReq = json_decode($request->getContent(), true);

        $to = isset($dataReq['to']) ? $dataReq['to'] : 'manuelebarrera@gmail.com';
        $subject = isset($dataReq['subject']) ? $dataReq['subject'] : 'Asunto predeterminado';
        $body = isset($dataReq['body']) ? $dataReq['body'] : 'Este es el cuerpo del correo electrónico ';
        $from = isset($dataReq['from']) ? $dataReq['from'] : 'pruebamanuelebarrera@gmail.com';
        $file = $dataReq['file'];

        $headers = "From: " . $from . "\r\n";
        $headers .= "To: " . $to . "\r\n";
        $headers .= "Subject: " . $subject . "\r\n";
        if ($file == 'true') {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"=A_GMAIL_BOUNDARY\"\r\n\r\n";


            $filePath = (__DIR__ . '/manual.pdf');
            $fileData = file_get_contents($filePath);
            //$encoded_data = base64_encode($fileData);
            $fileName = basename($filePath);
            $fileType = mime_content_type($filePath);

            $body .= "\r\n\r\n";



            $body .= "--=A_GMAIL_BOUNDARY\r\n";  //para separar cada adjunto
            $body .= "Content-Type: {$fileType}; name=\"{$fileName}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n\r\n";
            $body .= chunk_split(base64_encode($fileData)) . "\r\n";
            $body .= "--=A_GMAIL_BOUNDARY--";

            $rawMessage = strtr(base64_encode($headers . $body), '+/', '-_');     //base64_encode + strtr = base64url

        } else {

            $rawMessage = strtr(base64_encode($headers . "\r\n\r\n" . $body), '+/', '-_');  //base64url
        }

        $response = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'raw' => $rawMessage,
        ];

        $json = json_encode($response);

        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

      /**
     * @Route("/adjunto", name="adjunto", methods={"POST"})
     */
    public function adjunto(Request $request)
    {
        $dataReq = json_decode($request->getContent(), true);

        $data = isset($dataReq['data']) ? $dataReq['data'] : 'sindata';
        if ($data == 'sindata') {
            $response = [
                'error' => "sin data",
            ];
    
            $json = json_encode($response);
    
            $response = new Response($json, 200);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
            
        } else {

            $data = str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
            $fileContent = base64_decode($data);
            
            $response = new Response($fileContent);
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="file"');

          

        return $response;
    }
}
}
