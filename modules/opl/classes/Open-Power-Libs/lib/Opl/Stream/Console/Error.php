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
 * The class represents the script standard error output.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Stream_Console_Error extends Opl_Stream_Console_Output
{
	/**
	 * Constructs the standard error output stream.
	 */
	public function __construct()
	{
		$this->_stream = fopen(STDERR, 'w');
	} // end __construct();
} // end Opl_Stream_Console_Error;