<?php

namespace Fbn\Doctrine;

use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TablePrefix {

	protected $prefix = '';

	/**
	 *
	 * Inizializza con un prefisso dato
	 * @param String $prefix prefisso '<$prefix>_NomeTabella'
	 */
	public function __construct($prefix){
		$this->prefix = $prefix.'_';
	}

	/**
	 *
	 * Alterea i metadati della classe di dominio per aggiungere il prefisso
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
		$classMetadata = $eventArgs->getClassMetadata();
 		if(!$this->isAlreadyPrefixed($classMetadata)){
			$classMetadata->setTableName($this->prefix . $classMetadata->getTableName());
 		}
		foreach (array_filter($classMetadata->getAssociationMappings(),
		function ($am) {
			return ($am['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY);
		})
		AS  $property => $relation) {
			if( array_key_exists('name',$classMetadata->associationMappings[$property]['joinTable']) 
					&& !$this->isJoinTableAlreadyPrefixed($classMetadata,$property)){
				$classMetadata->associationMappings[$property]['joinTable']['name'] =
				$this->prefix . $classMetadata->associationMappings[$property]['joinTable']['name'];
			}
		}
	}

	/**
	 * Controlla che la tabella non sia gia prefissata
	 * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadata
	 */
	private function isAlreadyPrefixed($classMetadata){
		$n = $classMetadata->getTableName();
		return substr($n, 0, strlen($this->prefix)) == $this->prefix;
	}
	
	/**
	 * Controlla che la tabella di join non sia gia prefissata
	 * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadata
	 * @param string $association
	 */
	private function isJoinTableAlreadyPrefixed($classMetadata,$association){
		$n = $classMetadata->associationMappings[$association]['joinTable']['name'];
		return substr($n, 0, strlen($this->prefix)) == $this->prefix;
	}

}
