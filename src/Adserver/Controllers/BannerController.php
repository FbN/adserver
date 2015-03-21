<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\RoutedUrl;
use Fbn\Doctrine\PagePaginator;
use Adserver\Models\Banner;
use Adserver\Models\Campaign;
use Nette\Forms\Form;
use Nette\Forms\Controls;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BannerController extends SecuredController {
	
	protected $code='campaign';
	
	protected $titolo='Campaign/Banner';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Campaign/Banner'=>$this->urlGenerator->generate('campaign.index') );
	}
	
	protected function checkCampaignAccess(Campaign $res){
		$qb = $this->em
		->createQueryBuilder()
		->select('count(x)')
		->from('\\Adserver\\Models\\Campaign', 'x')
		->leftJoin('x.userList', 'u');
		$qb->andWhere($qb->expr()->eq('x.id', $res->getId()));
		$qb->andWhere($qb->expr()->eq('u.id', $this->security->getToken()->getUser()->getId()));
		if(!$qb->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR)) throw new AccessDeniedException();
	}
	
	protected function bannerForm($res){
		$form = new Form();
		
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
		
		return $this->bootstrapForm($form);
	}
		
	protected function editAction(Request $request, $id, $_route){
	
		$res = Banner::find($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$this->checkCampaignAccess($res->getCampaign());
		
		$selfUrl = $this->urlGenerator->generate('banner.edit', array('id'=>$id));
		
		$form = $this->bannerForm($res);					
		$form->setAction($selfUrl);
		
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
			'titolo' => 'Campaign '.$res->getCampaign()->getId(),
			'breadcrumb' => $this->getBreadcrumb()+array(($res->getName())=>$selfUrl),
			'form' => $form
		);
	}				
			
	protected function createAction( Request $request, $id, $_route ){
	
		$campaign = Campaign::find($this->em, $id);
	
		if(!$campaign){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
	
		$this->checkCampaignAccess($campaign);
	
		$selfUrl = $this->urlGenerator->generate('banner.create', array('id'=>$id));
	
		$res = new Banner();
		$res->setCampaign($campaign);
	
		$form = $this->bannerForm($res);
		$form->setAction($selfUrl);
	
		// processing
		if ($form->isSubmitted() && $form->isValid()) {
	
			$values = $form->getValues();
				
			$res->setName($values['name']);
			$res->setCaption($values['caption']);
			$res->setUrl($values['url']);
			$res->setFile($values['file']);
			$res->setWidth($values['width']);
			$res->setHeight($values['height']);
			$campaign->getBannerList()->add($res);
	
			$res->persist($this->em, true);
	
			$this->alerts->addInfo('New banner saved');
	
			return $this->redirect($this->urlGenerator->generate('campaign.edit', array('id'=>$campaign->getId())));
		}
	
		return array(
				'titolo' => 'Campaign '.$id,
				'breadcrumb' => $this->getBreadcrumb()+array(($campaign->getName())=>$this->urlGenerator->generate('campaign.edit', array('id'=>$campaign->getId())), 'Create Banner'=>$selfUrl),
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
	
	protected function deleteAction(Request $request, $_route){
		$i=0;
		$campaign = null;
		foreach ($request->request->get('ids') as $id){
			$res = Banner::find($this->em, $id);
			if($res){
				$i++;
				$campaign = $res->getCampaign()->getId();
				$this->checkCampaignAccess($res->getCampaign());
				$res->delete($this->em);
			}
		}
		$this->alerts->addInfo($i.' elements sucessfully deleted');
		return $this->redirect($this->urlGenerator->generate('campaign.edit', array('id'=>$campaign)));
	}
	
}