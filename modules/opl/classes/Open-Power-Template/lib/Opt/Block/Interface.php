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
 * The interface for writing blocks.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Block_Interface
{
	/**
	 * Sets the view the object is going to be deployed in.
	 *
	 * @param Opt_View $view The deploying view.
	 */
	public function setView(Opt_View $view);

	/**
	 * An action performed for block opening tag. The method may
	 * return a boolean value to specify whether to display the
	 * block content or not.
	 *
	 * @param array $attributes An associative list of block tag attributes.
	 * @return boolean
	 */
	public function onOpen(array $attributes);

	/**
	 * An action performed for block closing tag.
	 */
	public function onClose();

	/**
	 * An action performed for single block tag.
	 * 
	 * @param array $attributes An associative list of block tag attributes.
	 */
	public function onSingle(array $attributes);
} // end Opt_Block_Interface;