<?php
namespace Adserver\Utils;

class Alert {
	
	const SESS_FIELD = '__alerts';
	
	protected $success;
	
	protected $info;
	
	protected $warning;
	
	protected $danger;
	
	protected $autoclean;
	
	public function __construct($autoclean=true){
		$this->autoclean = $autoclean;
		$this->reset();
	}
	
	public function addWarning($msg){
		$this->warning []= $msg;
	}
	
	public function addInfo($msg){
		$this->info []= $msg;
	}
	
	public function addSuccess($msg){
		$this->success []= $msg;
	}
	
	public function addDanger($msg){
		$this->danger []= $msg;
	}
	
	public function getSuccess(){
		if($this->autoclean) $this->success = array();
		return $this->success;
	}
	
	public function getInfo(){
		if($this->autoclean) $this->info = array();
		return $this->info;
	}
	
	public function getWarning(){
		if($this->autoclean) $this->warning = array();
		return $this->warning;
	}
	
	public function getDanger(){
		if($this->autoclean) $this->danger = array();
		return $this->danger;
	}
	
	public function getAlerts(){
		$alerts = array();
		foreach ($this->success as $m){ $alerts []= array('success',$m); }
		foreach ($this->info as $m){ $alerts []= array('info',$m); }
		foreach ($this->warning as $m){ $alerts []= array('warning',$m); }
		foreach ($this->danger as $m){ $alerts []= array('danger',$m); }
		if($this->autoclean) {
			$this->reset();
		}
		return $alerts;
	}
	
	public function reset(){
		$this->success = array();
		$this->info = array();
		$this->warning = array();
		$this->danger = array();
	}
	
	public function isAnyAllert(){
		return $this->success || $this->info || $this->warning || $this->danger;
	}
	
	/**
	 * 
	 * @param Symfony\Component\HttpFoundation\Session\Session $session
	 */
	public function save(\Symfony\Component\HttpFoundation\Session\Session $session){
		$session->set(self::SESS_FIELD, serialize($this));
	}
	
	/**
	 * 
	 * @param Symfony\Component\HttpFoundation\Session\Session $session
	 * @return Ambigous <\Agents\Utils\Alert, mixed>
	 */
	public static function load(\Symfony\Component\HttpFoundation\Session\Session $session){
		return $session->has(self::SESS_FIELD)?unserialize($session->get(self::SESS_FIELD)):new Alert();
	}
	
}