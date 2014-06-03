<?php
class PluginObject {
// version 1.1.1
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
//=======================================
	function PluginObject() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
//======================================= 
	function runOperationPlugin($operationAry,$base){
		$base->DebugObj->printDebug("PluginObj:runOperationPlugin($operationAry,'base')",0);
		//echo "xxxf";
		//xxxd else above
		$operationName=$operationAry['operationname'];
		switch ($operationName){
			case 'runcgi':
				$pluginName=$base->paramsAry['operation'];
				if ($pluginName == ""){$pluginName='none';}
				$base->FileObj->writeLog('jefftest66',"run operation pluginname: $pluginName",&$base);
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
			//echo "xxxf2";
			$PluginObject=$base->pluginProfileAry['operation'][$pluginName]['pluginobject'];
			//objects have to have capitalized first letter
			$firstLetter=substr($PluginObject,0,1);
			$firstLetterNo=ord($firstLetter);
			if ($firstLetterNo>=97 && $firstLetterNo<=122){
				$firstLetterNo=$firstLetterNo-32;
				$firstLetter=chr($firstLetterNo);
				$objectLength=strlen($PluginObject);
				$objectLength--;
				$PluginObjectSuffix=substr($PluginObject,1,$objectLength);
				$PluginObject=$firstLetter.$PluginObjectSuffix;
			}
			
			$pluginMethod=$base->pluginProfileAry['operation'][$pluginName]['pluginmethod'];
			if (($PluginObject != "") && ($pluginMethod != "")){
				//echo "xxxf3";
				//echo "pl: run $PluginObject, $pluginMethod<br>";//xxxd
//- setup useotherdb
				$useOtherDb=$base->UtlObj->returnFormattedData($base->jobProfileAry['jobuseotherdb'],'boolean','internal',&$base);
				$test=null;
				foreach ($base->jobProfileAry as $name=>$value){
					$test.=", $name: $value";
				}
				$base->UtlObj->appendValue('debug',"PluginObj,runOperationPlugin: jobprofileary: $test<br>",&$base);//xxxd
				if ($useOtherDb){$base->DbObj->setUseOtherDbNoReset(&$base);}
				$base->UtlObj->appendValue('debug',"PluginObj,runOperationPlugin: run $PluginObject, $pluginMethod useotherdb: $useOtherDb<br>",&$base);
				$base->FileObj->writeLog('writedbfromajaxsimple',"PluginObj) run $PluginObject($pluginMethod)",&$base);
				
				if ($hasReturn){
				
					$returnAry=$base->$PluginObject->$pluginMethod(&$base);
				
					return $returnAry;
				}
				else {
					//echo "xxxd: run $PluginObject, $pluginMethod<br>";
					//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd');
					$base->$PluginObject->$pluginMethod(&$base);
				}
				if ($useOtherDb){$base->DbObj->unsetUseOtherDb(&$base);}
			}
			else {
				$base->DebugObj->printDebug("Error: plugintype: $pluginType, pluginname: $pluginName, pluginclassname: $PluginObject,pluginmethodname: $pluginMethod",1);
				$base->DebugObj->displayStack();
			}
		}
		$base->DebugObj->printDebug("-rtn:runOperationPlugin",0); //xx (f)
	}		
//=======================================
	function runTagPlugin($pluginName,$paramFeed,$base){
		$base->DebugObj->printDebug("PluginObj:runPlugin($pluginName,$pluginType,'base')",0);
			//echo "pluginname: $pluginName<br>";
			//$base->DebugObj->printDebug($paramFeed,1,'xxx');
			$pluginType='tag';
			$PluginObject=$base->pluginProfileAry[$pluginType][$pluginName]['pluginobject'];
			$firstLetter=substr($PluginObject,0,1);

			$firstLetterNo=ord($firstLetter);
			if ($firstLetterNo>=97 && $firstLetterNo<=122){
				$firstLetterNo=$firstLetterNo-32;
				$firstLetter=chr($firstLetterNo);
				$objectLength=strlen($PluginObject);
				$objectLength--;
				$PluginObjectSuffix=substr($PluginObject,1,$objectLength);
				$PluginObject=$firstLetter.$PluginObjectSuffix;
			}

			$pluginMethod=$base->pluginProfileAry[$pluginType][$pluginName]['pluginmethod'];
			//print "obj: $PluginObject, method: $pluginMethod<br>";
			if (($PluginObject != "") && ($pluginMethod != "")){
				$returnAry=$base->$PluginObject->$pluginMethod($paramFeed,&$base);
			}
			else {
				$base->DebugObj->printDebug("Error: pluginname: $pluginName, plugintype: $pluginType,  pluginclassname: $PluginObject,pluginmethodname: $pluginMethod",1);
				$base->DebugObj->displayStack();
				$returnAry=array();
			}
			$base->DebugObj->printDebug("-rtn:runTabPlugin",0); //xx (f)
			return $returnAry;
	}
//=======================================
	function runAppPlugin($operAry,$applicationPassedAry,$base){
		$base->DebugObj->printDebug("PluginObj:runAppPlugin($operAry,'base')",0);
			$pluginType='app';
			$pluginName=$operAry['applicationpluginname'];
			$pluginArgs=$base->applicationProfileAry[$pluginName]['applicationpluginarguments'];
			//$base->DebugObj->printDebug($operAry,1,'operary');
			$PluginObject=$base->pluginProfileAry[$pluginType][$pluginName]['pluginobject'];
			$pluginMethod=$base->pluginProfileAry[$pluginType][$pluginName]['pluginmethod'];
			if (($PluginObject != "") && ($pluginMethod != "")){
				$applicationPassedAry['pluginargs']=$pluginArgs;
				//echo "obj: $PluginObject, method: $pluginMethod<br>";//xxx
				$applicationPassedAry=$base->$PluginObject->$pluginMethod($applicationPassedAry,&$base);
			}
			else {
				$base->DebugObj->printDebug("Error: pluginname: $pluginName, plugintype: $pluginType, pluginname: $pluginName, PluginObject: $PluginObject, pluginmethod: $pluginMethod",1);
				$base->DebugObj->displayStack();
				$applicationPassedAry=array();
			}
			$base->DebugObj->printDebug("-rtn:runTabPlugin",0); //xx (f)
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
