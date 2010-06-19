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
 * The base exception class for Open Power Template section
 * exceptions.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_Instruction_Section_Exception extends Opt_Exception implements Opl_Exception_Stack_Interface
{
	/**
	 * The recursion data
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * Assigns the section stack to the exception.
	 *
	 * @param array|SplStack $data The section data.
	 */
	public function setStackData($data)
	{
		$this->_data = $data;
	} // end setStackData();

	/**
	 * Returns the section stack.
	 *
	 * @return array|SplStack
	 */
	public function getStackData()
	{
		return $this->_data;
	} // end getStackData();
} // end Opt_Instruction_Section_Exception;