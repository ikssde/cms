<?php
	/**
	 * @author Agares
	 */

	/**
	 * The class that manages widgets and boxes(box is place where the widgets are).
	 */
	class Agares_Widget
	{
		/**
		 * The array of boxes('id' => 'name').
		 *
		 * @var array
		 */
		protected static $_boxes = array();
		/**
		 * The array of widgets('box' => ('id' => 'object'))
		 *
		 * @var array
		 */
		protected static $_widgets = array();

		/**
		 * Add box.
		 *
		 * @param string $id
		 * @param string $name
		 */
		public static function AddBox($id, $name)
		{
			self::$_boxes[$id] = $name;
			self::$_widgets[$id] = array();
		}// end AddBox();

		/**
		 * Get box by it's id.
		 *
		 * @throws Agares_Exception
		 * @param string $id Id of the box
		 * @return string The name of the box.
		 */
		public static function GetBox($id)
		{
			if(!isset(self::$_boxes[$id]))
			{
				throw new Agares_Exception('Box ' . $id . ' does not exist.');
			}

			return self::$_boxes[$id];
		}// end GetBox();

		/**
		 * Add widget object with given id. Must implement Widget_Interface.
		 *
		 * @param string $boxId The id of the box.
		 * @param string $id The id of the widget.
		 * @param Widget_Interface $object The instance of the widget.
		 */
		public static function AddWidget($boxId, $id, Widget_Interface $object)
		{
			self::$_widgets[$boxId][$id] = new Widget_Wrapper($object);
		}// end AddWidget();

		/**
		 * Get widget by it's id.
		 *
		 * @param string $boxId Id of the widget's box.
		 * @param string $id Id of the widget.
		 * @return Widget_Wrapper The widget.
		 */
		public static function GetWidget($boxId, $id)
		{
			if(!isset(self::$_widgets[$boxId][$id]))
			{
				throw new Agares_Exception('Widget ' . $id . ' does not exist in box ' . $boxId . '.');
			}

			return self::$_widgets[$boxId][$id];
		}// end GetWidget();

		/**
		 * Get all widgets in box.
		 *
		 * @param string $box The name of the box.
		 * @return array The widgets.
		 */
		public static function GetWidgets($box)
		{
			return array_values(self::$_widgets[$box]);
		}// end GetWidgets();

		/**
		 * Get all the boxes.
		 *
		 * @return array Boxes.
		 */
		public static function GetBoxes()
		{
			return self::$_boxes;
		}// end GetBoxes();
	}// end Agares_Widget;
