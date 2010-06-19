<?php
/**
 * Extra mock component used in testing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_Mock_Component implements Opt_Component_Interface
{
	/**
	 * The parameter list.
	 * @var array
	 */
	private $_params = array();

	/**
	 * The component injection.
	 * @var Closure
	 */
	private $_injection = null;

	/**
	 * Creates the component object.
	 * @param string $name The component name.
	 */
	public function __construct($name = '')
	{
		/* null */
	} // end __construct();

	/**
	 * Sets the component view.
	 * @param Opt_View $view The view
	 */
	public function setView(Opt_View $view)
	{
		echo "VIEW PASSED\r\n";
	} // end setView();

	/**
	 * Sets the data source.
	 * @param mixed $data The data source
	 */
	public function setDatasource($data)
	{
		if(is_array($data))
		{
			echo "DATASOURCE PASSED\r\n";
		}
	} // end setDatasource();

	/**
	 * Injects the procedure.
	 *
	 * @param Closure $data The procedure closure.
	 */
	public function setInjection($data)
	{
		$this->_injection = $data;
	} // end setDatasource();

	/**
	 * Sets the component parameter.
	 * @param string $name The parameter name.
	 * @param mixed $value The parameter value.
	 */
	public function __set($name, $value)
	{
		$this->_params[$name] = $value;
		echo "PARAM ".$name." PASSED\r\n";
	} // end set();

	/**
	 * Returns the component value.
	 * @param string $name The parameter name.
	 * @return mixed
	 */
	public function __get($name)
	{
		echo "PARAM ".$name." RETURNED\r\n";
		return $this->_params[$name];
	} // end set();

	/**
	 * Is the parameter defined?
	 * @param string $name The parameter name.
	 * @return boolean
	 */
	public function __isset($name)
	{
		/* null */
	} // end defined();

	/**
	 * Displays the component.
	 * @param array $attributes opt:display attributes.
	 */
	public function display($attributes = array())
	{
		if(sizeof($attributes) == 0)
		{
			echo "COMPONENT DISPLAYED\r\n";
		}
		else
		{
			echo "COMPONENT DISPLAYED WITH:\r\n";
		}
		if($this->_injection !== null)
		{
			$injection = $this->_injection;
			$injection($this->_params['injected']);
		}
		else
		{
			foreach($attributes as $name => $value)
			{
				echo $name.': '.$value."\r\n";
			}
		}
	} // end display();

	/**
	 * Controls the event launching.
	 *
	 * @param string $name Event name.
	 * @return boolean
	 */
	public function processEvent($name)
	{
		if($name == 'falseEvent')
		{
			echo "FALSE EVENT CHECKED\r\n";
			return false;
		}
		echo "TRUE EVENT LAUNCHED\r\n";
		return true;
	} // end processEvent();

	/**
	 * Performs the attribute management on the specified tag.
	 * @param string $tagName The tag name
	 * @param array $attributes The list of attributes.
	 * @return array
	 */
	public function manageAttributes($tagName, array $attributes)
	{
		echo "ATTRIBUTE MANAGEMENT FOR: ".$tagName."\r\n";
		foreach($attributes as $name => $value)
		{
			echo $name.': '.$value."\r\n";
		}
		return $attributes;
	} // end manageAttributes();
} // end Extra_Mock_Component;
