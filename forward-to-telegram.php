<?php
if (empty($_GET) || !isset($_GET['action'])) die('Action is not defined!');
if (empty($_POST)) die('POST is not defined!');

if (!isset($_POST['token'])) die('Bot token is not defined!');
$token = trim($_POST['token']);
unset($_POST['token']);

preg_match('/(\d+):[\w\-]+/', $token, $matches);
if (!isset($matches[1]))
    die('Invalid token!');

$handle = \curl_init();

$headers   = [];
$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.29 Safari/537.36';
$headers[] = 'Content-Type: application/json; charset=UTF-8';
$headers[] = 'Cache-Control: no-cache';
// $headers[] = 'Connection: keep-alive';
\curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

\curl_setopt_array($handle, [
	CURLOPT_URL            => "https://api.telegram.org/bot$token/" . trim($_GET['action']),
	CURLOPT_POST           => true, // Enable the POST request.
	CURLOPT_CONNECTTIMEOUT => 0,
	CURLOPT_TIMEOUT        => 10, // Timeout in seconds
	CURLOPT_ENCODING       => 'gzip, deflate',
	CURLOPT_RETURNTRANSFER => true,
]);

\curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($_POST));
echo \curl_exec($handle);