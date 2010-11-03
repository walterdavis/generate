<?php
//add your base extensions to the main classes here
class ActiveRecord extends MyActiveRecord{
	function __get($key){
		if( method_exists($this,"$key")){
			return call_user_func(  array($this, "$key" ) );
		}elseif(in_array($key,ActiveRecord::Tables())){
			if($p = $this->find_parent($key)) {
				$key = $p->get_label();
				return $p->$key;
			}else{
				return false;
			}
		}
		return false;
	}
	function get_value($fieldname){
		if(substr($fieldname,-3) == '_id'){
			$classname = substr($fieldname, 0, -3);
			//see if it's a classname
			if($class = ActiveRecord::Create($classname)){
				$fieldname = $classname;
			}
		}
		return $this->$fieldname;
	}
}
class ActionView extends MyActionView{

}
class ActionController extends MyActionController{

}

?>