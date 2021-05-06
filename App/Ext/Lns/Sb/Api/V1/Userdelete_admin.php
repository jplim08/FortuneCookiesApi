<?php
namespace Lns\Sb\Api\V1;

class Userdelete_admin {
	
	protected $token;
    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
		$this->_userModel = $Users;
        $this->_lang = $Lang;
	}
    public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = $this->_lang->getLang('login_no');

        if (isset($payload['payload']['jti'])) {
            
            if ($params->getParam('userId')) {

                $data = $this->_userModel->getByColumn(['id' => $params->getParam('userId')],1);

                if ($data) {
                    $data->setData('isDeleted', 1)->__save();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  'Account deleted!';
                }

            } else {
                
                $this->jsonData['message'] = 'No userId found!';

            }
            
        }

        return $this->jsonData;
    }
}