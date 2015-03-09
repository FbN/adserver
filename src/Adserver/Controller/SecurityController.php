<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\MeshController;

class SecurityController extends MeshController {
	
	const layout = "signin";
	
	public function SignInAction(Request $request, \Fbn\Silex\FbnApp $app){
		return $this->response(__FUNCTION__, array(
				'error' => $app['security.last_error']($request),
				'lastUsername' => $app['session']->get('_security.last_username'),
				'ug' => $this->urlGenerator
				));
	}
	
}