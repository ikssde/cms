<?php
/**
 * Authlite library v2.0 Alpha 1
 * 
 * Based on Kohana's Auth library.
 *
 * @author		Fred Wu <fred@wuit.com>
 * @author		ikssde
 * @author		Agares
 * @copyright	Wuit
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class Auth
{	
	/**
	 * Authlite instances
	 *
	 * @var array
	 */
	protected static $instances;
	
	/**
	 * Kohana session object
	 *
	 * @var object
	 */
	protected $session;
	
	/**
	 * Configuration instance name
	 *
	 * @var string
	 */
	protected $config_name;
	
	/**
	 * Kohana config object
	 *
	 * @var object
	 */
	protected $config;
	
	public $model;
	
	protected $user = null;
	
	/**
	 * Create an instance of Authlite.
	 *
	 * @param string $config config file name
	 * @return object
	 */
	public static function factory($config_name = 'auth')
	{
		return new Auth($config_name);
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return object
	 */
	public static function instance($config_name = 'auth')
	{
		// Load the Auth instance
		empty(Auth::$instances[$config_name]) and Auth::$instances[$config_name] = new Auth($config_name);

		return Auth::$instances[$config_name];
	}

	public function __construct($config_name = 'auth')
	{
		$this->session = Session::instance();
		$this->config  = Kohana::config($config_name);
		$this->config_name = $config_name;
		$this->model = Context::getMainInstance() -> get('Model_User');
		
		Kohana_Log::instance()->add('debug', 'Auth Library loaded');
	}

	/**
	 * Check if there is an active session.
	 *
	 * @return object|false|null
	 */
	public function logged_in()
	{		
		$this->user = $this->session->get($this->config['session_key']);
		
		if(!$this->user)
		{
			$token = Cookie::get($this->config['cookie_key']);
			
			if($token)
			{
				$this->user = $this->model->getUserToken($token);
				
				if($this->user)
				{
					$this->proceed(true);
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $this->user;
		}
	}

	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @see self::logged_in()
	 * @return object|false
	 */
	public function get_user()
	{
		return $this->logged_in();
	}

	/**
	 * Attempts to log in a user
	 *
	 * @param string username to log in
	 * @param string password to check against
	 * @param boolean enable auto-login
	 * @return array|false
	 */
	public function login($username, $password, $remember = false)
	{
		if(empty($password) or empty($username))
		{
			return false;
		}
		
		$this->user = $this->model->getUser($username, $this->hash($password));
		$this->proceed($remember);
		
		return $this->user;
	}
	
	/**
	 * Logs out a user by removing the related session variables.
	 *
	 * @param boolean $destroy completely destroy the session
	 * @return boolean
	 */
	public function logout($destroy = false)
	{
		if(Cookie::get($this->config['cookie_key']))
		{
			Cookie::delete($this->config['cookie_key']);
		}
		
		if ($destroy === true)
		{
			$this->session->destroy();
		}
		else
		{
			// Remove the user from the session
			$this->session->delete($this->config['session_key']);

			// Regenerate session_id
			$this->session->regenerate();
		}

		return ! $this->logged_in();
	}
	
	private function proceed($remember)
	{
		if ($this->user)
		{
			// Remove password from data
			unset($this->user['password']);
			unset($this->user['token']);
			
			// Regenerate session_id
			$this->session->regenerate();		
			$this->session->set($this->config['session_key'], $this->user);
			
			// Catching user session in cookie
			if ($remember == true)
			{
				$token = sha1(session_id());
				$this->model->setToken($this->user['id'], $token);

				Cookie::set($this->config['cookie_key'], $token, $this->config['lifetime']);
			}
			
			return $this->user;
		}
		else
		{
			return false;
		}
	}
	
	public function register($array)
	{
		$array['password'] = $this->hash($array['password']);
		return $this->model->register($array);
	}	
	
	public function remind($username, $email)
	{
		return $this->model->remind($username, $email);
	}
	
	/**
	 * Hashes a string using the configured hash method
	 *
	 * @param string $str 
	 * @return string
	 */
	public function hash($str)
	{
		return hash($this->config['hash_method'], $str);
	}

} // End Auth