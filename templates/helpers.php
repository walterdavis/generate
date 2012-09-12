<?php
function render_collection($strPartial, $objects=array()){
	$out = '';
	foreach($objects as $object){
		$out .= render_partial($strPartial,$object);
	}
	return $out;
}
function render_partial($strPartial, $object=null){
	$self = (isset($GLOBALS['self'])) ? $GLOBALS['self'] : '';
	$parts = preg_split('/\//',$strPartial, -1, PREG_SPLIT_NO_EMPTY);
	$path = APP_ROOT . '/_app/views/';
	$view = new ActionView();
	$pfile = '_';
	if(is_object($object)){
		$view->object = $object;
	}
	if(count($parts) == 1 && is_object($object)){
		$path .= tableize(get_class($object)) . '/';
		$pfile .= tableize(get_class($object)) . '_';
	}
	$file = '_' . array_pop($parts);
	$pfile .= array_pop($parts);
	$path .= implode('/',$parts);
	$path .= '/' . $file;
	ob_start();
	foreach(array('','.php','.html','.html.php','.phtml') as $ext){
		if(@file_exists($path . $ext)){
			include($path . $ext);
			return ob_get_clean();
		}
	}
	//try again in the root folder
	$path = APP_ROOT . FOLDER;
	foreach(array('','.php','.html','.html.php','.phtml') as $ext){
		if(@file_exists($path . $ext)){
			include($path . $pfile . $ext);
			return ob_get_clean();
		}
	}
	ob_end_clean();
	trigger_error( $path . '.* does not exist' );
}
function render($strTemplate, $object=null, $new_object = null){
	$self = (isset($GLOBALS['self'])) ? $GLOBALS['self'] : '';
	if(is_array($object)){
		$objects = $object;
		if(count($objects) > 0) $new_object = reset($object);
	}
	if(!$new_object && isset($GLOBALS['object'])) $new_object = $GLOBALS['object'];
	$parts = preg_split('/\//',$strTemplate, -1, PREG_SPLIT_NO_EMPTY);
	$path = APP_ROOT . '/_app/views/';
	$view = new ActionView();
	if(is_object($object)){
		$view->object = $object;
	}elseif(is_object($new_object)){
		$view->object = $new_object;
	}
	if(count($parts) == 1 && is_object($new_object)){
		$path .= tableize(get_class($new_object)) . '/';
	}
	$path .= implode('/',$parts);
	ob_start();
	foreach(array('','.php','.html','.html.php','.phtml') as $ext){
		if(@file_exists($path . $ext)){
			include($path . $ext);
			return ob_get_clean();
		}
	}
	//try again in the root folder, just in case
	$path = APP_ROOT . FOLDER;
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
	$out = '<ul class="' . $strClass . '">' . "\n";
	foreach((array)$arrMessages as $m) $out .=  '<li>' . $m . "</li>\n";
	return $out . "</ul>\n";
}
function build_navbar(){
	//build automatic navigation bar
	$navigation = '<ul class="navigation">
	<li><a href="' . FOLDER . '">Home</a></li>
';
	$models = scandir(APP_ROOT . '/_app/models');
	foreach($models as $m){
		if(!is_dir(APP_ROOT . '/_app/models/' . $m) && file_exists(APP_ROOT . '/_app/models/' . $m) && substr($m,0,1) != '.' && substr($m,0,1) != '_') {
			$m = substr($m,0,strrpos($m,'.'));
			$navigation .= '  <li><a href="' . FOLDER . tableize($m) . '">' . pluralize(humanize($m)) . '</a></li>
';
		}
	}
	$navigation .= '</ul>
';
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

function get_primary_key($table_name){
	$pk = null;
	foreach(get_fields_from_table($table_name) as $k => $v){
		if(!$pk && $v['Extra'] == 'auto_increment'){
			return $k;
		}
	}
	return false;
}

/**
 * identical to MyActiveRecord::Columns, but uses raw table name rather than abstract lookup
 *
 * @param string $table_name 
 * @return multi-dimensional array of fields
 * @author Walter Lee Davis
 */
function get_fields_from_table($table_name){
	$arrFields = array();
	if( $rscResult = ActiveRecord::Query("SHOW COLUMNS FROM $table_name") ){
		while( $col = mysql_fetch_assoc($rscResult) ){
			$arrFields[$col['Field']] = $col;
		}
		mysql_free_result($rscResult);
	}
	return $arrFields;
}

function translate_attribute_name($fieldname, $table_name){
	if($fieldname == get_primary_key($table_name)){
		return classify($fieldname);
	}
	if(substr($fieldname,-3) == '_id'){
		$classname = substr($fieldname, 0, -3);
		//see if it's a classname
		if($class = ActiveRecord::Create($classname)){
			return $classname;
		}
	}
	return $fieldname;
}
function m($string){
	return SmartyPants(Markdown($string));
}
function cycle($odd = 'odd', $even = 'even'){
	static $class;
	if($class == $odd) {
		$class = $even;
	}else{
		$class = $odd;
	}
	return $class;
}
function send_header($content='text/html'){
	return header('Content-type: ' . $content);
}
function send_response_code($code=200){
	return header(':',true,$code);
}
?>