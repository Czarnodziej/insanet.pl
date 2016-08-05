<?php
namespace InsanetSilex\Tests;

use Silex\WebTestCase;

class MainTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../src/app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);
        $app['session.test'] = true;
        return $app;
    }

    public function testMainPage()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('form'));
        $this->assertCount(1, $crawler->filter('#about'));
    }
}
