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
 * This informer informs about a missing dependency and explains
 * Opt_Dependency_Exception messages.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_ErrorHandler_Informer_Dependency implements Opl_ErrorHandler_Informer_Interface
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
		if(!$exception instanceof Opl_Dependency_Exception)
		{
			return;
		}
		switch($exception->getDependencyType())
		{
			case Opl_Dependency_Exception::PHP:
				echo '  			<p><strong>PHP dependency:</strong> one of required PHP extensions is missing. Update your php.ini configuration or contact the administrator to get to know more information.'."</p>\r\n";

				echo '		<p class="directive">Installed extensions:</p>'.PHP_EOL;
				foreach(get_loaded_extensions() as $num => $extension)
				{
					echo "		<p class=\"directive\">".($num+1).". <span>".$extension."</span></p>\r\n";
				}
				break;
			case Opl_Dependency_Exception::OPL:
				echo '  			<p><strong>OPL dependency:</strong> this feature relies on one of Open Power Libs project which is not installed in your case. Do not use the feature or install the requested OPL library.'."</p>\r\n";
				break;
		}
	} // end display();
} // end Opl_ErrorHandler_Informer_ErrorInformation;