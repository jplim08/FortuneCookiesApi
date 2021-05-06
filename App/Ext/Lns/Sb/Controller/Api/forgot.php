<?php
namespace Lns\Bc\Controller\Api\User;

use Lns\Sb\Lib\Status;
use Lns\Sb\Lib\Userrole;

class Forgotpassword extends \Lns\Sb\Controller\Controller {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ){
        parent::__construct($Url,$Message,$Session);
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
		$this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
		$this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_schoolModel = $this->_di->get('Lns\Bc\Lib\Entity\Db\School');
        $this->_deviceToken = $this->_di->get('Lns\Sb\Lib\Entity\Db\DeviceToken');
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
        $this->_password = $this->_di->get('Lns\Sb\Lib\Password\Password');
        $this->_mailTemplateEntity = $this->_di->get('Lns\Sb\Lib\Entity\Db\MailTemplate');
        $this->_mailer = $this->_di->get('Lns\Sb\Lib\Mailer');
	}	

	public function run(){
        $payload = $this->token
        ->setLang($this->_lang)
        ->setSiteConfig($this->_siteConfig)
        ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60*3), false)
        ->validate($this->_request);

        $email = $this->getParam('email');
        $type = $this->getParam('type');

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];        
        } else {
            if($type == 1){
                $verifyEmail = $this->_global->validateEmail(2,$email);
                
                if($verifyEmail['error'] == 0){
                    
                    $userdata = $this->_userModel->getByColumn(['email' => $email]);
                    if($userdata->getData('user_role_id') == 2){
                        $userProfileData = $this->_userProfile->getByColumn(['user_id' => $userdata->getData('id')]);
                        $name = $userProfileData->getData('first_name').' '.$userProfileData->getData('last_name');
                    }else if($userdata->getData('user_role_id') == 3){
                        $userProfileData = $this->_userProfile->getByColumn(['user_id' => $userdata->getData('id')]);
                        $schoolData = $this->_schoolModel->getByColumn(['profile_id' => $userProfileData->getData('id')]);
                        $name = $schoolData->getData('name');
                    } else {
                        $this->jsonData['message'] = "Invalid User Role";
                    }
                    
                    $tokenInDb = $this->_deviceToken->getByColumn([
                        'api_key' => $payload['payload']['key']
                    ]);
                    $code = strtoupper(\Lns\SB\Lib\Password\Password::generate(6));
                    $this->jsonData['code'] = $code;

                    $response = $this->_global->addSecretInjwt($verifyEmail['data'],'forgot code', $tokenInDb,$code);
                    $mail = array(
                        "email" => $email,
                        "code" => $code,
                        "token" => $response,
                        "name" => $name,
                    );
                    $this->sendMail($mail,'reset_password'); 
                    $this->jsonData['error'] = 0;
                    $this->jsonData['token'] = $response;
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_sent');
                } else {
                    $this->jsonData= $verifyEmail;
                }
            } else if($type == 2) {
                $code = $this->getParam('code');
                $token = $this->getParam('token');
                
                $tokenParts = explode(".", $token);  
                $tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $jwtHeader = json_decode($tokenHeader);
                $jwtPayload = json_decode($tokenPayload);
                
                if($jwtPayload->data == $code){
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_valid');
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('verification_code_invalid');
                }
            } else if($type == 3) {   
                $token = $this->getParam('token');
                $tokenParts = explode(".", $token);  
                $tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $jwtHeader = json_decode($tokenHeader);
                $jwtPayload = json_decode($tokenPayload);
                
                $checkPassword = $this->_password->confirmPassword($this->getParam('password'),$this->getParam('retype_password'));
                if($checkPassword){

                    $hashedPassword = $this->_password->setPassword($this->getParam('retype_password'))->getHash();
                    $response = $this->_global->changePassword($jwtPayload->jti, $hashedPassword);

                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  $this->_lang->getLang('change_password_success');
                } else {
                    $this->jsonData['message'] = $this->_lang->getLang('password_new_confirm_not_match');
                }
            } else {
                $this->jsonData['message'] = "Invalid type!";
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
    
    public function sendMail($mail, $temp_code){
        $message = 'Reset password link failed to send';
        $emailTpl = $this->_mailTemplateEntity->getByColumn(['template_code' => $temp_code], 1);
			if($emailTpl){
				$tpl = $emailTpl->getData();
	
				$subject = $emailTpl->getData('subject');
				$template = $emailTpl->getData('template');
				$from_name = $emailTpl->getData('from_name');
				$from_email = $emailTpl->getData('email');
				
				
				$mailer = new $this->_mailer;

				$messageBody = $template;
                $messageBody = str_replace('{{email}}', $mail['email'], $messageBody);
                $messageBody = str_replace('{{code}}', $mail['code'], $messageBody);
                $messageBody = str_replace('{{name}}', $mail['name'], $messageBody);
                $messageBody = str_replace('{{token}}', $mail['token'], $messageBody);
				ob_start();
					include(ROOT.DS.'App/Ext/Lns/Sb/View/Template/mail/email_template.phtml');
					$tplHtml = ob_get_contents();
				ob_end_clean();
					
				$mailer->addAddress($mail['email'], '', 'To')
				->setFrom($from_email, $from_name)
				->setSubject($subject)
				->setMessage($tplHtml);
		
				$mailer->send();
				$message = 'Reset password link has been sent';
			}
        
        
        return $message;
    } 
    
    
}


