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
 * The processor for opt:component instruction. Note that compiler
 * DEPENDS on this processor, using its API in order to provide the
 * support for the statically deployed components.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Components
 */
class Opt_Instruction_Component extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'component';
	/**
	 * The opt:component counter used to generate unique variable names.
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
		$this->_addInstructions(array('opt:component', 'opt:on-event', 'opt:display', 'opt:inject'));
		$this->_stack = new SplStack;
	} // end configure();

	/**
	 * Migrates the opt:component node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:component, opt:on-event and opt:display nodes.
	 * 
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		switch($node->getName())
		{
			case 'component':
				$node->set('component', true);
				// Undefined component processing
				$params = array(
					'from' => array(self::REQUIRED, self::EXPRESSION, null),
					'datasource' => array(self::OPTIONAL, self::EXPRESSION, null),
					'template' => array(self::OPTIONAL, self::ID, null),
					'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null)
				);
				$vars = $this->_extractAttributes($node, $params);
				$this->_stack->push($params['from']);

				$mainCode = ' if(is_object('.$params['from'].') && '.$params['from'].' instanceof Opt_Component_Interface){ '.$params['from'].'->setView($this); ';
				if($params['datasource'] !== null)
				{
					$mainCode .= $params['from'].'->setDatasource('.$params['datasource'].'); ';
				}

				$mainCode .= $this->_commonProcessing($node, $params['from'], $params, $vars);

				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE,  $mainCode);
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
				break;
			case 'on-event':
				if($this->_stack->count() == 0)
				{
					throw new Opt_Instruction_Exception('Component error: invalid use of "opt:on-event": no active component.');
				}

				$tagParams = array(
					'name' => array(self::REQUIRED, self::EXPRESSION)
				);

				$this->_extractAttributes($node, $tagParams);
				$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, ' if('.$this->_stack->top().'->processEvent('.$tagParams['name'].')){ ');
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
				$this->_process($node);
				break;

			case 'display':
				if($this->_stack->count() == 0)
				{
					throw new Opt_Instruction_Exception('Component error: invalid use of "opt:display": no active component.');
				}
				$node->set('hidden', false);
				$node->removeChildren();
				// The opt:display attributes must be packed into array and sent
				// to Opt_Component_Interface::display()
				$subCode = '';
				if($node->hasAttributes())
				{
					$params = array(
						'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null)
					);
					$vars = $this->_extractAttributes($node, $params);
					$subCode = 'array(';
					foreach($vars as $name => $value)
					{
						$subCode .= '\''.$name.'\' => '.$value.',';
					}
					$subCode .= ')';
				}
				$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $this->_stack->top().'->display('.$subCode.'); ');
				break;
			case 'inject':
				if($this->_stack->count() == 0)
				{
					throw new Opt_Instruction_Exception('Component error: invalid use of "opt:inject": no active component.');
				}
				$code = 'function() use($ctx){ ';

				if($node->getAttribute('procedure') !== null)
				{
					$params = array(
						'procedure' => array(self::REQUIRED, self::EXPRESSION_EXT, null)
					);
					$this->_extractAttributes($node, $params);
					$code .= ' $args = func_get_args(); array_unshift($args, $ctx); '.PHP_EOL;
					$code .= $this->_compiler->processor('procedure')->callProcedure($params['procedure'], '$args', true).PHP_EOL;
					$code .= '}'.PHP_EOL;
					$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $this->_stack->top().'->setInjection('.$code.'); ');
				}
				else
				{
					// TODO: Implement snippet support!
					$params = array(
						'snippet' => array(self::REQUIRED, self::STRING, null)
					);
					$this->_extractAttributes($node, $params);
					$code .= ' $args = func_get_args(); '.PHP_EOL;
					$snippetArgs = $this->_compiler->processor('snippet')->getArguments($params['snippet']);

					$i = 0;
					foreach($snippetArgs as $name => &$value)
					{
						if($value == 'required')
						{
							$code .= 'if(!isset($args['.$i.'])){ throw new Opt_SnippetArgumentNotDefined_Exception(\''.$name.'\', \''.$params['snippet'].'\'); } '.PHP_EOL;
						}
						else
						{
							$code .= 'if(!isset($args['.$i.'])){ $args['.$i.'] = '.$value.'; } '.PHP_EOL;
						}
						$value = '$args['.($i++).']';
					}
					$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $this->_stack->top().'->setInjection('.$code);
					$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, ' }); ');
					$this->_compiler->processor('snippet')->useSnippet($node, $params['snippet'], $snippetArgs, false, true);
					$this->_process($node);
				}
				break;
		}
	} // end processNode();

	/**
	 * Finishes the processing of the opt:component node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function postprocessNode(Opt_Xml_Node $node)
	{
		if($node->getXmlName() == 'opt:inject')
		{
			$this->_compiler->processor('snippet')->postuseSnippet($node);
		}
		else
		{
			if(($attribute = $node->get('_componentTemplate')) !== null)
			{
				$this->_compiler->processor('snippet')->postprocessAttribute($node, $attribute);
			}
			$this->_stack->pop();
		}
	} // end postprocessNode();

	/**
	 * This method implements the publicly available code that generates
	 * a component support within an XML tag. By default, it is used by
	 * the compiler to support statically deployed components.
	 *
	 * @param Opt_Xml_Element $node The component tag
	 */
	public function processComponent(Opt_Xml_Element $node)
	{
		// Defined component processing
		$params = array(
			'datasource' => array(self::OPTIONAL, self::EXPRESSION, null),
			'template' => array(self::OPTIONAL, self::ID, null),
			'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null)
		);

		$vars = $this->_extractAttributes($node, $params);
		// Get the real class name
		$cn = '$_component_'.($this->_unique++);

		$this->_stack->push($cn);

		$class = $this->_compiler->component($node->getXmlName());

		// Check, if there are any conversions that may take control over initializing
		// the component object. We are allowed to capture only particular component
		// creation or all of them.
		if((($to = $this->_compiler->convert('##component_'.$class)) != '##component_'.$class))
		{
			$attributes = 'array(';
			foreach($vars as $name => $value)
			{
				$attributes .= '\''.$name.'\' => '.$value.', ';
			}
			$attributes .= ')';
			$ccode = str_replace(array('%CLASS%', '%TAG%', '%ATTRIBUTES%'), array($class, $node->getXmlName(), $attributes), $to);
		}
		elseif((($to = $this->_compiler->convert('##component')) != '##component'))
		{
			$attributes = 'array(';
			foreach($vars as $name => $value)
			{
				$attributes .= '\''.$name.'\' => '.$value.', ';
			}
			$attributes .= ')';
			$ccode = str_replace(array('%CLASS%', '%TAG%', '%ATTRIBUTES%'), array($class, $node->getXmlName(), $attributes), $to);
		}
		else
		{
			$ccode = 'new '.$class;
		}

		// Generate the initialization code
		$mainCode = $cn.' = '.$ccode.'; '.$cn.'->setView($this); ';
		if($params['datasource'] !== null)
		{
			$mainCode .= $cn.'->setDatasource('.$params['datasource'].'); ';
		}

		$mainCode .= $this->_commonProcessing($node, $cn, $params, $vars);
		$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE,  $mainCode);
	} // end processComponent();

	/**
	 * Finishes the public processing of the component.
	 *
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function postprocessComponent(Opt_Xml_Node $node)
	{
		if(($attribute = $node->get('_componentTemplate')) !== null)
		{
			$this->_compiler->processor('snippet')->postprocessAttribute($node, $attribute);
			$node->set('_componentTemplate', NULL);
		}
		$this->_stack->pop();
	} // end postprocessComponent();

	/**
	 * The common processing part of the dynamically and statically
	 * deployed components. Returns the compiled PHP code ready to
	 * be appended to the XML tag. The caller must generate a component
	 * variable name that will be used in the generated code to refer
	 * to the component object. Furthermore, he must pass the results
	 * of _extractAttributes() method: both the $params array and the
	 * returned values.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The node with the component data.
	 * @param string $componentVariable The PHP component variable name.
	 * @param array $params The array of standard component attributes.
	 * @param array $args The array of custom component attributes.
	 * @return string
	 */
	private function _commonProcessing(Opt_Xml_Element $node, $componentVariable, array $params, array $args)
	{
		// Common part of the component processing
		$set2 = array();
		if($params['template'] !== null)
		{
			// Scan for opt:set tags - they may contain some custom arguments.
			$set2 = $node->getElementsByTagNameNS('opt', 'set');

			// Now a little trick - how to cheat the opt:insert instruction
			$useAttribute = new Opt_Xml_Attribute('opt:use', $params['template']);
			$this->_compiler->processor('snippet')->processAttribute($node, $useAttribute);
		}

		// Find all the important component elements
		// Remember that some opt:set tags may have been found above and are located in $set2 array.
		$everything = $this->_find($node);
		$everything[0] = array_merge($everything[0], $set2);

		$code = '';
		// opt:set
		foreach($everything[0] as $set)
		{
			$tagParams = array(
				'name' => array(self::REQUIRED, self::EXPRESSION_EXT),
				'value' => array(self::REQUIRED, self::EXPRESSION)
			);

			$this->_extractAttributes($set, $tagParams);
			$code .= $componentVariable.'->__set('.$tagParams['name']['bare'].', '.$tagParams['value'].'); ';
		}
		foreach($args as $name => $value)
		{
			$code .= $componentVariable.'->__set(\''.$name.'\', '.$value.'); ';
		}
		// opt:component-attributes
		foreach($everything[1] as $wtf)
		{
			$id = $wtf->getAttribute('opt:component-attributes')->getValue();
			$subCode = ' $out = '.$componentVariable.'->manageAttributes(\''.$wtf->getName().'#'.$id.'\', array(';
		//	$wtf->removeAttribute('opt:component-attributes');

		//	foreach($wtf->getAttributes() as $attribute)
	//		{
				$params = array(
					'__UNKNOWN__' => array(self::OPTIONAL, self::EXPRESSION, null, 'str')
				);
				$vars = $this->_extractAttributes($wtf, $params);
				foreach($vars as $name => $value)
				{
					$subCode .= '\''.$name.'\' => '.$value.',';
				}
	//		}
			$wtf->removeAttributes();
			$wtf->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $subCode.')); ');
			$wtf->addAfter(Opt_Xml_Buffer::TAG_ENDING_ATTRIBUTES, ' if(is_array($out)){ foreach($out as $name=>$value){ echo \' \'.$name.\'="\'.$value.\'"\'; } } ');
		}

		$node->set('postprocess', true);
		if(isset($useAttribute))
		{
			$node->set('_componentTemplate', $useAttribute);
		}
		$this->_process($node);
		return $code;
	} // end _commonProcessing();

	/**
	 * A hook to the $system special variable. Returns the
	 * compiled PHP code for the call.
	 *
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param array $namespace The namespace to parse
	 * @return string
	 */
	public function processSystemVar($opt)
	{
		if($this->_stack->count() == 0)
		{
			throw new Opt_Instruction_Exception('Component error: invalid use of $'.implode('.',$opt).': no active component.');
		}
		return $this->_stack->top().'->'.$opt[2];
	} // end processSystemVar();

	/**
	 * An utility function that scans the descendants of the component node
	 * and looks for special tags. Returns an array of two elements:
	 *
	 *  - 0 - contains the opt:set XML nodes
	 *  - 1 - contains the nodes with the opt:component-attributes attribute
	 *
	 * @internal
	 * @param Opt_Xml_Node $node The starting point
	 * @return array
	 */
	private function _find($node)
	{
		// We have so many recursions... let's do it in the imperative way.
		$queue = new SplQueue;
		foreach($node as $subnode)
		{
			$queue->enqueue($subnode);
		}
		$result = array(
			0 => array(),	// opt:set
			1 => array(),	// com:*
		);
		$map = array('opt:set' => 0);

		while($queue->count() > 0)
		{
			$current = $queue->dequeue();

			if($current instanceof Opt_Xml_Element)
			{
				if(isset($map[$current->getXmlName()]))
				{
					
					$result[$map[$current->getXmlName()]][] = $current;
				}
				elseif($current->getAttribute('opt:component-attributes') !== null)
				{
					$result[1][] = $current;
				}
				
				// Do not visit the nested components
				if($current->getXmlName() == 'opt:component' || $this->_compiler->isComponent($current->getXmlName()))
				{
					if($queue->count() == 0)
					{
						break;
					}
					continue;
				}
			}			
			foreach($current as $subnode)
			{
				$queue->enqueue($subnode);
			}
		}
		return $result;
	} // end _find();
} // end Opt_Instruction_Component;
