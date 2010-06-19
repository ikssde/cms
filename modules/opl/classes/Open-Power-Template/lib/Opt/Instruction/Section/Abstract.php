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
 * This abstract class allows to create custom section instructions.
 * It contains all the shared logic and cooperation subroutines.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage API
 * @abstract
 */
abstract class Opt_Instruction_Section_Abstract extends Opt_Instruction_Loop_Abstract
{
	/**
	 * The currently available sections and their data.
	 * @internal
	 * @var array
	 */
	static private $_sections = array();

	/**
	 * The stack of sections in the order they are nested. The
	 * stack holds the section names.
	 * @var SplStack
	 */
	static private $_stack;

	/**
	 * Initializes the section processor.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function __construct($compiler)
	{
		parent::__construct($compiler);

		if(!is_object(self::$_stack))
		{
			self::$_stack = new SplStack;
		}
	} // end __construct();

	/**
	 * Resets the processor state.
	 */
	public function reset()
	{
		if(sizeof(self::$_sections) > 0)
		{
			self::$_stack = new SplStack;
			self::$_sections = array();
		}
	} // end reset();

	/**
	 * Returns the information record about the specified section.
	 *
	 * @static
	 * @param String $name Section ID
	 * @return Array The information record or NULL, if the section is not found.
	 */
	static public function getSection($name)
	{
		if(!isset(self::$_sections[$name]))
		{
			return NULL;
		}
		return self::$_sections[$name];
	} // end getSection();

	/**
	 * Returns the currently parsed section record.
	 *
	 * @static
	 * @return Array The information record or NULL, if no sections are active.
	 */
	static public function getLastSection()
	{
		return self::getSection(self::$_stack->top());
	} // end getLastSection();

	/**
	 * Returns the number of active sections.
	 *
	 * @static
	 * @return Int The number of sections on the stack.
	 */
	static public function countSections()
	{
		return self::$_stack->count();
	} // end countSections();

	/**
	 * Creates a new section record, using the information from the specified node.
	 * If the node is not a valid OPT instruction, the method scans the ancestors
	 * to find a free opt:show node.
	 *
	 * The method can also initialize the attributed section, if we provide the
	 * opt:section attribute object as the second argument.
	 *
	 * The method initializes also the neighbourhood of the section by parsing the
	 * opt:show tag, if necessary and creating the enter condition. Note that it
	 * does not start the section - the record is neither put onto the section stack
	 * nor opt:use integration is not made. You have to use _sectionStart() in order
	 * to fully initialize the section.
	 *
	 * @param Opt_Xml_Element $node
	 * @param Opt_Xml_Attribute $attr optional
	 * @param Array $extraAttributes optional
	 * @return Array The section record.
	 */
	protected function _sectionCreate(Opt_Xml_Element $node, $attr = NULL, $extraAttributes = NULL)
	{
		/* First, we need to determine, whether the section is associated with
		 * opt:show. In case of tag sections, this is done only if the node
		 * does not contain any section attributes.
		 */
		$show = NULL;
		if($attr instanceof Opt_Xml_Attribute)
		{
			$show = $this->_findShowNode($node, $attr->getValue());

			if($show !== null)
			{
				// In this case we can obtain the attributes from opt:show.
				$section = $this->_extractSectionAttributes($show, $extraAttributes);
				$section['show'] = $show;
				$section['node'] = $node;
				$section['attribute'] = $attr;
			}
			else
			{
				// Generate a default section record, using the $attr value and
				// optionally checking for the "separator" attribute in the current node.
				$_params = array(
					'separator' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL),
				);
				$this->_extractAttributes($node, $_params);

				$section = array(
					'name' => $attr->getValue(),
					'order' => 'asc',
					'parent' => NULL,
					'datasource' => NULL,
					'display' => NULL,
					'separator' => $_params['separator'],
					'show' => null,
					'node' => $node,
					'attribute' => $attr
				);
			}
		}
		else
		{
			if($node->get('ambiguous:opt:body') !== null)
			{
				// New way with opt:body
				$section = $this->_extractSectionAttributes($node, $extraAttributes);
				$section['show'] = $node;
				$section['node'] = $node->get('ambiguous:opt:body');
				$section['attribute'] = null;
				$section['node']->set('priv:section', $section['name']);
			}
			else
			{
				// Classic way from OPT 2.0
				if($node->getAttribute('name') === null)
				{
					// We must look for opt:show
					$show = $this->_findShowNode($node);

					if($show === null)
					{
						throw new Opt_Instruction_Section_Exception('Section error: the attribute "name" is not defined in '.$node->getXmlName());
					}

					$section = $this->_extractSectionAttributes($show, $extraAttributes);
					$section['show'] = $show;
					$section['node'] = $node;
					$section['attribute'] = null;
				}
				else
				{
					$section = $this->_extractSectionAttributes($node, $extraAttributes);
					$section['show'] = null;
					$section['node'] = $node;
					$section['attribute'] = null;
				}
			}
		}
		$this->_validateSection($section);

		if($section['show'] === null)
		{
			$this->_createShowCondition($node, $section);
		}
		elseif($section['show']->get('priv:initialized') === null)
		{
			$this->_createShowCondition($section['show'], $section);
		}

		// A faster way to obtain the section name associated with this section.
		$node->set('priv:section', $section['name']);

		return $section;
	} // end _sectionCreate();

	/**
	 * Starts the section by putting its record on a section stack.
	 *
	 * @param Array $section The section record.
	 */
	protected function _sectionStart(Array &$section)
	{
		self::_addSection($section);

		if(!is_null($section['node']->get('call:use')))
		{
			$this->_compiler->setConversion('##simplevar_'.$section['node']->get('call:use'), $section['name']);
		}

		// Populate the debug console.
		if($this->_tpl->debugConsole)
		{
			if(isset($section['datasource']))
			{
				$parent = '<em>Datasource</em>';
			}
			elseif(!is_null($section['parent']))
			{
				$parent = $section['parent'];
			}
			else
			{
				$parent = '-';
			}
			Opt_Support::addSection($section['name'], $parent, (string)$section['format'], $section['node']->getXmlName());
		}
	} // end _sectionStart();

	/**
	 * Finalizes the section associated with the specified XML node. If the
	 * node does not contain any valid section information, it generates
	 * an exception.
	 *
	 * @param Opt_Xml_Element $node The section node
	 */
	protected function _sectionEnd(Opt_Xml_Element $node)
	{
		$section = $node->get('priv:section');
		if(!is_array($section))
		{
			$section = self::getSection($section);
		}

		if($node->get('call:use') !== null)
		{
			$this->_compiler->unsetConversion('##simplevar_'.$node->get('priv:section'));
		}

		self::_removeSection($section['name']);
	} // end _sectionEnd();

	/**
	 * Adds the show condition PHP code to the specified node, using the
	 * information from the section record.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The node, where to add the show condition.
	 * @param Array &$section The section info record
	 */
	private function _createShowCondition(Opt_Xml_Element $node, &$section)
	{
		// First, try to check for the call:use tag variable.
		if($node->get('call:use') !== NULL)
		{
			$section['node']->set('call:use', $node->get('call:use'));
		}

		// Deal with the data formats
		$format = $section['format'];
		$format->assign('section', $section);


		$request = $format->property('section:anyRequests');
		if(!is_null($request))
		{
			// Send the requested data, if the data format needs any.
			switch($request)
			{
				case 'ancestorNames':
					self::$_stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
					$list = array();
					$parent = $section['parent'];
					foreach(self::$_stack as $up)
					{
						if($up == $parent)
						{
							$parent = self::$_sections[$up]['parent'];
							$list[] = self::$_sections[$up]['name'];
						}
					}
					$format->assign('requestedData', array_reverse($list));
					break;
				case 'ancestorNumbers':
					self::$_stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
					$list = array();
					$parent = $section['parent'];
					$iteration = self::$_stack->count();
					foreach(self::$_stack as $up)
					{
						if($up == $parent)
						{
							$parent = self::$_sections[$up]['parent'];
							$list[] = $iteration;
						}
						$iteration--;
					}
					$format->assign('requestedData', array_reverse($list));
					break;
			}
		}

		$code = $format->get('section:init');
		if(is_null($section['display']))
		{
			$code .= ' if('.$format->get('section:isNotEmpty').'){ ';
		}
		else
		{
			$code .= ' if('.$format->get('section:isNotEmpty').' && '.$section['display'].'){ ';
		}
		$code .= $format->get('section:started');
		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $code);

		$code = $format->get('section:finished').' } '.$format->get('section:done');
		$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, $code);
		$node->set('priv:initialized', true);
	} // end _createShowCondition();

	/**
	 * Finds the nearest free opt:show node in the ascentors of the specified node.
	 *
	 * @internal
	 * @param Opt_Xml_Element $item The current node.
	 * @param string $name optional The name that opt:show must match.
	 * @return Opt_Xml_Element The opt:show node or NULL if not found.
	 */
	private function _findShowNode(Opt_Xml_Element $item, $name = null)
	{
		if($name !== null)
		{
			// The section names must also match!
			while(!is_null($item = $item->getParent()))
			{
				if($item instanceof Opt_Xml_Element)
				{
					if($item->getXmlName() == 'opt:show')
					{
						$nameAttr = $item->getAttribute('name');
						if(!is_null($nameAttr) && $nameAttr->getValue() == $name)
						{
							return $item;
						}
					}
				}
			}
		}
		else
		{
			// Here we do not need to check, whether the name in opt:show matches the argument.
			while(($item = $item->getParent()) !== null)
			{
				if($item instanceof Opt_Xml_Element)
				{
					if($item->getXmlName() == 'opt:show')
					{
						return $item;
					}
				}
			}
		}
		return NULL;
	} // end _findShowNode();

	/**
	 * The section parameter parsing is used in several places, so the
	 * code was moved to a separate method.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node Parse the attributes from this node.
	 * @param array $extraAttributes=NULL Extra section attributes
	 * @return array The extracted attributes.
	 */
	private function _extractSectionAttributes(Opt_Xml_Element $node, $extraAttributes = NULL)
	{
		$params = array(
			'name' => array(0 => self::REQUIRED, self::ID),
			'parent' => array(0 => self::OPTIONAL, self::ID_EMP, NULL),
			'datasource' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL),
			'order' => array(0 => self::OPTIONAL, self::ID, 'asc'),
			'display' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL),
			'separator' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL),
		);

		// The instruction may add some extra attributes.
		if($extraAttributes !== null)
		{
			$params = array_merge($params, $extraAttributes);
		}

		$this->_extractAttributes($node, $params);

		return $params;
	} // end _extractSectionAttributes();

	/**
	 * Validates the section record, determines the section parent and
	 * the data format.
	 *
	 * @internal
	 * @param array &$section The section record.
	 */
	private function _validateSection(Array &$section)
	{
		// Verify the value of the "order" attribute.
		if($section['order'] != 'asc' && $section['order'] != 'desc')
		{
			throw new Opt_Instruction_Section_Exception('Section error: invalid "order" attribute value: "asc" or "desc" expected.');
		}

		// Determine the parent of the specified section.
		// if the attribute is not set, the default behaviour is to find the nearest
		// top-level and active section and link to it. Otherwise we must check if
		// the chosen section exists and is active.
		// Note that "parent" is ignored when we set "datasource"
		if($section['parent'] === null)
		{
			if(self::$_stack->count() > 0)
			{
				$section['parent'] = self::$_stack->top();
			}
		}
		elseif($section['parent'] != '')
		{
			if(self::getSection($section['parent']) === null)
			{
				$exception = new Opt_Instruction_Section_Exception('Section error: unknown parent section "'.$section['parent'].'".');
				$exception->setStackData(self::$_stack);
				throw $exception;
			}
		}
		else
		{
			// For the situation, if we had 'parent=""' in the template.
			$section['parent'] = null;
		}

		$section['nesting'] = self::countSections() + 1;

		// Now we need to obtain the information about the data format.
		$section['format'] = $this->_compiler->getFormat($section['name']);
		if(!$section['format']->supports('section'))
		{
			throw new Opt_Format_Exception('The format '.$section['format']->getName().' does not support "section" type.');
		}
	} // end _validateSection();

	/**
	 * Adds the new section record to the stack. If the section with the
	 * specified name already exists, an exception is thrown.
	 *
	 * @static
	 * @internal
	 * @throws Opt_Instruction_Section_Exception
	 * @param array $info The section record.
	 */
	static private function _addSection(Array $info)
	{
		if(isset(self::$_sections[$info['name']]))
		{
			$exception = new Opt_Instruction_Section_Exception('Section error: The section '.$info['name'].' aready exists.');
			$exception->setStackData(self::$_stack);
			throw $exception;
		}
		self::$_sections[$info['name']] = $info;
		self::$_stack->push($info['name']);
	} // end _addSectionInfo();

	/**
	 * Removes the specified section from the stack. The name
	 * is provided to check, if the order of the closing is
	 * valid.
	 *
	 * If the section does not exist or there is an error in
	 * the stack structure, the method throws exceptions.
	 *
	 * @static
	 * @internal
	 * @throws Opt_Instruction_Section_Exception
	 * @param string $name The section name.
	 */
	static private function _removeSection($name)
	{
		if(self::$_stack->count() == 0)
		{
			$exception = new Opt_Instruction_Section_Exception('Section error: unknown section '.$name.'.');
			$exception->setStackData(self::$_stack);
			throw $exception;
		}
		$name2 = self::$_stack->pop();
		if($name != $name2)
		{
			throw new Opt_Instruction_Section_Exception('OPT: Invalid section name thrown from the stack. Expected: '.$name.'; Actual: '.$name2);
		}
		self::$_sections[$name]['format']->resetVars();
		unset(self::$_sections[$name]);
	} // end _removeSection;

	/**
	 * A dummy opt:show processor that actually does nothing, because
	 * it must wait for the right section instruction. If such instruction
	 * will not appear, the node will be parsed in the postprocessing.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node The node
	 */
	protected function _processShow(Opt_Xml_Node $node)
	{
		$node->set('postprocess', true);
		$this->_sortSectionContents($node, 'opt', 'showelse');

		$this->_process($node);
	} // end _processShow();

	/**
	 * Finalizes the opt:show attribute. If there was no section in opt:show,
	 * it initializes a section for a moment just to generate the condition,
	 * but does not add it to the stack.
	 *
	 * @param Opt_Xml_Node $node The node.
	 */
	protected function _postprocessShow(Opt_Xml_Node $node)
	{
		if($node->get('priv:initialized') !== null)
		{
			return;
		}

		$section = $this->_extractSectionAttributes($node, null);
		$section['show'] = $node;
		$section['node'] = null;
		$section['attribute'] = null;
		$this->_validateSection($section);

		$this->_createShowCondition($node, $section);
	} // end _postprocessShow();

	/**
	 * An utility method that cleans the contents of the section node, by
	 * moving the alternative section (opt:sectionelse etc. tags) code to the end
	 * of the children list.
	 *
	 * In the parameters, we must specify the name and the namespace of the
	 * tags that will be treated as the alternative content tags.
	 *
	 * @throws Opt_Instruction_Section_Exception
	 * @param Opt_Xml_Element $node The section node
	 * @param String $ns The namespace
	 * @param String $name The alternative section content tag name
	 */
	protected function _sortSectionContents(Opt_Xml_Element $node, $ns, $name)
	{
		$else = $node->getElementsByTagNameNS($ns, $name, false);

		if(sizeof($else) == 1)
		{
			if(!$node->hasAttributes())
			{
				throw new Opt_Instruction_Section_Exception('Section error: too many '.$ns.':'.$name.' elements: none expected.');
			}
			$node->bringToEnd($else[0]);
		}
		elseif(sizeof($else) > 1)
		{
			throw new Opt_Instruction_Section_Exception('Section error: too many '.$ns.':'.$name.' elements: zero or one expected.');
		}
	} // end _locateElse();

	/**
	 * Processes the system variable $sys for the sections.
	 *
	 * @throws Opt_Instruction_Section_Exception
	 * @param array $opt The system variable call splitted into separate identifiers.
	 * @return string The output PHP code.
	 */
	public function processSystemVar($opt)
	{
		if(sizeof($opt) < 4)
		{
			throw new Opt_Instruction_Section_Exception('Section error: the special variable $'.implode('.', $opt).' is too short: four elements expected.');
		}
		// Determine the section
		$section = self::getSection($opt[2]);
		if($section === null)
		{
			$exception = new Opt_Instruction_Section_Exception('Section error: unknown section '.$opt[2].' in the special variable $'.implode('.', $opt).'.');
			$exception->setStackData(self::$_stack);
			throw $exception;
		}
		switch($opt[3])
		{
			case 'count':
				return $section['format']->get('section:count');
			case 'iterator':
				return $section['format']->get('section:iterator');
			case 'size':
				return $section['format']->get('section:size');
			case 'first':
				return $section['format']->get('section:isFirst');
			case 'last':
				return $section['format']->get('section:isLast');
			case 'extreme':
				return $section['format']->get('section:isExtreme');
			default:
				$result = $this->_processSystemVar($opt);
				if($result === null)
				{
					throw new Opt_Instruction_Section_Exception('Section error: the special variable $'.implode('.', $opt).' cannot be processed.');
				}
				return $result;
		}
	} // end processSystemVar();

	/**
	 * Allows the sections to handle specific uses of $sys special variable.
	 *
	 * @throws Opt_Instruction_Section_Exception
	 * @param array $opt The system variable call splitted into separate identifiers.
	 * @return string The output PHP code.
	 */
	protected function _processSystemVar($opt)
	{
		return NULL;
	} // end _processSystemVar();
} // end Opt_Instruction_Section_Abstract;
