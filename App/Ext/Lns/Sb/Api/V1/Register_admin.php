<?php
namespace Lns\Sb\Api\V1;

class Register_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Lns\Sb\Lib\Entity\Db\Address $Address,
        \Lns\Sb\Lib\Entity\Db\Activation $Activation,
        \Lns\Sb\Lib\Lang\Lang $Lang

    ){
		$this->_userModel = $Users;
		$this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->Password = $Password;
        $this->_contact = $Contact;
        $this->_address = $Address;
        $this->_activation = $Activation;
        $this->_lang = $Lang;
	}	

	public function runFunction($params, $payload){

        $this->jsonData['error'] = 1;

        $signUpForm = $params->getParam();

        $hashedPassword = $this->Password->setPassword($params->getParam('password'))->getHash();
        $signUpForm['password'] = $hashedPassword;
        $email = $params->getParam('email');
        $userExist = $this->_userModel->getByColumn(['email' => $email], 1);

        if($userExist){
            $this->jsonData['message'] = $this->_lang->getLang('email_exists');
        } else {
            $userId = $this->register($hashedPassword,$signUpForm,$payload);
            if ($userId) {
                $this->_activation->saveActivationCode($userId);
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = $this->_lang->getLang('account_create');
            }
        }
		return $this->jsonData;
    }
    
    public function register($hashedPassword,$signUpForm,$payload){

        $uniqueId = $this->_userProfile->generateUniqueId();
        $signUpForm['password'] = $hashedPassword;

        /* comment this status to use the activation code */
        $signUpForm['status'] = 0;
        $signUpForm['user_role_id'] = 3;

        $userId = $this->_userModel->saveUser($signUpForm);
        $signUpForm['user_id'] = $userId;
        $signUpForm['unique_id'] = $uniqueId;
        
        $userId = $this->_userProfile->saveUserProfile($signUpForm);
        $signUpForm['profile_id'] = $userId;
        $this->_contact->saveUserContact($signUpForm);
        $this->_address->saveUserAddress($signUpForm);
             
        if($userId){
            $tokenInDb = $this->_deviceToken->getByColumn([
                'token' => $payload['devicetoken'],
                'api_key' => $payload['payload']['key'],
            ], 1);

            if($tokenInDb){
                $this->_deviceToken->saveToken($userId,$tokenInDb->getData());
            }
            return $userId;
        } else {
            $this->jsonData['message'] = "Failed to register!";
        }
    }
}