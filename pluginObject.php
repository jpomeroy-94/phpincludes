<?php
class pluginObject {
// version 1.1.1
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
//=======================================
	function pluginObject() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
//======================================= 
	function runOperationPlugin($operationAry,$base){
		$base->debugObj->printDebug("pluginObj:runOperationPlugin($operationAry,'base')",0);
		//xxxd else above
		$operationName=$operationAry['operationname'];
		switch ($operationName){
			case 'runcgi':
				$pluginName=$base->paramsAry['operation'];
				if ($pluginName == ""){$pluginName='none';}
				$base->fileObj->writeLog('jefftest66',"run operation pluginname: $pluginName",&$base);
				$hasReturn=false;
			break;
			case 'runoperation':
				$pluginName=$operationAry['pluginname'];
				if ($pluginName == ''){$pluginName='error';}
				$hasReturn=false;
			break;
			case 'runspecial':
				$pluginName=$operationAry['pluginname'];
				if ($pluginName==NULL){$pluginName='error';}
				$hasReturn=true;
			break; 
			default:
				$pluginName='error';
		}
		if ($pluginName != "none"){
			$pluginObject=$base->pluginProfileAry['operation'][$pluginName]['pluginobject'];
			$pluginMethod=$base->pluginProfileAry['operation'][$pluginName]['pluginmethod'];
			if (($pluginObject != "") && ($pluginMethod != "")){
				//echo "pl: run $pluginObject, $pluginMethod<br>";//xxxd
//- setup useotherdb
				$useOtherDb=$base->utlObj->returnFormattedData($base->jobProfileAry['jobuseotherdb'],'boolean','internal',&$base);
				$test=null;
				foreach ($base->jobProfileAry as $name=>$value){
					$test.=", $name: $value";
				}
				$base->utlObj->appendValue('debug',"pluginObj,runOperationPlugin: jobprofileary: $test<br>",&$base);//xxxd
				if ($useOtherDb){$base->dbObj->setUseOtherDbNoReset(&$base);}
				$base->utlObj->appendValue('debug',"pluginObj,runOperationPlugin: run $pluginObject, $pluginMethod useotherdb: $useOtherDb<br>",&$base);
				$base->fileObj->writeLog('writedbfromajaxsimple',"pluginObj) run $pluginObject($pluginMethod)",&$base);
				if ($hasReturn){
					$returnAry=$base->$pluginObject->$pluginMethod(&$base);
					return $returnAry;
				}
				else {
					//echo "xxxd: run $pluginObject, $pluginMethod<br>";
					//$base->debugObj->printDebug($base->paramsAry,1,'xxxd');
					$base->$pluginObject->$pluginMethod(&$base);
				}
				if ($useOtherDb){$base->dbObj->unsetUseOtherDb(&$base);}
			}
			else {
				$base->debugObj->printDebug("Error: plugintype: $pluginType, pluginname: $pluginName, pluginclassname: $pluginObject,pluginmethodname: $pluginMethod",1);
				$base->debugObj->displayStack();
			}
		}
		$base->debugObj->printDebug("-rtn:runOperationPlugin",0); //xx (f)
	}		
//=======================================
	function runTagPlugin($pluginName,$paramFeed,$base){
		$base->debugObj->printDebug("pluginObj:runPlugin($pluginName,$pluginType,'base')",0);
			//echo "pluginname: $pluginName<br>";
			//$base->debugObj->printDebug($paramFeed,1,'xxx');
			$pluginType='tag';
			$pluginObject=$base->pluginProfileAry[$pluginType][$pluginName]['pluginobject'];
			$pluginMethod=$base->pluginProfileAry[$pluginType][$pluginName]['pluginmethod'];
			//print "obj: $pluginObject, method: $pluginMethod<br>";
			if (($pluginObject != "") && ($pluginMethod != "")){
				$returnAry=$base->$pluginObject->$pluginMethod($paramFeed,&$base);
			}
			else {
				$base->debugObj->printDebug("Error: pluginname: $pluginName, plugintype: $pluginType,  pluginclassname: $pluginObject,pluginmethodname: $pluginMethod",1);
				$base->debugObj->displayStack();
				$returnAry=array();
			}
			$base->debugObj->printDebug("-rtn:runTabPlugin",0); //xx (f)
			return $returnAry;
	}
//=======================================
	function runAppPlugin($operAry,$applicationPassedAry,$base){
		$base->debugObj->printDebug("pluginObj:runAppPlugin($operAry,'base')",0);
			$pluginType='app';
			$pluginName=$operAry['applicationpluginname'];
			$pluginArgs=$base->applicationProfileAry[$pluginName]['applicationpluginarguments'];
			//$base->debugObj->printDebug($operAry,1,'operary');
			$pluginObject=$base->pluginProfileAry[$pluginType][$pluginName]['pluginobject'];
			$pluginMethod=$base->pluginProfileAry[$pluginType][$pluginName]['pluginmethod'];
			if (($pluginObject != "") && ($pluginMethod != "")){
				$applicationPassedAry['pluginargs']=$pluginArgs;
				//echo "obj: $pluginObject, method: $pluginMethod<br>";//xxx
				$applicationPassedAry=$base->$pluginObject->$pluginMethod($applicationPassedAry,&$base);
			}
			else {
				$base->debugObj->printDebug("Error: pluginname: $pluginName, plugintype: $pluginType, pluginname: $pluginName, pluginObject: $pluginObject, pluginmethod: $pluginMethod",1);
				$base->debugObj->displayStack();
				$applicationPassedAry=array();
			}
			$base->debugObj->printDebug("-rtn:runTabPlugin",0); //xx (f)
			return $applicationPassedAry;
	}
//=======================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//=======================================
	function incCalls(){$this->callNo++;}
}
?>
