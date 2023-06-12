<?php
syslog (LOG_INFO, __FILE__);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

function notification_log($json) {
	$fichero_logs = 'notification_live.log';
	$myfile = fopen($fichero_logs, "w") or die("Unable to open file!");
	fwrite($myfile, $json);
	fclose($myfile);
}

function checkAuthManagement($token) {
	
	$auth_management = getAuthManagement();

	if ($auth_management !== $token) {
		
		syslog (LOG_INFO, __FILE__ . ":token-incorrecto token:$token vs auth_management : $auth_management");
		return false;
	}
	
	syslog (LOG_INFO, __FILE__ . ":token-ok-continue token:$token");
	return true;

}

function checkServint($order) {

	if (isset($order->order->merchantDefinedData)) {
		$merchant_data = $order->order->merchantDefinedData;

		if (isset($merchant_data->plataforma) && $merchant_data->plataforma == 'svnt-RQciiow8k01wOyJxqw2Z6XFt8iUF3zXA64') {
			return true;
		}
	} 	
	return false;
}

function getAuthManagement() {
	
	$auth_management = '';
	if (isset($_SERVER['HTTP_AUTH_MANAGEMENT'])) {
		$auth_management = $_SERVER['HTTP_AUTH_MANAGEMENT'];
	}
	return $auth_management;
}

function getJsonOrder($location = '', $tipo_log = '') {

	$json = '';
	if (empty($location)) {
		$json = file_get_contents("php://input");
	} else {
		//order, order_usd, order_failed, order_usd_failed
		if (!empty($tipo_log)) {
			switch($tipo_log) {
			case 'order':
				$file = 'order';
				break;
			case 'order_usd':
				$file = 'order_usd';
				break;
			case 'order_failed':
				$file = 'order_failed';
				break;	
			case 'order_usd_failed':
				$file = 'order_usd_failed';
				break;
			default:
				$file = $tipo_log;
				break;

			}
		}
		echo $path = '/home/diego/orders_tpv/'.$file.'.log';
		
		if (is_file($path)){
			$source = fopen($path, 'r');
		
			$json = fread($source, filesize($path));
		} else {
			echo('path '.$path.' not read');
		}
	}
	return $json;
}

function getNotificationUrl($plataforma) {
	$notificaciones = array(
		'ensip' => 'https://ensip.com/notificacion_pagos_network.php',
		//'servint' => 'https://servint.jyctel.com/tpv-dubai-iframe/notificacion-pagos/?'
		'servint' => 'https://servintnew.jyctel.com/tpv-dubai-iframe/notificacion-pagos/?'
	);

	if (isset($notificaciones[$plataforma])) {
		$url = $notificaciones[$plataforma]; 
	}
	return $url;
}

function getToken() {
	$token = file_get_contents('/usr/local/lib/network/token_network');
	return $token;
}

function getOrderAutorizada($event_name) {
       
	if ($event_name === 'AUTHORISED') {
		return true;
	}
	return false;
}

function invokeCurlRequestEnsip($json, $token, $url) {

	$ch = curl_init();

	$headers  = array("managementaut: Bearer " . base64_encode($token), "Content-Type: application/json");
	//$headers = array();

	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, 'management:' . $token);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	print_r($info);	
	curl_close ($ch);

	if ($info['http_code'] == '200') {
		//print_r($server_output);
		syslog(LOG_INFO, __FILE__ . ':connection '.$url.' Success');
		return 'ok';
	}
	syslog(LOG_INFO, __FILE__ . ':connection '.$url.' Error ('.$info['http_code'].')');
	return 'ko';
}
function invokeCurlRequestServint($url, $token) {
	
	syslog(LOG_INFO, __method__ .' :1');
	$headers  = array("managementaut: Bearer " . base64_encode($token), "Content-Type: application/json");

	syslog(LOG_INFO, __method__ .' :2');
        $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	syslog(LOG_INFO, __method__ .' :3');

	//print_r($info);	
	curl_close ($ch);

	/*
	echo "http-code: ".$info['http_code'] . "\n";
	var_dump($server_output);
	 */

	if ($info['http_code'] == '200') {
		syslog(LOG_INFO, __FILE__ . ':connection '.$url.' Success');
		return 'ok';
	}
	syslog(LOG_INFO, __FILE__ . ':connection '.$url.' Error ('.$info['http_code'].')');
	return 'ko';
}

$test = 0;
if (isset($argv[1])) {
	$test = $argv[1]; 
}
$test = 0;

$tipo_log = '';
if (isset($argv[2])) {
	$tipo_log = $argv[2]; //order, order_usd, order_failed, order_usd_failed
}

//foreach ($_SERVER as $key => $val) syslog(LOG_INFO, __FILE__ . "_server : $key - $val \n");
$token = getToken();
if (!$test && !checkAuthManagement($token)) {
	exit('exit-checkAuthManagement');
}

$json = getJsonOrder($test, $tipo_log);

if ($order = json_decode($json)) {
	if ($test) {
		print_r($order);
	}
	syslog (LOG_INFO, __FILE__ . ":order: $json\n");
	syslog (LOG_INFO, __FILE__ . ":order_decoded: ".print_r($order, true));

	$es_managementpowers = false;
	if ($order and is_object($order) and preg_match("!https://www.managementpowers.com/result_pago.php!", $order->order->merchantAttributes->redirectUrl)) {
		// Es un pago de managementpowers. Guardamos en fichero para su comprobación en result_pago.php.
		$es_managementpowers = true;

		if (!file_exists("/var/www/managementpowers/tmp/tokens_managementpowers")) {
			mkdir ("/var/www/managementpowers/tmp/tokens_managementpowers");
		}

		$ref = $order->order->reference;

		file_put_contents("/var/www/managementpowers/tmp/tokens_managementpowers/$ref.txt", serialize($order));
	}
	
	if (!$es_managementpowers) {
		// Reenvío a notificación real.
		
		$plataforma = 'ensip';
		if (checkServint($order)) {
			$plataforma = 'servint';
		}
		if ($test) {
			echo $plataforma;
		}
		
		$url = getNotificationUrl($plataforma);
		//if (isset($order->eventName) && $order->eventName != 'CAPTURED') {
		if ($plataforma == 'ensip') {
			if (isset($order->eventName) && getOrderAutorizada($order->eventName)) { //ENVIA SOLO AUTHORISED
				
				$token = getToken();
				if ($test)
					echo $token;

				$res = invokeCurlRequestEnsip($json, $token, $url);
				syslog (LOG_INFO, __FILE__ . ":invokeCurlRequest: $res\n");
			}

		}
		if ($plataforma == 'servint') {
			
			if (isset($order->eventName) && $order->eventName != 'CAPTURED') { //ENVIA SOLO DIFERENTE A CAPTURED, para poder dar los errors
				
				syslog (LOG_INFO, __FILE__ . ":eventName: ".$order->eventName."\n");

				//$token = getToken();

				$url .= 'req='.base64_encode($json);
				$res = invokeCurlRequestServint($url, $token);
				
				if ($test) {
					var_dump($res);
				}
				
				syslog (LOG_INFO, __FILE__ . ":eventName:".$order->eventName.":invokeCurlRequest: $res\n");
			}
		}
	}
} else {
	exit("\nnot json file");
}
