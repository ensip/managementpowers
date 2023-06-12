<?php
include('PayTpvDubaiVars.php');
//ini_set('default_socket_timeout', 3000);
/**
 * API PayTPV
 * El servicio se obtiene del usuario:
 * 	- Si se usa un integer se supone Ensip
 * 	- Si se usa un cardcode (CXXXXX) se supone Jyctel
 * 	- Si se usa un integer precedido de P se supone contrato Jyctel (PXXXXXXX);
 *
 *  Las tarjetas de prueba son las siguientes:
 *  https://docs.ngenius-payments.com/reference/sandbox-test-environment
 */
class PayTpvDubaiApi
{
	protected $api_key = '';
	protected $outlet = '';
	protected $paytpv_endpoint = '';
	protected $endpointurl = '';

	protected $test = false;
	protected $cbn_real = false;	// Poner a true tras implementar CBN en real

	private $log_activado = true;
	private $merchant = null;
	private $merchants_data = null;
	protected $service = null;
	public $usuario = null;
	public $usuario_servint = null;
	
	private $datos_usuario = array(
		'usuario_id' => 0,
		'email' => '',
		'iso_pais' => 0,
		'ciudad' => '-',
		'nombre' => '-',
		'apellido' => '-',
		'direccion' => '-',
		'divisa' => 'EUR'
	);
	public $is_pre = false;
	protected $ip;

	protected $divisa = 'EUR';
	
	public function __construct($usuario = null, $currency = 'EUR', $servicio = null, $opts = []) {
		$this->log(__FILE__ . ":".__METHOD__ . ":PayTpvApi usuario: $usuario, currency: $currency, servicio: $servicio, opts: ".print_r($opts, true));

		if (is_null($usuario) || empty($usuario) || is_null($servicio)) {
			if (is_null($usuario) || empty($usuario)) {
				syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": usuario empty|null");
			}
			if (is_null($servicio)) {
				syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": servicio null");
			}
			//return false;
		}
		
		$this->set_service($usuario, $servicio);
		$this->set_is_pre($usuario);
		$this->set_usuario_servint();
		$this->set_usuario($usuario);
		$this->set_data_usuario(); //falta completar
		$this->set_divisa($currency);
		$this->set_test($usuario, $servicio);
		$this->set_merchant();
		if (is_null($this->merchant)) {
			syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": no hay merchant");
			return false;
		} else {
			$this->set_api_key();
			$this->set_outlet();
		}

		
		$this->determine_ip();

		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": Construct usuario: $this->usuario, $this->divisa, service: $this->service, is_pre: $this->is_pre, IP: $this->ip");
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": merchant:".print_r($this->merchant, true));
	}

	public function AddUserUrl($transreference, $lang = "ES", $urlok = null, $urlko = null) {
		$pretest = array();

		$operation = new \stdClass();
		$operation->Type = 107;	// Añadir tarjeta
		$operation->Reference = $transreference;
		$operation->Language = $lang;
		if (isset($urlok) && $urlok) $operation->UrlOk = $urlok;
		if (isset($urlko) && $urlko) $operation->UrlKo = $urlko;

		$operation->Hash = $this->GenerateHash($operation, $operation->Type);
		$lastrequest = $this->ComposeURLParams($operation, $operation->Type);

		$pretest = $this->CheckUrlError($lastrequest);
		$pretest["URL_REDIRECT"] = ($this->endpointurl.$lastrequest); 

		return $this->SendResponse($pretest);
	}

	public function add_user($creditCard, $expiryDate, $cvv2) {

		return false;
	}

	public function add_user_url() {
		$response = array('status' => 0);

		$user = $this->usuario;

		if ($this->service == "ensip") {
			$order = $user."_EAUREF_".time().rand(1000,9999);
		} else if ($this->service == "cbn") {
			$order = $user."_CAUREF_".time().rand(1000,9999);
		} else {
			$order = $user."_AUREF_".time().rand(1000,9999);
		}

		$this->log("order: ".$order);

		$urlok = "http://".$_SERVER['HTTP_HOST'].Url::to(['tpv-iframe/index']);
		$urlko = null;

		$result = $this->AddUserUrl($order, "ES", $urlok, $urlko);

		if ($result && is_object($result) && $result->RESULT == "OK") {
			return $result->URL_REDIRECT;
		}

		return false;
	}

	private function determine_ip() {

		$ip = "2.139.151.77";
		if (defined('WEBAPP')) {
			$ip = Yii::$app->request->userIP;	// Incluir IP desde donde se realiza la compra
			if (preg_match('/^192\.168\.0\./', $ip)) {
				$ip = "2.139.151.77";
			}
		}

		$this->ip = $ip;
	}

	private function set_test($usuario, $servicio) {
		if (
			(0) || 
			(true && $usuario == "C020060") || 
			(true and $servicio == "ensip" && $usuario == "91184") || 
			(true && $servicio == "cbn" && $usuario == "1")
		) {
			$this->test = true;
		}
		syslog(LOG_INFO, __FILE__.": Pagos modo TEST : " . ($this->test ? 'success' : 'off'));
	}

	private function set_usuario($usuario) {
		
		if ($this->is_pre) {
			$this->usuario = $this->generateUserPre();
		} else{
			$this->usuario = $usuario;
		}
	}

	private function set_usuario_servint() {
		$this->usuario_servint = 0;
	}

	public function get_access_token() {

		$access_token = '';

		$tokenHeaders  = array("Authorization: Basic ".$this->api_key, "Content-Type: application/vnd.ni-identity.v1+json");
		syslog(LOG_INFO, __FILE__.":".__METHOD__ . ":tokenHeaders:" . serialize($tokenHeaders));
		$network_id_service_url = $this->get_merchant_value('network_id_service_url');

		if ($this->test) {
			$tokenResponse = $this->invoke_curl_request("POST", $network_id_service_url, $tokenHeaders, json_encode(array('realmName' => 'ni')));
			syslog(LOG_INFO, __FILE__.":".__METHOD__ . ":test:tokenResponse:" . serialize($tokenResponse));
		} else {
			$tokenResponse = $this->invoke_curl_request("POST", $network_id_service_url, $tokenHeaders, null);
			syslog(LOG_INFO, __FILE__.":".__METHOD__ . ":tokenResponse:" . serialize($tokenResponse));
		}
		$tokenResponse = json_decode($tokenResponse);

		if (isset($tokenResponse->access_token)) {
			$access_token  = $tokenResponse->access_token;

		} else return $access_token;
		
		syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':'.$access_token . ':apiKey:'.$this->api_key . ':network_id_service_url: '.$network_id_service_url);

		return $access_token;
	}

	private function get_merchant_value($key) {
		if (isset($this->merchant[$key])) {
			return $this->merchant[$key];
		}
	}

	public function get_model_pagos_referencia() {
		// Hacer acceso a pagos_referencia de la BBDD correcta.
		if ($this->service == "ensip") {
			syslog(LOG_INFO, __FILE__.": ENSIP PR");
			$modelpr = new EnsipPagosReferencia();
		} else if ($this->service == "jyctel") {
			syslog(LOG_INFO, __FILE__.": JYCTEL PR");
			$modelpr = new JyctelPagosReferencia();
		} else if ($this->service == "cbn") {
			syslog(LOG_INFO, __FILE__.": CBN PR");
			$modelpr = new CbnPagosReferencia();
		} else {
			$modelpr = new JyctelPagosReferencia();
		}
		return $modelpr;
	}

	public function get_model_network_tokens() {
		// Hacer acceso a pagos_referencia de la BBDD correcta.
		if ($this->service == "ensip") {
			$modelpttk = new EnsipNetworkTokens();
		}  else if ($this->service == "cbn") {
			$modelpttk = new CbnNetworkTokens();
		} 
		return $modelpttk;
	}

	public function get_model_payment_tokens() {
		// Hacer acceso a payment_tokens de la BBDD correcta.
		if ($this->service == "ensip") {
			$modelpt = new EnsipPaymentTokens();
		} else if ($this->service == "jyctel") {
			$modelpt = new JyctelPaymentTokens();
		} else if ($this->service == "cbn") {
			$modelpt = new CbnPaymentTokens();
		} else {
			$modelpt = new JyctelPaymentTokens();
		}
		return $modelpt;
	}

	/*
	 *	TODO: cambiar método si existe en el futuro
	 *
	 * */
	public function execute_purchase($id_pagos_referencia, $amount, $referencia, $currency, $productDescription, $owner) {
		return false;
	}


	public function execute_purchase_url($amount, $id_pagos_referencia = null, $force_3ds = false) {
		syslog(LOG_INFO, __FILE__.":".__METHOD__ . ": amount: $amount, id_pagos_referencia: $id_pagos_referencia service: $this->service");
		$response = array('status' => 0);

		$user = $this->usuario;
		$usuario_servint = $this->usuario_servint;

		$campo_usuario = "id_usuario";
		$campo_metodo = 'metodo';
		if ($this->service == 'ensip') {
			$campo_metodo = 'method';
			if ($this->is_pre) {
				$campo_usuario = "referencia_previa";
			}
		}		

		$model_tokens = new Tokens();
		$token = $model_tokens->get_new_token();
		$order = $this->generateToken($token);	
		$modelpt = $this->get_model_payment_tokens();
		// Primero buscar token iframe_reserved con mínimo 24 horas de antigüedad para este usuario servint y actualizar (no_pagado), TODO: iframe_reserved -> resetear cuando se recibe notificación, buscar por token.
		
		$where = [	// Esta condición es para jyctel y ensip
			'and',
			['servint_user_id' => $usuario_servint],
			['method' => "network_iframe"],
			['or',
				['pagado' => 0],
				['pagado' => null],
			],
			['entregado' => 0],
			['iframe_reserved' => 1],
			['<=', 'fecha', new Expression("date_sub(now(), interval 24 hour)")],
		];

		if ($this->service == "cbn") {
			$where = [	// Esta condición es para cbn
				'and',
				['servint_user_id' => $usuario_servint],
				['metodo' => "network_iframe"],
				['or',
					['pagado' => 0],
					['pagado' => null],
				],
				//['entregado' => 0],	// No existe en CBN
				['iframe_reserved' => 1],
				['<=', 'fecha', new Expression("date_sub(now(), interval 24 hour)")],
			];
		}
		

		$reserved = $modelpt->find()->where($where)->limit(1);
		$modelpt = $reserved->one();
		if (!$modelpt) {
			$modelpt = $this->get_model_payment_tokens();
		}
		
		$concept = "";
		if ($modelpt) {
			$modelpt->$campo_usuario = $user;
			$modelpt->servint_user_id = $usuario_servint;
			$modelpt->token = $token;
			$modelpt->tipo = "Pago manual";
			$modelpt->cantidad = $amount;
			$modelpt->iframe_reserved = 1;

			$modelpt->$campo_metodo = "network_iframe";
			
			if ($this->service == "cbn") {
				$modelpt->ip = $this->ip;
				$modelpt->descripcion_pago = "Pago TPV manual";
			} else {
				if ($this->service == "ensip" && !$this->is_pre) {
					$modelpt->id_usuario = 0;
				}
			}

			$modelpt->nombre_divisa = $this->divisa;

			if ($this->divisa == "TEST") 
				$modelpt->nombre_divisa = "EUR";

			$modelpt->fecha = date('Y-m-d H:i:s');
			$modelpt->notas = "Pago desde tpv manual para usuario $user realizado por usuario tpv $usuario_servint";
			$concept = "Pago desde tpv manual para usuario $user realizado por usuario tpv $usuario_servint";
			$modelpt->test = ($this->test ? 1 : 0);
			
			if (!$modelpt->save()) {
				syslog(LOG_INFO, __FILE__.": FORM (con errores): ".print_r($modelpt, true));
				syslog(LOG_INFO, __FILE__.": ERRORES FORM: ".print_r($modelpt->getErrors(), true));
				return null;
			}
		}
		
		//return "https://google.es";	

		$idUser = null;
		$tokenUser = null;
		if ($id_pagos_referencia) {
			$modelpr = $this->get_model_pagos_referencia();
			$ref = $modelpr->findOne($id_pagos_referencia);
			if ($ref) {
				$addUserResponseIdUser = $ref->id_usuario_proveedor;
				$addUserResponseTokenUser = $ref->token;
			} else {
				syslog(LOG_INFO, __FILE__ . "pr no encontrado idUser: $idUser, tokenUser: $tokenUser");
				return null;
			}

			$idUser		= $addUserResponseIdUser;
			$tokenUser	= $addUserResponseTokenUser;
		}

		$this->log("order: ".$order);
		//$this->log("modelpt en iframe: ".print_r($modelpt, true));

		$urlok = "https://".$_SERVER['HTTP_HOST'].Url::to(['tpv-dubai-iframe/notificacion-pps/?']);
		$urlko = "https://".$_SERVER['HTTP_HOST'].Url::to(['tpv-dubai-iframe/notificacion-pps/?']);
		
		$access_token = $this->get_access_token();

		$result = $this->ExecutePurchaseUrl($access_token, $order, $amount*100, "ES", $urlok, $urlko, $concept, $idUser, $tokenUser, $force_3ds);
		
		$this->log("order result: ".serialize($result));

		if ($result && is_object($result) && $result->RESULT == "OK") {
			return $result->URL_REDIRECT;
		}

		return false;
	}
	
	private function generateToken($token) {
		$order = "EM".$token;
		return $order;	
	}

	private function generateUserPre() {
		
		syslog(LOG_INFO, "ref_previa (jyctel|ensip|cbn) user");
		$usuario_servint = $this->usuario_servint;
		$user = "PRE".time();

		return $user;
	}
	public function info_user($id_pagos_referencia) {
		syslog(LOG_INFO, "INFO_USER ID: $id_pagos_referencia");
		return null; //TODO
	}

	private function invoke_curl_request($type, $url, $headers, $post) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($type == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		
		$server_output = curl_exec ($ch);
		curl_close ($ch);
			
		syslog(LOG_INFO, ":server_output:".serialize($server_output));

		return $server_output;
	}


	private function load_merchant_data() {

		$this->merchants_data = array(
			'ensip' => array(
				'EUR' => array(
					'TEST' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY_TEST,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL_TEST,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL_TEST,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL_TEST,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_TEST_EUR
					),
					'REAL' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_EUR
					),
				),
				'USD' => array(
					'TEST' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY_TEST,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL_TEST,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL_TEST,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL_TEST,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_TEST_USD
					),
					'REAL' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_USD
					),
				),
			),
			'cbn' => array(
				'EUR' => array(
					'TEST' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY_TEST,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL_TEST,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL_TEST,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL_TEST,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_TEST_EUR
					),
					'REAL' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_EUR
					),
				),
				'USD' => array(
					'TEST' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY_TEST,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL_TEST,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL_TEST,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL_TEST,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_TEST_USD
					),
					'REAL' => array(
						'network_api_key' => PayTpvDubaiVars::NETWORK_APIKEY,
						'network_id_service_url' => PayTpvDubaiVars::NETWORK_ID_SERVICE_URL,
						'network_txn_service_url' => PayTpvDubaiVars::NETWORK_TXN_SERVICE_URL,
						'network_res_id_service_url' => PayTpvDubaiVars::NETWORK_RES_ID_SERVICE_URL,
						'network_outlet' => PayTpvDubaiVars::NETWORK_OUTLET_USD
					),
				),
			)
		);
	}

	/*
	 *	TODO: cambiar método si existe en el futuro
	 *
	 * */
	public function remove_user($id_pagos_referencia) {
		return false;
	}

	private function set_api_key() {
		$this->api_key = $this->get_merchant_value('network_api_key');
	}
	
	private function set_outlet() {
		$this->outlet = $this->get_merchant_value('network_outlet');
	}

	private function set_service($usuario, $servicio) {
		
		if ($usuario && preg_match("/^C/", $usuario)) {
			// Cardcode Jyctel
			$this->service = "jyctel";

		} else if ($servicio and $servicio == "cbn") {	// TODO: PRE cbn y ensip?
			
			$this->service = "cbn";

		} else if ($servicio and $servicio == "ensip") {
			$this->service = "ensip";

		} else if (($servicio == null && $usuario == "") || $servicio == "jyctel" || (preg_match("/^PRE/", $usuario) || $usuario == "")) {
			$this->service = "jyctel";

		} else {
			$this->service = "ensip";
		}
	}

	/*
	 *	By default false
	 *	@state params: true/false
	 * */
	private function set_is_pre($usuario) {

		if (!$usuario) {
			if ($this->service == 'cbn' || $this->service == 'ensip') {
				$this->is_pre = 'true';
			}
		}
	}
	private function set_merchant() {
		
		$this->load_merchant_data(); 
		$merchant = $this->set_merchant_from_service();
		
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": set_merchant:" . serialize($merchant));

		if (!empty($merchant)) {
			$this->set_merchant_from_currency_and_test_or_real($merchant);		
		}
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ": set_merchant:" . serialize($this->merchant));
	}
	private function set_merchant_from_service() {
		if (isset($this->merchants_data[$this->service])) {
			return $this->merchants_data[$this->service];
		}
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ":".__METHOD__ . ":no_merchant_service : " . $this->service);
		return '';
	}

	private function set_merchant_from_currency_and_test_or_real($merchant_currency) {

		$merchant = '';
		if (isset($merchant_currency[$this->divisa])) {
			if ($this->test) {
				$merchant = $merchant_currency[$this->divisa]['TEST'];
			} else {
				$merchant = $merchant_currency[$this->divisa]['REAL'];
			}	
		}	
		if (!empty($merchant)) {
			$this->merchant = $merchant;
		}
	}

	public function paytpv_get_error($id) {
		$errors = array(
				"0" => "Sin error",
				"1" => "Error",
				"442" => "Email usiario incorrecto",
				"100" => "Tarjeta caducada",
				"101" => "En lista negra",
				"102" => "Operación no permitida para el tipo de tarjeta",
				"103" => "Por favor, contacte con el banco emisor",
				"104" => "Error inesperado",
				"105" => "Crédito insuficiente para realizar el cargo",
				"106" => "Tarjeta no dada de alta o no registrada por el banco emisor",
				"107" => "Error de formato en los datos capturados. CodValid",
				"108" => "Error en el número de la tarjeta",
				"109" => "Error en FechaCaducidad",
				"110" => "Error en los datos",
				"111" => "Bloque CVC2 incorrecto",
				"112" => "Por favor, contacte con el banco emisor",
				"113" => "Tarjeta de crédito no válida",
				"114" => "La tarjeta tiene restricciones de crédito",
				"115" => "El emisor de la tarjeta no pudo identificar al propietario",
				"116" => "Pago no permitido en operaciones fuera de línea",
				"118" => "Tarjeta caducada. Por favor retenga físicamente la tarjeta",
				"119" => "Tarjeta en lista negra. Por favor retenga físicamente la tarjeta",
				"120" => "Tarjeta perdida o robada. Por favor retenga físicamente la tarjeta",
				"121" => "Error en CVC2. Por favor retenga físicamente la tarjeta",
				"122" => "Error en el proceso pre-transacción. Inténtelo más tarde",
				"123" => "Operación denegada. Por favor retenga físicamente la tarjeta",
				"124" => "Cierre con acuerdo",
				"125" => "Cierre sin acuerdo",
				"126" => "No es posible cerrar en este momento",
				"127" => "Parámetro no válido",
				"128" => "Las transacciones no fueron finalizadas",
				"129" => "Referencia interna duplicada",
				"130" => "Operación anterior no encontrada. No se pudo ejecutar la devolución",
				"131" => "Preautorización caducada",
				"132" => "Operación no válida con la moneda actual",
				"133" => "Error en formato del mensaje",
				"134" => "Mensaje no reconocido por el sistema",
				"135" => "Bloque CVC2 incorrecto",
				"137" => "Tarjeta no válida",
				"138" => "Error en mensaje de pasarela",
				"139" => "Error en formato de pasarela",
				"140" => "Tarjeta inexistente",
				"141" => "Cantidad cero o no válida",
				"142" => "Operación cancelada",
				"143" => "Error de autenticación",
				"144" => "Denegado debido al nivel de seguridad",
				"145" => "Error en el mensaje PUC. Contacte con PAYTPV",
				"146" => "Error del sistema",
				"147" => "Transacción duplicada",
				"148" => "Error de MAC",
				"149" => "Liquidación rechazada",
				"150" => "Fecha/hora del sistema no sincronizada",
				"151" => "Fecha de caducidad no válida",
				"152" => "No se pudo encontrar la preautorización",
				"153" => "No se encontraron los datos solicitados",
				"154" => "No se puede realizar la operación con la tarjeta de crédito proporcionada",
				"500" => "Error inesperado",
				"501" => "Error inesperado",
				"502" => "Error inesperado",
				"504" => "Transacción cancelada previamente",
				"505" => "Transacción original denegada",
				"506" => "Datos de confirmación no válidos",
				"507" => "Error inesperado",
				"508" => "Transacción aún en proceso",
				"509" => "Error inesperado",
				"510" => "No es posible la devolución",
				"511" => "Error inesperado",
				"512" => "No es posible contactar con el banco emisor. Inténtelo más tarde",
				"513" => "Error inesperado",
				"514" => "Error inesperado",
				"515" => "Error inesperado",
				"516" => "Error inesperado",
				"517" => "Error inesperado",
				"518" => "Error inesperado",
				"519" => "Error inesperado",
				"520" => "Error inesperado",
				"521" => "Error inesperado",
				"522" => "Error inesperado",
				"523" => "Error inesperado",
				"524" => "Error inesperado",
				"525" => "Error inesperado",
				"526" => "Error inesperado",
				"527" => "Tipo de transacción desconocido",
				"528" => "Error inesperado",
				"529" => "Error inesperado",
				"530" => "Error inesperado",
				"531" => "Error inesperado",
				"532" => "Error inesperado",
				"533" => "Error inesperado",
				"534" => "Error inesperado",
				"535" => "Error inesperado",
				"536" => "Error inesperado",
				"537" => "Error inesperado",
				"538" => "Operación no cancelable",
				"539" => "Error inesperado",
				"540" => "Error inesperado",
				"541" => "Error inesperado",
				"542" => "Error inesperado",
				"543" => "Error inesperado",
				"544" => "Error inesperado",
				"545" => "Error inesperado",
				"546" => "Error inesperado",
				"547" => "Error inesperado",
				"548" => "Error inesperado",
				"549" => "Error inesperado",
				"550" => "Error inesperado",
				"551" => "Error inesperado",
				"552" => "Error inesperado",
				"553" => "Error inesperado",
				"554" => "Error inesperado",
				"555" => "No se pudo encontrar la operación previa",
				"556" => "Inconsistencia de datos en la validación de la cancelación",
				"557" => "El pago diferido no existe",
				"558" => "Error inesperado",
				"559" => "Error inesperado",
				"560" => "Error inesperado",
				"561" => "Error inesperado",
				"562" => "La tarjeta no admite preautorizaciones",
				"563" => "Inconsistencia de datos en confirmación",
				"564" => "Error inesperado",
				"565" => "Error inesperado",
				"567" => "Operación de devolución no definida correctamente",
				"569" => "Operación denegada",
				"1000" => "Cuenta no encontrada. Revise su configuración",
				"1001" => "Usuario no encontrado. Contacte con PAYTPV",
				"1002" => "Error en respuesta de pasarela. Contacte con PAYTPV",
				"1003" => "Firma no válida. Por favor, revise su configuración",
				"1004" => "Acceso no permitido",
				"1005" => "Formato de tarjeta de crédito no válido",
				"1006" => "Error en el campo Código de Validación",
				"1007" => "Error en el campo Fecha de Caducidad",
				"1008" => "Referencia de preautorización no encontrada",
				"1009" => "Datos de preautorización no encontrados",
				"1010" => "No se pudo enviar la devolución. Por favor reinténtelo más tarde",
				"1011" => "No se pudo conectar con el host",
				"1012" => "No se pudo resolver el proxy",
				"1013" => "No se pudo resolver el host",
				"1014" => "Inicialización fallida",
				"1015" => "No se ha encontrado el recurso HTTP",
				"1016" => "El rango de opciones no es válido para la transferencia HTTP",
				"1017" => "No se construyó correctamente el POST",
				"1018" => "El nombre de usuario no se encuentra bien formateado",
				"1019" => "Se agotó el tiempo de espera en la petición",
				"1020" => "Sin memoria",
				"1021" => "No se pudo conectar al servidor SSL",
				"1022" => "Protocolo no soportado",
				"1023" => "La URL dada no está bien formateada y no puede usarse",
				"1024" => "El usuario en la URL se formateó de manera incorrecta",
				"1025" => "No se pudo registrar ningún recurso disponible para completar la operación",
				"1026" => "Referencia externa duplicada",
				"1027" => "El total de las devoluciones no puede superar la operación original",
				"1028" => "La cuenta no se encuentra activa. Contacte con PAYTPV",
				"1029" => "La cuenta no se encuentra certificada. Contacte con PAYTPV",
				"1030" => "El producto está marcado para eliminar y no puede ser utilizado",
				"1031" => "Permisos insuficientes",
				"1032" => "El producto no puede ser utilizado en el entorno de pruebas",
				"1033" => "El producto no puede ser utilizado en el entorno de producción",
				"1034" => "No ha sido posible enviar la petición de devolución",
				"1035" => "Error en el campo IP de origen de la operación",
				"1036" => "Error en formato XML",
				"1037" => "El elemento raíz no es correcto",
				"1038" => "Campo DS_MERCHANT_AMOUNT incorrecto",
				"1039" => "Campo DS_MERCHANT_ORDER incorrecto",
				"1040" => "Campo DS_MERCHANT_MERCHANTCODE incorrecto",
				"1041" => "Campo DS_MERCHANT_CURRENCY incorrecto",
				"1042" => "Campo DS_MERCHANT_PAN incorrecto",
				"1043" => "Campo DS_MERCHANT_CVV2 incorrecto",
				"1044" => "Campo DS_MERCHANT_TRANSACTIONTYPE incorrecto",
				"1045" => "Campo DS_MERCHANT_TERMINAL incorrecto",
				"1046" => "Campo DS_MERCHANT_EXPIRYDATE incorrecto",
				"1047" => "Campo DS_MERCHANT_MERCHANTSIGNATURE incorrecto",
				"1048" => "Campo DS_ORIGINAL_IP incorrecto",
				"1049" => "No se encuentra el cliente",
				"1050" => "La nueva cantidad a preautorizar no puede superar la cantidad de la preautorización original",
				"1099" => "Error inesperado",
				"1100" => "Limite diario por tarjeta excedido",
				"1103" => "Error en el campo ACCOUNT",
				"1104" => "Error en el campo USERCODE",
				"1105" => "Error en el campo TERMINAL",
				"1106" => "Error en el campo OPERATION",
				"1107" => "Error en el campo REFERENCE",
				"1108" => "Error en el campo AMOUNT",
				"1109" => "Error en el campo CURRENCY",
				"1110" => "Error en el campo SIGNATURE",
				"1120" => "Operación no disponible",
				"1121" => "No se encuentra el cliente",
				"1122" => "Usuario no encontrado. Contacte con PAYTPV",
				"1123" => "Firma no válida. Por favor, revise su configuración",
				"1124" => "Operación no disponible con el usuario especificado",
				"1125" => "Operación no válida con una moneda distinta del Euro",
				"1127" => "Cantidad cero o no válida",
				"1128" => "Conversión de la moneda actual no válida",
				"1129" => "Cantidad no válida",
				"1130" => "No se encuentra el producto",
				"1131" => "Operación no válida con la moneda actual",
				"1132" => "Operación no válida con una moneda distina del Euro",
				"1133" => "Información del botón corrupta",
				"1134" => "La subscripción no puede ser mayor de la fecha de caducidad de la tarjeta",
				"1135" => "DS_EXECUTE no puede ser true si DS_SUBSCRIPTION_STARTDATE es diferente de hoy",
				"1136" => "Error en el campo PAYTPV_OPERATIONS_MERCHANTCODE",
				"1137" => "PAYTPV_OPERATIONS_TERMINAL debe ser Array",
				"1138" => "PAYTPV_OPERATIONS_OPERATIONS debe ser Array",
				"1139" => "Error en el campo PAYTPV_OPERATIONS_SIGNATURE",
				"1140" => "No se encuentra alguno de los PAYTPV_OPERATIONS_TERMINAL",
				"1141" => "Error en el intervalo de fechas solicitado",
				"1142" => "La solicitud no puede tener un intervalo mayor a 2 años",
				"1143" => "El estado de la operación es incorrecto",
				"1144" => "Error en los importes de la búsqueda",
				"1145" => "El tipo de operación solicitado no existe",
				"1146" => "Tipo de ordenación no reconocido",
				"1147" => "PAYTPV_OPERATIONS_SORTORDER no válido",
				"1148" => "Fecha de inicio de suscripción errónea",
				"1149" => "Fecha de final de suscripción errónea",
				"1150" => "Error en la periodicidad de la suscripción",
				"1151" => "Falta el parámetro usuarioXML",
				"1152" => "Falta el parámetro codigoCliente",
				"1153" => "Falta el parámetro usuarios",
				"1154" => "Falta el parámetro firma",
				"1155" => "El parámetro usuarios no tiene el formato correcto",
				"1156" => "Falta el parámetro type",
				"1157" => "Falta el parámetro name",
				"1158" => "Falta el parámetro surname",
				"1159" => "Falta el parámetro email",
				"1160" => "Falta el parámetro password",
				"1161" => "Falta el parámetro language",
				"1162" => "Falta el parámetro maxamount o su valor no puede ser 0",
				"1163" => "Falta el parámetro multicurrency",
				"1165" => "El parámetro permissions_specs no tiene el formato correcto",
				"1166" => "El parámetro permissions_products no tiene el formato correcto",
				"1167" => "El parámetro email no parece una dirección válida",
				"1168" => "El parámetro password no tiene la fortaleza suficiente",
				"1169" => "El valor del parámetro type no está admitido",
				"1170" => "El valor del parámetro language no está admitido",
				"1171" => "El formato del parámetro maxamount no está permitido",
				"1172" => "El valor del parámetro multicurrency no está admitido",
				"1173" => "El valor del parámetro permission_id - permissions_specs no está admitido",
				"1174" => "No existe el usuario",
				"1175" => "El usuario no tiene permisos para acceder al método altaUsario",
				"1176" => "No se encuentra la cuenta de cliente",
				"1177" => "No se pudo cargar el usuario de la cuenta",
				"1178" => "La firma no es correcta",
				"1179" => "No existen productos asociados a la cuenta",
				"1180" => "El valor del parámetro product_id - permissions_products no está autorizado",
				"1181" => "El valor del parámetro permission_id -permissions_products no está admitido",
				"1185" => "Límite mínimo por operación no permitido",
				"1186" => "Límite máximo por operación no permitido",
				"1187" => "Límite máximo diario no permitido",
				"1188" => "Límite máximo mensual no permitido",
				"1189" => "Cantidad máxima por tarjeta / 24h. no permitida",
				"1190" => "Cantidad máxima por tarjeta / 24h. / misma dirección IP no permitida",
				"1191" => "Límite de transacciones por dirección IP /día (diferentes tarjetas) no permitido",
				"1192" => "País no admitido (dirección IP del comercio)",
				"1193" => "Tipo de tarjeta (crédito / débito) no admitido",
				"1194" => "Marca de la tarjeta no admitida",
				"1195" => "Categoría de la tarjeta no admitida",
				"1196" => "Transacción desde país distinto al emisor de la tarjeta no admitida",
				"1197" => "Operación denegada. Filtro país emisor de la tarjeta no admitido",
				"1200" => "Operación denegada. Filtro misma tarjeta, distinto país en las últimas 48 horas",
				"1201" => "Número de intentos consecutivos erróneos con la misma tarjeta excedidos",
				"1202" => "Número de intentos fallidos (últimos 30 minutos) desde la misma dirección ip excedidos",
				"1203" => "Las credenciales PayPal no son válidas o no están configuradas",
				"1204" => "Recibido token incorrecto",
				"1205" => "No ha sido posible realizar la operación",
				"1206" => "providerID no disponible",
				"1207" => "Falta el parámetro operaciones o no tiene el formato correcto",
				"1208" => "Falta el parámetro paytpvMerchant",
				"1209" => "Falta el parámetro merchatID",
				"1210" => "Falta el parámetro terminalID",
				"1211" => "Falta el parámetro tpvID",
				"1212" => "Falta el parámetro operationType",
				"1213" => "Falta el parámetro operationResult",
				"1214" => "Falta el parámetro operationAmount",
				"1215" => "Falta el parámetro operationCurrency",
				"1216" => "Falta el parámetro operationDatetime",
				"1217" => "Falta el parámetro originalAmount",
				"1218" => "Falta el parámetro pan",
				"1219" => "Falta el parámetro expiryDate",
				"1220" => "Falta el parámetro reference",
				"1221" => "Falta el parámetro signature",
				"1222" => "Falta el parámetro original IP no tiene el formato correcto",
				"1223" => "Falta el parámetro authCode o errorCode",
				"1224" => "No se encuentra el producto de la operación",
				"1225" => "El tipo de la operación no está admitido",
				"1226" => "El resultado de la operación no está admitido",
				"1227" => "La moneda de la operación no está admitida",
				"1228" => "La fecha de la operación no tiene el formato correcto",
				"1229" => "La firma no es correcta",
				"1230" => "No se encuentra información de la cuenta asociada",
				"1231" => "No se encuentra información del producto asociado",
				"1232" => "No se encuentra información del usuario asociado",
				"1233" => "El producto no está configurado como multimoneda",
				"1234" => "La cantidad de la operación no tiene el formato correcto",
				"1235" => "La cantidad original de la operación no tiene el formato correcto",
				"1236" => "La tarjeta no tiene el formato correcto",
				"1237" => "La fecha de caducidad de la tarjeta no tiene el formato correcto",
				"1238" => "No puede inicializarse el servicio",
				"1239" => "No puede inicializarse el servicio",
				"1240" => "Método no implementado",
				"1241" => "No puede inicializarse el servicio",
				"1242" => "No puede finalizarse el servicio",
				"1243" => "Falta el parámetro operationCode",
				"1244" => "Falta el parámetro bankName",
				"1245" => "Falta el parámetro csb",
				"1246" => "Falta el parámetro userReference",
				"1247" => "No se encuentra el FUC enviado",
				"1248" => "Referencia externa duplicada. Operación en curso.",
				"1249" => "No se encuentra el parámetro [DS_]AGENT_FEE",
				"1250" => "El parámetro [DS_]AGENT_FEE no tienen el formato correcto",
				"1251" => "El parámetro DS_AGENT_FEE no es correcto",
				"1252" => "No se encuentra el parámetro CANCEL_URL",
				"1253" => "El parámetro CANCEL_URL no es correcto",
				"1254" => "Comercio con titular seguro y titular sin clave de compra segura",
				"1255" => "Llamada finalizada por el cliente",
				"1256" => "Llamada finalizada, intentos incorrectos excedidos",
				"1257" => "Llamada finalizada, intentos de operación excedidos",
				"1258" => "stationID no disponible",
				"1259" => "No ha sido posible establecer la sesión IVR",
				"1260" => "Falta el parámetro merchantCode",
				"1261" => "El parámetro merchantCode no es correcto",
				"1262" => "Falta el parámetro terminalIDDebtor",
				"1263" => "Falta el parámetro terminalIDCreditor",
				"1264" => "No dispone de permisos para realizar la operación",
				"1265" => "La cuenta Iban (terminalIDDebtor) no es válida",
				"1266" => "La cuenta Iban (terminalIDCreditor) no es válida",
				"1267" => "El BicCode de la cuenta Iban (terminalIDDebtor) no es válido",
				"1268" => "El BicCode de la cuenta Iban (terminalIDCreditor) no es válido",
				"1269" => "Falta el parámetro operationOrder",
				"1270" => "El parámetro operationOrder no tiene el formato correcto",
				"1271" => "El parámetro operationAmount no tiene el formato correcto",
				"1272" => "El parámetro operationDatetime no tiene el formato correcto",
				"1273" => "El parámetro operationConcept contiene caracteres inválidos o excede de 140 caracteres",
				"1274" => "No ha sido posible grabar la operación SEPA",
				"1275" => "No ha sido posible grabar la operación SEPA",
				// TODO: Acabar de introducir
			);
		if (isset($errors[$id])) {
			return $errors[$id];
		}
		return "Error no especificado";
	}
	// Funciones PayTPV Bankstore

	/**
	* Devuelve la URL para lanzar un add_user bajo IFRAME/Fullscreen
	* @param string $transreference Identificador único de la transacción
	* @param string $lang Idioma de los literales de la transacción
	* @return object Objeto de respuesta de la operación
	* @version 1.0 2022-01-31
	*/	
	public function ExecutePurchaseUrl($access_token, $transreference, $amount, $lang = "en", $urlok = null, $urlko = null, $concept = "", $idUser = null, $tokenUser = null, $force_3ds = false) {
		//108982 USD, 70 EUR
		syslog(LOG_INFO, __FILE__.": ExecutePurchaseUrl($transreference, $amount, $lang, $urlok, $urlko, $concept, $idUser, $tokenUser)");
		
		$order = array();

		$order['action'] = PayTpvDubaiVars::NETWORK_ACTION_ORDER; //Transaction mode ("AUTH" = authorize only, no automatic settle/capture, "SALE" = authorize + automatic settle/capture)
		$order['amount']['currencyCode'] = $this->datos_usuario['divisa']; //Payment currency ('AED' only for now)
		$order['amount']['value'] = $amount; //Minor units (1000 = 10.00 AED)
		$order['language'] = 'en'; // Payment page language ('en' or 'ar' only)
		$order['emailAddress']   = $this->getEmail();
		$order['billingAddress']['firstName'] = htmlentities($this->datos_usuario['apellido']);
		$order['billingAddress']['lastName']  =  htmlentities($this->datos_usuario['nombre']);
		$order['billingAddress']['address1']  = htmlentities($this->datos_usuario['direccion']);
		$order['billingAddress']['city'] = htmlentities($this->datos_usuario['ciudad']);
		$order['billingAddress']['countryCode'] = $this->datos_usuario['iso_pais'];
		$order['merchantOrderReference'] = $transreference;   //token ensip
		$order['merchantAttributes']['redirectUrl'] = $urlok; //https:quebola.es/test/network/pps.php
		$order['merchantAttributes']['cancelUrl']   = $urlko; //https:quebola.es/test/network/pps.php
		$order['merchantAttributes']['skipConfirmationPage'] = true;

		$order['merchantAttributes']['skip3DS'] = true;
		if ($force_3ds) {
			$order['merchantAttributes']['skip3DS'] = true;
		}
		
		$order['merchantAttributes']['cancelText']  = 'Cancelar pago';

		$order['merchantDefinedData']['user_id'] = $this->datos_usuario['usuario_id'];
		$order['merchantDefinedData']['currency'] = $this->datos_usuario['divisa'];
		$order['merchantDefinedData']['token'] = $transreference;
		$order['merchantDefinedData']['epochtime'] = time().rand(1000,9999);
		$order['merchantDefinedData']['servicio'] = base64_encode($this->service);
		$order['merchantDefinedData']['plataforma'] = PayTpvDubaiVars::NETWORK_TOKEN_CALLBACK;

		//syslog (LOG_INFO, __FILE__ . ':'.__method__ . ':order='. serialize($order));

		$json_order = json_encode($order);

		$orderCreateHeaders  = array("Authorization: Bearer ".$access_token, "Content-Type: application/vnd.ni-payment.v2+json", "Accept: application/vnd.ni-payment.v2+json");

		$txn_service_url = $this->get_merchant_value('network_txn_service_url') . $this->outlet . '/orders';
		
		syslog (LOG_INFO, __FILE__ . ':'.__method__ .':txn_service_url:'. $txn_service_url . "($access_token)");

		$response_order = $this->invoke_curl_request("POST", $txn_service_url, $orderCreateHeaders, $json_order);

		$orderCreateResponse = json_decode($response_order);
		syslog (LOG_INFO, __FILE__ . ':'.__method__ . ":order: " . $response_order);
		$result = new \StdClass();

		if (isset($orderCreateResponse->_links)) {
			$payment_link = $orderCreateResponse->_links->payment->href . '&slim=2';
			syslog (LOG_INFO, __FILE__ . ':'.__method__ . ":paymentlink: " . $payment_link);

			$result->RESULT = "OK";
			$result->URL_REDIRECT = $payment_link;
		} else {
			$result->RESULT = "KO";
		}
		
		return $result;
	}
	
	/**
	* Genera la firma en función al tipo de operación para BankStore IFRAME/Fullscreen
	* @param object $operationdata Objeto con los datos de la operación para calcular su firma
	* @param int $operationtype Tipo de operación para generar la firma
	* @return string Hash de la firma calculado
	* @version 1.0 2016-06-06
	*/
	private function GenerateHash($operationdata, $operationtype)
	{
		$hash = false;

		syslog(LOG_INFO, __FILE__.": operationtype: $operationtype");
		syslog(LOG_INFO, __FILE__.": operationdata: ".print_r($operationdata,true));
		$reference = $operationdata->Reference;
		$amount = null;
		if (isset($operationdata->Amount)) $amount = $operationdata->Amount;
		$currency = null;
		if (isset($operationdata->Currency)) $currency = $operationdata->Currency;
		if ($currency == "TEST") $currency = "EUR";
		$iduser = null;
		if (isset($operationdata->IdUser)) $iduser = $operationdata->IdUser;
		$tokenuser = null;
		if (isset($operationdata->TokenUser)) $tokenuser = $operationdata->TokenUser;

		if ((int)$operationtype == 1) {				// Authorization (execute_purchase)
			$hash = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 3) {		// Preauthorization
			$hash = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 6) {		// Confirmación de Preauthorization
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 4) {		// Cancelación de Preauthorization
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 9) {		// Subscription
			$hash = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 107) {		// Add_user
			$hash = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$operationtype.$reference.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 109) {		// execute_purchase_token
			syslog(LOG_INFO, __FILE__." ------------------- operationdata: ".print_r($operationdata, true));
			syslog(LOG_INFO, "check hash merchantCode: $this->paytpv_merchantCode, iduser: $iduser, tokenuser: $tokenuser, terminal: $this->paytpv_terminal, optype: $operationtype, referenca: $reference, amount: $amount, currency: $currency, pass: $this->paytpv_password");
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 110) {		// create_subscription_token
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 111) {		// create_preauthorization_token
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 13) {		// Preauthorization Diferida
			$hash = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$operationtype.$reference.$amount.$currency.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 16) {		// Confirmación de Preauthorization Diferida
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.md5($this->paytpv_password));
		} elseif ((int)$operationtype == 14) {		// Cancelación de Preauthorization Diferida
			$hash = md5($this->paytpv_merchantCode.$iduser.$tokenuser.$this->paytpv_terminal.$operationtype.$reference.$amount.md5($this->paytpv_password));
		}

		return $hash;
	}

	/**
	* Recibe toda la operación completa y la genera para que llegue por GET en la ENDPOINTURL
	* @param object $operationdata Objeto con los datos de la operación para calcular y generar la URL
	* @param int $operationtype Tipo de operación para generar la petición
	* @return string URL para enviar al ENDPOINTURL
	* @version 1.0 2016-06-06
	*/
	private function ComposeURLParams($operationdata, $operationtype)
	{
		$secureurlhash = false;
		$data = array();

		$data["MERCHANT_MERCHANTCODE"]				= $this->paytpv_merchantCode;
		$data["MERCHANT_TERMINAL"]					= $this->paytpv_terminal;
		$data["OPERATION"]							= $operationtype;
		$data["LANGUAGE"]							= $operationdata->Language;
		$data["MERCHANT_MERCHANTSIGNATURE"]			= $operationdata->Hash;
		if (isset($operationdata->UrlOk)) $data["URLOK"]								= $operationdata->UrlOk;
		if (isset($operationdata->UrlKo)) $data["URLKO"]								= $operationdata->UrlKo;
		$data["MERCHANT_ORDER"]						= $operationdata->Reference;
		if (isset($operationdata->Secure3D) && $operationdata->Secure3D != false) {
			$data["3DSECURE"]						= $operationdata->Secure3D;
		}
		//syslog(LOG_INFO, __FILE__.": operationdata->Amount: $operationdata->Currency");
		if (isset($operationdata->Amount)) {
			$data["MERCHANT_AMOUNT"]					= $operationdata->Amount;
		}
		if (isset($operationdata->Concept) && $operationdata->Concept != "") {
			$data["MERCHANT_PRODUCTDESCRIPTION"]	= $operationdata->Concept;
		}

		if ((int)$operationtype == 1) {					// Authorization (execute_purchase)
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			if (isset($oprationdata->Scoring))
				$data["MERCHANT_SCORING"]			= $operationdata->Scoring;
		} elseif ((int)$operationtype == 3) {			// Preauthorization
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			$data["MERCHANT_SCORING"]				= $operationdata->Scoring;
		} elseif ((int)$operationtype == 6) {			// Confirmación de Preauthorization
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
		} elseif ((int)$operationtype == 4) {			// Cancelación de Preauthorization
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
		} elseif ((int)$operationtype == 9) {			// Subscription
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			$data["SUBSCRIPTION_STARTDATE"]			= $operationdata->StartDate;
			$data["SUBSCRIPTION_ENDDATE"]			= $operationdata->EndDate;
			$data["SUBSCRIPTION_PERIODICITY"]		= $operationdata->Periodicity;
			$data["MERCHANT_SCORING"]				= $operationdata->Scoring;
		} elseif ((int)$operationtype == 109) {			// execute_purchase_token
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			if (isset($data["MERCHANT_SCORING"])) $data["MERCHANT_SCORING"]				= $operationdata->Scoring;
		} elseif ((int)$operationtype == 110) {			// create_subscription_token
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			$data["SUBSCRIPTION_STARTDATE"]			= $operationdata->StartDate;
			$data["SUBSCRIPTION_ENDDATE"]			= $operationdata->EndDate;
			$data["SUBSCRIPTION_PERIODICITY"]		= $operationdata->Periodicity;
			$data["MERCHANT_SCORING"]				= $operationdata->Scoring;
		} elseif ((int)$operationtype == 111) {			// create_preauthorization_token
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
			$data["MERCHANT_SCORING"]				= $operationdata->Scoring;
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
		} elseif ((int)$operationtype == 13) {			// Deferred Preauthorization
			$data["MERCHANT_CURRENCY"]				= $operationdata->Currency;
			$data["MERCHANT_SCORING"]				= $operationdata->Scoring;
		} elseif ((int)$operationtype == 16) {			// Deferred Confirmación de Preauthorization
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
		} elseif ((int)$operationtype == 14) {			// Deferred  Cancelación de Preauthorization
			$data["IDUSER"]							= $operationdata->IdUser;
			$data["TOKEN_USER"]						= $operationdata->TokenUser;
		}

		if (isset($data["MERCHANT_CURRENCY"]) && $data["MERCHANT_CURRENCY"] == "TEST") {
			$data["MERCHANT_CURRENCY"] = "EUR";
			//syslog(LOG_INFO, __FILE__.": MERCHANT_CURRENCY: ".$data["MERCHANT_CURRENCY"]);
		}

		$content = "";
		foreach($data as $key => $value)
		{
			if($content != "") $content.="&";
			$content .= urlencode($key)."=".urlencode($value);
		}

		$data["VHASH"] = hash('sha512', md5($content.md5($this->paytpv_password)));
		krsort($data);

		$secureurlhash = "";
		foreach($data as $key => $value)
		{
			if($secureurlhash != "") $secureurlhash.="&";
			$secureurlhash .= urlencode($key)."=".urlencode($value);
		}

		return $secureurlhash;
	}

	/**
	 * TODO : cambiar funcion
	* Comprueba si la URL generada con la operativa deseada genera un error
	* @param string $peticion La URL con la petición a PAYTPV.
	* @return array $response Array con la respuesta. Si hay un error devuelve el error que ha generado, si es OK el value DS_ERROR_ID irá a 0.
	* @version 1.0 2016-06-06
	*/
	private function CheckUrlError($urlgen)
	{
		$response = array("DS_ERROR_ID" => 1023);

		if ($urlgen != "") {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endpointurl.$urlgen); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);

			//syslog(LOG_INFO, __FILE__.": output CheckUrlError: $output");

			if($errno = curl_errno($ch)) {
				$response = array("DS_ERROR_ID" => 1021);
			} else {
				if ((strpos($output, "Error: ") == 0 && strpos($output, "Error: ") !== false) || (strpos($output, "<!-- Error: ") == 0 && strpos($output, "<!-- Error: ") !== false)) {
					$response = array("DS_ERROR_ID" => (int)str_replace(array("<!-- Error: ", "Error: ", " -->"), "", $output));
				} else {
					$response = array("DS_ERROR_ID" => 0);
				}
			}

			curl_close($ch);
		}

		return $response;
	}

	/**
	* Crea una respuesta del servicio PAYTPV BankStore en objeto
	* @param array $respuesta Array de la respuesta a ser convertida a objeto
	* @return object Objeto de respuesta. Se incluye el valor RESULT (OK para correcto y KO incorrecto)
	* @version 1.0 2016-06-03
	*/
	private function SendResponse($respuesta = false)
	{
		$result = new \stdClass();
		if (!is_array($respuesta)) {
			$result->RESULT = "KO";
			$result->DS_ERROR_ID = 1011; // No se pudo conectar con el host
		} else {
			$result = (object)$respuesta;
			if ($respuesta["DS_ERROR_ID"] != "" && $respuesta["DS_ERROR_ID"] != 0) {
				$result->RESULT = "KO";
			} else {
				$result->RESULT = "OK";
			}
		}

		return $result;
	}

	public static function getDivisaFromTerminal($terminal, $Currency = null) {
		$model = new PayTpvApi();
		return $model->_getDivisaFromTerminal($terminal, $Currency = null);
	}
	private function getEmail() {
		if (!empty($this->datos_usuario['email'])) {
			$email = $this->datos_usuario['email'];
		} else {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyz'; 
			$tld = array("com", "net"); 
			$randomName = ''; 
			for($j = 0; $j <= 6; $j++){
				$randomName .= $characters[rand(0, strlen($characters) -1)];
			}
			$k = array_rand($tld); 
			$extension = $tld[$k]; 
			$email = $randomName . "@" ."mail.".$extension; 
		}
		syslog(LOG_INFO, __FILE__ . ':email:'.serialize($this->datos_usuario['email']) . '-'.$email);
		return $email;
	}

	public function _getDivisaFromTerminal($terminal, $Currency = null) {
		foreach ($this->terminales as $divisa => $data) {
			//$this->log("divisa: $divisa, terminal: $terminal, data: ".print_r($data, true));
			if ($data['terminal'] == $terminal) {
				if ($Currency and preg_match("/TEST/", $data['terminal'])) {
					return $divisa;
				}
				if ($Currency != $divisa) {
					// TPV no test con Currency especificado, pero no coincidente
					continue;
				}
				// Devolvemos divisa TPV como antes de filtro $Currency
				return $divisa;
			}
		}
		return null;
	}

	public function checkSignature($TransactionType = "", $TransactionName = "", $CardCountry = "", $BankDateTime = "", $Signature = "", $Order = "", $Response = "", $ErrorID = "", $ErrorDescription = "", $AuthCode = "", $Currency = "", $Amount = "", $AmountEur = "", $Language = "", $AccountCode = "", $TpvID = "", $Concept = "", $ExtendedSignature = "", $IdUser = "", $TokenUser = "", $SecurePayment = "", $CardBrand = "", $BicCode = "", $sepaCard = "", $cardType = "", $cardCategory = "") {
		if ($TransactionType == 107) {	// Notificación llamada a URL (para add_user)
			$calculated_signature = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$TransactionType.$Order.$BankDateTime.md5($this->paytpv_password));
			if ($Signature != $calculated_signature and $ExtendedSignature != $calculated_signature) {
				// Buscar password del terminal
				$md5_passwd = null;
				foreach ($this->terminales_serv as $serv) {
					foreach ($serv as $divisa => $d) {
						if ($d['merchantCode'] == $AccountCode and $d['terminal'] == $TpvID) {
							$md5_passwd = md5($d['password']);
						}
						if ($md5_passwd) break;
						if (isset($d['terminal_w']) and isset($d['password_w'])) {
							if ($d['merchantCode'] == $AccountCode and $d['terminal_w'] == $TpvID) {
								$md5_passwd = md5($d['password_w']);
								syslog(LOG_INFO, __FILE__.": Encontrado terminal $TpvID, md5_pwd: $md5_passwd");
							}
						}
					}
					if ($md5_passwd) break;
				}
				$calculated_signature = md5($AccountCode.$TpvID.$TransactionType.$Order.$Amount.$Currency.$md5_passwd.$BankDateTime.$Response);
				//md5($account_code.$tpvid.$transaction_type.$order.$amount.$currency.$md5_password.$bankdatetime.$response);
			}
			if ($Signature != $calculated_signature and $ExtendedSignature != $calculated_signature) {
				syslog(LOG_INFO, __FILE__.": Notificación con firma incorrecta p1 ($Order)");
				return false;
			} else {
				syslog(LOG_INFO, __FILE__.": Notificación con firma Ok ($Order)");
				return true;
			}
		} else if ($TransactionType == 1) {
			$calculated_signature = md5($this->paytpv_merchantCode.$this->paytpv_terminal.$TransactionType.$Order.$Amount.$Currency.md5($this->paytpv_password));
			if ($Signature != $calculated_signature and $ExtendedSignature != $calculated_signature) {
				// Buscar password del terminal
				$md5_passwd = null;
				foreach ($this->terminales_serv as $serv) {
					foreach ($serv as $divisa => $d) {
						syslog(LOG_INFO, __FILE__.": merchantcode: ".$d['merchantCode'].", accountcode: $AccountCode".", terminal: ".$d['terminal'].", tpvid: $TpvID");
						if ($d['merchantCode'] == $AccountCode and $d['terminal'] == $TpvID) {
							$md5_passwd = md5($d['password']);
							syslog(LOG_INFO, __FILE__.": Encontrado terminal $TpvID, md5_pwd: $md5_passwd");
						}
						if ($md5_passwd) break;
						if (isset($d['terminal_w']) and isset($d['password_w'])) {
							if ($d['merchantCode'] == $AccountCode and $d['terminal_w'] == $TpvID) {
								$md5_passwd = md5($d['password_w']);
								syslog(LOG_INFO, __FILE__.": Encontrado terminal $TpvID, md5_pwd: $md5_passwd");
							}
						}
					}
					if ($md5_passwd) break;
				}
				$calculated_signature = md5($AccountCode.$TpvID.$TransactionType.$Order.$Amount.$Currency.$md5_passwd.$BankDateTime.$Response);
				//md5($account_code.$tpvid.$transaction_type.$order.$amount.$currency.$md5_password.$bankdatetime.$response);
			}
			if ($Signature != $calculated_signature and $ExtendedSignature != $calculated_signature) {
				syslog(LOG_INFO, __FILE__.": Notificación con firma incorrecta p2 ($Order): AccountCode: $AccountCode, TpvID: $TpvID, TransactionType: $TransactionType, Order: $Order, Amount: $Amount, Currency: $Currency, md5_passwd: $md5_passwd, BankDateTime: $BankDateTime, Response: $Response");
				return false;
			} else {
				syslog(LOG_INFO, __FILE__.": Notificación con firma Ok ($Order)");
				return true;
			}
		} else {
			syslog(LOG_INFO, __FILE__.": Notificación tipo desconocido $TransactionType (Añadir en models/PayTpvApi.php)");
		}
		// Añadir otros casos cuando se requiera
	}
	
	private function set_data_usuario() {

		$user = $_POST;

		if ($user) {
			syslog (LOG_INFO, __FILE__ . ':'.__method__ . ':'.$this->usuario . ':'. print_r($user['id'], true));
			$this->datos_usuario['usuario_id'] = $user['id'];
			$this->datos_usuario['email'] = $user['email'];
			$this->datos_usuario['iso_pais'] = $user['pais'];
			$this->datos_usuario['ciudad'] = $user['ciudad'];
			$this->datos_usuario['nombre'] = $user['name'];
			$this->datos_usuario['apellido'] = $user['apellido'];
			$this->datos_usuario['direccion'] = $user['direccion'];
			$this->datos_usuario['divisa'] = ($user['id_divisa'] == 1 ? 'EUR' : ($user['id_divisa'] == 2 ? 'USD' : 'EUR'));
		}
	}

	public function log($texto, $debug = false, $deep = 1) {
		if (is_array($texto) or is_object($texto)) {
			//$texto = preg_replace("/\n/", " | ", print_r($texto, true));
			$texto = serialize($texto);
		}

		if ($deep) {
			if (!preg_match("/^[0-9]+$/", $deep)) {
				$deep = 1;
			}
		} else {
			$deep = 0;
		}

		$texto_extra = "";

		$ip_cliente = $this->ip;
		if ($ip_cliente) {
			$texto_extra .= "$ip_cliente - ";
		}

		if (false && isset($_COOKIE) and count($_COOKIE) and isset($_COOKIE['cbn_sid']) && $_COOKIE['cbn_sid']) {
			$texto_extra .= substr($_COOKIE['cbn_sid'], 0, 6)." - ";
		}

		$texto = $texto_extra.$texto;

		if ($this->log_activado or $debug) {
			$bt = \debug_backtrace();
			for ($i = 1; $i <= $deep; $i++) {
				$caller = array_shift($bt);

				// en BBDD
				/*
				if ($i == 1 && $this->log_bbdd_activado) {
					$sql = "insert into cbn_log(fecha, called_from, txt_mini, txt) values (now(), '".$caller['file'].":".$caller['line']."', '".$this->dbconn->escapar($texto)."', '".$this->dbconn->escapar($texto)."')";
					$this->dbconn->query($sql);
				}
				*/

				if ($caller) {
					if (isset($caller['file'])) {
						if ($i == 1) {
							syslog(LOG_INFO, $caller['file'].":".$caller['line']."($deep): $texto");
						} else {
							syslog(LOG_INFO, "- from: ".$caller['file'].":".$caller['line']);
						}
					} else {
						//syslog(LOG_INFO, print_r($caller, true));
						break;
					}
				} else {
					break;
				}
			}

		}
	}
	
	private function set_divisa($currency) {

		if (isset($this->datos_usuario['divisa']) && $this->datos_usuario['divisa'] != $currency) {
			if ($this->datos_usuario['usuario_id'] == 0){
				$this->datos_usuario['divisa'] = $currency;
			} else {
				$currency = $this->datos_usuario['divisa'];
			}
		}
		syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ':'.$currency . ':'.print_r($this->datos_usuario, true));
		$this->divisa = $currency;
	}

	public function getTest() {
		return $this->test;
	}
	public function getIframePago($form_model, $servicio, $usuario) {
	
		$form_model->usuario = $this->usuario;
		//$token = $model_tokens->get_new_token();
		$token =  md5(uniqid(mt_rand(),true)); //esto lo he puesto yo
		$order = $this->generateToken($token);

	        $force_3ds = true;
                if ($form_model->divisa == "EUR") {
                        //$force_3ds = true;
                }

		//$response = $this->execute_purchase_url($form_model->cantidad, null, $secure3d);
                $access_token = $this->get_access_token();

		$amount = $form_model->cantidad;

		//$urlok = "https://".$_SERVER['HTTP_HOST'].Url::to(['tpv-dubai-iframe/notificacion-pps/?']);
                //$urlko = "https://".$_SERVER['HTTP_HOST'].Url::to(['tpv-dubai-iframe/notificacion-pps/?']);
		$urlok = "https://".$_SERVER['HTTP_HOST']."/result_pago.php?";
                $urlko = "https://".$_SERVER['HTTP_HOST']."/result_pago.php?";

		$response = $this->ExecutePurchaseUrl($access_token, $order, $amount*100, "ES", $urlok, $urlko, $concept, $idUser, $tokenUser, $force_3ds);
		if (isset($response->RESULT) && $response->RESULT == 'OK') {
                	return $response->URL_REDIRECT;
		}
		return false;
	}
}

