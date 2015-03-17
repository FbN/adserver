<?php
namespace Fbn\Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use Fbn\Silex\RoutedUrl;
use Doctrine\Common\Collections\ArrayCollection;

class TimelinePaginator implements PaginatorInterface {
	
	const SINCE = '_since';
	const UNTIL = '_until';
	
	/**
	 *
	 * @var \DateTime
	 */
	protected $until;
	
	/**
	 * 
	 * @var \DateTime
	 */
	protected $since;
	
	protected $pageSize;
	
	protected $orderBy;
	
	protected $collection;
	
	/**
	 * 
	 * @var Doctrine\ORM\QueryBuilder
	 */
	protected $qb;
	
	/**
	 * 
	 * @param Doctrine\ORM\QueryBuilder $qb
	 * @param int $pageSize
	 * @param String $orderBy
	 * @param Symfony\Component\HttpFoundation\Request $request
	 */
	public function __construct(
			$qb,
			$pageSize,
			$orderBy,
			Request $request=null){
		$this->qb = $qb;
		$this->orderBy = $orderBy;
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
		$since = $request->query->get(self::SINCE);
		$until = $request->query->get(self::UNTIL);
		if($since) $this->since = \DateTime::createFromFormat(\DateTime::ISO8601,$since);
		if($until) $this->until = \DateTime::createFromFormat(\DateTime::ISO8601,$until);
	}
	
	protected function queryBuilderFilter($as = 'x'){
		$qb=$this->qb;
		
		if($this->until){
			$qb->andWhere($as.'.'.$this->orderBy.' < ?1')
			   ->setParameter(1, $this->until)
			   ->orderBy($as.'.'.$this->orderBy, 'DESC');
		} else if($this->since){
			$qb->andWhere($as.'.'.$this->orderBy.' > ?1')
			  ->setParameter(1, $this->since)
			  ->orderBy($as.'.'.$this->orderBy, 'ASC');
		} else {
			$qb->orderBy($as.'.'.$this->orderBy, 'DESC');
		}
		
		$qb->setFirstResult( 0 )
		   ->setMaxResults( $this->pageSize );
		
		return $qb;
	}
	
	protected function getNextUntill(Collection $collection){
		if($collection->isEmpty( )) return null;
		return $collection->last()->getField($this->orderBy);
	}
	
	protected function getPrevSince(Collection $collection){
		if($collection->isEmpty( )) return null;
		return $collection->first()->getField($this->orderBy);
	}
	
	public function getCollection(){
		if(!$this->collection){
			$qb = $this->queryBuilderFilter();		
			$el = $qb->getQuery()->getResult();
			if($this->since) $el = array_reverse($el);
			$this->collection =  new ArrayCollection($el);
		}
		return $this->collection;
	}
	
	public function getPaging( \Fbn\Silex\RoutedUrl $routedUrl){
		$collection = $this->getCollection();
		$n = $this->getNextUntill($collection);
		$p = $this->getPrevSince($collection);
		$nextRoutedUrl = null;
		$prevRoutedUrl = null;
		if($n){
			$nextRoutedUrl = clone $routedUrl;
			$nextRoutedUrl[self::UNTIL] = $n->format(\DateTime::ISO8601);
			}
		
		if($p){
			$prevRoutedUrl = clone $routedUrl;
			$prevRoutedUrl[self::SINCE] = $p->format(\DateTime::ISO8601);
			}
		
		return array(
			'next'=> $nextRoutedUrl,
			'prev'=> $prevRoutedUrl
		);
	}
	
	public function renderPaging( \Fbn\Silex\RoutedUrl $routedUrl, \Symfony\Component\Routing\Generator\UrlGeneratorInterface  $urlGenerator){
		$paging = $this->getPaging($routedUrl);		
		$paging['next'] = $paging['next']?$paging['next']->render($urlGenerator):null;
		$paging['prev'] = $paging['prev']?$paging['prev']->render($urlGenerator):null;
		return $paging;
	}
	
}