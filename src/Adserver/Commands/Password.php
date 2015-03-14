<?php
namespace Adserver\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Security\Core\User\User;

class Password extends AppCommand
{

    protected function configure()
    {
        $this
        ->setName('adserver:password')
        ->setDescription('Encrypt a password')
        ->setDefinition(array(
            new InputArgument('password', InputArgument::REQUIRED, 'Plaintext password.')
        ))
        ->setHelp(<<<EOT
Encrypt a password.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output){
    	
        $app = $this->app;

        if (($password = $input->getArgument('password')) === null) {
            throw new \RuntimeException("You must specify a password to encode.");
        }
		
        $fakeUser = new User('...', '...', array( 'ROLE_ADMIN' ));
        
		$encoder = $app['security.encoder_factory']->getEncoder($fakeUser);
        
        $password = $encoder->encodePassword($password, $fakeUser->getSalt());

        echo "\n".$password."\n";
         
    }
    
}
