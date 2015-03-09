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
	require $appFolder.'/config.php';
	return $app;
});

$app->register(new ServiceControllerServiceProvider());

$app->register(new RoutingServiceProvider());
	
// === Doctrine ===	
$app['models.namespace']="Adserver\Models";
$app['models.path']= $app['root']."/src/Adserver/Models";
$app->register(new \Fbn\Doctrine\ModelsServiceProvider());

// === Security ===
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session.storage.save_path'] = $app['config']['session.localPath']?$app['folder'].'/cache/sessions':null;
$app['security.userProvider'] =  function($c){
	return new \Adserver\Security\UserProvider($c['orm.em']);
};
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
		'security.firewalls' => array(
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
	return new \Agents\Controllers\SecurityController(
			$c['url_generator'],
			$c['oc'],
			$c['orm.em'],
			$c['config'],
			$c['meshService']
	);
};
$app->get('/signIn', "controllers.security:signInAction");
// ================

// === Filters ===
$app['meshService'] = function($c){
	return new \Fbn\Silex\MeshService(
			$c['folder'].'/views', 
			$c['folder'].'/views/_layout');
};
$app->after(function (Request $request, Response $response) {
	if(is_a($response, '\\Fbn\\Silex\\MeshResponse')){
		$response->render();
	}
	return $response; 
});
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
	'home', 'agents', 'customers', 'orders'
);
foreach ($controllers as $cc){
	$app['controllers.'.$cc] = function($c) use ($cc){
		$claz = "\\Agents\\Controllers\\".ucfirst($cc)."Controller";
		return new $claz(
				$c['url_generator'],
				$c['oc'],
				$c['orm.em'],
				$c['config'],
				$c['meshService'],
				$c['security'],
				$c['security.encoder_factory'],
				$c['alerts']
		);
	};	
}
// ====================


// === Routing ===
$app->get('/', "controllers.home:indexAction");

$app->get('/agent', "controllers.agents:indexAction")->bind('agents');
$app->post('/agent/delete', "controllers.agents:deleteAction")->bind('agents.delete');
$app->get('/agent/create', "controllers.agents:createAction")->bind('agents.create');
$app->post('/agent/create', "controllers.agents:createAction");
$app->get('/agent/{id}', "controllers.agents:editAction")->bind('agents.edit');
$app->post('/agent/{id}', "controllers.agents:editAction");
$app->get('/agent/{id}/customers', "controllers.agents:indexCustomersAction")->bind('agents.customers');
$app->post('/agent/{id}/customers/{cid}', "controllers.agents:addCustomersAction")->bind('agents.customers.add');
$app->post('/agent/{id}/customers.delete', "controllers.agents:deleteCustomersAction")->bind('agents.customers.delete');

$app->get('/customer', "controllers.customers:indexAction")->bind('customers');
$app->get('/customer/search', "controllers.customers:searchAction")->bind('customers.search'); //json
$app->get('/customer/{id}/login', "controllers.customers:loginAction")->bind('customers.login');

$app->get('/order', "controllers.orders:indexAction")->bind('orders');


return $app;


