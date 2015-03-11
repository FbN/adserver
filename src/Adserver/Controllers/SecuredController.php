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
	 * @var Symfony\Component\Security\Core\SecurityContext
	 */
	protected $urlGenerator;
		
	/**
	 * 
	 * @var Symfony\Component\Security\Core\SecurityContext
	 */
	protected $security;
	
	/**
	 * @var Symfony\Component\Security\Core\Encoder\EncoderFactory
	 */
	protected $securityEncoderFactory;
	
	/**
	 * @var Agents\Utils\Alert
	 */
	protected $alerts;
	
	protected $pagesize = 10;
	
	public function __construct(
			$urlGenerator,
			$em,
			$config) {		
		$this->security = $app['security'];
		$this->securityEncoderFactory = $app['security.encoder_factory'];
		$this->alerts = $app['alerts'];
		$this->urlgenerator = $app['url_generator'];
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
	
	protected function response($method, $out=array()){
		if(is_array($out)){
			$out = array_merge(					
					array( 
							'ug' => $this->urlGenerator,
							'usr' => $this->security->getToken()->getUser(),
							'titolo' => $this->titolo,
							'code' => $this->code,
							'security' => $this->security,
							'breadcrumb' => $this->getBreadcrumb(),
							'alerts' => $this->alerts 
						),
					$out
			);
		}		
		return parent::response($method, $out);
	}
	
	protected function isGrantedAdmin(){
		return $this->security->isGranted(\ApiMart\Models\B2bUser::ROLE_ADMIN);
	}
	
	protected function isGrantedAgent(){
		return $this->security->isGranted(\ApiMart\Models\B2bUser::ROLE_AGENT);
	}
	
	protected function assertGrantedAdmin(){
		if(!$this->isGrantedAdmin()){
			throw new AccessDeniedException('You are not Admin');
		}
	}
	
	protected function assertGrantedAgent(){
		if(!$this->isGrantedAgent()){
			throw new AccessDeniedException('You are not Agent or Admin');
		}
	}
	
}