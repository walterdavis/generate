<?php
/**
 * MyActionController
 *
 * A companion Controller class for MyActiveRecord. Together with MyActionView, forms MyActionPack.
 *
 * License
 * 
 * Copyright (c) 2010, Walter Lee Davis <waltd@wdstudio.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 *	-	Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 *	-	Redistributions in binary form must reproduce the above copyright 
 *		notice, this list of conditions and the following disclaimer in the 
 *		documentation and/or other materials provided with the distribution.
 *
 *	-	Neither the name of MyActionController nor the names of its contributors 
 *		may be used to endorse or promote products derived from this 
 *		software without specific prior written permission.
 *
 *	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS 
 *	IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 *	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
 *	PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR 
 *	CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 *	EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
 *	PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 *	PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 *	LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
 *	NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
 *	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
class MyActionController{
	var $object;
	function MyActionController(&$object=null,&$view=null){
		if(is_object($object)){
			$this->object = $object;
		}
		if(!$view){
			$this->view = new ActionView();
		}else{
			$this->view = $view;
		}
		$this->view->object = $this->object;
	}
	function is_ajax(){
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
			($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
	/**
	 * Generate 302 redirect to a different URL. Uses url_for to disambiguate URLs
	 *
	 * @param string $strNextAction Can be an action on the current model, a root-relative URL or a fully-qualified URI.
	 * @return header 302
	 * @author Walter Lee Davis
	 */
	function redirect_to($strNextAction){
		header("Location: " . MyActionView::url_for($strNextAction,$this->object, false));
		exit;
	}
	/**
	 * handle a form submission, reload on errors, redirect on success
	 *
	 * @param string $strNextAction The url of the next page on success. 
	 *     Uses redirect_to, which uses url_for, so see that for syntax.
	 * @param string $strSuccessMessage The flash message on success
	 * @return mixed
	 * @author Walter Lee Davis
	 */
	function manage_result($strNextAction, $strSuccessMessage){
		//copy nested objects' errors into the object
		foreach(get_object_vars($this->object) as $k => $v){
			if(is_object($v) && ($errors = $v->get_errors())){
				$prefix = strtolower(get_class($v)) . '_';
				foreach($errors as $key => $val){
					$this->object->add_error($prefix . $key, $val);
				}
			}
		}
		if(!$this->object->get_errors()){
			$_SESSION["flash"] = flash($strSuccessMessage);
			$this->redirect_to($strNextAction);
		}else{
			$GLOBALS["flash"] = flash($this->object->get_errors(),"error");
		}
	}
	function create(){
		return render("create", $this->object);
	}
	function edit(){
		return render("edit", $this->object);
	}
	function show(){
		return render("show", $this->object);
	}
}
?>