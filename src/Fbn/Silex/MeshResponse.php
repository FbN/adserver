<?php
namespace Fbn\Silex;

use Symfony\Component\HttpFoundation\Response;
use Ganon\Ganon;

class MeshResponse extends Response {
	
	const html5MinDoc = '<!doctype html><title></title>';
	
	/**
	 *
	 * @var string
	 */
	protected $view;
	
	/**
	 * 
	 * @var string
	 */
	protected $layout;
	
	/**
	 * @var array
	 */
	protected $model;
	
	public function setView($view){
		$this->view = $view;
	}
	
	public function setLayout($layout){
		$this->layout = $layout;
	}
	
	public function setModel($model){
		$this->model = $model;
	}
	
	protected function renderLayout(){
		$out='';
		if(!file_exists($this->layout)) return self::html5MinDoc;
		extract( (array) $this->model);
		ob_start();
		include($this->layout);		
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	
	protected function renderView(){
		$out='';
		if(!file_exists($this->view)) return self::html5MinDoc;
		extract( (array) $this->model);		
		ob_start();
		include($this->view);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	
	protected function template($path, $model=array()){
		
		$file = null;
		
		if($path[0]=='/'){
			$file = dirname(dirname($this->layout)).$path.'.php';
		} else {
			$file = dirname($this->view).'/_'.$path.'.php';
		}
		
		$out='';
		if(!file_exists($file)) throw new \RuntimeException('Template file not found '.$file);
		
		extract( (array) $this->model);
		extract( (array) $model);
		
		ob_start();
		include($file);
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
				
	}
	
	protected function dateFormat($date){
		return $date->format('d/m/Y H:i:s');
	}
	
	public function render(){
		
		libxml_use_internal_errors(true);
		
		$l = $this->renderLayout();
		
		$lDom = new \DOMDocument();
		$lDom->loadHTML($l);
		
		$v = $this->renderView();
		$vDom = new \DOMDocument();
		$vDom->loadHTML($v);
				
		//Title
		$auxHead = $lDom->getElementsByTagName('head')->item(0);
		if($auxHead && $auxHead->getElementsByTagName('title')->item(0)){
			$lhead = $lDom->getElementsByTagName('head')->item(0)->getElementsByTagName('title')->item(0);
			if($lhead){
				foreach ($lhead->childNodes as $item) {
					$lhead->removeChild($item);
				}
				$vtitle = $vDom->getElementsByTagName('head')->item(0)->getElementsByTagName('title')->item(0);
				foreach ($vtitle->childNodes as $item) {
					$item = $lDom->importNode($item, true);
					$lhead->appendChild($item);
				}
				$vtitle->parentNode->removeChild($vtitle);
			}	
		}
		
		//Head
		$target = $lDom->getElementById('meshHead');
		if($target){
			$targetParent = $target->parentNode;
			foreach ($vDom->getElementsByTagName('head')->item(0)->childNodes as $item) {
					$item = $lDom->importNode($item, true);
					$targetParent->insertBefore($item, $target);
				}
			$targetParent->removeChild($target);
		}
			
		//Body
		$target = $lDom->getElementById('meshBody');
		if($target){
			$targetParent = $target->parentNode;
			foreach ($vDom->getElementsByTagName('body')->item(0)->childNodes as $item) {
					$item = $lDom->importNode($item, true);
					$targetParent->insertBefore($item, $target);
				}
			$targetParent->removeChild($target);			
		}
		
		$this->setContent($lDom->saveHTML());
		
		return $this;
	}
	
}