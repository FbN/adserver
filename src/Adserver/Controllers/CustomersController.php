<?php
namespace Adserver\Controllers;

use Fbn\Silex\MeshController;
use ApiMart\Models\B2bUser;
use ApiMart\DoctrineExtensions\PagePaginator;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMart\Utils\RoutedUrl;
use Symfony\Component\HttpFoundation\Request;
use Nette\Forms\Form;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nette\Forms\Controls;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiMart\Models\Customer;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomersController extends B2BController {
	
	protected $titolo = 'Clienti';
	
	protected $code = 'customers';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Clienti'=>$this->urlGenerator->generate('customers') );
	}

	public function searchAction(Request $request, $_route ){
		
		$this->assertGrantedAdmin();
		
		$s  = $request->query->get('q');
		$id  = $request->query->get('id');
		
		$out = array();
		
		if($s&$id){
			
			$qb = $this->em->createQueryBuilder();
			
			$qb = $qb->select('x')->from('ApiMart\\Models\\Customer', 'x');
			
			$qb->leftJoin('x.b2bUsers', 'u', 'WITH', 'u.b2bUserId = :b2bUserId');
			$qb->setParameter('b2bUserId', $id);
			$qb->groupBy('x.customerId');
			$qb->having('COUNT(u.b2bUserId) = 0');
			
			$qb->where(
					$qb->expr()->orX(
							$qb->expr()->like('x.email', '?1'),
							$qb->expr()->like('x.firstname', '?1'),
							$qb->expr()->like('x.lastname', '?1'),
							$qb->expr()->like('x.gid', '?1')
					)
			);
			
			$qb->setParameter(1, '%'.$s.'%');
			
			$qb->setFirstResult( 0 )
   			   ->setMaxResults( 25 );
			
			$collection = new ArrayCollection($qb->getQuery()->getResult());
			
			foreach ($collection as $c){
				$out[]=array(
					'email'=> $c->getEmail(),
					'label'=> $c->getLabel(),
					'id'=> $c->getCustomerId(),
					'gid'=> $c->getGid()
					);
			}
			
		}
		
		return new JsonResponse(array('data'=>$out));
		
	}
	
	public function indexAction(  Request $request, $_route ){
		
		$this->assertGrantedAgent();
	
		$me = $this->security->getToken()->getUser();
		
		$qb = $this->em
			->createQueryBuilder()
			->select('x')
			->from('\\ApiMart\\Models\\Customer', 'x')
			->innerJoin('x.b2bUsers', 'u', 'WITH', 'u.b2bUserId = :identifier')
			->setParameter('identifier', $me->getId());
	
		$routedUrl = new RoutedUrl(array(), $_route );
	
		$search=null;
	
		if($request->query->has('q') && $search=$request->query->get('q')){
			$qb->where(
					$qb->expr()->orX(
							$qb->expr()->like('x.email', '?1'),
							$qb->expr()->like('x.firstname', '?1'),
							$qb->expr()->like('x.lastname', '?1'),
							$qb->expr()->like('x.gid', '?1')
					)
			);
			$qb->setParameter(1, '%'.$search.'%');
			$routedUrl['q'] = $search;
		}
	
		$paginator = new PagePaginator($qb, $this->pagesize, $request);
	
		return $this->response( __FUNCTION__,
				array(
						'me' => $me,
						'search' => $search,
						'collection' => $paginator->getCollection(),
						'paginator' => $paginator->renderPaging(
								$routedUrl,
								$this->urlGenerator )
				)
		);
	
	}
	
	public function loginAction(  $id, Request $request, $_route ){
		
		$this->assertGrantedAgent();
	
		$me = $this->security->getToken()->getUser();
		
		$meModel = $me->getModel($this->em);
		
		$customer = Customer::guess($this->em, $id);
		
		if(!$customer){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		if(!$meModel->getCustomers()->contains($customer)){
			throw new HttpException(404, 'Access denied');
		}
		
		$token = md5(mt_rand());
		
		$customer->setToken($token);
		
		$params = array(
			'route' => 'account/login',
			'token' => $token
		);
		
		if($request->query->has('route')){
			$params['to-route'] = $request->query->get('route'); 
		}
		
		if($request->query->has('args')){
			$params['to-args'] = $request->query->get('args');
		}
		
		return new RedirectResponse(
				$this->config['front.protocol'].'://'.$this->config['front.domain'].'/index.php?'.http_build_query($params), 
				302);
		
	}
	

}