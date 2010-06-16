<?php

	defined('SYSPATH') or die('No direct script access.');

	class Controller_Welcome extends Controller
	{
		public function action_index()
		{
			Widget::AddBox('left', 'Left super-trooper box');
			Widget::AddWidget('left', 'test', new Widget_Test);
			$this->_layout->widgets = Widget::GetWidgets('left');
			$this->_layout->setFormat('widgets', 'Array/Objective');
		}
	}

	// End Welcome

	