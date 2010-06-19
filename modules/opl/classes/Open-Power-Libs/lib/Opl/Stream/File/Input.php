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
 * The class represents the file input.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_File_Input extends Opl_Stream_Input
{
	/**
	 * Constructs the file input stream. The file to open may be specified either
	 * as a string or SplFileInfo object.
	 *
	 * @throws Opl_Filesystem_Exception
	 * @param string|SplFileInfo The file to open for reading.
	 */
	public function __construct($file)
	{
		if($file instanceof SplFileInfo)
		{
			$file = $file->getFilename();
		}
		else
		{
			if(!file_exists($file))
			{
				throw new Opl_Filesystem_Exception('File '.$file.' does not exist.');
			}
		}
		$this->_stream = fopen($file, 'r');

		if(!is_resource($this->_stream))
		{
			$this->_stream = null;
			throw new Opl_Filesystem_Exception('File '.$file.' is not accessible for reading.');
		}
	} // end __construct();

	/**
	 * Returns true, if there are any data available in the stream.
	 *
	 * @throws Opl_Stream_Exception
	 * @return boolean
	 */
	public function available()
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}
		return !feof($this->_stream);
	} // end available();

	/**
	 * Closes the input stream.
	 */
	public function close()
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}
		fclose($this->_stream);
		$this->_stream = null;
	} // end close();

	/**
	 * Resets the input stream.
	 */
	public function reset()
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}
		rewind($this->_stream);
	} // end reset();
} // end Opl_Stream_File_Input;