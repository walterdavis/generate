<?php
class MyActionController{
	var $object;
	function MyActionController(&$object=null,&$view=null){
		if(!$object){
			
		}else{
			$this->object = $object;
		}
		if(!$view){
			$this->view = new ActionView();
		}else{
			$this->view = $view;
		}
		$this->view->object = $this->object;
	}
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
	function create(){
		$this->view->object = $this->object;
		return $this->view->render("create");
	}
	function edit(){
		$this->view->object = $this->object;
		return $this->view->render("edit");
	}
	function show(){
		$this->view->object = $this->object;
		return $this->view->render("show");
	}
}
?>