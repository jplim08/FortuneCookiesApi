<?php 
namespace Lns\Sb\Lib\Entity\Db;

use Lns\Sb\Lib\Entity\Db\UserProfile;
use Lns\Sb\Lib\Entity\Db\Contact;
use Lns\Sb\Lib\Entity\Db\Address;
use Lns\Sb\Lib\Entity\Db\Attachments;
use Lns\Sb\Lib\AttachmentType;

class Users extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	const SUPER_ADMIN = 1;
	const ADMIN = 2;

	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	const COLUMNS = [
		'id',
		'password',
		'email',
		'status',
		'created_at',
		'update_at',
		'created_by',
		'update_by',
		'user_role_id',
		'last_login',
		'isDeleted',
		'archive',

	];

	protected $tablename = 'user';
	protected $primaryKey = 'id';

	public $_userProfile;
	public $_roles;
	protected $_session;
	protected $_validator;
	protected $_password;
	protected $result;
	public $_address;
	public $_contact;

	public function __construct(
		\Of\Http\Request $Request
	){
		parent::__construct($Request);
		$this->_userProfile = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
		$this->_address = $this->_di->get('Lns\Sb\Lib\Entity\Db\Address');
		$this->_contact = $this->_di->get('Lns\Sb\Lib\Entity\Db\Contact');
		$this->_roles = $this->_di->get('Lns\Sb\Lib\Entity\Db\Roles');
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

	public function getUsersLists($params) {
		$limit = 10;
		if (isset($params['limit'])) {
			$limit = $params['limit'];
		}
		$mainTable = $this->getTableName();
		$userProfileTb = $this->getTableName('user_profile');
		$where = $mainTable.'.`isDeleted` = 0';
		$this->_select->where($where);
		if (isset($params['q'])){

			$userLike = $this->likeQuery(['email'], $this->escape($params['q']), 'user', true);
			$userProfileTb = $this->getTableName('user_profile');
			
			$roleLike = $this->likeQuery(['name'], $this->escape($params['q']), 'roles', false);
			$this->_select->where($where . " and ((".$roleLike.") or (".$userLike.")) OR CONCAT(TRIM(".$userProfileTb.".`first_name`), ' ', + TRIM(".$userProfileTb.".`last_name`)) LIKE '%" . $this->escape($params['q']). "%'");
		}

		$this->__join('profile_', $mainTable.'.id', 'user_profile', '', 'left', 'user_id', ['first_name','last_name']);
		$this->__join('role_', $mainTable.'.user_role_id', 'roles', '', 'left', 'id', ['name']);

		return $this->getFinalResponse($limit);
	}

	public function saveUserInfo($user_id = NULL){

		$this->result['error'] = 1;
		$postedFormData = $this->_request->getParam();
		
		if($user_id){
			$user = $this->getByColumn(['id' => $postedFormData['id']]);
			if(!$user){
				$this->result['message'] = 'User not found.';
				die;
			}else{
				$_password = $this->_password->generate(); 
				$hashedPassword = $this->_password->setPassword($_password)->getHash();
				$postedFormData['password'] = $hashedPassword;
			}
		}else{
			if($this->getByColumn(['email' => $postedFormData['email']])){
				$this->result['message'] = 'Email already exists.';
				die;
				/* $this->_message->setMessage('Email already exists.', 'danger');
				$this->_url->redirectTo($this->_request->getUrlReferer()); */
			}
		}

		/** EMAIL IS VALIDATED */
		if($this->_validator->validateEmail($postedFormData['email'])){
			
			if($postedFormData['password']){
				$hashedPassword = $this->_password->setPassword($postedFormData['password'])->getHash();
				$postedFormData['password'] = $hashedPassword;
			}

			$postedFormData['user_id'] = $this->saveEntity($postedFormData);
			if($postedFormData['user_id']){
				unset($postedFormData['id']);

				$userProfile = $this->_userProfile;
				$address = $this->_address;
				$contact = $this->_contact;
				if($user_id){
					$userProfile = $this->getUserProfile();
					$address = $this->getAddress();
					$contact = $this->getContact();
				}
				/** insert here: send raw generated password to new user's email */
				$userProfile->saveEntity($postedFormData);
				$address->saveEntity($postedFormData);
				$contact->saveEntity($postedFormData);

				$this->result['error'] = 0;
				$this->result['message'] = 'User successfully saved.';
			} else{
				$this->result['message'] = 'Unable to save data.';
			}
		} else{
			$this->result['message'] = 'Email is invalid.';
		}
		
		return $this->result;

	}

	public function getLoggedInUser(){
		$this->_session = $this->_di->get('\Lns\Sb\Lib\Session\Session');
		$userId = $this->_session->getLogedInUser();
		$user = $this->getByColumn(['id' => $userId]);

		return $user;
	}
	
	public function deleteUser($userId){
		$user = $this->getByColumn(['id' => $userId]);
		if($user){
			return $this->delete([$this->primaryKey => $userId]);
		} else{
			return false;
		}
	}

	public function getUsers(){
		$this->setOrderBy('created_at');
		$this->setIsCache(true);
		$this->setCacheMaxLifeTime(60*60*24*30);
		$users = $this->getFinalResponse();

		/* need to reset cache for the next database call */
		$this->setIsCache(false)->setCacheMaxLifeTime(0);

		return $users;
	}

	public function getUserProfile($returnArray = false){
		if($this->getData('id')){
			$model = $this->_userProfile->getByColumn(['user_id' => $this->getData('id')]);
			if($model){
				if($returnArray){
					return $model->getData();
				} else{
					return $model;
				}
			} else{
				return null;
			}
		} else{
			return null;
		}
	}

	public function getRole($returnArray = false){
		if($this->getData('id')){
			$model = $this->_roles->getByColumn(['id' => $this->getData('user_role_id')]);
			if($model){
				if($returnArray){
					return $model->getData();
				} else{
					return $model;
				}
			} else{
				return null;
			}
		} else{
			return null;
		}
	}

	public function getCompleteUserInfo($returnArray = false){

		if($this->getData('id')){
			$_userProfile = $this->getUserProfile();
			return [
				'user' => $returnArray ? $this->getData() : $this,
				'user_profile' => $returnArray ? $_userProfile->getData() : $_userProfile,
				'address' => $_userProfile->getAddress($returnArray),
				'contact' => $_userProfile->getContact($returnArray),
				'role' => $this->getRole($returnArray),
				'profile_picture' => $_userProfile->getProfilePicture($returnArray),
			];
		} else{
			return null;
		}
	}

	public function getUserById($userId){	
		
		$mainTable = $this->getTableName();
		$this->__join('profile_', $mainTable.'.id', 'user_profile', '', 'left', 'user_id', UserProfile::COLUMNS);
 
		$this->__join('address_', $mainTable.'.id', 'address', '', 'left', 'profile_id', Address::COLUMNS);
 
		$this->__join('contact_', $mainTable.'.id', 'contact', '', 'left', 'profile_id', Contact::COLUMNS);
 
		$this->__join('attachments_', $mainTable.'.id', 'attachments', '', 'left', 'profile_id', Attachments::COLUMNS);
 
 		$user = $this->getByColumn([$mainTable.'.id' => $userId], 1, null, false);
 		if($user){
 			return $user->getData();
 		}
	} 

	public function saveUser($userData){

		foreach($userData as $key => $value){
			if(in_array($key, self::COLUMNS) && isset($value)){
				$this->setData($key, $value);
			}
		}
		$savedUserId = $this->__save();
		return $savedUserId;
	}

	public function getUsersByRole($role_id){
		$this->resetQuery();
		$this->_select->where("user_role_id = $role_id");

		return $this->getCollection();
	}
}


/** 
 * 	REUSABLE FUNCTIONS FOR CRUD
 */

/* public function deleteUser($userId){
	$user = $this->getByColumn(['id' => $userId]);
	if($user){
		return $this->delete([$this->primaryKey => $userId]);
	} else{
		return false;
	}
} */

/* public function getUsers(){
	$this->setOrderBy('created_at');
	$this->setIsCache(true);
	$this->setCacheMaxLifeTime(60*60*24*30);
	$users = $this->getFinalResponse(5);

	// need to reset cache for the next database call
	$this->setIsCache(false)->setCacheMaxLifeTime(0);

	return $users;
} */



/** 
 * 	END: REUSABLE FUNCTIONS FOR CRUD
 */
