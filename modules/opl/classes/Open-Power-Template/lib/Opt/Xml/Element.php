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
 * The class represents an XML tag.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 */
class Opt_Xml_Element extends Opt_Xml_Scannable
{
	protected $_name;
	protected $_namespace;
	protected $_attributes;

	protected $_postprocess = null;

	/**
	 * Creates a new XML tag with the specified name. The accepted
	 * name format is 'name' or 'namespace:name'.
	 *
	 * @param String $name The tag name.
	 */
	public function __construct($name)
	{
		parent::__construct();
		$this->setName($name);
	} // end __construct();

	/**
	 * Sets the name for the tag. The accepted format is 'name' or
	 * 'namespace:name'.
	 *
	 * @param String $name The tag name.
	 */
	public function setName($name)
	{
		if(strpos($name, ':') !== false)
		{
			$data = explode(':', $name);
			$this->_name = $data[1];
			$this->_namespace = $data[0];
		}
		else
		{
			$this->_name = $name;
		}
	} // end setName();

	/**
	 * Sets the namespace for the tag.
	 *
	 * @param String $namespace The namespace name.
	 */
	public function setNamespace($namespace)
	{
		$this->_namespace = $namespace;
	} // end setNamespace();

	/**
	 * Returns the tag name (without the namespace).
	 * @return String
	 */
	public function getName()
	{
		return $this->_name;
	} // end getName();

	/**
	 * Returns the tag namespace name.
	 * @return String
	 */
	public function getNamespace()
	{
		return $this->_namespace;
	} // end getNamespace();

	/**
	 * Returns the tag name (with the namespace, if possible)
	 *
	 * @return String
	 */
	public function getXmlName()
	{
		if(is_null($this->_namespace))
		{
			return $this->_name;
		}
		return $this->_namespace.':'.$this->_name;
	} // end getXmlName();

	/**
	 * Returns the list of attribute objects.
	 *
	 * @return Array
	 */
	public function getAttributes()
	{
		if(!is_array($this->_attributes))
		{
			return array();
		}
		return $this->_attributes;
	} // end getAttributes();

	/**
	 * Returns the attribute with the specified name.
	 *
	 * @param String $xmlName The XML name of the attribute (with the namespace)
	 * @return Opt_Xml_Attribute
	 */
	public function getAttribute($xmlName)
	{
		if(!is_array($this->_attributes))
		{
			return NULL;
		}
		if(!isset($this->_attributes[$xmlName]))
		{
			return NULL;
		}
		return $this->_attributes[$xmlName];
	} // end getAttribute();

	/**
	 * Adds a new attribute to the tag.
	 *
	 * @param Opt_Xml_Attribute $attribute The new attribute.
	 */
	public function addAttribute(Opt_Xml_Attribute $attribute)
	{
		if(!is_array($this->_attributes))
		{
			$this->_attributes = array();
		}
		$this->_attributes[$attribute->getXmlName()] = $attribute;
	} // end addAttribute();

	/**
	 * Removes the specified attribute identified either by the object
	 * or by the XML name.
	 *
	 * @param String|Opt_Xml_Attribute $refNode The attribute to be removed
	 * @return Boolean
	 */
	public function removeAttribute($refNode)
	{
		if(!is_array($this->_attributes))
		{
			return NULL;
		}
		if(is_object($refNode))
		{
			foreach($this->_attributes as $id => $node)
			{
				if($node === $refNode)
				{
					unset($this->_attributes[$id]);
					return true;
				}
			}
		}
		elseif(is_string($refNode))
		{
			if(isset($this->_attributes[$refNode]))
			{
				unset($this->_attributes[$refNode]);
				return true;
			}
		}
		return false;
	} // end removeAttribute();

	/**
	 * Clears the attribute list.
	 */
	public function removeAttributes()
	{
		$this->_attributes = array();
	} // end removeAttributes();

	/**
	 * Returns 'true', if the tag contains attributes.
	 *
	 * @return Boolean
	 */
	public function hasAttributes()
	{
		if(!is_array($this->_attributes))
		{
			return false;
		}
		return (sizeof($this->_attributes) > 0);
	} // end hasAttributes();

	/**
	 * Returns the XML tag name.
	 * @return String
	 */
	public function __toString()
	{
		return $this->getXmlName();
	} // end __toString();

	/**
	 * The method helps to clone the XML node by cloning
	 * its attributes.
	 *
	 * @internal
	 */
	protected function _cloneHandler()
	{
		if(is_array($this->_attributes))
		{
			foreach($this->_attributes as $name => $attribute)
			{
				$this->_attributes[$name] = clone $attribute;
			}
		}
	} // end _cloneHandler();

	/**
	 * Specifies, what node types can be children of XML tags.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node
	 */
	protected function _testNode(Opt_Xml_Node $node)
	{
		if($node->getType() != 'Opt_Xml_Element' && $node->getType() != 'Opt_Xml_Text' && $node->getType() != 'Opt_Xml_Comment')
		{
			throw new Opt_APIInvalidNodeType_Exception('Opt_Xml_Element', $node->getType());
		}
	} // end _testNode();

	/**
	 * This function is executed by the compiler before the second compilation stage,
	 * processing. It migrates syntax from OPT 2.0 to OPT 2.1
	 */
	public function preMigrate(Opt_Compiler_Class $compiler)
	{
		if($compiler->isNamespace($this->getNamespace()))
		{
			$name = $this->getXmlName();
			//$this->_processXml($compiler, false);

			// Look for the processor
			if(!is_null($processor = $compiler->isInstruction($name)))
			{
				$processor->migrateNode($this);
			}
			elseif($compiler->isComponent($name))
			{
				$processor = $compiler->processor('component');
				$processor->migrateComponent($this);
			}
			elseif($compiler->isBlock($name))
			{
				$processor = $compiler->processor('block');
				$processor->migrateBlock($this);
			}

			if(is_object($processor))
			{
				$this->set('hidden', false);
				$compiler->setChildren($processor->getQueue());
			}
			elseif($this->get('processAll'))
			{
				$this->set('hidden', false);
				$compiler->setChildren($this);
			}
			else
			{
				// Remember to set the hidden state IF AND ONLY IF
				// it is not set.
				$this->get('hidden') === null and $this->set('hidden', true);
			}
		}
		else
		{
			$this->_processMigration($compiler);
			$this->set('hidden', false);
			if($this->hasChildren())
			{
				$compiler->setChildren($this);
			}
		}
	} // end preMigrate();

	/**
	 * Function processes attributes and changes them to new syntax.
	 *
	 * @internal
	 * @param Opt_Compiler_Class $compiler Compiler class
	 */
	private function _processMigration(Opt_Compiler_Class $compiler)
	{
		if(!$this->hasAttributes())
		{
			return array();
		}
		foreach($this->getAttributes() as $attr)
		{
			if($compiler->isNamespace($attr->getNamespace()))
			{
				switch($attr->getNamespace())
				{
					case 'parse':
						$this->removeAttribute($attr->getXmlName());
						$attr->setValue('parse:'.$attr->getValue());
						$attr->setNamespace(null);
						$this->addAttribute($attr);
						break;
					case 'str':
						$this->removeAttribute($attr->getXmlName());
						$attr->setValue('str:'.$attr->getValue());
						$attr->setNamespace(null);
						$this->addAttribute($attr);
						break;
					case 'opt':
						if(($processor = $compiler->getAttribute($attr->getXmlName())) !== null && $processor->attributeNeedMigration($attr))
						{
							$this->removeAttribute($attr->getXmlName());
							$attr = $processor->migrateAttribute($attr);
							$this->addAttribute($attr);
						}
						break;
				}
			}
		}
	}

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	public function postMigrate(Opt_Compiler_Class $compiler)
	{
		/*
		if(sizeof($this->_postprocess) > 0)
		{
			$this->_postprocessXml($compiler);
		}
		$this->_postprocess = null;

		if($this->get('postprocess'))
		{
			if(!is_null($processor = $compiler->isInstruction($this->getXmlName())))
			{
				$processor->postprocessNode($this);
			}
			elseif($compiler->isComponent($this->getXmlName()))
			{
				$processor = $compiler->processor('component');
				$processor->postprocessComponent($this);
			}
			elseif($compiler->isBlock($this->getXmlName()))
			{
				$processor = $compiler->processor('block');
				$processor->postprocessBlock($this);
			}
			else
			{
				throw new Opt_UnknownProcessor_Exception($this->getXmlName());
			}
		}
		*/
	} // end postMigrate();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	public function preProcess(Opt_Compiler_Class $compiler)
	{
		if($compiler->isNamespace($this->getNamespace()))
		{
			$name = $this->getXmlName();
			$this->_processXml($compiler, false);

			// Look for the processor
			if($compiler->hasAmbiguous($this->getXmlName()))
			{
				$processor = $this->get('priv:ambiguous');
				$processor->processNode($this);
			}
			elseif(($processor = $compiler->isInstruction($name)) !== null)
			{
				$processor->processNode($this);
			}
			elseif($compiler->isComponent($name))
			{
				$processor = $compiler->processor('component');
				$processor->processComponent($this);
			}
			elseif($compiler->isBlock($name))
			{
				$processor = $compiler->processor('block');
				$processor->processBlock($this);
			}

			if(is_object($processor))
			{
				$this->set('hidden', false);
				$compiler->setChildren($processor->getQueue());
			}
			elseif($this->get('processAll'))
			{
				$this->set('hidden', false);
				$compiler->setChildren($this);
			}
			else
			{
				// Remember to set the hidden state IF AND ONLY IF
				// it is not set.
				$this->get('hidden') === null and $this->set('hidden', true);
			}
		}
		else
		{
			$this->_processXml($compiler, true);
			$this->set('hidden', false);
			if($this->hasChildren())
			{
				$compiler->setChildren($this);
			}
		}
	} // end preProcess();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	public function postProcess(Opt_Compiler_Class $compiler)
	{
		if(sizeof($this->_postprocess) > 0)
		{
			$this->_postprocessXml($compiler);
		}
		$this->_postprocess = null;

		if($this->get('postprocess'))
		{
			if($compiler->hasAmbiguous($this->getXmlName()))
			{
				$this->get('priv:ambiguous')->postprocessNode($this);
			}
			elseif(($processor = $compiler->isInstruction($this->getXmlName())) !== null)
			{
				$processor->postprocessNode($this);
			}
			elseif($compiler->isComponent($this->getXmlName()))
			{
				$processor = $compiler->processor('component');
				$processor->postprocessComponent($this);
			}
			elseif($compiler->isBlock($this->getXmlName()))
			{
				$processor = $compiler->processor('block');
				$processor->postprocessBlock($this);
			}
			else
			{
				throw new Opt_UnknownProcessor_Exception($this->getXmlName());
			}
		}
	} // end postProcess();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking.
	 */
	public function preLink(Opt_Compiler_Class $compiler)
	{
		if($compiler->isNamespace($this->getNamespace()))
		{
			// This code handles the XML elements that represent the
			// OPT instructions. They have shorter code, because
			// we do not need to display their tags.
			if(!$this->hasChildren() && $this->get('single'))
			{
				$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_SINGLE_BEFORE));
			}
			elseif($this->hasChildren())
			{
				$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_OPENING_BEFORE,
					Opt_Xml_Buffer::TAG_OPENING_AFTER, Opt_Xml_Buffer::TAG_CONTENT_BEFORE));

				$compiler->setChildren($this);
			}
			else
			{
				$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_OPENING_BEFORE,
					Opt_Xml_Buffer::TAG_OPENING_AFTER, Opt_Xml_Buffer::TAG_CONTENT_BEFORE));
			}
		}
		else
		{
			// This code is executed for normal tags that must be rendered.
			// The first thing is to check if we are a root node, because we must add the "xmlns"
			// attributes there.
		/*	if($this->get('rootNode') === true)
			{
				if(!$this->getParent() instanceof Opt_Xml_Root)
				{
					throw new Opt_APIInvalidParent_Exception($this->getType(), $this->getParent()->getType(), 'Opt_Xml_Root');
				}
				foreach($this->getParent()->getNamespaces() as $prefix => $uri)
				{
					$newAttr = new Opt_Xml_Attribute($prefix, $uri);
					$newAttr->setNamespace('xmlns');
					$this->addAttribute($newAttr);
				}
			} */

			// Now construct the output code...
			$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_OPENING_BEFORE));
			if($this->bufferSize(Opt_Xml_Buffer::TAG_NAME) == 0)
			{
				$name = $this->getXmlName();
			}
			elseif($this->bufferSize(Opt_Xml_Buffer::TAG_NAME) == 1)
			{
				$name = $this->buildCode(Opt_Xml_Buffer::TAG_NAME);
			}
			else
			{
				throw new Opt_CompilerCodeBufferConflict_Exception(1, 'TAG_NAME', $this->getXmlName());
			}
			if(!$this->hasChildren() && $this->bufferSize(Opt_Xml_Buffer::TAG_CONTENT) == 0 && $this->get('single'))
			{
				$compiler->appendOutput('<'.$name.$this->_linkAttributes().' />'.$this->buildCode(Opt_Xml_Buffer::TAG_SINGLE_AFTER,Opt_Xml_Buffer::TAG_AFTER));
			}
			else
			{
				$compiler->appendOutput('<'.$name.$this->_linkAttributes().'>'.$this->buildCode(Opt_Xml_Buffer::TAG_OPENING_AFTER));
				$this->set('_name', $name);
				if($this->bufferSize(Opt_Xml_Buffer::TAG_CONTENT) > 0)
				{
					$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, Opt_Xml_Buffer::TAG_CONTENT, Opt_Xml_Buffer::TAG_CONTENT_AFTER));
				}
				elseif($this->hasChildren())
				{
					$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_CONTENT_BEFORE));
					$compiler->setChildren($this);
				}
			}
		}
	} // end preLink();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	public function postLink(Opt_Compiler_Class $compiler)
	{
		if($compiler->isNamespace($this->getNamespace()))
		{
			if($this->get('single'))
			{
				$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_SINGLE_AFTER, Opt_Xml_Buffer::TAG_AFTER));
			}
			else
			{
				$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_CONTENT_AFTER, Opt_Xml_Buffer::TAG_CLOSING_BEFORE,
					Opt_Xml_Buffer::TAG_CLOSING_AFTER, Opt_Xml_Buffer::TAG_AFTER));
			}
		}
		elseif($this->hasChildren() || $this->bufferSize(Opt_Xml_Buffer::TAG_CONTENT) != 0 || !$this->get('single'))
		{
			$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_CONTENT_AFTER, Opt_Xml_Buffer::TAG_CLOSING_BEFORE).'</'.$this->get('_name').'>'.$this->buildCode(Opt_Xml_Buffer::TAG_CLOSING_AFTER, Opt_Xml_Buffer::TAG_AFTER));
			$this->set('_name', NULL);
		}
	} // end postLink();

	/**
	 * Looks for special OPT attributes in the element attribute list and
	 * processes them. Returns the list of nodes that need to be postprocessed.
	 *
	 * @internal
	 * @param Opt_Compiler_Class $compiler The compiler.
	 * @param Boolean $specialNs Do we recognize special namespaces?
	 */
	protected function _processXml(Opt_Compiler_Class $compiler, $specialNs = true)
	{
		if(!$this->hasAttributes())
		{
			return array();
		}
		$attributes = $this->getAttributes();
		$this->_postprocess = array();
		$opt = Opl_Registry::get('opt');

		// Look for special OPT attributes
		foreach($attributes as $attr)
		{
			if($compiler->isNamespace($attr->getNamespace()))
			{
				$xml = $attr->getXmlName();
				if(($processor = $compiler->isOptAttribute($xml)) !== null)
				{
					$processor->processAttribute($this, $attr);
					if($attr->get('postprocess'))
					{
						$this->_postprocess[] = array($processor, $attr);
					}
				}
				$this->removeAttribute($xml);
			}
			else
			{
				$compiler->compileAttribute($attr);
			}
		}
	} // end _processXml();

	/**
	 * Runs the postprocessors for the specified attributes.
	 *
	 * @internal
	 * @param Opt_Compiler_Class $compiler The compiler.
	 */
	protected function _postprocessXml(Opt_Compiler_Class $compiler)
	{
		$cnt = sizeof($this->_postprocess);
		for($i = 0; $i < $cnt; $i++)
		{
			$this->_postprocess[$i][0]->postprocessAttribute($this, $this->_postprocess[$i][1]);
		}
	} // end _postprocessXml();

	/**
	 * Links the element attributes into a valid XML code and returns
	 * the output code.
	 *
	 * @internal
	 * @param Opt_Xml_Element $subitem The XML element.
	 * @return String
	 */
	protected function _linkAttributes()
	{
		// Links the attributes into the PHP code
		if($this->hasAttributes() || $this->bufferSize(Opt_Xml_Buffer::TAG_BEGINNING_ATTRIBUTES) > 0 || $this->bufferSize(Opt_Xml_Buffer::TAG_ENDING_ATTRIBUTES) > 0)
		{

			$code = $this->buildCode(Opt_Xml_Buffer::TAG_ATTRIBUTES_BEFORE, Opt_Xml_Buffer::TAG_BEGINNING_ATTRIBUTES);
			$attrList = $this->getAttributes();
			// Link attributes into a string
			foreach($attrList as $attribute)
			{
				$s = $attribute->bufferSize(Opt_Xml_Buffer::ATTRIBUTE_NAME);
				switch($s)
				{
					case 0:
						$code .= $attribute->buildCode(Opt_Xml_Buffer::ATTRIBUTE_BEGIN).' '.$attribute->getXmlName();
						break;
					case 1:
						$code .= ($attribute->bufferSize(Opt_Xml_Buffer::ATTRIBUTE_BEGIN) == 0 ? ' ' : '').$attribute->buildCode(Opt_Xml_Buffer::ATTRIBUTE_BEGIN, ' ', Opt_Xml_Buffer::ATTRIBUTE_NAME);
						break;
					default:
						throw new Opt_CompilerCodeBufferConflict_Exception(1, 'ATTRIBUTE_NAME', $this->getXmlName());
				}

				if($attribute->bufferSize(Opt_Xml_Buffer::ATTRIBUTE_VALUE) == 0)
				{
					// Static value
					$tpl = Opl_Registry::get('opt');
					if(!($tpl->htmlAttributes && $attribute->getValue() == $attribute->getName()))
					{
						$code .= '="'.htmlspecialchars($attribute->getValue()).'"';
					}
				}
				else
				{
					$code .= '="'.$attribute->buildCode(Opt_Xml_Buffer::ATTRIBUTE_VALUE).'"';
				}
				$code .= $attribute->buildCode(Opt_Xml_Buffer::ATTRIBUTE_END);
			}
			return $code.$this->buildCode(Opt_Xml_Buffer::TAG_ENDING_ATTRIBUTES, Opt_Xml_Buffer::TAG_ATTRIBUTES_AFTER);
		}
		return '';
	} // end _linkAttributes();
} // end Opt_Xml_Element;
