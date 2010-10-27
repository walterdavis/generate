<?php
Class MyActionView{

	function picker($strKey,$arrOptions,$boolUseKeyAsValue=false,$boolCombo = false){
		$combo = ($boolCombo) ? ' class="combo"' : '';
		$out = '<select size="1" name="' . $strKey . '" id="' . $strKey . '"' . $combo . '>';
		$out .= '<option value="" label=""></option>';
		foreach($arrOptions as $k=>$o) {
			if($boolUseKeyAsValue){
				$out .= '<option label="' . $o . '" value="' . $k . '"';
				$out .= ($this->object->id > 0 && $k == $this->object->$strKey) ? ' selected="selected"' : '';
			}else{
				$out .= '<option label="' . $o . '" value="' . $o . '"';
				$out .= ($this->object->id > 0 && $o == $this->object->$strKey && '' !== $this->object->$strKey) ? ' selected="selected"' : '';
			}
			$out .= '>' . $o . '</option>';
		}
		return $out . '</select>';
	}
	
	function h($strKey){
		return MyActiveRecord::h($strKey);
	}
	
	function distinct_picker($strKey,$arrDefaultValues = array(),$boolCombo = false){
		$combo = ($boolCombo) ? ' class="combo"' : '';
		$cols = $this->object->distinct_values($strKey);
		foreach($arrDefaultValues as $d){
			foreach($cols as $k=>$v){
				if($v == $d) unset($cols[$k]);
			}
		}
		$cols = array_merge($arrDefaultValues,$cols);
		$out = '<select name="' . $strKey . '" size="1" id="' . $strKey . '"' . $combo . '>';
		foreach($cols as $col){
			$out .= '<option label = "' . $this->h($col) . '" value="' . $this->h($col) . '"';
			if($this->$strKey == $col) $out .= ' selected="selected"';
			$out .= '>' . $this->h($col) . '</option>';
		}
		$out .= '</select>';
		return $out;
	}
	

	function ParentPicker($object, $strKey,$boolCombo = false){
		if(!$boolCombo && MyActiveRecord::AllowNull(get_class($object),$strKey)) $boolCombo = true;
		$combo = ($boolCombo) ? ' class="combo"' : '';
		$out = '<select size="1" name="' . $strKey . '" id="' . $strKey . '"' . $combo . '>';
		if($boolCombo) $out .= '<option value="" label=""></option>';
		$model = substr($strKey,0,-3);
		$name = MyActiveRecord::Label($model);
		$objects = MyActiveRecord::FindAll($model,null,$name . ' ASC');
		foreach($objects as $o) {
			$out .= '<option label="' . $o->h($name) . '" value="' . $o->id . '"';
			$out .= ($object->$strKey == $o->$name) ? ' selected="selected"' : '';
			$out .= '>' . $o->h($name) . '</option>';
		}
		return $out . '</select>';
	}
		
	function Show($template,$mxdObject,$singleton = null){
		$objects = array();
		if(is_array($mxdObject) && count($mxdObject) > 0){
			$object = reset($mxdObject);
			$objects = $mxdObject;
		}elseif(is_object($mxdObject)){
			$object = $mxdObject;
		}elseif(is_object($singleton)){
			$object = $singleton;
		}else{
			return '';
		}
		$path = APP_ROOT . '/_views/' . strtolower(get_class($object)) . '/' . $template . '.html.php';
		ob_start();
		include($path);
		return ob_get_clean();
	}
	
	function Input($name, $arrField = array()){
		if($name == 'id'){
			$out = '<input type="hidden" name="id" value="<?=$object->id?>" />';
		}elseif($name == 'save'){
			$out = '<label for="save">&nbsp;</label><input type="submit" name="save" value="Save" id="save" class="save" />';
		}elseif($name == 'delete'){
			$out = '&nbsp; <input type="submit" name="delete" value="Delete" id="delete" class="delete" />';
		}elseif(substr($name,-3) == '_id' && MyActiveRecord::TableExists(substr($name, 0, -3))){
			$parent_name = substr($name, 0, -3);
			$out = '<label for="' . $name . '">' . $parent_name . '</label>';
			$out .= '
<?php
	print ActionView::ParentPicker($object,\'' . $name . '\');
?>';
		}elseif(isset($arrField['Field']) && (preg_match('/password/',$arrField['Field'])) || (isset($arrField['Type']) && (preg_match('/password/',$arrField['Type'])))){
			$out = '<label for="' . $name . '">' . $name . '</label><input type="password" class="password" name="' . $name . '" value="" id="' . $name . '"/>';
		}else{
			switch ($arrField['Type']) {
				case 'tinyint(1)':
					//boolean
					$out = '<label for="' . $name . '">' . $name . '</label><input type="hidden" name="' . $name . '" value="0" /><input type="checkbox" name="' . $name . '" value="1" id="' . $name . '" class="boolean"<?= ($object->' . $name . ' > 0) ? \' checked="checked"\' : \'\' ?> />';
					break;
				case 'text':
					$out = '<label for="' . $name . '">' . $name . '</label><textarea name="' . $name . '" rows="8" cols="40"><?=$object->h(\'' . $name . '\')?></textarea>';
					break;
				default:
					if(isset($arrField['Type']) && isset($arrField['Field'])){
						if(preg_match('/datetime/',$arrField['Type'])){
							$classname = 'datetime';
						}elseif(preg_match('/date/',$arrField['Type'])){
							$classname = 'date';
						}elseif(preg_match('/password/',$arrField['Field'])){
							$classname = 'password text';
						}elseif(preg_match('/char/',$arrField['Type'])){
							$classname = 'text';
						}elseif(preg_match('/int/',$arrField['Type'])){
							$classname = 'integer';
						}else{
							$classname = '';
						}
					}
					$out = '<label for="' . $name . '">' . $name . '</label><input type="text" name="' . $name . '" value="<?=$object->h(\'' . $name . '\')?>" id="' . $name . '" class="' . $classname . '"/>';
					break;
			}
		}
		return $out;
	}
	function link_to($strText, $strAction, $object){
		return '<a href="' . MyActionView::url_for($strAction, $object) . '">' . $strText . '</a>';
	}
	function url_for($strAction,$object){
		$controller = strtolower(get_class($object));
		$link = "/" . $controller . "/" . $strAction;
		if($object->id > 0 && $strAction != "index" && $strAction != "create") $link .= "/" . $object->id;
		return $link;
	}
	function simple_format($strKey,$object){
		$out = '<p>' . nl2br($object->h($strKey)) . '</p>';
		$out = preg_replace('/<br \/>\s+<br \/>/m',"</p>\n<p>",$out);
		return $out . "\n";
	}
}
?>