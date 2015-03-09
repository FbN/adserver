<?php
namespace Fbn\Silex;

class MeshService {
	
	protected $viewFolder;
	
	protected $layoutFolder;
	
	public function __construct($viewFolder, $layoutFolder){
		$this->layoutFolder = $layoutFolder;
		$this->viewFolder = $viewFolder;
	}
	
	public function response($layout, $controller, $view, $model=array()){
		$r = new MeshResponse();
		$r->setLayout($this->layoutFolder.'/'.$layout.'.php');
		$r->setView($this->viewFolder.'/'.$controller.'/'.$view.'.php');
		$r->setModel($model);
		return $r;
	}
	
}