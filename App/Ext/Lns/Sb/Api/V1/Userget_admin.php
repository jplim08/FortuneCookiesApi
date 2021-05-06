<?php
namespace Lns\Sb\Api\V1;

class Userget_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Controller\Api\AllFunction $AllFunction,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
        $this->_global = $AllFunction;
		$this->_userModel = $Users;
        $this->_lang = $Lang;
	}
    public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = $this->_lang->getLang('login_no');

        if (isset($payload['payload']['jti'])) {
            
            if ($params->getParam('user_id')) {
                
                $data = $this->_userModel->getUserById($params->getParam('user_id'));

                if ($data) {

                    unset($data['password']);
                    $this->jsonData['user_info'] = $data;
                    $this->jsonData['user_info']['profile_pic_url'] = $this->_global->getProfileImageUrlCondition($data['id']);
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  'success';

                } else {

                    $this->jsonData['message'] =  'No data found!';

                }

            } else {

                $data = $this->_userModel->getUsersLists($params->getParam());
                
                if ($data) {

                    $this->jsonData = $data;
                    $dataResults = [];

                    foreach ($data['datas'] as $key => $value) {

                        $dataResults[] = $value->getData();
                        unset($dataResults[$key]['password']);

                    }
                    
                    $this->jsonData['datas'] = $dataResults;
                }

                $this->jsonData['error'] = 0;
                $this->jsonData['message'] =  'success';
                
            }

        }
        
        return $this->jsonData;
    }
}