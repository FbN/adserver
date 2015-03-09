<?php
namespace Fbn\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

class SmartRepository extends EntityRepository {
	
	protected function fromGid($id){
		if(!$id || strlen($id)<2) { return null; }
		if($id[0]=='!'){ return substr($id, 1); }
	}
	
	public function guess($id){
		$gid = $this->fromGid($id);
		if($gid){
			return $this->findOneByGid($gid);					
		} else {
			return $this->find($id);
		}
	}
	
	public function updateGid($id, $gid){
		$res = $this->find($id);
		$res->setGid($gid);
		return $res;
	}
	
	public function idFromGid($gid){
		
		$idRow = $this->idAssociativeFromGid($gid);
		
		return $idRow[1];
	}
	
	public function idAssociativeFromGid($gid){
	
		$em = $this->getEntityManager();
	
		$classMeta = $this->getClassMetadata();
	
		$idField = $classMeta->getSingleIdentifierFieldName();
	
		$id = $em->createQueryBuilder()
			->select('x.'.$idField)
			->from($this->getClassName(), 'x')
			->where('x.gid = ?1')
			->setParameter(1, $gid)
			->getQuery()
			->getOneOrNullResult();
		
		if(is_array($id)) $id = current($id);
	
		return array( $classMeta->getSingleIdentifierColumnName(), $id);		
	}
	
	public function idFieldName(){
		return $this->getClassMetadata()->getSingleIdentifierFieldName();
	}
	
	public function idFieldColumnName(){
		return $this->getClassMetadata()->getSingleIdentifierColumnName();
	}
	
}