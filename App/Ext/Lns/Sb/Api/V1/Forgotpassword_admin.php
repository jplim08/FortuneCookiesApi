<?php
namespace Lns\Sb\Api\V1;

class Forgotpassword_admin {
	
	public function __construct(
        \Lns\Sb\Lib\Lang\Lang $Lang,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Entity\Db\Activation $Activation

    ){
        $this->_lang = $Lang;
		$this->_userModel = $Users;
        $this->_deviceToken = $DeviceToken;
        $this->_global = $AllFunction;
        $this->_password = $Password;
        $this->_activation = $Activation;
    }	

	public function runFunction($params, $payload)
    {

        $email = $params->getParam('email');
        $type = $params->getParam('type');
        /* forgot password steps 1-3;
        type: 1 = send to email , 2  = check code , 3 = change password */
        $this->jsonData['error'] = 1;
        if($type == 1){
            /*     Parameter to pass
            type (1 = Validate Email , 2 = Forgot password) , email */
            $verifyEmail = $this->_global->validateEmail(2,$email);

            if($verifyEmail['error'] == 0){
                $tokenInDb = $this->_deviceToken->getByColumn([
                    'api_key' => $payload['payload']['key']
                ]);
                $code = strtoupper(\Lns\SB\Lib\Password\Password::generate(6));
                $this->jsonData['code'] = $code;
                /*  Parameter to pass
                ID , Audience , Payload , New secret */
                $response = $this->_global->addSecretInjwt($verifyEmail['data'],'forgot code', $tokenInDb,$code);
                $mail = array(
                    "email" => $email,
                    "code" => $code,
                );
                $userId = $this->_userModel->getByColumn(['email' => $email], 1)->getData('id');
                $activation = $this->_activation->getByColumn(['user_id' => $userId], 1);

                if (!$activation) {

                    $this->_activation->setDatas([
                        'user_id' => $userId,
                        'activation_code' => $code
                    ])->__save();

                } else {

                    $activation->setData('activation_code', $code)->__save();

                }
                $this->_global->sendMail($mail,'send_code'); 
                $this->jsonData['error'] = 0;
                $this->jsonData['token'] = $response;
                $this->jsonData['message'] = $this->_lang->getLang('verification_code_sent');
            } else {
                $this->jsonData= $verifyEmail;
            }
        } else if($type == 2) {
            $code = $this->getParam('code');
            $userId = $this->_userModel->getByColumn(['email' => $email], 1)->getData('id');
            $validate = $this->_activation->getByColumn(['user_id' => $userId, 'activation_code' => $code], 1);
            if ($validate/* $payload['payload']['data'] == $code */){
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = $this->_lang->getLang('verification_code_valid');
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('verification_code_invalid');
            }
        } else if($type == 3) {
            $checkPassword = $this->_password->confirmPassword($this->getParam('password'),$this->getParam('retype_password'));
            if($checkPassword){

                $hashedPassword = $this->_password->setPassword($this->getParam('retype_password'))->getHash();
                /*  parameter to pass
                User id , password */
                $response = $this->_global->changePassword($payload['payload']['key'],$hashedPassword);

                $this->jsonData['error'] = 0;
                $this->jsonData['message'] =  $this->_lang->getLang('change_password_success');
            } else {
                $this->jsonData['message'] = $this->_lang->getLang('password_new_confirm_not_match');
            }
        } else {
            $this->jsonData['message'] = "Invalid type!";
        }
        return $this->jsonData;
    }
}

