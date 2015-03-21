<?php
namespace Adserver\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Fbn\Silex\RoutedUrl;
use Fbn\Doctrine\PagePaginator;
use Adserver\Models\Campaign;
use Nette\Forms\Form;
use Nette\Forms\Controls;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Adserver\Models\User;
use Adserver\Models\CampaignRuntime;

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
		
		//restrict to user
		$qb->leftJoin('x.userList', 'u')
		   ->andWhere($qb->expr()->eq('u.id', $this->security->getToken()->getUser()->getId()));
		
		$paginator = new PagePaginator($qb, $this->pagesize, $request);
		
		return array(
						'search' => $search,
						'collection' => $paginator->getCollection(),
						'paginator' => $paginator->renderPaging(
								$routedUrl,
								$this->urlGenerator )
				);
	}
	
	protected function campaignForm($res){
		$form = new Form();
		
		$form->addGroup('Campaign');
		$form->addHidden('id');
		$form->addText('name', 'Name:')
			->setRequired('Name is required');
		$form->addCheckbox('active', 'Active:');
		$form->addText('goal', 'Goal:')
			->setRequired('Gaol is required');
		$form->addCheckbox('timeFilterActive', 'Active Day/Hour filter:');
		
		$form->addGroup('Day Filter');
		$form->addCheckbox('dSunday', 'Sunday:');
		$form->addCheckbox('dMonday', 'Monday:');
		$form->addCheckbox('dTuesday', 'Tuesday:');
		$form->addCheckbox('dWednesday', 'Wednesday:');
		$form->addCheckbox('dThursday', 'Thursday:');
		$form->addCheckbox('dFriday', 'Friday:');
		$form->addCheckbox('dSaturday', 'Saturday:');
		$form->addGroup('Hour Filter');
		for($i=0;$i<24;$i++){
			$form->addCheckbox('h'.$i, 'Hour '.$i);
		}
		
		$form->addGroup('Cookie filter');
		$form->addText('cookie', 'Cookie value (clear for disable filter):');
		
		$form->addSubmit('send', 'Save');
		
		$defaults = array(
				'id' => $res->getId(),
				'name' => $res->getName(),
				'active' => $res->getActive(),
				'goal' => $res->getGoal(),
				'timeFilterActive' => $res->getTimeFilterActive(),
				'dSunday' => $res->getDSunday(),
				'dMonday' => $res->getDMonday(),
				'dTuesday' => $res->getDTuesday(),
				'dWednesday' => $res->getDWednesday(),
				'dThursday' => $res->getDThursday(),
				'dFriday' => $res->getDFriday(),
				'dSaturday' => $res->getDSaturday(),
				'cookie' => $res->getCookie(),
		
		);
		for($i=0;$i<24;$i++){
			$defaults['h'.$i] = $res->{'getH'.$i}();
		}
		
		$form->setDefaults($defaults);
		
		return $this->bootstrapForm($form);
	}
	
	protected function checkCampaginAccess(Campaign $res){
		$qb = $this->em
			->createQueryBuilder()
			->select('count(x)')
			->from('\\Adserver\\Models\\Campaign', 'x')
			->leftJoin('x.userList', 'u');
		$qb->andWhere($qb->expr()->eq('x.id', $res->getId()));
		$qb->andWhere($qb->expr()->eq('u.id', $this->security->getToken()->getUser()->getId()));
		if(!$qb->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR)) throw new AccessDeniedException();
	}
		
	protected function editAction(Request $request, $id, $_route){
	
		$res = Campaign::find($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
				
		$this->checkCampaginAccess($res);
		
		$selfUrl = $this->urlGenerator->generate('campaign.edit', array('id'=>$id));
		
		$form = $this->campaignForm($res);
		$form->setAction($selfUrl);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$values = $form->getValues();
		
			if($res->getField('id')!=$values['id']) { throw new \RuntimeException('Invalid resource ID'); }
						
			$res->setName($values['name']);
			$res->setActive($values['active']);
			$res->setGoal($values['goal']);
			
			$res->setTimeFilterActive($values['timeFilterActive']);
			$res->setDSunday($values['dSunday']);
			$res->setDMonday($values['dMonday']);
			$res->setDTuesday($values['dTuesday']);
			$res->setDWednesday($values['dWednesday']);
			$res->setDThursday($values['dThursday']);
			$res->setDFriday($values['dFriday']);
			$res->setDSaturday($values['dSaturday']);
			$res->setCookie($values['cookie']);
			for($i=0;$i<24;$i++){
				$res->{'setH'.$i}($values['h'.$i]);
				
			}
			
			$this->alerts->addInfo('Campaign updated');
			
			return $this->redirect($selfUrl);
		}
		
		return array(
			'breadcrumb' => $this->getBreadcrumb()+array(($res->getName())=>$selfUrl),
			'form' => $form,
			'id'=> $res->getId(),
			'campaignRuntimeList' => $res->getCampaignRuntimeList(),
			'campaignRefererFilterList' => $res->getCampaignRefererFilterList(),
			'bannerList' => $res->getBannerList()
		);
	}
	
	protected function createAction( Request $request, $_route ){
	
		$res = new Campaign();	
		$selfUrl = $this->urlGenerator->generate('campaign.create');
		$form = $this->campaignForm($res);
		$form->setAction($selfUrl);
	
		// processing
		if ($form->isSubmitted() && $form->isValid()) {
	
			$values = $form->getValues();
		
			if($res->getField('id')!=$values['id']) { throw new \RuntimeException('Invalid resource ID'); }
			$user = User::find($this->em, $this->security->getToken()->getUser()->getId());
			$res->getUserList()->add($user);
			$user->getCampaignList()->add($res);
			$res->setName($values['name']);
			$res->setActive($values['active']);
			$res->setGoal($values['goal']);
			
			$res->setTimeFilterActive($values['timeFilterActive']);
			$res->setDSunday($values['dSunday']);
			$res->setDMonday($values['dMonday']);
			$res->setDTuesday($values['dTuesday']);
			$res->setDWednesday($values['dWednesday']);
			$res->setDThursday($values['dThursday']);
			$res->setDFriday($values['dFriday']);
			$res->setDSaturday($values['dSaturday']);
			$res->setCookie($values['cookie']);
			for($i=0;$i<24;$i++){
				$res->{'setH'.$i}($values['h'.$i]);
				
			}			
				
			$res->persist($this->em, true);
	
			$this->alerts->addInfo('New campaign saved');
	
			return $this->redirect($this->urlGenerator->generate('campaign.edit', array('id'=>$res->getId())));
		}
	
		return array(
			'breadcrumb' => $this->getBreadcrumb()+array('Create'=>$selfUrl),
			'form' => $form
		);
	
	}
	
	protected function campaignRuntimeForm($res){
		$form = new Form();
	
		$form->addHidden('campaign');
		$form->addText('start', 'Start:')
			->setRequired('Start is required');
		$form->addText('end', 'End:')
			->setRequired('Start is required');
			
		$form->addSubmit('send', 'Save');
	
		$form->setDefaults(array(
				'start' => $res->getEnd()?$res->getEnd()->format('d/m/Y H:i:s'):'',
				'end' => $res->getStart()?$res->getStart()->format('d/m/Y H:i:s'):'',
				'campaign' => $res->getCampaign()->getId()	
		));
	
		return $this->bootstrapForm($form);
	}
	
	protected function createCampaginRuntimeAction( Request $request, $id, $_route ){
	
		$campaign = Campaign::find($this->em, $id);
		
		if(!$campaign){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
				
		$this->checkCampaginAccess($campaign);
		
		$selfUrl = $this->urlGenerator->generate('campaignRuntime.create', array('id'=>$id));
		
		$res = new CampaignRuntime();
		$res->setCampaign($campaign);
		
		$form = $this->campaignRuntimeForm($res);
		$form->setAction($selfUrl);
	
		// processing
		if ($form->isSubmitted() && $form->isValid()) {
	
			$values = $form->getValues();
	
			if($campaign->getId()!=$values['campaign']) { throw new \RuntimeException('Invalid resource ID'); }
			
			$format = 'm/d/Y H:i A';

			$res->setStart(\DateTime::createFromFormat($format, $values['start']));
			$res->setEnd(\DateTime::createFromFormat($format, $values['end']));
			$campaign->getCampaignRuntimeList()->add($res);			
	
			$res->persist($this->em, true);
	
			$this->alerts->addInfo('New campaign runtime saved');
	
			return $this->redirect($this->urlGenerator->generate('campaign.edit', array('id'=>$campaign->getId())));
		}
	
		return array(
				'breadcrumb' => $this->getBreadcrumb()+array(($campaign->getName())=>$this->urlGenerator->generate('campaign.edit', array('id'=>$campaign->getId())), 'Create Runtime'=>$selfUrl),
				'form' => $form
		);
	
	}
	
}