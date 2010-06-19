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
 * A port that handles Opt_Exception exceptions.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_ErrorHandler_Port implements Opl_ErrorHandler_Port_Interface
{
	/**
	 * Matches the exception to the port.
	 *
	 * @param Exception $exception The exception to match
	 * @return boolean Does the port handle the exception?
	 */
	public function match(Exception $exception)
	{
		return ($exception instanceof Opt_Exception);
	} // end match();

	/**
	 * Returns the port name to display in the error message header.
	 *
	 * @return string The port name
	 */
	public function getName()
	{
		return 'Open Power Template';
	} // end getName();

	/**
	 * Returns the generic context information for OPL errors.
	 *
	 * @param Exception $exception The exception
	 * @return array|Null The context information
	 */
	public function getContext(Exception $exception)
	{
		switch(get_class($exception))
		{
			case 'Opt_Compiler_Recursion_Exception':
				return array('template' => array(), 'stack' => array('Recursion stack trace'));
			case 'Opt_Instruction_Section_Exception':
				return array('template' => array(), 'stack' => array('Section stack'));
			case 'Opt_Instruction_Exception':
				return array('template' => array());
			case 'Opt_Compiler_Exception':
				return array('template' => array());
			default:
				return array('config-opt' => array(), 'backtrace' => array());
		}
	} // end getContext();

	/**
	 * Registers the OPT error handling port in OPL error handler together
	 * with the necessary informers.
	 *
	 * @static
	 * @param Opl_ErrorHandler $handler The OPL error handler
	 */
	static public function register(Opl_ErrorHandler $handler)
	{
		$handler->addInformer('template', 'Opt_ErrorHandler_Informer_Template');
		$handler->addInformer('config-opt', 'Opt_ErrorHandler_Informer_Configuration');
		$handler->addPort(new Opt_ErrorHandler_Port());
	} // end register();
} // end Opt_ErrorHandler_Port;