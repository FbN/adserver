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
			\Symfony\Component\Routing\Generator\UrlGenerator $urlGenerator,
			\Doctrine\ORM\EntityManager $em,
			array $config			
			) {		
		
		$this->urlGenerator = $urlGenerator;
		$this->em = $em;
		$this->config = $config;				
	}
	
	public function indexAction(Request $request, \Fbn\Silex\FbnApp $app){
		$em = $this->em;
		
		//$em->getConnection()->beginTransaction();
		
		$cookie = $request->query->get('cookie');
		$cookie = $cookie&&$cookie!='undefined'?$cookie:null;
		$referer = $request->query->get('referer')?$request->query->get('referer'):null;
		$b = Banner::deliverNext($em, $request->query->get('w'), $request->query->get('h'), $cookie, $referer);		
		
		$app->finish(function()use($b, $em){
			$b->incDelivered($em);
			//$em->flush();
			//$em->getConnection()->commit();
		});
		$body=array(
				'banner' => $b->getBanner(),
				'name' => $b->getName(),
				'caption' => $b->getCaption(),
				'url' => $b->getUrl()
		);
		if($request->query->has('jsonp')){
			return $request->query->get('jsonp')."(".json_encode($body).");"; 
		}
		return new JsonResponse($body);
	}
	
	
}