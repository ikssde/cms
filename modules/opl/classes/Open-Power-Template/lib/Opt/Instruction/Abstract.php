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
 * The class provides the interface to create custom instruction
 * processors.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 */
abstract class Opt_Instruction_Abstract
{
	// Attribute types
	const STRING = 1;
	const NUMBER = 2;
	const ID = 3;
	const ID_EMP = 4;
	const BOOL = 5;
	const EXPRESSION = 6;
	const EXPRESSION_EXT = 7;

	const REQUIRED = 1;
	const OPTIONAL = 2;

	// Class fields
	/**
	 * The compiler object.
	 *
	 * @var Opt_Compiler_Class
	 */
	protected $_compiler;
	/**
	 * The main class object.
	 *
	 * @var Opt_Class
	 */
	protected $_tpl;

	/**
	 * The processor name (see getName() description for details).
	 * @var string
	 */
	protected $_name;

	/**
	 * The processing queue
	 * @internal
	 * @var SplQueue
	 */
	private $_queue = NULL;
	/**
	 * The list of registered instruction tags.
	 * @internal
	 * @var array
	 */
	private $_instructions = array();
	/**
	 * The list of registered instruction attributes.
	 * @internal
	 * @var array
	 */
	private $_attributes = array();
	/**
	 * The list of registered ambiguous tags.
	 * @internal
	 * @var array
	 */
	private $_ambiguous = array();

	/**
	 * Creates a new instruction processor for the specified compiler.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object.
	 */
	public function __construct(Opt_Compiler_Class $compiler)
	{
		$this->_compiler = $compiler;
		$this->_tpl = Opl_Registry::get('opt');

		$this->configure();
	} // end __construct();

	/**
	 * Frees the circular references.
	 */
	public function dispose()
	{
		$this->reset();
		$this->_tpl = null;
		$this->_compiler = null;
		$this->_queue = null;
	} // end dispose();

	/**
	 * Called during the processor initialization. It allows to define
	 * the list of instructions and attributes supported by this processor.
	 */
	public function configure()
	{
		/* null */
	} // end configure();

	/**
	 * Resets the processor state after compiling the template. The default
	 * implementation is empty.
	 */
	public function reset()
	{
		/* null */
	} // end reset();

	/**
	 * This method is called automatically for each XML element that the
	 * processor has registered, because of backwards compatibility mode is on.
	 * It can handle many instructions tags, and the default implementation
	 * redirects the migrating to the private user-created methods _migrateTagName
	 * for "opt:tagName".
	 *
	 * @param Opt_Xml_Node $node The node to be migrated.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$name = '_migrate'.ucfirst($node->getName());
		
		if(method_exists($this, $name))
		{
			$this->$name($node);
		}
	} // end migrateNode();

	/**
	 * This method is called automatically for each XML element that the
	 * processor has registered. It can handle many instructions tags, and
	 * the default implementation redirects the processing to the private
	 * user-created methods _processTagName for "opt:tagName".
	 *
	 * @param Opt_Xml_Node $node The node to be processed.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$name = '_process'.ucfirst($node->getName());
		$this->$name($node);
	} // end processNode();

	/**
	 * This method is called automatically for each XML element that the
	 * processor has registered during the postprocessing, if the instruction
	 * requested this by setting the "postprocess" variable to "true" in the
	 * node. It can handle many instructions tags, and
	 * the default implementation redirects the processing to the private
	 * user-created methods _postprocessTagName for "opt:tagName".
	 *
	 * @param Opt_Xml_Node $node The node to be postprocessed.
	 */
	public function postprocessNode(Opt_Xml_Node $node)
	{
		$name = '_postprocess'.ucfirst($node->getName());
		$this->$name($node);
	} // end postprocessNode();

	/**
	 * This method is called automatically for each XML attribute that the
	 * processor has registered. It can handle many attributes, and the
	 * default implementation redirects the processing to the private
	 * user-created methods _processAttrName for "opt:name" attribute.
	 *
	 * @param Opt_Xml_Node $node The node that contains the attribute.
	 * @param Opt_Xml_Attribute $attr The attribute to be processed.
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$name = '_processAttr'.ucfirst($attr->getName());
		$this->$name($node, $attr);
	} // end processAttribute();

	/**
	 * This method is called automatically for each XML attribute that the
	 * processor has registered during the postprocessing, if the instruction
	 * requested this by setting the "postprocess" variable to "true" in the
	 * attribute. It can handle many attributes, and the
	 * default implementation redirects the processing to the private
	 * user-created methods _postprocessAttrName for "opt:name" attribute.
	 *
	 * @param Opt_Xml_Node $node The node that contains the attribute.
	 * @param Opt_Xml_Attribute $attr The attribute to be processed.
	 */
	public function postprocessAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$name = '_postprocessAttr'.ucfirst($attr->getName());
		$this->$name($node, $attr);
	} // end postprocessAttribute();

	/**
	 * Processes the $system special variable call. OPT chooses the processor via
	 * the second part of the call. For example, if the processor's method getName()
	 * returns the name "foo", the variables "$system.foo.something" are be redirected
	 * to that processor. The method must return a valid PHP code that replaces the
	 * specified call.
	 *
	 * @param array $opt The $system special variable already splitted into array.
	 * @return string The output PHP code
	 */
	public function processSystemVar($opt)
	{
		/* null */
	} // end processSystemVar();

	/**
	 * Returns the processor name. The name should be a valid identifier
	 * (the first character must be a letter or underline, the next ones
	 * may also contain numbers). The name is read from the protected
	 * property $_name;
	 *
	 * @final
	 * @internal
	 * @return string The processor name
	 */
	final public function getName()
	{
		return $this->_name;
	} // end getName();

	/**
	 * Returns the queue of children to be processed for the recently
	 * processed node/attribute.
	 *
	 * @internal
	 * @return SplQueue The queue of nodes to process
	 */
	final public function getQueue()
	{
		$q = $this->_queue;
		$this->_queue = NULL;
		return $q;
	} // end getQueue();

	/**
	 * Returns the names of the XML instruction tags registered by this processor.
	 *
	 * @final
	 * @return array The list of registered instruction tags.
	 */
	final public function getInstructions()
	{
		return $this->_instructions;
	} // end getInstructions();

	/**
	 * Returns the names of the XML attributes registered by this processor.
	 *
	 * @final
	 * @internal
	 * @return array The list of registered instruction attributes.
	 */
	final public function getAttributes()
	{
		return $this->_attributes;
	} // end getAttributes();

	/**
	 * Returns the names of the XML ambiguous tags registered by this processor and
	 * their matchings.
	 *
	 * @final
	 * @internal
	 * @return array The list of registered ambiguous tags.
	 */
	final public function getAmbiguous()
	{
		return $this->_ambiguous;
	} // end getAmbiguous();

	/**
	 * Adds the children of the specified node to the queue of the currently
	 * parsed element. It allows them to be processed.
	 *
	 * @final
	 * @internal
	 * @param Opt_Xml_Node $node The node to process.
	 */
	final protected function _process(Opt_Xml_Node $node)
	{
		if($this->_queue === null)
		{
			$this->_queue = new SplQueue;
		}
		if($node->hasChildren())
		{
			foreach($node as $child)
			{
				$this->_queue->enqueue($child);
			}
		}
	} // end _process();

	/**
	 * Allows to define the instructions parsed by this processor.
	 * It is intended to be used in configure() method.
	 *
	 * @final
	 * @param string|array $list The name of a single instruction or list of instructions.
	 */
	final protected function _addInstructions($list)
	{
		if(is_array($list))
		{
			$this->_instructions = array_merge($this->_instructions, $list);
		}
		else
		{
			$this->_instructions[] = $list;
		}
	} // end _addInstructions();

	/**
	 * Allows to define the attributes parsed by this processor.
	 * It is intended to be used in configure() method.
	 *
	 * @final
	 * @param string|array $list The name of a single attribute or list of attributes.
	 */
	final protected function _addAttributes($list)
	{
		if(is_array($list))
		{
			$this->_attributes = array_merge($this->_attributes, $list);
		}
		else
		{
			$this->_attributes[] = $list;
		}
	} // end _addAttributes();

	/**
	 * Allows to define the ambiguous tags matched by this instruction. An
	 * ambiguous tag can be handled by different processors, depending on
	 * their direct or indirect parent. It means that the programmer must also
	 * specify the parent instruction tag that will be the basis to redirect
	 * particular nodes to this processor.
	 *
	 * @final
	 * @param array $list The associative list of ambiguous tags and their mappings.
	 */
	final protected function _addAmbiguous(array $list)
	{
		foreach($list as $item => $matching)
		{
			$this->_ambiguous[$item] = $matching;
		}
	} // end _addAmbiguous();

	/**
	 * This helper method is the default instruction attribute handler in OPT.
	 * It allows to parse the list of attributes using the specified rules.
	 * The attribute configuration is passed as a second argument by reference,
	 * and OPT returns the compiled attribute values in the same way.
	 *
	 * If the attribute specification contains "__UNKNOWN__" element, the node
	 * may contain an undefined number of attributes. The undefined attributes
	 * must match to the rules in "__UNKNOWN__" element and are returned by the
	 * method as a separate array. For details, see the OPT user manual.
	 *
	 * @final
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Element $subitem The scanned XML element.
	 * @param array &$config The reference to the attribute configuration
	 * @param boolean $allowWith Do we look for opt:with special instruction?
	 * @return array|Null The list of undefined attributes, if "__UNKNOWN__" is set.
	 */
	final protected function _extractAttributes(Opt_Xml_Element $subitem, array &$config, $allowWith = false)
	{
		$required = array();
		$optional = array();
		$unknown = null;
		// Decide, what is what.
		foreach($config as $name => &$data)
		{
			if(!isset($data[3]))
			{
				$data[3] = null;
			}
			if($name == '__UNKNOWN__')
			{
				$unknown = &$data;
			}
			elseif($data[0] == self::REQUIRED)
			{
				$required[$name] = &$data;
			}
			elseif($data[0] == self::OPTIONAL)
			{
				$optional[$name] = &$data;
			}
		}
		$config = array();
		$return = array();
		$exprEngines = $this->_compiler->getExpressionEngines();

		// Look for opt:with
		if($allowWith === true)
		{
			$extra = $subitem->getElementsByTagName('opt', 'with');

			switch(sizeof($extra))
			{
				case 0:
					$attrList = $subitem->getAttributes(false);
					break;
				case 1:
					$attrList = array_merge(
						$subitem->getAttributes(false),
						$extra[0]->getAttributes(false)
					);
					break;
				default:
					throw new Opt_Instruction_Exception('Too many opt:with elements in '.$subitem->getXmlName());
			}
		}
		else
		{
			$attrList = $subitem->getAttributes(false);
		}

		// Parse required attributes
		foreach($required as $name => &$data)
		{
			$ok = false;
			if(isset($attrList[$name]))
			{
				$aname = $name;
				$ok = true;
			}
			elseif(($data[1] == self::EXPRESSION || $data[1] == self::EXPRESSION_EXT) && $this->_tpl->backwardCompatibility == true)
			{
				// DEPRECATED: Legacy code for compatibility with OPT 2.0
				foreach($exprEngines as $eeName => $eeValue)
				{
					if(isset($attrList[$eeName.':'.$name]))
					{
						$aname = $eeName.':'.$name;
						$data[3] = $eeName;
						$ok = true;
						break;
					}
				}
			}
			if(!$ok)
			{
				throw new Opt_AttributeNotDefined_Exception($name, $subitem->getXmlName());
			}

			$config[$name] = $this->_extractAttribute($subitem, $attrList[$aname], $data[1], $data[3]);
			unset($attrList[$aname]);
		}

		// Parse optional attributes
		foreach($optional as $name => &$data)
		{
			$ok = false;
			if(isset($attrList[$name]))
			{
				$aname = $name;
				$ok = true;
			}
			elseif(($data[1] == self::EXPRESSION || $data[1] == self::EXPRESSION_EXT) && $this->_tpl->backwardCompatibility == true)
			{
				// DEPRECATED: Legacy code for compatibility with OPT 2.0
				foreach($exprEngines as $eeName => $eeValue)
				{
					if(isset($attrList[$eeName.':'.$name]))
					{
						$aname = $eeName.':'.$name;
						$data[3] = $eeName;
						$ok = true;
						break;
					}
				}
			}
			if(!$ok)
			{
				// We can't use isset() because the default data might be "NULL"
				if(!array_key_exists(2, $data))
				{
					throw new Opt_APIMissingDefaultValue_Exception($name, $subitem->getXmlName());
				}
				$config[$name] = $data[2];
				continue;
			}

			$config[$name] = $this->_extractAttribute($subitem, $attrList[$aname], $data[1], $data[3]);
			unset($attrList[$aname]);
		}
		// The remaining tags must be processed using $unknown rule, however it
		// must be defined.
		if($unknown !== null)
		{
			$type = $unknown[1];
			$exprType = $unknown[3];
			foreach($attrList as $name => $attr)
			{

				if($this->_compiler->isNamespace($attr->getNamespace()))
				{
					continue;
				}
				$return[$name] = $this->_extractAttribute($subitem, $attr, $type, $exprType);
			}
		}
		return $return;
	} // end _extractAttributes();

	/**
	 * Tries to extract a single attribute, using the specified value type. The validation
	 * errors are reported as exceptions.
	 *
	 * @final
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Element $item The scanned XML element.
	 * @param Opt_Xml_Attribute $attr The parsed attribute
	 * @param int $type The requested value type.
	 * @return mixed The extracted attribute value
	 */
	final private function _extractAttribute(Opt_Xml_Element $item, Opt_Xml_Attribute $attr, $type, $exprType = null)
	{
		$value = (string)$attr;
		switch($type)
		{
			// An identifier, but with empty values allowed.
			case self::ID_EMP:
				if($value == '')
				{
					return $value;
				}
			// An identifier
			case self::ID:
				if(!preg_match('/^[a-zA-Z0-9\_\.]+$/', $value))
				{
					throw new Opt_Instruction_Exception('Invalid type for the attribute "'.$attr->getXmlName().'" in '.$item->getXmlName().': identifier expected.');
				}
				return $value;
			// A number
			case self::NUMBER:
				if(!preg_match('/^\-?([0-9]+\.?[0-9]*)|(0[xX][0-9a-fA-F]+)$/', $value))
				{
					throw new Opt_Instruction_Exception('Invalid type for the attribute "'.$attr->getXmlName().'" in '.$item->getXmlName().': numeric value expected.');
				}
				return $value;
			// Boolean value: "yes" or "no"
			case self::BOOL:
				if($value != 'yes' && $value != 'no')
				{
					throw new Opt_Instruction_Exception('Invalid type for the attribute "'.$attr->getXmlName().'" in '.$item->getXmlName().': "yes" or "no" expected.');
				}
				return ($value == 'yes');
			// A string packed into PHP expression. Can be switched to EXPRESSION.
			case self::STRING:
				return $value;
				break;
			// An OPT expression.
			case self::EXPRESSION:
			case self::EXPRESSION_EXT:
				if(strlen(trim($value)) == 0)
				{
					throw new Opt_Instruction_Exception('Yhe attribute "'.$attr->getXmlName().'" in '.$item->getXmlName().' is empty.');
					throw new Opt_AttributeEmpty_Exception($attr->getXmlName(), $item->getXmlName());
				}
			//	if(preg_match('/^([a-zA-Z0-9\_]{2,})\:([^\:].*)$/', $value, $found))
			//	{
			//		$result = $this->_compiler->parseExpression($found[2], $found[1]);
			//	}
			//	else
			//	{
					$result = $this->_compiler->parseExpression($value, $exprType);
			//	}

				if($type == self::EXPRESSION_EXT)
				{
					return $result;
				}
				elseif($result['type'] == Opt_Expression_Interface::ASSIGNMENT)
				{
					throw new Opt_Instruction_Exception('Cannot use assignment operator in expression '.$value);
				}
				return $result['bare'];
		}
	} // end _extractAttribute();
} // end Opt_Instruction_Abstract;