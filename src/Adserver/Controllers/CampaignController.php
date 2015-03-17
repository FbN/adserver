<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\RoutedUrl;
use Fbn\Doctrine\PagePaginator;
use Adserver\Models\Campaign;
use Nette\Forms\Form;
use Nette\Forms\Controls;

class CampaignController extends SecuredController {
	
	protected $code='campaign';
	
	protected $titolo='Campaign';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Campaign'=>$this->urlGenerator->generate('campaign.index') );
	}
		
	protected function indexAction(Request $request, $_route){
		
		$qb = $this->em
			->createQueryBuilder()
			->select('x')
			->from('\\Adserver\\Models\\Campaign', 'x');
		
		$routedUrl = new RoutedUrl(array(), $_route );
		
		$search=null;
		
		if($request->query->has('q') && $search=$request->query->get('q')){
			$qb->where(
					$qb->expr()->orX(
							$qb->expr()->like('x.name', '?1'),
							$qb->expr()->like('x.id', '?1')
					)
			);
			$qb->setParameter(1, '%'.$search.'%');
			$routedUrl['q'] = $search;
		}
		
		$paginator = new PagePaginator($qb, $this->pagesize, $request);
		
		return array(
						'search' => $search,
						'collection' => $paginator->getCollection(),
						'paginator' => $paginator->renderPaging(
								$routedUrl,
								$this->urlGenerator )
				);
	}
	
	protected function editAction(Request $request, $id, $_route){
	
		$res = Campaign::find($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$selfUrl = $this->urlGenerator->generate('campaign.edit', array('id'=>$id));
		
		$form = new Form();				
		$form->setAction($selfUrl);
		$form->addHidden('id');
		$form->addText('name', 'Name:')
			->setRequired('Name is required');			
		$form->addCheckbox('active', 'Active:');
		$form->addText('goal', 'Goal:')
			->setRequired('Gaol is required');
		$form->addSubmit('send', 'Save');
		
		$form->setDefaults(array(
				'id' => $res->getId(),
				'name' => $res->getName(),
				'active' => $res->getActive(),
				'goal' => $res->getGoal()
		));
		
		$this->bootstrapForm($form);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$values = $form->getValues();
		
			if($res->getField('id')!=$values['id']) { throw new \RuntimeException('Invalid resource ID'); }
						
			$res->setName($values['name']);
			$res->setActive($values['active']);
			$res->setGoal($values['goal']);
			
			$this->alerts->addInfo('Campaign updated');
			
			return $this->redirect($selfUrl);
		}
		
		return array(
						'breadcrumb' => $this->getBreadcrumb()+array(($res->getName())=>$selfUrl),
						'form' => $form
		);
	}
	
}