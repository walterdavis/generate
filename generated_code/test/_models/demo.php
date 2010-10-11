<?php 
class Demo extends ActiveRecord{
	function save(){
		$this->validate_existence('name');
		$this->validate_existence('rank');
		$this->validate_existence('serial_number');
		$this->validate_uniqueness_of('serial_number');
		$this->updated_on = $this->DbDate();
		if($this->id < 1) $this->added_at = $this->DbDateTime();
		return parent::save();
	}
}
?>