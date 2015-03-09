<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\MeshResponse;
use Fbn\Silex\MeshController;

class HomeController extends B2BController {
		
	protected $titolo = 'Dashboard';
	protected $code = 'home';
	
	public function indexAction(){
	
		return $this->response(__FUNCTION__);
		
	}
	
}