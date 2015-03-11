<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HomeController extends BaseController {
		
	protected $title = 'Dashboard';
	protected $code  = 'home';
	
	public function indexAction(){
	
		return $this->response(__FUNCTION__);
		
	}
	
}