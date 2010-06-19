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
 * This data format informs the decorated formats that they
 * should refer to the global template data, shared across
 * the views.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Formats
 */
class Opt_Format_Global extends Opt_Format_Abstract
{
	/**
	 * Notify the decorated format that it should refer
	 * to the global data.
	 */
	protected function _onDecorate()
	{
		$this->_decorated->assign('global', true);
	} // end _onDecorate();

	/**
	 * In this particular format, the method does nothing.
	 *
	 * @param string $hookName The hook name
	 * @return NULL
	 */
	protected function _build($hookName)
	{
		if(!$this->isDecorating())
		{
			throw new Opt_FormatNotDecorated_Exception('Global');
		}
		return NULL;
	} // end _build();
} // end Opt_Format_Global;