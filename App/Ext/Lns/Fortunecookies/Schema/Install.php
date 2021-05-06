<?php

namespace Lns\Fortunecookies\Schema;


class Install extends \Of\Db\Createtable
{

	protected $_fortunes;
	// protected $_apiKeys;

	public function __construct(
		\Of\Std\Versioncompare $Versioncompare,
		\Lns\Fortunecookies\Entity\Fortunes $Fortunes
	) {
		parent::__construct($Versioncompare);
		$this->_fortunes = $Fortunes;
		
	}

	public function createSchema()
	{
		$this->fortunes();
	}
	
	private function fortunes()
	{
		$this->setTablename('fortunes');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name' => 'id',
			'type' => self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
			'comment' => 'id(Primary Key)',
		]);
		$this->addColumn([
			'name' => 'message',
			'type' => self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
			'comment' => 'this message will appear in fortune cookies',
		]);
		$this->addColumn([
			'name' => 'created_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name' => 'updated_at',
			'type' => self::_TIMESTAMP,
			'default' => self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	
}
