<?php
namespace Lns\Sb\Api\V1;

class Login_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_lang = $Lang;
        $this->_global = $AllFunction;
		$this->_userModel = $Users;
        $this->_deviceToken = $DeviceToken;
        $this->Password = $Password;
    }
    
    public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;

        $requestPassword = $params->getParam('password');
        

        if ($params->getParam('email') || $params->getParam('username')) {

            if ($params->getParam('email')) {

                $userExist = $this->_userModel->getByColumn(['email' => $params->getParam('email')], 1);

            } else {

                $userExist = $this->_userModel->getByColumn(['username' => $params->getParam('username')], 1);
                
            }

            if ($userExist) {

                if ($userExist->getData('user_role_id') == 1) {

                    $passwordVerify = $this->Password->setPassword($requestPassword)
                        ->setHash($userExist->getData('password'))->verify();
    
                    if ($passwordVerify) {
                        $tokenInDb = $this->_deviceToken->getByColumn([
                            'token' => $payload['devicetoken'],
                            'api_key' => $payload['payload']['key'],
                        ], 1);
                        if ($tokenInDb) {
                            $this->_deviceToken->saveToken($userExist->getData('id'), $tokenInDb->getData());
                            $userInfo = $this->_userModel->getUserById($userExist->getData('id'));
    
                            if ($userInfo['status'] == 1) {
                                if ($userInfo) {
                                    $userInfo =  $this->_global->reconstruct($userInfo);
                                }
    
                                $response  =  $this->_global->updatejwt($userExist->getData('id'), $userExist->getData('email'), $tokenInDb);
                                $this->jsonData = $response;
                                $this->jsonData['user'] = $userInfo;
                                $this->jsonData['user']['profile_pic_url'] = $this->_global->getProfileImageUrlCondition($userExist->getData('id'));
                            } else {
                                /* $this->_global->sendMail($signupForm,'register'); */
                                /* $this->_activation->updateActivationCode($userExist->getData('id')); */
                                $this->jsonData['error'] = 2;
                                $this->jsonData['message'] = $this->_lang->getLang('account_need_activate');
                            }
                        } else {
                            $this->jsonData['message'] = $this->_lang->getLang('api_invalid_token');
                        }
                    } else {
                        $this->jsonData['message'] = $this->_lang->getLang('password_current_incorrect');
                    }

                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('admin_login_error');
                }
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('email_not_found');
            }
        } else {
            $this->jsonData['message'] = $this->_lang->getLang('login_no_email');
        } 
        return $this->jsonData;
    }
}