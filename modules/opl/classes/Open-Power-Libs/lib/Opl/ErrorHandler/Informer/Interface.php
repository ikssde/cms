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
 * The interface for informers which provide additional information
 * about exceptions in the generic error handler.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_ErrorHandler_Informer_Interface
{
	/**
	 * Displays the extra piece of information about the exception.
	 * 
	 * @param Exception $exception The exception to process.
	 * @param array $params The extra parameters passed from the port.
	 */
	public function display(Exception $exception, array $params);
} // end Opl_ErrorHandler_Informer_Interface;