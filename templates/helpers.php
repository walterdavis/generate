<?php
function render_partial($strPartial, $object=null){
	$parts = explode('/',$strPartial);
	$path = APP_ROOT . '/_app/views/';
	$file = '_' . array_pop($parts);
	$path .= implode('/',$parts);
	$path .= '/' . $file;
	ob_start();
	foreach(array('','.php','.html','.html.php','.phtml') as $ext){
		if(@file_exists($path . $ext)){
			include($path . $ext);
			return ob_get_clean();
		}
	}
	ob_end_clean();
	trigger_error( $path . '.php does not exist' );
}
function render($strTemplate, $object=null){
	$parts = explode('/',$strTemplate);
	$path = APP_ROOT . '/_app/views/';
	if(count($parts) == 1 && is_object($object)){
		$path .= strtolower(get_class($object)) . '/';
	}
	$path .= implode('/',$parts);
	ob_start();
	foreach(array('','.php','.html','.html.php','.phtml') as $ext){
		if(@file_exists($path . $ext)){
			include($path . $ext);
			return ob_get_clean();
		}
	}
	ob_end_clean();
	trigger_error( $path . '.php does not exist' );
}
function h($string){
	return htmlentities($string,ENT_COMPAT,MYACTIVERECORD_CHARSET);
}
function t($string){
	$search = array("’","‘","”","“",'"');
	$replace = array("'","'",'','','');
	return str_replace($search,$replace,(strip_tags($string)));
}
//flash messages built here
function flash($arrMessages,$strClass=''){
	if(empty($strClass)) $strClass = 'flash';
	$out = '<ul class="' . $strClass . '">';
	foreach((array)$arrMessages as $m) $out .=  '<li>' . $m . '</li>';
	return $out . '</ul>';
}
function build_navbar(){
	//build automatic navigation bar
	$navigation = '<ul class="navigation"><li><a href="/">Home</a></li>';
	$models = scandir(APP_ROOT . '/_app/models');
	foreach($models as $m){
		if(!is_dir(APP_ROOT . '/_app/models/' . $m) && file_exists(APP_ROOT . '/_app/models/' . $m) && substr($m,0,1) != '.' && substr($m,0,1) != '_') {
			$m = substr($m,0,strrpos($m,'.'));
			$navigation .= '<li><a href="/' . $m . '">' . trim(ucfirst(str_replace('_',' ',$m))) . '</a></li>';
		}
	}
	$navigation .= '</ul>';
	return $navigation;
}
function load_models(){
	$models = scandir(APP_ROOT . '/_app/models');
	foreach($models as $m){
		if(!is_dir(APP_ROOT . '/_app/models/' . $m) && file_exists(APP_ROOT . '/_app/models/' . $m) && substr($m,0,1) != '.' && substr($m,0,1) != '_') {
			require_once(APP_ROOT . '/_app/models/' . $m);
		}
	}
}

?>