<?php
	class Context
	{
		protected $_implementations = array();

		public function get($className)
		{
			if(!isset($this->_implementations[$className]))
			{
				return new $className;
			}
			return (is_object($this->_implementations[$className]) ? $this->_implementations[$className] : new $this->_implementations[$className]);
		}

		public function set($interfaceName, $implementation)
		{
			$this->_implementations[$interfaceName] = $implementation;
		}

		public static function getMainInstance()
		{
			static $instance = null;
			if(empty($instance))
				$instance = new static;
			return $instance;
		}
	}