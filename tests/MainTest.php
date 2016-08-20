<?php
use Silex\WebTestCase;

class MainTest extends WebTestCase
{
    public function createApplication()
    {
        require __DIR__.'/../config/dev.php';
        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../src/controllers.php';
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

        $this->assertCount(1, $crawler->filter('#form'));
        $this->assertCount(1, $crawler->filter('#about'));
        $this->assertCount(1, $crawler->filter('#skills'));
        $this->assertCount(1, $crawler->filter('#portfolio'));
        $this->assertCount(2, $crawler->filter('#lastfm .lastfm-top-tracks p'));
        //$this->assertCount(5, $crawler->filter('#lastfm li'));
    }
}
