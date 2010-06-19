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
 * The class represents the file output stream.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_File_Output extends Opl_Stream_Output
{
	const OVERWRITE = 0;
	const APPEND = 1;

	/**
	 * Constructs the file input stream. The file to open may be specified either
	 * as a string or SplFileInfo object.
	 *
	 * @throws Opl_Filesystem_Exception
	 * @param string|SplFileInfo The file to open for reading.
	 */
	public function __construct($file, $mode = self::OVERWRITE)
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
		$this->_stream = fopen($file, ($mode == self::OVERWRITE ? 'w' : 'a'));

		if(!is_resource($this->_stream))
		{
			$this->_stream = null;
			throw new Opl_Filesystem_Exception('File '.$file.' is not accessible for writing.');
		}
	} // end __construct();

	/**
	 * Closes the output stream.
	 */
	public function close()
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Output stream is not opened.');
		}
		fclose($this->_stream);
		$this->_stream = null;
	} // end close();

	/**
	 * Flushes the data to the file.
	 *
	 * @return boolean
	 */
	public function flush()
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Output stream is not opened.');
		}
		return fflush($this->_stream);
	} // end flush();
} // end Opl_Stream_File_Output;