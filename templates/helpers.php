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
?>