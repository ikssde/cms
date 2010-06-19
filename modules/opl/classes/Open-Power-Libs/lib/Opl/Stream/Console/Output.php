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
 * The class represents the script standard output.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_Console_Output extends Opl_Stream_Output
{
	/**
	 * Constructs the standard output stream.
	 */
	public function __construct()
	{
		$this->_stream = STDOUT;
	} // end __construct();

	/**
	 * Closes the standard output stream.
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
	 * Flushes the data to the standard output.
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
} // end Opl_Stream_Console_Output;
