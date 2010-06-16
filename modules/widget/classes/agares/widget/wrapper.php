<?php
	/**
	 * @author Agares
	 */

	/**
	 * Widget wrapper, which changes interface from -> GetSomething(), -> SetSomething() to -> something, -> something =
	 */
	class Agares_Widget_Wrapper
	{
		/**
		 * Array of available getters.
		 *
		 * @var array
		 */
		protected static $_getters = array('view');
		/**
		 * Array of available setters.
		 *
		 * @var array
		 */
		protected static $_setters = array();

		/**
		 * The wrapped widget.
		 *
		 * @var Widget_Interface
		 */
		protected $_widget;

		/**
		 * Construct the object.
		 *
		 * @param Widget_Interface $widget The widget to be wrapped.
		 */
		public function __construct(Widget_Interface $widget)
		{
			$this -> _widget = $widget;
		}// end __construct();

		/**
		 * Get value;
		 *
		 * @param string $name
		 */
		public function __get($name)
		{
			if(!in_array($name, self::$_getters))
			{
				throw new Agares_Exception('Getter ' . $name . ' not found!');
			}
			return $this -> _widget -> {'Get' . ucfirst($name)}();
		}// end __get();

		/**
		 * Set value.
		 *
		 * @param string $name
		 * @param string $value
		 */
		public function __set($name, $value)
		{
			if(!in_array($name, self::$_setters))
			{
				throw new Agares_Exception('Setter ' . $name . ' not found!');
			}
			return $this -> _widget -> {'Set' . ucfirst($name)}($value);
		}// end __set();
	}// end Agares_Widget_Wrapper;