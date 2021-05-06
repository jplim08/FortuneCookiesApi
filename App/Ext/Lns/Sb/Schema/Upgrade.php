<?php
namespace Lns\Sb\Schema;

use Of\Std\Status;

class Upgrade extends \Of\Db\Createtable {
	
	public function upgradeSchema($currentVersion, $newVersion){
		$vc = $this->versionCompare($currentVersion, $newVersion);

		if($vc == 1) {
			/* $tableName = $this->getTablename('test');
			$query = "ALTER TABLE `".$tableName."` ADD `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`;";
			$this->save($query); */
			$this->createActivationTable();
			$this->createEmailTemplateTable();
		}
	}

	private function createActivationTable(){
		$this->setTablename('user_activation_code');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'user_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name'=>'activation_code',
			'type'=> self::_VARCHAR,
			'length' => 6,
			'nullable' => false,
			'comment' => '6 digit activation code',
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	private function createEmailTemplateTable(){
		$this->setTablename('email_templates');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 11,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'template_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'template_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'subject',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'template',
			'type'=> 'LONGTEXT',
			'nullable' => false,
			'comment' => 'email template',
		]);
		$this->addColumn([
			'name'=>'from_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'The name where the email came from',
		]);
		$this->addColumn([
			'name'=>'email',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);

		$this->save();
	}
}
?>