<?php
namespace Lns\Sb\Api\V1;

class Rolesadd_admin {

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
            
            if ($params->getParam('name') && $params->getParam('description')) {

                $savedId = $this->_roles->saveEntities($params->getParam());
                if ($savedId) {
                    $this->jsonData['saved_id'] = $savedId;
                    $this->jsonData['error'] = 0;
                    $this->jsonData['message'] =  'success';
                }

            } else {
                $this->jsonData['message'] =  'Check parameters!';
            }
        }
        return $this->jsonData;
    }
}