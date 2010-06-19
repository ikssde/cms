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
 * Using this stream the programmer may use ordinary PHP strings
 * as fully functional streams.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_String_Input extends Opl_Stream_Input
{
	/**
	 * The string buffer.
	 * @var string
	 */
	private $_string;

	/**
	 * The string buffer size.
	 * @var integer
	 */
	private $_size;

	/**
	 * The string buffer cursor.
	 * @var integer
	 */
	private $_cursor;

	/**
	 * Creates a new string input stream.
	 *
	 * @param string $string The string to stream.
	 */
	public function __construct($string)
	{
		$this->_string = (string)$string;
		$this->_size = strlen($this->_string);
		$this->_cursor = 0;
	} // end __construct();

	/**
	 * Returns true, if there are still some data available
	 * in the stream.
	 *
	 * @return boolean
	 */
	public function available()
	{
		return $this->_cursor < $this->_size;
	} // end available();

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
		if($this->_cursor >= $this->_size)
		{
			return null;
		}

		$data = substr($this->_string, $this->_cursor, $byteNum);
		$this->_cursor += strlen($data);

		return $data;
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
		if($this->_cursor >= $this->_size)
		{
			return null;
		}

		$endPosition = strpos($this->_string, "\n", $this->_cursor);
		if($endPosition === null && $length === null)
		{
			$length = $this->_size - $this->_cursor;
		}
		elseif($endPosition !== null && $length !== null)
		{
			if($endPosition - $this->_cursor < $length)
			{
				$length = $endPosition - $this->_cursor;
			}
		}
		elseif($endPosition !== null)
		{
			$length = $endPosition;
		}

		$data = substr($this->_string, $this->_cursor, $length);
		$this->_cursor += strlen($data);

		return $data;
	} // end readLine();

	/**
	 * Closes the stream.
	 */
	public function close()
	{
		$this->_string = '';
		$this->_size = 0;
		$this->_cursor = 0;
	} // close();

	/**
	 * Resets the internal cursor.
	 */
	public function reset()
	{
		$this->_cursor = 0;
	} // end reset();
} // end Opl_Stream_String_Input;