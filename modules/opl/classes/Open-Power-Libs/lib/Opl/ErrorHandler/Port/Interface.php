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
 * An interface for the error handler ports.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_ErrorHandler_Port_Interface
{
	/**
	 * Matches the exception to the port. The method must return true
	 * in order to inform the error handler that the port is capable
	 * to handle this exception.
	 *
	 * @param Exception $exception The exception to match
	 * @return boolean Does the port handle the exception?
	 */
	public function match(Exception $exception);

	/**
	 * Returns the port name to display in the error message header.
	 *
	 * @return string The port name
	 */
	public function getName();

	/**
	 * Returns the context information for the specified exception
	 * as an associative array. The array keys must be the identifiers
	 * of informers to be used, and the values - the parameters for
	 * the informers. If there is no context information available,
	 * the method shall return either NULL or empty array.
	 *
	 * @param Exception $exception The exception
	 * @return array|Null The context information
	 */
	public function getContext(Exception $exception);
} // end Opl_ErrorHandler_Informer_Interface;