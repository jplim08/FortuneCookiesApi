<?php
namespace Lns\Sb\Controller\Admin\Settings\Firebase\Action;

class Save extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Firebase Settings';

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session
	){
		parent::__construct($Url,$Message,$Session);
	}

	public function run(){
		$this->requireLogin();

		$canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage($this->_lang->getLang('settings_firebase_save_error'), 'danger');
			$this->_url->redirect('');
		}
		if(!$this->getPost('isAjax')){
			$this->_siteConfig->setDatas($this->getPost())->save();

			$this->_message->setMessage($this->_lang->getLang('settings_firebase_save_success'));
			$this->_url->redirect('/settings/firebase');
		}
	}
}
?>