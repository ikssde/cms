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
 * This informer displays a static piece of text passed from the port
 * which should contain some extra information on the exception
 * occurence circumstances and the tips how to fix it.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_ErrorHandler_Informer_ErrorInformation implements Opl_ErrorHandler_Informer_Interface
{
	/**
	 * Displays the extra piece of information about the exception.
	 *
	 * @internal
	 * @param Exception $exception The exception to process.
	 * @param array $params The extra parameters passed from the port.
	 */
	public function display(Exception $exception, array $params)
	{
		if(!isset($params['text']))
		{
			return;
		}
		echo '  			<p><strong>Exception information:</strong> '.$params['text']."</p>\r\n";
	} // end display();
} // end Opl_ErrorHandler_Informer_ErrorInformation;