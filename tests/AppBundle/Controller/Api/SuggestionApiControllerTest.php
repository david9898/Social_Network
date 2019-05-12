<?php

namespace Tests\AppBundle\Controller\Api;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SuggestionApiControllerTest extends WebTestCase
{

    public function testAddSuggestion()
    {
        $client = static::createClient();

        $session = new Session(new MockFileSessionStorage());

        $session->set('csrf_token', '9999');
        $session->set('friends', [1, 2, 3, 5, 50,588]);
        $session->set('currentId', 1);

        $client->request(
            'POST',
            'http://192.168.0.103:8000/addSuggestion',
            [],
            [],
            ['PHP_AUTH_USER' => 'david_786@abv.bg', 'PHP_AUTH_PW'   => '123',],
            '{"csrf_token": "9999", "target_user": "582"}'
        );

        print_r($client->getResponse()->headers->all());
        $this->assertContains('status', $client->getResponse()->getContent());
    }
}
