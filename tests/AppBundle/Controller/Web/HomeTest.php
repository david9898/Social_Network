<?php


namespace Tests\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler\PdoSessionHandlerTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class HomeTest extends WebTestCase
{
    private $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = static::createClient();
    }
//    public function testHomeView()
//    {
//        $client = static::createClient();
//
//        $client->request('GET', '/home');
//
//        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
//    }

    public function testWithAuth()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/home', [], [], [
            'PHP_AUTH_USER' => 'david_786@abv.bg',
            'PHP_AUTH_PW'   => '123'
        ]);

        $this->assertGreaterThan(0, $crawler->filter('div.article_info')->count());
    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken('david_786@abv.bg', '123', $firewallName);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}