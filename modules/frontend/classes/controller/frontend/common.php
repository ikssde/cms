<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Frontend_Common extends Controller {

	public function __construct( Kohana_Request $request )
	{
		// code here
		
		return parent::__construct( $request );
	}
	
	public function after()
	{
		// code here
		
		return parent::after();
	}
	
	public function before()
	{
		// code here
		
		return parent::before();
	}

} 
