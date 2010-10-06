<?php
class MyActionController{
	
	function redirect_to($strNextAction){
		header("Location: " . MyActionView::url_for($strNextAction,$this->object));
		exit;
	}
	function manage_result($strNextAction, $strSuccessMessage){
		if(!$this->object->get_errors()){
			$_SESSION["flash"] = flash($strSuccessMessage);
			$this->redirect_to($strNextAction);
		}else{
			$GLOBALS["flash"] = flash($this->object->get_errors(),"error");
		}
	}
}
?>