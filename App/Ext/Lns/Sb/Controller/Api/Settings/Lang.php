<?php
namespace Lns\Sb\Controller\Api\Settings;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Lang extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
	}	
	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $langtype = $this->getParam('type');

            $this->_lang->setLang($langtype);

            $this->jsonData['error'] = 0;
            $this->jsonData['message'] = $this->_lang->getLang('success');
            $this->jsonData['data'] = $this->_lang->getAll();
        }
        $this->jsonEncode($this->jsonData);
		die;
    }
}