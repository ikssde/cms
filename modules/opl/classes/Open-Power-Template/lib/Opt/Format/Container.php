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
 * The class implementing the $system special variable calls.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_Container extends Opt_Format_Abstract
{
	/**
	 * The list of supported types.
	 * @var array
	 */
	protected $_supports = array(
		'container'
	);

	/**
	 * The unique variable name generator.
	 * 
	 * @internal
	 * @var integer
	 */
	protected $_cnt = 0;

	/**
	 * Build a PHP code for the specified hook name.
	 *
	 * @internal
	 * @param string $hookName The hook name
	 * @return string The output PHP code
	 */
	protected function _build($hookName)
	{
		$negate = false;
		switch($hookName)
		{
			case 'container:notContains':
				$negate = true;
			case 'container:contains':
				$container = $this->_getVar('container');
				$list = $this->_getVar('values');
				$optimize = $this->_getVar('optimize');
				$operator = $this->_getVar('operator');
				if(empty($operator))
				{
					$operator = '&&';
				}

				if(is_string($list))
				{
					$list = array($list);
				}

				if(sizeof($list) == 1)
				{
					if($negate)
					{
						return '!Opt_Function::contains('.$container.', '.$list[0].')';
					}
					return 'Opt_Function::contains('.$container.', '.$list[0].')';
				}
				else
				{
					if($optimize)
					{
						$firstContainer = '$__contains_'.$this->_cnt.' = '.$container;
						$formerContainer = '$__contains_'.$this->_cnt;
					}
					else
					{
						$firstContainer = $container;
						$formerContainer = $container;
					}
					$first = true;
					foreach($list as &$item)
					{
						if($first)
						{
							$item = 'Opt_Function::contains('.$firstContainer.', '.$item.')';
							$first = false;
						}
						else
						{
							$item = 'Opt_Function::contains('.$formerContainer.', '.$item.')';
						}
						if($negate)
						{
							$item = '!'.$item;
						}
					}
					return implode($list, $operator);
				}
		}
	} // end _build();
} // end Opt_Format_System;
