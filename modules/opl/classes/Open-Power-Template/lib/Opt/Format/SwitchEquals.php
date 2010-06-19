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
 * A compiler for `equals` statements in opt:switch instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_SwitchEquals extends Opt_Format_Abstract
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
	private $_finalConditions = '';

	/**
	 * State variable initializer buffer.
	 *
	 * @var string
	 */
	private $_stateInitializer = '';

	/**
	 * Returned by switch:inform, allows to inform the parent node
	 * about something.
	 *
	 * @var mixed
	 */
	private $_inform = null;

	/**
	 * The switch counter to generate unique variable names.
	 * @static
	 * @var integer
	 */
	static private $_counter = 0;

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
				self::$_counter++;
				return 'if(is_scalar($__test = '.$this->_getVar('test').')){ ';
			case 'switch:enterTestEnd.first':
				return ' } ';
			case 'switch:enterTestBegin.later':
				return 'elseif(is_scalar($__test = '.$this->_getVar('test').')){ ';
			case 'switch:enterTestEnd.later':
				return ' } ';
			case 'switch:testsBefore':
				$code = $this->_stateInitializer.' switch($__test) { ';
				$this->_stateInitializer = '';
				return $code;
			case 'switch:testsAfter':
				$code = $this->_finalConditions.' } ';
				$this->_finalConditions = '';
				return $code;
			case 'switch:caseBefore':
				$this->_inform = null;
				$params = $this->_getVar('attributes');
				$element = $this->_getVar('element');
				$order = $element->get('priv:switch.order');
				if($this->_getVar('nesting') == 0)
				{
					if($this->_getVar('informed') === 1)
					{
						return 'case '.$params['value'].':'.PHP_EOL.' $__state_'.self::$_counter.' = '.$order.'; ';
					}
					return 'case '.$params['value'].':'.PHP_EOL.' ';
				}
				else
				{
					// This is an element without a tail recursion. PHP does not support such a case
					// so we must emulate it with GOTO by jumping to the appropriate label somewhere
					// deep in the switch.
					if($element->get('priv:switch.tail') === Opt_Instruction_Switch::TAIL_NO)
					{
						// The information from the bottom must be passed upwards.
						if($this->_getVar('informed') === 1)
						{
							$this->_inform = 1;
						}

						$this->_finalConditions .= ' case '.$params['value'].':'.PHP_EOL.' $__state_'.self::$_counter.' = '.$order.'; goto __switcheq_'.self::$_counter.'_'.$order.';';
						return ' __switcheq_'.self::$_counter.'_'.$order.': ';
					}
					else
					{
						if($this->_getVar('skipOrdering') === true && $this->_getVar('informed') !== 1)
						{
							return 'case '.$params['value'].':'.PHP_EOL;
						}
						$this->_stateInitializer = '$__state_'.self::$_counter.' = false;';
						$this->_inform = 1;
						return 'case '.$params['value'].':'.PHP_EOL.' $__state_'.self::$_counter.' = $__state_'.self::$_counter.' ?: '.$order.'; ';
					}
				}
				return '';
			case 'switch:caseAfter':
				// We render it only if the switch:analyze action reported it as an occurence
				// that should generate something
				if($this->_getVar('tail') !== Opt_Instruction_Switch::TAIL_PASSIVE)
				{
					if($this->_getVar('nesting') == 0)
					{
						return ' break; '.PHP_EOL;
					}
					else
					{
						// This code processes a place without tail recursion. This case
						// is not available in pure PHP, so we have to help us a bit with
						// IF clause and check the state manually once again.
						$code = ' if(';
						$first = true;
						foreach($this->_getVar('orderList') as $order)
						{
							if(!$first)
							{
								$code .= ' || ';
							}
							else
							{
								$first = false;
							}
							$code .= '$__state_'.self::$_counter.' == '.$order;
						}
						return $code.'){ break; }';
					}
				}
				return '';
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
		else
		{
			// switch:inform
			$data = $this->_inform;
			$this->_inform = null;
			return $data;
		}
	} // end action();

} // end Opt_Format_SwitchEquals;