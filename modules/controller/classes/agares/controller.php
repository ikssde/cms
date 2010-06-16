<?php
	/**
	 * @author Agares
	 */

	/**
	 * My small extension for controller - manages layout and prepares content view.
	 */
	class Agares_Controller extends Kohana_Controller
	{
		/**
		 * The layout's view.
		 *
		 * @var View
		 */
		protected $_layout = null;
		/**
		 * The content's view.
		 *
		 * @var View
		 */
		protected $_content = null;
		/**
		 * Autorender flag.
		 *
		 * @var boolean
		 */
		protected $_autoRender = true;

		/**
		 * Construct the class - set layout and action view.
		 *
		 * @param Request $request
		 */
		public function __construct(Request $request)
		{
			parent::__construct($request);

			$this->_layout = new View('common/layout');
			$this->_content = new View(($request->directory == '' ? '' : $request->directory.'/') . $request->controller.'/'.$request->action);
		}// end __construct();

		/**
		 * If $this->_autorender is set - autorender
		 */
		public function after()
		{
			if($this->_autoRender)
			{
				$this->_layout->content = $this->_content;
				$this->request->setView($this->_layout);
			}
		}// end after();
	}// end Agares_Controller;
