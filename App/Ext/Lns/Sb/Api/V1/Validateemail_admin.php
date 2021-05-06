<?php
namespace Lns\Sb\Api\V1;

class Validateemail_admin {
	
	public function __construct(
        \Lns\Sb\Controller\Api\AllFunction $AllFunction
    ){
        $this->_global = $AllFunction;
        
    }	
    
	public function runFunction($params, $payload)
    {
        $this->jsonData['error'] = 1;
        $this->jsonData['message'] = 'No email parameter found!';
        
        if ($params->getParam('email')) {
            /* Parameter to pass
            Type (1 = Validate Email , 2 = Forgot password) , Email */
            $response = $this->_global->validateEmail(1, $params->getParam('email'));
            $this->jsonData = $response;
        }

        return $this->jsonData;
    }
}

