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
 * The interface for writing data generators for
 * StaticGenerator and RuntimeGenerator data formats.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Generator_Interface
{
	/**
	 * The method should generate the data for the section. The section
	 * name is guaranteed to be passed in the argument.
	 *
	 * @param string $what The section name from the template
	 * @return mixed
	 */
	public function generate($what);
} // end Opt_Generator_Interface;