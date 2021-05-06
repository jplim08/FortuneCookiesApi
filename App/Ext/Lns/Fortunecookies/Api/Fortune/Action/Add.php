<?php
namespace Lns\Fortunecookies\Api\Fortune\Action;

class Add extends \Lns\Sb\Controller\Controller{
	protected $_request;
	protected $_password;
	protected $_jwt;
    protected $_url;
    protected $token;
    protected $payload;
    
	protected $_userModel;

	public function __construct(
		\Of\Http\Request $Request,
		\Of\Token\Jwt $Jwt,
		\Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
	){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
		$this->_request = $Request;
		$this->_jwt = $Jwt;
		$this->_url = $Url;

        $this->_fortunes = $this->_di->get('Lns\Fortunecookies\Entity\Fortunes');
	}

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = 'Save Unsuccessful!';

        if($payload['error'] == 1){

            $this->jsonData['message'] = $payload['message'];

        }else{

            $params = $this->getParam();
            $fortune = $this->_fortunes->addFortune($params);
            if($fortune){
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = 'Success';
            }else{
                $this->jsonData['error'] = 1;
                $this->jsonData['message'] = 'Failed';
            }
            
        }

        $this->jsonEncode($this->jsonData);

		die;
    }
}
?>