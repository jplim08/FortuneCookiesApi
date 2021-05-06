<?php
namespace Lns\Sb\Controller\Admin\Settings\Action;

class Editlang extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Save Site Settings';

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Lns\Sb\Lib\Session\Session $Session,
		\Of\Std\Upload $Upload
	){
		parent::__construct($Url,$Message,$Session);
		$this->_upload = $Upload;
	}

	public function run(){
		$this->requireLogin();

		$canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage($this->_lang->getLang('settings_save_error'), 'danger');
			$this->_url->redirect('');
		}

		echo '<pre>';
		print_r($this->getAllLang());
		die;
	}

	public function getAllLang(){
		return $this->_lang->setLang('en')->getAll();
	}
}
?>