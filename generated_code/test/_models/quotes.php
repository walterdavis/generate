<?php 
class Quotes extends ActiveRecord{
	function save(){
		$this->validate_existence('title');
		$this->validate_existence('headline');
		$this->validate_existence('quotation');
		$this->validate_existence('url');
		$this->validate_uniqueness_of('url');
		if($this->id < 1) $this->added_at = $this->DbDateTime();
		return parent::save();
	}
}
?>