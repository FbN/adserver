<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecuredController extends BaseController {
	
	/**
	 * 
	 * @var Symfony\Component\Security\Core\SecurityContext
	 */
	protected $security;
	
	/**
	 * @var Symfony\Component\Security\Core\Encoder\EncoderFactory
	 */
	protected $securityEncoderFactory;
	
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