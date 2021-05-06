<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Extension extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    
  protected $tablename = 'extension';
  protected $primaryKey = 'id';
    
	const COLUMNS = [
		'id',
		'vendor',
		'extension',
		'version',
		'created_at',
		'updated_at',
		'status',
	];

	public function getExtensions() {
		$this->resetQuery();
		return $this->getByColumn(['status' => 1], 0);
	}
}
