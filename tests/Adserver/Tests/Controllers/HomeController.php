<?php
namespace Adserver\Tests\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends \PHPUnit_Framework_TestCase{
		
	public function testIndexAction()
	{
		$request = Request::create('/');
		$app->run($request);
	}
	
}