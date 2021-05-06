<?php
namespace Lns\Sb\Api\V1;

class Changepassword_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Lang\Lang $Lang

    ){
        $this->token = $Validate;
		$this->_userModel = $Users;
        $this->_deviceToken = $DeviceToken;
        $this->_global = $AllFunction;
        $this->_password = $Password;
        $this->_lang = $Lang;
    }	
    
	public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = $this->_lang->getLang('login_no');

        if (isset($payload['payload']['jti'])) {
            $response = $this->checkPassword($params, $payload['payload']['jti']);
            $this->jsonData = $response;
        }

        $this->jsonEncode($this->jsonData);
        die;
    }
    
    public function checkPassword($params, $id){
        $current_password = $params->getParam('current_password');
        $new_password = $params->getParam('new_password');
        $confirm_password = $thparamsis->getParam('confirm_password');

        $userExist = $this->_userModel->getByColumn(['id' => $id], 1);

        $passwordVerify = $this->_password->setPassword($current_password)
        ->setHash($userExist->getData('password'))->verify();

        if($passwordVerify){
            return $this->changePassword($userExist->getData('id'),$new_password,$confirm_password);
        } else {
            $this->result['message'] = $this->_lang->getLang('password_current_incorrect');    
            return $this->result;
        }
    }

    public function changePassword($id,$new_password,$confirm_password){
        $checkPassword = $this->_password->confirmPassword($new_password,$confirm_password);

        if($checkPassword){
            $hashedPassword = $this->_password->setPassword($confirm_password)->getHash();
            $response = $this->_global->changePassword($id,$hashedPassword);
            $this->result['error'] = 0;
            $this->result['message'] = $this->_lang->getLang('change_password_success');  
        } else {
            $this->result['message'] = $this->_lang->getLang('password_new_confirm_not_match');
        }

        return $this->result;
    }


}

