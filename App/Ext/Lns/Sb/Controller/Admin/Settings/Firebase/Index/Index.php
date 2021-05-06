<?php
namespace Lns\Sb\Controller\Admin\Settings\Firebase\Index;

class Index extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Firebase Settings';

	public function run(){
		$this->requireLogin();

		$canView = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::VIEW);
		if(!$canView){
			$this->_message->setMessage('Your are not allowed to view firebase settings.', 'danger');
			$this->_url->redirect('');
		}

		return parent::run();
	}
}
?>