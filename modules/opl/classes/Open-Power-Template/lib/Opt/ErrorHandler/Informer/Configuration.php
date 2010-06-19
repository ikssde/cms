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
 * This informer displays some configuration info about OPT.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_ErrorHandler_Informer_Configuration implements Opl_ErrorHandler_Informer_Interface
{
	/**
	 * Displays the basic OPT configuration.
	 *
	 * @internal
	 * @param Exception $exception The exception to process.
	 * @param array $params The extra parameters passed from the port.
	 */
	public function display(Exception $exception, array $params)
	{
		if(!$exception instanceof Opt_Exception)
		{
			return;
		}
		$compileMode = array(
			Opt_Class::CM_DEFAULT => 'Default',
			Opt_Class::CM_REBUILD => 'Rebuild',
			Opt_Class::CM_PERFORMANCE => 'Performance'
		);

		$tpl = Opl_Registry::get('opt');
		echo "  			<p class=\"directive\">Source directories:</p>\r\n";
		echo "  			<ol>\r\n";
		if(is_array($tpl->sourceDir))
		{
			foreach($tpl->sourceDir as $name => $path)
			{
				echo '  			<li><code>'.$name.'</code>: <span>'.$path."</span></li>\r\n";
			}
		}
		elseif(is_string($tpl->sourceDir))
		{
			echo '  			<li><code>file</code>: <span>'.$tpl->sourceDir."</span></li>\r\n";
		}
		echo "  			</ol>\r\n";
		echo '  			<p class="directive">Compilation directory: <span>'.$tpl->compileDir."</span></p>\r\n";
		echo '  			<p class="directive">Compilation mode: <span'.($tpl->compileMode == Opt_Class::CM_REBUILD ? ' class="bad"' : '').'>'.$compileMode[$tpl->compileMode]."</span></p>\r\n";
	} // end display();
} // end Opt_ErrorHandler_Informer_Configuration;