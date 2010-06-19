<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
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
 * A modification of the default data format where sub-sections are stored in
 * the elements of the upper-level section.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_SingleArray extends Opt_Format_Abstract
{
	/**
	 * Data format properties as information for the
	 * caller.
	 *
	 * @var array
	 */
	protected $_properties = array(
		'section:useReference' => true,
		'section:anyRequests' => null,
		'variable:assign' => true,
		'variable:useReference' => true,
		'item:assign' => true,
		'item:useReference' => true,
		'section:itemAssign' => false,
		'section:variableAssign' => true
	);

	/**
	 * Build a PHP code for the specified hook name.
	 *
	 * @internal
	 * @param String $hookName The hook name
	 * @return String The output PHP code
	 */
	protected function _build($hookName)
	{
		if($hookName == 'section:init')
		{
			$section = $this->_getVar('section');

			if(!is_null($section['parent']))
			{
				$parent = Opt_Instruction_BaseSection::getSection($section['parent']);
				$parent['format']->assign('item', $section['name']);
				if($parent['format']->property('section:useReference'))
				{
					return '$_sect'.$section['name'].'_vals = &'.$parent['format']->get('section:variable').'; ';
				}
				return '$_sect'.$section['name'].'_vals = '.$parent['format']->get('section:variable').'; ';
			}
			elseif(!is_null($section['datasource']))
			{
				return '$_sect'.$section['name'].'_vals = '.$section['datasource'].'; ';
			}
			else
			{
				$this->assign('item', $section['name']);
				return '$_sect'.$section['name'].'_vals = &'.$this->get('variable:main').'; ';
			}
		}
		else
		{
			return parent::_build($hookName);
		}
		return NULL;
	} // end _build();
} // end Opt_Format_SingleArray;
