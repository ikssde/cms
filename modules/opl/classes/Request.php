<?php

	class Request extends Kohana_Request
	{
		public function setView(View $view)
		{
			$outHttp = new Opt_Output_Http;
			$outRet = new Opt_Output_Return;

			$outHttp->setContentType();
			$this->headers = $outHttp->getHeaders();
			$this->response = $outRet->render($view);

			return $this;
		}
	}