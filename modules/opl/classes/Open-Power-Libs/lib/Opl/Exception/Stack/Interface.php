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
 * The interface for exceptions that might contain custom stack
 * data.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_Exception_Stack_Interface
{
	/**
	 * Assigns the custom stack data to the exception. The stack may
	 * be either an array or SplStack object.
	 *
	 * @param array|SplStack $data The custom stack data.
	 */
	public function setStackData($data);

	/**
	 * Returns the custom stack data from the exception.
	 *
	 * @return array|SplStack The custom stack data.
	 */
	public function getStackData();
} // end Opl_ErrorHandler_Informer_Interface;