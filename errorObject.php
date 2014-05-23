<?php
class ErrorObject {
	var $statusMsg;
	var $callNo = 0;
	var $errorAry = array();
	var $keyConvAry = array();
//---
	function ErrorObject() {
		$this->incCalls();
		$this->statusMsg='file Object is fired up and ready for work!';
	}
	function incCalls(){
		$this->callNo++;
	}
	function saveError($errorName,$errorMsg,$base){
		//echo "save errobj: $errorName, $errorMsg<br>";//xxxd
		$this->errorAry[$errorName]=$errorMsg;	
	}
	function retrieveError($errorName,$base){
		$errorMsg=$this->errorAry[$errorName];
		//echo "get errobj: $errorName<br>";//xxxd
		return $errorMsg;	
	}
	function retrieveAllErrors($base){
		$returnStrg=null;
		$theComma=null;
		foreach ($this->errorAry as $name=>$msg){
			$returnStrg.="$theComma$name: $msg";
			$theComma=", ";	
		}
		return $returnStrg;
	}
	function printAllErrors($base){
		$errorStrg=$this->retrieveAllErrors(&$base);
		echo "errors: $errorStrg<br>";	
	}
	function saveKeyConv($tempKeyId,$realKeyId,$base){
		$this->keyConvAry[$tempKeyId]=$realKeyId;	
	}
	function getKeyConv($base){
		return $this->keyConvAry;
	}
}
