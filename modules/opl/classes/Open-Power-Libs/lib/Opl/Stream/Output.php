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
 * The output stream abstract primitive.
 *
 * @abstract
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
abstract class Opl_Stream_Output implements Opl_Stream_Interface
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
	 * The end-of-line symbol.
	 * @var string
	 */
	static protected $_eol = PHP_EOL;

	/**
	 * Returns the current end-of-line symbol. Unless the programmer
	 * changed it, it is set to PHP_EOL.
	 *
	 * @static
	 * @return string
	 */
	static public function getEol()
	{
		return self::$_eol;
	} // end getEndOfLine();

	/**
	 * Sets the end-of-line symbols used by the output streams.
	 *
	 * @static
	 * @param string $eol The new end-of-line symbol.
	 */
	static public function setEol($eol)
	{
		self::$_eol = (string)$eol;
	} // end setEol();

	/**
	 * Flushes the remaining bytes and forces them to be saved in the stream.
	 */
	abstract function flush();

	/**
	 * Writes the specified string to a stream. The optional arguments
	 * may control the part of the input string that will be actually
	 * written.
	 *
	 * @throws Opl_Stream_Exception
	 * @param string $bytes The data to write.
	 * @param int $offset The offset of the write buffer to start.
	 * @param int $length The data length.
	 */
	public function write($bytes, $offset = 0, $length = null)
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Output stream is not opened.');
		}
		if($length === null)
		{
			$length = strlen($bytes) - $offset;
		}

		if($offset > 0)
		{
			fwrite($this->_stream, substr($bytes, $offset, $length), $length);
		}
		else
		{
			fwrite($this->_stream, $bytes, $length);
		}
	} // end write();

	/**
	 * Writes a string to a stream, terminating it with an end-of-line
	 * symbol.
	 *
	 * @param string $string The string to write.
	 */
	public function writeLine($string)
	{
		if(!is_resource($this->_stream))
		{
			throw new Opl_Stream_Exception('Output stream is not opened.');
		}

		fwrite($this->_stream, $string.self::$_eol);
	} // end writeLine();

	/**
	 * Writes a compound PHP type to the stream. For scalar values, it works
	 * identically, as write(). For arrays and serializable objects, it serializes
	 * them and writes to the stream.
	 *
	 * @throws DomainException
	 * @throws Opl_Stream_Exception
	 * @param mixed $item The item to write.
	 */
	public function writeCompound($item)
	{
		if(is_scalar($item))
		{
			$this->write($item);
		}
		elseif(is_array($item) || (is_object($item) && method_exists($item, '__sleep')))
		{
			$this->write(serialize($item));
		}
		throw new DomainException('The specified argument is not serializable.');
	} // end writeCompound();

	/**
	 * Returns true, if the stream is open.
	 *
	 * @return boolean Is the stream open?
	 */
	public function isOpen()
	{
		return is_resource($this->_stream);
	} // end isOpen();
} // end Opl_Stream_Output;
