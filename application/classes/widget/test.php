<?php
	/**
	 * @author Agares
	 */

	class Widget_Test implements Widget_Interface
	{
		public function getView()
		{
			$wju = new View('frontend/widget/test');
			$wju->dyn = 'dupa';
			return $wju;
		}
	}
