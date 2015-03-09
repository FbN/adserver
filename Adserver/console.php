<?php
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

$app = require 'app.php';

// === Console ===
$app['console.name'] = 'Adserver';
$app['console.version'] = 'Alpha';
$app['console'] = function () use ($app) {
	$app->boot();

	$console = new ConsoleApplication($app['console.name'], $app['console.version']);
	$console->setCatchExceptions($app['debug']);
	$console->setDispatcher($app['dispatcher']);

	$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
			'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper( $app['dbs']['default'] ),
			'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper( $app['orm.em'] )
	));
	
	$console->setHelperSet($helperSet);
	
	foreach ($app->keys() as $key) {
		if (false === strpos($key, 'command.')) {
			continue;
		}

		$command = $app[$key];

		if ($command instanceof Command) {
			$console->add($command);
		}
	}
	
	return $console;
};

\Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands($app['console']);
$app['command.agents.password'] = function ($app) { return new \Agents\Commands\Password(); };

// ===============

$app['console']->run();
