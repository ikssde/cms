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
 * The exception class for various dependencies etc.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Dependency_Exception extends Opl_Exception
{
	const PHP = 0;
	const OPL = 1;
	const OTHER = 2;

	/**
	 * The name of the file or directory that the exception concerns.
	 * @var string
	 */
	private $_dependencyType = 2;

	/**
	 * Constructs a new dependency exception. In the second argument we can
	 * specify what kind of dependency it is (PHP-related, OPL-related or some other).
	 *
	 * @param string $message The message
	 * @param integer $dependencyType The dependency type
	 */
	public function __construct($message, $dependencyType)
	{
		$this->message = (string)$message;
		$this->_dependencyType = (int)$dependencyType;
	} // end __construct();

	/**
	 * Returns the dependency type of this exception.
	 *
	 * @return integer The dependency type.
	 */
	public function getDependencyType()
	{
		return $this->_dependencyType;
	} // end getDependencyType();
} // end Opl_Dependency_Exception;