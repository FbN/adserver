<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\RoutedUrl;
use Fbn\Doctrine\PagePaginator;
use Adserver\Models\Banner;
use Nette\Forms\Form;
use Nette\Forms\Controls;
use Symfony\Component\HttpFoundation\JsonResponse;

class BannerController extends SecuredController {
	
	protected $code='campaign';
	
	protected $titolo='Campaign/Banner';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Campaign/Banner'=>$this->urlGenerator->generate('campaign.index') );
	}
		
	protected function editAction(Request $request, $id, $_route){
	
		$res = Banner::find($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$selfUrl = $this->urlGenerator->generate('banner.edit', array('id'=>$id));
		
		$form = new Form();					
		$form->setAction($selfUrl);
		
		$form->addHidden('id');
		$form->addText('name', 'Name:')
			->setRequired('Name is required');
		$form->addText('caption', 'Caption:')
			->setRequired('Caption is required');
		$form->addText('url', 'Url:')
			->setRequired('Url is required');
		$form->addText('file', 'File:')
			->setRequired('Please upload a jpg file');
		$form->addText('width','Width:')
			->setRequired('Please upload a jpg file');
		$form->addText('height','Height:')
			->setRequired('Please upload a jpg file');
		
		$form->addSubmit('send', 'Save');
		
		$form->setDefaults(array(
			'id' => $res->getId(),
			'name' => $res->getName(),
			'caption' => $res->getCaption(),
			'url' => $res->getUrl(),
			'file' => $res->getFile(),
			'width' => $res->getWidth(),
			'height' => $res->getHeight()
		));
		
		$this->bootstrapForm($form);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$values = $form->getValues();
		
			if($res->getField('id')!=$values['id']) { throw new \RuntimeException('Invalid resource ID'); }
						
			$res->setName($values['name']);
			$res->setCaption($values['caption']);
			$res->setUrl($values['url']);
			$res->setFile($values['file']);
			$res->setWidth($values['width']);
			$res->setHeight($values['height']);

			$this->alerts->addInfo('Banner updated');
			
			return $this->redirect($selfUrl);
		}
		
		return array(
			'breadcrumb' => $this->getBreadcrumb()+array(($res->getName())=>$selfUrl),
			'form' => $form
		);
	}
	
	protected function uploadAction(Request $request, $_route){
		$file = $request->files->get('file');
		$filename =uniqid().'.jpg';
		$file->move($this->config['bannerFolder'],$filename);
		$s = getimagesize($this->config['bannerFolder'].'/'.$filename);
		return new JsonResponse(array(
				'name' => $filename,
				'width' => $s[0],
				'height' => $s[1]
			));
	}
	
}