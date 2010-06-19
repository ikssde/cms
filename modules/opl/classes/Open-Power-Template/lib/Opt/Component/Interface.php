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
 * The interface for writing components.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Component_Interface
{
	/**
	 * Constructs a new component object.
	 *
	 * @param string $name The component name - "name" parameter passed to the component port.
	 */
	public function __construct($name = '');

	/**
	 * Sets the view the object is going to be deployed in.
	 *
	 * @param Opt_View $view The deploying view.
	 */
	public function setView(Opt_View $view);

	/**
	 * Retrieves the data passed by the "datasource" port attribute.
	 *
	 * @param mixed $data The data source data
	 */
	public function setDatasource($data);

	/**
	 * Sets a component parameter.
	 *
	 * @param string $name The parameter name
	 * @param mixed $value The parameter value
	 */
	public function __set($name, $value);
	/**
	 * Returns the component parameter value.
	 *
	 * @param string $name The parameter name
	 * @return mixed
	 */
	public function __get($name);

	/**
	 * Returns true, if the specified parameter is
	 * defined in the component.
	 *
	 * @param string $name The parameter name.
	 * @return boolean
	 */
	public function __isset($name);

	/**
	 * Performs a code injection.
	 *
	 * @params Closure $injection The injected closure with the code to execute.
	 */
	public function setInjection($injection);

	/**
	 * Generates and echoes the HTML field represented by
	 * the component. Note that it **should not** generate any
	 * HTML related to the layout of the field, but just a plain
	 * INPUT or something like this.
	 *
	 * @param array $attributes The associative list of attributes passed to "opt:display".
	 */
	public function display($attributes = array());

	/**
	 * Tests a component event whose name is passed in the argument. It should
	 * return true, if the event occurs, and false otherwise.
	 *
	 * @param string $name The event name
	 * @return boolean
	 */
	public function processEvent($name);

	/**
	 * Captures the attributes of a tag with the "opt:component-attributes" attribute
	 * added. The method should modify them and return the updated atttribute list.
	 * The attributes are passed as an associative array and the extra `$tagName` argument
	 * allows to recognize what tag the method is going to modify. The identifier
	 * specified in the "component-attributes" attribute is passed after the hash symbol, i.e.
	 * "div#foo" for **<div opt:component-attributes="foo">** tag.
	 *
	 * @param string $tagName The tag name (with the identifier)
	 * @param array $attributes The associative list of attributes.
	 * @return array
	 */
	public function manageAttributes($tagName, Array $attributes);
} // end Opt_Component_Interface;