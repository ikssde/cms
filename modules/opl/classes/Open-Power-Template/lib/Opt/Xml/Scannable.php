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
 * The abstract class that represents the XML nodes which can contain
 * another nodes. It uses the DOM-like API to manage the nodes.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 * @abstract
 */
abstract class Opt_Xml_Scannable extends Opt_Xml_Node implements Iterator
{
	/**
	 * The first child.
	 * @var Opt_Xml_Node
	 */
	protected $_first = null;
	/**
	 * The last child
	 * @var Opt_Xml_Node
	 */
	protected $_last = null;

	/**
	 * The number of children
	 * @var integer
	 */
	private $_size = 0;

	/**
	 * The collection iterator.
	 * @var Opt_Xml_Node
	 */
	private $_iterator = null;

	/**
	 * The collection iterator position.
	 * @var integer
	 */
	private $_position = 0;

	/**
	 * The list of prototypes for sort() method.
	 * @var array
	 */
	private $_prototypes;

	/**
	 * Creates the scannable node.
	 */
	public function __construct()
	{
		parent::__construct();
	} // end __construct();

	/**
	 * Appends a new child to the end of the children list. The method
	 * is DOM-compatible.
	 *
	 * @param Opt_Xml_Node $child The child to be appended.
	 */
	public function appendChild(Opt_Xml_Node $child)
	{
		// Test if the node can be a child of this and initialize an
		// empty array if needed.
		$this->_testNode($child);

		$child->unmount();
		if($this->_last === null)
		{
			$this->_first = $this->_last = $child;
			$child->_parent = $this;
		}
		else
		{
			$child->_previous = $this->_last;
			$child->_parent = $this;
			$this->_last->_next = $child;
			$this->_last = $child;
		}
		$this->_size++;
	} // end appendChild();

	/**
	 * Inserts the new node after the node identified by the '$refnode'. The
	 * reference node can be identified either by the number or by the object.
	 * If the reference node is empty, the new node is appended to the children
	 * list, if the last argument allows for that.
	 *
	 * Note that in case of objective reference node specification, the reference
	 * node must be a child of the current node. Otherwise, an exception is thrown.
	 *
	 * @throws Opt_APIInvalidBorders_Exception
	 * @param Opt_Xml_Node $newnode The new node.
	 * @param integer|Opt_Xml_Node $refnode The reference node.
	 * @param boolean $appendOnError Do we like to append the node, if $refnode does not exist?
	 */
	public function insertBefore(Opt_Xml_Node $newnode, $refnode = null, $appendOnError = false)
	{
		// Test if the node can be a child of this and initialize an
		// empty array if needed.
		$this->_testNode($newnode);
		if($refnode === null)
		{
			return $this->appendChild($newnode);
		}

		// If the reference node is specified with an integer, we must find it.
		if(is_integer($refnode))
		{
			$i = 0;
			$scan = $this->_first;
			while($scan !== null)
			{
				if($i == $refnode)
				{
					$refnode = $scan;
					break;
				}
				$scan = $scan->_next;
				$i++;
			}
			if(!is_object($refnode))
			{
				if($appendOnError)
				{
					return $this->appendChild($node);
				}
				throw new Opt_APIInvalidBorders_Exception;
			}
		}

		// Now, do the insert.		
		if($refnode->_parent !== $this)
		{
			if($appendOnError)
			{
				return $this->appendChild($node);
			}
			throw new Opt_APIInvalidBorders_Exception;
		}
		$newnode->unmount();
		if($refnode->_previous !== null)
		{
			$refnode->_previous->_next = $newnode;
			$newnode->_previous = $refnode->_previous;
		}
		$newnode->_next = $refnode;
		$newnode->_parent = $this;
		$refnode->_previous = $newnode;
		if($refnode === $this->_first)
		{
			$this->_first = $newnode;
		}
		$this->_size++;
	} // end insertBefore();

	/**
	 * Removes the child identified either by the number or the object.
	 *
	 * @param integer|Opt_Xml_Node $node The node to be removed.
	 * @return boolean
	 */
	public function removeChild($node)
	{
		if(is_integer($node))
		{
			$i = 0;
			$scan = $this->_first;
			while($scan !== null)
			{
				if($i == $node)
				{
					$node = $scan;
					break;
				}
				$scan = $scan->_next;
				$i++;
			}
			if(!is_object($node))
			{
				return false;
			}
		}

		// Check if this is really our child. We cannot exterminate the
		// children of other nodes.
		if($node->_parent !== $this)
		{
			return false;
		}
		$this->_size--;

		// Iteration...
		if($this->_iterator === $node)
		{
			$this->_iterator = $node->_previous;
			if($this->_iterator === null)
			{
				$this->_iterator = -1;
			}
		}

		// The border cases...
		if($this->_first === $node)
		{
			$this->_first = $node->_next;
		}
		if($this->_last === $node)
		{
			$this->_last = $node->_previous;
		}

		// Unlink it.
		if($node->_previous !== null)
		{
			$node->_previous->_next = $node->_next;
		}
		if($node->_next !== null)
		{
			$node->_next->_previous = $node->_previous;
		}
		$node->_parent = null;
		$node->_previous = null;
		$node->_next = null;
		return true;
	} // end removeChild();

	/**
	 * Removes all the children. The memory after the children is not freed, so they
	 * can be used again in other places.
	 */
	public function removeChildren()
	{
		$scan = $this->_first;
		while($scan !== null)
		{
			$next = $scan->_next;
			$scan->_previous = $scan->_next = $scan->_parent = null;
			$scan = $next;
		}
		$this->_first = $this->_last = null;
		$this->_size = 0;
	} // end removeChildren();

	/**
	 * Moves all the children of another node to the current node.
	 *
	 * @param Opt_Xml_Node $node Another node.
	 */
	public function moveChildren(Opt_Xml_Scannable $node)
	{
		// If there are already some nodes, we have to free the memory first.
		$scan = $this->_first;
		while($scan !== null)
		{
			$next = $scan->_next;
			$scan->dispose();
			$scan = $next;
		}

		$this->_first = $node->_first;
		$this->_last = $node->_last;
		$this->_size = $node->_size;

		$scan = $this->_first;
		while($scan !== null)
		{
			$next = $scan->_next;
			$scan->_parent = $this;
			$scan = $next;
		}

		$node->_size = 0;
		$node->_first = $node->_last = null;
		$node->_iterator = null;
		$node->_position = 0;
	} // end moveChildren();

	/**
	 * Replaces the child with the new node. The reference node can be
	 * identified either by the number or by the object.
	 *
	 * @param Opt_Xml_Node $newnode The new node.
	 * @param integer|Opt_Xml_Node $refnode The old node.
	 * @return boolean
	 */
	public function replaceChild(Opt_Xml_Node $newnode, $refnode)
	{
		$this->_testNode($newnode);

		// If the reference node is specified with an integer, we must find it.
		if(is_integer($refnode))
		{
			$i = 0;
			$scan = $this->_first;
			while($scan !== null)
			{
				if($i == $refnode)
				{
					$refnode = $scan;
					break;
				}
				$scan = $scan->_next;
				$i++;
			}
			if(!is_object($refnode))
			{
				return false;
			}
		}

		// Now, do the replacement.
		if($refnode->_parent !== $this)
		{
			throw new Opt_APIInvalidBorders_Exception;
		}

		if($this->_iterator === $refnode)
		{
			$this->_iterator = $newnode;
		}

		$newnode->unmount();
		$newnode->_previous = $refnode->_previous;
		$newnode->_next = $refnode->_next;
		$newnode->_parent = $this;

		if($refnode->_previous !== null)
		{
			$refnode->_previous->_next = $newnode;
		}
		else
		{
			$this->_first = $newnode;
		}
		if($refnode->_next !== null)
		{
			$refnode->_next->_previous = $newnode;
		}
		else
		{
			$this->_last = $newnode;
		}
		$refnode->_parent = $refnode->_next = $refnode->_previous = null;
		return true;
	} // end replaceChild();

	/**
	 * Returns true, if the current node contains any children.
	 *
	 * @return boolean
	 */
	public function hasChildren()
	{
		return $this->_size > 0;
	} // end hasChildren();

	/**
	 * Returns the number of the children.
	 *
	 * @return integer
	 */
	public function countChildren()
	{
		return $this->_size;
	} // end countChildren();

	/**
	 * Returns the last child of the node. If there are no child nodes
	 * in the current node, the method returns NULL.
	 *
	 * @return Opt_Xml_Node
	 */
	public function getLastChild()
	{
		return $this->_last;
	} // end getLastChild();

	/**
	 * Returns the first child of the node. If there are no child nodes
	 * in the current node, the method returns NULL.
	 *
	 * @return Opt_Xml_Node
	 */
	public function getFirstChild()
	{
		return $this->_first;
	} // end getFirstChild();

	/**
	 * Returns the array containing all the children. The method guarantees
	 * the correct element order.
	 *
	 * @return array
	 */
	public function getChildren()
	{
		$array = array();
		$scan = $this->_first;
		while($scan !== null)
		{
			$array[] = $scan;
			$scan = $scan->_next;
		}
		return $array;
	} // end getChildren();

	/**
	 * Returns all the descendants of the current node. They are provided
	 * in the BFS order - the closer descendants come first, then the farther
	 * ones, then even more farther.
	 *
	 * @return array
	 */
	public function getElements()
	{
		$queue = new SplQueue;
		$scan = $this->_first;
		$list = array();
		while($scan !== null)
		{
			$queue->enqueue($scan);
			$list[] = $scan;
			$scan = $scan->_next;
		}
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();
			if($item instanceof Opt_Xml_Scannable)
			{
				$scan = $item->_first;
				while($scan !== null)
				{
					$queue->enqueue($scan);
					$list[] = $scan;
					$scan = $scan->_next;
				}
			}
		}
		return $list;
	} // end getElements();

	/**
	 * Returns all the children or descendants with the specified name.
	 *
	 * @param string $name The tag name (without the namespace)
	 * @param boolean $recursive Scan the descendants recursively?
	 * @return array
	 */
	public function getElementsByTagName($name, $recursive = true)
	{
		if($recursive)
		{
			return $this->_getElementsByTagName($name, null);
		}

		$array = array();
		$scan = $this->_first;
		while($scan !== null)
		{
			if($scan instanceof Opt_Xml_Element)
			{
				if($name == '*' || $scan->getName() == $name)
				{
					$array[] = $scan;
				}
			}
			$scan = $scan->_next;
		}
		return $array;
	} // end getElementsByTagName();

	/**
	 * Returns all the children or descendants with the specified name
	 * and namespace.
	 *
	 * @param string $namespace The tag namespace
	 * @param string $name The tag name
	 * @param boolean $recursive Scan the descendants recursively?
	 * @return array
	 */
	public function getElementsByTagNameNS($namespace, $name, $recursive = true)
	{
		if($recursive)
		{
			return $this->_getElementsByTagName($name, $namespace);
		}

		$array = array();
		$scan = $this->_first;
		while($scan !== null)
		{
			if($scan instanceof Opt_Xml_Element)
			{
				if(($name == '*' || $scan->getName() == $name) && ($namespace == '*' || $scan->getNamespace() == $namespace))
				{
					$array[] = $scan;
				}
			}
			$scan = $scan->_next;
		}
		return $array;
	} // end getElementsByTagNameNS();

	/**
	 * Returns all the descendants with the specified name and namespace.
	 * Contrary to getElementsByTagNameNS(), the method does not go into
	 * the matching descendants.
	 *
	 * @param string $ns The namespace name
	 * @param string $name The tag name
	 * @return array
	 */
	public function getElementsExt($ns, $name)
	{
		return $this->_getElementsByTagName($name, $ns, true);
	} // end getElementsExt();

	/**
	 * Sorts the children with the order specified in the associative
	 * array. The array must contain the pairs 'tag name' => order. Moreover,
	 * it must contain the '*' element representing the new location of
	 * the rest of the nodes.
	 *
	 * @param array $prototypes The required order.
	 */
	public function sort(Array $prototypes)
	{
		$this->_prototypes = $prototypes;
		if(!isset($prototypes['*']))
		{
			throw new Opt_APINoWildcard_Exception;
		}
		// To create a stable sort.
		$scan = $this->_first;
		$array = array();
		$i = 0;
		while($scan !== null)
		{
			$array[] = $scan;
			$scan->set('_ssort', $i++);
			$scan = $scan->_next;
		}
		// Sort!
		usort($array, array($this, 'sortCmp'));

		// Apply new connections.
		$previous = null;
		foreach($array as $item)
		{
			if($previous === null)
			{
				$this->_first = $item;
				$item->_previous = null;
			}
			else
			{
				$item->_previous = $previous;
				$previous->_next = $item;	
			}
			$item->_next = null;
			$this->_last = $item;
			$previous = $item;
		}
		$this->_prototypes = null;
	} // end sort();

	/**
	 * Moves the specified node to the end of the children list.
	 *
	 * @param integer|Opt_Xml_Node $node The node to be moved.
	 * @return boolean
	 */
	public function bringToEnd($node)
	{
		if(is_integer($node))
		{
			$i = 0;
			$scan = $this->_first;
			while($scan !== null)
			{
				if($i == $node)
				{
					$node = $scan;
					break;
				}
				$scan = $scan->_next;
				$i++;
			}
			if(!is_object($node))
			{
				return false;
			}
		}

		// Check if this is really our child. We cannot exterminate the
		// children of other nodes.
		if($node->_parent !== $this)
		{
			return false;
		}

		// The border cases...
		if($this->_last === $node)
		{
			return true;
		}
		if($this->_first === $node)
		{
			$this->_first = $node->_next;
		}

		// Unlink it.
		if($node->_previous !== null)
		{
			$node->_previous->_next = $node->_next;
		}
		if($node->_next !== null)
		{
			$node->_next->_previous = $node->_previous;
		}
		$this->_last->_next = $node;
		$node->_previous = $this->_last;
		$node->_next = null;
		return true;
	} // end bringToEnd();

	/**
	 * The cloning helper that clones also all the descendants. The cloning algorithm
	 * does not use true recursion, so that it can be safely used even with very deep
	 * trees.
	 *
	 * @internal
	 */
	final public function __clone()
	{
		if($this->get('__nrc') === true)
		{
			// In this state, we do not clone the subnodes, because some else node takes
			// care of it
			$this->set('__nrc', NULL);
			$this->_first = null;
			$this->_last = null;
			$this->_cloneHandler();
		}
		else
		{
			// Prepare the recursiveless cloning operation.
			$this->_cloneHandler();
			$queue = new SplQueue;
			$scan = $this->_first;
			while($scan !== null)
			{
				$obj = new SplFixedArray(2);
				$obj[0] = $scan;
				$obj[1] = $this;
				$queue->enqueue($obj);
				$scan = $scan->_next;
			}

			// Main cloning loop
			$this->_first = null;
			$this->_last = null;
			while($queue->count() > 0)
			{
				$pair = $queue->dequeue();
				$pair[0]->set('__nrc', true);
				$pair[1]->appendChild($clone = clone $pair[0]);
				if($pair[0] instanceof Opt_Xml_Scannable)
				{
					$scan = $pair[0]->_first;
					while($scan !== null)
					{
						$obj = new SplFixedArray(2);
						$obj[0] = $scan;
						$obj[1] = $clone;
						$queue->enqueue($obj);
						$scan = $scan->_next;
					}
				}
			}
		}
	} // end __clone();

	/**
	 * Removes the connections between all the descendants so that they can
	 * be safely collected by the PHP garbage collector. Remember to use this
	 * method before you free the last reference to the root node or you will
	 * get a memory leak.
	 *
	 * Although PHP 5.3 provides a cycle detection GC, but it may be disabled
	 * and furthermore it seems that is still quite buggy and produces some
	 * memory leaks. Thus, we recommend the manual way of freeing the nodes.
	 */
	public function dispose()
	{
		$queue = new SplQueue;
		$scan = $this->_first;
		while($scan !== null)
		{
			$queue->enqueue($scan);
			$scan = $scan->_next;
		}
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();
			if($item instanceof Opt_Xml_Scannable)
			{
				$scan = $item->_first;
				while($scan !== null)
				{
					$queue->enqueue($scan);
					$scan = $scan->_next;
				}
			}
			$item->_dispose();
		}
		$this->unmount();
		$this->_dispose();
	} // end dispose();

	/**
	 * Extra dispose function.
	 *
	 * @internal
	 */
	protected function _dispose()
	{
		parent::_dispose();
		$this->_first = null;
		$this->_last = null;
	} // end _dispose();

	/**
	 * An implementation of the method from the Iterator interface. It
	 * sets the internal collection pointer to the first element of the
	 * collection.
	 */
	public function rewind()
	{
		$this->_iterator = $this->_first;
		$this->_position = 0;
	} // end rewind();

	/**
	 * An implementation of the method from the Iterator interface. It tests
	 * whether the current collection pointer is valid and returns it as
	 * a 'true' or 'false' value.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return ($this->_iterator !== null);
	} // end valid();

	/**
	 * An implementation of the method from the Iterator interface. Returns
	 * the element currently visited by the collection pointer. If the pointer
	 * is invalid, the method returns 'null'.
	 *
	 * @return Opt_Xml_Scannable
	 */
	public function current()
	{
		return $this->_iterator;
	} // end current();

	/**
	 * An implementation of the method from the Iterator interface. Moves the
	 * collection pointer to the next element. Please note this method assumes
	 * that the current pointer is valid.
	 *
	 * @throws OutOfBoundsException
	 */
	public function next()
	{
		if($this->_iterator === null)
		{
			throw new OutOfBoundsException('Opt_Xml_Scannable has already reached the end of a collection.');
		}
		if($this->_iterator === -1)
		{
			$this->_iterator = $this->_first;
			$this->_position = 0;
		}
		else
		{
			$this->_iterator = $this->_iterator->_next;
			$this->_position++;
		}
	} // end next();

	/**
	 * An implementation of the method from the Iterator interface. Returns
	 * the key of the current pointer position in a collection.
	 * 
	 * @return integer
	 */
	public function key()
	{
		return $this->_position;
	} // end key();

	/**
	 * The method is used by the sort() method to compare the two nodes. Returns
	 * the comparison result.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node1 The first node.
	 * @param Opt_Xml_Node $node2 The second node.
	 * @return integer
	 */
	final public function sortCmp($node1, $node2)
	{
		$name1 = (string)$node1;
		$name2 = (string)$node2;
		if(!isset($this->_prototypes[$name1]))
		{
			$name1 = '*';
		}
		if(!isset($this->_prototypes[$name2]))
		{
			$name2 = '*';
		}
		if($this->_prototypes[$name1] == $this->_prototypes[$name2])
		{
			if($node1->get('_ssort') < $node2->get('_ssort'))
			{
				return -1;
			}
			return 1;
		}
		elseif($this->_prototypes[$name1] < $this->_prototypes[$name2])
		{
			return -1;
		}
		return +1;
	} // end sortCmp();

	/**
	 * A helper method for non-recursive tree search of the elements with
	 * the specified name and namespace in order not to implement the
	 * same algorithm twice. Returns a list of matching elements. If the
	 * last argument is set to 'true', the method does not visit the descendants
	 * of matching elements.
	 *
	 * @internal
	 * @param string $name The name to look for
	 * @param string $ns The namespace to look for
	 * @param boolean $skipMatching Skip visiting descendants of matching nodes?
	 * @return array
	 */
	final protected function _getElementsByTagName($name, $ns, $skipMatching = false)
	{
		$queue = new SplQueue;
		$scan = $this->_first;
		$list = array();
		while($scan !== null)
		{
			$queue->enqueue($scan);
			$scan = $scan->_next;
		}
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();

			// Match the element.
			if($item instanceof Opt_Xml_Element)
			{
				if($ns === null)
				{
					if($item->getName() == $name || $name == '*')
					{
						$list[] = $item;
						if($skipMatching)
						{
							continue;
						}
					}
				}
				elseif(($item->getName() == $name || $name == '*') && ($item->getNamespace() == $ns || $ns == '*'))
				{
					$list[] = $item;
					if($skipMatching)
					{
						continue;
					}
				}
			}

			// If the element may have some children, we need to visit them, too.
			if($item instanceof Opt_Xml_Scannable)
			{
				$scan = $item->_first;
				while($scan !== null)
				{
					$queue->enqueue($scan);
					$scan = $scan->_next;
				}
			}
		}
		return $list;
	} // end _getElementsByTagName();

	/**
	 * Checks whether the node can be added to this collection. If the specified
	 * node cannot be inserted into a collection, the method is supposed to
	 * return 'Opt_APIInvalidNodeType_Exception'
	 *
	 * @throws Opt_APIInvalidNodeType_Exception
	 * @param Opt_Xml_Node $node The node to test
	 */
	protected function _testNode(Opt_Xml_Node $node)
	{
		/* null */
	} // end _testNode();
} // end Opt_Xml_Scannable;
