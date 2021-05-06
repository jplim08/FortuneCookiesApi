<?php
namespace Lns\Sb\Api\V1;

class Sociallogin_admin {
	
	public function __construct(
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Entity\Db\SocialLogin $SocialLogin,
        \Lns\Sb\Api\V1\Register $Register,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_global = $AllFunction;
		$this->_userModel = $Users;
		$this->_userProfile = $UserProfile;
        $this->_deviceToken = $DeviceToken;
        $this->_social = $SocialLogin;
        $this->_register = $Register;
        $this->_lang = $Lang;
	}	

	public function runFunction($params, $payload){

        $this->jsonData['error'] = 1;

        $loginForm = $params->getParam();

        $email = $params->getParam('email');
        $tokenInDb = $this->_deviceToken->getByColumn([
            'token' => $payload['devicetoken'],
            'api_key' => $payload['payload']['key'],
        ], 1);

        if($email){
            $userExist = $this->_userModel->getByColumn(['email' => $email], 1);
            
            if($userExist){
                if(!$userExist->getData('password')){
                    $this->_deviceToken->saveToken($userExist->getData('id'),$tokenInDb->getData());
                    $token = $this->_global->updatejwt($userExist->getData('id'),$email,$tokenInDb);
                    $response =  $this->_global->reconstruct($this->_userModel->getUserById($userExist->getData('id')));
                    $response['profile_pic_url'] = $this->_global->getProfileImageUrlCondition($userExist->getData('id'));
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $this->_lang->getLang('login_success');
                    $this->jsonData['data'] = $response;
                    $this->jsonData['token'] = $token['token'];
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('email_exists');
                }
            } else {
                $userId = $this->_register->register('', $params->getParam(), $payload);
                $social_details = array(
                    'type' => $params->getParam('type'),
                    'social_id' => $params->getParam('social_id'),
                    'email' => $params->getParam('email'),
                    'name' => $params->getParam('name'),
                    'first_name' => $params->getParam('first_name'),
                    'last_name' => $params->getParam('last_name'),
                    'image_url' => $params->getParam('image_url'),
                );
                $loginForm['user_id'] = $userId;
                $loginForm['profile_pic'] = $params->getParam('type');
                $loginForm['social_details'] = json_encode($social_details);

                $this->_social->saveSocialAccount($loginForm);
                
                $useProfileId = $this->_userProfile->getByColumn(['user_id' => $userId], 1);
                $userProfile = $loginForm;
                $userProfile['id'] = $useProfileId->getData('id');
                $this->_userProfile->saveUserProfile($userProfile);
                /* $this->_deviceToken->saveToken($userId,$tokenInDb->getData()); */
                $token = $this->_global->updatejwt($userId,$email,$tokenInDb);
                $response =  $this->_global->reconstruct($this->_userModel->getUserById($userId));
                $response['profile_pic_url'] = $this->_global->getProfileImageUrlCondition($userId);
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = $this->_lang->getLang('login_success');
                $this->jsonData['data'] = $response;
                $this->jsonData['token'] = $token['token'];
            }
            
        } else {
            $this->jsonData['message'] = $this->_lang->getLang('login_no_email');
        }

        return $this->jsonData;
    }
}