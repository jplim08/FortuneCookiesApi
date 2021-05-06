<?php
namespace Lns\Sb\Api\V1;

class Logout_admin {

    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_deviceToken = $DeviceToken;
        $this->_lang = $Lang;
    }

	public function runFunction($params, $payload){
        
        $this->jsonData['error'] = 1;

        /* add this condition if logged user is needed */
        if (isset($payload['payload']['jti'])) {
            $this->_deviceToken->removeToken($payload['payload']['jti']);
            $this->jsonData['message'] = $this->_lang->getLang('logout_yes');
            $this->jsonData['error'] = 0;
        } else {
            $this->jsonData['message'] = $this->_lang->getLang('login_no');
        }

        return $this->jsonData;
    }
}

