<?php
$app['cache.dir'] = $config['cache.dir'];
$app['lastFMTracks'] = function ($app) {

        $opts = array(
            'http' => array(
                'method'     => "GET",
                'header'     => "App-name: Insanet.pl favorite tracks\r\n".
                                "Content-Type: application/json\r\n",
                                "Accept: application/json\r\n",
                'user_agent' => 'insanet-silex.dev/#lastfm periodic curl favorite tracks v1',
            ),
        );

        $context = stream_context_create($opts);

        $url     = 'http://ws.audioscrobbler.com/2.0/?'.
                   'method=user.gettoptracks'.
                   '&user=pagodemc'.
                   '&api_key='.$app['lastFMApiKey'].
                   '&period=7day'.
                   '&limit=5'.
                   '&format=json';
        $request = file_get_contents($url, false, $context);

    return json_decode($request);
};

$app['pageModTime'] = function () {
    $incls     = get_included_files();
    $included  = array_filter($incls, "is_file");
    $mod_times = array_map('filemtime', $included);
    $mod_time  = max($mod_times);

    return $mod_time;
};

$app['mail'] = $app->protect(function($request, $maildata, $app) {
    $message = \Swift_Message::newInstance()
                             ->setSubject($maildata['subject'])
                             ->setFrom('kontakt@insanet.pl')
                             ->setTo('pagodemc@gmail.com')
                             ->setBody(
                                 $app['twig']->render(
                                     'mail/contact.html.twig',
                                     array(
                                         'ip'      => $request->getClientIp(),
                                         'name'    => $maildata['name'],
                                         'email'   => $maildata['email'],
                                         'message' => $maildata['message'],
                                     )
                                 )
                             );


    return $app['mailer']->send($message);
});
