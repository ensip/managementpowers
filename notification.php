<?php
syslog (LOG_INFO, __FILE__);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

$token = file_get_contents('/usr/local/lib/network/token_network');

$auth_management = '';
if (isset($_SERVER['HTTP_AUTH_MANAGEMENT'])) {
	$auth_management = $_SERVER['HTTP_AUTH_MANAGEMENT'];
}

//foreach ($_SERVER as $key => $val) syslog(LOG_INFO, __FILE__ . "_server : $key - $val \n");

if ($auth_management !== $token) {
	syslog (LOG_INFO, __FILE__ . ":token-incorrecto token:$token vs auth_management : $auth_management");
	exit();
}
syslog (LOG_INFO, __FILE__ . ":token-ok-continue token:$token");

$json = file_get_contents("php://input");
$order = json_decode($json);

syslog (LOG_INFO, __FILE__ . ":order: $json\n");
//print_r($post);

if (isset($order->eventName) && $order->eventName != 'CAPTURED') {
	$res = invokeCurlRequest($json, $token);
	syslog (LOG_INFO, __FILE__ . ":invokeCurlRequest: $res\n");
}

function invokeCurlRequest($post, $token) {

	$ch = curl_init();
	$headers = [];
	$headers[] = "managementaut: Bearer " . base64_encode($token);
	$headers[] = "managementtype: test";	       
	$headers[] = "Content-Type: application/json";
	
	$url = 'https://ensip.com/notificacion_pagos_network.php';

	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, 'management:' . $token);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	
	curl_close ($ch);

	if ($info['http_code'] == '200') {
		//print_r($server_output);
		syslog(LOG_INFO, __FILE__ . ':connection-ensip Success');
		return 'ok';
	}
	return 'ko';
}
