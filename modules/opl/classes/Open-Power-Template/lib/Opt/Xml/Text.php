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
 * A container for Opt_Xml_Cdata and Opt_Xml_Expression objects.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 */
class Opt_Xml_Text extends Opt_Xml_Scannable
{
	/**
	 * Constructs a new object of Opt_Xml_Text. The first argument may be
	 * used to initialize the first Opt_Xml_Cdata node.
	 *
	 * @param String $cdata The optional text to initialize the Opt_Xml_Cdata.
	 */
	public function __construct($cdata = null)
	{
		parent::__construct();
		if(!is_null($cdata))
		{
			$this->appendData($cdata);
		}
	} // end __construct();

	/**
	 * Appends the text to the last Opt_Xml_Cdata node. If the last child is
	 * not Opt_Xml_Cdata, it is created.
	 *
	 * @param String $cdata The text to append.
	 */
	public function appendData($cdata)
	{
		$node = $this->getLastChild();
		if(is_null($node) || $node->getType() != 'Opt_Xml_Cdata' || $node->get('cdata') == true)
		{
			$node = new Opt_Xml_Cdata($cdata);
			$this->appendChild($node);
		}
		else
		{
			$node->appendData($cdata);
		}
	} // end appendData();

	/**
	 * Returns true if the node contains only static whitespace symbols.
	 *
	 * @return boolean
	 */
	public function isWhitespace()
	{
		foreach($this as $node)
		{
			if($node instanceof Opt_Xml_Cdata)
			{
				if(!ctype_space((string)$node))
				{
					return false;
				}
			}
			elseif($node instanceof Opt_Xml_Expression)
			{
				return false;
			}
		}
		return true;
	} // end isWhitespace();

	/**
	 * Tests if the specified node can be appended to this node type.
	 *
	 * @param Opt_Xml_Node $node The node to test.
	 */
	protected function _testNode(Opt_Xml_Node $node)
	{
		if($node->getType() != 'Opt_Xml_Expression' && $node->getType() != 'Opt_Xml_Cdata')
		{
			throw new Opt_APIInvalidNodeType_Exception('Opt_Xml_Text', $node->getType());
		}
	} // end _testNode();

	/**
	 * This function is executed by the compiler before the second compilation stage.
	 */
	public function preMigrate(Opt_Compiler_Class $compiler)
	{
		$this->set('hidden', false);
		if($this->hasChildren())
		{
			$compiler->setChildren($this);
		}
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
		if($this->hasChildren())
		{
			$compiler->setChildren($this);
		}
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
		$compiler->setChildren($this);
	} // end preLink();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	public function postLink(Opt_Compiler_Class $compiler)
	{
		$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_AFTER));
	} // end postLink();
} // end Opt_Xml_Text;
