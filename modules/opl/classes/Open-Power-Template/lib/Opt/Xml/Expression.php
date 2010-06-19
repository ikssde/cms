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
 * A node representing an expression in brackets: { and  }.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 */
class Opt_Xml_Expression extends Opt_Xml_Node
{
	/**
	 * The expression stored in the node.
	 * @var String
	 */
	private $_expression;

	/**
	 * Initializes the expression node. The argument determines the
	 * expression that it represents.
	 * @param String $expression The expression represented by the node.
	 */
	public function __construct($expression)
	{
		parent::__construct();
		$this->_expression = $expression;
	} // end __construct();

	/**
	 * Returns the expression stored in the node.
	 * @return String
	 */
	public function getExpression()
	{
		return $this->_expression;
	} // end getExpression();

	/**
	 * Sets a new expression for the node.
	 * @param String $expression The new expression.
	 */
	public function setExpression($expression)
	{
		$this->_expression = $expression;
	} // end setExpression();

	/**
	 * Returns the expression stored in the node.
	 * @return String
	 */
	public function __toString()
	{
		return $this->_expression;
	} // end __toString();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	public function preMigrate(Opt_Compiler_Class $compiler)
	{
		$this->set('hidden', false);
	} // end preMigrate();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * after processing the child nodes.
	 */
	public function postMigrate(Opt_Compiler_Class $compiler)
	{

	} // end postMigrate();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	public function preProcess(Opt_Compiler_Class $compiler)
	{
		$this->set('hidden', false);
		// Empty expressions will be caught by the try... catch.
		try
		{
			$result = $compiler->parseExpression((string)$this);
			switch($result['type'])
			{
				case Opt_Expression_Interface::ASSIGNMENT:
					$this->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $result['bare'].'; ');
					break;
				case Opt_Expression_Interface::SCALAR:
					if($result['escaping'] == false)
					{
						$this->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $result['bare']);
						$this->set('nophp', true);
						break;
					}
					// TODO: Add escaping for scalar values.
				default:
					$this->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'echo '.$result['escaped'].'; ');
			}
		}
		catch(Opt_EmptyExpression_Exception $e){}
	} // end preProcess();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	public function postProcess(Opt_Compiler_Class $compiler)
	{

	} // end postProcess();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking.
	 */
	public function preLink(Opt_Compiler_Class $compiler)
	{
		$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE));
	//	$this->_closeComments($item, $output);
	} // end preLink();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	public function postLink(Opt_Compiler_Class $compiler)
	{

	} // end postLink();
} // end Opt_Xml_Expression;
