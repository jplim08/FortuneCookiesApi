<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Roles extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'roles';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'name',
		'description'
	];

	public function getRoleById($RoleId){
		return $this->getByColumn(['id' => $RoleId],1);
	}

	public function getRoles($params=null) {
		$limit = 10;

		if (isset($params['limit'])) {
			$limit = $params['limit'];
		}

		$mainTable = $this->getTableName();
		if (isset($params['q'])){
			
			$nameLike = $this->likeQuery(['name'], $this->escape($params['q']), 'roles', true);
			$this->_select->where($nameLike);

		}

		return $this->getFinalResponse($limit);
	}

	public function saveEntities($param){

		foreach($param as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, trim($value));
			}
		}

		$savedId = $this->__save();
		
		return $savedId;
	}
}

 