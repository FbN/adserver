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
use ApiMart\DoctrineExtensions\TimelinePaginator;

class OrdersController extends B2BController {
	
	protected $titolo = 'Ordini';
	
	protected $code = 'orders';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Ordini'=>$this->urlGenerator->generate('orders') );
	}
	
	public function indexAction(  Request $request, $_route ){
		
		$this->assertGrantedAgent();
	
		$me = $this->security->getToken()->getUser();
		
		$qb = $this->em->createQueryBuilder();
	
		$q = $qb->select('x')->from('\\ApiMart\\Models\\Order', 'x');		
		$q->innerJoin('x.customer', 'c')
		  ->innerJoin('c.b2bUsers', 'u', 'WITH', 'u.b2bUserId = :identifier')
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
	
		$paginator = new TimelinePaginator($q, $this->pagesize, 'dateModified', $request);
		
		return $this->response( __FUNCTION__,
				array(
						'me' => $me,
						'search' => $search,
						'collection' =>  $paginator->getCollection(),
						'paginator' => $paginator->renderPaging(
								$routedUrl,
								$this->urlGenerator )
				)
		);
	
	}
	

}