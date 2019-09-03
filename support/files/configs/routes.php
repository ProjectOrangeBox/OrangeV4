<?php

/**
 * Routes
 *
 * These are setup using PHPs static Function Syntax
 * MyClass::test();
 *
 */

$config['routes'] = [
	'form' => 'FormController',
	'form/test_filter' => 'FormController::test_filter',
	'form/test_pear_plugin/(:any)' => 'FormController::test_pear_plugin',
	'form/test_validation' => 'FormController::test_validation',

	'test' => 'TestController::index',
	'test/test1' => 'TestController::test1',
	'test/test2' => 'TestController::test2',
	'test/test3' => 'TestController::test3',
	'test/test4' => 'TestController::test4',

	'form/input' => 'FormController::index',
	'form/output' => ['post'=>'FormController::post'],
	'form/dn' => 'FormController::dotnotation',

	'inject' => 'FormController::inject',

	'search' => 'FormController::searchApplication',

	'user/drpepper' => 'don::index',

	'tyson/([a-zA-Z]+)/edit/(/d+)' => '/test2/levela/levelb/admin/don/controller::method',
	'foo/([a-zA-Z]+)/edit/(/d+)' => '/test2/levela/levelb/admin/don/index/controller::method',
	'bar/([a-zA-Z]+)/edit/(/d+)' => '/test2/levela/levelb/admin/don/index/controller::method',

	'snakes/([a-zA-Z]+)/edit/(/d+)' => ['Get'=>'forder1/folder2/admin/welcome::index'],

	'products/(:num)' => ['DelEte' => 'product/delete/$1'],
	'dogss/(:num)' => ['cli' => 'product/delete/$1'],
	'product/(:num)' => 'product/edit/$1',
	'products/delete/(:num)' => 'product/edit$Httpmethod/$1',

	'welcome/index' => 'welcomeController::index',
	'welcome/edit/(/d+)' => '/packages/projectorangebox/theme/controllers/folder1/folder2/admin/PeopleController::index',

	'welcome/index2' => '/packages/projectorangebox/theme/controllers/folder1/folder2/admin/PeopleController::index',
	'welcome/index3' => '/packages/projectorangebox/theme/controllers/folder1/folder2/admin/PeopleController::index',

	'welcome/remap' => '/packages/projectorangebox/theme/controllers/welcomeController::remap',

	'welcome/test' => 'welcome::test',
];

/* home page */
$config['routes'][''] = ['*'=>'/packages/projectorangebox/theme/controllers/WelcomeController::index'];

/* four oh four! - catch all */
$config['routes']['(.*)'] = ['*'=>'/packages/projectorangebox/theme/controllers/WelcomeController::fourohfour'];

/* PHP Unit test controller location */
if (isset($_ENV['PHPUNIT'])) {
	$config['routes']['(.*)'] = ['cli'=>'/tests/support/PHPUnitController::index'];
}

/**
 * Middleware Request
 * Method is always request(/CI_Input &$input) : bool
 */
$config['request'] = [
	'(.*)' => ['*'=>['/projectorangebox/theme/middleware/PrivateMiddleware','/projectorangebox/theme/middleware/PublicMiddleware']],
];


//$onRequest['(.*)']['*'] = ['name'];

//$onRequest['(.*)']['post'] = ['packagePath/name'];


/**
 * Middleware Response
 * method is always response(string &$output) : bool
 */

$config['response'] = [
	'(.*)' => ['*'=>['/packages/projectorangebox/theme/middleware/PublicMiddleware']],
];

/*

These aren't used anymore

'default_controller' => 'welcome';

now should be

$config[''] => ['*','/packages/projectorangebox/theme/controllers/WelcomeController::index'];

and

'404_override' => '';

now should be

$config['(.*)'] => ['*','/packages/projectorangebox/theme/controllers/WelcomeController::fourohfour'];

This isn't used because ALL dashes should be underscores.
If you need dashes then you can create a route for it.

'translate_uri_dashes'] = FALSE;

*/

/* how many levels down is the __ROOT__ from APPPATH/Controllers ? */
$config['back up levels to root'] = '../..';

/* what is the default method if none is provied (which you should!) */
$config['default method'] = 'index';

/* turn on middleware */
$config['request middleware on'] = true;
$config['response middleware on'] = true;
