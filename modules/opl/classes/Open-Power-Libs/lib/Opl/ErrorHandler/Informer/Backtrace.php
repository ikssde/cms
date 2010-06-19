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
 * This informer displays the exception stack trace.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_ErrorHandler_Informer_Backtrace implements Opl_ErrorHandler_Informer_Interface
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
		echo "		<p class=\"directive\"><strong>Debug backtrace:</strong></p>\r\n";
		$data = array_reverse($exception->getTrace());
/*		$data[] = array(
			'function' => 'Opl_Debug_Exception',
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		);*/
		$size = sizeof($data);
		echo "		<ul>\r\n";
		while(sizeof($data) > 0)
		{
			$item = array_shift($data);

			$name = (isset($item['class']) ? $item['class'].'::' : '').$item['function'];

			if(sizeof($data) == 0)
			{
				echo "		<li><p class=\"directive\">".$size.". ".$name."() - <span class=\"bad\"><code>".basename($item['file']).'</code> ['.$item['line']."]</span></p></li>\r\n";
			}
			else
			{
				echo "		<li><p class=\"directive\">".$size.". ".$name."() - <span><code>".basename($item['file']).'</code> ['.$item['line']."]</span></p></li>\r\n";
			}
			$size--;
		}
		echo "		</ul>\r\n";
	} // end display();
} // end Opl_ErrorHandler_Informer_ErrorInformation;