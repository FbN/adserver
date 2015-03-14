<?php
namespace Adserver\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Security\Core\User\User;

class Fakefill extends AppCommand
{
	
	private $fake;
	
	const TYPE_OLD = -1;
	const TYPE_CURRENT = 0;
	const TYPE_FUTURE = 1;
	
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('adserver:fakefill')
        ->setDescription('Generate random data')
        ->setHelp(<<<EOT
Generate random data.
EOT
        );
    }
    
    protected function user(){
    	$u = new \Adserver\Models\User();
    	$u->setEmail($this->fake->email);
		$u->setEncPassword('fabiano', $this->app['security.encoder.digest']);
		$u->setRole(\Adserver\Models\User::ROLE_CUSTOMER);
		$u->setFirstname($this->fake->firstName);
		$u->setLastname($this->fake->lastName);
		$u->setActive(true);
    	$u->persist($this->app['orm.em']);
    	return $u;
    }
    
    protected function campaign($u){
    	$c = new \Adserver\Models\Campaign();
    	$c->setName($this->fake->company.' '.$this->fake->randomDigit);
    	$c->setActive($this->fake->boolean(80));
    	$c->setGoal($this->fake->numberBetween(500, 10000));
    	$c->getUserList()->add($u);
    	$u->getCampaignList()->add($c);
    	$c->persist($this->app['orm.em']);
    	return $c;
    }
    
    protected function runtime($c, $type=self::TYPE_CURRENT){
    	$r = new \Adserver\Models\CampaignRuntime();
		
    	if($type==self::TYPE_CURRENT){
	    	$r->setStart($this->fake->dateTimeBetween('-3 month','now'));
			$r->setEnd($this->fake->dateTimeBetween($r->getStart(),'3 month'));
    	}
    	elseif($type==self::TYPE_OLD){
    		$r->setStart($this->fake->dateTimeBetween('-12 month','-6 month'));
    		$r->setEnd($this->fake->dateTimeBetween($r->getStart(),'-3 month'));
    	}
    	elseif($type==self::TYPE_FUTURE){
    		$r->setStart($this->fake->dateTimeBetween('6 month','12 month'));
    		$r->setEnd($this->fake->dateTimeBetween($r->getStart(),'2 years'));
    	}
		$r->setCampaign($c);
		$c->getCampaignRuntimeList()->add($r);
		$r->persist($this->app['orm.em']);
		return $r;
    }
    
    protected function cleanAll(){
    	$em = $this->app['orm.em'];
    	$meta = $em->getMetadataFactory()->getAllMetadata();
    	foreach ($meta as $m) {
    		echo $m->getName()."\n";
    	}
    	$connection = $em->getConnection();
    	$dbPlatform = $connection->getDatabasePlatform();
    	$connection->beginTransaction();
    	try {
    		$connection->query('SET FOREIGN_KEY_CHECKS=0');
    		foreach ($meta as $m){
    			$cmd = $em->getClassMetadata($m->getName());
	    		$q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
	    		$connection->executeUpdate($q);
    		}
    		$q = $dbPlatform->getTruncateTableSql('ad_user_campaign');
    		$connection->executeUpdate($q);
    		$connection->query('SET FOREIGN_KEY_CHECKS=1');
    		$connection->commit();
    	}
    	catch (\Exception $e) {
    		$connection->rollback();
    	}
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
    	
        $this->fake = \Faker\Factory::create();
        
        $this->cleanAll();
     
        for($i=0; $i<10; $i++){
        	$u = $this->user();
        	for($j=0; $j<10; $j++){
        		$c = $this->campaign($u);
        		
        		// runtime
        		$this->runtime($c, self::TYPE_OLD);
        		$this->runtime($c, self::TYPE_CURRENT);
        		$this->runtime($c, self::TYPE_FUTURE);
        		        		
        		// day/hour targeting        		
        		
        		// coockie targeting
        		
        		// referrer targeting        		
        		
        	}        	
        }
        
        $this->app['orm.em']->flush();
         
    }
    
}
