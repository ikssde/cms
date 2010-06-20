<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	/**
	 * Type of hash to use for passwords. Any algorithm supported by the hash function
	 * can be used here.
	 * @see http://php.net/hash
	 * @see http://php.net/hash_algos
	 */
	'hash_method' => 'sha1',
	
	/**
	 * Set the auto-login (remember me) cookie lifetime, in seconds. The default
	 * lifetime is two weeks.
	 */
	'lifetime' => 1209600,
	
	/**
	 * Set the session key that will be used to store the current user.
	 */
	'session_key' => 'auth_user',

	/**
	 * Set the cookie name which would be used to handle user session.
	 */
	'cookie_key' => 'auth_cookie',
);