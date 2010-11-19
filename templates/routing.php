<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
define('MYACTIVERECORD_CONNECTION_STR', 'DSN');
define('MYACTIVERECORD_CHARSET', 'MARCHAR');
define('DEFAULT_LIMIT', 'DEFLIMIT');
define('APP_ROOT', dirname(__FILE__));
date_default_timezone_set('TZ');
$page_title = $page_header = 'Site Name';
$self = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$id = 0;
$out = $flash = $head = '';
session_start();
require_once('_app/lib/MyActiveRecord.php');
require_once('_app/lib/MyActionView.php');
require_once('_app/lib/MyActionController.php');
require_once('_app/models/_app.php');
require_once('_app/lib/smartypants.php');
require_once('_app/lib/markdown.php');
require_once('_app/helpers/helpers.php');
function __autoload($class_name) {
	$class_name_path = underscore($class_name);
	if(file_exists(APP_ROOT . '/_app/models/' . $class_name_path . '.php')){
		return @require(APP_ROOT . '/_app/models/' . $class_name_path . '.php');
	}
	if(file_exists(APP_ROOT . '/_app/controllers/' . $class_name_path . '.php')){
		return @require(APP_ROOT . '/_app/controllers/' . $class_name_path . '.php');
	}
	trigger_error('Could not load the "' . $class_name . '" class. Make sure you have generated it before trying again.', E_USER_ERROR);
}
load_models();
$navigation = build_navbar();
//catch any flash messages and play them back
if(isset($_SESSION["flash"])){
	$flash = $_SESSION["flash"];
	unset($_SESSION["flash"]);
}
//routing happens here
$uri = preg_split('/\//',$_SERVER['REQUEST_URI'],-1,PREG_SPLIT_NO_EMPTY);
//If your application is in a subfolder, you can either array_shift($uri) or adjust your
//uri index numbers upward below. If you do the shift trick, then be sure to add a base HREF 
//tag to your layouts/index.html.php file.
if(!isset($uri[0]) || empty($uri[0])){
	//add root view actions here
	//$uri[0] = 'some_controller';
	//$uri[1] = 'some_action';
}
if(isset($uri[0]) && file_exists(APP_ROOT . '/_app/controllers/' . strtolower($uri[0]) . '_controller.php')){
	require_once(APP_ROOT . '/_app/controllers/' . strtolower($uri[0]) . '_controller.php');
	/**
	 * Naming Convention:
	 * db table: people
	 * model: class Person (person.php)
	 * controller: class PeopleController (people_controller.php)
	 */
	$className = classify($uri[0]);
	$controllerName = pluralize($className) . 'Controller';
	$object = ActiveRecord::Create($className); //blank model for default forms etc.
	$view = new ActionView();
	
	$controller = new $controllerName($object,$view);
	if(isset($uri[2]) && is_numeric(substr($uri[2],0,1)) && !is_numeric($uri[1])){
		$id = (int) preg_replace('/^([\d]+?)[^\d]*$/',"$1",$uri[2]);
	}
	if(isset($_POST['delete'])){
		//allow button name to control action
		$uri[1] = 'delete';
	}
	if(isset($uri[1]) && $uri[1] != 'index' && method_exists($controller,$uri[1])){
		$page_title .= t(' | ' . ucfirst($uri[1]) . ' ' . $className);
		$page_header = h(ucfirst($uri[1]) . ' ' . $className);
		$out = $controller->{$uri[1]}($id);
	}else{
		$page_title .= t(' | ' . pluralize($className));
		$page_header = h(pluralize($className));
		$out = $controller->index();
	}
	include(APP_ROOT . '/_app/views/layouts/index.html.php');
}elseif(!isset($uri[0])){
	//catch a missing model with no default, show the list of models
	$controller = new DefaultController();
	$out = $controller->index();
	include(APP_ROOT . '/_app/views/layouts/index.html.php');
}else{
	header('x',true,404); //sends default 404 as configured by your server
	$page_header = 'Missing File!';
	$page_title = 'Missing File | ' . $page_title;
	$out = '<p>The file you requested could not be located.</p><p>Please check the address and try something different next time.</p>';
	include(APP_ROOT . '/_app/views/layouts/index.html.php');
}
?>
