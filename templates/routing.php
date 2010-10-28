<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
define('MYACTIVERECORD_CONNECTION_STR', 'DSN');
define('MYACTIVERECORD_CHARSET', 'MARCHAR');
define('DEFAULT_LIMIT', 'DEFLIMIT');
define('APP_ROOT', dirname(__FILE__));
date_default_timezone_set('TZ');
session_start();
$id = 0;
$out = $flash = '';
$page_title = $page_header = 'Site Name';
require_once('_lib/MyActiveRecord.php');
require_once('_lib/MyActionView.php');
require_once('_lib/MyActionController.php');
class ActiveRecord extends MyActiveRecord{

}
class ActionView extends MyActionView{

}
class ActionController extends MyActionController{

}
function __autoload($class_name) {
	if(!file_exists(APP_ROOT . '/_models/' . $class_name . '.php')){
		trigger_error('Could not load the “' . $class_name . '” class. Make sure you have generated it before trying again.', E_USER_ERROR);
	}else{
		@require(APP_ROOT . '/_models/' . $class_name . '.php');
	}
}
$navigation = '<ul class="navigation"><li><a href="/">Home</a></li>';
$models = scandir(APP_ROOT . '/_models');
foreach($models as $m){
	if(!is_dir(APP_ROOT . '/_models/' . $m) && file_exists(APP_ROOT . '/_models/' . $m)) {
		require_once(APP_ROOT . '/_models/' . $m);
		$m = substr($m,0,strrpos($m,'.'));
		$navigation .= '<li><a href="/' . $m . '">' . trim(ucfirst(str_replace('_',' ',$m))) . '</a></li>';
	}
}
$navigation .= '</ul>';
function flash($arrMessages,$strClass=''){
	if(empty($strClass)) $strClass = 'flash';
	$out = '<ul class="' . $strClass . '">';
	foreach((array)$arrMessages as $m) $out .=  '<li>' . $m . '</li>';
	return $out . '</ul>';
}
//routing happens here
$segments = preg_split('/\//',$_SERVER['REQUEST_URI'],-1,PREG_SPLIT_NO_EMPTY);
if(!isset($segments[0]) || empty($segments[0])){
	//add root view actions here
	//$segments[0] = 'some_controller';
	//$segments[1] = 'some_action';
}

if(@file_exists(APP_ROOT . '/_controllers/' . strtolower($segments[0]) . '_controller.php')){
	require_once(APP_ROOT . '/_controllers/' . strtolower($segments[0]) . '_controller.php');
	/**
	 * Naming Convention:
	 * db: people
	 * model: class People (people.php)
	 * controller: class PeopleController (people_controller.php)
	 */
	$className = ucfirst(strtolower($segments[0]));
	$controllerName = $className . 'Controller';
	$model = ActiveRecord::Create($className); //blank model for default forms etc.
	$controller = new $controllerName($model);
	if(isset($segments[2]) && is_numeric(substr($segments[2],0,1)) && !is_numeric($segments[1])){
		$id = (int) preg_replace('/^([\d]+?)[^\d]*$/',"$1",$segments[2]);
	}
	if(isset($_POST['delete'])){
		//allow button name to control action
		$segments[1] = 'delete';
	}
	if(isset($segments[1]) && method_exists($controller,$segments[1])){
		$out = $controller->{$segments[1]}($id);
		$page_title .= ' | ' . ucfirst($segments[1]) . ' ' . ucfirst($segments[0]);
		$page_header = ucfirst($segments[1]) . ' ' . ucfirst($segments[0]);
	}else{
		$out = $controller->index();
		$page_title .= ' | ' . ucfirst($segments[0]);
		$page_header = 'Index ' . ucfirst($segments[0]);
	}
	if(isset($_SESSION["flash"])){
		$flash = $_SESSION["flash"];
		unset($_SESSION["flash"]);
	}
	include(APP_ROOT . '/_views/layouts/index.html.php');
}else{
	header('x',true,404); //sends default 404 as configured by your server
}
?>
