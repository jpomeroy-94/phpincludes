<?php
class initObject{
//=======================================
	function initObject(){
	}
//======================================= will be deprecated 
	function getJobProfileDeprecated($base){
		$base->debugObj->printDebug("dbObj:getJobProfile('base')",-2);
		$returnAry=array();
		$job=$base->paramsAry['job'];
		$query="select * from jobprofileview where jobname='$job'";
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array();
		$returnAry=$base->utlObj->tableRowToHashAry($result,$passAry);
		$jobProfileId=$returnAry['jobprofileid'];
		$query="select * from jobxrefview where jobprofileid=$jobProfileId";
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$workAry=$base->utlObj->tableRowToHashAry($result,$passAry);
		//$base->debugObj->printDebug($workAry,1,'wary');//xxxx
		$jobParentId=$workAry['jobparentid'];
//--- setup for only 1 parent - has to be changed !!!
		if ($jobParentId != NULL){
			$returnAry['jobparentid']=$jobParentId;
			$query="select * from jobprofileview where jobprofileid=$jobParentId";
			//echo "query: $query<br>";
			$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
			$workAry=$base->utlObj->tableRowToHashAry($result,$passAry);
			//$base->debugObj->printDebug($workAry,1,'wary');//xxxx
			$returnAry['jobparentid']=$workAry['jobprofileid'];
		}
		$base->debugObj->printDebug("-rtn:dbObj:getDbTableProfile",-2); //xx (f)
		return $returnAry;
	}
//======================================= 
	function getJobProfile($base){
		$base->debugObj->printDebug("dbObj:getJobProfile('base')",-2);
		$returnAry=array();
		$job=$base->paramsAry['job'];
		//now if it is emply, inituser is run within userobj
		$selectCompanyAry=$_SESSION['userobj']->getCompanySelects(&$base);
		$query="select * from jobprofileview where jobname='$job' ";
		//echo "query: $query<br>";//xxxf
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array('delimit1'=>'companyprofileid');
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$jobProfileId=$workAry[$job];
		//- the below is temporarily disabled
		$retrievedJobProfile=false;
		for ($ctr=0;$ctr<20;$ctr++){
			$companyProfileId=$selectCompanyAry[$ctr];
			if ($companyProfileId != NULL){
				//echo "check $companyProfileId if in workary<br>";//xxxf
				if (array_key_exists($companyProfileId,$workAry)){
					//echo 'itisthere<br>';//xxxf
					$returnAry=$workAry[$companyProfileId];
					$retrievedJobProfile=true;
					$ctr=20;
				}				
			}
		}
		//echo "retrievedjobprofile: $retrievedJobProfile<br>";//xxx
		//xxx - need to finish this
		if (!$retrievedJobProfile) {
			if ($_SESSION['userobj']->isAccessAll(&$base)){
				$dmyAry=array_keys($workAry);
				$useCompanyProfileId=$dmyAry[0];
				$returnAry=$workAry[$useCompanyProfileId];
			}
		}
		//$base->debugObj->printDebug($workAry,1,'workary');//xxx
		//$base->debugObj->printDebug($returnAry,1,'returnary');//xxx
		$jobProfileId=$returnAry['jobprofileid'];
		if ($jobProfileId != NULL){
			$query="select * from jobxrefview where jobprofileid=$jobProfileId";
			$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
			$workAry=$base->utlObj->tableRowToHashAry($result,$passAry);
			//$base->debugObj->printDebug($workAry,1,'wary');//xxxx
			$jobParentId=$workAry['jobparentid'];
//--- setup for only 1 parent - has to be changed !!!
			if ($jobParentId != NULL){
				$returnAry['jobparentid']=$jobParentId;
				$query="select * from jobprofileview where jobprofileid=$jobParentId";
				//echo "query: $query<br>";
				$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
				$workAry=$base->utlObj->tableRowToHashAry($result,$passAry);
				//$base->debugObj->printDebug($workAry,1,'wary');//xxxx
				$returnAry['jobparentid']=$workAry['jobprofileid'];
			}
			$base->debugObj->printDebug("-rtn:dbObj:getDbTableProfile",-2); //xx (f)
		}
		else {
			$_SESSION['userobj']->displayUserSetups(&$base);
			exit('invalid job: '.$job);	
		}
		return $returnAry;
	}
//=======================================  
	function getDbTableProfile($base){
		$base->debugObj->printDebug("dbObj:getDbTableProfile('base')",-2);
//xx needs to be rethought
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from dbtableprofileview where jobprofileid=$jobProfileId";
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array('delimit1'=>'dbtablename');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$base->debugObj->printDebug("-rtn:dbObj:getDbTableProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================
	function getOperationProfile($base){
		$base->debugObj->printDebug("dbObj:getOperationProfile('base')",-2); //xx (h)
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from joboperationxrefview where jobprofileid=$jobProfileId order by joboperationno";
		//echo "$query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array('delimit1'=>'joboperationno');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		if (count($returnAry) == 0){
			$query="select * from joboperationxrefview where jobname='default' order by joboperationno";
			$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
			$passAry=array('delimit1'=>'joboperationno');
			$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		}
		$base->debugObj->printDebug("-rtn:getOperationProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================complex 
	function getHtmlProfile($base){
		$base->debugObj->printDebug("dbObj:getHtmlProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from htmlprofileview where jobprofileid=$jobProfileId";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error2";}
		else {$workAry=$base->utlObj->tableToHashAryV3($result);}
		$begFlg=true;
		foreach ($workAry as $ctr=>$valueAry){
			$htmlName=$valueAry['htmlname'];
			if ($htmlName == ""){$htmlName='none';}
			else {$returnAry['htmlname'][]=$htmlName;}
			$returnAry[$htmlName]=$valueAry;
			if ($begFlg) {
				$returnAry['default']=$valueAry;
				$begFlg=false;
			}
		}
//if operationprofileary has htmlname as default then it is assumed
// that there is only one htmlprofile row and it will replace default
		foreach ($base->operationProfileAry as $ctr=>$valueAry){
			$operationName=$valueAry['operationname'];
			$operationHtmlName=$valueAry['htmlname'];
			if ($operationName == 'processhtml' && $operationHtmlName == 'default'){
				$base->operationProfileAry[$ctr]['htmlname']=$htmlName;
			}
		}
		$base->debugObj->printDebug("-rtn:dbObj:getHtmlProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================complex
	function getHtmlElementProfile($base){
		$base->debugObj->printDebug("dbObj:getHtmlElementProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){
			$parentInsert=" or jobprofileid=$jobParentId";
		}
		else {$parentInsert=NULL;}
// - get job htmlelements ... can bypass htmlname - only one per job
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'htmlelementname');
		$query="select * from htmlelementprofileview where jobprofileid=$jobProfileId $parentInsert";
		//echo "query: $query<br>";//xxx
		$result = $base->dbObj->queryTable($query,'select',$base,-2);
		if (!$result) {$workAry=array();}
		else {$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);}
// - merge them
		$returnAry=$workAry[$jobProfileId];
		if (!is_array($returnAry)){$returnAry=array();}
		if ($jobParentId != NULL){
		if (array_key_exists($jobParentId,$workAry)){
		if (count($workAry[$jobParentId])>0){
			foreach ($workAry[$jobParentId] as $htmlElementName=>$htmlElementAry){
				//echo "htmlname: $htmlElementName, rtnary: $returnAry<br>";//xxx
				if (!array_key_exists($htmlElementName,$returnAry)){
					$returnAry[$htmlElementName]=$htmlElementAry;
				}
			}
			//exit();//xxx
		}
		}
		}
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:dbObj:getHtmlElementProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================simple
	function getSystemProfile($base){
		$base->debugObj->printDebug("dbObj:getSystemProfile('base')",-2);
		$returnAry=array();
		$query="select * from systemprofile where domainname='home'";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error2";}
		else {$returnAry=$base->utlObj->tableRowToHashAry($result);}
		$base->debugObj->printDebug("-rtn:dbObj:getDbTableMetaInfo",-2); //xx (f)
		return $returnAry;
	}
//=======================================other 
	function getTableProfile($base){
		$base->debugObj->printDebug("dbObj:getTableProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$parentNameInsert=" or jobprofileid=$jobParentId";}
		else {$parentNameInsert=NULL;}
		$query="select * from tableprofileview where jobprofileid=$jobProfileId $parentNameInsert order by tablename";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'tablename');
		$getItAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry=$getItAry[$jobProfileId];
		if (!is_array($returnAry)){$returnAry=array();}
		if ($jobParentId != NULL){
		if (array_key_exists($jobParentId,$getItAry)){
			foreach ($getItAry[$jobParentId] as $tableName=>$tableAry){
				if (!array_key_exists($tableName,$returnAry)){
					$returnAry[$tableName]=$tableAry;
				}	
			}
		}
		}
		//$base->debugObj->printDebug($returnAry,1,'xxx');
		$base->debugObj->printDebug("-rtn:dbObj:getTableProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================other 
	function getRowProfile($base){
		$base->debugObj->printDebug("dbObj:getRowProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from rowProfileview where jobprofileid=$jobProfileId";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'tablename');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$base->debugObj->printDebug("-rtn:dbObj:getRowProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================other 
	function getColumnProfile($base){
		$base->debugObj->printDebug("dbObj:getColumnProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$parentNameInsert=" or jobprofileid=$jobParentId";}
		else {$parentNameInsert=NULL;}
		$query="select * from columnprofileview where jobprofileid=$jobProfileId $parentNameInsert order by tablename, columnname";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		if ($jobParentId == NULL){
			$passAry=array('delimit1'=>'tablename','delimit2'=>'columnname','order1'=>'columnno');
			$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		}
		else {
			$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'tablename','delimit3'=>'columnname','order1'=>'columnno');
			$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
			//$base->debugObj->printDebug($workAry,1,'work');//xxx
// job tables
			$returnAry=$workAry[$jobProfileId];
			if (!is_array($returnAry)){$returnAry=array();}
// parent tables
			if ($jobParentId != NULL){
				$cnt=count($workAry[$jobParentId]);
				if ($cnt>0){
					foreach ($workAry[$jobParentId] as $tableName=>$tableAry){
					if (!array_key_exists($tableName,$returnAry)){
						$returnAry[$tableName]=$tableAry;	
					}
					}
				}	
			}
		}
		//$base->debugObj->printDebug($workAry,1,'work');
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:dbObj:getColumnProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================other 
	function getFormProfile($base){
		$base->debugObj->printDebug("dbObj:getFormProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		$query="select * from formprofileview where jobprofileid=$jobProfileId $parentInsert order by formname";
		//echo "xxxf1: query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'formname');
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		//$base->debugObj->printDebug($workAry,1,'work');//xxx
		$cnt=count($workAry[$jobProfileId]);
		if ($cnt>0){
			foreach ($workAry[$jobProfileId] as $formName=>$formAry){
				$returnAry[$formName]=$formAry;
			}
		}
		if ($jobParentId != NULL){
			$cnt=count($workAry[$jobParentId]);
			if ($cnt>0){
				foreach ($workAry[$jobParentId] as $formName=>$formAry){
					if (!array_key_exists($formName,$returnAry)){
						$returnAry[$formName]=$formAry;	
					}	
				}
			}	
		}
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:dbObj:getFormProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================other inheritance overlay
	function getObjects($base){
		$base->debugObj->printDebug("dbObj:getObjects('base')",-2);
		$query="select * from objectprofileview where jobname='$job' $parentInsert";
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$workAry=$base->utlObj->tableToHashAryV3($result);
		$returnAry=array();
		foreach ($workAry as $key=>$valueAry){
			$objectName=$valueAry['objectname'];
			$objectPath=$valueAry['objectpath'];
			$fullPath="$objectPath/$objectName.php";
			require_once($fullPath);
			$returnObj=new $objectName;
			$returnAry[$objectName]=$returnObj;
		}
		$base->debugObj->printDebug("-rtn:initObj:getOjects",-2); //xx (f)
		return $returnAry;
	}
//=======================================other inheritance overlay
	function getFormDataProfile($base){
		$base->debugObj->printDebug("dbObj:getFormDataProfile('base')",-2);
		$returnAry=array();
		$job=$base->paramsAry['job'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		//xxx: need to use jobprofileid
		$query="select * from formdataprofileview where jobname='$job' $parentInsert order by formname, formdatatype, formdatano";
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$workAry=$base->utlObj->tableToHashAryV3($result);
		foreach ($workAry as $key=>$valueAry){
			$formName=$valueAry['formname'];
			$formDataType=$valueAry['formdatatype'];
			if ($formName == ""){$formName='none';}
			if ($formDataType == ''){$formDataType='none';}
			$returnAry[$formName][$formDataType][]=$valueAry;
		}
		$base->debugObj->printDebug("-rtn:dbObj:getFormProfile",-2); //xx (f)
		return $returnAry;
	}
//=======================================other  
	function getFormElementProfile($base){
		$base->debugObj->printDebug("dbObj:getFormElementProfile('base')",-2);
		$returnAry=array();
		$jobName=$base->paramsAry['job'];
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		$query="select * from formelementprofileview where jobprofileid=$jobProfileId $parentInsert order by formname";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'formname','delimit3'=>'formelementname','order1'=>'formelementno');
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		if (array_key_exists($jobProfileId,$workAry)){
			$returnAry=$workAry[$jobProfileId];
		}
//- merge in parent fields, but do not overlay!
		if ($jobParentId != NULL){
			if (array_key_exists($jobParentId,$workAry)){
				$parentReturnAry=$workAry[$jobParentId];
				foreach ($parentReturnAry as $parentFormName=>$parentFormElementsAry){
					if (!array_key_exists($parentFormName,$returnAry)){$returnAry[$parentFormName]=$parentFormElementsAry;}
				}
			}
		}
//- build sort ary
		$cnt=count($returnAry);
		if ($cnt>0){
		foreach ($returnAry as $key=>$valueAry){
			$pos=strpos('x'.$key,'sortorderary');
			//echo "key: $key, pos: $pos<br>";//xxx
			if ($pos>0){
				$formNameLength=strlen($key)-13;
				$formName=substr($key,13,$formNameLength);
				//echo "formname: $formName, key: $key, keylength,$keyLength<br>";//xxx
				$base->formProfileAry['element_order'][$formName]=$valueAry;
				//$base->debugObj->printDebug($base->formProfileAry,1,'formpa');//xxx
			}
		}
		}
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:dbObj:getFormElementProfile",-2); //xx (f)
		return $returnAry;
	}
//===============================================
function getPluginProfile($base){
    $base->debugObj->printDebug("dbObj:getPluginProfile('base')",-2);
    $returnAry=array();
    $workAry=array();
		$newAry=array();
    $jobSt = $base->jobSt;
 //- operation
    $query="select * from pluginprofile where plugintype='operation'";
	$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
    if (!$result) {echo "Error8";}
    else {$workAry=$base->utlObj->tableToHashAryV3($result);}
	$cnt=count($workAry);
	for ($lp=0;$lp<$cnt;$lp++){
		$newAry=$workAry[$lp];
    $returnAry['operation'][$newAry['pluginname']]=$newAry;
	}
//- tag
    $query="select * from pluginprofile where plugintype='tag'";
	$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
    if (!$result) {echo "Error9";}
    else {$workAry=$base->utlObj->tableToHashAryV3($result);}
	$cnt=count($workAry);
	for ($lp=0;$lp<$cnt;$lp++){
		$newAry=$workAry[$lp];
    $returnAry['tag'][$newAry['pluginname']]=$newAry;
	}
//- app
	$query="select * from pluginprofile where plugintype='app'";
	$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
    if (!$result) {echo "Error13";}
    else {$workAry=$base->utlObj->tableToHashAryV3($result);}
	$cnt=count($workAry);
	for ($lp=0;$lp<$cnt;$lp++){
		$newAry=$workAry[$lp];
    $returnAry['app'][$newAry['pluginname']]=$newAry;
	}
	$base->debugObj->printDebug("-rtn:dbObj:getPluginProfile",-2); //xx (f)
    return $returnAry;
  }
//==========================================  overlays record!!!
	function getMenus($base){
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$parentNameInsert=" or jobprofileid=$jobParentId";}
		else {$parentIdInsert=NULL;}
		$query="select * from menuprofileview where jobprofileid=$jobProfileId $parentNameInsert";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
    	if (!$result) {echo "Error14";}
    	else {
    		$passAry=array('delimit1'=>'menuname');
			$menuProfileAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		}
		$query="select * from menuelementprofileview where jobprofileid=$jobProfileId $parentNameInsert order by menuname,menuelementparentid,menuelementno";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
    	if (!$result) {echo "Error15";}
    	else {
    		$passAry=array('delimit1'=>'menuname','delimit2'=>'menuelementprofileid');
			$menuElementProfileAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		}
		$query="select * from albumprofileview where jobprofileid=$jobProfileId order by albumname";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		if (!result){$albumAry=array();}
		else {
			$passAry=array('delimit1'=>albumprofileid);
			$albumAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		}
		//$base->debugObj->printDebug($albumAry,1,'alb');//xxx
		$sortOrder=array();
		foreach ($menuElementProfileAry as $menuName=>$menuElementAry){
			$menuType=$menuProfileAry[$menuName]['menutype'];
			if ($menuType == 'horizontaldropdown'){$dropDown=true;}
			else {$dropDown=false;}
			foreach ($menuElementAry as $menuElementProfileId=>$elementAry){
				$menuElementName=$elementAry['menuelementname'];
				$menuElementParentId=$elementAry['menuelementparentid'];
				$menuElementNo=$elementAry['menuelementno'];
				switch ($dropDown){
				case true:
					if ($menuElementParentId == NULL){
						$thisIsAParent=true;
						$parentMenuElementNo=$menuElementNo;
						$menuElementNo=0;
					}
					else {
						$thisIsAParent=false;
						$parentMenuElementNo=$menuElementAry[$menuElementParentId]['menuelementno'];
					}
					$sortOrder[$menuName][$parentMenuElementNo][$menuElementNo]=$menuElementProfileId;
					break;
				case false:
					$sortOrder[$menuName][$menuElementNo]=$menuElementProfileId;			
					break;
				}
				$albumProfileId=$elementAry['albumprofileid'];
				//echo "menuname: $menuName, albumprofileid: $albumProfileId<br>";//xxxd
				if ($albumProfileId>0){$albumName=$albumAry[$albumProfileId]['albumname'];}
				else {$albumName=NULL;}
				$menuElementProfileAry[$menuName][$menuElementProfileId]['albumname']=$albumName;
			}
		}
		$menuProfileAry['jsmenusary']=array();
		$menuProfileAry['sortorder']=$sortOrder;
		//$base->debugObj->printDebug($menuProfileAry,1,'xxxf');
		//exit();
		$returnAry=array('menuprofileary'=>$menuProfileAry,'menuelementprofileary'=>$menuElementProfileAry);
		return $returnAry;
	}
	//=======================================other overlays parent record on inheritance
	function getCssProfile($base){
		$base->debugObj->printDebug("dbObj:getCssProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$parentNameInsert=" or jobprofileid=$jobParentId";}
		else {$parentIdInsert=NULL;}
		$query="select * from csselementprofileview where jobprofileid=$jobProfileId $parentNameInsert order by prefix, cssclass, cssid, htmltag";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array();
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry['id']=array();
		$returnAry['class']=array();
		$returnAry['none']=array();
		$returnAry['prefix']=array();
		$returnAry['prefix']['class']=array();
		$returnAry['prefix']['id']=array();
		$returnAry['prefix']['none']=array();
		//$base->debugObj->printDebug($workAry,1,'xxxd: workAry');
		foreach ($workAry as $rowNo=>$cssAry){
//- check if this job or parent setting overlayflag
			$thisJobProfileId=$cssAry['jobprofileid'];
			if ($thisJobProfileId == $jobProfileId){$alwaysOverlay=true;}
			else {$alwaysOverlay=false;}
			//echo "jobname: $jobName<br>";//xxx
			$class=$cssAry['cssclass'];
			$id=$cssAry['cssid'];
			$prefix=$cssAry['prefix'];
			$tag=$cssAry['htmltag'];
			$property=$cssAry['csselementproperty'];
			$value=$cssAry['csselementvalue'];
			$value=str_replace('%sglqt%',"'",$value);
			$cssRepeatNo=$cssAry['cssrepeatno'];
			$cssElementIncrementNo=$cssAry['csselementincrementno'];
			if ($class==NULL){$class='none';}
			if ($id==NULL){$id='none';}
			if ($prefix==NULL){$prefix='none';}
			if ($tag==NULL){$tag='none';}
			if ($class != 'none'){
//--- class
				if ($cssRepeatNo>0){
					for ($lp=0;$lp<$cssRepeatNo;$lp++){
						$useClass=$class.$lp;
						$dontOverlay=false;
						if (!$alwaysOverlay){
							if (array_key_exists($useClass,$returnAry['class'])){
							if (array_key_exists($tag,$returnAry['class'][$useClass])){
							if (array_key_exists($property,$returnAry['class'][$useClass][$tag])){
								$dontOverlay=true;	
							}
							}
							}	
						}
//xxxdd
						if (!$dontOverlay){
							$returnAry['class'][$useClass][$tag][$property]=$value;
							if (!array_key_exists($useClass,$returnAry['prefix']['class'])){	
								$returnAry['prefix']['class'][$useClass]=$prefix;
							}
						}
						if ($cssElementIncrementNo>0){
							$pos=strpos($value,'px');
							if ($pos>0){
								$valueAry=explode('px',$value);
								$value=$valueAry[0];
								$suffix='px';	
							}	
							else {$suffix=NULL;}
							$value+=$cssElementIncrementNo;
							$value.=$suffix;
						} // end if csselementicrementno 
					} // end for lp
				} // end if cssrepeatno
				else {
					$dontOverlay=false;
					if (!$alwaysOverlay){
						if (array_key_exists($class,$returnAry['class'])){
						if (array_key_exists($tag,$returnAry['class'][$class])){
						if (array_key_exists($property,$returnAry['class'][$class][$tag])){
							$dontOverlay=true;	
						}
						}
						}	
					}
					if (!$dontOverlay){
						$returnAry['class'][$class][$tag][$property]=$value;
						if (!array_key_exists($class,$returnAry['prefix']['class'])){	
							$returnAry['prefix']['class'][$class]=$prefix;
						}
					}
				} // end else repeatno
			} // end class!=none
			else {
				if ($id != 'none'){
// --- id
					if ($cssRepeatNo>0){
						for ($lp=0;$lp<$cssRepeatNo;$lp++){
							$useId=$id.$lp;
							$dontOverlay=false;
							if (!$alwaysOverlay){
								if (array_key_exists($useId,$returnAry['id'])){
								if (array_key_exists($tag,$returnAry['id'][$useId])){
								if (array_key_exists($property,$returnAry['id'][$useId][$tag])){
									$dontOverlay=true;	
								}
								}
								}	
							}
							if (!$dontOverlay){
								$returnAry['id'][$useId][$tag][$property]=$value;
								if (!array_key_exists($useId,$returnAry['prefix']['id'])){	
									$returnAry['prefix']['id'][$useId]=$prefix;
								}
							}
							//$returnAry['id'][$useId][$tag][$property]=$value;	
						}
					} // end if cssrepeatno>0
					else {
						$dontOverlay=false;
						if (!$alwaysOverlay){
							if (array_key_exists($id,$returnAry['id'])){
							if (array_key_exists($tag,$returnAry['id'][$id])){
							if (array_key_exists($property,$returnAry['id'][$id][$tag])){
								$dontOverlay=true;	
							}
							}
							}	
						}
						if (!$dontOverlay){
							$returnAry['id'][$id][$tag][$property]=$value;
							if (!array_key_exists($id,$returnAry['prefix']['id'])){	
								$returnAry['prefix']['class'][$id]=$prefix;
							}
						}
						//$returnAry['id'][$id][$tag][$property]=$value;
					} //end else cssrepeatno<0
				} // end if id != 'none'
				else {
					$dontOverlay=false;
					if (!$alwaysOverlay){
						if (array_key_exists('none',$returnAry['none'])){
						if (array_key_exists($tag,$returnAry['none']['none'])){
						if (array_key_exists($property,$returnAry['none']['none'][$tag])){
							$dontOverlay=true;	
						}
						}
						}	
					}
					if (!$dontOverlay){
						$returnAry['none']['none'][$tag][$property]=$value;
						if (!array_key_exists('none',$returnAry['prefix']['none'])){	
								$returnAry['prefix']['none'][$tag]=$prefix;
							}
						
					}
					//$returnAry['none']['none'][$tag][$property]=$value;
				} // end else (id=none)
			} // end else (class=none)
		} // end foreach workary
		$base->debugObj->printDebug("-rtn:dbObj:getCssProfile",-2); //xx (f)
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxxf
//- events
		$query="select * from csseventprofileview where jobprofileid=$jobProfileId $parentNameInsert order by prefix, cssclass, cssid, htmltag";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array();
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry['events']=$workAry;		
		return $returnAry;
	}
	//=======================================  
	function getImageProfile($base){
		$base->debugObj->printDebug("dbObj:getImageProfile('base')",-2);
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		$query="select * from imageprofileview where jobprofileid=$jobProfileId $parentInsert order by imagename";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'imagename');
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry=$workAry[$jobProfileId];
		if (!is_array($returnAry)){$returnAry=array();}
		if (is_array($workAry[$jobParentId])){
			$cnt=count($workAry[$jobParentId]);
			if ($cnt>0){
				foreach ($workAry[$jobParentId] as $imageName=>$imageAry){
					if (!array_key_exists($imageName,$returnAry)){$returnAry[$imageName]=$imageAry;}	
				}	
			}	
		}
		$base->debugObj->printDebug("-rtn:dbObj:getImageProfile",-2); //xx (f)
		return $returnAry;
	}
	//==================================
	function getAlbumProfile($base){
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from albumprofileview where jobprofileid=$jobProfileId order by albumname";
		$result=$base->dbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'albumprofileid');
		$mainAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$query="select * from pictureprofileview where jobprofileid=$jobProfileId and picturetype='active' order by albumname, pictureno";
		$result=$base->dbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'albumname','delimit2'=>'picturename');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry['main']=$mainAry;
		$returnAry['mainv2']=array();
		foreach ($returnAry['main'] as $albumProfileId=>$albumAry){
			$albumName=$albumAry['albumname'];
			$returnAry['mainv2'][$albumName]=$albumAry;
		}
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxxf	
		return $returnAry;
	}
	//====================================== could overlay on inheritance
	function getMapProfile($base){
		$base->debugObj->printDebug("dbObj:getMapProfile('base')",-2);
		$job=$base->paramsAry['job'];	
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		$query="select * from mapelementprofileview where jobprofileid=$jobProfileId $parentInsert order by mapname, mapelementorder";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'mapname','delimit2'=>'mapelementname');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$query="select * from mapprofileview where jobprofileid=$jobProfileId";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'mapprofileid');
		$returnAry2=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry['main']=$returnAry2;
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:dbObj:getMapProfile",-2); //xx (f)
		return $returnAry;
	}
	//======================================
	function getDeptProfile($base){
		$base->debugObj->printDebug("dbObj:getDeptProfile('base')",-2);
		$job=$base->paramsAry['job'];
		$jobProfileId=$base->jobProfileAry['jobprofileid'];	
		$userName=$base->paramsAry['username'];
		$userFlag=$base->paramsAry['userflag'];
		if ($userFlag == NULL){
			$selInsert="jobprofileid=$jobProfileId";
		}
		else {
			$selInsert="username='$userName'";
		}
		$query="select * from deptfunctionprofileview where $selInsert order by deptname, deptfunctionname";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'deptname','delimit2'=>'deptfunctionname');
		$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$query="select * from deptprofileview where $selInsert";
		$result=$base->dbObj->queryTable($query,'retrieve',$base,-2);
		$passAry=array('delimit1'=>'deptname');
		$returnAry2=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry['main']=$returnAry2;
		$base->debugObj->printDebug("-rtn:dbObj:getDeptProfile",-2); //xx (f)
		return $returnAry;
	}
	//=======================================
	function initCart($base){
		$base->debugObj->printDebug("dbObj:initCart('base')",-2);
		require_once('../includes/cartObject.php');
		if (!isset($_SESSION['cartobj'])) {
			$cartObj = new cartObject();
			$_SESSION['cartobj'] = $cartObj;
		}
		else {
			$cartObj = $_SESSION['cartobj'];
		}		
		$base->debugObj->printDebug("-rtn:dbObj:initCart",-2); //xx (f)
		return $cartObj;
	}
//======================================= need to put in begin/end here
	function getParagraphProfile($base){
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'paragraphname');
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$jobParentInsert=" or jobprofileid = $jobParentId";}
		else {$jobParentInsert=NULL;}
		$query="select * from paragraphprofileview where jobprofileid=$jobProfileId $jobParentInsert";
		//echo "query: $query<br>";//xxx
		$result=$base->dbObj->queryTable($query,'read',&$base);
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry=$workAry[$jobProfileId];
		if (!is_array($returnAry)){$returnAry=array();}
		if ($jobParentId != NULL){
			$cnt=count($workAry[$jobParentId]);
			if ($cnt>0){
				foreach ($workAry[$jobParentId] as $paragraphName=>$paragraphAry){
					//echo "paragraphname: $paragraphName<br>";//xxx
					if (!array_key_exists($paragraphName,$returnAry)){
						$returnAry[$paragraphName]=$paragraphAry;
					}	
				}
			}
		}
		//$base->debugObj->printDebug($returnAry,1,'rtnary');//xxx
		return $returnAry;	
	}
//=======================================other could overlay on inheritance
	function getGenericProfile($passAry,$base){
		$base->debugObj->printDebug("dbObj:getGenericProfile('base')",-2);
		$viewName=$passAry['viewname'];
		$sortName=$passAry['sortname'];
		$delimit1=$passAry['delimit1'];
		$delimit2=$passAry['delimit2'];
//-
		$buildSort=$passAry['buildsort'];
		$parentName=$passAry['parentname'];
		$orderName=$passAry['ordername'];
		$sortDestination=$passAry['sortdestination'];
//- sort insert
		if ($sortName != null){$sortInsert=" order by $sortName";}
		else {$sortInsert=null;}
//-
		$returnAry=array();
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId!=NULL){$parentInsert="or jobprofileid=$jobParentId";}
		else {$parentInsert=NULL;}
		$query="select * from $viewName where jobprofileid=$jobProfileId $parentInsert $sortInsert";
		//echo "query: $query<br>";//xxxf
		$result=$base->dbObj->queryTable($query,'retrieve',&$base,-2);
		$passAry=array();
		if ($delimit1 != NULL){$passAry['delimit1']=$delimit1;}
		if ($delimit2 != NULL){$passAry['delimit2']=$delimit2;}
		$dataAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		if ($buildSort){
			$dataOrder=array();
			foreach ($dataAry as $key=>$valueAry){
				foreach ($valueAry as $key2=>$valueAry2){
					$orderNo=$valueAry2[$orderName];
					$dataOrder[$key][$orderNo]=$key2;	
				}
			}
			$returnAry['element_order']=$dataOrder;
		}
		$returnAry['dataorder']=$dataOrder;
		$returnAry['dataary']=$dataAry;
		$base->debugObj->printDebug("-rtn:dbObj:getGenericProfile",-2); //xx (f)
		return $returnAry;
	}
//======================================= what if two sentences have the same name!!!!!!!!!
	function getSentenceProfile($base){
		$passAry=array('delimit1'=>'jobprofileid','delimit2'=>'paragraphname','delimit3'=>'sentencename','order1'=>'sentenceno');
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$jobParentId=$base->jobProfileAry['jobparentid'];
		if ($jobParentId != NULL){$jobParentInsert=" or jobprofileid = $jobParentId";}
		else {$jobParentInsert=NULL;}
		$query="select * from sentenceprofileview where jobprofileid=$jobProfileId $jobParentInsert order by sentenceno";
		$result=$base->dbObj->queryTable($query,'read',&$base);
		$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$returnAry=$workAry[$jobProfileId];
		if (!is_array($returnAry)){$returnAry=array();}
		if ($jobParentId != NULL){
			$cnt=count($workAry[$jobParentId]);
			if ($cnt>0){
				foreach ($workAry[$jobParentId] as $paragraphName=>$paragraphAry){
					if (!array_key_exists($paragraphName,$returnAry)){
						$returnAry[$paragraphName]=$paragraphAry;
					}	
				}
			}
		}
		//$base->debugObj->printDebug($returnAry,1,'rtnary');//xxx
		//exit();//xxx
		return $returnAry;	
	}
}
?>
