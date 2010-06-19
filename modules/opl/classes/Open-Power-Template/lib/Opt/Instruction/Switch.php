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
 * Processes the Switch instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Control
 */
class Opt_Instruction_Switch extends Opt_Instruction_Abstract
{
	const TAIL_NO = 0;
	const TAIL_PASSIVE = 1;
	const TAIL_ACTIVE = 2;

	/**
	 * The processor name.
	 * @var string
	 */
	protected $_name = 'switch';

	/**
	 * The registered switch handlers
	 * @var array
	 */
	private $_handlers = array();

	/**
	 * The switchable tags
	 * @var array
	 */
	private $_switchable = array('opt:switch' => true);

	/**
	 * The handler priority.
	 * @var array
	 */
	private $_priority = array();

	/**
	 * Is the processor initialized?
	 * @var boolean
	 */
	private $_initialized = false;

	/**
	 * The data for the sort() method.
	 * @var array
	 */
	private $_sort = array();

	/**
	 * Reverse group information
	 * @var array
	 */
	private $_reverseGroup = array();

	/**
	 * Configures the instruction processor.
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:switch'));

		$this->addSwitchable('opt:switch');
		$this->addSwitchHandler('opt:equals', $this->_compiler->createFormat(null, 'SwitchEquals'), 500);
		$this->addSwitchHandler('opt:contains', $this->_compiler->createFormat(null, 'SwitchContains'), 1000);
	} // end configure();

	/**
	 * Migrates the opt:switch node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function _migrateSwitch(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end _migrateSwitch();

	/**
	 * Processes the opt:switch tag.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node The node.
	 */
	protected function _processSwitch(Opt_Xml_Node $node)
	{
		$params = array(
			'test' => array(0 => self::REQUIRED, self::EXPRESSION, null, 'parse'),
		);
		$this->_extractAttributes($node, $params);

		$this->createSwitch($node, $params['test']);
	} // end _processSwitch();

	/**
	 * Adds a tag that is recognized as a beginning of a new switch.
	 *
	 * @param string $tagName The switch tag name
	 */
	final public function addSwitchable($tagName)
	{
		$this->_switchable[$tagName] = true;
	} // end addSwitchable();

	/**
	 * Registers a new switch handler which is able to process various conditions etc.
	 * The switch cases are handled by ordinary data formats and thus, we must provide
	 * a data format object here. The data format must implement the 'switch' hook type.
	 *
	 * The last argument has two meanings. If it contains an integer number, it defines
	 * the processing priority of this handler, or in other words - whether it will appear
	 * earlier or later in the source code (lower values mean earlier occurence). If it
	 * contains string, the registered tag is grouped with another tag, appearing in its
	 * condition group and following its rules.
	 *
	 * Note that OPT neither checks nor supports grouping with tags that are themselves grouped
	 * to something other.
	 *
	 * Note that the data format must implement the 'switch' hook type.
	 *
	 * @param string $tagName The registered tag name
	 * @param Opt_Format_Abstract $dataFormat The data format to handle these requests.
	 * @param string|integer $groupInfo Group information or the priority.
	 */
	final public function addSwitchHandler($tagName, Opt_Format_Abstract $dataFormat, $groupInfo)
	{
		if(!$dataFormat->supports('switch'))
		{
			throw new Opt_FormatNotSupported_Exception($dataFormat->getName(), 'switch');
		}

		$obj = new SplFixedArray(2);
		$obj[0] = $dataFormat;
		$obj[1] = $groupInfo;

		$this->_handlers[$tagName] = $obj;

		if(is_integer($groupInfo))
		{
			$this->_priority[$groupInfo] = $tagName;
			$this->_reverseGroup[$tagName] = array();
		}
		else
		{
			$this->_reverseGroup[$groupInfo][] = $tagName;
		}
	} // end addSwitchHandler();

	/**
	 * Removes an existing switch handler. If the handler is not found, it throws
	 * an exception.
	 *
	 * @throws Opt_ObjectNotExists_Exception
	 * @param string $tagName The tag name registered for the handler.
	 */
	final public function removeSwitchHandler($tagName)
	{
		if($this->_initialized)
		{
			// TODO: Exception here...
			die('Error');
		}
		if(!isset($this->_handlers[(string)$tagName]))
		{
			throw new Opt_ObjectNotExists_Exception('switch handler', $tagName);
		}
		// Remove the data from some extra arrays...
		if(is_integer($this->_handlers[(string)$tagName][1]))
		{
			unset($this->_priority[$this->_handlers[(string)$tagName][1]]);
			unset($this->_reverseGroup[(string)$tagName]);
		}
		else
		{
			$id = array_search($this->_reverseGroup[$this->_handlers[(string)$tagName][1]]);
			unset($this->_reverseGroup[(string)$tagName][$id]);
		}
		unset($this->_handlers[(string)$tagName]);
	} // end removeSwitchHandler();

	/**
	 * Returns true, if the specified switch handler exists under the specified
	 * tag name.
	 *
	 * @param string $tagName The registered tag name
	 */
	final public function hasSwitchHandler($tagName)
	{
		return isset($this->_handlers[(string)$tagName]);
	} // end hasSwitchHandler();

	/**
	 * Compiles the specified tag contents as a programmable switch statement.
	 * The programmer may define his own actions and requirements for node
	 * compilation.
	 *
	 * @param Opt_Xml_Node $node The root node that acts as a switch.
	 * @param string $test The test condition.
	 */
	final public function createSwitch(Opt_Xml_Node $node, $test)
	{
		// Initialize the processor, if necessary
		if(!$this->_initialized)
		{
			ksort($this->_priority);
			$this->_initialized = true;
		}
		$containers = array();
		foreach($this->_priority as $handler)
		{
			$containers[$handler] = $this->_createSwitchGroup($node, $handler, $this->_handlers[$handler][0]);
		}
		// Clear the list
		$elements = array();
		foreach($node as $subnodes)
		{
			$elements[] = $subnodes;
		}
	 
		$node->removeChildren();
		foreach($elements as $subnodes)
		{
			$subnodes->dispose();
		}

		// Now build the new tree.
		$first = true;
		foreach($containers as $handler => $container)
		{
			if($container->hasChildren())
			{
				$format = $this->_handlers[$handler][0];
				$format->assign('test', $test);
				if($first)
				{
					$container->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $format->get('switch:enterTestBegin.first'));
					$container->addAfter(Opt_Xml_Buffer::TAG_AFTER, $format->get('switch:enterTestEnd.first'));
				
					$first = false;
				}
				else
				{
					$container->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $format->get('switch:enterTestBegin.later'));
					$container->addAfter(Opt_Xml_Buffer::TAG_AFTER, $format->get('switch:enterTestEnd.later'));
				}
				$node->appendChild($container);
				$container->set('hidden', false);
			}
		}
	//	$this->_compiler->_debugPrintNodes($node);
	} // end createSwitch();

	/**
	 * Creates and compiles a single opt:switch group. Note that it generates a
	 * copy of the tree part that matches the group which is later returned.
	 *
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Node $base The base node
	 * @param string $group The group to match
	 * @param Opt_Format_Abstract The format to be used by this group.
	 * @return Opt_Xml_Node
	 */
	private function _createSwitchGroup(Opt_Xml_Node $base, $group, Opt_Format_Abstract $format)
	{
		$node = new Opt_Xml_Element('opt:_');
		$order = 0;

		foreach($base as $subnode)
		{
			if($subnode instanceof Opt_Xml_Element)
			{
				if(($case = $this->_detectCase($subnode)) === null)
				{
					throw new Opt_Instruction_Exception('Invalid opt:switch node: '.$subnode->getXmlName());
				}
				
				if($case == $group)
				{
					// This node matches this particular group, so we can scan it
					$node->appendChild($clone = clone $subnode);

					$clone->set('priv:switch.parent', null);
					$clone->set('priv:switch.tail', self::TAIL_ACTIVE);
					$clone->set('priv:switch.skipOrdering', true);

					$queue = new SplQueue;
					$stack = new SplStack;
					$queue->enqueue(array(0 => $clone, null, 0));
					$scanned = null;

					do
					{
						list($item, $parent, $nesting) = $queue->dequeue();
						

						if($this->_detectCase($item) !== null)
						{
							$stack->push($item);

							// Pass some extra information for formats that use pure switch()
							// that they can omit various internal indexes and use pure case()
							// here.
							if($parent !== null && $item->get('priv:switch.tail') === self::TAIL_PASSIVE && $parent->get('priv:switch.skipOrdering') === true)
							{
								$item->set('priv:switch.skipOrdering', true);
							}

							$item->set('priv:switch.parent', $parent);
							$item->set('priv:switch.order', $order);
							$item->set('priv:switch.orderList', array($order));
							$item->set('priv:switch.nesting', $nesting);
							$params = $format->action('switch:caseAttributes');
							$this->_extractAttributes($item, $params);
							$item->set('priv:switch.params', $params);
							
							$item->set('hidden', false);
							$this->_process($item);

							$order++;
						}
						
						// Strip final whitespaces that would crash tail detection.
						if($item instanceof Opt_Xml_Element && ($last = $item->getLastChild()) !== null)
						{
							if($last instanceof Opt_Xml_Text && $last->isWhitespace())
							{
								$item->removeChild($last);
							}
						}

						// Now add the children to the processing queue.
						$prev = null;
						foreach($item as $subitem)
						{
							if($subitem instanceof Opt_Xml_Element && $this->_detectSwitchable($subitem))
							{
								continue;
							}

							if(($case = $this->_detectCase($subitem)) !== null)
							{
								// switch node.
								// if it does not belong to our group, skip it but
								// leave the contents, as they must be still available
								// according to the switch case semantics.
								if($case != $group)
								{
									foreach($subitem as $oneMoreSubnode)
									{
										$item->insertBefore($oneMoreSubnode, $subitem);
									}
									$item->removeChild($subitem);
									continue;
								}								
								$queue->enqueue(array($subitem, $item, $nesting+1));

								// Check tailness.
								if($subitem->getNext() === null && ($next = $subitem->getParent()) !== null && $this->_detectCase($next) !== null)
								{
									$subitem->set('priv:switch.tail', self::TAIL_PASSIVE);
								}
								else
								{
									$subitem->set('priv:switch.tail', self::TAIL_NO);
								}
							}
							else
							{
								// Ordinary node
								$queue->enqueue(array($subitem, $parent, $nesting));
							}
						}
					}
					while($queue->count() > 0);

					// Now the stack and the reversed BFS - note that it operates on
					// switch cases only.
					do
					{
						$item = $stack->pop();

						// We must pass the order number
						// to the parent which could need it to close it properly.
						$parent = $item->get('priv:switch.parent');
						$list = array();
						if(is_array($tmp = $item->get('priv:switch.orderList')))
						{
							foreach($tmp as $stuff)
							{
								$list[] = $stuff;
							}
						}
						if(is_object($parent) && is_array($tmp = $parent->get('priv:switch.orderList')))
						{
							foreach($tmp as $stuff)
							{
								$list[] = $stuff;
							}
						}
						if(is_object($parent))
						{
							$parent->set('priv:switch.orderList', $list);
						}

						$format->assign('orderList', $item->get('priv:switch.orderList'));
						$format->assign('tail', $item->get('priv:switch.tail'));
						$format->assign('attributes', $item->get('priv:switch.params'));
						$format->assign('nesting', $item->get('priv:switch.nesting'));
						$format->assign('order', $item->get('priv:switch.order'));
						$format->assign('skipOrdering', $item->get('priv:switch.skipOrdering'));
						$format->assign('informed', $item->get('priv:switch.informed'));
						$format->assign('element', $item);
						$item->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $format->get('switch:caseBefore'));
						$item->addAfter(Opt_Xml_Buffer::TAG_AFTER, $format->get('switch:caseAfter'));

						// The item may notify parents about something
						if($parent !== null && $parent->get('priv:switch.informed') === null)
						{
							$parent->set('priv:switch.informed', $format->action('switch:inform'));
						}
					}
					while($stack->count() > 0);
				}
			}
		}
		$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $format->get('switch:testsBefore'));
		$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, $format->get('switch:testsAfter'));
		$node->set('hidden', false);

		return $node;
	} // end _createSwitchGroup();

	/**
	 * Detects a switchable tag.
	 *
	 * @internal
	 * @param Opt_Xml_Element $element The tag to test.
	 * @return boolean
	 */
	private function _detectSwitchable(Opt_Xml_Element $element)
	{
		if(isset($this->_switchable[$element->getXmlName()]))
		{
			return true;
		}
		return false;
	} // end _detectSwitchable();

	/**
	 * Recognizes the case tag in the switch. Returns the recognized tag name
	 * or NULL. Please note that checking the attributed forms is quite slow,
	 * so please do not use it too often, but rather cache the results for
	 * a particular tag.
	 *
	 * @internal
	 * @param Opt_Xml_Node $element The element to test
	 * @return string|null
	 */
	private function _detectCase(Opt_Xml_Node $element)
	{
		if(! $element instanceof Opt_Xml_Element)
		{
			return null;
		}
		if($element->get('priv:switch.nextCase') !== null)
		{
			return $element->get('priv:switch.nextCase');
		}

		if(isset($this->_handlers[$element->getXmlName()]))
		{
			$element->set('priv:switch.nextCase', $element->getXmlName());
			return $element->getXmlName();
		}
		// Look for an attribute.
		else
		{
			foreach($element->getAttributes() as $attribute)
			{
				if(isset($this->_handlers[$attribute->getXmlName()]))
				{
					$element->set('priv:switch.nextCase', $attribute->getXmlName());
					return $attribute->getXmlName();
				}
			}
		}
		return null;
	} // end _detectCase();
} // end Opt_Instruction_Switch;
