<?php
class ajaxObject {
	//dont use this
	var $statusMsg;
	var $callNo = 0;
	var $ajaxLines=array();
//====================================================
	function ajaxObject() {
		$this->incCalls();
		$this->statusMsg='tag Object is fired up and ready for work!';
	}
//====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//=====================================================
	function retrieveAjaxLinesdeprecated($paramFeed,$base){
		$base->debugObj->printDebug("ajaxObj:retrieveAjaxLinesdeprecated",0);
		$returnAry=array();
	//- css
		$returnAry=$this->ajaxLines['csslines'];
	//- calendar
		$subReturnAry=$base->calendarObj->retrieveCalendarAjax(&$base);//xxx
		$returnAry=array_merge($returnAry,$subReturnAry);
	//-
		$base->debugObj->printDebug("-rtn:ajaxObj:retrieveAjaxLinesdeprecated",0); //xx (f)
		return $returnAry;
	}
//====================================================
	function incCalls(){$this->callNo++;}
//====================================================
	function getContainerForAjax($paramFeed,$base){
		$base->debugObj->printDebug("ajaxObj:getContainerForAjax",0);	
		$containerName=$paramFeed['param_1'];
		$containerNameAry=explode('_',$containerName);
		$containerName=$containerNameAry[0];
//!!! only run if ajax (has container in paramsary)
		if ($containerName != null){
			$returnAry=array();
// --- container information
			$returnAry[]="\n!!container!!\n";
			$returnAry[]="containername|$containerName\n";
			$sessionName=$base->paramsAry['sessionname'];
			if ($sessionName != null){
				$returnAry[]="loadetc|sessionname|$sessionName\n";
			}
			$base->fileObj->writeLog('debug',"ajaxObj.getcontainerforajax: sessionname: $sessionName",&$base);
			$workAry=$base->containerObj->getContainer($containerName,&$base);
			$theInnards=$workAry['containerary'];
			//$base->debugObj->printDebug($workAry['containerary'],1,'xxxf1');
			$ctr=0;
			foreach ($workAry['containerary'] as $name=>$value){
				$returnAry[]="loadetc|$name|$value\n";
			}
			//echo "$returnAry[0], $returnAry[1], $returnAry[2], $returnAry[3],";//xxxf
			$theCnt=count($returnAry);
			//echo "cnt: $theCnt";
			//$base->debugObj->printDebug($returnAry,1,'xxxf2');
			$containerElementAry=$workAry['containerelementary'];
			$noEntries=count($containerElementAry);
			if ($noEntries==0){
				echo "container: $containerName, noentries: $noEntries<br>";
				exit();
			}
// --- container elements
//xxxf - why are we doing the below!!!!!!!!!!!!!!!!!!!
			foreach ($containerElementAry as $elementName=>$elementAry){
				$elementType=$elementAry['containerelementtype'];
				if ($elementType=='container'){
					$subWorkAry=$base->containerObj->getContainer($elementName,&$base);
					$subContainer=$subWorkAry['containerelementary'];
					if ($subContainer == null){
						echo "ajaxObj.getCssForAjax, containername: $containerName, element: $elementName<br>\n";
						echo "container type: $elementType, sub container is null!!<br>\n";
						exit();					
					}
					foreach ($subContainer as $subElementName=>$subElementAry){
						$subElementType=$subElementAry['containerelementtype'];
						if ($subElementType=='container'){
							$subWork2Ary=$base->containerObj->getContainer($subElementName,&$base);
							foreach ($subWork2Ary['containerelementary'] as $subElement2Name=>$subElement2Ary){
								$subElement2Type=$subElement2Ary['containerelementtype'];
								$workLine=$this->saveAjaxFields($subElement2Name,$subElement2Type,&$base);
								if ($workLine != null){$returnAry[]=$workLine."\n";}	
							}
						}
						else {
							$workLine=$this->saveAjaxFields($subElementName,$subElementType,&$base);
							if ($workLine != null){$returnAry[]=$workLine."\n";}		
						}
					}	
				}
				else {
					$workLine=$this->saveAjaxFields($elementName,$elementType,&$base);
					if ($workLine != null){$returnAry[]=$workLine."\n";}		
				}
			}
			$base->debugObj->printDebug("-rtn:ajaxObj:getContainerForAjax",0); //xx (f)
		}
		return $returnAry;
	}
//---------- css xxxf !!!! - now this selects by css name - many things may fail
	function getCssForAjax($paramAry,$base){
		$base->debugObj->printDebug("ajaxObj:getCssForAjax",0);
		//- can only be run if ajax - has container in paramsary
		$tst=$base->paramsAry['container'];
		if ($tst != null){
			//xxxf - cant do this anymore must do it manually: $returnAry=$this->getContainerForAjaxInternal(&$base);
			$returnAry=array();
			$returnAry[]='!!css!!'."\n";
			//$base->debugObj->printDebug($base->cssProfileAry,1,'xxxf');
			//$base->debugObj->printDebug($paramAry,1,'xxxf');
			$prefixSelect=$paramAry['param_1'];
			foreach ($base->cssProfileAry as $type=>$cssAry){
				if ($type != 'prefix'){
					foreach ($cssAry as $typeName=>$cssAry2){
						$prefix=$base->cssProfileAry['prefix'][$type][$typeName];
						//'getcssforajax' -> legacy setups
						if ($prefixSelect == $prefix || $prefixSelect=='getcssforajax' || $prefixSelect=='all'){
							//echo "$prefix, $type, $typeName<br>";//xxxf
							$cssAjaxLine=$type.'|'.$typeName;
							foreach ($cssAry2 as $element=>$cssAry3){
								$cssAjaxLine2=$cssAjaxLine.'|'.$element;
								$propertyStrg=null;$delim=null;
								foreach ($cssAry3 as $property=>$theValue){
									$propertyStrg.="$delim$property:$theValue";
									$delim='~';
								}	
								$cssAjaxLine2.='|'.$propertyStrg."\n";
								//echo $cssAjaxLine2.'<br>';
								$returnAry[]=$cssAjaxLine2;
							}
						}
					}
				}
			}
		}
		$base->debugObj->printDebug("-rtn:ajaxObj:getCssForAjax",0); //xx (f)
		return $returnAry;
	}
//===============================================================
	function getImageForAjax($paramsAry,&$base){
		$returnAry=array();
		/*
		 //xxxf - you must get the container manually
		$tst=$base->paramsAry['container'];
		if ($tst != null){
			$returnAry=$this->getContainerForAjaxInternal(&$base);
		}
		*/
		$imageName=$paramsAry['param_1'];
		$imageNameAry=explode('_',$imageName);
		$imageName=$imageNameAry[0];
		$imageAry=$base->imageProfileAry[$imageName];
		$returnAry[]="!!image!!\n";
		$returnAry[]="setname|$imageName\n";
//- get all standard setups for image
		foreach ($imageAry as $imageFieldName=>$imageFieldValue){
			$returnAry[]='loadetc|'.$imageFieldName.'|'.$imageFieldValue."\n";
		}
//- get css settings for image
		$imageClass=$imageAry['imageclass'];
		$imageId=$imageAry['imageid'];
		//$base->debugObj->printDebug($base->cssProfileAry,1,'xxxf');
		if ($imageClass != ''){
			$leftPos=$base->cssProfileAry['class'][$imageClass]['img']['left'];
			$returnAry[]="loadetc|left|$leftPos\n";
			$topPos=$base->cssProfileAry['class'][$imageClass]['img']['top'];
			$returnAry[]="loadetc|top|$topPos\n";
		}
		if ($imageId != ''){
			$leftPos=$base->cssProfileAry['id'][$imageClass]['img']['left'];
			if ($leftPos != NULL){$returnAry[]="loadetc|left|$leftPos\n";}
			$topPos=$base->cssProfileAry['id'][$imageClass]['img']['top'];
			if ($topPos != NULL){$returnAry[]="loadetc|top|$leftPos\n";}
		}
		return $returnAry;
	}
//===============================================================
	function saveAjaxFields($elementName,$elementType,$base){
		$base->debugObj->printDebug("ajaxObj:",0);
		//xxxf - why are we doing the below!!!!! There are overlays below
		if ($elementType == 'table' || $elementType=='form' || $elementType=='menu' || $elementType=='calendar'){
			$returnLine="loadetc|$elementType".'name'."|$elementName";
		}
		else $returnLine=null;
		$base->debugObj->printDebug("-rtn:ajaxObj:saveAjaxFields",0); //xx (f)
		return $returnLine;
	}
//========================================
function writeDbFromAjaxSimple($base){
//- this operation expects that a tag plugin ran saveparams
	$base->fileObj->writeLog('writedbfromajaxsimple',"-----xxxf0--------",&$base);
		$passAry=array();
		$passAry['param_1']='paramsave';
		$base->tagPlugin001Obj->getParams($passAry,&$base);
		//$base->debugObj->printDebug($base->paramsAry,1,'xxxf');
		//exit();//xxxf
		$ajaxFieldDelim="~";
		$ajaxLineDelim="`";
		$ajaxSubLineDelim="|";
		$sentData=$base->paramsAry['senddata'];
		$base->fileObj->writeLog('writedbfromajaxsimple',"sentdata: $sentData",&$base);
		foreach ($base->paramsAry as $name=>$value){
			$base->fileObj->writeLog('writedbfromajaxsimple',"paramsary: $name -> $value",&$base);
		}
		//echo "sentdata: $sentData";//xxxd
		$sentDataAry=explode($ajaxLineDelim,$sentData);
		$theLen=count($sentDataAry);
		$dbTableDataAry=array();
		$dbTableName='none';
		$dbTableDefs='none';
		$gotTableData=false;
		$gotTableDefs=false;
		$gotTableName=false;
		$statusKey='oknoalert';
		$statusMsg=null;
		$updStrg=null;
		$updDelim=null;
		$errorString=null;
		$formName=null;
		$paramNames=null;
		$paramValues=null;
		$updCtr=1;
		for ($lp=0;$lp<$theLen;$lp++){
			$lineAry=explode($ajaxSubLineDelim,$sentDataAry[$lp]);
			//echo "lineary: $lineAry[0], $lineAry[1]\n";//xxxd
			switch($lineAry[0]){
			case 'tabledata':
				$dbTableDataAry[]=$lineAry[1];
				$updStrg.="$updDelim$updCtr";
				$updDelim=':';
				$gotTableData=true;
			break;
			case 'dbtablename':
				$dbTableName=$lineAry[1];
				$gotTableName=true;
			break;
			case 'datadef':
				$dbTableDefs=$lineAry[1];
				$gotTableDefs=true;
			break;	
			case 'formname':
				$formName=$lineAry[1];
			break;
			case 'paramnames':
				//- already accessed in engineObj
			break;
			case 'paramvalues':
				//- already accessed in engineObj
			break;
			default:
				$errorString.=$lineAry[1].',';
			}
		}
		if ($gotTableData&&$gotTableDefs&&$gotTableName){
			$tableDefsAry=explode($ajaxFieldDelim,$dbTableDefs);
			$dbControlsAry=array();
			$dbControlsAry['dbtablename']=$dbTableName;
			$writeRowsAry=array();
			//$writeRowsTempIdAry=array();
			$theEmailMessage=null;
			$noRows=count($dbTableDataAry);
			$noCols=count($tableDefsAry);
			//$base->fileObj->writeLog('jefftest3',"xxxf2",&$base);
			for ($lp=0;$lp<$noRows;$lp++){
				$dataRow=$dbTableDataAry[$lp];
				$base->fileObj->writeLog('ajax',"datarow: $dataRow",&$base);//xxxd
				$dataRowAry_raw=explode($ajaxFieldDelim,$dataRow);
				$dataRowAry=array();
				$theDelim=null;
				for ($lp2=0;$lp2<$noCols;$lp2++){
					$dataRowAry[$tableDefsAry[$lp2]]=$dataRowAry_raw[$lp2];	
					$theEmailMessage.="$theDelim$tableDefsAry[$lp2]: $dataRowAry_raw[$lp2]";
					$theDelim=", \n";
				}
				$writeRowsAry[]=$dataRowAry;
			}
			$dbControlsAry['writerowsary']=$writeRowsAry;
			//$base->debugObj->printDebug($dbControlsAry,1,'xxxd');
			$base->dbObj->writeToDb($dbControlsAry,&$base);
			$checkStrg=$base->errorObj->retrieveAllErrors(&$base);
			$base->fileObj->writeLog('ajax','checkstrg: '+$checkStrg,&$base);//xxxd
			if ($checkStrg!=null){
				$statusKey='error';
				$statusMsg.=$checkStrg;	
			}
			else {
				if ($noRows<2){$rowWords='row has';}
				else {$rowWords='rows have';}
				$statusMsg.="$noRows $rowWords been updated!\n";
				$formJsCode=$base->formProfileAry[$formName]['formjscode'];
				if ($formJsCode == ''){$statusKey='oknoalert';}
				else {$statusKey=$formJsCode;}
			}
		}
		else {
				$errorFlg=false;
				$errMsg=null;
				if (!$gotTableData){$errMsg="There are no table rows to update!";}
				else if(!$gotTableDefs){$errMsg="There are no table definitions to use!";$errorFlg=true;}
				else if(!$gotTableName){$errMsg="The table name is missing from the transmission!";$errorFlg=true;}
				if ($errorFlg){$statusKey='error';}
				else {
					$formJsCode=$base->formProfileAry[$formName]['formjscode'];
					if ($formJsCode == ''){$statusKey='oknoalert';}
					else {$statusKey=$formJsCode;}
				}
				if ($errMsg != null){
					$statusMsg.=$errMsg."\n";
				}
		}
		$emailStuff="formname: $formName, ";
		if ($formName != null){
//--- send mail
			$formEmail=$base->formProfileAry[$formName]['formemail'];
			if ($formEmail != null){
				$theEmailSubject=$base->formProfileAry[$formName]['formemailsubject'];
				$emailStuff="formemail: $formEmail, theemailsubject: $theEmailSubject, theemailmessage: $theEmailMessage";
				$theEmailMessage=$base->utlObj->returnFormattedString($theEmailMessage,&$base);
				$base->utlObj->sendMail($formEmail,$theEmailSubject,$theEmailMessage,&$base);
			}
		}
		echo "$statusKey|$statusMsg|upd:$updStrg|email:$emailStuff";
	}
//==========================================================================
	function retrieveFormDbFromAjax($base){
		$sendData=$base->paramsAry['senddata'];
		//echo "senddata: $sendDate";//xxxd
		$jobName=$base->paramsAry['job'];
		$sendDataAry=explode('`',$sendData);
		$dbTableName=null;
		$dbTableKeyId=null;
		$workAry=array();
		foreach ($sendDataAry as $ctr=>$theValue){
			$valueAry=explode('|',$theValue);
			$theCode=$valueAry[0];
			$theValue1=$valueAry[1];
			$theValue2=$valueAry[2];
			$workAry[$theCode]=$theValue1;
		}
		$dbTableName=$workAry['dbtablename'];
		$formName=$workAry['formname'];
		if ($dbTableName!=null){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->dbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbKeyName=$dbControlsAry['keyname'];
			//foreach ($dbControlsAry as $one=>$two){echo "$one: $two\n";}
			$dbKeyValue=$workAry['dbkeyid'];
			if ($dbKeyValue != null){
				$query="select * from $dbTableName where $dbKeyName=$dbKeyValue";
				$work2Ary=$base->dbObj->queryTableRead($query,$passAry,&$base);
				$dbColumnNames=null;
				$dbColumnValues=null;
				$delim=null;
				foreach ($work2Ary[0] as $dbColumnName=>$dbColumnValue){
					$dbColumnNames.=$delim.$dbColumnName;
					$dbColumnValues.=$delim.$dbColumnValue;
					$delim='~';
				}
				$sendAjaxDataAry=array();
				$sendAjaxDataAry[]='formname|'.$formName;
				$sendAjaxDataAry[]='formcolumnnames|'.$dbColumnNames;
				$sendAjaxDataAry[]='formcolumnvalues|'.$dbColumnValues;
				$sendAjaxData=implode('`',$sendAjaxDataAry);
				echo $sendAjaxData;
			}
		}
	}
//==========================================================================
	function retrieveTableDbFromAjax($base){
		$sendData=$base->paramsAry['senddata'];
		$jobName=$base->paramsAry['job'];
		//echo "senddata: $sendData";exit();//xxxd
		$sendDataAry=explode('`',$sendData);
		//$base->debugObj->printDebug($sendDataAry,1,'xxxd');exit();//xxxd
		$workAry=array();
		foreach ($sendDataAry as $ctr=>$theValue){
			$valueAry=explode('|',$theValue);
			$theCode=$valueAry[0];
			$theValue=$valueAry[1];
			$workAry[$theCode]=$theValue;
		}
		$dbTableName=$workAry['dbtablename'];
//- need to get tablename
		$tableName=$workAry['tablename'];
		if ($dbTableName!=null && tableName != null){
			$selectKey1Insert=null;$selectKey2Insert=null;$selectKey3Insert=null;
			$sortKey1Insert=null;$sortKey2Insert=null;$sortKey3Insert=null;
			//
			$selectKey1=$workAry['selectkey1'];
			$selectKey1Ary=explode('~',$selectKey1Ary);
			$selectKey1=$selectKey1Ary[0];
			$selectKey1Value=$selectKey1Ary[1];
			//
			$selectKey2=$workAry['selectkey2'];
			$selectKey2Ary=explode('~',$selectKey2Ary);
			$selectKey2=$selectKey2Ary[0];
			$selectKey2Value=$selectKey2Ary[1];
			//
			$selectKey3=$workAry['selectkey3'];
			$selectKey3Ary=explode('~',$selectKey3Ary);
			$selectKey3=$selectKey3Ary[0];
			$selectKey3Value=$selectKey3Ary[1];
			//echo 'xxxd0';
			//
			if ($selectKey1 != null){$selectKey1Insert=" where $selectKey1='$selectKey1Value' ";}
			if ($selectKey2 != null){$selectKey2Insert=" ,$selectKey2='$selectKey2Value' ";}
			if ($selectKey3 != null){$selectKey3Insert=" ,$selectKey3='$selectKey3Value' ";}
			$sortKey1=$workAry['sortkey1'];
			$sortKey2=$workAry['sortkey2'];
			$sortKey3=$workAry['sortkey3'];
			if ($sortKey1 != null){$sortKey1Insert=" order by $sortKey1 ";}
			if ($sortKey2 != null){$sortKey2Insert=", $sortKey2 ";}
			if ($sortKey3 != null){$sortKey3Insert=", $sortKey3 ";}
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->dbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$query="select * from $dbTableName $selectKey1Insert$selectKey2Insert$selectKey3Insert ";
			if ($sortKey1Insert != null){$query.=" $sortKey1Insert$sortKey2Insert$sortKey3Insert";}
			$passAry=array();
			$workDataAry=$base->dbObj->queryTableRead($query,$passAry,&$base);
			$sendAjaxDataAry=array();
			$firstTime=true;
			$noRows=0;
			//echo 'xxxd0';
			foreach ($workDataAry as $ctr=>$workDataRowAry){
				$dbColumnNames=null;
				$dbColumnValues=null;
				$delim=null;
				foreach ($workDataRowAry as $dbColumnName=>$dbColumnValue){
					$dbColumnNames.=$delim.$dbColumnName;
					$dbColumnValues.=$delim.$dbColumnValue;
					$delim='~';
				}
//xxxd99
				if ($firstTime){
					$sendAjaxDataAry[]='tablename|'.$tableName;
					//$sendAjaxDataAry[]='dbtablename|'.$dbTableName;
					$sendAjaxDataAry[]='etc|datadef|'.$dbColumnNames;
					$firstTime=false;
				}
				$sendAjaxDataAry[]='dataary|'.$dbColumnValues;
				$noRows++;
			}
			$sendAjaxDataAry[]="etc|maxdataary|$noRows";
//- displayary of table
			$tableClass=$base->tableProfileAry[$tableName]['tableclass'];
			$columnProfileAry=$base->columnProfileAry[$tableName];
			$columnSortOrder=array();
			foreach ($columnProfileAry as $columnName=>$columnValueAry){
				$columnNo=$columnValueAry['columnno'];
				$columnSortOrder[$columnNo]=$columnName;
			}
			//echo 'xxxd-1';exit();//xxxd
			$noColumns=count($columnSortOrder);
			foreach ($workDataAry as $ctr=>$workDataRowAry){
				$dbColumnTableValuesAry=array();
				for ($lp=1;$lp<=$noColumns;$lp++){
					$columnName=$columnSortOrder[$lp];
					$columnAry=$columnProfileAry[$columnName];
					$columnType=$columnAry['columntype'];
					$columnClass=$columnAry['columnclass'];
					if ($columnClass==null){$columnClass=$tableClass;}
					//echo 'xxxd-9';exit();//xxxd
					switch($columnType){
						case 'text':
							//echo 'xxxd-10';exit();//xxxd
							$theData=$workDataRowAry[$columnName];
							$theData=$base->utlObj->returnFormattedString($theData,&$base);
							$theData_formatted="<p class=\"$columnClass\">$theData</p>";
							//echo 'xxxd-2';exit();//xxxd
							break;
						case 'url':
							//echo 'xxxd-8';exit();//xxxd
							$columnClassInsert="class=\"$columnClass\"";
							//echo 'xxxd-7';exit();//xxxd
							$jobLinkSt_raw=$columnAry['joblink'];
							//echo 'xxxd-6';exit();//xxxd
							$oldJobLinkSt=$jobLinkSt_raw;
							$pos=strpos('x'.$jobLinkSt_raw,'sessionname',0);
							if ($pos<=0){
								$sessionValue=$base->paramsAry['sessionname'];
								if ($sessionValue != NULL){$jobLinkSt_raw.="&sessionname=$sessionValue";}	
							}	
							//echo 'xxxd-5';exit();//xxxd
							$jobLinkSt=$base->utlObj->returnFormattedStringDataFed($jobLinkSt_raw,$workDataRowAry,&$base);
							//echo "before: $jobLinkSt_raw, after: $jobLinkSt<br>";//xxx
							//- url 
							$urlNameSt_raw=$columnAry['urlname'];
							$urlNameSt=$base->utlObj->returnFormattedStringDataFed($urlNameSt_raw,$workDataRowAry,&$base);
							//- column events
							$columnEvents_raw=$columnAry['columnevents'];
							$columnEvents=$base->utlObj->returnFormattedStringDataFed($columnEvents_raw,$workDataRowAry,&$base);
							//echo "columnevents: $columnEvents<br>";//xxxd
							//$base->debugObj->printDebug($columnDataAry[$dataCtr],1,'xxxd');
							//- final check of joblink
							$pos=strpos('x'.$jobLinkSt,'http',0);
							if ($pos>0 or $jobLinkSt=='#'){
								$theData_formatted="<a href=\"$jobLinkSt\" $columnClassInsert $tableIdInsert $columnEvents>$urlNameSt</a>";
							}
							else {
								$jobLocal=$base->systemAry['joblocal'];
								$theData_formatted="<a href=\"$jobLocal$jobLinkSt&$colName=$colDataSt\" $columnClassInsert>$urlNameSt</a>";
							}
							//echo 'xxxd-3';exit();//xxxd
							break;
						default:
							$theData_formatted=$workDataRowAry[$columnName];
					}
					$dbColumnTableValuesAry[]=$theData_formatted;
				}
				//echo 'xxxd-2';exit();//xxxd
				$dbColumnTableValues=implode('~',$dbColumnTableValuesAry);
				$sendAjaxDataAry[]='displayary|'.$dbColumnTableValues;			
			}
			$sendAjaxData=implode('`',$sendAjaxDataAry);
			//$base->debugObj->printDebug($sendAjaxDataAry,1,'xxxd');
			echo $sendAjaxData;
		}
	}
//=====================================================
	function copyInParams($base){
		$sendData=$base->paramsAry['senddata'];
		//echo "senddata: $sendData,";//xxxd
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		$paramNames=null;
		$paramValues=null;
		$theCnt=count($sendDataAry);
		for ($lp=0;$lp<$theCnt;$lp++){
			$theLine=$sendDataAry[$lp];
			$theLineAry=explode('|',$theLine);
			$theName=$theLineAry[0];
			$theValue=$theLineAry[1];
			if ($theName=='paramnames'){$paramNames=$theValue;}
			if ($theName=='paramvalues'){$paramValues=$theValue;}
		}
		$base->fileObj->writeLog('debug99',"senddata: $sendData, paramnames: $paramNames, paramvalues: $paramValues",&$base);
		$paramNamesAry=explode('~',$paramNames);
		$paramValuesAry=explode('~',$paramValues);
		$theCnt=count($paramNamesAry);
		$strg="--copying senddata into paramsary(thcnt: $theCnt)--\n";			
		for ($lp=0;$lp<$theCnt;$lp++){
			$paramName=$paramNamesAry[$lp];
			$paramValue=$paramValuesAry[$lp];
			//echo "xxxf0: $paramName: $paramValue,";
			if ($paramName != null){
				$base->paramsAry[$paramName]=$paramValue;
				$writtenValue=$base->paramsAry[$paramName];
				$strg.="$paramName: $paramValue\n";
			}
		}
		$strg.="---end copy---\n";
	}
//=====================================================
	function getAllParams($base){
		$combineParamsAry=$base->paramsAry;
		$sendData=$base->paramsAry['senddata'];
		//echo "senddata: $sendData,";//xxxd
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		$paramNames=null;
		$paramValues=null;
		$theCnt=count($sendDataAry);
		for ($lp=0;$lp<$theCnt;$lp++){
			$theLine=$sendDataAry[$lp];
			$theLineAry=explode('|',$theLine);
			$theName=$theLineAry[0];
			$theValue=$theLineAry[1];
			if ($theName=='paramnames'){$paramNames=$theValue;}
			if ($theName=='paramvalues'){$paramValues=$theValue;}
		}
		$base->fileObj->writeLog('debug',"senddata: $sendData, paramnames: $paramName",&$base);
		$paramNamesAry=explode('~',$paramNames);
		$paramValuesAry=explode('~',$paramValues);
		$theCnt=count($paramNamesAry);
		$strg="--copying senddata paramnamesary, paramvaluesary into combineparamsary(thcnt: $theCnt)--\n";			
		for ($lp=0;$lp<$theCnt;$lp++){
			$paramName=$paramNamesAry[$lp];
			$paramValue=$paramValuesAry[$lp];
			if ($paramName != null){
				$combineParamsAry[$paramName]=$paramValue;
				$strg.="$paramName: $paramValue\n";
			}
		}
		$strg.="---end copy---\n";
		$base->fileObj->writeLog('debug',$strg,&$base);
		return $combineParamsAry;
		//echo "n: $paramName, v: $paramValue,";//xxxd
	}
//===================================================
	function getNamedStringForAjax($paramAry,$base){
		$returnAry=array();
		$returnAry[]="!!paragraph!!\n";
		$paragraphName=$paramAry['param_1'];
		$sortAryName="sortorderary_".$paragraphName;
		//- I should be zero, but isn't
		$firstSentenceName=$base->sentenceProfileAry[$sortAryName][1];
		$firstSentenceString=$base->sentenceProfileAry[$paragraphName][$firstSentenceName]['sentencetext'];
		$returnAry[]='savenamedstring|'.$firstSentenceName.'|'.$firstSentenceString."\n";
		return $returnAry;
	}
//==================================================
	function setupContainerViaAjaxJson($paramAry,$base){
		$returnAry[]="!!utility!!\n";
		$containerName=$paramAry['param_1'];
		$containerNameAry=explode('_',$containerName);
		$containerName=$containerNameAry[0];
		$loadId=$containerNameAry[1];
		$workAry=$base->containerObj->getContainer($containerName,&$base);
		$htmlAry=$base->containerObj->insertContainerHtml($containerName,&$base);
		$htmlStrg=implode("",$htmlAry);
		$workAry['htmlline']=$htmlStrg;
		$workStrg=$base->xmlObj->array2Json($workAry,&$base);
		$returnAry[]="setupcontainerviaajaxjson|$containerName|$loadId|$workStrg\n";
		//$base->debugObj->printDebug($workAry,1,'xxxfworkary ajaxObject.php setupContainerviaajaxjson');
//- sometimes this precedes the creation of the html
		$returnAry[]="!!html!!\n";
		return $returnAry;
	}
//end of functions
}
