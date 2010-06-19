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
 * The processor for opt:block instruction. Note that compiler
 * DEPENDS on this processor, using its API in order to provide the
 * support for the statically deployed blocks.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Components
 */
class Opt_Instruction_Block extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'block';
	/**
	 * The opt:block counter used to generate unique variable names.
	 * @internal
	 * @var integer
	 */
	protected $_unique = 0;

	/**
	 * The component call stack used by processSystemVar() to determine which
	 * component the call refers to.
	 * @internal
	 * @var SplStack
	 */
	protected $_stack;

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions('opt:block');
		$this->_stack = new SplStack;
	} // end configure();

	/**
	 * Migrates the opt:block node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:block node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$node->set('block', true);
		// Undefined block processing
		$params = array(
			'from' => array(self::REQUIRED, self::EXPRESSION, null),
			'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null)
		);
		$vars = $this->_extractAttributes($node, $params);
		$this->_stack->push($params['from']);

		$mainCode = ' if(is_object('.$params['from'].') && '.$params['from'].' instanceof Opt_Block_Interface){ '.$params['from'].'->setView($this); ';
		$mainCode .= $this->_commonProcessing($node, $params['from'], $vars);

		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE,  $mainCode);
		$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
		$node->set('postprocess', true);
	} // end processNode();

	/**
	 * Finishes the processing of the opt:block node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function postprocessNode(Opt_Xml_Node $node)
	{
		$this->_stack->pop();
	} // end postprocessNode();

	/**
	 * This method implements the publicly available code that generates
	 * a block support within an XML tag. By default, it is used by
	 * the compiler to support statically deployed blocks.
	 *
	 * @param Opt_Xml_Element $node The component tag
	 */
	public function processBlock(Opt_Xml_Element $node)
	{
		// Defined block processing
		$params = array(
			'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null)
		);

		$vars = $this->_extractAttributes($node, $params);
		// Get the real class name
		$cn = '$_block_'.($this->_unique++);

		$this->_stack->push($cn);

		$class = $this->_compiler->block($node->getXmlName());
		// Check, if there are any conversions that may take control over initializing
		// the component object. We are allowed to capture only particular component
		// creation or all of them.
		if((($to = $this->_compiler->convert('##block_'.$class)) != '##block_'.$class))
		{
			$ccode = str_replace(array('%CLASS%', '%TAG%'), array($class, $node->getXmlName()), $to);
		}
		elseif((($to = $this->_compiler->convert('##block')) != '##block'))
		{
			$ccode = str_replace(array('%CLASS%', '%TAG%'), array($class, $node->getXmlName()), $to);
		}
		else
		{
			$ccode = 'new '.$class;
		}

		$mainCode = $cn.' = '.$ccode.'; '.$cn.'->setView($this); ';

		$this->_commonProcessing($node, $cn, $vars);
		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE,  $mainCode);
	} // end processBlock();

	/**
	 * Finishes the public processing of the block.
	 *
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function postprocessBlock(Opt_Xml_Node $node)
	{
		$this->_stack->pop();
	} // end postprocessBlock();

	/**
	 * The common processing part of the dynamically and statically
	 * deployed components. Returns the compiled PHP code ready to
	 * be appended to the XML tag. The caller must generate a component
	 * variable name that will be used in the generated code to refer
	 * to the component object. Furthermore, he must pass the returned results
	 * of _extractAttributes() method.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The node with the component data.
	 * @param string $blockVariable The PHP block variable name.
	 * @param array $args The array of custom block attributes.
	 * @return string
	 */
	private function _commonProcessing(Opt_Xml_Element $node, $blockVariable, array $args)
	{
		// Common part of the component processing
		$argList = 'array( ';
		foreach($args as $name=>$value)
		{
			$argList .= '\''.$name.'\' => '.$value.', ';
		}
		$argList .= ')';

		if($node->get('single'))
		{
			$node->addAfter(Opt_Xml_Buffer::TAG_SINGLE_BEFORE, $blockVariable.'->onSingle('.$argList.'); ');
		}
		else
		{
			$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, ' if('.$blockVariable.'->onOpen('.$argList.')){ ');
			$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, ' } '.$blockVariable.'->onClose(); ');
		}

		$this->_process($node);
	} // end _commonProcessing();

	/**
	 * A hook to the $system special variable. Returns the
	 * compiled PHP code for the call.
	 *
	 * @internal
	 * @param array $namespace The namespace to parse
	 * @return string
	 */
	public function processSystemVar($opt)
	{
		if($this->_stack->count() == 0)
		{
			throw new Opt_Instruction_Exception('opt:block error: cannot process $'.implode('.',$opt).': no blocks active.');
		}
		return $this->_stack->top().'->get(\''.$opt[2].'\')';
	} // end processSystemVar();
} // end Opt_Instruction_Block;