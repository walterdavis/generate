<?php
//add your base extensions to the main classes here
class ActiveRecord extends MyActiveRecord{
	//attribute_missing -- does magical foreign key traversals and looks up children or linked objects
	function __get($key){
		if( method_exists($this,"$key")){
			return call_user_func(  array($this, "$key" ) );
		}elseif(in_array($key,ActiveRecord::Tables())){
			//might be children or linked
			if(ActiveRecord::TableExists(ActiveRecord::GetLinkTable(get_class($this),classify($key)))){
				return $this->find_linked(classify($key));
			}
			if(ActiveRecord::Class2Table(classify($key))){
				return $this->find_children(classify($key));
			}
		}elseif(in_array(tableize($key),ActiveRecord::Tables())){
			if($p = $this->find_parent(classify($key))) {
				$key = $p->get_label();
				return $p->$key;
			}else{
				return false;
			}
		}
		trigger_error("ActiveRecord::__get() - could not find attribute: ".$key, E_USER_ERROR);
	}
	//as near to method_missing as PHP can get at the moment
	function __call($method,$arguments){
		if(substr($method, 0, 5) == 'find_'){
			$next = substr($method,5);
			foreach(ActiveRecord::Tables() as $table){
				$key = '/^' . $table . '/';
				if(preg_match($key, $next)){
					$findClass = classify($table);
					$next = substr($next,strlen($table));
					$finder = 'FindAll';
					break;
				}
				$key = '/^' . singularize($table) . '/';
				if(preg_match($key, $next)){
					$findClass = classify($table);
					$next = substr($next,strlen(singularize($table)));
					$finder = 'FindFirst';
					break;
				}
			}
			if(substr($next,0,4) == '_by_'){
				$next = substr($next,4);
				if(false !== strstr($next,'_and_')){
					$link = ' AND ';
					$parts = explode('_and_',$next);
				}elseif(false !== strstr($next,'_or_')){
					$link = ' OR ';
					$parts = explode('_or_',$next);
				}else{
					return ActiveRecord::$finder($findClass,"`{$next}` = \"" . $arguments[0] . '"');
				}
				if(count($parts) == count($arguments)){
					$strWhere = '';
					foreach($parts as $key => $val){
						$strWhere .= ($key > 0) ? $link : '';
						$strWhere .= '`' . $val . '` = "' . $arguments[$key] . '"';
					}
					return ActiveRecord::$finder($findClass,$strWhere);
				}
			}
		}
		trigger_error("ActiveRecord::__call() - could not find method: ".$method, E_USER_ERROR);
	}
	function get_value($fieldname){
		if(substr($fieldname,-3) == '_id'){
			$classname = classify(substr($fieldname, 0, -3));
			//see if it's a classname
			if($class = ActiveRecord::Create($classname)){
				$fieldname = $classname;
			}
		}elseif($this->is_boolean($fieldname)){
			return ($this->$fieldname > 0) ? '✓' : '';
		}
		return $this->$fieldname;
	}
}
class ActionView extends MyActionView{

}
class ActionController extends MyActionController{

}

?>