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
 * The base exception class for Open Power Template compiler
 * messages.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_Compiler_Exception extends Opt_Exception
{
	/**
	 * The template, where the exception occured.
	 *
	 * @var string
	 */
	private $_template;


	/**
	 * Sets the template name, where the exception occured.
	 *
	 * @param string $template The template name.
	 */
	public function setTemplate($template)
	{
		$this->_template = $template;
	} // end setTemplate();

	/**
	 * Returns the template name where the exception occured.
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->_template;
	} // end getTemplate();
} // end Opt_Exception;