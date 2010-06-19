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
 * The class represents the GZIP-compressed file input stream.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_Compressed_Input extends Opl_Stream_Input
{
	/**
	 * Constructs the compressed file input stream. The file to open may be
	 * specified either as a string or SplFileInfo object.
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
				throw new Opl_Filesystem_Exception('Compressed file '.$file.' does not exist.');
			}
		}
		$this->_stream = gzopen($file, 'r');

		if(!is_resource($this->_stream))
		{
			$this->_stream = null;
			throw new Opl_Filesystem_Exception('Compressed file '.$file.' is not accessible for reading.');
		}
	} // end __construct();

	/**
	 * Reads the specified number of bytes from a stream. If the stream
	 * is empty, the method returns NULL in order to provide a convenient
	 * way to find the end of streams that do not support other ways
	 * of checking it.
	 *
	 * @throws Opl_Stream_Exception
	 * @param integer $byteNum The number of bytes to read.
	 * @return string|null The returned string.
	 */
	public function read($byteNum = 1)
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}

		$content = gzread($this->_stream, (integer)$byteNum);

		if($content === false)
		{
			return null;
		}
		$this->_readingPtr += strlen($content);
		return $content;
	} // end read();

	/**
	 * Reads a line from a stream. If the optional argument is provided,
	 * the method terminates reading on raching the specified length.
	 *
	 * @throws Opl_Stream_Exception
	 * @param integer $length The maximum line length to read.
	 * @return string The returned string.
	 */
	public function readLine($length = null)
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}

		$content = gzgets($this->_stream, (integer)$length, "\r\n");

		if($content === false)
		{
			throw new Opl_Stream_Exception('Unable to read a line from an input stream.');
		}
		$this->_readingPtr += strlen($content);
		return $content;
	} // end readLine();

	/**
	 * Skips the specified number of bytes in the input. Returns the
	 * number of actual skipped bytes. Note that in case of this stream,
	 * the number of bytes does mean the output bytes, not the actual
	 * binary data taken by the decompression algorithm.
	 *
	 * @param int $byteNum The number of bytes to skip.
	 * @return int The number of actual skipped bytes.
	 */
	public function skip($byteNum = 1)
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Input stream is not opened.');
		}
		$actual = strlen((string)gzread($this->_stream, (integer)$byteNum));
		$this->_readingPtr += $actual;

		return $actual;
	} // end skip();

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
		return !gzeof($this->_stream);
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
		gzclose($this->_stream);
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
		gzrewind($this->_stream);
	} // end reset();
} // end Opl_Stream_Compressed_Input;