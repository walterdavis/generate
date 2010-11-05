<?php
class %sController extends ActionController{
	function create(){
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "%s saved successfully");
		}
		return parent::create();
	}
	function delete($id){
		$this->object = ActiveRecord::FindById("%s",$id);
		$this->object->destroy();
		$this->manage_result("index", "%s deleted");
	}
	function edit($id){
		$this->object = ActiveRecord::FindById("%s",$id);
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "%s updated successfully");
		}
		return parent::edit();
	}
	function show($id){
		$this->object = ActiveRecord::FindById("%s",$id);
		return parent::show();
	}
	function index(){
		$objects = ActiveRecord::FindAll("%s");
		return render("index", $objects, ActiveRecord::Create("%s"));
	}
}
?>