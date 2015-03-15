<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class SecuredController extends BaseController {
	
	/**
	 * 
	 * @var Symfony\Component\Security\Core\SecurityContext
	 */
	protected $security;
	
	/**
	 * @var Symfony\Component\Security\Core\Encoder\EncoderFactory
	 */
	protected $securityEncoderFactory;
	
	protected $code;
	
	protected $titolo;
	
	public function __construct(
			\Symfony\Component\Routing\Generator\UrlGenerator $urlgenerator,
			\Doctrine\ORM\EntityManager $em,
			\Adserver\Utils\Alert $alerts,
			\Twig_Environment $twig,
			array $config,
			\Symfony\Component\Security\Core\SecurityContext $security,
			\Symfony\Component\Security\Core\Encoder\EncoderFactory $securityEncoderFactory
			) {		
		parent::__construct($urlgenerator,$em,$alerts,$twig,$config);
		$this->security = $security;
		$this->securityEncoderFactory = $securityEncoderFactory;
	}
	
	protected function beforeView(array $response){		
		return array_merge(
				parent::beforeView($response), 
				array(
					'usr' => $this->security->getToken()->getUser(),
					'code' => $this->code,
					'titolo' => $this->titolo,
					'breadcrumb' => $this->getBreadcrumb()
				)
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
	
	protected function isGrantedAdmin(){
		return $this->security->isGranted(\Adserver\Models\User::ROLE_ADMIN);
	}
	
	protected function isGrantedCustomer(){
		return $this->security->isGranted(\Adserver\Models\User::ROLE_CUSTOMER);
	}
	
	protected function assertGrantedAdmin(){
		if(!$this->isGrantedAdmin()){
			throw new AccessDeniedException('You are not Admin');
		}
	}
	
	protected function assertGrantedCustomer(){
		if(!$this->isGrantedAgent()){
			throw new AccessDeniedException('You are not Customer or Admin');
		}
	}
	
}