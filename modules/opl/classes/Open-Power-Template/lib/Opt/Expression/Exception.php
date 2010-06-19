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
 * The base exception class for Open Power Template expression
 * parsers.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_Expression_Exception extends Opt_Exception
{
	/**
	 * The invalid expression
	 * @var string
	 */
	private $_expression;

	/**
	 * Sets the buggy expression.
	 *
	 * @param string $expression The buggy expression
	 */
	public function setExpression($expression)
	{
		$this->_expression = (string)$expression;
	} // end setExpression();

	/**
	 * Returns the buggy expression represented by
	 * this exception.
	 *
	 * @return string The buggy expression.
	 */
	public function getExpression()
	{
		return $this->_expression;
	} // end getExpression();
} // end Opt_Expression_Exception;