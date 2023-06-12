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

function getJsonOrder($test = '', $tipo_log = '', $tipo_test = '') {

	$json = '';
	if (empty($test)) {
		$json = file_get_contents("php://input");
	} else {
		//order, order_usd, order_failed, order_usd_failed
		if (!empty($tipo_log) && $tipo_test == 'file') {
			
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

			echo $path = '/home/diego/orders_tpv/'.$file.'.log';
			
			if (is_file($path)){
				$source = fopen($path, 'r');
			
				$json = fread($source, filesize($path));
			} else {
				echo('path '.$path.' not read');
			}

		} else if ($tipo_test == 'text') {
			$json = '{"eventId":"71151ab9-2c06-4015-917e-efd0c220a0fd","eventName":"DECLINED","order":{"_id":"urn:order:47cc3f33-1259-4a88-9f24-52ead4b5ddba","_links":{"self":{"href":"http://transaction-service/transactions/outlets/24b40817-ef43-44f2-8c6c-f341f12498b6/orders/47cc3f33-1259-4a88-9f24-52ead4b5ddba"},"tenant-brand":{"href":"http://config-service/config/outlets/24b40817-ef43-44f2-8c6c-f341f12498b6/configs/tenant-brand"},"merchant-brand":{"href":"http://config-service/config/outlets/24b40817-ef43-44f2-8c6c-f341f12498b6/configs/merchant-brand"}},"type":"SINGLE","merchantDefinedData":{"user_id":"0","currency":"USD","token":"E-8b27c6f3c93682d5a292f527812b1d50","epochtime":"16484751662085","servicio":"ZW5zaXA=","plataforma":"svnt-RQciiow8k01wOyJxqw2Z6XFt8iUF3zXA64"},"action":"SALE","amount":{"currencyCode":"USD","value":18400},"language":"en","merchantAttributes":{"cancelUrl":"https://servint.jyctel.com/tpv-dubai-iframe/notificacion-pps/?","redirectUrl":"https://servint.jyctel.com/tpv-dubai-iframe/notificacion-pps/?","skipConfirmationPage":"true","skip3DS":"true","cancelText":"Cancelar pago"},"emailAddress":"uw4p1p8@mail.com","reference":"47cc3f33-1259-4a88-9f24-52ead4b5ddba","outletId":"24b40817-ef43-44f2-8c6c-f341f12498b6","createDateTime":"2022-03-28T13:46:06.520Z","paymentMethods":{"card":["MASTERCARD","VISA"]},"billingAddress":{"firstName":"-","lastName":"-","address1":"-","city":"-","countryCode":"0"},"referrer":"urn:Ecom:47cc3f33-1259-4a88-9f24-52ead4b5ddba","merchantOrderReference":"E-8b27c6f3c93682d5a292f527812b1d50","formattedAmount":"USD184","formattedOrderSummary":{},"_embedded":{"payment":[{"_id":"urn:payment:b86803fd-98d5-4de1-81b4-5336d8294313","_links":{"self":{"href":"http://transaction-service/transactions/outlets/24b40817-ef43-44f2-8c6c-f341f12498b6/orders/47cc3f33-1259-4a88-9f24-52ead4b5ddba/payments/b86803fd-98d5-4de1-81b4-5336d8294313"},"curies":[{"name":"cnp","href":"http://transaction-service/docs/rels/{rel}","templated":true}]},"reference":"b86803fd-98d5-4de1-81b4-5336d8294313","paymentMethod":{"expiry":"2026-02","cardholderName":"swsw","name":"VISA","pan":"453506******9513"},"state":"FAILED","amount":{"currencyCode":"USD","value":18400},"updateDateTime":"2022-03-28T13:47:43.935Z","outletId":"24b40817-ef43-44f2-8c6c-f341f12498b6","orderReference":"47cc3f33-1259-4a88-9f24-52ead4b5ddba","merchantOrderReference":"E-8b27c6f3c93682d5a292f527812b1d50","authResponse":{"authorizationCode":"      ","success":false,"resultCode":"51","resultMessage":"Insufficient funds","mid":"002200006366","rrn":"208617052592"},"3ds":{"status":"SUCCESS","eci":"05","eciDescription":"Card holder authenticated","summaryText":"The card-holder has been successfully authenticated by their card issuer."}}]}}}';
		}
	}
	return $json;
}

function getNotificationUrl($plataforma) {
	$notificaciones = array(
		'ensip' => 'https://ensip.com/notificacion_pagos_network.php',
		'servint' => 'https://servint.jyctel.com/tpv-dubai-iframe/notificacion-pagos/?'
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
	echo base64_encode($token);
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

$tipo_log = '';
if (isset($argv[2])) {
	$tipo_log = $argv[2]; //order, order_usd, order_failed, order_usd_failed
}

//foreach ($_SERVER as $key => $val) syslog(LOG_INFO, __FILE__ . "_server : $key - $val \n");
$token = getToken();
if (!$test && !checkAuthManagement($token)) {
	exit('exit-checkAuthManagement');
}

$json = getJsonOrder($test, $tipo_log, 'text');

if ($order = json_decode($json)) {
	if ($test) {
		print_r($order);
	}
	syslog (LOG_INFO, __FILE__ . ":order: $json\n");
	
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

			echo $url .= 'req='.base64_encode($json);
			exit;
			$res = invokeCurlRequestServint($url, $token);
			
			if ($test) {
				var_dump($res);
			}
			
			syslog (LOG_INFO, __FILE__ . ":eventName:".$order->eventName.":invokeCurlRequest: $res\n");
		}
	}
} else {
	exit("\nnot json file");
}
