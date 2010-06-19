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
 * The processor for the classic sections.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Sections
 */
class Opt_Instruction_Section extends Opt_Instruction_Section_Abstract
{
	/**
	 * The processor name - required by the instruction API
	 * @internal
	 * @var string
	 */
	protected $_name = 'section';

	/**
	 * Array contains deprecated attributes.
	 * @var array
	 */
	protected $_deprecatedAttributes = array();

	/**
	 * Array contains deprecated instructions.
	 * @var array
	 */
	protected $_deprecatedInstructions = array('opt:sectionelse', 'opt:showelse');

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:section', 'opt:show'));
		$this->_addAttributes('opt:section');
		$this->_addAmbiguous(array(
			'opt:else' => array('opt:section', 'opt:show'),
			'opt:body' => 'opt:section'
		));
		if($this->_tpl->backwardCompatibility)
		{
			$this->_addAttributes($this->_deprecatedAttributes);
			$this->_addInstructions($this->_deprecatedInstructions);
		}
	} // end configure();

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
	 * Migrates the opt:section attribute.
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
	 * Migrates the opt:section node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function _migrateSection(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end _migrateSection();

	/**
	 * Processes the opt:section tag.
	 * @internal
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _processSection(Opt_Xml_Element $node)
	{
		$section = $this->_sectionCreate($node);
		$this->_sectionStart($section);

		if($node->get('ambiguous:opt:body') !== null)
		{
			$this->_process($node);
		}
		else
		{
			$this->_processBody($node);
		}
	} // end _processSection();

	/**
	 * Processes the opt:body tag for opt:section.
	 * 
	 * @internal
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _processBody(Opt_Xml_Element $node)
	{
		$section = self::getSection($node->get('priv:section'));
		$code = $section['format']->get('section:loopBefore');
		if($section['order'] == 'asc')
		{
			$code .= $section['format']->get('section:startAscLoop');
		}
		else
		{
			$code .= $section['format']->get('section:startDescLoop');
		}
		$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $code);
		$this->processSeparator('$__sect_'.$section['name'], $section['separator'], $node);
		$this->_sortSectionContents($node, 'opt', 'sectionelse');

		$node->set('postprocess', true);
		$this->_process($node);
	} // end _processBody();

	/**
	 * Finishes the processing of the opt:section tag.
	 * @internal
	 * @param Opt_Xml_Element $node The recognized element.
	 */
	protected function _postprocessSection(Opt_Xml_Element $node)
	{
		$section = self::getSection($node->get('priv:section'));
		if(!$node->get('priv:alternative'))
		{
			$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, $section['format']->get('section:endLoop'));
			$this->_sectionEnd($node);
		}
	} // end _postprocessSection();

	/**
	 * Post-processes the opt:body tag for opt:section.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The opt:body tag.
	 */
	protected function _postprocessBody(Opt_Xml_Element $node)
	{
		$this->_postprocessSection($node);
	} // end _postprocessBody();

	/**
	 * Processes the opt:else tag for opt:show.
	 * @internal
	 * @param Opt_Xml_Element $node The recognized element.
	 * @param Opt_Xml_Element $parent The parent of recognized element element.
	 */
	protected function _processShowelse(Opt_Xml_Element $node, Opt_Xml_Element $parent)
	{
		$parent->set('priv:alternative', true);
		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } else { ');
		$this->_process($node);
	} // end _processShowelse();

	/**
	 * Processes the opt:else element, both for opt:show and opt:section.
	 * 
	 * @internal
	 * @param Opt_Xml_Element $node The recognized element.
	 */
	protected function _processElse(Opt_Xml_Element $node)
	{
		$parent = $node->getParent();
		if($parent instanceof Opt_Xml_Element && $parent->getXmlName() == 'opt:section')
		{
			if($parent->get('ambiguous:opt:body') !== null)
			{
				$this->_processShowelse($node, $parent);
			}
			else
			{
				$parent->set('priv:alternative', true);

				$section = self::getSection($parent->get('priv:section'));
				if(!is_array($section))
				{
					throw new Opt_Instruction_Section_Exception('API error: Opt_Instruction_BaseSection::getSection cannot find the section data for the given section while processing opt:else.');
				}
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $section['format']->get('section:endLoop').' } else { ');

				$this->_sectionEnd($parent);

				$this->_process($node);
			}
		}
		elseif($parent instanceof Opt_Xml_Element && $parent->getXmlName() == 'opt:show')
		{
			$this->_processShowelse($node, $parent);
		}
		else
		{
			throw new Opt_Instruction_Section_Exception('Invalid parent of opt:else: opt:section or opt:show expected');
		}
	} // end _processElse();

	/**
	 * Processes the attribute form of opt:section.
	 * @internal
	 * @param Opt_Xml_Node $node The node the section is appended to
	 * @param Opt_Xml_Attribute $attr The section attribute
	 */
	protected function _processAttrSection(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$section = $this->_sectionCreate($node, $attr);
		$this->_sectionStart($section);
		$code = $section['format']->get('section:loopBefore');
		if($section['order'] == 'asc')
		{
			$code .= $section['format']->get('section:startAscLoop');
		}
		else
		{
			$code .= $section['format']->get('section:startDescLoop');
		}
		$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $code);
		$this->processSeparator('$__sect_'.$section['name'], $section['separator'], $node);
		$attr->set('postprocess', true);
	} // end _processAttrSection();

	/**
	 * Finishes the processing of attribute form of opt:section.
	 * @internal
	 * @param Opt_Xml_Node $node The node the section is appended to
	 * @param Opt_Xml_Attribute $attr The section attribute
	 */
	protected function _postprocessAttrSection(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		$section = self::getSection($node->get('priv:section'));
		$node->addBefore(Opt_Xml_Buffer::TAG_AFTER, $section['format']->get('section:endLoop'));
		$this->_sectionEnd($node);
	} // end _postprocessAttrSection();
} // end Opt_Instruction_Section;