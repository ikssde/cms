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
 * The base exception class for the exceptions across Open
 * Power Libs. It provides the library detection.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Exception extends Exception
{
	/**
	 * Returns the main OPL library class that threw the exception.
	 *
	 * @return Opl_Class The main class of the exception library.
	 */
	public function getLibrary()
	{
		$name = explode('_', get_class_name($this));
		$name = strtolower($name[0]);

		if(Opl_Registry::exists($name))
		{
			return Opl_Registry::get($name);
		}
		return null;
	} // end getLibrary();
} // end Opl_Exception;