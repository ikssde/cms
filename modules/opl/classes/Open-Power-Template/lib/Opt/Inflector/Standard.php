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

/**
 * The standard inflector for OPT that resolves the file paths
 * using the default rules.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Public
 */
class Opt_Inflector_Standard implements Opt_Inflector_Interface
{
	/**
	 * The main OPT object.
	 * @var Opt_Class
	 */
	protected $_tpl;

	/**
	 * The source directories and the streams.
	 * @var array
	 */
	protected $_streams;

	/**
	 * The compilation directory.
	 * @var string
	 */
	protected $_compileDir;

	/**
	 * Constructs the inflector object. If the sourceDir option is
	 * not valid, the constructor throws an exception.
	 *
	 * @throws Opt_Inflector_Exception
	 * @param Opt_Class $tpl The main OPT object
	 */
	public function __construct(Opt_Class $tpl)
	{
		$this->_tpl = $tpl;

		// Copy the initial streams from the configuration.
		if(is_string($this->_tpl->sourceDir))
		{
			$this->_streams = array('file' => $this->_tpl->sourceDir);
		}
		elseif(is_array($this->_tpl->sourceDir))
		{
			$this->_streams = $this->_tpl->sourceDir;
		}
		else
		{
			throw new Opt_Inflector_Exception('Standard inflector error: "sourceDir" option is not a valid path.');
		}

		// Obfuscate the paths.
		foreach($this->_streams as &$path)
		{
			$this->_tpl->_securePath($path);
		}
	} // end __construct();

	/**
	 * Registers a new stream in the inflector.
	 *
	 * @throws Opt_Inflector_Exception
	 * @param string $streamName The new stream name
	 * @param string $streamPath The new stream path
	 * @param boolean $secure Do we secure the path by adding the ending slash?
	 */
	public function addStream($streamName, $streamPath, $secure = true)
	{
		if(isset($this->_streams[(string)$streamName]))
		{
			throw new Opt_Inflector_Exception('Standard inflector error: stream already exists: '.(string)$streamName);
		}
		$this->_streams[(string)$streamName] = (string)$streamPath;

		if($secure)
		{
			$this->_tpl->_securePath($this->_streams[(string)$streamName]);
		}
	} // end addStream();

	/**
	 * Checks if the specified stream exists in the inflector.
	 * @param string $streamName The stream name
	 * @return boolean
	 */
	public function hasStream($streamName)
	{
		return isset($this->_streams[(string)$streamName]);
	} // end hasStream();

	/**
	 * Returns the path represented by the specified stream.
	 * @param string $streamName The stream name
	 * @return string
	 * @throws Opt_Inflector_Exception
	 */
	public function getStream($streamName)
	{
		if(!isset($this->_streams[(string)$streamName]))
		{
			throw new Opt_Inflector_Exception('Standard inflector error: stream does not exist: '.(string)$streamName);
		}
		return $this->_streams[(string)$streamName];
	} // end getStream();

	/**
	 * Removes a stream from the inflector.
	 * 
	 * @param string $streamName The stream name
	 * @throws Opt_Inflector_Exception
	 */
	public function removeStream($streamName)
	{
		if(!isset($this->_streams[(string)$streamName]))
		{
			throw new Opt_Inflector_Exception('Standard inflector error: stream does not exist: '.(string)$streamName);
		}

		unset($this->_streams[(string)$streamName]);
	} // end removeStream();

	/**
	 * Returns the real (filesystem) path to the specified
	 * template file. The path may be either relative or
	 * absolute.
	 *
	 * @throws Opt_Inflector_Exception
	 * @param string $file The file name
	 * @return string
	 */
	public function getSourcePath($name)
	{
		if(strpos($name, ':') !== FALSE)
		{
			// We get the stream ID from the given filename.
			$data = explode(':', $name);
			if(!isset($this->_streams[$data[0]]))
			{
				throw new Opt_Inflector_Exception('Standard inflector error: Unknown stream: '.$data[0]);
			}
			if(!$this->_tpl->allowRelativePaths && strpos($data[1], '../') !== false)
			{
				throw new Opt_Inflector_Exception('Standard inflector error: '.$data[1].': relative paths are not supported.');
			}
			return $this->_streams[$data[0]].$data[1];
		}
		// Here, the standard stream is used.
		if(!isset($this->_streams[$this->_tpl->stdStream]))
		{
			throw new Opt_Inflector_Exception('Standard inflector error: Unknown stream: '.$this->_tpl->stdStream);
		}
		if(!$this->_tpl->allowRelativePaths && strpos($name, '../') !== false)
		{
			throw new Opt_Inflector_Exception('Standard inflector error: Error while inflecting '.$name.': relative paths are not supported.');
		}
		return $this->_streams[$this->_tpl->stdStream].$name;
	} // end getSourcePath();

	/**
	 * Returns the real (filesystem) and obfuscated path
	 * to the compiled version of the specified file.
	 *
	 * @param string $file The file name
	 * @param array $inheritance The list of templates used in the inheritance
	 * @return string
	 */
	public function getCompiledPath($file, array $inheritance)
	{
		if(sizeof($inheritance) > 0)
		{
			$list = $inheritance;
			sort($list);
		}
		else
		{
			$list = array();
		}
		$path = ($this->_tpl->compileId !== null ? $this->_tpl->compileId.'_' : '');
		foreach($list as $item)
		{
			$path .= strtr($item, '/:\\', '___').'/';
		}
		return $path.strtr((string)$file, '/:\\', '___').'.php';
	} // end getCompiledPath();
} // end Opt_Inflector_Standard;
