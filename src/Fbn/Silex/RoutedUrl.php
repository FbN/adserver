<?php
namespace Fbn\Silex;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;

class RoutedUrl extends ArrayCollection { 
		
	protected $routeName;
	
	public function __construct($params=array(), $routeName=null){
		parent::__construct($params);
		$this->routeName = $routeName;
	}
	
	public function render(UrlGenerator $urlGenerator, $type=UrlGenerator::ABSOLUTE_URL){
		return $urlGenerator->generate($this->routeName, $this->toArray(), $type);
	}
	
	
}