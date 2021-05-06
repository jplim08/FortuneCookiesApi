<?php
namespace Lns\Sb\Controller\Admin\Settings\Firebase\Action;

class Send extends \Lns\Sb\Controller\Controller {
	
	protected $pageTitle = 'Send Test';
    protected $_cfs;

	public function __construct(
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\CloudFirestore\CloudFirestore $CloudFirestore
	){
        parent::__construct($Url,$Message,$Session);
        $this->_cfs = $CloudFirestore;
	}

	public function run(){
		$this->requireLogin();

        $send = $this->_cfs->setData($this->getParam(), 'test');

        if (isset($send->error)) {
            $this->_message->setMessage($send->error->message, 'danger');
        } else {
            $this->_message->setMessage($this->_lang->getLang('firebase_send_success'), 'success');
        }
        $this->_url->redirect('/settings/firebase');
        die;
        /* echo "<pre>";

        var_dump($send);die; */
		/* $canUpdate = $this->checkPermission('MANAGESITESETTINGS', \Lns\Sb\Lib\Permission::UPDATE);
		if(!$canUpdate){
			$this->_message->setMessage($this->_lang->getLang('settings_firebase_save_error'), 'danger');
			$this->_url->redirect('');
		}
		if(!$this->getPost('isAjax')){
			$this->_siteConfig->setDatas($this->getPost())->save();

			$this->_message->setMessage($this->_lang->getLang('settings_firebase_save_success'));
			$this->_url->redirect('/settings/firebase');
		} */
	}
}
?>