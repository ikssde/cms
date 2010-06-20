<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Kohana_Model {
	
	private $tableName = 'user';
	
	protected $_validationRules = array(
		'username'			=> array
		( 
			'not_empty'		    => NULL,
			'min_length'		=> array(4),
			'max_length'		=> array(32),
			'regex'			    => array('/^[-\pL\pN_.]++$/uD'),
		),
		'password'			    => array
		(
			'not_empty'		    => NULL,
			'min_length'		=> array(5),
			'max_length'		=> array(42),
		),
		'password_repeat'	    => array
		(
			'matches'	    	=> array('password'),
		),
		'email'				    => array
		(
			'not_empty'		    => NULL,
			'min_length'		=> array(4),
			'max_length'		=> array(255),
			'validate::email'	=> NULL,
		),
	);
	
	protected $_validationCallbacks = array
	(
		'username'				=> array('isUsernameUnique'),
		'email'					=> array('isEmailUnique'),
	);
	
	public function validate(&$array)
	{
			$array = Validate::factory($array)
						->rules('password', $this->_validationRules['password'])
						->rules('username', $this->_validationRules['username'])
						->rules('email', $this->_validationRules['email'])
						->rules('password_repeat', $this->_validationRules['password_repeat'])
						->filter('username', 'trim')
						->filter('email', 'trim')
						->filter('password', 'trim')
						->filter('password_repeat', 'trim');
      
            foreach ($this->_validationCallbacks as $field => $callbacks)
            {
		    	foreach ($callbacks as $callback)
		    	{
					$array->callback($field, array($this, $callback));
		    	}
			}
 
		return $array;
	
	}
	
	public function getUser($username, $password)
	{
		return DB::select('user.*', array('user_role.name', 'role'))
				   ->from($this->tableName)
				   ->where('user.username', '=', $username)
		           ->and_where('user.password', '=', $password)
		           ->join('user_role', 'left')
		           ->on('user_role.id', '=', 'user.role_id')
		           ->execute()
		           ->current();
	}

	public function getUserToken($token)
	{
		return DB::select('user.*', array('user_role.name', 'role'))
				   ->from($this->tableName)
				   ->where('token', '=', $token)
				   ->join('user_role', 'left')
		           ->on('user_role.id', '=', 'user.role_id')
		           ->execute()
		           ->current();
	}
	
	public function setToken($id, $token)
	{
		return DB::update($this->tableName)
				 ->value('token', $token)
				 ->where('id', '=', $id)
				 ->execute();
	}
	
	public function isEmailUnique(Validate $array, $field)
	{
		$value = (bool) DB::select()
						   ->from($this->tableName)
						   ->where('email', '=', $array[$field])
				           ->execute()
				           ->current();
				           
		if($value) $array->error($field, 'isEmailUnique', array($array[$field]));
	}
	
	public function isUsernameUnique(Validate $array, $field)
	{
		$value = (bool) DB::select()
						   ->from($this->tableName)
						   ->where('username', '=', $array[$field])
				           ->execute()
				           ->current();
				           
		if($value) $array->error($field, 'isUsernameUnique', array($array[$field]));
	}
	
	public function register($array)
	{
		return DB::insert($this->tableName)
				  ->columns(array('username', 'password', 'email', 'role_id'))
				  ->values(array($array['username'], $array['password'], $array['email'], 1))
				  ->execute();
	}
	
	public function remind($username, $email)
	{
		return (bool) DB::select()
						 ->from($this->tableName)
						 ->where('username', '=', $username)
						 ->and_where('email', '=', $email)
						 ->execute()
						 ->current();

	}
	
}
