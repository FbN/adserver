<?php
namespace Adserver\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Security\Core\User\User;

abstract class AppCommand extends Command
{
   
	protected $app;
	
	public function __construct($name = null, $app)
	{
		parent::__construct($name);
		$this->app = $app;		
	}
    
}
