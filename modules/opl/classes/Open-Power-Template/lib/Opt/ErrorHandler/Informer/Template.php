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
 * This informer displays information about the template for compiler
 * errors.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_ErrorHandler_Informer_Template implements Opl_ErrorHandler_Informer_Interface
{
	/**
	 * Displays the template name.
	 *
	 * @internal
	 * @param Exception $exception The exception to process.
	 * @param array $params The extra parameters passed from the port.
	 */
	public function display(Exception $exception, array $params)
	{
		if(!$exception instanceof Opt_Compiler_Exception)
		{
			return;
		}
		echo '  			<p><strong>Compiled template:</strong> '.$exception->getTemplate()."</p>\r\n";
	} // end display();
} // end Opt_ErrorHandler_Informer_Template;