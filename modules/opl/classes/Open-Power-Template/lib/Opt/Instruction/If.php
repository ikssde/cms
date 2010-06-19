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
 * The processor for opt:if instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Control
 */
class Opt_Instruction_If extends Opt_Instruction_Abstract
{
	const ALLOW_LONG_IF = 1;
	const ALLOW_TAG_FORM = 2;
	const ALLOW_ATTRIBUTE_FORM = 4;
	const FORCE_ELSE = 8;

	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'if';
	/**
	 * The opt:if occurence counter used to generate unique variable names.
	 * @internal
	 * @var integer
	 */
	protected $_cnt = 0;

	/**
	 * Array contains deprecated attributes.
	 * @var array
	 */
	protected $_deprecatedAttributes = array('opt:on');

	/**
	 * Array contains deprecated instructions.
	 * @var array
	 */
	protected $_deprecatedInstructions = array('opt:elseif');

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:if', 'opt:else-if'));
		$this->_addAttributes(array('opt:if', 'opt:omit-tag'));
		$this->_addAmbiguous(array('opt:else' => 'opt:if'));
		if($this->_tpl->backwardCompatibility)
		{
			$this->_addAttributes($this->_deprecatedAttributes);
			$this->_addInstructions($this->_deprecatedInstructions);
		}
	} // end configure();

	/**
	 * Migrates the opt:if node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		switch($node->getName())
		{
			case 'elseif':
				$node->setName('else-if');
				break;
		}
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:if node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$params = array(
			'test' => array(0 => self::REQUIRED, self::EXPRESSION)
		);

		switch($node->getName())
		{
			case 'if':
				if(!$node->hasAttributes())
				{
					$this->parseCondition($node, self::ALLOW_TAG_FORM | self::ALLOW_ATTRIBUTE_FORM | self::ALLOW_LONG_IF, array($this,'_localCallback'));
					return;
				}
				$this->_extractAttributes($node, $params);
				$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, ' if('.$params['test'].'){ ');
				$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, ' } ');
				$node->sort(array('*' => 0, 'opt:else-if' => 1, 'opt:else' => 2));
				$this->_process($node);
				break;
			case 'else-if':
				if($node->getParent()->getName() == 'if')
				{
					$this->_extractAttributes($node, $params);
					$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } elseif('.$params['test'].'){ ');
					$this->_process($node);
				}
				else
				{
					throw new Opt_InstructionInvalidParent_Exception($node->getXmlName(), 'opt:if');
				}
				break;
			case 'else':
				if($node->getParent()->getName() == 'if')
				{
					$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, '}else{ ');
					$this->_process($node);
				}
				else
				{
					throw new Opt_InstructionInvalidParent_Exception($node->getXmlName(), 'opt:if');
				}
				break;
		}
	} // end processNode();

	/**
	 * Checks if attribute is deprecated and needs migration.
	 * @param Opt_Xml_Attribute $attr Attribute to migrate
	 * @return boolean If attribute needs migration
	 */
	public function attributeNeedMigration(Opt_Xml_Attribute $attr)
	{
		$name = $attr->getXmlName();
		if(in_array($name, $this->_deprecatedAttributes))
		{
			return true;
		}
		return false;
	} // end attributeNeedMigration();

	/**
	 * Migrates the opt:if (and its derivatives) attributes.
	 * @internal
	 * @param Opt_Xml_Attribute $attr The recognized attribute.
	 * @return Opt_Xml_Attribute Migrated attribute
	 */
	public function migrateAttribute(Opt_Xml_Attribute $attr)
	{
		switch($attr->getName())
		{
			case 'on':
				$attr->setName('omit-tag');
				break;
		}
		return $attr;
	} // end migrateAttribute();

	/**
	 * Processes the opt:if and opt:omit-tag attributes.
	 * @internal
	 * @param Opt_Xml_Node $node The node with the attribute
	 * @param Opt_Xml_Attribute $attr The recognized attribute.
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		// TODO: Add opt:omit-tag implementation, changed opt:on->opt:omit-tag, it should work as before
		switch($attr->getName())
		{
			case 'omit-tag':
				if(!$this->_compiler->isNamespace($node->getNamespace()))
				{
					$expr = $this->_compiler->compileExpression((string)$attr, false, Opt_Compiler_Class::ESCAPE_OFF);

					$node->addBefore(Opt_Xml_Buffer::TAG_OPENING_BEFORE, ' $_tag_'.$this->_cnt.' = false; if(!('.$expr[0].')){ $_tag_'.$this->_cnt.' = true; ');
					$node->addAfter(Opt_Xml_Buffer::TAG_OPENING_AFTER, ' } ');
					$node->addBefore(Opt_Xml_Buffer::TAG_CLOSING_BEFORE, ' if($_tag_'.$this->_cnt.' === true){ ');
					$node->addAfter(Opt_Xml_Buffer::TAG_CLOSING_AFTER, ' } ');
					$this->_cnt++;
					break;
				}
			case 'if':
				// opt:if added to an section must be handled differently.
				// Wait for the section processor and add the condition in the postprocessing.
				if($this->_compiler->isInstruction($node->getXmlName()) instanceof Opt_Instruction_BaseSection)
				{
					$attr->set('postprocess', true);
					return;
				}

				$expr = $this->_compiler->compileExpression((string)$attr, false, Opt_Compiler_Class::ESCAPE_OFF);

				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if('.$expr[0].'){ ');
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
		}
	} // end processAttribute();

	/**
	 * Finalizes the processing of the opt:if and opt:omit-tag attributes.
	 * @internal
	 * @param Opt_Xml_Node $node The node with the attribute
	 * @param Opt_Xml_Attribute $attr The recognized attribute.
	 */
	public function postprocessAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$expr = $this->_compiler->compileExpression((string)$attr, false, Opt_Compiler_Class::ESCAPE_OFF);

		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if('.$expr[0].'){ ');
		$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
	} // end postprocessAttribute();

	/**
	 * The public API for the NEW-IF syntax, allowing to use it in other instructions.
	 *
	 * @param Opt_Xml_Node $node The scanned node.
	 * @param integer $flags The parsing flags
	 * @param callback $conditionCallback The callback for checking every condition.
	 */
	public function parseCondition(Opt_Xml_Node $node, $flags, $conditionCallback = null)
	{
		$first = true;
		$reallyFirst = true;
		$else = false;

		// For the long if compilation...
		$firstNode = 0;
		$group = array();
		$groupNum = 0;
		$prevType = 0;
		foreach($node as $subnode)
		{
			if($flags & self::ALLOW_LONG_IF)
			{
				// For long IF-s, we skip the whitespaces only.
				if($subnode instanceof Opt_Xml_Text && $subnode->isWhitespace())
				{
					continue;
				}
			}
			else
			{
				// Skip TEXT blocks.
				if($subnode instanceof Opt_Xml_Text)
				{
					continue;
				}
			}

			// Parse the tags.
			if($subnode instanceof Opt_Xml_Element)
			{
				// Test the tag forms.
				if($flags & self::ALLOW_TAG_FORM)
				{
					switch($subnode->getXmlName())
					{
						case 'opt:condition':
							$params = array(
								'test' => array(0 => self::REQUIRED, self::EXPRESSION)
							);
							$this->_extractAttributes($subnode, $params);
							if($first == true)
							{
								// For the long if compilation purposes
								if($reallyFirst)
								{
									$firstNode = $subnode;
								}
								// Now the normal code...
								$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'if('.$params['test'].'){ ');
								$first = false;
								$reallyFirst = false;
							}
							else
							{
								$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'elseif('.$params['test'].'){ ');
							}
							$subnode->addAfter(Opt_Xml_Buffer::TAG_AFTER, '} ');
							call_user_func($conditionCallback, $subnode, false);

							$prevType = 0;
							$group[] = $subnode;
							continue 2;
						case 'opt:else':
							if($reallyFirst)
							{
								throw new Opt_ElseCannotBeFirst_Exception();
							}
							if($else)
							{
								throw new Opt_InstructionTooManyItems_Exception('opt:else', 'conditional instruction');
							}
							if(!$first)
							{
								$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'else{ ');
								$subnode->addAfter(Opt_Xml_Buffer::TAG_AFTER, '} ');
							}
							$else = true;
							call_user_func($conditionCallback, $subnode, false);

							$prevType = 0;
							$group[] = $subnode;
							continue 2;
					}
				}
				// Now test the attribute forms.
				if($flags & self::ALLOW_ATTRIBUTE_FORM)
				{
					if(($attr = $subnode->getAttribute('opt:condition')) !== null)
					{
						$subnode->removeAttribute($attr);
						$test = $this->_compiler->parseExpression($attr->getValue(), null, Opt_Compiler_Class::ESCAPE_OFF);
						if($first == true)
						{
							// For the long if compilation purposes
							if($reallyFirst)
							{
								$firstNode = $subnode;
							}
							// Now the normal code...
							$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'if('.$test['bare'].'){ ');
							$first = false;
							$reallyFirst = false;
						}
						else
						{
							$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'elseif('.$test['bare'].'){ ');
						}
						$subnode->addAfter(Opt_Xml_Buffer::TAG_AFTER, '} ');
						call_user_func($conditionCallback, $subnode, false);

						$prevType = 0;
						$group[] = $subnode;
						continue;
					}
					elseif(($attr = $subnode->getAttribute('opt:else')) !== null)
					{
						$subnode->removeAttribute($attr);
						if($reallyFirst)
						{
							throw new Opt_ElseCannotBeFirst_Exception();
						}
						if($else)
						{
							throw new Opt_InstructionTooManyItems_Exception('opt:else', 'conditional instruction');
						}
						if(!$first)
						{
							$subnode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'else{ ');
							$subnode->addAfter(Opt_Xml_Buffer::TAG_AFTER, '} ');
						}
						$else = true;
						call_user_func($conditionCallback, $subnode, false);

						$prevType = 0;
						$group[] = $subnode;
						continue;
					}
				}
				// If we are here, we have a long if.
				$prevType++;
				call_user_func($conditionCallback, $subnode, true);
				if($prevType == 1)
				{
					if(!($flags & self::ALLOW_LONG_IF))
					{
						throw new Opt_NotSupported_Exception('long if', 'cannot use here');
					}
					// We haven't reached the first node yet.
					if($firstNode === 0)
					{
						continue;
					}
					elseif($firstNode !== null)
					{
						$firstNode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' $__if_'.$this->_cnt.' = 0; ');
						$firstNode = null;
					}
					$groupNum++;
					// Add the state changers to the condition blocks.
					foreach($group as $it)
					{
						$it->addBefore(Opt_Xml_Buffer::TAG_AFTER,' $__if_'.$this->_cnt.' = 1; ');
					}
					// If this is not the first group, surround it with an extra condition.
					if($groupNum > 1)
					{
						$group[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE,' if($__if_'.$this->_cnt.' == 0){ ');
						$group[sizeof($group)-1]->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
					}
					$group = array();
					$first = true;
				}
				
			}
		}
		// Process the final group.
		if($groupNum >= 1)
		{
			$group[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE,' if($__if_'.$this->_cnt.' == 0){ ');
			$group[sizeof($group)-1]->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
			foreach($group as $it)
			{
				$it->addBefore(Opt_Xml_Buffer::TAG_AFTER,' $__if_'.$this->_cnt.' = 1; ');
			}
		}

		if($flags & self::FORCE_ELSE && !$else)
		{
			throw new Opt_TagMissing_Exception('opt:else', 'conditional instruction');
		}
		$this->_cnt++;
	} // end parseCondition();

	/**
	 * The local callback for a single conditional block. The method
	 * is used by parseCondition().
	 *
	 * @param Opt_Xml_Node $subnode The subnode with the conditional block.
	 * @param boolean $longForm Is the subnode a delimiter in long-if?
	 */
	private function _localCallback(Opt_Xml_Node $subnode, $longForm)
	{
		$subnode->set('hidden', false);
		$this->_process($subnode);
	} // end _localCallback();

} // end Opt_Instruction_If;
