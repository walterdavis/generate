<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
define('MYACTIVERECORD_CONNECTION_STR', 'mysql://waltd:@localhost/test');
define('MYACTIVERECORD_CHARSET', 'UTF-8');
define('DEFAULT_LIMIT', '1000');
date_default_timezone_set('US/Eastern');
//
require_once('templates/MyActiveRecord.php');
require_once('templates/MyActionView.php');
class ActiveRecord extends MyActiveRecord{

}
class ActionView extends MyActionView{
	
}
$db = parse_url(MYACTIVERECORD_CONNECTION_STR);
$db = $db['path'];
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
		if(isset($_POST['children'])){
			$code .= "\tfunction destroy(){\n";
			foreach($_POST['children'] as $k => $v){
				if($v > 0){
					$code .= '		foreach($this->find_children(\'' . $k . '\') as $c) $c->destroy();
';
				}
			}
			$code .= "\t\treturn parent::destroy();\n\t}\n";
		}
		$code .= '}
?>';
		$out .= '<p><a href="scaffold.php">Start Over</a></p>';
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
			$view_index .= '		<td>\' . $object->h(\'' . $k . '\') . \'</td>
';
		}
		$view_create .= '	<p>' . ActionView::Input('save') . ' | <?= ActionView::link_to("Cancel","index",$object)?></p>
</form>
';
		$view_edit .= '	<p>' . ActionView::Input('save') . ActionView::Input('delete') . ' | <?= ActionView::link_to("Index","index",$object)?></p>
</form>
';
		$view_show .= '	<?= \'	<p>\' . ActionView::link_to("Index","index",$object) . \' | \' . ActionView::link_to("Edit","edit",$object) . \'</p>\' ?>
';
		$view_index .= '	</tr>
\';
	}
?>
</table>
<p><?= ActionView::link_to("Create","create",$object) ?></p>
';
		$routing = file_get_contents('templates/routing.php');
		$routing = str_replace(array('DSN','MARCHAR','DEFLIMIT','TZ'),array(MYACTIVERECORD_CONNECTION_STR,MYACTIVERECORD_CHARSET,DEFAULT_LIMIT,date_default_timezone_get()),$routing);
		$htaccess = file_get_contents('templates/htaccess.txt');
		$layout = file_get_contents('templates/layouts_index.html');
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
		if(!file_exists(dirname(__FILE__) . '/generated_code')){
			$out .= '<p>Warning! Create a folder in the same directory as scaffold.php called generated_code, and be sure to give it 777 permissions!</p>';
		}else{
			$out .= create_directory($db);
			$out .= create_directory($db . '/_models');
			$out .= create_directory($db . '/_helpers');
			$out .= create_directory($db . '/_views');
			$out .= create_directory($db . '/css');
			$out .= create_directory($db . '/_views/' . $table_name);
			$out .= create_directory($db . '/_lib');
			$out .= create_directory($db . '/_controllers');
			$out .= copy_directory('/images', $db . '/_images');
			$out .= create_directory($db . '/_views/layouts');
			$out .= copy_file('MyActionController.php',$db . '/_lib/MyActionController.php');
			$out .= copy_file('MyActiveRecord.php',$db . '/_lib/MyActiveRecord.php');
			$out .= copy_file('MyActionView.php',$db . '/_lib/MyActionView.php');
			$out .= copy_file('application.css',$db . '/css/application.css');
			$out .= create_file($db . '/_views/' . $table_name . '/create.html.php',$view_create);
			$out .= create_file($db . '/_views/' . $table_name . '/edit.html.php',$view_edit);
			$out .= create_file($db . '/_views/' . $table_name . '/show.html.php',$view_show);
			$out .= create_file($db . '/_views/' . $table_name . '/index.html.php',$view_index);
			$out .= create_file($db . '/_models/' . $table_name . '.php',$code);
			$out .= create_file($db . '/_controllers/' . $table_name . '_controller.php',sprintf($controller,ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name),ucfirst($table_name)));
			$out .= create_file($db . '/.htaccess', $htaccess);
			$out .= create_file($db . '/routing.php', $routing);
			$out .= create_file($db . '/_views/layouts/index.html.php',$layout);
		}
	}else{
		$out = '<h1>' . substr($db,1) . '</h1>
		<form action="scaffold.php?table_name=' . $table_name . '" method="post">
		<p><label for="force">Force</label><input type="checkbox" name="force" value="1" id="force"/></p>';
		
		foreach($arrFields as $k => $v){
			if($k == 'id'){
				$out .= '<p><span class="field">id</span>(primary key)</p>';
			}elseif(substr($k,-3) == '_id'){
				$out .= '<p><span class="field">' . $k . '</span>(parent key)</p>';
			}elseif(preg_match('/_at$|_on$/',$k) && preg_match('/date/',$v['Type'])){
				$out .= '<p><span class="field">' . $k . '</span>(timestamp)<input type="hidden" name="timestamps[' . $k . ']" value="' . $k . '" id="timestamps_' . $k . '"/></p>';
			}else{
				$regexp = (isset($_POST['regexp'][$k])) ? $_POST['regexp'][$k] : '';
				$out .= '<p><span class="field">' . $k . '</span>Validate: <label for="regexp_' . $k . '">regexp</label><input type="text" name="regexp[' . $k . ']" value="' . $regexp . '" id="regexp_' . $k . '"/>
				<label for="existence_' . $k . '"><input type="hidden" name="existence[' . $k . ']" value="0" /><input type="checkbox" name="existence[' . $k . ']" value="1" id="existence_' . $k . '"/> existence</label>
				<label for="uniqueness_of_' . $k . '"><input type="hidden" name="uniqueness_of[' . $k . ']" value="0" /><input type="checkbox" name="uniqueness_of[' . $k . ']" value="1" id="uniqueness_of_' . $k . '"/> uniqueness_of</label>
				<label for="email_' . $k . '"><input type="hidden" name="email[' . $k . ']" value="0" /><input type="checkbox" name="email[' . $k . ']" value="1" id="email_' . $k . '"/> email</label></p>';
			}
		}
		//scan for children
		foreach($tables as $table){
			if(in_array($table_name . '_id',array_keys(get_fields_from_table($table)))){
				$out .= '<p><span class="field">' . $table . '</span>(children)<input type="hidden" name="children[' . $table . ']" value="0"/><label for="children_' . $table . '"><input type="checkbox" name="children[' . $table . ']" id="children_' . $table . '" value="1" />Delete Children on Delete</label></p>';
			}
		}
		$out .= '<p><input type="submit" name="generate_wrapper" class="indent" value="Generate" id="generate_wrapper"/> <a href="scaffold.php">Start Over</a></p></form>';
	}
}else{
	$out .= '<h1>Choose a table</h1>';
	$out .= '<p>Available tables in <strong>' . substr($db,1) . '</strong>:</p><ul>';
	foreach($tables as $table) $out .= '<li><a href="scaffold.php?table_name=' . $table . '">' . $table . '</a></li>';
	$out .= '</ul>';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title>Scaffold Generator</title>
	<style type="text/css" media="screen">
	body {
		padding: 24px;
		font: 13px "Lucida Grande", Lucida, Verdana, sans-serif;
		font-size: 14px;
		background-color: #fff;
	}
	#PageDiv {
		width: 800px;
		margin: auto;
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
	</style>

</head>

<body>
	<div id="PageDiv">
		<?= $out ?>
	</div>

</body>
</html>