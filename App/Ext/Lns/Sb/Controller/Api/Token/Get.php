<?php
namespace Lns\Sb\Controller\Api\Token;

class Get extends \Lns\Sb\Controller\Controller {
	
		protected $token;
		protected $payload;	
		protected $_deviceToken;
		protected $test_work = 20;
		public function __construct(
			\Of\Http\Url $Url,
			\Of\Std\Message $Message,
			\Lns\Sb\Lib\Session\Session $Session
		){
			parent::__construct($Url,$Message, $Session);
			$this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
			// $this->$test_work = 20;
			
		}
		function generate_combinations(array $data, array &$all = array(), array $group = array(), $value = null, $i = 0,$test_work = 0)
		{
			$keys = array_keys($data);
			if (isset($value) === true) {
				array_push($group, $value);
			}

			if ($i >= count($data)) {

				if(strpos($group[0], 'P') === false){
					$invalid = false;
					$checker = $data[0];
					foreach ($group as $item) {
						$combi = array_search($item,$checker);
						if($combi === false){
							$invalid = true;
							break;
						}else{
							array_splice($checker, $combi,1);
						}
					}
					if(!$invalid){
	
						foreach ($group as $key => $comb) {
							$test_work = $this->compute($comb,$test_work);		
							
							if($test_work < 1 || $test_work > 16){
								$test_work = 0;
								break;
							}	
	
						}
						if($test_work != 0 && !in_array($test_work,$all)){
							// echo $test_work;
							array_push($all, $test_work);
						}
					}
				}
			} else {
				$currentKey     = $keys[$i];
				// var_dump($currentKey);
				$currentElement = $data[$currentKey];
				// var_dump($currentElement);
				foreach ($currentElement as $key => $val) {
					$this->generate_combinations($data, $all, $group, $val, $i + 1);
				}
			}

			return $all;
		}

		
		function create_data ($count,$value){
			$data = [
				'count'=>$count,
				'value'=>$value
			];
			return $data;
		}
		function get_set ($array_module){
			$set =[];
			foreach ($array_module as $key => $value) {
				for ($i=0; $i < $value['count'] ; $i++) { 
					array_push($set,$value['value']);
				}
			}
			return $set;
		}
		function get_2d ($set){
			$set_2d = [];
			for ($i=0; $i < count($set) ; $i++) { 
				$set_2d[$i] = $set; 
			}

			return $set_2d;
		}
		function compute($value,$test_work){
			if($value == 'P1'){
				$test_work -= 2;
			}else if($value == 'P2'){
				$test_work -= 4;
			}else if($value == 'P3'){
				$test_work /= 2;
			}else if($value == 'M1'){
				$test_work += 2;
			}else if($value == 'M2'){
				$test_work += 4;
			}else if($value == 'M3'){
				$test_work *= 2;
			}
			return $test_work;
		}
		
		public function run(){

			$_jwt = $this->_di->get('Lns\Sb\Lib\Token\Validate');
			$isValidToken = $_jwt->setSiteConfig($this->_siteConfig)
			->setLang($this->_lang)
			->validateClientToken($this->_request);

			if($isValidToken['error'] == 0){
				$device_token = $this->getParam('Devicetoken');

				$jwt = $this->_di->make('Lns\Sb\Lib\Token\Jwt');
				$jwt->setIssuer($this->_url->getDomain());
				$jwt->setIssuedAt(time());
				$jwt->setSecret($this->_siteConfig->getData('site_api_secret'));

				if($device_token == 'no-device'){
					/* do nothing here */
					
				} else {
					// $p1 = intval(1);
					// $p2 = intval(1);
					// $p3 = intval(1);
					// $m1 = intval(3);
					// $m2 = intval(2);
					// $m3 = intval(0);
					
					// var_dump('start');
					
					// $array_module=[];
					// $work = 0;
					// $test_work = 0;
					// $set = [];
					// $set_2d = [];
					// if($p1 != 0){
					// 	$data = $this->create_data($p1,'P1');
					// 	array_push($array_module,$data);
					// }
					// if($p2 != 0){
					// 	$data = $this->create_data($p2,'P2');
					// 	array_push($array_module,$data);
					// }
					// if($p3 != 0){
					// 	$data = $this->create_data($p3,'P3');
					// 	array_push($array_module,$data);
					// }
					// if($m1 != 0){
					// 	$data = $this->create_data($m1,'M1');
					// 	array_push($array_module,$data);
					// }
					// if($m2 != 0){
					// 	$data = $this->create_data($m2,'M2');
					// 	array_push($array_module,$data);
					// }
					// if($m3 != 0){
					// 	$data = $this->create_data($m3,'M3');
					// 	array_push($array_module,$data);
					// }
					// $set = $this->get_set($array_module);
					// $set_2d = $this->get_2d($set);
					// $combinations = $this->generate_combinations($set_2d);

					// // var_dump($combinations);

					// // foreach ($combinations as $value) {
					// // 	if($work <= $value){
					// // 		$work = $value;
					// // 	}else{
					// // 		$work = $work;
					// // 	}
					// // }
					// echo max($combinations);
					// die;

					$deviceId = $this->_request->getServer('HTTP_DEVICEID');
					$deviceToken = $this->_request->getServer('HTTP_DEVICETOKEN');
					$platform = $this->_request->getServer('HTTP_PLATFORM');

					if (!$deviceId || !$deviceToken) {
						
						$errorMsg = $this->_lang->getLang('api_invalid_deviceid') . ' or '. $this->_lang->getLang('api_invalid_devicetoken');
						$this->result['message'] = ucfirst($errorMsg);

					} else {

						$device_token = $deviceId . '---' . $deviceToken;
						$savedDevice = $this->_deviceToken->registerDevice($this->_siteConfig->getData('site_api_key'),$device_token, $deviceId, $platform);

						if ($savedDevice) {

							$savedDevice = $savedDevice->getData();
							$jwt->setClaim('key', $savedDevice['api_key']);
							$jwt->setAdditionalSecret($savedDevice['api_secret']);

							$this->result['error'] = 0;
							$this->result['message'] = $this->_lang->getLang('success');
							$this->result['data']['token'] = $jwt->getToken();

						} else {

							$this->result['message'] = $this->_lang->getLang('invalid_request');

						}
					}
				}

			} else {
				
				$this->result['message'] = $isValidToken['message'];
			}

			$this->jsonEncode($this->result);
			die;
		}
	}
