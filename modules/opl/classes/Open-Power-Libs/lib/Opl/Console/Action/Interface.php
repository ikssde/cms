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
 * The generic interface for console actions.
 *
 * @author Paweł Łuczkiewicz
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface Opl_Console_Action_Interface
{
	/**
	 * Returns the name of the action used later as an action key in Opl_Console.
	 *
	 * @return string The action name.
	 */
	public function getName();
	/**
	 * Returns the help description.
	 *
	 * @return string Help description.
	 */
	public function getDescription();

	/**
	 * Returns array, for e.g.:
	 * array
	 * (
	 *	's,short' => 'description',
	 *	'l,long' => 'description'
	 * )
	 * where s and l are short params and short and long are long params. Description is of course description ;).
	 */
	public function getParams();

	/**
	 * Starts the action.
	 *
	 * @param array $params Array of Opl_Getopt_Option - params found in the command line.
	 */
	public function run(array $params);
} // end Opl_Console_Action_Interface;
