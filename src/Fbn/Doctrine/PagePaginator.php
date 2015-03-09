<?php
namespace Fbn\Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use Fbn\Utils\RoutedUrl;
use Doctrine\Common\Collections\ArrayCollection;

class PagePaginator implements PaginatorInterface {
	
	const PAGE = '_page';
	
	protected $page = 0;
	
	protected $pageSize;
	
	/**
	 *
	 * @var Doctrine\ORM\QueryBuilder
	 */
	protected $qb;
	
	protected $collection;
	
	/**
	 * 
	 * @param Serializer $serializer
	 * @param unknown $orderBy
	 * @param Request $request
	 * @param \Symfony\Component\Routing\Generator\UrlGenerator $urlGenerator
	 */
	public function __construct(
			$qb,
			$pageSize,
			Request $request=null){
		$this->qb = $qb;
		$this->pageSize = $pageSize;
		if($request) $this->hidrate($request);		
	}
	
	/**
	 *
	 * @return Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder(){
		return $this->qb;
	}
	
	public function hidrate(\Symfony\Component\HttpFoundation\Request $request){
		$this->page = $request->query->get(self::PAGE);			
	}
	
	public function queryBuilderFilter(){
		$this->qb->setFirstResult( $this->page*$this->pageSize )
		   ->setMaxResults( $this->pageSize );
		return $this->qb;
	}
	
	protected function getNext(){		
		return $this->page + 1; 
	}
	
	protected function getPrev(){
		return $this->page>1?$this->page-1:0;
	}
	
	public function getCollection(){
		if(!$this->collection){
			$this->collection =  new ArrayCollection( $this->queryBuilderFilter()->getQuery()->getResult() );
		}
		return $this->collection;
	}
	
	public function getPaging( \Fbn\Utils\RoutedUrl $routedUrl){
		$n = $this->getNext();
		$p = $this->getPrev();
		
		$nextRoutedUrl = clone $routedUrl;
		$prevRoutedUrl = clone $routedUrl;
		$nextRoutedUrl[self::PAGE] = $n;
		$prevRoutedUrl[self::PAGE] = $p;
		
		return array(
			'next'=> $nextRoutedUrl,
			'prev'=> $p==$this->page?null:$prevRoutedUrl
		);
	}
	
	public function renderPaging( \Fbn\Utils\RoutedUrl $routedUrl, \Symfony\Component\Routing\Generator\UrlGeneratorInterface  $urlGenerator){
		$paging = $this->getPaging($routedUrl);		
		$paging['next'] = $paging['next']->render($urlGenerator);
		$paging['prev'] = $paging['prev']?$paging['prev']->render($urlGenerator):null;
		return $paging;
	}
	
}