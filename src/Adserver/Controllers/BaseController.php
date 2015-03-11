<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nette\Forms\Controls;
use Nette\Forms\Form;
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
	
	protected function getBreadcrumb(){
		 return array( '<i class="fa fa-dashboard"></i> Home'=>'/' );
	}
	
	protected function bootstrapForm($form){
		
		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-sm-9';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
	
		// make form and controls compatible with Twitter Bootstrap
		$form->getElementPrototype()->class('form-horizontal');
	
		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif (
					$control instanceof Controls\TextBase ||
					$control instanceof Controls\SelectBox ||
					$control instanceof Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');
			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}
		
		return $form;
	}			
	
	public function __call($name, $arguments)
	{
		$response = call_user_func_array(array($this, $name), $arguments);
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
				
			return $this->twig->render($controller.'/'.$view.'.html', $response);
		}
		return $response;
	}
	
}