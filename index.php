<?php
set_time_limit(0);

$address = '127.0.0.1';
$port = 8000;

$sock = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($sock, $address, $port) or die('Could not bind to address');

echo "\n Listening On port $port For Connection... \n\n";

while(1)
{
    clearstatcache();
    socket_listen($sock);
    $client = socket_accept($sock);

    $input = socket_read($client, 1024);

    $incoming = array();
    $incoming = explode("\r\n", $input);

    $fetchArray = array();
    $fetchArray = explode(" ", $incoming[0]);

    echo 'incoming';
    echo '<pre>'; print_r($incoming); echo '</pre>';
    echo 'fetcharray';
    echo '<pre>'; print_r($fetchArray); echo '</pre>';

    $file = $fetchArray[1];
    if($file == "/"){
        $file = "index.html"; 

    } else {

        $filearray = array();
        $filearray = explode("/", $file);
        $file = $filearray[1];
    }

echo $fetchArray[0] . " Request " . $file . "\n"; 

$output = "";
$Header = "HTTP/1.1 200 OK \r\n" .
"content-Type: text/html \r\n" .
"cache-control: private, max-age=10 \r\n" .
"ETag: teste \r\n" .
"Keep-Alive: timeout=5, max=99 \n\r" .
"date: " . date("Y-m-d h:i:sa", strtotime("now")) . "\r\n\r\n";

$Content = file_get_contents($file);
$output = $Header . $Content;

    socket_write($client,$output,strlen($output));
    socket_close($client);
}
