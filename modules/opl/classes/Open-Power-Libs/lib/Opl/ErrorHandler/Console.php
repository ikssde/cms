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
 * TODO: To be documented.
 *
 * @author Paweł Łukasiewicz
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
 class Opl_ErrorHandler_Console extends Opl_ErrorHandler
{
	/**
	 * TODO: To be documented.
	 * 
	 * @param <type> $name
	 * @return <type>
	 */
	public function getInformer($name)
	{
		if(!isset($this->_informers[(string)$name]))
		{
			return NULL;
		}

		// Lazy-load the informer in the string representation.
		if(is_string($this->_informers[(string)$name]))
		{
			$name = $this->_informers[(string)$name];
			$object = new $name('console');
			if(!$object instanceof Opl_ErrorHandler_Informer_Interface)
			{
				// Invalid value, skip it!
				unset($this->_informers[(string)$name]);
				return NULL;
			}
			$this->_informers[(string)$name] = $object;
		}
		return $this->_informers[(string)$name];
	} // end getInformer();

	/**
	 * TODO: To be documented.
	 *
	 * @param <type> $name
	 * @return <type>
	 */
	public function display(Exception $exception)
	{
		$debug = false;
		if(Opl_Registry::getValue('opl_extended_errors'))
		{
			$debug = true;
		}
		$out = Opl_Registry::get('stdout');

		// Match the port to the exception.
		foreach($this->_ports as $port)
		{
			if($port->match($exception))
			{
				$libraryName = $port->getName();
				if($debug === true)
				{
					$context = $port->getContext($exception);
				}
				break;
			}
		}

		if(!isset($libraryName))
		{
			return false;
		}
		$out->writeLine('= ' . $libraryName . ' =');
		$out->writeLine('MESSAGE: '.$exception->getMessage());
		$out->writeLine('CODE: ' . get_class($exception));
		if($debug)
		{
			$out->writeLine('FILE: '.$exception->getFile().' [LINE '.$exception->getLine().']');
		}
		else
		{
			$out->writeLine('Debug mode is disabled. No additional information provided.');
		}

		if($debug)
		{
			foreach($context as $name => $params)
			{
				$informer = $this->getInformer($name);
				if($informer !== null)
				{
					$informer->display($exception, $params);
				}
				else
				{
					$out->writeLine('Unknown informer: ' . $name);
				}
			}
		}
		return true;
	} // end display();
} // end Opl_ErrorHandler_Console;