<?php
use Silex\WebTestCase;
use Silex\Provider\WebProfilerServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

class MainTest extends WebTestCase
{
    public function createApplication()
    {
        require __DIR__.'/../config/dev.php';
        $app = require __DIR__.'/../src/app.php';
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new WebProfilerServiceProvider(), array(
            'profiler.cache_dir' => __DIR__.'/../var/cache/profiler',
        ));
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

    public function testMailIsSentAndContentIsOk()
    {
        $client = $this->createClient();

        // Enable the profiler for the next request (it does nothing if the profiler is not available)

        $crawler = $client->request('POST', '/');

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an email was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Hello Email', $message->getSubject());
        $this->assertEquals('send@example.com', key($message->getFrom()));
        $this->assertEquals('recipient@example.com', key($message->getTo()));
        $this->assertEquals(
            'You should see me from the profiler!',
            $message->getBody()
        );
    }
}
