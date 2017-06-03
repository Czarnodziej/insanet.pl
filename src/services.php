<?php
$app['cache.dir']    = $config['cache.dir'];
$app['lastFMTracks'] = function ($app) {

    if($app['debug']){
        return false;
    }

    $headers = array(
        'method'     => "GET",
        'header'     => "App-name: Insanet.pl favorite tracks\r\n".
                        "Content-Type: application/json\r\n",
                        "Accept: application/json\r\n",
        'user_agent' => 'insanet-silex.dev/#lastfm periodic curl favorite tracks v1',
    );

    $url = 'http://ws.audioscrobbler.com/2.0/?'.
           'method=user.gettoptracks'.
           '&user=pagodemc'.
           '&api_key='.$app['lastFMApiKey'].
           '&period=7day'.
           '&limit=5'.
           '&format=json';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    curl_close($curl);

    $jsonResultFile = $app['cache.dir'].'lastfm.json';

    if (!$result) {

        if(file_get_contents($jsonResultFile)) {
           return file_get_contents($jsonResultFile);
        }
        return false;
    }

    $jsonResult = json_decode($result);

    file_put_contents($jsonResultFile, $result);
    return $jsonResult;
};

$app['pageModTime'] = function () {
    $incls     = get_included_files();
    $included  = array_filter($incls, "is_file");
    $mod_times = array_map('filemtime', $included);
    $mod_time  = max($mod_times);

    return $mod_time;
};

$app['mail'] = $app->protect(
    function ($request, $maildata, $app) {
        $message = \Swift_Message::newInstance()
                                 ->setSubject($maildata['subject'])
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
$test = $app['swiftmailerSmtp'];
        $app['swiftmailer.options'] = $app['swiftmailerSmtp'];

        return $app['mailer']->send($message);
    }
);
