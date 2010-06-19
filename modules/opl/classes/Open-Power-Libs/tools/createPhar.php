<?php
/*
 *  OPEN POWER LIBS HELPER FILE
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) 2008 Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 *
 */
/**
 * The file creates a PHAR from the specified library
 */

/**
 * The class parses the stub template.
 */
class Stub_Parser
{
	private $_stubs = array();
	private $_names = array();
	private $_values = array();

	/**
	 * Registers the stub.
	 * 
	 * @param String $name Stub name
	 * @param String $file Stub file
	 */
	public function register($name, $file)
	{
		$this->_stubs[$name] = $file;
	} // end register();

	/**
	 * Assigns the variable to the parser.
	 *
	 * @param String $name The variable name
	 * @param Mixed $value The variable value
	 */
	public function __set($name, $value)
	{
		$this->_names[] = '%%'.$name.'%%';
		$this->_values[] = $value;
	} // end __set();

	/**
	 * Resets the variable list.
	 */
	public function reset()
	{
		$this->_names = array();
		$this->_values = array();
	} // end reset();

	/**
	 * Builds a stub from the specified template and returns it.
	 *
	 * @param String $name The stub name.
	 * @return String
	 */
	public function create($name)
	{
		return str_replace($this->_names, $this->_values, file_get_contents($this->_stubs[$name]));
	} // end create();
		
} // end Stub_Parser;

/**
 * The class is used to filter some unwanted files and directories from the
 * directory iteration process.
 */
class KeyFilter extends FilterIterator
{
	/**
	 * The item to be removed.
	 * @var String
	 */
	private $remove;

	/**
	 * Constructs the key filter.
	 * @param Iterator $it The iterator.
	 * @param String $remove The unwanted content.
	 */
	public function __construct(Iterator $it, $remove)
	{
		parent::__construct($it);
		$this->remove = $remove;
	} // end __construct();

	/**
	 * Decides whether to accept the iterator key.
	 * @return Boolean
	 */
	public function accept()
	{
		if(strpos($this->getInnerIterator()->key(), $this->remove) === false)
		{
			return true;
		}
		return false;
	} // end accept();

	/**
	 * Returns the unwanted value.
	 * @return String
	 */
	public function getRegex()
	{
		return $this->remove;
	} // end getRegex();

	protected function __clone()
	{
		// disallow clone 
	} // end __clone();
} // end KeyIterator;

try
{
	if($_SERVER['argc'] != 4)
	{
		die('Usage: php createPhar.php [Library] [Path] [Output]');
	}
	if(!is_dir($_SERVER['argv'][2]))
	{
		die('The specified library path is invalid.');
	}

	// Sanitize the path
	if($_SERVER['argv'][2][strlen($_SERVER['argv'][2])-1] != '/')
	{
		$_SERVER['argv'][2] .= '/';
	}

	// Initialize the stub parser
	$stubs = new Stub_Parser;
	$stubs->register('base', './stubs/base.php');
	$stubs->register('addon', './stubs/addon.php');
	$stubs->register('standalone', './stubs/standalone.php');

	// Build the archive
	$phar = new Phar($_SERVER['argv'][3]);
	$phar->startBuffering();	
	$phar->buildFromIterator(
		new KeyFilter(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($_SERVER['argv'][2])
			) ,
			'.svn'
		),
		$_SERVER['argv'][2]
	);

	// Add the stub
	if($_SERVER['argv'][1] == 'Opl')
	{
		// Define a stub for the core
		$phar->setStub(file_get_contents($_SERVER['argv'][2].'Base.php').$stubs->create('base'));
		$phar->delete('Base.php');
	}
	else
	{
		// Define a stub for other libraries
				
		$stubs->library = $_SERVER['argv'][1];
		if(isset($phar['Class.php']))
		{
			$phar->setStub(file_get_contents($_SERVER['argv'][2].'Class.php').$stubs->create('addon'));
			$phar->delete('Class.php');
		}
		else
		{
			$phar->setStub($stubs->create('standalone'));
		}
	}
	
	$phar->stopBuffering();
	echo $_SERVER['argv'][3]." created\r\n";
}
catch(Exception $e)
{
   	die('Some error occured: '.$e->getMessage()."\n");
}