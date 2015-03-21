<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Fbn\Doctrine\TablePrefix;
use Adserver\Utils\Alert;
use Adserver\Models\User;

$app = call_user_func(function() {
	$appFolder = __DIR__;
	$rootFolder = dirname($appFolder);
	$loader = require_once $rootFolder.'/vendor/autoload.php';
	$app = new \Fbn\Silex\FbnApp();
	$app['loader'] = $loader;
	$app['root'] = $rootFolder;
	$app['name'] = 'Adserver';
	$app['folder'] = $appFolder;
	$app['cachefolder'] = $rootFolder.'/cache';
	require $appFolder.'/config.php';
	return $app;
});

$app->register(new ServiceControllerServiceProvider());

$app->register(new RoutingServiceProvider());
	
// === Doctrine ===	
$app['models.namespace']="Adserver\Models";
$app['models.path']= $app['root']."/src/Adserver/Models";
$app->register(new \Fbn\Doctrine\ModelsServiceProvider());

// === Twig ===
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => $app['folder'].'/views'
));

// === Security ===
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session.storage.save_path'] = $app['config']['session.localPath']?$app['cachefolder'].'/sessions':null;
$app['security.userProvider'] =  function($c){
	return new \Adserver\Security\UserProvider($c['orm.em']);
};
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
		'security.firewalls' => array(
			'deliver' => array(
				'pattern' => '^/deliver'
			),
		    'admin' => array(
		        'pattern' => '^(?!\/signIn)',
		        //'http' => true,
		    	'form' => array('login_path' => '/signIn', 'check_path' => '/signCheck'),
		    	'logout' => array('logout_path' => '/signOut'),
	    		'remember_me' => array(
	    				'key'                => 'sdfasdf[]s{}23',
	    				'always_remember_me' => false
	    		),
		        'users' => $app['security.userProvider']
		    ),
		)
));
$app['security.role_hierarchy'] = array( (User::ROLE_ADMIN) => array((User::ROLE_CUSTOMER)) );
$app->register(new Silex\Provider\RememberMeServiceProvider());
$app['controllers.security'] = function($c){
	return new \Adserver\Controllers\SecurityController(
			$c['url_generator'],
			$c['orm.em'],
			$c['alerts'],
			$c['twig'],
			$c['config']
	);
};
$app->get('/signIn', "controllers.security:signInAction");
// ================

// === Filters ===
// ===============

// === Alerts ===
$app['alerts'] = function($c) use ($app){ 
	return Alert::load($app['session']); 
};
$app->after(function ( $request,  $response) use ($app) {
	$app['alerts']->save($app['session']);
});
// ==============

// === Controllers ====
$controllers = array(
	'home', 'campaign', 'banner'
);
foreach ($controllers as $cc){
	$app['controllers.'.$cc] = function($c) use ($cc){
		$claz = "\\Adserver\\Controllers\\".ucfirst($cc)."Controller";
		return new $claz(
			$c['url_generator'],
			$c['orm.em'],
			$c['alerts'],
			$c['twig'],
			$c['config'],
			$c['security'],
			$c['security.encoder_factory']
		);
	};	
}
$app['controllers.deliver'] = function($c){
	return new \Adserver\Controllers\DeliverController(
			$c['url_generator'],
			$c['orm.em'],
			$c['config']
	);
};
// ====================


// === Routing ===

$app->get('/', "controllers.home:indexAction")->bind('home.index');

$app->get('/deliver', "controllers.deliver:indexAction")->bind('deliver.index');

$app->get('/campaign', "controllers.campaign:indexAction")->bind('campaign.index');
$app->match('/campaign/create', "controllers.campaign:createAction")->method('GET|POST')->bind('campaign.create');
$app->match('/campaign/edit/{id}', "controllers.campaign:editAction")->method('GET|POST')->bind('campaign.edit');
$app->match('/campaign/runtime/create/{id}', "controllers.campaign:createCampaginRuntimeAction")->method('GET|POST')->bind('campaignRuntime.create');
$app->match('/campaign/runtime/edit/{id}', "controllers.campaign:editCampaginRuntimeAction")->method('GET|POST')->bind('campaignRuntime.edit');
$app->match('/campaign/banner/create/{id}', "controllers.banner:createAction")->method('GET|POST')->bind('banner.create');
$app->match('/campaign/banner/edit/{id}', "controllers.banner:editAction")->method('GET|POST')->bind('banner.edit');
$app->post('/campaign/banner/upload', "controllers.banner:uploadAction")->bind('banner.upload');
$app->match('/campaign/referer/create/{id}', "controllers.campaign:createCampaginRefererAction")->method('GET|POST')->bind('campaignReferer.create');
$app->match('/campaign/referer/edit/{id}', "controllers.campaign:editCampaginRefererAction")->method('GET|POST')->bind('campaignReferer.edit');

return $app;


