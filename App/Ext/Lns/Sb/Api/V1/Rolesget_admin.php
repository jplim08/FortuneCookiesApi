<?php
namespace Lns\Sb\Api\V1;

class Rolesget_admin {

    protected $payload;
	
	public function __construct(
        \Lns\Sb\Lib\Entity\Db\Roles $Roles,
        \Lns\Sb\Lib\Lang\Lang $Lang
    ){
		$this->_roles = $Roles;
        $this->_lang = $Lang;
	}
    public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = $this->_lang->getLang('login_no');

        if (isset($payload['payload']['jti'])) {
            
            if ($params->getParam('role_id')) {

                $data = $this->_roles->getRoleById($params->getParam('role_id'));

                if ($data) {

                    $this->jsonData['role_info'] = $data->getData();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  'success';

                } else {

                    $this->jsonData['message'] =  'No data found!';

                }

            } else {

                $data = $this->_roles->getRoles($params->getParam());
                
                if ($data) {
                    
                    $this->jsonData = $data;
                    $dataResults = [];
                    
                    foreach ($data['datas'] as $key => $value) {
                        
                        $dataResults[] = $value->getData();
                        
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