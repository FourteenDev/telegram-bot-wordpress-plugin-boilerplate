<?php
$handle = \curl_init();

$headers   = [];
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.29 Safari/537.36';
$headers[] = 'Content-Type: application/json; charset=UTF-8';
$headers[] = 'Cache-Control: no-cache';
// $headers[] = 'Connection: keep-alive';
\curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

\curl_setopt_array($handle, [
	CURLOPT_URL            => 'https://website.com/wp-json/fdtbwpb/v1/get_message',
	CURLOPT_POST           => true, // Enable the POST request.
	CURLOPT_CONNECTTIMEOUT => 0,
	CURLOPT_TIMEOUT        => 10, // Timeout in seconds
	CURLOPT_ENCODING       => 'gzip, deflate',
	CURLOPT_RETURNTRANSFER => true,
]);

\curl_setopt($handle, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
\curl_exec($handle);