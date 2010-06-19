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
 * A simple expression engine that treats the input as a string.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Expressions
 */
class Opt_Expression_String implements Opt_Expression_Interface
{
	/**
	 * The compiler instance.
	 *
	 * @var Opt_Compiler_Class
	 */
	protected $_compiler;

	/**
	 * Sets the compiler instance in the expression parser.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function setCompiler(Opt_Compiler_Class $compiler)
	{
		$this->_compiler = $compiler;
	} // end setCompiler();

	/**
	 * The method should reset all the object references it possesses.
	 */
	public function dispose()
	{
		$this->_compiler = null;
	} // end dispose();

	/**
	 * Parses the source expressions to the PHP code.
	 *
	 * @param string $expression The expression source
	 * @return array
	 */
	public function parse($expression)
	{
		if($expression[0] == '\\')
		{
			if(strlen($expression) == 1)
			{
				$expression = '';
			}
			else
			{
				$expression = substr($expression, 1, strlen($expression) - 1);
			}
		}

		$expression = '\''.addslashes($expression).'\'';
		return array('bare' => $expression, 'escaped' => $expression, 'type' => Opt_Expression_Interface::SCALAR);
	} // end parse();
} // end Opt_Expression_String;