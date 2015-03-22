<?php
namespace Adserver\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Console\Helper\ProgressBar;

class Fakefill extends AppCommand
{
	
	private $fake;
	
	private $cookieValues = array('cookieval1','cookieval2','cookieval3');
	private $refererValues = array(
			'artefice.co',
			'myfakehost1.com',			
			'myfakehost2.com',
			'myfakehost4.com'
			);
	
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
        ->addOption(
        		'clean',
        		'c',
        		InputOption::VALUE_NONE,
        		'Clean DB'
        )
        ->setHelp(<<<EOT
Generate random data.
EOT
        );
    }
    
    protected function user(){
    	$u = new \Adserver\Models\User();
    	$u->setEmail($this->fake->randomLetter.$this->fake->randomLetter.$this->fake->randomDigit.'--'.$this->fake->email);
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
    	$c->setName($this->fake->company.' '.$this->fake->randomNumber(8));
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
    
    protected function cookieFilter($c){
    	$c->setCookie($this->fake->randomElement($this->cookieValues));    	
    	return $c;
    }
    
    protected function timeFilter($c){   
    	$c->setTimeFilterActive(true);
    	$c->setDSunday( $this->fake->boolean() );
    	$c->setDMonday( $this->fake->boolean() );
    	$c->setDTuesday( $this->fake->boolean() );
    	$c->setDWednesday( $this->fake->boolean() );
    	$c->setDWednesday( $this->fake->boolean() );
    	$c->setDThursday( $this->fake->boolean() );
    	$c->setDFriday( $this->fake->boolean() );
    	$c->setDSaturday( $this->fake->boolean() );
    	for($i=0;$i<24;$i++){
    		$c->{'setH'.$i}( $this->fake->boolean() );
    	}    	
    	return $c;
    }
    
    protected function refererFilter($c){
    	$r = new \Adserver\Models\CampaignRefererFilter();
    	$r->setReferer('http://www.'.$this->fake->randomElement($this->refererValues).'/'.$this->fake->lexify('??????/?????.php'));
    	$r->setHostnameOnly($this->fake->boolean(25));    	
    	$r->setCampaign($c);
    	$c->getCampaignRefererFilterList()->add($r);
    	$r->persist($this->app['orm.em']);
    	return $r;
     }
     
    protected function banner($c){
    	$b = new \Adserver\Models\Banner();
    	$b->setName($this->fake->sentence(2));
    	$b->setCaption($this->fake->sentence(15));
    	$b->setUrl($this->fake->url);
    	$b->setFile('fake.jpg');
    	$b->setHeight($this->fake->randomElement(array(100,200,300,500)));
    	$b->setWidth($this->fake->randomElement(array(100,200,300,500)));
    	$b->setCampaign($c);
    	$c->getBannerList()->add($b);
    	$b->persist($this->app['orm.em']);
    	return $b;
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
        
        $this->app['orm.em']->getConnection()->getConfiguration()->setSQLLogger(null);
        
        if ($input->getOption('clean')) {
        	$this->cleanAll();
        }
        
        $userN = 10000;
        $progress = new ProgressBar($output, $userN);
        $progress->start();
        for($i=0; $i<$userN; $i++){
        	$u = $this->user();
        	for($j=0; $j<10; $j++){
        		$c = $this->campaign($u);
        		
        		// runtime
        		$this->runtime($c, self::TYPE_OLD);
        		$this->runtime($c, self::TYPE_CURRENT);
        		$this->runtime($c, self::TYPE_FUTURE);
        		        		
        		// day/hour targeting
        		if($this->fake->boolean){
        			$this->timeFilter($c);
        		}
        		
        		// coockie targeting
        		if($this->fake->boolean){
        			$this->cookieFilter($c);
        		}
        		
        		// referrer targeting        		
        		if($this->fake->boolean){
        			$n = $this->fake->randomDigitNotNull();
        			for ($j=0; $j <= $n; $j++) {
        				$this->refererFilter($c);
        			}
        		}
        		
        		// banner
        		$n = $this->fake->randomDigitNotNull();
        		for ($k=0; $k <= $n; $k++) {
        			$this->banner($c);
        		}
        		
        	}
        	$this->app['orm.em']->flush();
        	$this->app['orm.em']->clear();
        	gc_collect_cycles();
        	$progress->advance();
        }
        $progress->finish();
        $this->app['orm.em']->flush();
        echo "\n";
         
    }
    
}
