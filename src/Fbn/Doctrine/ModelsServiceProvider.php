<?php
namespace  Fbn\Doctrine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class ModelsServiceProvider implements ServiceProviderInterface, BootableProviderInterface  {
	
	public function register(Container $app){
		
		\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($app['loader'], 'loadClass'));
		$app->register(new DoctrineServiceProvider);
		$app->register(new DoctrineOrmServiceProvider, array(
				"orm.proxies_dir" => $app['folder']."/resources/cache/proxy",
				"orm.em.options" => array(
						"mappings" => array(
								array(
										"type" => "annotation",
										"namespace" => $app['models.namespace'],
										"path" => $app['models.path'],
										"use_simple_annotation_reader" => false
										//"resources_namespace" => "Fbn\Models",
								)
						)
				)
		));
		$app->extend('dbs', function($dbs, $app) {
		
			$platform = $dbs['default']->getDatabasePlatform();
			$platform->registerDoctrineTypeMapping('enum', 'string');
		
			return $dbs;
		});
		$app['orm.sqlLogger'] = function($c){
			return new \Doctrine\DBAL\Logging\DebugStack();
		};
		
		$app->extend('orm.em', function($em, $app) {
			$evm = $app['dbs.event_manager']['default'];
			if($app['db.options']['prefix']){
				$evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, new TablePrefix($app['db.options']['prefix']));
				
			}
			if($app['debug']){
				$em->getConnection()
				->getConfiguration()
				->setSQLLogger($app['orm.sqlLogger']);
			}
			return $em;
		});
		
	}
	
	public function boot(Application $app){
		
		$app->after(function ( $request,  $response) use ($app) {
			$app['orm.em']->flush();
		}, -1);
		
	}
	
}