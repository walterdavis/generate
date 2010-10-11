<?php
class DemoController extends ActionController{
	function create(){
		$this->object = ActiveRecord::Create("Demo");
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "Object saved successfully");
		}
		return ActionView::Show("create",$this->object);
	}
	function delete($id){
		$this->object = ActiveRecord::FindById("Demo",$id);
		$this->object->destroy();
		$this->manage_result("index", "Object deleted");
	}
	function edit($id){
		$this->object = ActiveRecord::FindById("Demo",$id);
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "Object updated successfully");
		}
		return ActionView::Show("edit",$this->object);
	}
	function show($id){
		$object = ActiveRecord::FindById("Demo",$id);
		return ActionView::Show("show",$object);
	}
	function index(){
		$objects = ActiveRecord::FindAll("Demo");
		return ActionView::Show("index", $objects, ActiveRecord::Create("Demo"));
	}
}
?>