<?php
class DefaultController extends ActionController{
	function index(){
		return ActionView::Show("layouts/default");
	}
}
?>