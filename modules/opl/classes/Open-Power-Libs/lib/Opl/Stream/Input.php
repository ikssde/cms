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
 * The input stream abstract primitive.
 *
 * @abstract
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
abstract class Opl_Stream_Input implements Opl_Stream_Interface
{
	const NONBLOCKING = 0;
	const BLOCKING = 1;

	/**
	 * The stream.
	 *
	 * @var resource
	 */
	protected $_stream;

	/**
	 * The current blocking mode.
	 *
	 * @var integer
	 */
	private $_blocking = 1;

	/**
	 * The reading pointer
	 * 
	 * @var integer
	 */
	protected $_readingPtr = 0;

	/**
	 * Returns true, if there are any data available in the buffer that may be
	 * read immediately. If the stream does not support checking the buffer status,
	 * the method is obliged to throw an exception.
	 *
	 * @throws Opl_Stream_Exception
	 * @return boolean
	 */
	abstract function available();

	/**
	 * Sets the blocking mode: either NONBLOCKING or BLOCKING. Returns true,
	 * if the operation was successful.
	 *
	 * @throws DomainException
	 * @param integer $mode The blocking mode
	 * @return boolean The operation status.
	 */
	public function setBlocking($mode)
	{
		if($mode != 0 && $mode != 1)
		{
			throw new DomainException('Invalid blocking mode: either NONBLOCKING or BLOCKING expected');
		}

		if(is_resource($this->_stream))
		{
			if(stream_set_blocking($this->_stream, $mode))
			{
				$this->_blocking = $mode;
				return true;
			}
			return false;
		}
		$this->_blocking = $mode;
		return true;
	} // end setBlocking();

	/**
	 * Returns the current blocking mode for this stream.
	 *
	 * @return integer The current blocking mode.
	 */
	public function getBlocking()
	{
		return $this->_blocking;
	} // end getBlocking();

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

		$content = stream_get_line($this->_stream, (integer)$byteNum, '');

		if($content === false)
		{
			return null;
		}
		elseif($content === '' && $byteNum > 0)
		{
			throw new Opl_Stream_Exception('Host disconnected.');
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

		$content = ($length === null ? fgets($this->_stream) : fgets($this->_stream, $length));

		if($content === false)
		{
			throw new Opl_Stream_Exception('Unable to read a line from an input stream.');
		}
		$this->_readingPtr += strlen($content);
		return $content;
	} // end readLine();

	/**
	 * Resets the stream, setting the internal cursor to the beginning.
	 */
	abstract function reset();

	/**
	 * Skips the specified number of bytes in the input. Returns the
	 * number of actual skipped bytes.
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
		$actual = strlen((string)stream_get_line($this->_stream, (integer)$byteNum, ''));
		$this->_readingPtr += $actual;

		return $actual;
	} // end skip();
	
	/**
	 * Returns true, if the stream is open.
	 *
	 * @return boolean Is the stream open?
	 */
	public function isOpen()
	{
		return is_resource($this->_stream);
	} // end isOpen();
} // end Opl_Stream_Input;
