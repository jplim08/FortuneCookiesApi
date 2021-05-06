<?php
namespace Lns\Sb\Api\V1;

class Rolesdelete_admin {

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

                $data = $this->_roles->getByColumn(['id' => $params->getParam('role_id')],1);

                if ($data) {

                    $data->delete();
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  'Role successfully deleted.';

                } else {
                    
                    $this->jsonData['message'] = 'No data found!';

                }

            } else {
                
                $this->jsonData['message'] = 'No roleId found!';

            }
            
        }
        
        return $this->jsonData;
    }
}