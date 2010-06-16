<?php
	/**
	 * @author Agares
	 */

	class Agares_Request extends Kohana_Request
	{
		public function setView(View $view)
		{
			$output = new Opt_Output_Http;
			$outputReturn = new Opt_Output_Return;
			$output -> setContentType(Opt_Output_Http::HTML, 'utf-8');
			$this -> headers = $output -> getHeaders();
			$this -> response = $outputReturn -> render($view);
		}
	}
