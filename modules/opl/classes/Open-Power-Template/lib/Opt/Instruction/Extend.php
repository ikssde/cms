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
 * The processor for opt:extend instruction.
 *
 * This instruction is partially hard-coded inside the compiler, because
 * it would be too hard to perform all the necessary operations from this
 * particular level. See: Opt_Compiler_Class::_addDependencies() to get
 * more details.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Modules
 */
class Opt_Instruction_Extend extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'extend';

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		/* IMPORTANT NOTICE: This instruction is partially hard-coded inside the compiler, because
		 * 	it would be to hard to do what it is intented to do from this level. See: _generateExtend()
		 * in "Internal tools and utilities" of /opt/compiler/class.php
		 */

		$this->_addInstructions(array('opt:extend'));
	} // end configure();

	/**
	 * Migrates the opt:extend node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:extend node.
	 * 
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		if($node->getParent()->getType() != 'Opt_Xml_Root')
		{
			throw new Opt_Instruction_Exception('opt:extend error: this instruction must be a top-level template node.');
		}

		$params = array(
			'file' => array(0 => self::REQUIRED, self::STRING),
			'escaping' => array(0 => self::OPTIONAL, self::BOOL, NULL),
			'dynamic' => array(0 => self::OPTIONAL, self::BOOL, false),
			'__UNKNOWN__' => array(0 => self::OPTIONAL, self::STRING, null),
		);

		$branches = $this->_extractAttributes($node, $params);

		if($params['escaping'] !== null)
		{
			$this->_compiler->set('escaping', $params['escaping']);
		}

		if($params['dynamic'] && (($branch = $this->_compiler->inherits($this->_compiler->get('currentTemplate'))) !== null))
		{
		}
		elseif(isset($branches[$this->_compiler->get('branch')]))
		{
			$branch = $branches[$this->_compiler->get('branch')];
		}
		else
		{
			$branch = $params['file'];
		}

		$node->set('branch', $branch);
		$node->set('postprocess', true);
		$this->_process($node);
	} // end processNode();

	/**
	 * Finishes the processing of the opt:extend node. In this particular
	 * case it handles the support of snippet extending, where the snippets
	 * need to be scanned AFTER they are actually loaded.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function postprocessNode(Opt_Xml_Node $node)
	{
		if($this->_compiler->processor('snippet')->isSnippet($node->get('branch')))
		{
			$node->getParent()->set('snippet', $node->get('branch'));
		}
		else
		{
			$node->getParent()->set('extend', $node->get('branch'));
		}
	} // end postprocessNode();
} // end Opt_Instruction_Extend;