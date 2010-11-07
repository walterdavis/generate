<?php
/**
 * MyActionView
 *
 * A companion View class for MyActiveRecord.
 *
 * License
 * 
 * Copyright (c) 2010, Walter Lee Davis <waltd@wdstudio.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 *	-	Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 *	-	Redistributions in binary form must reproduce the above copyright 
 *		notice, this list of conditions and the following disclaimer in the 
 *		documentation and/or other materials provided with the distribution.
 *
 *	-	Neither the name of MyActionView nor the names of its contributors 
 *		may be used to endorse or promote products derived from this 
 *		software without specific prior written permission.
 *
 *	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS 
 *	IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 *	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
 *	PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR 
 *	CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 *	EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
 *	PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 *	PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 *	LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
 *	NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
 *	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
Class MyActionView{
	var $object;
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
	
	function children_picker($strClass){
		$out = '<div class="children_picker" id="' . tableize($strClass) . '">
';
		$links = $this->object->find_linked($strClass);
		foreach(MyActiveRecord::FindAll($strClass) as $target){
			$checked = (array_key_exists($target->id,$links)) ? ' checked="checked"' : '';
			$out .= '<p><input type="hidden" name="' . tableize($strClass) . '[0]" value="0"/><input type="checkbox" class="right" name="' . tableize($strClass) . '[' . $target->id . ']" id="' . tableize($strClass) . '_' . $target->id . '" value="1"' . $checked . ' /><label class="inline" for="' . tableize($strClass) . '_' . $target->id . '">' . h($target->{$target->get_label()}) . '</label></p>
';
		}
		$out .= '
</div>
';
		return $out;
	}
	

	function ParentPicker($strKey,$boolCombo = false, $html = array()){
		if(!$boolCombo && MyActiveRecord::AllowNull(get_class($this->object),$strKey)) $boolCombo = true;
		if ($boolCombo) $field_html = array('class' => 'combo');
		$out = '<select size="1" name="' . $strKey . '" id="' . $strKey . '"%s>';
		if($boolCombo) $out .= '<option value="" label=""></option>';
		$model_name = substr($strKey,0,-3);
		$model = MyActiveRecord::Create($model_name);
		$name = $model->get_label();
		$objects = MyActiveRecord::FindAll($model_name);
		foreach($objects as $o) {
			$out .= '<option label="' . $o->h($name) . '" value="' . $o->id . '"';
			$out .= ($this->object->$strKey == $o->id) ? ' selected="selected"' : '';
			$out .= '>' . $o->h($name) . '</option>';
		}
		foreach($html as $k => $v){
			if(array_key_exists($k, $field_html)){
				//class is additive, all others are replaced
				if($k == 'class'){
					$field_html['class'] .= ' ' . $v;
				}else{
					$field_html[$k] = $v;
				}
			}else{
				$field_html[$k] = $v;
			}
		}
		$html_extras = '';
		foreach($field_html as $k => $v){
			$html_extras .= ' ' . $k . '="' . $v . '"';
		}
		return sprintf($out, $html_extras) . '</select>';
	}
		
	function Show($template, $mxdObject = null, $singleton = null){
		$objects = array();
		if(is_array($mxdObject) && count($mxdObject) > 0){
			$object = reset($mxdObject);
			$objects = $mxdObject;
		}elseif(is_object($mxdObject)){
			$object = $mxdObject;
		}elseif(is_object($singleton)){
			$object = $singleton;
		}else{
			//we're not using these objects, so carry on
			$object = null;
		}
		if(is_object($object)){
			$path = APP_ROOT . '/_app/views/' . strtolower(get_class($object)) . '/' . $template . '.html.php';
		}else{
			$path = APP_ROOT . '/_app/views/' . $template . '.html.php';
		}
		ob_start();
		include($path);
		return ob_get_clean();
	}
	
	function Input($name, $arrField = array(), $html = array()){
		$label = humanize($name);
		if($name == 'id'){
			$field_html = array();
			$out = '<input type="hidden" name="id" value="<?=$object->id?>"%s />';
		}elseif($name == 'save'){
			$field_html = array('id' => 'save', 'class' => 'save');
			$out = '<label for="save">&nbsp;</label><input type="submit" name="save" value="Save"%s />';
		}elseif($name == 'delete'){
			$field_html = array('id' => 'delete', 'class' => 'delete');
			$out = '&nbsp; <input type="submit" name="delete" value="Delete"%s/>';
		}elseif(substr($name,-3) == '_id' && MyActiveRecord::TableExists(tableize(substr($name, 0, -3)))){
			$parent_name = substr($name, 0, -3);
			$field_html = array();
			$out = '<label for="' . $name . '"%s>' . humanize($parent_name) . '</label>';
			$html_array_as_string = 'array(';
			foreach($html as $k => $v){
				$html_array_as_string .= "'" . $k . "' => " . $v . "',";
			}
			if(substr($html_array_as_string,-1) == ',') $html_array_as_string = substr($html_array_as_string,-1);
			$html_array_as_string .= ')';
			$out .= '
<?= $view->ParentPicker(\'' . $name . '\', false, ' . $html_array_as_string . ') ?>';
		}elseif(isset($arrField['Field']) && (preg_match('/password/',$arrField['Field'])) || (isset($arrField['Type']) && (preg_match('/password/',$arrField['Type'])))){
			$field_html = array('class' => 'password');
			$out = '<label for="' . $name . '">' . $label . '</label><input type="password" name="' . $name . '" value="" id="' . $name . '"%s/>';
		}else{
			switch ($arrField['Type']) {
				case 'tinyint(1)':
					//boolean
					$field_html = array('class' => 'boolean');
					$out = '<label for="' . $name . '">' . $label . '</label><input type="hidden" name="' . $name . '" value="0" /><input type="checkbox" name="' . $name . '" value="1" id="' . $name . '" <?= ($object->' . $name . ' > 0) ? \' checked="checked"\' : \'\' ?>%s/>';
					break;
				case 'text':
					$field_html = array();
					$out = '<label for="' . $name . '">' . $label . '</label><textarea name="' . $name . '" rows="8" cols="40"%s><?=$object->h(\'' . $name . '\')?></textarea>';
					break;
				default:
					if(isset($arrField['Type']) && isset($arrField['Field'])){
						if(preg_match('/datetime/',$arrField['Type'])){
							$field_html = array('class' => 'datetime');
						}elseif(preg_match('/date/',$arrField['Type'])){
							$field_html = array('class' => 'date');
						}elseif(preg_match('/password/',$arrField['Field'])){
							$field_html = array('class' => 'password text');
						}elseif(preg_match('/char/',$arrField['Type'])){
							$field_html = array('class' => 'text');
						}elseif(preg_match('/int/',$arrField['Type'])){
							$field_html = array('class' => 'integer');
						}else{
							$field_html = array();
						}
					}
					if(false !== array_search($name,array('added_at','added_on','updated_at','updated_on'))) $field_html['disabled'] = 'disabled';
					$out = '<label for="' . $name . '">' . $label . '</label><input type="text" name="' . $name . '" value="<?=$object->h(\'' . $name . '\')?>" id="' . $name . '"%s/>';
					break;
			}
		}
		foreach($html as $k => $v){
			if(array_key_exists($k, $field_html)){
				//class is additive, all others are replaced
				if($k == 'class'){
					$field_html['class'] .= ' ' . $v;
				}else{
					$field_html[$k] = $v;
				}
			}else{
				$field_html[$k] = $v;
			}
		}
		$html_extras = '';
		foreach($field_html as $k => $v){
			$html_extras .= ' ' . $k . '="' . $v . '"';
		}
		return sprintf($out, $html_extras);
	}
	function LinkTo($object, $strText, $strAction, $html = array()){
		$html_extras = '';
		foreach($html as $k => $v){
			$html_extras .= ' ' . $k . '="' . $v . '"';
		}
		return '<a href="' . MyActionView::UrlFor($object, $strAction) . '"' . $html_extras . '>' . $strText . '</a>';
	}
	
	function link_to($strText, $strAction, $html = array()){
		$html_extras = '';
		foreach($html as $k => $v){
			$html_extras .= ' ' . $k . '="' . $v . '"';
		}
		return '<a href="' . $this->url_for($strAction) . '"' . $html_extras . '>' . $strText . '</a>';
	}
	function UrlFor($object,$strAction){
		if(substr($strAction,0,1) == '/') return $strAction;
		$controller = tableize(get_class($object));
		$link = "/" . $controller . "/" . $strAction;
		if($object->id > 0 && $strAction != "index" && $strAction != "create") $link .= "/" . $object->id;
		return $link;
	}
	function url_for($strAction){
		if(substr($strAction,0,1) == '/') return $strAction;
		$controller = tableize(get_class($this->object));
		$link = "/" . $controller . "/" . $strAction;
		if($this->object->id > 0 && $strAction != "index" && $strAction != "create") $link .= "/" . $this->object->id;
		return $link;
	}
	function simple_format($strKey){
		$out = '<p>' . nl2br($this->object->h($strKey)) . '</p>';
		$out = preg_replace('/<br \/>\s+<br \/>/m',"</p>\n<p>",$out);
		return $out . "\n";
	}
}
?>