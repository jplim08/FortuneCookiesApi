<?php
namespace Lns\Sb\Api\V1;

class Saveaccount_admin {
	
	protected $token;
    protected $payload;
    protected $_upload;
	
	public function __construct(
        \Of\Http\Request $Request,
        \Of\Std\Upload $Upload,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\DeviceToken $DeviceToken,
        \Lns\Sb\Lib\Password\Password $Password,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Lns\Sb\Lib\Entity\Db\Address $Address,
        \Lns\Sb\Lib\Entity\Db\Activation $Activation,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_req = $Request;
        $this->_upload = $Upload;
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
        $this->jsonData['message'] = $this->_lang->getLang('login_no');
        
        $signupForm = $params->getParam();
        if (isset($payload['payload']['jti'])) {

            if ($params->getParam('id')) {

                $userId = $this->register($signupForm, $payload);
                $this->jsonData['error'] = 0;
                $this->jsonData['message'] = "Account saved!";

            } else {

                $userExist = $this->_userModel->getByColumn(['email' => $params->getParam('email')], 1);
    
                if($userExist){
                    $this->jsonData['message'] = $this->_lang->getLang('email_exists');
                } else if($userExist){
    
                    $userId = $this->register($signupForm, $payload);
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = "Account saved!";
                }
                
            }
            

        }
        
		return $this->jsonData;
    }
    
    public function register($signupForm, $payload){

        $oldPic = null;
        if (isset($signupForm['password'])){

            $signupForm['password'] = $this->Password->setPassword($signupForm['password'])->getHash();
            
        }

        if (!isset($signupForm['id'])) {
            $signupForm['unique_id'] = $this->_userProfile->generateUniqueId();
        }

        $userId = $this->_userModel->saveEntities($signupForm);
        $signupForm['user_id'] = $userId;
        
        $checkIfProfileExist = $this->_userProfile->getByColumn(['user_id' => $userId],1);

        if ($checkIfProfileExist && $checkIfProfileExist->getData('profile_pic')) {
            $oldPic = $checkIfProfileExist->getData('profile_pic');
        }

        $profilepic = $this->upload($oldPic,$userId);
        if($profilepic){
            $signupForm['profile_pic'] = $profilepic;
        }
        
        $userId = $this->_userProfile->saveUserProfile($signupForm);
        $signupForm['profile_id'] = $userId;
        $this->_contact->saveUserContact($signupForm);
        $this->_address->saveUserAddress($signupForm);
        
        if (!isset($signupForm['id'])) {
            if($userId){
                $tokenInDb = $this->_deviceToken->getByColumn([
                    'token' => $payload['devicetoken'],
                    'api_key' => $payload['payload']['key'],
                ], 1);

                if($tokenInDb){
                    $this->_deviceToken->saveToken($userId,$tokenInDb->getData());
                }
                $this->_activation->saveActivationCode($userId);
                return $userId;
            } else {
                $this->jsonData['message'] = "Failed to register!";
            }
        }
        
    }

    protected function upload($oldPic,$userId){
        $path = 'Lns'.DS.'Sb'.DS.'View'.DS.'images'.DS.'uploads'.DS.'profilepic'.DS.$userId;

        $file = $this->_req->getFile('attachment');
        $fileName = null;
        if($file){
            $_file = $this->_upload->setFile($file)
            ->setPath($path)
            ->setAcceptedFile(['ico','jpg','png','jpeg'])
            ->save();

            if($_file['error'] == 0){
                if($oldPic){
                    $t = ROOT.DS.'App'.DS.'Ext'.DS.$path.DS.$oldPic;
                    
                    if(file_exists($t) && is_file($t)){
                        unlink($t);
                    }
                }
                $fileName = $_file['file']['newName'] . '.' . $_file['file']['ext'];
            }   
        }
        return $fileName;
    }
}