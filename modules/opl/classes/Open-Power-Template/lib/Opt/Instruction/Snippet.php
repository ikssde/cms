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
 * The snippet instruction processor.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Snippets
 */
class Opt_Instruction_Snippet extends Opt_Instruction_Abstract
{
	/**
	 * The processor name required by the parent.
	 * @internal
	 * @var string
	 */
	protected $_name = 'snippet';
	/**
	 * The list of available snippets.
	 * @internal
	 * @var array
	 */
	protected $_snippets = array();

	/**
	 * The currently inserted snippet stack used for detecting the infinite recursion.
	 * @internal
	 * @var SplStack
	 */
	protected $_current = array();

	/**
	 * Array contains deprecated attributes.
	 * @var array
	 */
	protected $_deprecatedAttributes = array();

	/**
	 * Array contains deprecated instructions.
	 * @var array
	 */
	protected $_deprecatedInstructions = array('opt:insert');

	/**
	 * Configures the instruction processor.
	 *
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:snippet', 'opt:use', 'opt:parent'));
		$this->_addAttributes(array('opt:use'));
		if($this->_tpl->backwardCompatibility)
		{
			$this->_addAttributes($this->_deprecatedAttributes);
			$this->_addInstructions($this->_deprecatedInstructions);
		}
	} // end configure();

	/**
	 * Resets the processor after the finished compilation and frees the
	 * memory taken by the snippets.
	 *
	 * @internal
	 */
	public function reset()
	{
		foreach($this->_snippets as &$snippetList)
		{
			foreach($snippetList as $snippet)
			{
				$snippet->dispose();
			}
		}
		$this->_snippets = array();
		$this->_current = new SplStack;
	} // end reset();

	/**
	 * Changes opt:insert to opt:use.
	 * @internal
	 * @param Opt_Xml_Node $node The node to migrate.
	 * @return Opt_Xml_Node The migrated attribute
	 */
	public function _migrateInsert(Opt_Xml_Node $node)
	{
		$node->setName('use');

		if($node->hasAttribute('ignoredefault'))
		{
			$attribute = $node->getAttribute('ignoredefault');
			$attribute->setName('ignore-default');

			$node->removeAttribute('ignoredefault');
			$node->addAttribute($attribute);
		}

		return $node;
	} // end _migrateInsert();

	/**
	 * Processes the opt:use attribute.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node The node the attribute is added to
	 * @param Opt_Xml_Attribute $attr The found attribute.
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		if(isset($this->_macros[$attr->getValue()]))
		{
			$this->_current->snippet($attr->getValue());
			$macro = &$this->_macros[$attr->getValue()];

			// Move all the stuff to the fake node.
			if($node->hasChildren())
			{
				$newNode = new Opt_Xml_Element('opt:_');
				$newNode->moveChildren($node);

				$size = sizeof($macro);
				$macro[$size] = $newNode;

				$attr->set('macroObj', $macro);
				$attr->set('size', $size);
			}
			$node->removeChildren();

			// Process the macros
			$node->set('escaping', $this->_compiler->get('escaping'));
			$this->_compiler->set('escaping', $macro[0]->get('escaping'));
			foreach($macro[0] as $subnode)
			{
				$node->appendChild(clone $subnode);
			}

			$node->set('call:use', $attr->getValue());
			$attr->set('postprocess', true);
		}
	} // end processAttribute();

	/**
	 * A postprocessing routine for opt:use
	 * @internal
	 * @param Opt_Xml_Node $node
	 * @param Opt_Xml_Attribute $attr
	 */
	public function postprocessAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$info = $this->_current->pop();

		// This needs to be fixed.
		if(isset($info['size']))
		{
			$macro = $info['snippetObj'];
			unset($macro[$info['size']]);
		}
		// Restore the original escaping state
		$this->_compiler->set('escaping', $node->get('escaping'));
	} // end postprocessAttribute();

	/**
	 * Processes the opt:snippet element.
	 * @internal
	 * @param Opt_Xml_Element $node The found snippet.
	 */
	public function _processSnippet(Opt_Xml_Element $node)
	{
		$params = array(
			'name' => array(0 => self::REQUIRED, self::ID),
			'__UNKNOWN__' => array(0 => self::OPTIONAL, self::STRING)
		);
		$arguments = $this->_extractAttributes($node, $params);

		// Test the snippet arguments
		if(isset($this->_arguments[$params['name']]))
		{
			if($this->_arguments[$params['name']] != $arguments)
			{
				throw new Exception('Invalid snippet arguments');
			}
		}
		else
		{
			$this->_arguments[$params['name']] = $arguments;
		}

		// Assign this snippet
		if(!isset($this->_snippets[$params['name']]))
		{
			$this->_snippets[$params['name']] = array(0 => $node);
			$current = 0;
		}
		else
		{
			$current = sizeof($this->_snippets[$params['name']]);

			$this->_snippets[$params['name']][] = $node;
		}
		if($node->getParent()->removeChild($node) == 0)
		{
			throw new Opl_Debug_Exception();
		}
		// Remember the template state of escaping for this snippet.
		// This is necessary to make per-template escaping work with
		// the inheritance.
		$node->set('escaping', $this->_compiler->get('escaping'));

		// Link "opt:parent" with the parent
		$parentTags = $node->getElementsByTagNameNS('opt', 'parent');
		foreach($parentTags as $parent)
		{
			$parent->set('snippetName', $params['name']);
			$parent->set('snippetId', $current + 1);
		}
	} // end _processSnippet();

	/**
	 * Processes the opt:parent element.
	 * @internal
	 * @param Opt_Xml_Element $node The found element.
	 */
	public function _processParent(Opt_Xml_Element $node)
	{
		$n = $node->get('snippetName');
		$i = $node->get('snippetId');
		// If there is a parent, append it here and execute.
		if(isset($this->_snippets[$n][$i]))
		{
			$node->set('escaping', $this->_compiler->get('escaping'));
			$node->set('single', false);
			$this->_compiler->set('escaping', $this->_snippets[$n][$i]->get('escaping'));
			$master = $node->getParent();
			foreach($this->_snippets[$n][$i] as $subnode)
			{
				$master->insertBefore($cloned = clone $subnode, $node);
				$this->_enqueue($cloned);
			}
			$master->removeChild($node);
			$node->set('postprocess', true);
			$this->_process($node);
		}
	} // end _processParent();

	/**
	 * A postprocessing routine for opt:parent
	 * @internal
	 * @param Opt_Xml_Element $node The found element
	 */
	public function _postprocessParent(Opt_Xml_Element $node)
	{
		$this->_compiler->set('escaping', $node->get('escaping'));
	} // end _postprocessParent();

	/**
	 * Processes the opt:use element.
	 * @internal
	 * @param Opt_Xml_Element $node The found element
	 */
	public function _processUse(Opt_Xml_Element $node)
	{
		// A support for the dynamically chosen part captured by opt:capture
		if($node->getAttribute('captured') !== NULL)
		{
			$params = array(
				'captured' => array(0 => self::REQUIRED, self::EXPRESSION)
			);
			$this->_extractAttributes($node, $params);
			if($node->hasChildren())
			{
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'if(!isset(self::$_capture['.$params['captured'].'])){ ');
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, '} else { echo self::$_capture['.$params['captured'].']; } ');
				$this->_process($node);
			}
			else
			{
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'if(isset(self::$_capture['.$params['captured'].'])){ echo self::$_capture['.$params['captured'].']; } ');
			}
		}
		elseif($node->getAttribute('procedure') !== null)
		{
			// Calling the procedure.
			$params = array(
				'procedure' => array(0 => self::REQUIRED, self::EXPRESSION_EXT),
				'__UNKNOWN__' => array(0 => self::OPTIONAL, self::EXPRESSION)
			);
			$arguments = $this->_extractAttributes($node, $params);
			$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $this->_compiler->processor('procedure')->callProcedure($params['procedure'], $arguments));
		}
		else
		{
			// Snippet insertion
			$params = array(
				'snippet' => array(0 => self::REQUIRED, self::ID),
				'ignore-default' => array(0 => self::OPTIONAL, self::BOOL, false),
				'__UNKNOWN__' => array(0 => self::OPTIONAL, self::STRING)
			);
			$arguments = $this->_extractAttributes($node, $params);
			$this->useSnippet($node, $params['snippet'], $arguments, $params['ignore-default']);
			$this->_process($node);
		}
	} // end _processUse();

	/**
	 * Postprocesses the opt:use element.
	 * @internal
	 * @param Opt_Xml_Element $node The found element.
	 */
	public function _postprocessUse(Opt_Xml_Element $node)
	{
		$this->postuseSnippet($node);
	} // end _postprocessUse();

	/**
	 * The public API function allowing to put a snippet into the specified
	 * node. The programmer is obliged to call _process() method on the node
	 * with the snippet and terminate it in postprocessing by postuseSnippet().
	 * @param Opt_Xml_Node $node The node to put the snippet to
	 * @param string $snippetName The snippet name
	 * @param array $arguments The snippet arguments
	 * @param boolean $ignoreDefault Ignore the default content?
	 * @param boolean $predefinedArguments Are the arguments predefined and do they need parsing?
	 */
	public function useSnippet(Opt_Xml_Node $node, $snippetName, array $arguments, $ignoreDefault = false, $predefinedArguments = false)
	{
		if($this->isUsed($snippetName))
		{
			$data = array($snippetName);
			foreach($this->_current as $info)
			{
				$data[] = $info['name'];
			}
			$err = new Opt_SnippetRecursion_Exception($snippetName);
			throw $err->setData($data);
		}

		$snippetBlock = array('name' => $snippetName, 'arguments' => array());

		foreach($this->_current as $it)
		{
			if($it == $snippetName)
			{
				$this->_current->push($snippetName);
				$err = new Opt_SnippetRecursion_Exception($snippetName);
				throw $err->setData($this->_current);
			}
		}
		if(isset($this->_snippets[$snippetName]))
		{
			// Testing the arguments.
			$startCode = '';
			$i = 0;
			if(!$predefinedArguments)
			{
				foreach($this->_arguments[$snippetName] as $name => $suggestedValue)
				{
					$process = true;
					if($suggestedValue == 'required' && !isset($arguments[$name]))
					{
						throw new Opt_SnippetArgumentNotDefined_Exception($name, $snippetName);
					}
					elseif(!isset($arguments[$name]))
					{
						$this->_compiler->setConversion('##simplevar_'.$name, $suggestedValue);
						$snippetBlock['arguments'][$name] = -1;
						$i++;
						$process = false;
					}

					if($process)
					{
						// We must parse an OPT expression in order to get to know what we have
						// here...
						$expression = $this->_compiler->parseExpression($arguments[$name], null, Opt_Compiler_Class::ESCAPE_OFF);
						if($expression['type'] == Opt_Expression_Interface::SINGLE_VAR)
						{
							$this->_compiler->setConversion('##rawvar_'.$name, $expression['bare']);
							$snippetBlock['arguments'][$name] = -1;
						}
						else
						{
							$startCode .= '$__snippet_'.$name.'_'.$i.' = '.$expression['bare'].'; ';
							$this->_compiler->setConversion('##rawvar_'.$name, '$__snippet_'.$name.'_'.$i);
							$snippetBlock['arguments'][$name] = $i;
						}
						$i++;
						$process = false;
					}
				}
			}
			else
			{
				foreach($this->_arguments[$snippetName] as $name => $suggestedValue)
				{
					if($suggestedValue == 'required' && !isset($arguments[$name]))
					{
						throw new Opt_SnippetArgumentNotDefined_Exception($name, $snippetName);
					}
					elseif(!isset($arguments[$name]))
					{
						$this->_compiler->setConversion('##rawvar_'.$name, $suggestedValue);
					}
					else
					{
						$this->_compiler->setConversion('##rawvar_'.$name, $arguments[$name]);
					}
					$snippetBlock['arguments'][$name] = -1;
				}
			}

			// OK, now we can deal with the snippet itself.
			$snippet = &$this->_snippets[$snippetName];

			// Move all the stuff to the fake node.
			if($node->hasChildren() && $ignoreDefault == false)
			{
				$newNode = new Opt_Xml_Element('opt:_');
				$newNode->set('escaping', $this->_compiler->get('escaping'));
				$newNode->moveChildren($node);
				$size = sizeof($snippet);
				$snippet[$size] = $newNode;
				$snippetBlock['insertSize'] = $size;
			}
			// We must do the cleaning for the inserted node.
			$node->removeChildren();

			// Process the snippets
			$snippetBlock['escaping'] = $this->_compiler->get('escaping');
			$this->_compiler->set('escaping', $snippet[0]->get('escaping'));

			foreach($snippet[0] as $subnode)
			{
				$node->appendChild(clone $subnode);
			}

			if(strlen($startCode) > 0)
			{
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $startCode);
			}

			$this->_current->push($snippetBlock);
			$node->set('insertSnippet', $snippetName);
			$node->set('postprocess', true);
		}
	} // end useSnippet();

	/**
	 * Terminates snippet insertion.
	 *
	 * @param Opt_Xml_Node $node
	 */
	public function postuseSnippet(Opt_Xml_Node $node)
	{
		// Freeing the fake node, if necessary.
		$info = $this->_current->pop();

		// Freeing the fake node, if necessary.
		if(isset($info['insertSize']))
		{
			$this->_snippets[$info['name']][$info['insertSize']]->dispose();
			unset($this->_snippets[$info['name']][$info['insertSize']]);
		}

		// Clean the argument information
		$code = '';
		foreach($info['arguments'] as $name => $type)
		{
			if($type >= 0)
			{
				$code .= 'unset($__snippet_'.$name.'_'.$type.'); ';
			}
			$this->_compiler->unsetConversion('##rawvar_'.$name);
		}

		if(strlen($code) > 0)
		{
			$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, $code);
		}

		// Restore the original escaping state
		$this->_compiler->set('escaping', $info['escaping']);
	} // end postuseSnippet();

	/**
	 * Returns the list of arguments of the specified snippet.
	 *
	 * @throws Opt_SnippetNotFound_Exception
	 * @param string $snippetName The snippet name.
	 * @return array
	 */
	public function getArguments($snippetName)
	{
		if(!isset($this->_arguments[$snippetName]))
		{
			throw new Opt_SnippetNotFound_Exception($snippetName);
		}
		return $this->_arguments[$snippetName];
	} // end getArguments();

	/**
	 * Returns true, if the snippet with the given name is defined.
	 * @param string $name The snippet name
	 * @return boolean
	 */
	public function isSnippet($name)
	{
		return isset($this->_snippets[$name]);
	} // end isSnippet();

	/**
	 * Checks if we have already used the specified dmo[[ry to avoid recursive
	 * linking.
	 *
	 * @param string $name The snippet name
	 * @return boolean
	 */
	public function isUsed($name)
	{
		foreach($this->_current as $info)
		{
			if($info['name'] == $name)
			{
				return true;
			}
		}
		return false;
	} // end isUsed();
} // end Opt_Instruction_Snippet;
