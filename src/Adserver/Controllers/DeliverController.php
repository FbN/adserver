<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Adserver\Models\Banner;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeliverController {
	
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
	 * 
	 * @var array
	 */
	protected $config;
	
	public function __construct(
			\Symfony\Component\Routing\Generator\UrlGenerator $urlgenerator,
			\Doctrine\ORM\EntityManager $em,
			array $config			
			) {		
		
		$this->urlgenerator = $urlgenerator;
		$this->em = $em;
		$this->config = $config;				
	}
	
	public function indexAction(Request $request, \Fbn\Silex\FbnApp $app){
		$em = $this->em;
		
		$em->getConnection()->beginTransaction();
		
		$b = Banner::deliverNext($em, null, null);		
		
		$app->finish(function()use($b, $em){
			$b->getCampaign()->incDelivered();
			$em->flush();
			$em->getConnection()->commit();
		});
		
		return $response = new JsonResponse(array(
				'name' => $b->getName(),
				'caption' => $b->getCaption(),
				'url' => $b->getUrl()
			));
	}
	
	
}