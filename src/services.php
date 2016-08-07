<?php
$app['cache.dir'] = $config['cache.dir'];
$app['lastFMTracks'] = function ($app) {

    $lastFMJson = $app['cache.dir'].'lastfm.json';

    //prevents needlessly overloading lastfm api endpoint
    if ( ! file_exists($lastFMJson) || file_exists($lastFMJson) && filemtime($lastFMJson) < strtotime('-6 hours')) {

        if ( ! file_exists($lastFMJson)) {
            touch($lastFMJson);
        }

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
        file_put_contents($lastFMJson, $request);

    }

    return json_decode(file_get_contents($lastFMJson));
};

$app['pageModTime'] = function () {
    $incls     = get_included_files();
    $included  = array_filter($incls, "is_file");
    $mod_times = array_map('filemtime', $included);
    $mod_time  = max($mod_times);

    return $mod_time;
};
