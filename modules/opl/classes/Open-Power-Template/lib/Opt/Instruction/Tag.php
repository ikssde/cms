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
 * The instruction processor for opt:tag and opt:single elements.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage XML
 */
class Opt_Instruction_Tag extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'tag';

	/**
	 * Array contains deprecated attributes.
	 * @var array
	 */
	protected $_deprecatedAttributes = array();

	/**
	 * Array contains deprecated instructions.
	 * @var array
	 */
	protected $_deprecatedInstructions = array();

	/**
	 * Configures the instruction processor.
	 *
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions('opt:tag');
		$this->_addAttributes('opt:single');
		if($this->_tpl->backwardCompatibility)
		{
			$this->_addAttributes($this->_deprecatedAttributes);
			$this->_addInstructions($this->_deprecatedInstructions);
		}
	} // end configure();

	/**
	 * Migrates the opt:tag node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

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
		/*switch($attr->getName())
		{
			// null
		}*/
		return $attr;
	} // end migrateAttribute();

	/**
	 * Processes the opt:tag element.
	 * @internal
	 * @param Opt_Xml_Node $node The found node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$params = array(
			'name' => array(0 => self::REQUIRED, self::EXPRESSION),
			'ns' => array(0 => self::OPTIONAL, self::EXPRESSION, null),
			'single' => array(0 => self::OPTIONAL, self::BOOL, false)
		);
		$this->_extractAttributes($node, $params);

		// Remove these nodes
		$node->removeAttribute('name');
		$node->removeAttribute('ns');
		$node->removeAttribute('single');
		$node->set('call:attribute-friendly', true);

		if(is_null($params['ns']))
		{
			$node->addBefore(Opt_Xml_Buffer::TAG_NAME, ' echo '.$params['name'].'; ');
		}
		else
		{
			$node->addBefore(Opt_Xml_Buffer::TAG_NAME, ' $_ns = '.$params['ns'].'; echo (!empty($_ns) ? $_ns.\':\' : \'\').'.$params['name'].'; ');
		}

		if($params['single'] == true)
		{
			$node->set('single', true);

		}
		$node->set('postprocess', true);
		$this->_process($node);
	} // end processNode();

	/**
	 * Postprocessing routine for opt:tag element.
	 * @internal
	 * @param Opt_Xml_Node $node The found node.
	 */
	public function postprocessNode(Opt_Xml_Node $node)
	{
		if($node->get('single'))
		{
			$node->removeChildren();
		}
		$node->setNamespace(null);
		$node->setName('__default__');
	} // end postprocessNode();

	/**
	 * Processes the opt:single instruction attribute.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node XML node.
	 * @param Opt_Xml_Attribute $attr XML attribute.
	 * @throws Opt_AttributeInvalidNamespace_Exception
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		if($this->_compiler->isNamespace($node->getNamespace()))
		{
			throw new Opt_AttributeInvalidNamespace_Exception($node->getXmlName());
		}
		if($attr->getValue() == 'yes')
		{
			$attr->set('postprocess', true);
		}
	} // end processAttribute();

	/**
	 * Postprocesses the opt:single instruction attribute.
	 *
	 * @internal
	 * @param Opt_Xml_Node $node XML node.
	 * @param Opt_Xml_Attribute $attr XML attribute.
	 */
	public function postprocessAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		if($attr->getValue() == 'yes')
		{
			$node->set('single', true);
			$node->removeChildren();
		}
	} // end processAttribute();
} // end Opt_Instruction_Tag;
