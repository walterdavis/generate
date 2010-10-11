<?php 
class Titles extends ActiveRecord{
	function save(){
		$this->validate_existence('name');
		$this->validate_existence('description');
		return parent::save();
	}
	function destroy(){
		return parent::destroy();
	}
}
?>