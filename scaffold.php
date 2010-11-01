<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
define('MYACTIVERECORD_CONNECTION_STR', 'mysql://waltd:testpass@localhost/test');
define('MYACTIVERECORD_CHARSET', 'UTF-8'); //whatever your DB is set to use
define('DEFAULT_LIMIT', '1000'); //top limit for FindAll
date_default_timezone_set('US/Eastern');
//
require_once('templates/MyActiveRecord.php');
require_once('templates/MyActionView.php');
require_once('templates/inflector.php');
require_once('templates/inflections.php');
class ActiveRecord extends MyActiveRecord{

}
class ActionView extends MyActionView{
	
}
$db = parse_url(MYACTIVERECORD_CONNECTION_STR);
$db = $db['path'];
function __autoload($class_name) {
	$class_name_path = underscore($class_name);
	$missed = false;
	if(!file_exists(dirname(__FILE__) . $db . '/_app/models/' . $class_name_path . '.php')){
		$missed = true;
	}else{
		require (dirname(__FILE__) . $db . '/_app/models/' . $class_name_path . '.php');
	}
	if($missed == true && !file_exists(dirname(__FILE__) . $db . '/_app/controllers/' . $class_name_path . '.php')){
		$missed = true;
	}else{
		$missed = false;
		require (dirname(__FILE__) . $db . '/_app/controllers/' . $class_name_path . '.php');
	}
	if($missed == true) trigger_error('Could not load the "' . $class_name . '" class. Make sure you have generated it before trying again.', E_USER_ERROR);
}
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
function is_linking_table($table_name,$all_tables){
	if(false !== strpos($table_name,'_')){
		$parts = explode('_',$table_name);
		$matches = 0;
		foreach($parts as $part){
			if(in_array($part, $all_tables)){
				//this might be a linking table
				$matches ++;
			}
		}
		return ($matches == 2);
	}
	return false;
}
$out = '';
$table_name = (isset($_GET['table_name'])) ? $_GET['table_name'] : '';
$arrFields = array();
$tables = ActiveRecord::Tables();
if(!empty($table_name) && in_array($table_name, $tables)){
	$arrFields = get_fields_from_table($table_name);
	if(isset($_POST['generate_wrapper'])){
		$code = '<?php 
class ' . ucfirst($table_name) . ' extends ActiveRecord{
	function save(){
';
		foreach(array('regexp','existence','uniqueness_of','email') as $validation){
			if(isset($_POST[$validation])){
				$key = '$this->validate_' . $validation;
				foreach($_POST[$validation] as $k => $v){
					if(!empty($v)) {
						if($validation == 'regexp') $k = $_POST['regexp'][$k] . "', '" . $k;
						$code .= "\t\t" . $key . '(\'' . $k . '\');
';
					}
				}
			}
		}
		if(isset($_POST['timestamps'])){
			foreach($_POST['timestamps'] as $field){
				$function = (substr($field,-3) == '_at') ? '$this->DbDateTime();' : '$this->DbDate();';
				$condition = (substr($field,0,6) == 'added_') ? 'if($this->id < 1) ' : '';
				$code .= (preg_match('/^added|^updated/',$field)) ? "\t\t" . $condition . '$this->' . $field . ' = ' . $function . '
' : '';
			}
		}
		$code .= '		return parent::save();
	}
';
		if(isset($_POST['children']) || isset($_POST['habtm'])){
			$code .= "\tfunction destroy(){\n";
			foreach($_POST['children'] as $k => $v){
				if($v > 0){
					$code .= '		foreach($this->find_children(\'' . $k . '\') as $c) $c->destroy();
';
				}
			}
			foreach($_POST['habtm'] as $k => $v){
				if($v > 0){
					$code .= '		foreach($this->find_attached(\'' . $k . '\') as $a) $this->detach($a);
';
				}
			}
			$code .= "\t\treturn parent::destroy();\n\t}\n";
		}
		$code .= '}
?>';
		$out .= '<h2>' . substr($db,1) . '/' . $table_name . '</h2>
		<p><a href="scaffold.php" class="faux-button">Start Over</a></p>';
		$view_create = $view_edit = $view_index = $view_show = '<?php
?>
';
		$view_index .= '<table>
	<tr>
		<th>actions</th>
';
		foreach($arrFields as $k => $v){
			$view_index .= '		<th>' . $k . '</th>
';
		}
		$view_index .= '	</tr>
<?php
	foreach($objects as $object){
		print \'	<tr>
		<td>
			\' . ActionView::link_to("Show","show",$object) . \' | \' . ActionView::link_to("Edit","edit",$object) . \'
		</td>
';
		$view_edit .= '<form action="" method="post" accept-charset="utf-8">
';
		$view_create .= '<form action="" method="post" accept-charset="utf-8">
';
		foreach($arrFields as $k => $v){
			if($k != 'id') {
				$view_create .= '	<p>' . ActionView::Input($k, $v) . '</p>
';
				if($v['Type'] == 'text'){
					$view_show .= '	<p><strong>' . $k . '</strong></p>
	<?= ActionView::simple_format(\'' . $k . '\',$object) ?>
';
				}else{
					$view_show .= '	<p><strong>' . $k . '</strong><br />
		<?= $object->h(\'' . $k . '\') ?>
	</p>
';
				}
		}
			$view_edit .= '	<p>' . ActionView::Input($k, $v) . '</p>
';
			$view_index .= ($v['Type'] == 'tinyint(1)') ? '		<td>\' . (($object->' . $k . ' > 0) ? \'✓\' : \'\') . \'</td>
' : '		<td>\' . $object->h(\'' . $k . '\') . \'</td>
';
		}
		$view_create .= '	<p>' . ActionView::Input('save', array(), array('class' => 'form_button')) . ' <?= ActionView::link_to("Cancel","index",$object, array("class" => "faux-button"))?></p>
</form>
';
		$view_edit .= '	<p>' . ActionView::Input('save', array(), array('class' => 'form_button')) . ActionView::Input('delete', array(), array('class' => 'form_button')) . ' <?= ActionView::link_to("Index","index",$object, array("class" => "faux-button"))?></p>
</form>
';
		$view_show .= '	<?= \'<p>\' . ActionView::link_to("Index","index",$object, array("class" => "faux-button")) . \' \' . ActionView::link_to("Edit","edit",$object, array("class" => "form_button")) . \'</p>\' ?>
';
		$view_index .= '	</tr>
\';
	}
?>
</table>
<p><?= ActionView::link_to("Create","create",$object, array("class" => "form_button")) ?></p>
';
		$routing = file_get_contents('templates/routing.php');
		$routing = str_replace(array('DSN','MARCHAR','DEFLIMIT','TZ'),array(MYACTIVERECORD_CONNECTION_STR,MYACTIVERECORD_CHARSET,DEFAULT_LIMIT,date_default_timezone_get()),$routing);
		$htaccess = file_get_contents('templates/htaccess.txt');
		$layout = file_get_contents('templates/layouts_index.html');
		$default = file_get_contents('templates/layouts_default.html');
		$default_controller = file_get_contents('templates/default_controller.php');
		$controller = file_get_contents('templates/controller.php');
		function create_file($path, $content, $mode = 0664){
			if(!@file_exists(dirname(__FILE__) . '/generated_code' . $path) || isset($_POST['force'])){
				file_put_contents(dirname(__FILE__) . '/generated_code' . $path,$content);
				chmod(dirname(__FILE__) . '/generated_code' . $path,$mode);
				return '<p>Generated ' . $path . '.</p>';
			}else{
				return '<p>' . $path . ' exists; skipping...</p>';
			}
			return '';
		}
		function create_directory($path, $mode = 0775){
			if(!@file_exists(dirname(__FILE__) . '/generated_code' . $path) || isset($_POST['force'])){
				@mkdir(dirname(__FILE__) . '/generated_code' . $path);
				chmod(dirname(__FILE__) . '/generated_code' . $path,$mode);
				return '<p>Generated ' . $path . '.</p>';
			}else{
				return '<p>' . $path . ' exists; skipping...</p>';
			}
			return '';
		}
		function copy_directory($source, $dest = '', $foldermode = 0775, $filemode = 0664){ //warning, not recursive, will not do nested directories
			$dest = (!empty($dest)) ? $dest : $source;
			$out = create_directory($dest,$foldermode);
			$files = scandir(dirname(__FILE__) . '/templates' . $source);
			foreach($files as $f){
				if(!is_dir(dirname(__FILE__) . '/templates' . $source . '/' . $f) && file_exists(dirname(__FILE__) . '/templates' . $source . '/' . $f)) {
					copy(dirname(__FILE__) . '/templates' . $source . '/' . $f,dirname(__FILE__) . '/generated_code' . $dest . '/' . $f);
					chmod(dirname(__FILE__) . '/generated_code' . $dest . '/' . $f,$filemode);
					$out .= '<p>Copied ' . $f . ' to ' . $dest . '</p>';
				}
			}
		}
		function copy_file($source, $dest, $mode = 0664){
			copy(dirname(__FILE__) . '/templates/' . $source, dirname(__FILE__) . '/generated_code' . $dest);
			chmod(dirname(__FILE__) . '/generated_code' . $dest,0664);
			return '<p>Generated ' . $dest . '.</p>';
		}
		if(!file_exists(dirname(__FILE__) . '/generated_code') || (file_exists(dirname(__FILE__) . '/generated_code') && !is_writable(dirname(__FILE__) . '/generated_code'))){
			$out .= '<p>Warning! Create a folder in the same directory as scaffold.php called <strong>generated_code</strong>, and be sure to give it <strong>777</strong> permissions!</p>';
		}else{
			$out .= create_directory($db);
			$out .= create_directory($db . '/_app');
			$out .= create_directory($db . '/_app/models');
			$out .= create_directory($db . '/_app/helpers');
			$out .= create_directory($db . '/_app/views');
			$out .= create_directory($db . '/css');
			$out .= create_directory($db . '/_app/views/' . $table_name);
			$out .= create_directory($db . '/_app/lib');
			$out .= create_directory($db . '/_app/controllers');
			$out .= copy_directory('/images', $db . '/Resources');
			$out .= create_directory($db . '/_app/views/layouts');
			$out .= copy_file('MyActionController.php',$db . '/_app/lib/MyActionController.php');
			$out .= copy_file('MyActiveRecord.php',$db . '/_app/lib/MyActiveRecord.php');
			$out .= copy_file('MyActionView.php',$db . '/_app/lib/MyActionView.php');
			$out .= copy_file('application.css',$db . '/css/application.css');
			$out .= copy_file('inflector.php',$db . '/_app/lib/inflector.php');
			$out .= copy_file('inflections.php',$db . '/_app/lib/inflections.php');
			$out .= copy_file('/images/favicon.ico',$db . '/favicon.ico');
			$out .= create_file($db . '/_app/views/' . $table_name . '/create.html.php',$view_create);
			$out .= create_file($db . '/_app/views/' . $table_name . '/edit.html.php',$view_edit);
			$out .= create_file($db . '/_app/views/' . $table_name . '/show.html.php',$view_show);
			$out .= create_file($db . '/_app/views/' . $table_name . '/index.html.php',$view_index);
			$out .= create_file($db . '/_app/models/' . $table_name . '.php',$code);
			$out .= create_file($db . '/_app/controllers/' . $table_name . '_controller.php',sprintf($controller,ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name)));
			$out .= create_file($db . '/_app/controllers/default_controller.php',$default_controller);
			$out .= create_file($db . '/.htaccess', $htaccess);
			$out .= create_file($db . '/_routing.php', $routing);
			$out .= create_file($db . '/_app/views/layouts/index.html.php',$layout);
			$out .= create_file($db . '/_app/views/layouts/default.html.php',$default);
		}
	}else{
		$out = '<h2>' . substr($db,1) . '/' . $table_name . '</h2>
		<form action="scaffold.php?table_name=' . $table_name . '" method="post">
';
		
		foreach($arrFields as $k => $v){
			if($k == 'id'){
				$out .= '<p><span class="field">id</span>(primary key)</p>';
			}elseif(substr($k,-3) == '_id'){
				$out .= '<p><span class="field">' . $k . '</span>(parent key)</p>';
			}elseif(preg_match('/_at$|_on$/',$k) && preg_match('/date/',$v['Type'])){
				$out .= '<p><span class="field">' . $k . '</span>(timestamp)<input type="hidden" name="timestamps[' . $k . ']" value="' . $k . '" id="timestamps_' . $k . '"/></p>';
			}elseif($v['Type'] == 'tinyint(1)'){
				$out .= '<p><span class="field">' . $k . '</span>(checkbox) <label class="inline" for="existence_' . $k . '"><input type="hidden" name="existence[' . $k . ']" value="0" /><input type="checkbox" name="existence[' . $k . ']" value="1" id="existence_' . $k . '"/> Validate existence</label></p>';
			}else{
				$regexp = (isset($_POST['regexp'][$k])) ? $_POST['regexp'][$k] : '';
				$out .= '<p><span class="field">' . $k . '</span>Validate: <label class="inline" for="regexp_' . $k . '">regexp</label><input type="text" name="regexp[' . $k . ']" value="' . $regexp . '" id="regexp_' . $k . '"/>
				<label class="inline" for="existence_' . $k . '"><input type="hidden" name="existence[' . $k . ']" value="0" /><input type="checkbox" name="existence[' . $k . ']" value="1" id="existence_' . $k . '"/> existence</label>
				<label class="inline" for="uniqueness_of_' . $k . '"><input type="hidden" name="uniqueness_of[' . $k . ']" value="0" /><input type="checkbox" name="uniqueness_of[' . $k . ']" value="1" id="uniqueness_of_' . $k . '"/> uniqueness_of</label>
				<label class="inline" for="email_' . $k . '"><input type="hidden" name="email[' . $k . ']" value="0" /><input type="checkbox" name="email[' . $k . ']" value="1" id="email_' . $k . '"/> email</label></p>';
			}
		}
		//scan for children
		foreach($tables as $table){
			if(in_array($table_name . '_id',array_keys(get_fields_from_table($table)))){
				if(! is_linking_table($table,$tables)){
					$out .= '<p><span class="field"><strong>' . $table . '</strong></span>(children)<input type="hidden" name="children[' . $table . ']" value="0"/><label class="inline" for="children_' . $table . '"><input type="checkbox" name="children[' . $table . ']" id="children_' . $table . '" value="1" />Delete Children on Delete</label></p>';
				}else{
					$partner = preg_replace('/_?' . $table_name . '_?/','',$table);
					$out .= '<p><span class="field"><strong>' . $partner . '</strong></span>(many-to-many)<input type="hidden" name="habtm[' . $partner . ']" value="0"/><label class="inline" for="habtm_' . $partner . '"><input type="checkbox" name="habtm[' . $partner . ']" id="habtm_' . $partner . '" value="1" />Unlink Related Records on Delete</label></p>';
				}
			}
		}
		$out .= '<p><label for="force" class="inline"><input type="checkbox" name="force" class="indent" value="1" id="force"/>Overwrite Existing Files</label></p>
		<p><input type="submit" name="generate_wrapper" class="indent form_button" value="Generate" id="generate_wrapper"/> <a href="scaffold.php" class="faux-button">Start Over</a></p></form>';
	}
}else{
	$out .= '<h2>Choose a table in “' . substr($db,1) . '”</h2>';
	$out .= '<p>Available tables in <strong>' . substr($db,1) . '</strong>:</p><ul style="list-style-type: none; padding:0; margin: 1em 0;">';
	foreach($tables as $table){
		if( ! is_linking_table($table,$tables) && in_array('id',array_keys(get_fields_from_table($table)))){
			$out .= '<li style="padding: 4px; display: inline;"><a href="scaffold.php?table_name=' . $table . '" class="faux-button">' . $table . '</a></li>';
		}
	}
	$out .= '</ul>';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title>Scaffold Generator</title>
	<link rel="stylesheet" href="templates/application.css" type="text/css" media="screen" charset="utf-8"/>
	<style type="text/css" media="screen">
	#PageDiv {
		margin-top:64px;
	}
	.field {
		width: 120px;
		display: inline-block;
		text-align: right;
		padding-right: 12px;
	}
	label {
		margin-left: 4px;
	}
	.indent {
		margin-left: 132px;
	}
	#logo {
		position: absolute;
		top: -6px;
		right: 8px;
		background: url(templates/images/logo.png) no-repeat top right;
		width: 125px;
		height: 100px;
	}
	</style>

</head>

<body>
	<div id="PageDiv">
		<div id="logo"></div>
		<h1>Generator</h1>
		<?php echo $out; ?>
	</div>

</body>
</html>