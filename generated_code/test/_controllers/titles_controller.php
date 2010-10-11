<?php
class TitlesController extends ActionController{
	function create(){
		$this->object = ActiveRecord::Create("Titles");
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "Object saved successfully");
		}
		return ActionView::Show("create",$this->object);
	}
	function delete($id){
		$this->object = ActiveRecord::FindById("Titles",$id);
		$this->object->destroy();
		$this->manage_result("index", "Object deleted");
	}
	function edit($id){
		$this->object = ActiveRecord::FindById("Titles",$id);
		if(isset($_POST["save"])){
			$this->object->populate($_POST);
			$this->object->save();
			$this->manage_result("edit", "Object updated successfully");
		}
		return ActionView::Show("edit",$this->object);
	}
	function show($id){
		$object = ActiveRecord::FindById("Titles",$id);
		return ActionView::Show("show",$object);
	}
	function index(){
		$objects = ActiveRecord::FindAll("Titles");
		return ActionView::Show("index", $objects, ActiveRecord::Create("Titles"));
	}
}
?>