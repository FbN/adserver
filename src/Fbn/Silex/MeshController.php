<?php
namespace Fbn\Silex;

use Symfony\Component\HttpFoundation\RedirectResponse;

class MeshController {
	
	const layout = "main";
	
	protected $urlGenerator;
	protected $oc;
	protected $em;
	protected $config;
	protected $meshService;
	
	public function __construct($urlGenerator, $oc, $em, $config, $meshService) {
		$this->urlGenerator = $urlGenerator;
		$this->oc = $oc;
		$this->em = $em;
		$this->config = $config;
		$this->meshService = $meshService;
	}
	
	public static function getNamespace() {
		return implode('\\', array_slice(explode('\\', get_called_class()), 0, -1));
	}
	
	public static function getBaseClassName() {
		$class = explode('\\', get_called_class());
		return array_pop($class);
	}
	
	protected function getName(){
		return strtolower(substr($this->getBaseClassName(), 0, -10));
	}
	
	protected function getLayout(){
		return static::layout;
	}
	
	protected function response($method, $out=array()){
		
		$action = substr($method, 0, -6);
		
		if(is_object($out)){
			
			if(is_a($out, '\\Symfony\\Component\\HttpFoundation\\Response')){
				return $out;
			}
			
			if(is_a($out, '\\Fbn\\Silex\\MeshPage')){
				return $this->meshService->response(
						$out->layout?$out->layout:$this->getLayout(), 
						$out->controller?$out->controller:$this->getName(), 
						$out->action?$out->action:$action, 
						$out->model?$out->model:arrray());
			}
			
			throw new \RuntimeException("Action return unsupported");
			
		} else if (is_array($out)){
			return $this->meshService->response(
					$this->getLayout(), 
					$this->getName(), 
					$action, 
					$out);
		} else return $out;
				
	}
	
	/**
	 * Redirects the user to another URL.
	 *
	 * @param string $url    The URL to redirect to
	 * @param int    $status The status code (302 by default)
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302){
		return new RedirectResponse($url, $status);
	}
	
}