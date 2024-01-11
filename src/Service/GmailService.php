<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function authenticate()
    {
        $this->client->setAuthConfig('/../config/credentials.json');
        $this->client->setScopes(Gmail::MAIL_GOOGLE_COM);

        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents('/../config/credentials.json', json_encode($this->client->getAccessToken()));
        }
    }

    // Otros métodos para interactuar con la API de Gmail 
}
