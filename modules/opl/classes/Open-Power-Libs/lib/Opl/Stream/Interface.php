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
 * The basic interface for all the input/output operations.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_Stream_Interface
{
	/**
	 * Returns true, if the stream is open.
	 *
	 * @return boolean Is the stream open?
	 */
	public function isOpen();

	/**
	 * Closes the stream.
	 */
	public function close();
} // end Opc_Io_Interface;