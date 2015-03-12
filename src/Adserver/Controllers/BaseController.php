<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController {
	
	/**
	 *
	 * @var Symfony\Component\Routing\Generator\UrlGenerator
	 */
	protected $urlGenerator;
	
	/**
	 * 
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;
	
	/**
	 * @var Adserver\Utils\Alert
	 */
	protected $alerts;	
	
	/**
	 *
	 * @var \Twig_Environment
	 */
	protected $twig;
	
	/**
	 * 
	 * @var array
	 */
	protected $config;
			
	protected $pagesize = 10;
	
	public function __construct(
			\Symfony\Component\Routing\Generator\UrlGenerator $urlgenerator,
			\Doctrine\ORM\EntityManager $em,
			\Adserver\Utils\Alert $alerts,
			\Twig_Environment $twig,
			array $config			
			) {		
		
		$this->urlgenerator = $urlgenerator;
		$this->em = $em;
		$this->alerts = $alerts;
		$this->twig = $twig;
		$this->config = $config;
		
		
	}

	protected function beforeView(array $response){
		return array_merge($response, array(
				'ug' => $this->urlGenerator,
				'alerts' => $this->alerts)
		);
	}
	
	public function __call($name, $arguments)
	{
		$response = call_user_func_array(array($this, $name), $arguments);
		if(!$response) $response = array();
		if(is_array($response)){			
			$response = $this->beforeView($response);
			$controller = null;
			$view = null;
			if($arguments[0]->attributes->has('_controller')){
				list($controller, $view) = explode(':',substr($arguments[0]->attributes->get('_controller'),12, -6));
			}
			
			if(isset($response['_controller']))
				$controller = $response['_controller'];
			
			if(isset($response['_view']))
				$view = $response['_view'];
				
			return $this->twig->render($controller.'/'.$view.'.twig', $response);
		}
		return $response;
	}
	
}