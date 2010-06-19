<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 *
 */

if(version_compare(PHP_VERSION, '5.3.0', '<'))
{
	die('Open Power Libs requires PHP 5.3.0 or newer. Your version: '.PHP_VERSION);
}

/**
 * The translation interface for OPL libraries.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_Translation_Interface
{
	/**
	 * This method is supposed to return the specified localized message.
	 *
	 * @param string $group The message group
	 * @param string $id The message identifier
	 * @return string
	 */
	public function _($group, $id);

	/**
	 * Assigns the external data to the message body. The operation of
	 * concatenating the message and the data is left for the programmer.
	 * The method should save it in the internal buffer and use in the
	 * next first call of _() method.
	 *
	 * @param string $group The message group
	 * @param string $id The message identifier
	 * @param ... Custom arguments for the specified text.
	 */
	public function assign($group, $id);
} // end Opl_Translation_Interface;

/**
 * The generic class autoloader is a slightly enhanced version of the
 * AggregateAutoloader <http://github.com/zyxist/AggregateAutoloader>
 * originally distributed under the terms of MIT license.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Loader
{
	/**
	 * The main directory used by autoloader
	 * @static
	 * @var string
	 */
	static private $_directory = '';

	/**
	 * The list of available libraries.
	 * @var array
	 */
	private $_libraries = array();

	/**
	 * The library extensions.
	 * @var array
	 */
	private $_extensions = array();

	/**
	 * The namespace separator
	 * @var string
	 */
	private $_namespaceSeparator = '\\';

	/**
	 * Constructs the autoloader.
	 *
	 * @param string $namespaceSeparator The namespace separator used in this autoloader.
	 * @param string $defaultPath The default library path.
	 */
	public function __construct($namespaceSeparator = '\\', $defaultPath = './')
	{
		$this->_namespaceSeparator = $namespaceSeparator;

		if($defaultPath[strlen($defaultPath) - 1] != '/')
		{
			$defaultPath .= '/';
		}
		$this->_defaultPath = $defaultPath;
	} // end __construct();

	/**
	 * Registers a new library to match.
	 *
	 * @param string $library The library name to add.
	 * @param string $path The path to the library.
	 * @param string $extension The library file extension.
	 */
	public function addLibrary($library, $path, $extension = '.php')
	{
		if(isset($this->_libraries[(string)$library]))
		{
			throw new RuntimeException('Library '.$library.' is already added.');
		}
		if($path !== null)
		{
			if($path[strlen($path) - 1] != '/')
			{
				$path .= '/';
			}
			$this->_libraries[(string)$library] = $path;
		}
		else
		{
			$this->_libraries[(string)$library] = $this->_defaultPath.$library.'/';
		}
		$this->_extensions[(string)$library] = $extension;
	} // end addLibrary();

	/**
	 * Checks if the specified library is available.
	 *
	 * @param string $library The library name to check.
	 */
	public function hasLibrary($library)
	{
		return isset($this->_libraries[(string)$library]);
	} // end hasLibrary();

	/**
	 * Removes a recognized library.
	 *
	 * @param string $library The library name to remove.
	 */
	public function removeLibrary($library)
	{
		if(!isset($this->_libraries[(string)$library]))
		{
			throw new RuntimeException('Library '.$library.' is not available.');
		}
		unset($this->_libraries[(string)$library]);
		unset($this->_extensions[(string)$library]);
	} // end removeLibrary();

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 */
	public function setNamespaceSeparator($sep)
	{
		$this->_namespaceSeparator = $sep;
	} // end setNamespaceSeparator();

	/**
	 * Gets the namespace seperator used by classes in the namespace of this class loader.
	 *
	 * @return string
	 */
	public function getNamespaceSeparator()
	{
		return $this->_namespaceSeparator;
	} // end getNamespaceSeparator();

	/**
	 * Sets the default path used by the libraries. Note that it does not affect
	 * the already added libraries.
	 *
	 * @param string $defaultPath The new default path.
	 */
	public function setDefaultPath($defaultPath)
	{
		if($defaultPath[strlen($defaultPath) - 1] != '/')
		{
			$defaultPath .= '/';
		}
		$this->_defaultPath = $defaultPath;
	} // end setDefaultPath();

	/**
	 * Returns the default path used by the libraries.
	 *
	 * @return string The current default path.
	 */
	public function getDefaultPath()
	{
		return $this->_defaultPath;
	} // end getDefaultPath();

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	} // end register();

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	*/
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	} // end unregister();

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 * @return void
	 */
	public function loadClass($className)
	{
		$className = ltrim($className, $this->_namespaceSeparator);
		$match = strstr($className, $this->_namespaceSeparator, true);

		if(false === $match || !isset($this->_libraries[$match]))
		{
			return false;
		}
		$rest = strrchr($className, $this->_namespaceSeparator);
		$replacement =
			str_replace($this->_namespaceSeparator, '/', substr($className, 0, strlen($className) - strlen($rest))).
			str_replace(array('_', $this->_namespaceSeparator), '/', $rest);

		require($this->_libraries[$match].$replacement.$this->_extensions[$match]);
		return true;
	} // end loadClass();
} // end Opl_Loader;

/**
 * The Registry provides a safe registry of the main system objects and
 * certain values. Contrary to singleton implementations, the registry
 * can be erased. Intentionally, the programmer should use different buffers
 * for scalar values and for objects.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Registry
{
	/**
	 * The list of stored objects.
	 * @static
	 * @var array
	 */
	static private $_objects = array();

	/**
	 * The list of stored values.
	 * @static
	 * @var array
	 */
	static private $_values = array();

	/**
	 * Registers a new object in the registry.
	 *
	 * @static
	 * @param string $name The object key
	 * @param object $object The registered object
	 */
	static public function set($name, $object)
	{
		self::$_objects[$name] = $object;
	} // end set();

	/**
	 * Returns the previously registered object. If the object does not
	 * exist, it throws an exception.
	 *
	 * @static
	 * @throws Opl_Registry_Exception
	 * @param string $name The registered object key.
	 * @return object The object stored under the specified key.
	 */
	static public function get($name)
	{
		if(!isset(self::$_objects[$name]))
		{
			throw new Opl_Registry_Exception('The specified registry object: '.$name.' does not exist');
		}
		return self::$_objects[$name];
	} // end get();

	/**
	 * Check whether there is an object registered under a specified key.
	 *
	 * @static
	 * @param string $name The object key
	 * @return boolean A boolean value indicating whether the object exists or not.
	 */
	static public function exists($name)
	{
		return !empty(self::$_objects[$name]);
	} // end exists();

	/**
	 * Sets the state variable in the registry.
	 *
	 * @static
	 * @param string $name The variable name
	 * @param mixed $value The variable value
	 */
	static public function setValue($name, $value)
	{
		self::$_values[$name] = $value;
	} // end setValue();

	/**
	 * Returns the state variable from the registry. If the
	 * variable does not exist, it returns NULL.
	 *
	 * @static
	 * @param string $name The variable name
	 * @return mixed
	 */
	static public function getValue($name)
	{
		if(!isset(self::$_values[$name]))
		{
			return NULL;
		}
		return self::$_values[$name];
	} // end getValue();
} // end Opl_Registry;

/**
 * The base class for the other OPL libraries. It provides the configuration
 * and plugin handling.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Class
{
	// Plugin support
	public $pluginDir = NULL;
	public $pluginDataDir = NULL;
	public $pluginAutoload = true;

	/**
	 * The optional configuration options.
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Returns the specified configuration property value.
	 *
	 * @param string $name The property name
	 * @return mixed The property value.
	 */
	public function __get($name)
	{
		if($name[0] == '_')
		{
			return NULL;
		}
		if(!isset($this->_config[$name]))
		{
			throw new Opl_Class_Exception('The specified property: '.$name.' does not exist in an object of '.get_class($this));
		}
		return $this->_config[$name];
	} // __get();

	/**
	 * Sets the custom configuration property value.
	 *
	 * @param string $name The property name
	 * @param mixed $value The property value
	 */
	public function __set($name, $value)
	{
		if($name[0] == '_')
		{
			return NULL;
		}

		$this->_config[$name] = $value;
	} // end __set();

	/**
	 * Loads the configuration from external array or INI file.
	 *
	 * @param string|array $config The configuration option values or the INI filename.
	 * @return boolean True on success
	 */
	public function loadConfig($config)
	{
		if(is_string($config))
		{
			$config = @parse_ini_file($config);
		}

		if(!is_array($config))
		{
			return false;
		}
		foreach($config as $name => $value)
		{
			if($name[0] == '_')
			{
				continue;
			}
			if(property_exists($this, $name))
			{
				$this->$name = $value;
			}
			else
			{
				$this->_config[$name] = $value;
			}
		}
		return true;
	} // end loadConfig();

	/**
	 * Returns the configuration as an array.
	 *
	 * @return array The configuration properties
	 */
	public function getConfig()
	{
		$vars = $this->_config;
		$internal = get_object_vars($this);
		foreach($internal as $id=>$var)
		{
			if($id[0] != '_')
			{
				$vars[$id] = $var;
			}
		}
		return $vars;
	} // end getConfig();

	/**
	 * Loads the plugins from the directories specified in the class configuration.
	 *
	 * @throws Opl_Filesystem_Exception If one of plugin directories does not exist or is not accessible.
	 */
	public function loadPlugins()
	{
		if(is_string($this->pluginDir))
		{
			$dirs[] = &$this->pluginDir;
		}
		elseif(is_array($this->pluginDir))
		{
			$dirs = &$this->pluginDir;
		}
		else
		{
			throw new DomainException(get_class($this).'::pluginDir requires pluginDir property to be either string or array.');
		}

		$dataFile = $this->pluginDataDir.get_class($this).'_Plugins.php';
		$cplTime = @filemtime($dataFile);
		$rebuild = false;
		if($this->pluginAutoload)
		{
			if($cplTime !== false)
			{
				// The plugin data file exists, but we have to check
				// whether there are some new plugins or not.
				$mode = 0;
				foreach($dirs as &$dir)
				{
					if($mode == 0)
					{
						$dirTime = @filemtime($dir);
						if($dirTime === false)
						{
							throw new Opl_Filesystem_Exception('The directory '.$dir.' does not exist.');
						}

						// Some new plugins have been added to this directory
						if($dirTime > $cplTime)
						{
							$rebuild = true;
							$mode = 1;
						}
					}
					// Now, we know that one of the dirs has a new plugin
					// We just have to check if all the directories exist
					elseif(!is_dir($dir))
					{
						throw new Opl_Filesystem_Exception('The directory '.$dir.' does not exist.');
					}
				}
			}
		}
		if($cplTime === false)
		{
			// No plugin data file,
			foreach($dirs as &$dir)
			{
				if(!is_dir($dir))
				{
					throw new Opl_Filesystem_Exception('The directory '.$dir.' does not exist.');
				}
			}
			$rebuild = true;
		}
		// We have to rebuild the file
		if($rebuild)
		{
			$src = '<'.'?php ';
			foreach($dirs as &$dir)
			{
				$this->_securePath($dir);
				foreach(new DirectoryIterator($dir) as $file)
				{
					if($file->isFile())
					{
						$src .= $this->_pluginLoader($dir, $file);
					}
				}
			}
			if(is_writeable($this->pluginDataDir))
			{
				file_put_contents($dataFile, $src);
			}
			else
			{
				throw new Opl_Filesystem_Exception('The directory '.$this->pluginDataDir.' is not writeable by PHP.');
			}
		}

		require($dataFile);
	} // end loadPlugins();

	/**
	 * The method allows to define the specific plugin loading settings for the
	 * library. Because the results are cached in order not to exhaust the server
	 * resources, the method must return a PHP code that loads the specified plugin.
	 *
	 * @internal
	 * @param string $directory The plugin location
	 * @param SplFileInfo $file The plugin file information
	 * @return string
	 */
	protected function _pluginLoader($directory, SplFileInfo $file)
	{
		return '';
	} // end _pluginLoader();

	/**
	 * The method allows to secure the path by adding an ending slash, if
	 * it is not specified.
	 *
	 * @internal
	 * @param string &$path The path to secure.
	 */
	public function _securePath(&$path)
	{
		if($path[strlen($path)-1] != '/')
		{
			$path .= '/';
		}
	} // end _securePath();
} // end Opl_Class;
