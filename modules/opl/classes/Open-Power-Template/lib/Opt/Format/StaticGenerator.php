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
 * A format class that generates the necessary section content on the fly. The
 * generation is obligatory, contrary to "RuntimeGenerator". Note that the data
 * format itself does not contain any other stuff for section and must decorate
 * something.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_StaticGenerator extends Opt_Format_Abstract
{
	/**
	 * The list of supported hook types.
	 * @var array
	 */
	protected $_supports = array(
		'section'
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

			// Choose the data source.
			if(!is_null($section['parent']))
			{
				$parent = Opt_Instruction_BaseSection::getSection($section['parent']);
				$parent['format']->assign('item', $section['name']);
				$ds = $parent['format']->get('section:variable');
			}
			elseif(!is_null($section['datasource']))
			{
				$ds = $section['datasource'];
			}
			else
			{
				$this->assign('item', $section['name']);
				$ds = $this->get('variable:main');
			}

			// This format must be decorated by the user, because it does not support directly the rest
			// of "section" namespace.
			if(!is_object($this->_decorated))
			{
				throw new Opt_FormatNotDecorated_Exception('StaticGenerator');
			}

			return ' $_sect'.$section['name'].'_vals = array(); if('.$ds.' instanceof Opt_Generator_Interface){ $_sect'.$section['name'].'_vals = '.$ds.'->generate(\''.$section['name'].'\'); } ';
		}
	} // end _build();
} // end Opt_Format_StaticGenerator;
