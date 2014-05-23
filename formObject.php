<?php
class FormObject {
	//5.3.13 changed getFormForAjax to get all of formprofileary
	var $statusMsg;
	var $formName;
	var $callNo = 0;
	var $delim = '!!';
	var $leftToggle = true;
	var $formAry_js;
	var $formNamesXref = array();
//---
	function FormObject() {
		$this->incCalls();
		$this->statusMsg='html Object is fired up and ready for work!';
		$this->setupFormJs();
	}
//===============================================
	function setupFormJs(){
		$this->formAry_js[]="//----------------------- setup for forms;\n";
		$this->formAry_js[]="var FormObj = new FormObject();\n";
		//- cant get to work $this->formAry_js[]="var AjaxObj = new AjaxObject();\n";
		$this->formAry_js[]='var validateArray = new Array();'."\n";
	}
//===============================================
	function getFormJs(){
		return $this->formAry_js;	
	}
//===============================================
	function loadFormSetups($base){
		//$base->DebugObj->printDebug($base->formElementProfileAry,1,'xxx');
		//exit();
		foreach ($base->formProfileAry as $formName=>$formDataAry){
			if ($formName != 'element_order'){
				//echo "$formName<br>";//xxx	
				$dbTableName=$formDataAry['tablename'];
				$this->formAry_js[]="FormObj.updateEtc('$formName','dbtablename','$dbTableName');\n";
				$workAry=$base->formElementProfileAry[$formName];
				//$base->DebugObj->printDebug($workAry,1,'xxx');
				$nameStrg=null;$idStrg=null;$separ=null;
				foreach ($workAry as $formElementName=>$formElementAry){
					//echo "$formName, $formElementName<br>";
					$formElementId=$formElementAry['formelementid'];
					$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
					if ($formElementId != null){
						$nameStrg.=$separ.$formElementName;
						$idStrg.=$separ.$formElementId;
						$separ='~';
					}
				}
				//echo "datstrg: $dataStrg";
				$this->formAry_js[]="FormObj.loadFormSetupsV2('$formName', '$nameStrg','$idStrg');\n";
			}
		}	
	}
//===============================================
	function getFormForAjax($paramsAry,$base){
	//xxxd - remove below may be a problem with another program!!!!
		//$ajaxAry=$base->AjaxObj->getContainerForAjax(&$base);
		$ajaxAry[]="!!form!!\n";
		$formName=$paramsAry['param_1'];
		$formNameAry=explode('_',$formName);
		$formName=$formNameAry[0];
		$formAry=$base->formProfileAry[$formName];
		$formOperation=$formAry['formoperation'];
		$formErrorReportType=$formAry['formerrorreporttype'];
		$formErrorReportId=$formAry['formerrorreportid'];
		//$base->DebugObj->printDebug($formAry,1,'xxxd');exit();
		$formContainerId=$formAry['formcontainerid'];
		$formCount=$formAry['formcount'];
		if ($formCount == null){$formCount=0;}
		$formElementsAry=$base->formElementProfileAry[$formName];	
		$ajaxAry[]='formname|'.$formName."\n";
//- get form html to load (may not be in container html)
		$newParamsAry=array('param_1'=>$formName);
		$formHtmlAry=$base->TagObj->insertForm($newParamsAry,&$base);
		$formHtml_raw=implode('',$formHtmlAry);
		$formHtml=str_replace("\n",null,$formHtml_raw);
		$ajaxAry[]='loadetc|html|'.$formHtml."\n";
//- get etc stuff
		$dbTableName=$formAry['tablename'];
		//echo "forname: $formName, dbtablename: $dbTableName<br>";//xxx
		$query="select * from dbcolumnprofileview where dbtablename='$dbTableName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>dbcolumnname);
		$validateAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$ajaxAry[]='loadetc|dbtablename|'.$dbTableName."\n";
//- get validation stuff
		$nameStrg=null;$idStrg=null;$validateRegExStrg=null;$validateKeyMapStrg=null;$dontClearDataStrg=null;
		$validateErrorMsgStrg=null;$separ=null;$dataTypeStrg=null;
//- create array of formelemntno=>formelementname
		$workAry=array();
		foreach ($formElementsAry as $name=>$valueAry){
			$theNo=$valueAry['formelementno'];
			$workAry[$theNo]=$name;
		}
		$workLpEnd=count($workAry);
		for ($workLp=1;$workLp<=$workLpEnd;$workLp++){
			$formElementName=$workAry[$workLp];
			$formElementAry=$formElementsAry[$formElementName];
			//$base->DebugObj->printDebug($formElementAry,1,'xxxd');
			$formElementId=$formElementAry['formelementid'];
			//- cant have additional info after _ for saving to javascript form directory
			$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
			//below causes blowups in validation
			$formElementIdAry=explode('_',$formElementId);
			$formElementId=$formElementIdAry[0];
			//- end special modification
			$formElementDontClear=$formElementAry['formelementdontclear'];
			$formElementDontClear=$base->UtlObj->returnFormattedData($formElementDontClear,'boolean','js',&$base);
			$validateRegEx=$validateAry[$formElementName]['validateregex'];
			$validateKeyMap=$validateAry[$formElementName]['validatekeymap'];
			$validateErrorMsg=$validateAry[$formElementName]['validateerrormsg'];
			$dataType=$validateAry[$formElementName]['dbcolumntype'];
			if ($formElementId != null){
				$nameStrg.=$separ.$formElementName;
				$idStrg.=$separ.$formElementId;
				$validateRegExStrg.=$separ.$validateRegEx;
				$validateKeyMapStrg.=$separ.$validateKeyMap;
				$validateErrorMsgStrg.=$separ.$validateErrorMsg;
				$dataTypeStrg.=$separ.$dataType;
				$dontClearDataStrg.=$separ.$formElementDontClear;
				$separ='~';
			}
		}
		//- validation
		$ajaxAry[]='loadetc|dbcolumnnames|'.$nameStrg."\n";
		$ajaxAry[]='loadetc|dbcolumnids|'.$idStrg."\n";
		$ajaxAry[]='loadetc|regexs|'.$validateRegExStrg."\n";
		$ajaxAry[]='loadetc|keymaps|'.$validateKeyMapStrg."\n";
		$ajaxAry[]='loadetc|errormsgs|'.$validateErrorMsgStrg."\n";
		//- control
		$ajaxAry[]='loadetc|datatypes|'.$dataTypeStrg."\n";
		$ajaxAry[]='loadetc|dontcleardata|'.$dontClearDataStrg."\n";
		//- other
		$sessionName=$base->paramsAry['sessionname'];
		$ajaxAry[]='loadetc|sessionname|'.$sessionName."\n";
		//- general form elements
		foreach ($base->formProfileAry[$formName] as $formPropertyName=>$formPropertyValue){
			$ajaxAry[]="loadetc|$formPropertyName|$formPropertyValue\n";
		}
		//$ajaxAry[]='loadetc|formcount|'.$formCount."\n";
		//$ajaxAry[]='loadetc|formoperation|'.$formOperation."\n";
		//$ajaxAry[]='loadetc|formerrorreporttype|'.$formErrorReportType."\n";
		//$ajaxAry[]='loadetc|formerrorreportid|'.$formErrorReportId."\n";
		return $ajaxAry;
	}
//===============================================
	function buildForm($formName,$dbControlsAry,$base){
		$base->DebugObj->printDebug("FormObj:buildForm($formName,$dbControlsAry,'base')",0);
		$base->FileObj->writeLog('jefftest2',"entering formname: $formName",&$base);//xxxf05
		$this->formName=$formName;
		$dbTableName=$dbControlsAry['dbtablename'];
		if ($dbTableName==''){$dbTableName='none';}
		$defaultDataAry=$dbControlsAry['datarowsary'][$dbTableName][0];
		$allDataAry=$dbControlsAry['datarowsary'];
		//echo "formname: $formName<br>";//xxx
		//$base->DebugObj->printDebug($allDataAry,1,'alldataary');//xxx
		//$base->FileObj->writeLog('formcreate.txt','--- form('.$formName.')---',&$base);//xxxf22
		$dataAry=$defaultDataAry;
		//$base->DebugObj->printDebug($dataAry,1,'dataary');//xxx
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$selectorNameAry=$dbControlsAry['selectornameary'];
		$keyName=$dbControlsAry['keyname'];
		$tabIndexBase=$dbControlsAry['tabindexbase'];
		$returnAry=array();
		$returnWorkAry=array();
		$formAry=$base->formProfileAry[$formName];
//- do <form ...>
		$formClass_raw=$formAry['formclass'];
		$formClass=$base->UtlObj->returnFormattedString($formClass_raw,&$base);
		$formId_raw=$formAry['formid'];
		$formId=$base->UtlObj->returnFormattedString($formId_raw,&$base);
		$formContainerId=$formAry['formcontainerid'];
		if ($formId == NULL){$formId=$formName;}
		$tableFormat=$formAry['formtableformat'];
		$labelFill=$formAry['formlabelfill'];
		$labelCols=$formAry['formlabelcols'];
		//- target and enctype
		$formTarget=$formAry['formtarget'];
		if ($formTarget == null){$formTargetInsert=null;}
		$formEncType=$formAry['formenctype'];
		if ($formEncType==null){$formEncType="text/plain";}
		$formEncTypeInsert="enctype=\"$formEncType\"";
		//- method
		$formMethod=$formAry['formmethod'];
		if ($formMethod==NULL){$formMethod='get';}
		$formMethodInsert="method=\"$formMethod\"";
		//- enctype
		$formEncType=$formAry['formenctype'];
		if ($formEncType==null){$formEncTypeInsert=null;}
		else {$formEncTypeInsert=" enctype=\"$formEncType\"";}
		//- action
		$formAction=$formAry['formaction'];
		if ($formAction==null){
			$systemAry=$base->ClientObj->getSystemData(&$base);
			$jobLocal=$systemAry['joblocal'];
			$job=$base->paramsAry['job'];
			$formAction="$jobLocal$job";
		}
		$formActionInsert="action=\"$formAction\"";		
		$formType=$formAry['formtype'];
		//- target
		$formTarget=$formAry['formtarget'];
		if ($formTarget != null){$formTargetInsert=" target=\"$formTarget\"";}
		else {$formTargetInsert=null;}
		//- events
		$formEvents_raw=$formAry['formevents'];
		$formEvents=$base->UtlObj->returnFormattedString($formEvents_raw,&$base);
		//- line
		//echo "xxxd1: name: $formName, type: $formType<br>";//xxxd
		if ($formType != 'fragment'){
			$formLine="<form $formActionInsert $formMethodInsert $formEncTypeInsert $formTargetInsert $formEvents";
			if ($formClass != NULL){$formLine .= " class=\"$formClass\"";}
			if ($formId != NULL){$formLine .= " id=\"$formId\"";}
			$formLine .= ">";
			$returnAry[]="$formLine\n";
			$this->formAry_js[]="FormObj.updateEtc('$formName','containerid','$formContainerId')"."\n";
			//xxx
			//if ($formName=='insertpictureprofile'){$base->DebugObj->printDebug($returnAry,1,'xxxd');exit();}
		}
		//- comment
//- do only if not form fragment
		//echo "----------do form initial hidden stuff!!!<br>";//xxxf24
		if ($formType != 'fragment'){
			$subReturnAry=$this->buildComment('hidden controls',&$base);
			$returnAry=array_merge($returnAry,$subReturnAry);
// -------------------------------------- do <input type="hidden" name="d" 
			$tst=$base->paramsAry['d'];
			if ($tst == 'y'){
				$subtype="hidden";
				$name="d";
				$value="y";
				$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden",'mode'=>'noprepost');
				$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
// -------------------------------------- do <input type="hidden" name="form" 
			$subtype="hidden";
			$name="form";
			$value=$formName;
			if ($value == ""){$value='none';}
			$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","formelementid"=>"formid","mode"=>"noprepost");
			$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
			$returnAry=array_merge($returnAry,$subReturnAry);
// ------------------------------------- do <input type="hidden" name="job" 
			$subtype="hidden";
			$name="job";
			$value=$base->jobProfileAry['jobname'];
			if ($value == ""){$value=$base->jobProfileAry['jobstr'];}
			$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","formelementid"=>"jobid","mode"=>"noprepost");
			$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
			$returnAry=array_merge($returnAry,$subReturnAry);
// ---------------------------- do type="hidden" name="operation" value="doit"
			$subtype="hidden";
			$name="operation";
			$value=$formAry['formoperation'];
			if ($value == ""){
				$value=$base->jobProfileAry['operationstr'];
			}
			if ($value != ""){
				$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","formelementid"=>"operationid","mode"=>"noprepost");
				$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
//--------------------------- do type="hidden" name="redirovr" value=<returnovr>
			$subtype="hidden";
			$name="returnovr";
			$value=$base->paramsAry['returnovr'];
			if ($value != ""){
				$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","mode"=>"noprepost");
				$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
//--------------------------- do type="hidden" name="sessionname" value=<sessionname>
			$subtype="hidden";
			$name="sessionname";
			$value=$base->paramsAry['sessionname'];
			if ($value != ""){
				$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","mode"=>"noprepost");
				$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
// --------------------------------- do <input type="hidden" $keyName=$keyValue 
//--- need to put in id here xxxf
			$subReturnAry=$this->buildComment('hidden key',&$base);
			$returnAry=array_merge($returnAry,$subReturnAry);
			$subtype="hidden";
			$name=$keyName;
			$value=$dataAry[$keyName];
			if ($value != ""){
				$tempAry=array("formname"=>$formName,"formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","mode"=>"noprepost");
				$subReturnAry=$this->buildInputHidden($tempAry,"",0,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
			$subReturnAry=$this->buildComment('rest of form',&$base);
			$returnAry=array_merge($returnAry,$subReturnAry);
// ---------------------------------------- do rest of form elements in a table 
			foreach ($base->errorProfileAry['othererrorary'] as $ctr=>$errorMsg){
				$returnAry[]="<br>".$errorMsg."<br>";
			}
			//xxxd - very important - <form and <table both have the same id!!!!
			//- put a _form on id and it automatically taken off for <table
			//- if you dont, then you have two ids
			$formIdAry=explode('_',$formId);
			$formIdUse=$formIdAry[0];
			$insertAttribute=" id=\"$formIdUse\"";
			if ($formClass != ''){$insertAttribute.=" class=\"$formClass\"";}
			if ($tableFormat != 6){
				if ($tableFormat==10){$debugInsert="";}
				else {$debugInsert=null;}
				$returnAry[]="<table border=0 $debugInsert $insertAttribute>\n";//xxx
			}
			else {
				$returnAry[]="<div $insertAttribute>\n";	
			}
		}
//- do form or form fragment
		$allFormElementAry=$base->formElementProfileAry[$formName];
/*
		//xxxf666
		if ($formName == 'basicform'){
			foreach ($allFormElementAry as $name=>$value){
				echo "$name<br>";
			}
			exit();
		} else {
			echo "formobj: formanme: $formName<br>";
		}
*/
		$formOrderAry=$base->formProfileAry['element_order'][$formName];
		$formElCnt=count($formOrderAry);
		$rowNo=0;
		//$base->DebugObj->printDebug($dataAry,1,'dtaary');//xxx
		//$base->DebugObj->printDebug($allFormElementAry,1,'for');//xxx
		//- need to alter this for row, column setup form
		//echo "----------------loop through form elements!!!<br>";//xxxf24
		for ($formElCtr=1;$formElCtr<=$formElCnt;$formElCtr++){
			//echo "forobj: $formElCtr<br>";//xxxf
			$formElName=$formOrderAry[$formElCtr];
			//echo "-----$formName, $formElName, $formElCtr<br>";//xxxf24
			$formElNameAry=explode('_',$formElName);
			$formElNameDbTable=$formElNameAry[0];
			$formElNameRowNo=$formElNameAry[1];
			$formElNameColumnName=$formElNameAry[2];
			$formElAry=$allFormElementAry[$formElName];
			$formElName=$formElAry['formelementname'];
			//$this->doDebug("FormObj: formname: $formName, formelctr: $formElCtr, formelname: $formElName, formelnamecolumnname: $formElNameColumnName",&$base);//xxxf
			if ($formElNameColumnName != ''){
				$dataAry=$allDataAry[$formElNameDbTable][$formElNameRowNo];
				$formElAry['formelementname_use']=$formElNameColumnName;
			}
			else {
				$dataAry=$defaultDataAry;
				$formElAry['formelementname_use']=$formElName;
			}
			//echo "formelnameuse: $formElName or $formElNameColumnName<br>";//xxx
			//$base->DebugObj->printDebug($dataAry,1,'dataary');//xxx
			$formElAry['datatype']=$dbTableMetaAry[$formElName]['dbcolumntype'];
			$formElAry['labelfill']=$labelFill;
			$formElAry['labelcols']=$labelCols;
			$formElAry['tabindexbase']=$tabIndexBase;
			$formElType=$formElAry['formelementtype'];
			$formElSubType=$formElAry['formelementsubtype'];
			//echo "name: $formElName, type: $formElType<br>";//xxx
			//if ($formElName == 'cssprofileid'){
				//$base->FileObj->writeLog('jefftest',"xxx05: formname: $formName, cssprofileid, type: $formElType",&$base);
			//}
			switch ($formElType){
				//- make the below actually call the real buildcontainer which will insert form fragments
				case 'jsbutton':
					$subReturnAry=$this->buildJsButton($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'container':
					$subReturnAry=$base->ContainerObj->insertContainerHtml($formElName,$base);
					break;
				case 'select':
					//echo "formelname: $formElName<br>";//xxx
					$subReturnAry=$this->buildSelect($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'inputselect':
					$subReturnAry=$this->buildInputSelect($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'display':
					$subReturnAry=$this->buildDisplay($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'simpledisplay':
					$subReturnAry=$this->buildSimpleDisplay($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'textarea':
					$subReturnAry=$this->buildTextArea($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'button':
					$subReturnAry=$this->buildButton($formElAry,$tableFormat,&$base);
					//$base->DebugObj->printDebug($subReturnAry,1,'brtn');//xxx
					break;
				//- same as jsbutton
				case 'jssubmit':
					$subReturnAry=$this->buildJsButton($formElAry,$tableFormat,&$base);
					//$base->DebugObj->printDebug($subReturnAry,1,'xxx');//xxx
					break;
				case 'boolean':
					//echo "name: $formElName<br>";//xxx
					$subReturnAry=$this->buildBoolean($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'url':
				//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
					$subReturnAry=$this->buildUrl($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'iframe':
					$subReturnAry=$this->buildIframe($formElAry,$dataAry,$tableFormat,&$base);
					break;
				case 'input':
					switch ($formElSubType){
						case 'hidden':
							$subReturnAry=$this->buildInputHidden($formElAry,$dataAry,$tableFormat,&$base);
							break;
						case 'text':
							$subReturnAry=$this->buildInputText($formElAry,$dataAry,$tableFormat,&$base);
							//$base->DebugObj->printDebug($subReturnAry,1,'itrtn');//xxx
							break;
						case 'password':
							$subReturnAry=$this->buildInputText($formElAry,$dataAry,$tableFormat,&$base);
							//$base->DebugObj->printDebug($subReturnAry,1,'iprtn');//xxx
							break;
						case 'checkbox':
							$subReturnAry=$this->buildInputCheckBox($formElAry,$dataAry,$tableFormat,&$base);
							break;
						case 'file':
							$subReturnAry=$this->buildInputText($formElAry,$dataAry,$tableFormat,&$base);
							break;
						case 'submit':
							$subReturnAry=$this->buildInputSubmit($formElAry,$dataAry,$tableFormat,&$base);
							break;
						default:
							echo "error in form name: $formElName,  subtype: $formElSubType";
					}
					break;
				case 'image':
					$formElLabel=$formElAry['formelementlabel'];
					$formElName=$formElAry['formelementname'];
					$class=$formElAry['formelementclass'];
					$formElementId=$formElAry['formelementid'];
					$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
					$passAry=array();
					$passAry=$this->setTableFields($formElName,$formElLabel,$errorMsg,$tableFormat,$class,$formElementId,$formElAry,&$base);
					$preTable=$passAry['pretable'];
					$postTable=$passAry['posttable'];
					//- class
					$formElClass=$formElAry['formelementclass'];
					if ($formElClass==NULL){$formElClass='NULL';}
					$formElClassInsert="class=\"$formElClass\"";
					//- id
					$formElementId=$formElAry['formelementid'];
					$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
					if ($formElementId==NULL){$formElIdInsert=NULL;}
					else {$formElIdInsert="id=\"$formElementId\"";}
					//- events
					$formElEvents_raw=$formElAry['formelementeventattributes'];
					$formElEvents=$base->UtlObj->returnFormattedString($formElEvents_raw,&$base);
					if ($formElEvents==NULL){$formElEventsInsert=NULL;}
					else {$formElEventsInsert=$formElEvents;}
					//- name
					$formElNameAry=explode('/',$formElName);
					$imagePath=$dataAry[$formElNameAry[0]].'/'.$dataAry[$formElNameAry[1]];
					//echo "formelnameary0: $formElNameAry[0], formelnameary1: $formElNameAry[1], imagepath: $imagePath";exit();//xxxd
					//$base->DebugObj->printDebug($dataAry,1,'xxxd');exit();//xxxd		
					$subReturnAry=array();
					$subReturnAry[]="$preTable<img src=\"$imagePath\" $formElClassInsert $formElIdInsert $formElEventsInsert>$postTable";
					break;
				case 'imageprofile':
					$formElName=$formElAry['formelementname'];
					$specificTableFormat=$formElAry['formelementtableformat'];
					if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
					$passAry=array();
					$passAry=$this->setTableFields($formElName,$formElLabel,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
					$preTable=$passAry['pretable'];
					$postTable=$passAry['posttable'];
					$subReturnAry=array();
					//$imgAry=$base->HtmlObj->buildImg($formElName,&$base);
					$passAry=array();
					$passAry['imagename']=$formElName;
					$imageEvents=$formElAry['formelementeventattributes'];
					$imageEvents=$base->UtlObj->returnFormattedStringDataFed($imageEvents,$dataAry,&$base);
					$passAry['imageevents']=$imageEvents;
					$formElementId=$formElAry['formelementid'];
					$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
					$passAry['imageid']=$formElementId;
					//echo "formelementid: $formElementId<br>";//xxxd
					$imgAry=$base->HtmlObj->buildImgPass($passAry,&$base);
					//$base->DebugObj->printDebug($imgAry,1,'xxxd');
					$subReturnAry[]="$preTable$imgAry[0]$postTable";
					break;
				default:
				echo "form name: $formName, formel name: $formElName,  element no: $formElCtr<br>";//xx
					$base->DebugObj->placeCheck("Invalid Form type: $formElType"); //xx (c)
					//$base->DebugObj->printDebug($formElAry,1,'formelary');//xx
					break;
			}
			if ($tableFormat==10){
				$formElCol=$formElAry['formelementtablecol'];
				$formElRow=$formElAry['formelementtablerow'];
				$returnWorkAry[$formElRow][$formElCol]=$subReturnAry;	
			}
			else {
				$returnAry=array_merge($returnAry,$subReturnAry);
			}
		}
		//echo "formname: $formName, tableformat: $tableFormat<br>";//xxx
		if ($tableFormat==10){
			//$base->DebugObj->printDebug($returnAry,1,'xxx');
			foreach ($returnWorkAry as $rowNo=>$colAry){
				$returnAry[]='<tr>';
				foreach ($colAry as $colNo=>$formElAry){
					$returnAry=array_merge($returnAry,$formElAry);
				}
				$returnAry[]='</tr>';
			}
			//$base->DebugObj->printDebug($returnWorkAry,1,'xxxd');
		}
		if ($tableFormat != 6){
			$returnAry[]="</table>";
		}
		else {
			$returnAry[]="</div>";			
		}
		//below might be an error xxx
		//else {$returnAry[]="</div>";}
		if ($formType !='fragment'){
			//xxxd - do </div>
			$returnAry[]="</form>";
		}
		$base->DebugObj->printDebug("-rtn:buildForm",0); //xx (f)
		//$base->DebugObj->printDebug($returnAry,1,'xxxf');
		//exit();//xxxf666
		return $returnAry;
	}
//==============================================================
	function buildInputText($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildInputText($formElAry,$dataAry,$tableFormat,'base')",0);
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$inputType=$formElAry['formelementsubtype'];
		$formElementEvent=$formElAry['formelementeventattributes'];
		$formElementEvent=$base->UtlObj->returnFormattedString($formElementEvent,&$base);
		//$base->DebugObj->printDebug($formElAry,1,'fea');//xxx
		$tabIndex_forthis=$formElAry['formelementtabindex'];
		if ($tabIndex_forthis == NULL){$tabIndex_forthis=$formElAry['formelementno'];}
		$tabIndexBase=$formElAry['tabindexbase'];
		$tabIndex=$tabIndexBase+$tabIndex_forthis;
		$tabIndexInsert="tabindex=\"$tabIndex\"";
		$checkKey='formelementnamesuffix';
		if (array_key_exists($checkKey,$formElAry)){
			$nameSuffix=$formElAry['formelementnamesuffix'];
			$useName=$name.'_'.$nameSuffix;
		}
		else $useName=$name;
		$labelFill=$formElAry['labelfill'];
		$labelCols=$formElAry['labelcols'];
		$class=$formElAry['formelementclass'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		$formElementId=$formElary['formelementid'];
		$formElementId=$base->UtlObj->returnFormattedString($formElementId,&$base);
		$theData=$dataAry[$name];
		$errorMsg=$base->errorProfileAry['columnerrorary'][$name];
		$value_raw=$formElAry['formelementvalue'];	
		if ($theData != NULL){$value_raw=$theData;}
		$formElementDontConvert_raw=$formElAry['formelementdontconvert'];
		$formElementDontConvert=$base->UtlObj->returnFormattedData($formElementDontConvert_raw,'boolean','internal');
		if ((strpos(('x'.$value_raw),'%',0)>0) && !($formElementDontConvert)) {
			$value=$base->UtlObj->returnFormattedString($value_raw,&$base);
		}
		else {
			$value=$value_raw;
		}
		$label=$formElAry['formelementlabel'];
		$label=$base->UtlObj->returnFormattedString($label,&$base);//xxx
		if ($labelFill != '' && $labelCols >0){
			$labelLen=strlen($label);
			$fillLen=$labelCols-$labelLen;
			for ($ctr=0; $ctr<$fillLen;$ctr++){$label.=$labelFill;}
		}
		$cols=$formElAry['formelementcols'];
		if ($cols != NULL){$colIns=" size=\"$cols\" ";}
		else {$colIns="";}
		$passAry=$this->setTableFields($useName,$label,$errorMsg,$tableFormat,$class,$formElementId,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$errorMsgName=$useName.'_errormsg';
		$typeInsert="type=\"$inputType\"";
		$nameInsert="name=\"$useName\"";
		$id_raw=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id_raw,&$base);
		//echo "idraw: $id_raw, name: $useName<br>";//xxxf
		//$base->DebugObj->printDebug($formElAry,1,'xxxea');
		if ($id == NULL){$id=$useName;}
		$idInsert="id=\"$id\"";	
		//xxxf lets dont do this when keying in stuff yet
		$onKeyUpIns="onkeyup=\"FormObj.validateInput('$useName');\"";
		//if ($formElementEvent == null){$formElementEvent=$onKeyUpIns;}
		//echo "formname: $formName, formelname: $name, text input, onkeyupins: $onKeyUpIns, event: $formElementEvent<br>";//xxxf
//-
		$insertLine="$preTable<input $typeInsert $nameInsert $idInsert $classInsert $tabIndexInsert $colIns value=\"$value\" $formElementEvent>\n$postTable";
		//if ($name=='formelementlabel'){$base->DebugObj->printDebug($insertLine,1,'xxx');}
		$returnAry[]=$insertLine;
		$base->DebugObj->printDebug("-rtn:buildInputText",0); //xx (f)
		return $returnAry;
	}
//=============================================================
	function buildTextArea($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildTextArea($formElAry,$dataAry,$tableFormat,'base')",0);
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$inputType=$formElAry['formelementsubtype'];
		$formElementCols=$formElAry['formelementcols'];
		if ($formElementCols<1){$formElementCols=80;}
		$formElementColsInsert="cols=\"$formElementCols\"";
		$formElementRows=$formElAry['formelementrows'];
		$formElementRowsInsert="rows=\"$formElementRows\"";
		if ($formElementRows<1){$formElementRows=5;}
		//$base->DebugObj->printDebug($formElAry,1,'fea');//xxx
		$tabIndexBase=$formElAry['tabindexbase'];
		$tabIndex_forthis=$formElAry['formelementtabindex'];
		if ($tabIndex_forthis == NULL){$tabIndex_forthis=$formElAry['formelementno'];}
		$tabIndex=$tabIndexBase+$tabIndex_forthis;//xxxdf
		$tabIndexInsert="tabindex=\"$tabIndex\"";
		$checkKey='formelementnamesuffix';
		if (array_key_exists($checkKey,$formElAry)){
			$nameSuffix=$formElAry['formelementnamesuffix'];
			$useName=$name.'_'.$nameSuffix;
		}
		else $useName=$name;
		$labelFill=$formElAry['labelfill'];
		$labelCols=$formElAry['labelcols'];
		$class=$formElAry['formelementclass'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id!=null){$idInsert="id=\"$id\"";}
		else {$idInsert=null;}
		$theData=$dataAry[$name];
		$errorMsg=$base->errorProfileAry['columnerrorary'][$name];
		$value=$formElAry['formelementvalue'];	
		if ($theData != NULL){$value=$theData;}
		$label=$formElAry['formelementlabel'];
		$label=$base->UtlObj->returnFormattedString($label,&$base);//xxx
		if ($labelFill != '' && $labelCols >0){
			$labelLen=strlen($label);
			$fillLen=$labelCols-$labelLen;
			for ($ctr=0; $ctr<$fillLen;$ctr++){$label.=$labelFill;}
		}
		$cols=$formElAry['formelementcols'];
		if ($cols != NULL){$colIns=" size=\"$cols\" ";}
		else {$colIns="";}
		$passAry=$this->setTableFields($useName,$label,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$errorMsgName=$useName.'_errormsg';
		$typeInsert="type=\"$inputType\"";
		$nameInsert="name=\"$useName\"";
		$formElementEvents=$formElAry['formelementeventattributes'];
		$formElementEvents=$base->UtlObj->returnFormattedString($formElementEvents,&$base);
//- not sure what this is going to do but will fix it xxxf22
		$onKeyUpIns="onkeyup=\"FormObj.validateInput('$useName');\"";
		if ($formElementEvents == ''){
			$formElementEvents=$onKeyUpIns;
		}
		//echo "formname: $formName, formelname: $name, text area, onkeyupins: $onKeyUpIns, events: $formElementEvents<br>";//xxxf
		$returnAry[]="$preTable<textarea $formElementRowsInsert $formElementColsInsert $typeInsert $nameInsert $idInsert $classInsert $tabIndexInsert $colIns $formElementEvents >$value</textarea>\n$postTable";
		$base->DebugObj->printDebug("-rtn:buildTextArea",0); //xx (f)
		return $returnAry;
	}
//=============================================================
	function buildBoolean($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("FormObj:buildBoolean",0);
		$optionQuery="select * from standardpromptsprofile where standardpromptsname='yesno' order by standardpromptslabel";
		$formElAry['formelementoptionsql']=$optionQuery;
		$formElAry['formelementoptionlabelname']='standardpromptslabel';
		$formElAry['formelementoptionvaluename']='standardpromptsvalue';
		$subReturnAry=$this->buildSelect($formElAry,$dataAry,$tableFormat,&$base);
		$base->DebugObj->printDebug("-rtn:buildBoolean",0); //xx (f)
		return $subReturnAry;
	}
//============================================================= 
	function buildSelect($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("FormObj:buildSelect($formElAry,$dataAry,$tableFormat,'base')",0);
		$formName=$formElAry['formname'];
		//$formElName=$formElAry['formelementname'];//xxx
		//echo "formname in buildselect: $formName, formelname: $formElName<br>";//xxx
		//$base->DebugObj->printDebug($dataAry,1,'dta');//xxx
		$selectLabel=$formElAry['formelementlabel'];
		$labelFill=$formElAry['labelfill'];
		$labelCols=$formElAry['labelcols'];
		$multipleSelect_raw=$formElAry['formelementmultiple'];
		$multipleSelect=$base->UtlObj->returnFormattedData($multipleSelect_raw,'boolean','internal');
		if ($multipleSelect){$multipleInsert=' multiple ';}
		else {$multipleInsert=NULL;}
//- build label
		if ($labelFill != '' && $labelCols >0){
			$labelLen=strlen($selectLabel);
			$fillLen=$labelCols-$labelLen;
			for ($ctr=0; $ctr<$fillLen;$ctr++){$selectLabel.=$labelFill;}
		}
//- build option select, label and value names
		$optionQuery=$formElAry['formelementoptionsql'];
		$labelColName=$formElAry['formelementoptionlabelname'];
		//echo "labelcolnme: $labelColName<br>";//xxx
		$valueColName=$formElAry['formelementoptionvaluename'];
		//xxx new
		$pos=strpos($valueColName,'_',0);
		if ($pos>0){
			$valueColNameAry=explode('_',$valueColName);
			$valueColName=$valueColNameAry[0];
			$valueColName2=$valueColNameAry[1];
		}
		else {$valueColName2 = '';}
		$selectName=$formElAry['formelementname'];
		$useSelectName=$formElAry['formelementname_use'];
		if (strpos($useSelectName,'_select',0)>0){
			$dbTableColumnNameLen=strlen($useSelectName)-7;
			$dbTableColumnName=substr($useSelectName,0,$dbTableColumnNameLen);
		}
		else {
			$dbTableColumnName=$useSelectName;
		}
		//echo "dbtablecolumnname: $dbTableColumnName<br>";//xxx
		$selectSize=$formElAry['formelementrows'];
		$valueFromFormElement_raw=$formElAry['formelementvalue'];
		$valueFromFormElement=$base->UtlObj->returnFormattedString($valueFromFormElement_raw,&$base);
		$formName=$formElAry['formname'];
		$formElName=$formElAry['formelementname'];
		$formElClass=$formElAry['formelementclass'];
		$formElId=$formElAry['formelementid'];
		$formElId=$base->UtlObj->returnFormattedString($formElId,&$base);
		if ($formElId==null){$formElIdInsert=null;}
		else {$formElIdInsert="id=\"$formElId\"";}			
		$formElFirstLabel=$formElAry['formelementfirstlabel'];
		$formElFirstValue=$formElAry['formelementfirstvalue'];
		if ($formElClass != ''){$formElClassInsert="class=\"$formElClass\"";}
		else {$formElClassInsert='';}
		$formName=$formElAry['formname'];
		$dontGetData_file=$base->formProfileAry[$formName]['formdontreaddata'];
		$dontGetData=$base->UtlObj->returnFormattedData($dontGetData_file,'boolean','internal');
		if ($dontGetData){
			$theData=$base->paramsAry[$dbTableColumnName];
			//$this->doDebug("from paramsary($dbTableColumnName) thedata: $theData",&$base);//xxxf
		}
		else{
			$theData=$dataAry[$dbTableColumnName];
			//$this->doDebug("from dataAry($dbTableColumnName) thedata: $theData",&$base);//xxxf
		}
		//$base->DebugObj->printDebug($dataAry,1,' dataary');//
		//echo "thdata: $theData from $dbTableColumnName<br>";//
		//$base->DebugObj->printDebug($dataAry,1,'xxx');
		$dataType=$formElAry['datatype'];
		if ($dataType == ""){$dataType="varchar";}
		if ($theData == ""){$theData=$valueFromFormElement;}
		//if theData is null here and you want to use the value in paramsary assoc with the
		//name of this element, then put %elementname% into its value field
		// I cant do it automatically because of select of parent ids using a current id which is also in the
		// option list and in paramsary - in that case the value field would be %parentprofileid%
		$theData_formformat=$base->UtlObj->returnFormattedData($theData,$dataType,'form');
//- get option list
//- formuseotherdb ... the form gets its data from the 'other' database
//- formelementotherdb ... option statement gets dbconn from System001Obj->getClientConn(dbNo,base)
//- formelementotherdbno ... dbno in System001Obj retrieved by getClientConn(dbNo,base) ... you have to set it up manually
//- if dbno is 0, then the options are retrieved from the same database as the data for the form
		$useOtherDb_raw=$formElAry['formelementotherdb'];
		$useOtherDb=$base->UtlObj->returnFormattedData($useOtherDb_raw,'boolean','internal');
		$dbNo=$formElAry['formelementotherdbno'];
		//echo "query: $optionQuery<br>";//xxxf28
		$base->FileObj->writeLog('jefftest',"option query: $optionQuery",&$base);
		
		if ($useOtherDb){
			if ($dbNo>0 && $dbNo<10){
				$theDbConn=$base->System001Obj->getClientConn($dbNo,&$base);
				if ($theDbConn != NULL){
					$result=$base->ClientObj->queryClientDbTable($optionQuery,$theDbConn,'read',&$base);
					$base->FileObj->writeLog('debug',"formname: $formName, formElName: $formElName, dbno: $dbNo from ele, dbconn: $theDbConn, option query: $optionQuery",&$base);
				}
				else {$result=NULL;}
			}
			else {
				if ($dbNo==0){
					// query the data like the formdata does
					$base->DbObj->setUseOtherDb(&$base);
					$result=$base->DbObj->queryTable($optionQuery,'read',&$base);
					$base->FileObj->writeLog('debug',"xxxf3.6: DbObj->setUseOtherDb run",&$base);
				}
				else {
					$base->FileObj->writeLog('debug',"xxxf3.8 dbno is not 0 so total error",&$base);
					$result=NULL;
				}
			}
		}
		else {
			// - form says to use otherdb 
			$formUseOtherDb_raw=$base->formProfileAry[$formName]['formuseotherdb'];
			$formUseOtherDb=$base->UtlObj->returnFormattedData($formUseOtherDb_raw,'boolean','internal');
			//$base->FileObj->writeLog('debug',"useotherdb: $useOtherDb, formuseotherdb: $formUseOtherDb so set useotherdb in DbObj, option query: $optionQuery",&$base);
			if ($useOtherDb){$base->DbObj->setUseOtherDb(&$base);}
			//echo "optionselect: $optionQuery, useotherdb: $useOtherDb<br>";//
			$result=$base->DbObj->queryTable($optionQuery,'select',&$base);
		}
		$dmyAry=array('selectoptionvalue'=>$dataType);
		$passAry=array('globaldatatype'=>$dmyAry);
		//!!redo above
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($workAry,1,'xxx');
		$returnAry=array();
		$errorMsg=$base->errorProfileAry['columnerrorary'][$selectName];
		$formEvent_raw=$formElAry['formelementeventattributes'];
		$formEvent=$base->UtlObj->returnFormattedString($formEvent_raw,&$base);
		if ($formEvent != NULL){$formEventInsert=$formEvent;}
		else {$formEventInsert=NULL;}
		//xxx
		$passAry=$this->setTableFields($selectName,$selectLabel,$errorMsg,$tableFormat,$formElClass,$formElId,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$returnAry[]="$preTable\n<select name=\"$selectName\" $formElIdInsert $formElClassInsert size=\"$selectSize\" $multipleInsert $formEventInsert>\n";
		//echo "name: $selectName, formelfirstlabel: $formElFirstLabel<br>";
		if ($formElFirstLabel != NULL){
			$optionLine="<option value=\"$formElFirstValue\">$formElFirstLabel</option>";
			$returnAry[]="$optionLine\n";
		}
		$formXref_raw=$formElAry['formelementxrefform'];
		$formXref=$base->UtlObj->returnFormattedData($formXref_raw,'boolean','internal');
		$formXrefName=$formElAry['formelementoptionformxrefname'];
		if ($formXref){
			//- below turns off bool if the formname is already in formnamesxref - once per form?!?
			if (array_key_exists($formName,$this->formNamesXref)){null;}
			else {
				$this->formAry_js[]="FormObj.setOptionXrefForm('$formName');\n";
				$this->formNamesXref[$formName]='xxx';
			}	
		}
//- loop through options
		//$chkpos=strpos($optionQuery,'columntype',0);//xxx
		//$base->DebugObj->printDebug($base->paramsAry,1,'paramsary');//xxxd
		$labelNameAry=explode(',',$labelColName);
		$labelNameAryNo=count($labelNameAry);
		$noOptions=count($workAry);
		for ($ctr=0;$ctr<$noOptions;$ctr++){
			$optionRow=$workAry[$ctr];
			$optionValue=$optionRow[$valueColName];
			if ($valueColName2 != ''){
				$optionValue.='_'.$optionRow[$valueColName2];
			}
			$optionValue_formformat=$base->UtlObj->returnFormattedData($optionValue,$dataType,'form');
			$useLabelColName=NULL;
			$firstTime=true;
			$optionLabels=NULL;
			//$base->DebugObj->printDebug($optionRow,1,'optionrow');
			for ($lblCtr=0;$lblCtr<$labelNameAryNo;$lblCtr++){
				if (!$firstTime){$optionLabels.=',';}
				$labelName=$labelNameAry[$lblCtr];
				//echo "labelname: $labelName<br>";//xxx
				$optionLabel=$optionRow[$labelName];
				//echo "optionlabel: $optionLabel<br>";//xxx
				$optionLabels.=$optionLabel;
				$firstTime=false;
			}
//- check selected insert
			$selectedInsert="";
			$dataType=$formElAry['datatype'];
			if ($dataType == ""){$dataType="varchar";}
			$theData_formformat=$base->UtlObj->returnFormattedData($theData,$dataType,'form');
			//echo "thedata: $theData_formformat, optionvalue: $optionValue_formformat<br>";//
			if ($optionValue_formformat == $theData_formformat){
				$selectedInsert=" selected=\"selected\" ";
			}
			else {$selectedInsert="";}
//- create option line
			$optionLine="<option value=$optionValue_formformat$selectedInsert>$optionLabels</option>";
			$returnAry[]="$optionLine\n";
			//xxx - wip
			//echo "name: $formName, optvalue: $optionValue, xrefbool: $formXref<br>";//xxx
			if ($formXref){
				$formXrefValue=$optionRow[$formXrefName];
				$this->formAry_js[]="FormObj.addOptionXref('$formName','$optionValue','$formXrefValue');\n";
			}
		}
		$returnAry[]="</select>$postTable\n";
		$base->DebugObj->printDebug("-rtn:buildSelect",0); //xx (f)
		return $returnAry;
	}
//=============================================================
	function buildInputSelect($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("FormObj:buildInputSelect($formElAry,$dataAry,$tableFormat,'base')",0);
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$label=$formElAry['formelementlabel'];
		$class=$formElAry['formelementclass'];
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($class != ''){
			$classInsert="class=\"$class\"";
		} else { $classInsert='';}
		$errorMsg=""; //???
		$passAry=$this->setTableFields($name,$label,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
// - text input is <name>_text, select is <name>_select
		$textName=$name.'_text';
		$selName=$name.'_select';
		$returnAry[]="$preTable<input type=\"text\" name=\"$textName\" $classInsert id=\"$textName\" onkeyup=\"checkInput('$textName','$selName');\">$postTable";
		$formElAry['onkeyup']="checkInputLabel('attributeId');";
		$formElAry['formelementname'].="_select";
		$subReturnAry = $this->buildSelect($formElAry,$dataAry,$tableFormat,&$base);
		$returnAry=array_merge($returnAry,$subReturnAry);
		$base->DebugObj->printDebug("-rtn:buildInputSelect",0); //xx (f)
		return $returnAry;
	}
//=============================================================
	function buildDisplay($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildDisplay($formElAry,$dataAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$oldTableFormat=$tableFormat;
		$tableFormat=99;
		$workAry=$this->buildInputHidden($formElAry,$dataAry,$tableFormat,&$base);
		$tableFormat=$oldTableFormat;
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$theData=$dataAry[$name];
		$value_raw=$formElAry['formelementvalue'];	
		$useSpan_raw=$formElAry['formelementspan'];
		$useSpan=$base->UtlObj->returnFormattedData($useSpan_raw,'boolean','internal');
		$value=$base->UtlObj->returnFormattedString($value_raw,&$base);
		if ($theData != ""){$value=$theData;}
		//echo "name: $name, value_raw: $value_raw, value: $value, thedate: $theData<br>";//xxx
		$label=$formElAry['formelementlabel'];
		$cols=$formElAry['formelementcols'];
		$class=$formElAry['formelementclass'];
		if ($class != NULL){$classInsert="class=\"$class\"";}
		else {$classInsert=NULL;}
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id == NULL){$id=$name;}
		$idInsert="id=\"$id\"";
		if ($cols != ""){$colIns=" size=\"$cols\" ";}
		else {$colIns="";}
		$events_raw=$formElAry['formelementeventattributes'];
		$events=$base->UtlObj->returnFormattedString($events_raw,&$base);
		$errorMsg=$base->errorProfileAry['columnerrorary'][$name];
		$passAry=$this->setTableFields($name,$label,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$preTableSpan=$passAry['pretablespan'];
		$postTable=$passAry['posttable'];
		$postTableSpan=$passAry['posttablespan'];
		if ($useSpan){$usePreTable=$preTableSpan;$usePostTable=$postTableSpan;$useElement='span';}
		else {$usePreTable=$preTable;$usePostTable=$postTable;$useElement='div';}
		$theLine="<$useElement $classInsert $idInsert $events>$value</$useElement>";
		$returnAry[]=	"$usePreTable$workAry[0]$theLine$usePostTable";
	  	$base->DebugObj->printDebug("-rtn:buildDisplay",0); //xx (f)
		return $returnAry;
	}
//=======================================================================
	function buildSimpleDisplay($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildSimpleDisplay($formElAry,$dataAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$oldTableFormat=$tableFormat;
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$theData=$dataAry[$name];
		$value_raw=$formElAry['formelementvalue'];	
		$useSpan_raw=$formElAry['formelementspan'];
		$useSpan=$base->UtlObj->returnFormattedData($useSpan_raw,'boolean','internal');
		$value=$base->UtlObj->returnFormattedString($value_raw,&$base);
		if ($theData != ""){$value=$theData;}
		//echo "name: $name, value_raw: $value_raw, value: $value, thedate: $theData<br>";//xxx
		$label=$formElAry['formelementlabel'];
		$cols=$formElAry['formelementcols'];
		$class=$formElAry['formelementclass'];
		if ($class != NULL){$classInsert="class=\"$class\"";}
		else {$classInsert=NULL;}
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id == NULL){$id=$name;}
		$idInsert="id=\"$id\"";
		if ($cols != ""){$colIns=" size=\"$cols\" ";}
		else {$colIns="";}
		$events_raw=$formElAry['formelementeventattributes'];
		$events=$base->UtlObj->returnFormattedString($events_raw,&$base);
		$errorMsg=$base->errorProfileAry['columnerrorary'][$name];
		$passAry=$this->setTableFields($name,$label,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$preTableSpan=$passAry['pretablespan'];
		$postTable=$passAry['posttable'];
		$postTableSpan=$passAry['posttablespan'];
		if ($useSpan){$usePreTable=$preTableSpan;$usePostTable=$postTableSpan;$useElement='span';}
		else {$usePreTable=$preTable;$usePostTable=$postTable;$useElement='div';}
		$theLine="<$useElement $classInsert $idInsert $events>$value</$useElement>";
		$returnAry[]=	"$usePreTable$theLine$usePostTable";
	  	$base->DebugObj->printDebug("-rtn:buildDisplay",0); //xx (f)
		return $returnAry;
	}
//===============================================================
	function buildJsButton($formElAry,$dataAry, $tableFormat,$base){
		$base->DebugObj->printDebug("buildJsButton($formElAry,$dataAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$oldTableFormat=$tableFormat;
		$returnAry=array();
		$name=$formElAry['formelementname'];
		$useSpan_raw=$formElAry['formelementspan'];
		$useSpan=$base->UtlObj->returnFormattedData($useSpan_raw,'boolean','internal');
	//- label
		$label_raw=$formElAry['formelementlabel'];	
		$label=$base->UtlObj->returnFormattedString($label_raw,&$base);
//- class
		$class=$formElAry['formelementclass'];
		if ($class != NULL){$classInsert="class=\"$class\"";}
		else {$classInsert=NULL;}
//- id
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id == NULL){$id=$name;}
		$idInsert="id=\"$id\"";
		$events_raw=$formElAry['formelementeventattributes'];
		$events=$base->UtlObj->returnFormattedString($events_raw,&$base);
		$errorMsg=$base->errorProfileAry['columnerrorary'][$name];
		//echo "tableformat: $tableFormat<br>";//xxxd
		$nullLabel='&nbsp;';
		$passAry=$this->setTableFields($name,$nullLabel,$errorMsg,$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$preTableSpan=$passAry['pretablespan'];
		$postTable=$passAry['posttable'];
		$postTableSpan=$passAry['posttablespan'];
		if ($useSpan){$usePreTable=$preTableSpan;$usePostTable=$postTableSpan;$useElement='span';}
		else {$usePreTable=$preTable;$usePostTable=$postTable;$useElement='div';}
		$theLine="<$useElement $classInsert $idInsert $events>$label</$useElement>";
		$returnAry[]="$usePreTable$theLine$usePostTable";
	  	$base->DebugObj->printDebug("-rtn:buildDisplay",0); //xx (f)
		return $returnAry;
	}
//===============================================================
	function buildUrl($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("FormObj:buildUrl",0);
		$htmlElementAry=array();
		$htmlElementAry['label']=$formElAry['formelementlabel'];
		$htmlElementAry['htmlelementclass']=$formElAry['formelementclass'];
		$formElementId_raw=$formElAry['formelementid'];
		$formElementId=$base->UtlObj->returnFormattedStringDataFed($formElementId_raw,$dataAry,&$base);
		//echo "formelementid: $formElementId<br>";//xxxf
		//$base->DebugObj->printDebug($formElAry,1,'xxxf');
		$htmlElementAry['htmlelementid']=$formElementId;
		$htmlElementAry['joblink']=$formElAry['formelementvalue'];
		$formElEvents_raw=$formElAry['formelementeventattributes'];
		$formElEvents=$base->UtlObj->returnFormattedStringDataFed($formElEvents_raw,$dataAry,&$base);
		//$formElEvents=$formElEvents_raw;
		$htmlElementAry['htmlelementeventattributes']=$formElEvents;
		$theName_raw=$formElAry['formelementname'];
		$theNameAry=explode('_',$theName_raw);
		$theName=$theNameAry[0];
		$base->paramsAry[$theName]=$dataAry[$theName];
		//xxxf22
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf: paramsary');
		//$base->DebugObj->printDebug($dataAry,1,'xxxf1: dataary');
		//$base->DebugObj->printDebug($htmlElementAry,1,'xxxf1: htmlelementary');
		//$base->DebugObj->printDebug($htmlElementAry,1,'xxxf');//xxxf
		$workAry=$base->HtmlObj->buildUrl($htmlElementAry,&$base);
		//$base->DebugObj->printDebug($workAry,1,'xxxf');
		//exit();
		$class=$formElAry['formelementclass'];
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		$passAry=$this->setTableFields('','','',$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$returnAry=array();
		$returnAry[]=$preTable.$workAry[0].$postTable;
		//$base->DebugObj->printDebug($returnAry,1,'xxx');
		$base->DebugObj->printDebug("-rtn:buildUrl",0); //xx (f)
		return $returnAry;
	}
//==================================================================xxx
//- if regular call then need to do table manipulation, else not
	function buildInputHidden($formElAry,$dataAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildInputHidden($formElAry,$dataAry,$tableFormat,'base')",0);
		$returnAry=array();
//- name, usename, usenametoread
		$formElName=$formElAry['formelementname'];
		$checkKey='formelementnamesuffix';
		if (array_key_exists($checkKey,$formElAry)){
			$formElNameSuffix=$formElAry['formelementnamesuffix'];
			$useFormElName=$formElName."_".$formElNameSuffix;
		}
		else {$useFormElName=$formElName;}
		if (array_key_exists('formelementname_use',$formElAry)){
			$useFormElNameToRead=$formElAry['formelementname_use'];
		}
		else {$useFormElNameToRead=$formElName;}
		$label='';
//- id
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id!=null){$idInsert="id=\"$id\"";}
		else {$idInsert=null;}
//- class
		$class=$formElAry['formelementclass'];
		if ($class!=null){$classInsert="class=\"$class\"";}
		else {$classInsert=null;}
//- value
		$value_raw=$formElAry['formelementvalue'];	
		//- try to get out of data file first
		$value_mid=$base->UtlObj->returnFormattedString($value_raw,&$base);
		$value=$value_mid;
		$value=$base->UtlObj->returnFormattedStringDataFed($value_mid,$dataAry,&$base);
		if ($value==null){
			$value=$dataAry[$useFormElNameToRead];
		}
//- table fields
		//echo "name: $formElName, usename: $useFormElNameToRead, thedata: $theData, value: $value<br>";//xxx
		$mode=$formElAry['mode'];
		if ($mode == 'noprepost'){$preTable=null;$postTable=null;}
		else {
			$passAry=$this->setTableFields("","","",$tableFormat,$class,$id,$formElAry,&$base);
			$preTable=$passAry['pretable'];
			$preTable=str_replace('&nbsp;',null,$preTable);
			$postTable=$passAry['posttable'];				
		}
		$hiddenAreaInsert =	"$preTable<input type=\"hidden\" name=\"$useFormElName\" $idInsert $classInsert value=\"$value\">$postTable\n";
		$returnAry[]=$hiddenAreaInsert;
		//if ($useFormElName == 'cssprofileid'){
			//$base->FileObj->writeLog('jefftest3',"formname: $this->formName, formelname: $formElName, useformelname: $useFormElName, insert: $hiddenAreaInsert",&$base);
		//}
		$base->DebugObj->printDebug("-rtn:buildInputHidden",0); //xx (f)
		return $returnAry;
	}
//===================================
	function buildFormLine($base){
		$base->DebugObj->printDebug("FormObj:buildFormLine('base')",0);
		//echo "xxxd22";
		$formAry=$base->formProfileAry;		
		$formAction=$formAry['formaction'];
		$formId_raw=$formAry['formid'];
		$formId=$base->UtlObj->returnFormattedString($formId_raw,&$base);
		$formClass=$formAry['formclass'];	
		$formLineAry=array();
		$formLineAry[0]="<form";
		if ($formAction != ""){$formLineAry[0].=" action=$formAction";}
		if ($formId != ""){$formLineAry[0].=" id=$formId";}
		if ($formClass != ""){$formLineAry[0].=" class=$formClass";}
		//- enctype
		$formEncType=$formAry['formenctype'];
		if ($formEncType==null){$formEncType="text/plain";}
		$formLineAry[0].=" enctype=\"$formEncTye\"";
		//- target
		$formTarget=$formAry['formtarget'];
		if ($formTarget != null){$formLineAry[0].=" target=\"$formTarget\"";}
		//- end
		$formLineAry[0].=">";
		if ($formName='insertpictureprofile'){
			$base->DebugObj->printDebug($formLineAry,1,'xxxf');exit();//xxxf
		}
// ----- do type="hidden" name="operation"
		$subtype="hidden";
		$name="operation";
		//$value=$base->jobProfileAry['operationstr'];
		$value="doit";
		$tempAry=array("formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","mode"=>"noprepost");
		$hiddenInputAry=$this->buildInputHidden($tempAry,"",0,&$base);
		$formLineAry[1]=$hiddenInputAry[0];
// ----- do <input type="hidden" name="job"
		$subtype="hidden";
		$name="job";
		$value=$base->jobProfileAry['jobstr'];
		$tempAry=array("formelementname"=>$name,"formelementvalue"=>$value,"formelementsubtype"=>"hidden","mode"=>"noprepost");
		$hiddenInputAry=$this->buildInputHidden($tempAry,"",0,&$base);
		$formLineAry[2]=$hiddenInputAry[0];
		$base->DebugObj->printDebug("-rtn:buildFormLine",0); //xx (f)
		return $formLineAry;
	}
//=============================================
//--------------------------------
	function buildButton($formElAry,$tableFormat,$base){
		$base->DebugObj->printDebug("FormObj:buildButton($formElAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$returnAry=array();
		$subtype=$formElAry['formelementsubtype'];
		$label_raw=$formElAry['formelementlabel'];
		$label=$base->UtlObj->returnFormattedString($label_raw,&$base);
		$class=$formElAry['formelementclass'];
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id != ''){$idInsert="id=\"$id\"";}
		else {$idInsert='';}
		$value=$formElAry['formelementvalue'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		if ($value != ''){$valueInsert="value=\"$value\"";}
		else {$valueInsert='';}
		$eventInsert_raw=$formElAry['formelementeventattributes'];
		$eventInsert=$base->UtlObj->returnFormattedString($eventInsert_raw,&$base);
//-		
		$passAry=$this->setTableFields("","","",$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$returnAry[]="$preTable<button type=\"$subtype\" $classInsert $idInsert $eventInsert>$label</button>\n$postTable";
		//$base->DebugObj->printDebug($returnAry,1,'brtn');//xxx
		$base->DebugObj->printDebug("-rtn:buildButton",0); //xx (f)
		return $returnAry;
	}
//--------------------------------
	function buildInputSubmit($formElAry,$dataAry,$tableFormat,&$base){
		$base->DebugObj->printDebug("FormObj:buildButton($formElAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$returnAry=array();
		$subtype=$formElAry['formelementsubtype'];
		//- label
		$label_raw=$formElAry['formelementlabel'];
		$label=$base->UtlObj->returnFormattedString($label_raw,&$base);
		//- id
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		//- class
		$class=$formElAry['formelementclass'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		//- event
		$eventInsert_raw=$formElAry['formelementeventattributes'];
		$eventInsert=$base->UtlObj->returnFormattedString($eventInsert_raw,&$base);
		//- value
		$value_raw=$formElAry['formelementvalue'];
		$value=$base->UtlObj->returnFormattedString($value_raw,&$base);
		if ($value == null){$valueInsert=null;}
		else {$valueInsert="value=\"$value\"";}
		//- name
		$formElName=$formElAry['formelementname'];
		$nameInsert="name=\"$formElName\"";
		//- environment
		$passAry=$this->setTableFields("","","",$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		if ($label != null){
			$returnAry[]="$preTable<input type=\"submit\" $nameInsert $classInsert $eventInsert $valueInsert >$label</input>\n$postTable";
		}
		else {
			$returnAry[]="$preTable<input type=\"submit\" $nameInsert $classInsert $eventInsert $valueInsert />\n$postTable";
		}
		//$base->DebugObj->printDebug($returnAry,1,'brtn');//xxx
		$base->DebugObj->printDebug("-rtn:buildButton",0); //xx (f)
		return $returnAry;
	}
//--------------------------------xxxd22
	function buildIframe($formElAry,$dataAry,$tableFormat,&$base){
		$base->DebugObj->printDebug("FormObj:buildIframe($formElAry,$dataAry,$tableFormat,'base')",0);
		$specificTableFormat=$formElAry['formelementtableformat'];
		if ($specificTableFormat != null){$tableFormat=$specificTableFormat;}
		$returnAry=array();
		//-label
		$label_raw=$formElAry['formelementlabel'];
		$label=$base->UtlObj->returnFormattedString($label_raw,&$base);
		//-id
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id == null){$idInsert=null;}
		else {$idInsert="id=\"$id\"";}
		//-class
		$class=$formElAry['formelementclass'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		//-value
		$value=$formElAry['formelementvalue'];
		if ($value != ''){$valueInsert="value=\"$value\"";}
		else {$valueInsert='';}
		//- event
		$eventInsert_raw=$formElAry['formelementeventattributes'];
		$eventInsert=$base->UtlObj->returnFormattedString($eventInsert_raw,&$base);
		//- src
		$formElSrc=$formElAry['formelementsrc'];
		if ($formElSrc == null){$srcInsert=null;}
		else {$srcInsert="src=\"$formElSrc\"";}
		//- style
		$formElStyle=$formElAry['formelementstyle'];
		if ($formElStyle==null){$styleInsert=null;}
		else {$styleInsert="style=\"$formElStyle\"";}
		//- name
		$formElementName=$formElAry['formelementname'];
		$nameInsert="name=\"$formElementName\"";
//-		
		$passAry=$this->setTableFields("","","",$tableFormat,$class,$id,$formElAry,&$base);
		$preTable=$passAry['pretable'];
		$postTable=$passAry['posttable'];
		$iFrameLine="<iframe $idInsert $nameInsert $srcInsert $styleInsert></iframe>";
		$returnAry[]="$preTable\n$iFrameLine\n$postTable\n";
		//$base->DebugObj->printDebug($returnAry,1,'brtn');//xxxd22
		//exit();//xxxd22
		$base->DebugObj->printDebug("-rtn:buildButton",0); //xx (f)
		return $returnAry;
	}
//-------------------------------
	function buildComment($comment,$base){
		$base->DebugObj->printDebug("DbObj:buildComment($comment,'base')",0); //xx (h)
		$comment=str_replace('<','{',$comment);
		$comment=str_replace('>','}',$comment);
		$returnAry=array(0=>"<!-- $comment -->\n");
		$base->DebugObj->printDebug("-rtn:buildComment",0); //xx (f)
		return $returnAry;
	}
//-------------------------------
	function buildJsSubmit($formElAry,$tableFormat,$base){
		$base->DebugObj->printDebug("buildJsSubmit($formElAry,$tableFormat,'base')",0);
		//$base->DebugObj->printDebug($formElAry,1,'formelary');//xxx
		$formName=$formElAry['formname'];
		$returnAry=array();
		$returnAry[]="<a href=\"javascript:document.$formName.submit();\">\n";
		$imageName=$formElAry['formelementsubtype'];
		$imageSource=$base->imageProfileAry[$imageName]['imagesource'];
		$imageClass=$base->imageProfileAry[$imageName]['imageclass'];
		$urlAry=array('joblink'=>$imageSource,'htmlelementimagename'=>$imageName,'imageclass'=>$imageClass);
		$workAry=$base->HtmlObj->buildOldImg($urlAry,&$base);
		$returnAry=array_merge($returnAry,$workAry);
		$returnAry[]='</a>'."\n";
		//$base->DebugObj->printDebug($returnAry,1,'workaryxxxd');//xxx
		$base->DebugObj->printDebug("-rtn:buildJsSubmit",0);
		return $returnAry;
	}
//------------------------------- 
	function setTableFields($name,$label,$errorMsg,$tableTypeNo,$theClass,$theId,$formElAry,$base){
		$base->DebugObj->printDebug("setTableFields($name,$label,$errorMsg,$tableTypeNo,$theClass,$theId,'base')",0);
		if ($theClass != null){
			$theClassInsert="class=\"$theClass\"";
			$theLabelSuffix="_label";
			$theLabelClassInsert="class=\"$theClass$theLabelSuffix\"";
			$theErrorSuffix="_errormsg";
			$theErrorClassInsert="class=\"$theClass$theErrorSuffix\"";
		}
		else {
			$theClassInsert=null;$theLabelClassInsert=null;$theErrorClassInsert=null;
		}
		//echo "label: $label, no: $tableTypeNo<br>";//xxx
		$errorMsgName=$name.'_errormsg';
		$errorMsgNameId=$name.'_errormsgid';
		switch ($tableTypeNo){
		case 1:
			$preTable="<tr>\n<td><p $theLabelClassInsert>$label</p></td>\n<td>";
			$postTable="</td></tr>\n";
			break;
		case 2:
			if ($errorMsg == ""){$errorMsg = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';}
			$preTable="<tr><td><blink><color=red><div name=\"$errorMsgName\" id=\"$errorMsgNameId\" $theErrorClassInsert>$errorMsg</div></color></blink></td><td><p $theLabelClassInsert>$label</p></td><td>\n";
			$postTable="</td></tr>\n";
			break;
		case 3:
			if ($errorMsg == ""){$errorMsg = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';}
			if ($this->leftToggle){
			$preTable="<tr><td>\n<blink><color=red><div name=\"$errorMsgName\" id=\"$errorMsgNameId\" $theErrorClassInsert>$errorMsg</div></color></blink></td><td><p $theLabelClassInsert>$label</p></td><td>\n";
			$postTable="</td>\n";
			$this->leftToggle=false;
			}
			else {
			$preTable="<td><blink><color=red>\n<div name=\"$errorMsgName\" id=\"$errorMsgNameId\" $theErrorClassInsert>$errorMsg</div></color></blink></td>\n<td><p $theLabelClassInsert>$label</p></td>\n<td>";
			$postTable="</td></tr>\n";
			$this->leftToggle=true;
			}
			break;
		case 4:
			$preTable="<tr><td><p $theLabelClassInsert>$label</p></td></tr><td>\n";
			$postTable="</td></tr>\n";
			break;
		case 5:
			if ($label != NULL){$preTable="<td><p $theLabelClassInsert>$label</p></td><td>";}
			else $preTable="<td>";
			$postTable="</td>";
			break;
		case 6:
			$labelAttach="_label";
			$contentAttach="_content";
			$mainAttach="_main";
			$errorAttach="_errormsg";
			if ($theClass==null){$theClass=$name;}
			if ($theClass != NULL){
				$classMainInsert="class=\"$theClass$mainAttach\"";
				$classLabelInsert="class=\"$theClass$labelAttach\"";
				$classContentInsert="class=\"$theClass$contentAttach\"";
				$classErrorInsert="class=\"$theClass$errorAttach\"";
			}
			else {
				$classMainInsert=NULL;
				$classLabelInsert=NULL;
				$classContentInsert=NULL;
				$classErrorInsert=NULL;
			}
			if ($theId != NULL){
				$idMainInsert="id=\"$theId$mainAttach\"";
				$idLabelInsert="id=\"$theId$labelAttach\"";
				$idContentInsert="id=\"$theId$contentAttach\"";
				$idErrorInsert="id=\"$theId$errorAttach\"";
			}
			else {
				$idMainInsert=NULL;
				$idLabelInsert=NULL;
				$idContentInsert=NULL;
				$idErrorInsert=NULL;
			}
			$preTable="<!-- start element $name -->\n";
			$preTable.="<div $classMainInsert $idMainInsert>\n";
			$preTable.="<span $classErrorInsert $idErrorInsert></span>\n";
			$preTable.="<span $classLabelInsert $idLabelInsert>$label</span>\n";
			$preTable.="<span $classContentInsert $idContentInsert>\n";
			$postTable="</span>\n</div>\n<!-- end element -->\n";
			$preTableSpan="<!-- start element $name -->\n";
			$preTableSpan.="<span $classMainInsert $idMainInsert>\n";
			$preTableSpan.="<span $classErrorInsert $idErrorInsert></span>\n";
			$preTableSpan.="<span $classLabelInsert $idLabelInsert>$label</span>\n";
			$preTableSpan.="<span $classContentInsert $idContentInsert>\n";
			$postTableSpan="</span>\n</span>\n<!-- end element -->\n";
			break;
		case 7:
			$preTable="<!--- start element $name -->\n";
			$preTable.="<tr><td colspan=\"3\"><table class=\"$theClass\"><tr><td class=\"$theClass\">\n";
			$postTable="</td>";
			break;
		case 8:
			$preTable="<!--- middle element $name -->\n";
			$preTable.="<td class=\"$theClass\">\n";
			$postTable="</td>\n";
			break;
		case 9:
			$preTable="<!--- end element $name -->\n";
			$preTable.="<td class=\"$theClass\">\n";
			$postTable="</td></tr></table></tr>\n";
			break;
		case 10:
			// use col row
			$colSpan=$formElAry['formelementtablecolspan'];
			$rowSpan=$formElAry['formelementtablerowspan'];
			if ($colSpan != null){$colSpanInsert="colspan=\"$colSpan\"";}
			else {$colSpanInsert=null;}
			if ($rowSpan != null){$rowSpanInsert="rowspan=\"$rowSpan\"";}
			else {$rowSpanInsert=null;}
			$labelAttach="_label";
			$contentAttach="_content";
			$mainAttach="_main";
			$errorAttach="_errormsg";
			if ($theClass != NULL){
				$classMainInsert="class=\"$theClass$mainAttach\"";
				$classLabelInsert="class=\"$theClass$labelAttach\"";
				$classContentInsert="class=\"$theClass$contentAttach\"";
				$classErrorInsert="class=\"$theClass$errorAttach\"";
			}
			else {
				$classMainInsert=NULL;
				$classLabelInsert=NULL;
				$classContentInsert=NULL;
				$classErrorInsert=NULL;
			}
			if ($theId != NULL){
				$idMainInsert="id=\"$theId$mainAttach\"";
				$idLabelInsert="id=\"$theId$labelAttach\"";
				$idContentInsert="id=\"$theId$contentAttach\"";
				$idErrorInsert="id=\"$theId$errorAttach\"";
			}
			else {
				$idMainInsert=NULL;
				$idLabelInsert=NULL;
				$idContentInsert=NULL;
				$idErrorInsert=NULL;
			}
			$preTable="<td $classMainInsert $idMainInsert $colSpanInsert $rowSpanInsert>\n";
			$preTable.="<!-- start element $name -->\n";
			$preTable.="<span $classLabelInsert $idLabelInsert>$label</span><span $classContentInsert $idContentInsert>\n";
			$postTable="</span>\n</td>\n";
			break;
		case 11:
			$preTable="";
			$postTable="";
			break;
		default:
			$preTable="";
			$postTable="";
		}
		$returnAry=array('pretable'=>$preTable,'pretablespan'=>$preTableSpan,'posttable'=>$postTable,'posttablespan'=>$postTableSpan);
		$base->DebugObj->printDebug("-rtn:setTableFields",0); //xx (f)
		return $returnAry;
	}
//======================================================================== !!! this must be made more generic(jobname used?)
	function buildInputCheckBox($formElAry,$dataAry,$tableFormat,&$base){
		$base->DebugObj->printDebug("FormObj:buildInputCheckBox",0);
		unset ($base->errorProfileAry['converterror']);
		$returnAry=array();
		$checkBoxName=$formElAry['formelementname'];
		$useOtherDb_raw=$formElAry['formelementotherdb'];
		$useOtherDb=$base->UtlObj->returnFormattedData($useOtherDb_raw,'boolean','internal',&$base);
		$useSql_raw=$formElAry['formelementusesql'];
		$checkBoxValue=$formElAry['formelementvalue'];
		$checkBoxLabel=$formElAry['formelementlabel'];
		$checkBoxClass=$formElAry['formelementclass'];
		if ($checkBoxClass != NULL){$checkBoxClassInsert="class=\"$checkBoxClass\"";}
		else {$checkBoxClassInsert=NULL;}
		//$base->DebugObj->printDebug($formElAry,1,'xxx');
		//echo "name: $checkBoxName, usesql: $useSql_raw, useotherdb: $useOtherDb_raw<br>";//xxxd
		$useSql=$base->UtlObj->returnFormattedData($useSql_raw,'boolean','internal',&$base);
		if ($useSql){
		if ($useOtherDb){
			$dbNo=$formElAry['formelementotherdbno'];
			$theQuery_raw=$formElAry['formelementoptionsql'];
			$theQuery=$base->UtlObj->returnFormattedString($theQuery_raw,&$base);
			if ($dbNo>0 && $dbNo<10){
				$theDbConn=$base->System001Obj->getClientConn($dbNo,&$base);
				if ($theDbConn != NULL){
					$errorReturn=$base->errorProfileAry['converterror'];
					if ($errorReturn==NULL){
						$result=$base->ClientObj->queryClientDbTable($theQuery,$theDbConn,'read',&$base);
					}
					else {$result=NULL;}
					//echo "query: $theQuery<br>";//xxxd
				}
				else {$result=NULL;}
			}
			else {$result=NULL;}
			$passAry=array();
			$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$checkBoxCnt=count($dataAry);
			$classInsert=NULL;
			$idInsert=NULL;
			$returnAry[]="<table $classInsert $idInsert>\n";
			for ($ctr=0;$ctr<$checkBoxCnt;$ctr++){
				$jobName=$dataAry[$ctr]['jobname'];
				$returnAry[]="<tr><td>\n";
				$useCheckBoxName=$checkBoxName.'_'.$ctr;
				$checkBox_html="<input name=\"$useCheckBoxName\" type=\"checkbox\" value=\"$jobName\">";
				$returnAry[]="$checkBox_html</td><td><span $checkBoxClassInsert>$jobName</span>\n";
				$returnAry[]="</td></tr>\n";	
			}
			$returnAry[]="</table>";
			//$base->DebugObj->printDebug($dataAry,1,'dataary');
		} // end useotherdb
		else {
			$theQuery_raw=$formElAry['formelementoptionsql'];
			$theQuery=$base->UtlObj->returnFormattedString($theQuery_raw,&$base);
			$result=$base->DbObj->queryTable($theQuery,'read',&$base);
			$passAry=array();
			$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$checkBoxCnt=count($dataAry);
			$classInsert=NULL;
			$idInsert=NULL;
			$returnAry[]="<table $classInsert $idInsert>\n";
			for ($ctr=0;$ctr<$checkBoxCnt;$ctr++){
				$jobName=$dataAry[$ctr]['jobname'];
				$returnAry[]="<tr><td>\n";
				$useCheckBoxName=$checkBoxName.'_'.$ctr;
				$theLabel=$base->UtlObj->returnFormattedStringDataFed($checkBoxLabel,$dataAry[$ctr],&$base);
				$theValue=$dataAry[$ctr][$checkBoxValue];
				$checkBox_html="<input name=\"$useCheckBoxName\" type=\"checkbox\" value=\"$theValue\">";
				$returnAry[]="$checkBox_html</td><td><span $checkBoxClassInsert>$theLabel</span>\n";
				$returnAry[]="</td></tr>\n";	
			}
			$returnAry[]="</table>";
		} // end else useotherdb
		} // end usesql
		else {
			$checkBox_html="<input name=\"$checkBoxName\" type=\"checkbox\" value=\"$checkBoxValue\">";
			$returnAry[]="<tr><td>\n";
			$returnAry[]="$checkBox_html</td><td><span $checkBoxClassInsert>$checkBoxLabel</span>\n";
			$returnAry[]="</td></tr>\n";
		} // end else usesql
		$base->DebugObj->printDebug("-rtn:buildInputCheckBox",0); //xx (f)
		return $returnAry;
	}
//================================
	function loadFormFragments($base){
		foreach ($base->formProfileAry as $formName=>$formAry){
			$formType=$formAry['formtype'];
			$dbControlsAry=array();
			if ($formType=='fragment'){
				$formFragmentAry=$this->buildForm($formName,$dbControlsAry,&$base);	
				//- below need to make the form fragment code be able to be entered via javascript
				$formFragment_raw=implode("",$formFragmentAry);
				//$formFragment=str_replace('"','&#34;',$formFragment_raw);
				$formFragment=str_replace('"','\"',$formFragment_raw);
				$formFragment=str_replace("'","\'",$formFragment);
				$formFragment=str_replace("\n",'&#10;',$formFragment);
				$this->formAry_js[]="FormObj.addFragment('$formName','$formFragment');\n";
			}	
		}	
	}
//================================
	function buildContainer($formElAry,$dataAry,$tableFormat,$base){
		$dq='"';$sq="'";$sufh="_heading";$suff="_footing";$sufc="_content";
		$returnAry=array();
		$class=$formElAry['formelementclass'];
		$id=$formElAry['formelementid'];
		$id=$base->UtlObj->returnFormattedString($id,&$base);
		if ($id==NULL){$id='formobj:buildcontainer:none';}
		if ($class==NULL){$class='formobj:buildcontainer:none';}
		$returnAry[]="<div class=$dq$class$dq id=$dq$id$dq>";
		$returnAry[]="<div class=$dq$class$sufh$dq id=$dq$id$sufh$dq />";
		$returnAry[]="<div class=$dq$class$sufc$dq id=$dq$id$sufc$dq />";
		$returnAry[]="<div class=$dq$class$suff$dq id=$dq$id$suff$dq />";
		$returnAry[]="</div>";
		return $returnAry;
	}
//-----------------------------------------------------------
	function writeDbFromAjaxMultiple($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd');
		$sendData=$base->paramsAry['senddata'];
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		$cnt=count($sendDataAry);
		for ($lp=0;$lp<$cnt;$lp++){
			$theRow=$sendDataAry[$lp];
			$theRowAry=explode('|',$theRow);
			$theKey=$theRowAry[0];
			$theRest=$theRowAry[1];
			$workAry[$theKey]=$theRest;
		}
		$dbTableName=$workAry['dbtablename'];
		$dataDef=$workAry['datadef'];
		$allData=$workAry['tabledata'];
		$dataDefAry=explode('~',$dataDef);
		$theDataArys=explode('~newform~',$allData);
		$dataCnt=count($theDataArys);
		$writeRowsAry=array();
		$writeRowAry=array();
		for ($dataLp=0;$dataLp<$dataCnt;$dataLp++){
			$theData=$theDataArys[$dataLp];
			$theDataAry=explode('~',$theData);
			$noDefs=count($dataDefAry);
			for ($data2Lp=0;$data2Lp<$noDefs;$data2Lp++){
				$aDataDef=$dataDefAry[$data2Lp];
				$aDataValue=$theDataAry[$data2Lp];
				$writeRowAry[$aDataDef]=$aDataValue;
			}
			$writeRowsAry[]=$writeRowAry;
		}
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
		echo "okdonothing";
	}
//==========================================
	function doDebug($theMsg,$base){
		//$base->FileObj->turnOnLog(&$base);//xxxf
		//$base->FileObj->writeLog('debug',$theMsg,&$base);
		//$base->FileObj->turnOffLog(&$base);//xxxf
	}
//---
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//---
	function incCalls(){$this->callNo++;}
}
?>
