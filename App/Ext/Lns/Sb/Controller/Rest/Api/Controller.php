<?php
namespace Lns\Sb\Controller\Rest\Api;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Controller extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_ext = $this->_di->get('Lns\Sb\Lib\Entity\Db\Extension');
	}
    public function run()
    {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60 * 3), false)
            ->validate($this->_request);

        $this->jsonData['error'] = 1;

        if ($payload['error'] == 1) {

            $this->jsonData['message'] = $payload['message'];

        } else {
            $version = 'V' . $this->getParam('v');
            $action = ucfirst(strtolower($this->getParam('ac')));

            if ($version) {

                if ($action) {

                    $getExtensions = $this->_ext->getExtensions();
                    foreach ($getExtensions as $key => $value) {

                        $checkIfExist = $this->checkFileExist($version, $action . '.php',$value->getData('extension'));
                        if($checkIfExist) {

                            break;

                        }

                    }

                    if ($checkIfExist) {

                        $path = $version. "\\" .$action;
                        $apiDir = 'Lns\\'. $value->getData('extension').'\\Api\\'. $path ;
                        $this->_api = $this->_di->get($apiDir);
                        $params = $this->getParam();
                        unset($params['ac']);
                        unset($params['v']);
                        $this->jsonData = $this->_api->runFunction($this->_request,$payload);
                        $this->jsonData['used_params'] = $params;
                        unset($this->jsonData['used_params']['password']);

                    } else {

                        $this->jsonData['message'] = 'Api not found!';

                    }

                } else {

                    $this->jsonData['message'] = 'Action not found!';
                }

            } else {

                $this->jsonData['message'] = 'Version not found!';

            }

        }

        $this->jsonEncode($this->jsonData);
        die;
    }

    public function checkFileExist($version, $action, $module) {
        $path = $version . DS . $action;
        $t = ROOT . DS . 'App' . DS . 'Ext' . DS . 'Lns' . DS . $module . DS . 'Api' . DS . $path;

        if (file_exists($t) && is_file($t)) {
            return true;
        }
        
    }
}