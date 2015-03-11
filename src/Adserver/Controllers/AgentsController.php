<?php
namespace Adserver\Controllers;

use Fbn\Silex\MeshController;
use ApiMart\Models\B2bUser;
use ApiMart\DoctrineExtensions\PagePaginator;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMart\Utils\RoutedUrl;
use Symfony\Component\HttpFoundation\Request;
use Nette\Forms\Form;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nette\Forms\Controls;
use ApiMart\Models\Customer;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgentsController extends B2BController {
	
	protected $titolo = 'Agenti';
	
	protected $code = 'agents';
	
	protected function getBreadcrumb(){
		return parent::getBreadcrumb()+array( 'Agenti'=>$this->urlGenerator->generate('agents') );
	}

	public function indexAction(  Request $request, $_route ){
		
		$this->assertGrantedAdmin();
		
		$qb = $this->em
			->createQueryBuilder()
			->select('x')
			->from('\\ApiMart\\Models\\B2bUser', 'x');
		
		$routedUrl = new RoutedUrl(array(), $_route );
		
		$search=null;
		
		if($request->query->has('q') && $search=$request->query->get('q')){
			$qb->where( 
					$qb->expr()->orX(
				       $qb->expr()->like('x.email', '?1'),
					   $qb->expr()->like('x.firstname', '?1'),
					   $qb->expr()->like('x.lastname', '?1'),
					   $qb->expr()->like('x.gid', '?1') 
					)
			);
			$qb->setParameter(1, '%'.$search.'%'); 
			$routedUrl['q'] = $search;
		}
		
		$paginator = new PagePaginator($qb, $this->pagesize, $request);

		return $this->response( __FUNCTION__, 
				array(
						'search' => $search,
						'collection' => $paginator->getCollection(),
						'paginator' => $paginator->renderPaging(
											$routedUrl,
											$this->urlGenerator )
				)
		);
		
	}
	
	public function indexCustomersAction(  $id, Request $request, $_route ){
		
		$this->assertGrantedAdmin();
	
		$res = B2bUser::guess($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
	
		$qb = $this->em
			->createQueryBuilder()
			->select('x')
			->from('ApiMart\\Models\\Customer', 'x')
			->innerJoin('x.b2bUsers', 'u', 'WITH', 'u = :identifier')
		  	->setParameter('identifier', $res);
		
		$routedUrl = new RoutedUrl(array('id'=> $id), $_route );
	
		$search=null;
	
		if($request->query->has('q') && $search=$request->query->get('q')){
			$qb->where(
					$qb->expr()->orX(
							$qb->expr()->like('x.email', '?1'),
							$qb->expr()->like('x.firstname', '?1'),
							$qb->expr()->like('x.lastname', '?1'),
							$qb->expr()->like('x.gid', '?1')
					)
			);
			$qb->setParameter(1, '%'.$search.'%');
			$routedUrl['q'] = $search;
		}
	
		$paginator = new PagePaginator($qb, $this->pagesize, $request);
	
		return $this->response( __FUNCTION__,
				array(
						'agent' => $res,
						'search' => $search,
						'collection' => $paginator->getCollection(),
						'breadcrumb'=> $this->getBreadcrumb()+array(
								($res->getLabel())=>$this->urlGenerator->generate('agents.edit',array('id'=>$id)), 
								'clienti'=>'#'
						),
						'paginator' => $paginator->renderPaging(
								$routedUrl,
								$this->urlGenerator )
				)
		);
	
	}
	
	public function editAction(  $id, Request $request, $_route ){
		
		$this->assertGrantedAdmin();
		
		$res = B2bUser::guess($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$form = new Form();			
		$form->addHidden('b2bUserId');
		$form->addText('email', 'Email:')
			->setRequired('Si prega di specificare la mail')
			->addRule(Form::EMAIL, 'Specificare un indirizzo email valido');
		$form->addPassword('password', 'Password:');			
		$form->addText('firstname', 'Nome:')
			->setRequired('Si prega di specificare il nome');
		$form->addText('lastname', 'Cognome:')
			->setRequired('Si prega di specificare il cognome');
		$form->addCheckbox('role', ' Amministratore');
		$form->addSubmit('send', 'Salva');
		
		$form->setDefaults(array(
				'b2bUserId' => $res->getB2bUserId(),
				'email' => $res->getEmail(),
				'firstname' => $res->getFirstname(),
				'lastname' => $res->getLastname(),
				'role' => $res->isAdmin()
		));
		
		$this->bootstrapForm($form);
		
		$selfUrl = $this->urlGenerator->generate('agents.edit', array('id'=>$id));
		
		// processing
		if ($form->isSubmitted() && $form->isValid()) {
			
			$values = $form->getValues();
		
			if($res->getField('b2bUserId')!=$values['b2bUserId']) { throw new \RuntimeException('Id risorsa invalido'); }
						
			$res->setEmail($values['email']);
			$res->setFirstname($values['firstname']);
			$res->setLastname($values['lastname']);
			
			if($values['password']){
				$fakeUser = new \Symfony\Component\Security\Core\User\User('...', '...', array( 'ROLE_ADMIN' ));
				$res->setPassword($this->securityEncoderFactory->getEncoder($fakeUser)->encodePassword($values['password'], $fakeUser->getSalt()));					
			}
			
			$values['role']?$res->setRoleAdmin():$res->setRoleAgent();
						
			$this->alerts->addInfo('Aggiornamento riuscito');
			
			return $this->redirect($selfUrl);
		}
		
		return $this->response( __FUNCTION__,
				array(
						'breadcrumb' => $this->getBreadcrumb()+array(($res->getLabel())=>$selfUrl),
						'form' => $form
				)
		);
		
	}
	
	public function createAction( Request $request, \Silex\Application $app, $_route ){
		
		$this->assertGrantedAdmin();
	
		$res = new B2bUser();
	
		$form = new Form();
		$form->addText('email', 'Email:')
			->setRequired('Si prega di specificare la mail')
			->addRule(Form::EMAIL, 'Specificare un indirizzo email valido');
		$form->addPassword('password', 'Password:')
			->setRequired('Si prega di specificare la password');
		$form->addText('firstname', 'Nome:')
			->setRequired('Si prega di specificare il nome');
		$form->addText('lastname', 'Cognome:')
			->setRequired('Si prega di specificare il cognome');
		$form->addCheckbox('role', ' Amministratore');
		$form->addSubmit('send', 'Salva');
	
		$form->setDefaults(array(
				'email' => $res->getEmail(),
				'firstname' => $res->getFirstname(),
				'lastname' => $res->getLastname(),
				'role' => false
		));
	
		$this->bootstrapForm($form);
		
		$selfUrl = $this->urlGenerator->generate('agents.create');
	
		// processing
		if ($form->isSubmitted() && $form->isValid()) {
				
			$values = $form->getValues();
				
			$res->setField('email', $values['email']);
			
			$fakeUser = new \Symfony\Component\Security\Core\User\User('...', '...', array( 'ROLE_ADMIN' ));
			$res->setPassword($this->securityEncoderFactory->getEncoder($fakeUser)->encodePassword($values['password'], $fakeUser->getSalt()) );
			
			$res->setFirstname($values['firstname']);
			
			$res->setLastname($values['lastname']);
			
			$values['role']?$res->setRoleAdmin():$res->setRoleAgent();
			
			$res->persist($this->em, true);
				
			$this->alerts->addInfo('Nuovo elemento creato');
				
			return $this->redirect($this->urlGenerator->generate('agents'));
		}
	
		return $this->response( __FUNCTION__,
				array(
						'breadcrumb' => $this->getBreadcrumb()+array('Crea'=>$selfUrl),
						'form' => $form
				)
		);
	
	}
	
	public function deleteAction( Request $request, $_route ){
		
		$this->assertGrantedAdmin();
		
		$i=0;
		foreach ($request->request->get('ids') as $id){
			$res = B2bUser::guess($this->em, $id);
			if($res){
				$i++;
				$res->delete($this->em);
			}
		}
		$this->alerts->addInfo($i.' elementi eliminati con successo.');
		return $this->redirect($this->urlGenerator->generate('agents'));
	}
	
	public function addCustomersAction($id, $cid, Request $request, $_route ){
		
		$this->assertGrantedAdmin();
		
		$res = B2bUser::guess($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$customer = Customer::guess($this->em, $cid);
		
		if(!$customer){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$res->getCustomers()->add($customer);
		
		return new JsonResponse(array('data'=>null));
		
	}
	
	public function deleteCustomersAction($id, Request $request, $_route ){
		
		$this->assertGrantedAdmin();

		$res = B2bUser::guess($this->em, $id);
		
		if(!$res){
			throw new HttpException(404, 'Resource '.$id.' not found');
		}
		
		$ids = $request->request->get('ids');
		
		foreach ($res->getCustomers() as $c){
			if(in_array($c->getCustomerId(), $ids) ){
				$res->getCustomers()->removeElement($c);
			}
		}
		
		return $this->redirect($this->urlGenerator->generate('agents.customers', array('id'=>$id)));
	}

}