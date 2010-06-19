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
 * Implementation of XML comments.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 */
class Opt_Xml_Comment extends Opt_Xml_Cdata
{
	public function __construct($cdata = '')
	{
		parent::__construct($cdata);
		$this->set('commented', true);
	} // end __construct();

	protected function _validate(&$text)
	{
		if(strpos($text, '--') !== false)
		{
			throw new Opt_XmlComment_Exception('--');
		}
		return true;
	} // end _validate();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	public function preProcess(Opt_Compiler_Class $compiler)
	{
		$tpl = Opl_Registry::get('opt');
		if($tpl->printComments)
		{
			$this->set('hidden', false);
		}
		else
		{
			$this->set('hidden', true);
		}
	} // end preProcess();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	public function postProcess(Opt_Compiler_Class $compiler)
	{

	} // end postProcess();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking.
	 */
	public function preLink(Opt_Compiler_Class $compiler)
	{
		$compiler->appendOutput((string)$this);
	} // end preLink();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	public function postLink(Opt_Compiler_Class $compiler)
	{

	} // end postLink();
} // end Opt_Xml_Comment;
