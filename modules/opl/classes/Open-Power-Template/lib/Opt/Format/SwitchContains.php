<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
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
 * A compiler for `contains` statements in opt:switch instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_SwitchContains extends Opt_Format_Abstract
{
	/**
	 * The list of supported hook types.
	 * @var array
	 */
	protected $_supports = array(
		'switch'
	);

	/**
	 * The list of conditions that need to be tested
	 * at the end.
	 *
	 * @var string
	 */
	private $_conditions = '';

	/**
	 * The list of conditions that need to be tested
	 * at the end of the current top-level case.
	 *
	 * @var SplStack
	 */
	private $_finalConditions;

	/**
	 * The switch counter to generate unique variable names.
	 * @static
	 * @var integer
	 */
	static private $_counter = 0;

	/**
	 * The local GOTO label generator.
	 * @var integer
	 */
	private $_label = 0;

	/**
	 * Build a PHP code for the specified hook name.
	 *
	 * @internal
	 * @param string $hookName The hook name
	 * @return string The output PHP code
	 */
	protected function _build($hookName)
	{
		switch($hookName)
		{
			case 'switch:enterTestBegin.first':
				return 'if(Opt_Function::isContainer($__test_'.self::$_counter.' = '.$this->_getVar('test').')){ ';
			case 'switch:enterTestEnd.first':
				return ' } ';
			case 'switch:enterTestBegin.later':
				return 'elseif(Opt_Function::isContainer($__test_'.self::$_counter.' = '.$this->_getVar('test').')){ ';
			case 'switch:enterTestEnd.later':
				self::$_counter++;
				return ' } ';
			case 'switch:testsBefore':
				return '';
			case 'switch:testsAfter':
				return '';
			case 'switch:caseBefore':
				$params = $this->_getVar('attributes');
				$element = $this->_getVar('element');
				$order = $element->get('priv:order');

				$format = $this->_compiler->getFormat('#container', true);
				$format->assign('container', '$__test_'.self::$_counter);
				$format->assign('values', $params['value']);
				$format->assign('optimize', false);
				$condition = $format->get('container:contains');

				if($this->_getVar('nesting') == 0)
				{
					return 'if('.$condition.'){ '.PHP_EOL;
				}
				else
				{
					return '__switch_'.self::$_counter.'_'.$this->_getVar('order').'e:'.PHP_EOL;
				}
			case 'switch:caseAfter':
				if($this->_getVar('nesting') == 0)
				{
					return ' }'.PHP_EOL;
				}
				else
				{
					return '';
				}
		}
	} // end _build();

	/**
	 * The format actions.
	 *
	 * @param string $name The action name
	 * @return mixed
	 */
	public function action($name)
	{
		if($name == 'switch:caseAttributes')
		{
			return array(
				'value' => array(0 => Opt_Instruction_Abstract::REQUIRED, Opt_Instruction_Abstract::EXPRESSION, null, 'parse')
			);
		}
	} // end action();

} // end Opt_Format_SwitchContains;