<?php

// * Address and port of the forwarding server
$target_ip = 'example.com';
$target_port = '443';

// * Request forwarding
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$target_ip:$target_port".$_SERVER['REQUEST_URI']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);

// * Checking the SSL certificate
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// * Perform routing of the incoming request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $params = $_GET;
} else {
    $params = $_POST;
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
}

// * Add the headers of the incoming request to the forwarded request
$headers = array();
foreach (getallheaders() as $name => $value) {
    if ($name != 'Host' && $name != 'Content-Length') {
        $headers[] = "$name: $value";
    }
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// * Receiving the response of the forwarded request and returning to the client
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

// * Return the response of the forwarded request to the client
foreach (explode("\r\n", $header) as $header_line) {
    header($header_line);
}
echo $body;
