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
 * The class represents the script standard input.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_Console_Input extends Opl_Stream_Input
{
	/**
	 * Constructs the standard input stream.
	 */
	public function __construct()
	{
		$this->_stream = STDIN;
	} // end __construct();

	/**
	 * Returns the estimate of the number of bytes that can be read from
	 * this stream without blocking it.
	 *
	 * @return integer
	 */
	public function available()
	{
		return 0;
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
		/* null */
	} // end reset();
} // end Opl_Stream_Console_Input;
