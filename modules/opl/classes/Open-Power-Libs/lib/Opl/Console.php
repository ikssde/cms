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
 * The core of the command-line interface. Allows discovering and executing
 * new actions.
 *
 * @author Paweł Łuczkiewicz
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Console
{
	/**
	 * List of actions.
	 *
	 * @var array
	 */
	const AUTO_HELP = 1;

	/**
	 * The list of currently registered actions.
	 *
	 * @var array
	 */
	protected $_actions = array();

	/**
	 * The console flags.
	 *
	 * @var integer
	 */
	protected $_flags = 0;

	/**
	 * The default action name.
	 * @var string
	 */
	protected $_default = null;

	/**
	 * Stores the class instance in the OPL registry under
	 * the key 'console'.
	 */
	public function __construct()
	{
		Opl_Registry::set('console', $this);
	} // end __construct();

	/**
	 * Sets the console application flags. Implements fluent
	 * interface.
	 *
	 * @param integer $flags The console flags.
	 * @return Opl_Console Fluent interface.
	 */
	public function setFlags($flags)
	{
		$this->_flags = (int)$flags;

		return $this;
	} // end setFlags();

	/**
	 * Sets the name of the default action. The action does not have
	 * to be registered at the time of calling this method. Implements
	 * fluent interface.
	 * 
	 * @param string $defaultAction The name of the default action.
	 * @return Opl_Console Fluent interface.
	 */
	public function setDefaultAction($defaultAction)
	{
		$this->_default = (string)$defaultAction;

		return $this;
	} // end setDefaultAction();

	/**
	 * Returns the name of the default action.
	 * 
	 * @return string The default action name.
	 */
	public function getDefaultAction()
	{
		return $this->_default;
	} // end getDefaultAction();

	/**
	 * Registers a new action in the system. Implements fluent
	 * interface.
	 * 
	 * @param Opl_Console_Action_Interface $action The registered action.
	 * @return Opl_Console Fluent interface.
	 */
	public function addAction(Opl_Console_Action_Interface $action)
	{
		$this->_actions[$action->getName()] = $action;

		return $this;
	} // end addAction();

	/**
	 * Returns the action registered under the specified name. If the
	 * action does not exist, an exception is thrown.
	 *
	 * @throws Opl_Console_Exception
	 * @param string $name The action name.
	 * @return Opl_Console_Action_Interface
	 */
	public function getAction($name)
	{
		if(!isset($this->_actions[$name]))
		{
			throw new Opl_Console_Exception('The specified action \''.$name.'\' does not exist.');
		}
		return $this->_actions[$name];
	} // end getAction();

	/**
	 * Returns true, if the specified action is registered in the system.
	 *
	 * @param string $name The action name to check.
	 * @return boolean Does the action exist?
	 */
	public function hasAction($name)
	{
		return isset($this->_actions[(string)$name]);
	} // end hasAction();

	/**
	 * Executes the specified action with the specified arguments.
	 * If the action does not exist, an exception is thrown. Implements
	 * fluent interface.
	 *
	 * @throws Opl_Console_Exception
	 * @param string $name The action name.
	 * @param array $params The action arguments.
	 * @return Opl_Console Fluent interface.
	 */
	public function runAction($name, array $params)
	{
		if(!isset($this->_actions[$name]))
		{
			throw new Opl_Console_Exception('The specified action \''.$name.'\' does not exist.');
		}
		$this->_actions[$name]->run($params);

		return $this;
	} // end runAction();

	/**
	 * Removes the specified action. If the action does not exist, an
	 * exception is thrown. Implements fluent interface.
	 *
	 * @throws Opl_Console_Exception
	 * @param string $name The action to remove.
	 * @return Opl_Console Fluent interface.
	 */
	public function removeAction($name)
	{
		if(!isset($this->_actions[$name]))
		{
			throw new Opl_Console_Exception('The specified action \''.$name.'\' does not exist.');
		}
		unset($this->_actions[$name]);

		return $this;
	} // end removeAction();

	/**
	 * Starts the console application. The method checks the action to run,
	 * parses the arguments and fires it.
	 *
	 * @throws Opl_Console_Exception
	 * @param array $argv The argument list.
	 */
	public function run(array $argv)
	{
		$action = isset($argv[1]) ? $argv[1] : $this->_default;

		if(!Opl_Registry::exists('stdout')) 
		{
			Opl_Registry::set('stdout', new Opl_Stream_Console_Output);
		}

		if(!Opl_Registry::exists('stdin'))
		{
			Opl_Registry::set('stdin', new Opl_Stream_Console_Input);
		}
		
		if(isset($this->_actions[$action]))
		{
			unset($argv[0], $argv[1]);
			$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS | Opl_Getopt::ALLOW_SHORT_ARGS | Opl_Getopt::AUTO_HELP);
			foreach($this->_actions[$action]->getParams() as $option)
			{
				$getopt->addOption($option);
			}

			if(!$getopt->parse(array_values($argv)))
			{
				// TODO: Exception goes here!
				return;
			}
			$this->runAction($action, $getopt->getIterator()->getArrayCopy());
		}
		else
		{
			if($this->_flags & self::AUTO_HELP)
			{
				$this->showHelp();
			}
			else
			{
				throw new Opl_Console_Exception('The specified action \''.$action.'\' does not exist.');
			}
		}
	} // end run();

	/**
	 * Displays the help.
	 */
	public function showHelp()
	{
		$stdout = Opl_Registry::get('stdout');
		$stdout->writeLine('Help');
		foreach(self::$_actions as $action)
		{
			$stdout->writeLine($action->getName() . ' - ' . $action->getDescription());
		}
	} // end showHelp();
} // end Opl_Console;
