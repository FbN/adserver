<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HomeController extends SecuredController {
	
	protected $code='home';
	
	protected $titolo='Dashboard';
		
	protected function indexAction(Request $request){

	}
	
}