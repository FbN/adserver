<?php
namespace Fbn\Doctrine;

use JMS\Serializer\Annotation as SERIALIZER;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;

abstract class SmartModel implements NotifyPropertyChanged {
	
	/**
	 * http://stackoverflow.com/questions/255517/mysql-offset-infinite-rows
	 * Mysql 64bits support 18446744073709551615 but php don't support it
	 * @var Long Integer
	 */
	const MYSQL_INFINITE = PHP_INT_MAX;
	
	/**
	 *
	 * Bridge per richiamare direttamente i metodi statici del repository Doctrine
	 * @see Doctrine\ORM\EntityRepository::createNamedQuery()
	 * @see Doctrine\ORM\EntityRepository::createQueryBuilder()
	 * @see Doctrine\ORM\EntityRepository::find()
	 * @see Doctrine\ORM\EntityRepository::findAll()
	 * @see Doctrine\ORM\EntityRepository::findBy()
	 * @see Doctrine\ORM\EntityRepository::findOneBy()
	 * ecc..
	 *
	 * @param Closure|String $arg0 Closure da applicare al querybuilder o stringa DQL
	 * @param array $arguments
	 * @return SmartModel|mixed
	 */
	public static function __callStatic($name, $arguments) {
		$em = array_shift($arguments);
		return call_user_func_array( array($em->getRepository(get_called_class()), $name),  $arguments);
	}
	
	/**
	 *
	 * Metodi magigi dei moduli
	 *
	 * - get<NomeField>() ritorna il valore del campo richiesto
	 * - set<NomeField>($value) assegna al campo un determinato valore
	 * - htm<NomeField>() ritorna il valore del campo con entità html convertite per l'output
	 * - out<NomeField>() esegue una echo del valore del campo con entità html convertite
	 * - is<NomeField>() il valore del campo è riconducibile ad un valore vero?
	 *
	 * @param String $name nome metodo
	 * @param array $arguments parametri di input
	 * @throws \RuntimeException
	 */
	public function __call($name, $arguments) {
		$matches=array();
		preg_match('/^([a-z]+)([A-Z][a-zA-Z0-9\-\_]+)$/',$name, $matches);
		if(count($matches)==3){
			$field = lcfirst($matches[2]);
			if(!$this->hasField($field)) return null;
			switch($matches[1]){
				case 'get':
					return $this->$field;
					break;
				case 'set':
					if ($this->$field !== $arguments[0]) {					
						$this->_onPropertyChanged($field, $this->$field, $arguments[0]);
						$this->$field = $arguments[0];
					}					
					return;
					break;
				case 'toggle':
					$this->_onPropertyChanged($field, $this->$field, !$this->$field);
					$this->$field = !$this->$field;					
					return $this->$field;
					break;
				case 'is':
					$get = 'get'.ucfirst($field);
					return true&&$this->$get();
					break;
			}
		}
		throw new \RuntimeException("SmartModel: method not found ".$name);
	}
	
	/**
	 * Restituisce il valore del campo dato
	 * @see _call()
	 * @param String $fieldName nome campo
	 * @return mixed valore campo
	 */
	public function getField($fieldName){
		$methodName = 'get'.ucfirst($fieldName);
		return $this->$methodName();
	}
	
	/**
	 * Setta il valore del campo dato
	 * @see _call()
	 * @param String $fieldName nome campo
	 * @param mixed $fieldValue valore campo
	 */
	public function setField($fieldName, $fieldValue){
		$methodName = 'set'.ucfirst($fieldName);
		$this->$methodName($fieldValue);
	}
	
	/**
	 * Controlla se il dominio possiede un campo con il nome dato
	 * @param String $fieldName Nome Campo
	 * @return boolean
	 */
	public function hasField($fieldName){
		return property_exists($this, $fieldName);
	}
	
	/**
	 * @return string
	 *
	 * @SERIALIZER\Exclude
	 */
	private $_listeners = array();
	
	public function addPropertyChangedListener(PropertyChangedListener $listener){
		$this->_listeners[] = $listener;
	}
	
	protected function _onPropertyChanged($propName, $oldValue, $newValue){
		if ($this->_listeners) {
			foreach ($this->_listeners as $listener) {
				$listener->propertyChanged($this, $propName, $oldValue, $newValue);
			}
		}
	}
	
	public function persist($em, $flush=false){
		$em->persist($this);
		if($flush){ $em->flush(); }
		return $this;
	}
	
	public function delete($em, $flush=false){
		$em->remove($this);
		if($flush){ $em->flush(); }
		return $this;
	}
		
}