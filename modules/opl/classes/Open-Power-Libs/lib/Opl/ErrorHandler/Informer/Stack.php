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
 * This informer displays a custom stack trace information. The
 * exception handler may program it for different purposes. The
 * exception must implement Opl_Exception_Stack_Interface.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_ErrorHandler_Informer_Stack implements Opl_ErrorHandler_Informer_Interface
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
		if(!$exception instanceof Opl_Exception_Stack_Interface)
		{
			return;
		}
		if(!isset($params['title']))
		{
			return;
		}
		$data = $exception->getStackData();
		
		// Reverse the stack to show the deepest parts on top.
		if($data instanceof SplStack)
		{
			$data->setIteratorMode(SplStack::IT_MODE_LIFO | SplStack::IT_MODE_KEEP);
		}
		elseif(is_array($data))
		{
			$data = array_reverse($data);
		}
		else
		{
			return;
		}
		// Display the custom stack
		echo '		<p class="directive">'.$params['title'].":</p>\r\n";
		$i = 1;
		foreach($data as $item)
		{
			if($i == 1)
			{
				echo "		<p class=\"directive\">".$i.". <span class=\"bad\">".$item."</span></p>\r\n";
			}
			else
			{
				echo "		<p class=\"directive\">".$i.". <span>".$item."</span></p>\r\n";
			}
			$i++;
		}
	} // end display();
} // end Opl_ErrorHandler_Informer_ErrorInformation;