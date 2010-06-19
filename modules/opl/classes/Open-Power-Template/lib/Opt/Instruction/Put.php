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
 * Processes the opt:put instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 */
class Opt_Instruction_Put extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'put';
	/**
	 * The opt:content nesting level used to generate unique variable names.
	 * @internal
	 * @var integer
	 */
	protected $_nesting = 0;

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:put'));
		$this->_addAttributes(array('opt:content'));
	} // end configure();

	/**
	 * Processes the opt:put node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$params = array(
			'value' => array(0 => self::REQUIRED, self::EXPRESSION_EXT)
		);
		$this->_extractAttributes($node, $params);

		$node->set('single', false);
		$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, ' echo '.$params['value']['bare'].'; ');
	} // end processNode();

	/**
	 * Processes the opt:content attribute.
	 * @internal
	 * @param Opt_Xml_Node $node The node with the attribute
	 * @param Opt_Xml_Attribute $attr The recognized attribute.
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$result = $this->_compiler->parseExpression($attr->getValue(), 'parse', Opt_Compiler_Class::ESCAPE_OFF);

		// Detect the neighbourhood.
		$prepend = null;
		$append = null;
		$display = null;
		if(($extra = $node->getAttribute('opt:content-prepend')) !== null)
		{
			$parsed = $this->_compiler->parseExpression($extra->getValue(), 'str', Opt_Compiler_Class::ESCAPE_OFF);
			$prepend = $parsed['bare'];
		}
		if(($extra = $node->getAttribute('opt:content-append')) !== null)
		{
			$parsed = $this->_compiler->parseExpression($extra->getValue(), 'str', Opt_Compiler_Class::ESCAPE_OFF);
			$append = $parsed['bare'];
		}
		if(($extra = $node->getAttribute('opt:content-display')) !== null)
		{
			$parsed = $this->_compiler->parseExpression($extra->getValue(), 'parse', Opt_Compiler_Class::ESCAPE_OFF);
			$display = $parsed['bare'];
		}

		if($result['type'] <= Opt_Expression_Interface::SINGLE_VAR || $display !== null)
		{
			if($display === null)
			{
				$display = $result['bare'];
			}
			if($prepend !== null)
			{
				$display = $prepend.'.'.$display;
			}
			if($append !== null)
			{
				$display .= '.'.$append;
			}
			// The expression is a single variable that can be handled in a simple way.
			$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, 'if(empty('.$result['bare'].')){ '.PHP_EOL);
			$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_AFTER, '} else { '.PHP_EOL.' echo '.$this->_compiler->escape('e', $display).'; } ');
		}
		else
		{
			$display = '$_cont'.$this->_nesting;
			if($prepend !== null)
			{
				$display = $prepend.'.'.$display;
			}
			if($append !== null)
			{
				$display .= '.'.$append;
			}
			// In more complex expressions, we store the result to a temporary variable.
			$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, ' $_cont'.$this->_nesting.' = '.$result['bare'].'; if(empty($_cont'.$this->_nesting.')){ '.PHP_EOL);
			$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_AFTER, '} else { '.PHP_EOL.' echo '.$this->_compiler->escape('e', $display).'; } ');
		}
		$this->_nesting++;
		$attr->set('postprocess', true);
	} // end processAttribute();

	/**
	 * Finishes the processing of the opt:content attribute.
	 * @internal
	 * @param Opt_Xml_Node $node The node with the attribute
	 * @param Opt_Xml_Attribute $attr The recognized attribute.
	 */
	public function postprocessAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$this->_nesting--;
	} // end postprocessAttribute();
} // end Opt_Instruction_Put;