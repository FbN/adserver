<?php
namespace Fbn\Doctrine;

use Doctrine\ORM\EntityRepository;

class SmartRepository extends EntityRepository {
	
	public function idFieldName(){
		return $this->getClassMetadata()->getSingleIdentifierFieldName();
	}
	
	public function idFieldColumnName(){
		return $this->getClassMetadata()->getSingleIdentifierColumnName();
	}
	
}