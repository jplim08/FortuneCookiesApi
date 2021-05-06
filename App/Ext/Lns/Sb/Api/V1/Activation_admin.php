<?php
namespace Lns\Sb\Api\V1;

class Activation_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\Activation $Activation,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_deviceToken = $DeviceToken;
        $this->_userModel = $Users;
        $this->_activation = $Activation;
        $this->_lang = $Lang;
	}	

	public function runFunction($params, $payload){

        $email = $params->getParam('email');
        $action = $params->getParam('action');

        $this->jsonData['error'] = 1;

        if ($email) {
            $userData = $this->_userModel->getByColumn(['email' => $email], 1);
            if ($userData) {
                $tokenInDb = $this->_deviceToken->getByColumn([
                    'token' => $payload['devicetoken'],
                    'api_key' => $payload['payload']['key'],
                ], 1);
                if ($tokenInDb) {
                    if ($action == 'resend') {
                        $this->_activation->updateActivationCode($userData->getData('id'));
                        $this->jsonData['error'] = 0;
                        $this->jsonData['title'] = 'Sent';
                        $this->jsonData['message'] = $this->_lang->getLang('account_resent_activation');
                    } else if ($action == 'activate') {
                        $activation_code = $params->getParam('activation_code');
                        $validate = $this->_activation->validateActivationCode($userData->getData('id'), $activation_code);
                        if ($validate) {
                            $userData->setData('status', 1)->__save();
                            $this->jsonData['error'] = 0;
                            $this->jsonData['title'] = 'Success';
                            $this->jsonData['message'] = $this->_lang->getLang('account_activated');
                        } else {
                            $this->jsonData['error'] = 2;
                            $this->jsonData['title'] = 'Failed';
                            $this->jsonData['message'] = $this->_lang->getLang('account_activation_code_invalid');
                        }
                    }
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
                }
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('email_not_found');
            } 
        } else {
            $this->jsonData['message'] = 'No email parameter found!';
        }
        return $this->jsonData;
    }
}