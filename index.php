<?php
set_time_limit(0);

$address = '127.0.0.1';
$port = $argv[1];

$sock = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($sock, $address, $port) or die('Erro de endereço!!!');

echo "\n Server iniciado na porta " . $port . " | " . $address . ":" . $port . " \n\n";
$cache = [];
$cacheTime = $argv[2];
echo 'cachetime: ' . $cacheTime . "\n\n";

while(1)
{
    socket_listen($sock);
    $client = socket_accept($sock);

    $input = socket_read($client, 1024);

    $incoming = array();
    $incoming = explode("\r\n", $input);

    $fetchArray = array();
    $fetchArray = explode(" ", $incoming[0]);
    
    $input = $fetchArray[1];
    
    if($input == "/"){
        $file = "index.html";
    } else {
        $filearray = array();
        $filearray = explode("/", $input);
        $file = $filearray[1];
    }
    
    if (file_exists($file) && filemtime($file) > $cache[$file]['lastTimeRequest']) { 
        unset($cache[$file]);
    }
     
    if (isset($cache[$file]) && $cache[$file]['timeToExpire'] > time()) {
        $output = "";
        $Header = "HTTP/1.1 200 OK \r\n" .
        "Date: " . date("m-d-Y h:i:s a", strtotime("now")) . "\r\n\r\n";
        
        $cache[$file]['lastTimeRequest'] = time();
        
        $output = $Header . $cache[$file]['content'];
        echo $fetchArray[0] . " Request do cache " . $file . "\n";
        // echo 'lastTime' . $cache[$file]['lastTimeRequest'] . "\n";
        // echo 'timeToExpire' . $cache[$file]['timeToExpire'] . "\n";
    } else {
        unset($cache[$file]);
        
        $output = "";
        $Header = "HTTP/1.1 200 OK \r\n" .
        "Date: " . date("m-d-Y h:i:s a", strtotime("now")) . "\r\n\r\n";
        
        $timeToExpire = time() + $cacheTime;
        $Content = file_get_contents($file);
        $cache[$file] = [
            'content' => $Content,
            'lastTimeRequest' => time(),
            'timeToExpire' => $timeToExpire
        ];

        $output = $Header . $Content;
        echo $fetchArray[0] . " Request da memoria " . $file . "\n";
        // echo 'lastTime' . $cache[$file]['lastTimeRequest'] . "\n";
        // echo 'timeToExpire' . $cache[$file]['timeToExpire'] . "\n";
    }

    socket_write($client,$output,strlen($output));
    socket_close($client);
}
