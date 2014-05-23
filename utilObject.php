<?php
class utilObject {
	var $statusMsg;
	var $callNo = 0;
	var $imageBuffers=array();
//=========================================
	function utilObject() {
		$this->incCalls();
		$this->statusMsg='util Object is fired up and ready for work!';
	}
//=========================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//=========================================
	function incCalls(){$this->callNo++;}
//=========================================
	function getBool($checkValue){
		if (($checkValue === true) || ($checkValue == 't')){$rtnVlu=true;}
		else {$rtnVlu=false;}
		return $rtnVlu;
	}
//========================================= 
//xxxd - changed to not duplicate values in the IP buffer - second one is now lost!!!!
	function getParams(){
		$paramsAry = array();
		foreach ($_POST as $key=>$value){
			//echo "post: $key, $value<br>";//xxxd
			$onFile=true;
			$useKey=$key;
			if (!array_key_exists($useKey,$paramsAry)){$paramsAry[$useKey]=$value;}
		}
		foreach ($_GET as $key=>$value){
			//echo "get: $key, $value<br>";//xxxd
			$onFile=true;
			$useKey=$key;
			if (!array_key_exists($useKey,$paramsAry)){$paramsAry[$useKey]=$value;}
		}
		return $paramsAry;
	}
//========================================= 
	function tableRowToHashAry($result,$dbControlsAry=""){
		$returnAry=array();
		if ($row = pg_fetch_row($result)) {
			foreach ($row as $key=>$value_dbformat){
				$colName = pg_field_name($result, $key);
				if (is_array($dbControlsAry['dbtablemetaary'])){
					$valueType=$dbControlsAry['dbtablemetaary'][$colName]['dbcolumntype'];
				} else {$valueType=NULL;}
				if ($valueType != NULL){
					$value=$this->returnFormattedData($value_dbformat,$valueType,'internal');
				}
				else {$value=$value_dbformat;}
				$returnAry[$colName]=$value;
			}
		}
		return $returnAry;
	}
//=========================================
		function tableToString($result,$base){
			$returnString=NULL;
			$insNewRow=NULL;
			while ($row=pg_fetch_row($result)){
				$rowString=NULL;
				$insTab=NULL;
				foreach ($row as $key=>$value_dbformat){
					//$colNameSt = pg_field_name($result, $key);
					//echo "key: $key, valuedbformat: $value_dbformat<br>";
					$rowString.=$insTab.$value_dbformat;
					$insTab="\t";
				}
				$returnString.=$insNewRow.$rowString;
				$insNewRow="\n";
			}	
			return $returnString;
		}
//========================================= soon to be deprecated
		function deprecatedtableToHashAry($result,$passAry=array()){
		$delimit1=$passAry['delimit1'];
		$delimit2=$passAry['delimit2'];
		$order1=$passAry['order1'];
		$returnAry=array();
		$rowCtr=0;
		$updatedReturnAry=false;
		while ($row = pg_fetch_row($result)) {
			$rowAry=array();
			$colvaluePkSt='error';
			foreach ($row as $key=>$value_dbformat){
				$colNameSt = pg_field_name($result, $key);
				$valueType=$passAry['dbcontrolsary']['dbtablemetaprofileary'][$colNameSt]['dbcolumntype'];
				if ($valueType == ""){$valueType=$passAry['globaldatatype'][$colNameSt];}
				if ($valueType != ""){
					$value=$this->returnFormattedData($value_dbformat,$valueType,'internal');
				}
				else {$value=$value_dbformat;}
				$rowAry[$colNameSt]=$value;
			}
//--- no delimits
			if ($delimit1 == ""){
				$returnAry[$rowCtr]=$rowAry;
				$updatedReturnAry=true;
			}
//--- one delimit
			if ($delimit1 != "" && $delimit2 == ""){
				$rowId=$rowAry[$delimit1];
				if ($order1 != ""){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary'][$orderNo]=$rowId;
					}
				}
				$returnAry[$rowId]=$rowAry;	
				$updatedReturnAry=true;
			} // end one delimt
//--- two delimits
			if ($delimit1 != "" && $delimit2 != ""){
				$subDelimits1=explode('_',$delimit1);
				$subDelimits2=explode('_',$delimit2);
				$noSubDelimits1=count($subDelimits1);
				$noSubDelimits2=count($subDelimits2);
				$insertUnderScore='';
				$rowId1='';
				for ($ctr=0;$ctr<$noSubDelimits1;$ctr++){
					$retrievedValue=$rowAry[$subDelimits1[$ctr]];
					if ($retrievedValue == NULL){$retrievedValue='none';}
					$rowId1.=$insertUnderScore.$retrievedValue;
					$insertUnderScore='_';
				}
				$rowId2='';
				$insertUnderScore='';
				for ($ctr=0;$ctr<$noSubDelimits2;$ctr++){
					$retrievedValue=$rowAry[$subDelimits2[$ctr]];
					if ($retrievedValue == NULL){$retrievedValue='none';}
					$rowId2.=$insertUnderScore.$retrievedValue;
					$insertUnderScore='_';
				}
				if ($rowId1 == NULL){$rowId1='none';}
				if ($rowId2 == NULL){$rowId2='none';}	
				$doContinue=false;
				$ctr=0;
				$newRowId2=$rowId2;
				while ($doContinue==false){
					if (array_key_exists($rowId1,$returnAry)){
						if (array_key_exists($newRowId2,$returnAry[$rowId1])){
							$ctr++;
							$newRowId2=$rowId2.'_'.$ctr;
						}
						else {$doContinue=true;}
					}
					else {$doContinue=true;}
				}
				$returnAry[$rowId1][$newRowId2]=$rowAry;
				$updatedReturnAry=true;
				if ($order1 != ""){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary_'.$rowId1][$orderNo]=$newRowId2;
					}
				}
			} // end two delimits
			$rowCtr++;
		}
		return $returnAry;
}
//========================================= soon to be deprecated 
		function deprecatedtableToHashAryV2($result,$passAry=array()){
		$delimit1=$passAry['delimit1'];
		$delimit2=$passAry['delimit2'];
		$delimit3=$passAry['delimit3'];
		$order1=$passAry['order1'];
		$returnAry=array();
		$rowCtr=0;
		$updatedReturnAry=false;
		while ($row = pg_fetch_row($result)) {
			$rowAry=array();
			$colvaluePkSt='error';
			foreach ($row as $key=>$value_dbformat){
				$colNameSt = pg_field_name($result, $key);
				$valueType=$passAry['dbcontrolsary']['dbtablemetaprofileary'][$colNameSt]['dbcolumntype'];
				if ($valueType == NULL){$valueType=$passAry['globaldatatype'][$colNameSt];}
				if ($valueType != NULL){
					$value=$this->returnFormattedData($value_dbformat,$valueType,'internal');
				}
				else {$value=$value_dbformat;}
				$rowAry[$colNameSt]=$value;
			}
//--- no delimits
			if ($delimit1 == NULL){
				$returnAry[$rowCtr]=$rowAry;
				$updatedReturnAry=true;
			}
//--- one delimit
			if ($delimit1 != NULL && $delimit2 == NULL && $delimit3 == NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary'][$orderNo]=$delimitValue1;
					}
				}
				$returnAry[$delimitValue1]=$rowAry;	
				$updatedReturnAry=true;
			} // end one delimt
//--- two delimits
			if ($delimit1 != NULL && $delimit2 != NULL && $delimit3 == NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($delimitValue1 == NULL){$delimitValue1='none';}
				$delimitValue2=$rowAry[$delimit2];
				if ($delimitValue2 == NULL){$delimitValue2='none';}
				$doContinue=false;
				$ctr=0;
				$returnAry[$delimitValue1][$delimitValue2]=$rowAry;
				$updatedReturnAry=true;
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary_'.$delimitValue1][$orderNo]=$delimitValue2;
					}
				}
			} // end two delimits
//--- three delimits
			if ($delimit1 != NULL && $delimit2 != NULL && $delimit3 !=NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($delimitValue1 == NULL){$delimitValue1='none';}
				$delimitValue2=$rowAry[$delimit2];
				if ($delimitValue2 == NULL){$delimitValue2='none';}
				$delimitValue3=$rowAry[$delimit3];
				if ($delimitValue3 == NULL){$delimitValue3='none';}
				$returnAry[$delimitValue1][$delimitValue2][$delimitValue3]=$rowAry;
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry[$delimitValue1]['sortorderary_'.$delimitValue2][$orderNo]=$delimitValue3;
					}
				}
			} // end three delimits
			$rowCtr++;
		}
		return $returnAry;
}
//========================================= 
		function tableToHashAryV3($result,$passAry=array()){
		$delimit1=$passAry['delimit1'];
		$delimit2=$passAry['delimit2'];
		$delimit3=$passAry['delimit3'];
		$order1=$passAry['order1'];
		$returnAry=array();
		$rowCtr=0;
		$updatedReturnAry=false;
		if ($result != NULL){
		while ($row = pg_fetch_row($result)) {
			$rowAry=array();
			$colvaluePkSt='error';
			foreach ($row as $key=>$value_dbformat){
				$colNameSt = pg_field_name($result, $key);
//- convert defining value type as internal
				$valueType=$passAry['dbcontrolsary']['dbtablemetaary'][$colNameSt]['dbcolumntype'];
				$valueConversion=$passAry['dbcontrolsary']['dbtablemetaary'][$colNameSt]['dbcolumnconversionname'];
				if ($valueConversion != null){$valueType=$valueType.'_'.$valueConversion;}
				if ($valueType == NULL){$valueType=$passAry['globaldatatype'][$colNameSt];}
				if ($valueType != NULL){
					$value=$this->returnFormattedData($value_dbformat,$valueType,'internal');
				}
				else {$value=$value_dbformat;}
//- convert for table output using conversion type
				$conversionType=$passAry['dbcontrolsary']['dbtablemetaary'][$colNameSt]['dbcolumnconversionname'];
				if ($conversionType != NULL){
					if ($conversionType=='boolean' && $booleanFlg=='numeric'){
						$conversionType='booleannumeric';
					}
					$newValue=$this->returnFormattedData($value,$conversionType,'table');// boolean for sqllite should be 1/0!!!!
					$value=$newValue;	
				}
				$rowAry[$colNameSt]=$value;
			}
//--- no delimits
			if ($delimit1 == NULL){
				$returnAry[$rowCtr]=$rowAry;
				$updatedReturnAry=true;
			}
//--- one delimit
			if ($delimit1 != NULL && $delimit2 == NULL && $delimit3 == NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary'][$orderNo]=$delimitValue1;
					}
				}
				$returnAry[$delimitValue1]=$rowAry;	
				$updatedReturnAry=true;
			} // end one delimt
//--- two delimits
			if ($delimit1 != NULL && $delimit2 != NULL && $delimit3 == NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($delimitValue1 == NULL){$delimitValue1='none';}
				$delimitValue2=$rowAry[$delimit2];
				if ($delimitValue2 == NULL){$delimitValue2='none';}
				$doContinue=false;
				$ctr=0;
				$returnAry[$delimitValue1][$delimitValue2]=$rowAry;
				$updatedReturnAry=true;
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry['sortorderary_'.$delimitValue1][$orderNo]=$delimitValue2;
					}
				}
			} // end two delimits
//--- three delimits
			if ($delimit1 != NULL && $delimit2 != NULL && $delimit3 !=NULL){
				$delimitValue1=$rowAry[$delimit1];
				if ($delimitValue1 == NULL){$delimitValue1='none';}
				$delimitValue2=$rowAry[$delimit2];
				if ($delimitValue2 == NULL){$delimitValue2='none';}
				$delimitValue3=$rowAry[$delimit3];
				if ($delimitValue3 == NULL){$delimitValue3='none';}
				$returnAry[$delimitValue1][$delimitValue2][$delimitValue3]=$rowAry;
				if ($order1 != NULL){
					$orderNo=$rowAry[$order1];
					if ($orderNo > 0 && $orderNo < 100){
						$returnAry[$delimitValue1]['sortorderary_'.$delimitValue2][$orderNo]=$delimitValue3;
					}
				}
			} // end three delimits
			$rowCtr++;
		} // end while pg_connect
		} // end if result not null
		return $returnAry;
}
//=========================================
	function extractInsertCode($line){
		$returnAry=array();
		$errorBl=false;
		$pos1=strpos($line,'!!',0);
		if ($pos1 > -1) {
			$startPos=$pos1+2;
			$pos2=strpos($line,'!!',$startPos);	
			if ($pos2 > -1) {
				$insertLen=$pos2-$startPos;
				$insertCode=substr($line,$startPos,$insertLen);
				if (strpos($insertCode,'_',0) !== false){
					$pos=strpos($insertCode,'_',0);
					$subCodeLen=strlen($insertCode)-($pos+1);
					$insertSubCode=substr($insertCode,($pos+1),$subCodeLen);
					$insertCode=substr($insertCode,0,$pos);
				} else {$insertsubCode="";}
			} else {$errorBl=true;}
		} else {$errBl=true;}
		if (!$errBl) {
			$returnAry['insertcode']=$insertCode;
			$returnAry['insertsubcode']=$insertSubCode;
		}
			return $returnAry;
	}
//=========================================
		function replaceStr($htmlLineSt,$OldSt,$NewSt,$base){
			$returnString=str_replace($OldSt, $NewSt, $htmlLineSt);
			//if ($htmlLineSt == 'limitselection'){echo "$htmlLineSt -> $returnString<br>";}//xxxf24
			return $returnString;
		}
//=========================================
		function extractStr($delim,$extractStr){
			if ($extractStr != ''){
				$pos1=strpos($extractStr,$delim,0);
				$pos2=strpos($extractStr,$delim,$pos1+1);
			} else {$pos1=0;$pos2=0;}
			if ($pos1>=0 && $pos2>0 && $pos2>$pos1){
				$pos1++;
				$extractLen=$pos2-$pos1;
				$returnValue=substr($extractStr,$pos1,$extractLen);
			}
			else {$returnValue='';}
			return $returnValue;
		}
//=========================================
	function checkData($theData,$regEx){
		$returnBool = preg_match($theData,$regEx);
		return $returnBool;
	}
//=======================================
	function returnFormattedString($theString_raw,$base){
		//need to fix anything with ?? to ?M - ?? is deprecated and causes problems
		$theString=$this->replaceStr($theString_raw,'??','?M',&$base);
		$convertError=false;
		$percentPos=strpos('x'.$theString,'%',0);
		if ($percentPos>0){
			$IamDone=false;
			$convertCtr=0;
			$currentDateAry=getdate();
			$year=$currentDateAry['year'];
			$month=$currentDateAry['mon'];
 			$month=substr(('0'.$month),-2,2);
			$day=$currentDateAry['mday'];
			$day=substr(('0'.$day),-2,2);
			$currentDate=$year.'-'.$month.'-'.$day;
 			$currentDate1=$month.'/'.$day.'/'.$year;
			$theString=$this->replaceStr($theString,'%currentdate%',"$currentDate",&$base);
 			$theString=$this->replaceStr($theString,'%currentdate1%',"$currentDate1",&$base);
			//$theOldString=$theString;
			//$theString=str_replace($theString,' ','');
			//echo 'thestring: xxx'.$theString.'xxx, length: '.$theLength."theoldstring: $theOldString".'<br>';//xxx
			$lastValueName=NULL;
			while ($IamDone===false && $convertCtr<20){
				if ($theString != NULL){
//- percentage have to be within 50 characters
					$findValidPercents=true;
					//echo "xxxf";//xxxf
					$startSearchPos=0;
					$findCtr=0;
					while ($findValidPercents){
						$percentPos=strpos($theString,'%',$startSearchPos);
					//echo "percentpos: $percentPos<br>";//xxxf
						$percentPos2=strpos($theString,'%',$percentPos+1);
						if ($percentPos2>$percentPos){
						$theDiff=$percentPos2-$percentPos;
							if ($theDiff>30){
								$startSearchPos=$percentPos+1;	
								//echo "error diff: ($percentPos): $theDiff<br>";//xxxf
								//$dmy=substr($theString,$percentPos,100);
								//echo "$dmy<br>";//xxxf
							}
							else {
								$findValidPercents=false;	
								//echo "good diff: ($percentPos -> $percentPos2): $theDiff<br>";//xxxf
							}
						}
						else {
							$findValidPercents=false;
						}
					}
					//$percentPos=strpos($theString,'%',0);
					//$percentPos2=strpos($theString,'%',$percentPos+1);
					if ($percentPos2>0){
						$convertCtr++;
						$valueName=substr($theString,$percentPos+1,$percentPos2-$percentPos-1);
						$valueNameAry=explode('_',$valueName);
						$valueNameDefault=$valueNameAry[1];
						$oldValueName=$valueName;
						$valueName=$valueNameAry[0];
						switch ($valueName){
							case 'pre';
								$valueReplace='<pre>';
								break;
							case '/pre';
								$valueReplace='</pre>';
								break;
							case 'cr';
								$valueReplace="\n";
								break;
							case 'tld';
								$valueReplace='~';
								break;
							case 'li';
								$valueReplace='<li>';
								break;
							case '/li';
								$valueReplace='</li>';
								break;
							case 'sglqt':
								$valueReplace="'";
								break;
							case 'isglqt':
								$valueReplace="&#39;";
								break;
							case 'dblqt':
								$valueReplace='"';
								break;
							case 'idblqt':
								$valueReplace='&#34;';
								break;
							case 'br':
								$valueReplace='<br>';
								break;
							case 'b':
								$valueReplace='<b>';
								break;
							case 'h1':
								$valueReplace='<h1>';
								break;
							case '/h1':
								$valueReplace='</h1>';
								break;
							case 'eb':
								$valueReplace='</b>';
								break;
							case '/b':
								$valueReplace='</b>';
								break;
							case 'c':
								$valueReplace=":";
								break;
							case 'date':
								$dateAry=getdate();
								$month=$dateAry['mon'];
								if (strlen($month)<2){$month='0'.$month;}
								$day=$dateAry['mday'];
								if (strlen($day)<2){$day='0'.$day;}
								$year=$dateAry['year'];
								$valueReplace=$month.'/'.$day.'/'.$year;
								break;
							case 'today':
								$dateAry=getdate();
								$month=$dateAry['mon'];
								if (strlen($month)<2){$month='0'.$month;}
								$day=$dateAry['mday'];
								if (strlen($day)<2){$day='0'.$day;}
								$year=$dateAry['year'];
								$valueReplace=$month.'/'.$day.'/'.$year;
								break;
							case 'companyprofileid':
								$job=$base->paramsAry['job'];
								$valueReplaceMain=$base->jobProfileAry['companyprofileid'];
								$valueReplace=$base->paramsAry['companyprofileid'];
								if ($valueReplace == null){$valueReplace=$valueReplaceMain;}
								break;
							case 'joblocal':
								$valueReplace=$base->systemAry['joblocal'];
								break;
							case 'htmllocal':
								$valueReplace=$base->systemAry['htmllocal'];
								break;
							case '{':
								$valueReplace="&#60;";
								break;
							case '}':
								$valueReplace="&#62;";
								break;
							case 'la':
								$valueReplace="<";
								break;
							case 'ra':
								$valueReplace=">";
								break;
							default:
								if (array_key_exists($valueName,$base->paramsAry)){
									$valueReplace=$base->paramsAry[$valueName];
									//echo "name: $valueName, valuereplace: $valueReplace<br>";//xxxf
								}
								else {
									$valueReplace=$valueNameDefault;
									//echo "error: $valueName<br>";//xxxf
									//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');//xxxf
								}
								//- below doesnt totally work - could be sglqt from prior value
								if (strlen($valueReplace) == NULL && $lastValueName != 'sglqt'){
									$valueReplace=NULL;
									//echo "$valueName: set to null<br>";//xxxd
									$convertError=true;
									$base->errorProfileAry['converterror']='error';
									//echo "set error on $valueName<br>";//xxxd
									//$base->DebugObj->displayStack();//xxxd
								}
						}
						//echo "change: $oldValueName to $valueReplace<br>";//xxxf
						$newString = $this->replaceStr($theString,'%'.$oldValueName.'%',$valueReplace,&$base);
						$lastValueName=$valueName;
						//$pos=strpos($theString,'maindisplayimageheaderleft');echo "old pos for 'maindisplayimageheaderleft': $pos<br>";
						$theString=$newString;
						//$pos=strpos($theString,'maindisplayimageheaderleft');echo "pos for 'maindisplayimageheaderleft': $pos<br>";
					} 
					else {$IamDone=true;}	
				}
				else {$IamDone=true;}
			}
		}
		//xxxf01
	return $theString;	
	}
	//=======================================
	function returnFormattedStringDataFed($theString_raw,$dataAry,$base){
		//need to fix anything with ?? to ?M - ?? is deprecated and causes problems
		$theString=$this->replaceStr($theString_raw,'??','?M',&$base);
		// convert ?d to % - ?d passes through returnFormattedString unchanged
		$dataDelimPos=strpos($theString,'?d',0);
		if ($dataDelimPos>-1){
			$theString=$this->replaceStr($theString_raw,'?d','%',&$base);// does this do it for all occurances?????xxxf24
			$dontUseParams=true;
		}
		else {
			$dontUseParams=false;
			$theString=$theString_raw;
		}
		//xxxf24
		//$theString=$theString_raw;//xxxf24
		$percentPos=strpos('x'.$theString,'%',0);
		if ($percentPos>0){
			$IamDone=false;
			$convertCtr=0;
			$currentDateAry=getdate();
			$year=$currentDateAry['year'];
			$month=$currentDateAry['mon'];
			$month=substr(('0'.$month),-2,2);
			$day=$currentDateAry['mday'];
			$day=substr(('0'.$day),-2,2);
			$currentDate=$year.'-'.$month.'-'.$day;
			$currentDate1=$month.'/'.$day.'/'.$year;
			$theString=$this->replaceStr($theString,'%currentdate%',"$currentDate",&$base);
			$theString=$this->replaceStr($theString,'%currentdate1%',"$currentDate1",&$base);
			//$theOldString=$theString;
			//$theString=str_replace($theString,' ','');
			//echo 'thestring: xxx: '.$theString.'<br>';//xxxd
			while ($IamDone===false && $convertCtr<20){
				if ($theString != NULL){
					$percentPos=strpos($theString,'%',0);
					//echo "thestring: ".'xxx'."$theString".'xxx'.",length: $theLength, theoldstring: $theOldString<br>";//xxx
					$percentPos2=strpos($theString,'%',$percentPos+1);
					//echo "xxxxxxx";//xxx
					if ($percentPos2>0){
						$convertCtr++;
						$valueName=substr($theString,$percentPos+1,$percentPos2-$percentPos-1);
						$valueNameAry=explode('_',$valueName);
						$valueNameDefault=$valueNameAry[1];
						$oldValueName=$valueName;
						$valueName=$valueNameAry[0];
						//xxxf - below may not be the best for <cr>
						switch ($valueName){
							case 'pre';
								$valueReplace='<pre>';
								break;
							case '/pre';
								$valueReplace='</pre>';
								break;
							case 'cr':
								$valueReplace="\n";
								break;
							case 'tld':
								$valueReplace='~';
								break;
							case 'li':
								$valueReplace='<li>';
								break;
							case '/li':
								$valueReplace='</li>';
								break;
							case 'sglqt':
								$valueReplace="'";
								break;
							case 'isglqt':
								$valueReplace="&#39;";
								break;
							case 'dblqt':
								$valueReplace='"';
								break;
							case 'idblqt':
								$valueReplace='&#34;';
								break;
							case 'br':
								$valueReplace='<br>';
								break;
							case 'b':
								$valueReplace='<b>';
								break;
							case 'c':
								$valueReplace=':';
								break;
							case 'eb':
								$valueReplace='</b>';
								break;
							case '/b':
								$valueReplace='</b>';
								break;
							case 'date':
								$dateAry=getdate();
								$month=$dateAry['mon'];
								if (strlen($month)<2){$month='0'.$month;}
								$day=$dateAry['mday'];
								if (strlen($day)<2){$day='0'.$day;}
								$year=$dateAry['year'];
								$valueReplace=$month.'/'.$day.'/'.$year;
								break;
							case 'today':
								$dateAry=getdate();
								$month=$dateAry['mon'];
								if (strlen($month)<2){$month='0'.$month;}
								$day=$dateAry['mday'];
								if (strlen($day)<2){$day='0'.$day;}
								$year=$dateAry['year'];
								$valueReplace=$month.'/'.$day.'/'.$year;
								break;
							case 'joblocal':
								$valueReplace=$base->systemAry['joblocal'];
								break;
							case 'htmllocal':
								$valueReplace=$base->systemAry['htmllocal'];
								break;
							case '{':
								$valueReplace="<";
								break;
							case '}':
								$valueReplace=">";
								break;
							default:
								if ($dataAry == null){$dataAry=array();}
								if (array_key_exists($valueName,$dataAry)){
									$valueReplace=$dataAry[$valueName];
								}
								elseif (array_key_exists($valueName,$base->paramsAry) && !($dontUseParams)){
									$valueReplace=$base->paramsAry[$valueName];
									//echo "name: $valueName, valuereplace: $valueReplace<br>";//xxxd
								}
								else {$valueReplace=$valueNameDefault;}
								//- below doesnt totally work - could be sglqt from prior value
								if (strlen($valueReplace) == NULL && $lastValueName != 'sglqt'){
									$valueReplace=NULL;
									//echo "$valueName: set to null<br>";//xxxd
									$convertError=true;
									$base->errorProfileAry['converterror']='error';
									//echo "set error on $valueName<br>";//xxxd
									//$base->DebugObj->displayStack();//xxxd
								}
							if (strlen($valueReplace) == 0){$valueReplace=$valueNameDefault;}
						}
						$newString = $this->replaceStr($theString,'%'.$oldValueName.'%',$valueReplace,&$base);
						//echo "newstrg: $newString, valuereplace: $valueReplace<br>";//xxxf
						$theString=$newString;
					} 
					else {$IamDone=true;}	
				}
				else {$IamDone=true;}
			}
			//echo "the string end: $theString<br>";
		} // end of if percentpos>0
		$percentPos=strpos($theString,'%',0);
		if ($percentPos>-1){$theString=$this->returnFormattedString($theString,&$base);}
		//echo "query: $query";//xx
		//echo "thestringend: $theString<br>";//xxx
	return $theString;	
	}
//======================================= 
	function returnFormattedData($colValue,$colType,$funcType='sql',$base){
		//if ($base == null){echo "!!! utilObj.returnFormattedData base is null";exit();}
		$colTypeAry=explode('_',$colType);
		$colType=$colTypeAry[0];
		if (count($colTypeAry)>1){
			$colConversion=$colTypeAry[1];
		}
		else {$colConversion=null;}
		switch ($funcType){
		case 'dbtype':
			$newColValue=$this->returnFormattedDataForDbType($colValue,$colType,$colConversion,&$base);
			break;
		case 'sql':
			$newColValue=$this->returnFormattedDataForSql($colValue,$colType,$colConversion,&$base);
			break;
		case 'form':
			$newColValue=$this->returnFormattedDataForForm($colValue,$colType,$colConversion,&$base);
			break;
		case 'internal':
			$newColValue=$this->returnFormattedDataForInternal($colValue,$colType,$colConversion,&$base);
			break;
		case 'js':
			$newColValue=$this->returnFormattedDataForJS($colValue,$colType,$colConversion,&$base);
			break;
		case 'html':
			$newColValue=$this->returnFormattedDataForHtml($colValue,$colType,$colConversion,&$base);
			break;
		case 'table':
			$newColValue=$this->returnFormattedDataForTable($colValue,$colType,$colConversion,&$base);
			break;
		case 'xml':
			$newColValue=$this->returnFormattedDataForXml($colValue,$colType,$colConversion,&$base);
			break;
		default:
			$newColValue=$colValue;
		}
		return $newColValue;
	}	
//=======================================
	function returnBoolean($colValue,$base){
		$returnValue=$this->returnFormattedDataForInternal($colValue,'boolean','',&$base);
		return $returnValue;	
	}
//=======================================
	function returnFormattedDataForTable($colValue,$colType,$colConversion,$base){
		switch ($colType){
			case 'money1':
				$newColValue=money_format("$%.2n",($colValue*.01));
			break;	
			case 'date1':
				$dateWorkAry=explode('-',$colValue);
				$newColValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
			break;
			case 'numeric0':
				$newColValue=number_format($colValue);	
			break;
			case 'numeric2':
				$newColValue=number_format($colValue,2);
			break;
			case 'booleannumeric':
				if ($colValue == 't' || $colValue=='true' || $colValue == true ){$newColValue=1;}
				else {$newColValue = 0;}
			break;
			default:
				$newColValue=$colValue;
		}
		return $newColValue;
	}
//=======================================
	function returnFormattedDataForHtml($colValue,$colType,$colConversion,$base){
		switch ($colType){
//--- boolean
		case 'boolean':
			$colValue=strtolower($colValue);
				if ($colValue === true || $colValue == 't' || $colValue == '1' || $colValue == 'true'){
					$newColValue='true';
				}
				else {
					if ($colValue === false || $colValue == 'f' || $colValue == '0' || $colValue == 'false' || $colValue == ''){
						$newColValue='false';
					}
					else {$newColValue='ERROR';}
			}
			break;
//--- numeric
		case 'numeric':
				$newColValue=$this->replaceStr($colValue,'%dblqt%','"',&$base);
				$pos=strpos($colValue,'"',0);
				if ($pos==''){$newColValue='"'.$newColValue.'"';}
				break;
//--- serial
		case 'serial':
				$newColValue=$this->replaceStr($colValue,'%dblqt%','"',&$base);
				$pos=strpos($colValue,'"',0);
				if ($pos==''){$newColValue='"'.$newColValue.'"';}
				break;
//--- int4
		case 'int4':
				$newColValue=$this->replaceStr($colValue,'%dblqt%','"',&$base);
				$pos=strpos($colValue,'"',0);
				if ($pos==''){$newColValue='"'.$newColValue.'"';}
			break;
//--- integer
		case 'integer':
				$newColValue=$this->replaceStr($colValue,'%dblqt%','"',&$base);
				$pos=strpos($colValue,'"',0);
				if ($pos==''){$newColValue='"'.$newColValue.'"';}
			break;
//--- varchar
		case 'varchar':
			//xxxf the below doesnt do nearly enough
			$currentDateAry=getdate();
			$year=$currentDateAry['year'];
			$month=$currentDateAry['mon'];
			$day=$currentDateAry['mday'];
			$currentDate=$year.'-'.$month.'-'.$day;
			//
			$baseUrl=$base->ClientObj->getHtmlBase(&$base);
			$baseUrlSym=str_replace(':','innercolon',$baseUrl);
			$tst=strpos($colValue,'http',0);
			//$base->FileObj->writeLog('debug1',"colvalue: $colValue, baseurlsym: $baseUrlSym",&$base);//xxxf
			$newColValue=$this->replaceStr($colValue,'%dblqt%','"',&$base);
			$newColValue=$this->replaceStr($newColValue,'%sglqt%',"'",&$base);
			$newColValue=$this->replaceStr($newColValue,'%br%','<br>',&$base);
			$newColValue=$this->replaceStr($newColValue,'%p%','<p>',&$base);
			$newColValue=$this->replaceStr($newColValue,'%currentdate%',"$currentDate",&$base);
			$newColValue=$this->replaceStr($newColValue,'%baseurl%',"$baseUrl",&$base);
			$newColValue=$this->replaceStr($newColValue,'%baseurlsym%',"$baseUrlSym",&$base);
			$pos=strpos($newColValue,'"',0);
			if ($pos==''){$newColValue='"'.$newColValue.'"';}
			//- go to standard ascii conv if still have percent
			$pos=strpos($newColValue,'%',0);
			if ($pos>-1){
				$newColValue=$this->returnFormattedString($newColValue,&$base);
			}
			break;
//--- date
		case 'date':
			if ($colConversion == 'dateconv1'){
				$dateWorkAry=explode('-',$colValue);
				$colValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
			}
			$newColValue="'".$colValue."'";
			break;
//--- url
		case 'url':
			$httpPos=strpos(('x'.$colValue),'http',0);
		  	$andPersandPos=strpos(('x'.$colValue),'&',0);
    		$forwardSlashPos=strpos(('x'.$colValue),'/',0);
      		if ($httpPos<1 && ($forwardSlashPos<1 || $andPersandPos<$forwardSlashPos)){
      			$htmlLocal=$base->systemAry['htmllocal'];
      			$newColValue="$htmlLocal/index.php?job=$colValue";
//echo "colvalue: $colValue, newcolvalue: $newColValue<br>";//xxxd
      		}
      else {$newColValue=$colValue;}
			break;
//--- default
		default:
			$newColValue="ERROR in type '$colType'";
		}
		return $newColValue;
	}
//======================================= 
	function returnFormattedDataForJS($colValue,$colType,$colConversion,$base){
		switch ($colType){
//--- boolean
		case 'boolean':
			$colValue=strtolower($colValue);
				if ($colValue === true || $colValue == 't' || $colValue == '1' || $colValue == 'true'){
					$newColValue='true';
				}
				else {
					if ($colValue === false || $colValue == 'f' || $colValue == '0' || $colValue == 'false' || $colValue == ''){
						$newColValue='false';
					}
					else {$newColValue='ERROR';}
			}
			break;
//--- numeric
		case 'numeric':
				$newColValue=$colValue;
				break;
//--- serial
		case 'serial':
				$newColValue=$colValue;
				break;
//--- int4
		case 'int4':
			$newColValue=$colValue;
			break;
//--- integer
		case 'integer':
			$newColValue=$colValue;
			break;
//--- varchar
		case 'varchar':
			$colValue=str_replace(chr(0x0a),'',$colValue);
			$colValue=str_replace(chr(0x0d),'',$colValue);
			//9/11/11 cant have single quote if surrounding it with single quotes
			$sglQt=chr(0x27);
			$colValue=str_replace($sglQt,"&#39;",$colValue);
			//str_replace("'","\'",$colValue);
			$newColValue="'".$colValue."'";
			//echo "newcolvalue: $newColValue<br>";//xxx
			break;
//--- date
		case 'date':
			if ($colConversion == 'dateconv1'){
				$dateWorkAry=explode('-',$colValue);
				$colValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
			}
			$newColValue="'".$colValue."'";
			break;
//--- default
		default:
			$newColValue="ERROR in type '$colType'";
		}
		return $newColValue;
}
//======================================= 
	function returnFormattedDataForSql($colValue,$colType,$colConversion,$base){
		switch ($colType){
//--- boolean
		case 'boolean':
			$colValue=strtolower($colValue);
			if ($colValue == ''){$newColValue='NULL';}
			else {
				if ($colValue === true || $colValue == 't' || $colValue == '1' || $colValue == 'true'){
					$newColValue='true';
				}
				else {
					if ($colValue === false || $colValue == 'f' || $colValue == '0' || $colValue == 'false'){
						$newColValue='false';
					}
					else {$newColValue='ERROR';}
				}
			}
			break;
//--- numeric
		case 'numeric':
				$colValueLength=strlen($colValue);
				if($colValueLength == 0){$newColValue='NULL';}
				else {$newColValue=$colValue;}
				break;
//--- serial
		case 'serial':
				$colValueLength=strlen($colValue);
				if($colValueLength == 0){$newColValue='NULL';}
				else {$newColValue=$colValue;}
				break;
//--- int4
		case 'int4':
			if ($colValue == ''){$newColValue='NULL';}
			else {$newColValue=$colValue;}
			break;
//--- integer
		case 'integer':
			$colValueLength=strlen($colValue);
			if ($colValueLength == 0){$newColValue='NULL';}
			else {$newColValue=$colValue;}
			//echo "colvalue: $colValue, newcolvalue: $newColValue\n";//xxxf
			break;
//--- varchar 
		case 'varchar':
			//$pos=strpos('x'.$colValue.'x',"'",0);
			//if ($pos>0){echo "colvalue: $colValue";}
			$fromStr="'";
			$toStr="%sglqt%";
			$pos=strpos($colValue,$fromStr,0);
			if ($pos > -1){
				$newColValue = $this->replaceStr($colValue,$fromStr,$toStr,&$base);
			}
			else {$newColValue = $colValue;}
			$colValue=$newColValue;
			$fromStr='"';
			$toStr="%dblqt%";
			$pos=strpos($colValue,$fromStr,0);
			if ($pos > -1){
				$newColValue = $this->replaceStr($colValue,$fromStr,$toStr,&$base);
			}
			else {$newColValue = $colValue;}
			$newColValue="'".$newColValue."'";
			break;
//--- date
		case 'date':
			if ($colConversion == 'dateconv1' && $colValue != null){
				$dateWorkAry=explode('-',$colValue);
				$colValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
			}
			if ($colValue == ''){$newColValue='NULL';}
			else {$newColValue="'".$colValue."'";}
			break;
//--- default
		default:
			$newColValue="ERROR in type '$colType'";
		}
		return $newColValue;
	}
//===============================
	function returnFormattedDataForForm($colValue,$colType,$colConversion,$base){
		switch ($colType){
			case 'boolean':
				$colValue_lc = strtolower($colValue);
				if ($colValue === true || $colValue == 't' || $colValue == '1' || $colValue_lc == 'true'){
					$newColValue='"TRUE"';
				}
				else {
					if ($colValue === false || $colValue == 'f' || $colValue == '0' || $colValue_lc == 'false' || $colValue == ''){
						$newColValue='"FALSE"';
					}
				}
				break;
			case 'int4':
				$newColValue='"'.$colValue.'"';
				break;
			case 'integer':
				$newColValue='"'.$colValue.'"';
				break;
			case 'varchar':
				$newColValue='"'.$colValue.'"';
				break;
			case 'numeric':
				$newColValue='"'.$colValue.'"';
			case 'serial':
				$newColValue='"'.$colValue.'"';
				break;
			case 'date':
				if ($colConversion == 'dateconv1' && $colValue != null){
					$dateWorkAry=explode('-',$colValue);
					$colValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
				}
				$newColValue='"'.$colValue.'"';
				break;
			default:
				$newColValue='ERROR';
		}
		return $newColValue;
	}
//=================================
	function returnFormattedDataForInternal($colValue,$colType,$colConversion,$base){
		switch ($colType){
		case 'boolean':
			if ($colValue == ''){$newColValue=false;}
			else {
				if ($colValue == 't' || $colValue == '1' || $colValue == 'true'){$newColValue=true;}
				else {
					if ($colValue == 'f'|| $colValue == '0' || $colValue == 'false'){$newColValue=false;}
					else {$newColValue='ERROR';}
				}
			}
			break;
		case 'int4':
			$newColValue=$colValue;
			break;
		case 'integer':
			$newColValue=$colValue;
			break;
		case 'varchar':
			$newColValue=$colValue;
			break;
		case 'numeric':
			$newColValue = trim($colValue, "\x3a..\xff");
			//echo "xxx: colvalue: $colValue, newcolvalue: $newColValue\n<br>";
			break;
		case 'serial':
			$newColValue = trim($colValue, "\x3a..\xff");
			//echo "xxx: colvalue: $colValue, newcolvalue: $newColValue\n<br>";
			break;
		case 'date':
			if ($colConversion == 'dateconv1' && $colValue != null){
				$dateWorkAry=explode('-',$colValue);
				$colValue=$dateWorkAry[1].'/'.$dateWorkAry[2].'/'.$dateWorkAry[0];
			}
			$newColValue=$colValue;
			break;
		default:
			$newColValue="ERROR with type '$colType'";
		}
		return $newColValue;
	}
//=======================================
	function returnFormattedDataForDbType($colValue,$colType,$colConversion,$base){
		if ($colType == 'in'){
//---------------------- incoming
			switch ($colValue){
			case 'bool':
				$newColValue='boolean';
				break;
			case 'int4':
				$newColValue='numeric';
				break;
			case 'integer':
				$newColValue='numeric';
				break;
			case 'serial':
				$newColValue='numeric';				
				break;
			default:
				$newColValue=$colValue;
			} // end switch colvalue
		} // end if 'in'
		else {
			if ($colType == 'out'){
//-------------------- outgoing
				$newColValue=$colValue;
			} // end if 'out'
			else {
//-------------------- neither
				$newColValue=$colValue;				
			} // end else 'out'
		} // end else 'in'
		return $newColValue;
	}
//======================================================================================
	function returnFormattedDataForXml($colValue,$colType,$colConversion,&$base){
		switch ($colType){
			case 'key':
		       	$newColValue=trim($colValue);
    		    if(preg_match("/^[0-9]/",$newColValue)){ $newColValue = "n$newColValue";}
        		$newColValue=str_replace(' ','_',$newColValue); 
 				break;
			case 'fromkey':
				$newColValue=$colValue;
				$newColValue=str_replace('slashReplace','/',$newColValue);
				$newColValue=str_replace('pipeReplace','|',$newColValue);
				$newColValue=str_replace('spaceReplace',' ',$newColValue);
				$newColValue=str_replace('colonReplace',':',$newColValue);
				$checkChar0=substr($newColValue,0,1);
				if ($checkChar0=='n'){
					$checkChar1=substr($newColValue,1,1);
					if (!preg_match("/[a-zA-Z]/",$checkChar1)){
						$theLen=strlen($newColValue);
						$theLen--;
						$newColValue=substr($newColValue,1,$theLen);
					}
				}
				break;
			default:
				echo "utilObj.returnFormattedDataForXml invalid coltype: $colType, colvalue: $colValue<br>";
		}
		return $newColValue;
	}
//================================= 
	function saveValue($saveName,$saveValue,$base){
		//echo "save: $saveName, $saveValue";//xxxd
		session_start();
		$_SESSION[$saveName] = $saveValue;
		//echo "overwrite $saveName with $saveValue<br>";//xxxd
	}	
//================================= !!! - firefox debug - cant retrieve from session variable
	function retrieveValue($retrieveName,$base){
		session_start();
		$retrieveValue=$_SESSION[$retrieveName];
		return $retrieveValue;
	}
//=================================
	function appendValue($appendName,$appendValue,$base){
		$theJob=$base->paramsAry['job'];
		$tstJob='x'.$theJob;
		$pos=strpos($tstJob,'debug',0);
		if (!($pos>0)){
			session_start();
			$oldValue=$_SESSION[$appendName];
			$oldValue.=$appendValue;
		//echo "write: $oldValue to $appendName";//xxxd
			$_SESSION[$appendName]=$oldValue;	
		}
	}
//=================================
	function clearSessionBuffer($base){
		session_start();
		foreach ($_SESSION as $id=>$body){
			if ($id != 'userobj' && $id != 'debug'){unset($_SESSION[$id]);}	
		}	
	}
//==================================================
	function nullSessionFile($valueName,$base){
		session_start();
		if ($valueName != null){
			$theJob=$base->paramsAry['job'];
			$tstJob='x'.$theJob;
			$pos=strpos($tstJob,'debug',0);
			if (!($pos>0)){
				//echo 'unset '.$valueName;
				unset($_SESSION[$valueName]);
			}
		}
	}
//================================================
	function validateUserCompanyDeprecated($paramsAry,$base){
		$userAry = $_SESSION['userobj']->getCurrentUserAry();
		//$this->DebugObj->printDebug($userAry,1,'uary');//xxx
		//$this->DebugObj->printDebug($this->jobProfileAry,1,'uary');//xxx
		$userName = $userAry['username'];
		$userCompanyNo = $userAry['companyprofileid'];
		$jobCompanyNo = $base->jobProfileAry['companyprofileid'];
		$allUsersAllowed_file = $base->jobProfileAry['allusersallowed'];
		//- problem: below may want to have menu element security
		$allUsersAllowed=$base->UtlObj->returnFormattedData($allUsersAllowed_file,'boolean','internal');
		$accessAllCompanies_file=$userAry['accessallcompanies'];
		//$base->DebugObj->printDebug($userAry,1,'user');//xxx
		$accessAllCompanies=$base->UtlObj->returnFormattedData($accessAllCompanies_file,'boolean','internal');
		$companyAllowsAccessToAll_file=$base->jobProfileAry['companyallowsaccesstoall'];
		$companyAllowsAccessToAll=$base->UtlObj->returnFormattedData($companyAllowsAccessToAll_file,'boolean','internal');
		//$base->DebugObj->printDebug($base->jobProfileAry,1,'job');//xxx
		//$base->DebugObj->printDebug($userAry,1,'user');//xxx
		//echo "user: $userCompanyNo, job: $jobCompanyNo<br>";//xxx
		//echo "jp:allusersallowed: $allUsersAllowed, up:accessallcompanies: $accessAllCompanies, cp:companyallowsaccesstoall: $companyAllowsAccessToAll<br>";//xxx
		$componentCheck=$paramsAry['componentcheck'];
		if ($componentCheck){
			if ($userCompanyNo == $jobCompanyNo || $accessAllCompanies){$okToContinue=true;}
		}
		elseif(($userCompanyNo == $jobCompanyNo) || $allUsersAllowed || $accessAllCompanies || $companyAllowsAccessToAll){$okToContinue=true;}
		else {$okToContinue=false;}
		//$okToContinue=true;
		return $okToContinue;
	}
	//===============================================
	function validateUserDept($base){
		$okToContinue=true;
		/*
		$userDeptAry=$_SESSION['userobj']->getCurrentDeptAry();
		//$this->DebugObj->printDebug($userDeptAry,1,'userdeptary');//xxx
		$jobDeptAry=$base->deptProfileAry;
		//$base->DebugObj->printDebug($jobDeptAry,1,'jobdeptary');//xxx
		$noDept=count($jobDeptAry['main']);
		if ($noDept > 0){
			$okToContinue=false;
			//echo "check out depts<br>";
			foreach ($jobDeptAry['main'] as $jobDept=>$jobDeptInfoAry){
				if (array_key_exists($jobDept,$userDeptAry['main'])){					
					//echo "both have dept: $jobDept<br>";
					foreach ($jobDeptAry[$jobDept] as $jobDeptFunction=>$jobDeptFunctionAry){
						//echo "function: $jobDeptFunction<br>";//xxx
						if (array_key_exists($jobDeptFunction,$userDeptAry[$jobDept])){
							//echo "both have function $jobDeptFunction<br>";
							$okToContinue=true;
						}
					}
				}
			}
		}
		else {$okToContinue=true;}
		//echo "oktocontinue: $okToContinue";//xxx
		 */
		return $okToContinue;
	}
//==================================================
	function adjustTodayDate($noDays,$base){
		$passAry=array('thedate'=>'today');
		$thisDateAry=$this->getDateInfo($passAry,&$base);
		$wDayNo=$thisDateAry['wday'];
		$mDayNo=$thisDateAry['mday'];
		$yearNo=$thisDateAry['year'];
		$monthNo=$thisDateAry['mon'];
		for ($theLp=$noDays;$theLp>0;$theLp--){
			$mDayNo--;
			if ($mDayNo<1){
				$monthNo--;
				if ($monthNo<1){
					$yearNo--;
					$monthNo=12;
					$mDayNo=31;
				}
				else {
					$mDayNo=$this->getLastDay($monthNo,&$base);
				}
			}
		}
		$adjustedDate=$monthNo.'/'.$mDayNo.'/'.$yearNo;
		return $adjustedDate;
	}
//==================================================
	function getLastDay($monthNo,$base){
		$dayMonths='312831303130313130313031';
		$firstPos=($monthNo-1)*2;
		$theLastDay=substr($dayMonths,$firstPos,2);
		return $theLastDay;
	}
//==================================================
	function getTodaysDate($base){
		$passAry=array('thedate'=>'today');
		$dateAry=$this->getDateInfo($passAry,&$base);
		$todaysDate=$dateAry['date_v1'];
		return $todaysDate;
	}
//===================================================
	function getDateInfo($passAry,$base){
		$theDate=$passAry['thedate'];
		$returnAry=array();
		switch ($theDate){
			case 'startyear':
				$thisDateAry=getdate();
				$monthNo='01';
				$yearNo=$thisDateAry['year'];
				$dayNo='01';
				$theDate_o=$monthNo.'/'.$dayNo.'/'.$yearNo;
				$theDate_i=strtotime($theDate_o);
				$returnAry=getdate($theDate_i);
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
			break;
			case 'endyear':
				$thisDateAry=getdate();
				$monthNo='12';
				$yearNo=$thisDateAry['year'];
				$dayNo='31';
				$theDate_o=$monthNo.'/'.$dayNo.'/'.$yearNo;
				$theDate_i=strtotime($theDate_o);
				$returnAry=getdate($theDate_i);
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
			break;
			case 'startmonth':
				$thisDateAry=getdate();
				$monthNo=$thisDateAry['mon'];
				$yearNo=$thisDateAry['year'];
				$dayNo='01';
				$theDate_o=$monthNo.'/'.$dayNo.'/'.$yearNo;
				$theDate_i=strtotime($theDate_o);
				$returnAry=getdate($theDate_i);
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
			break;
			case 'endmonth':
				$startDate=$passAry['startdate'];
				if ($startDate != NULL){
					$startDateWorkAry=explode('/',$startDate);
					$monthNo=$startDateWorkAry[0];
					$yearNo=$startDateWorkAry[2];
				}
				else {
					$thisDateAry=getdate();
					$monthNo=$thisDateAry['mon'];
					$yearNo=$thisDateAry['year'];
				}
				$dayNo=31;
				for ($dayNo=31;$dayNo>26;$dayNo--){
					$dateIsOk=checkdate($monthNo,$dayNo,$yearNo);
					if ($dateIsOk){break;}
				}
				$theDate_o=$monthNo.'/'.$dayNo.'/'.$yearNo;
				$theDate_i=strtotime($theDate_o);
				$returnAry=getdate($theDate_i);
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
			break;
			case 'today':
				$workAry=getdate();
				//print_r($workAry);//xxx
				$monthNo=$workAry['mon'];
				$yearNo=$workAry['year'];
				$dayNo=$workAry['mday'];
				$hourAdj=$base->systemAry['houradj'];
				if ($hourAdj<0){$hourAdjStr=$hourAdj;}
				elseif ($hourAdj>0) {$hourAdjStr='+'.$hourAdj;}
				if ($hourAdj != 0){
					$theTimeDate_i=strtotime("$hourAdjStr hours");
				}
				else {$theTimeDate_i=$workAry[0];}
				$returnAry=getdate($theTimeDate_i); 
				$theDate_i=$returnAry[0];
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
				$returnAry['time_v1']=date("H:i:s",$theDate_i);
				$returnAry['time_v2']=date("g:ia",$theDate_i);
				//print_r($returnAry);//xxx
			break;
			default:
				$theDate_i=strtotime($theDate);
				//echo "thedatei: $theDate_i, thedate: $theDate\n";//xxxf
				$returnAry=getdate($theDate_i);
				$returnAry['date_v1']=date("m/d/Y",$theDate_i);
		} // end if dateisok
		//$base->DebugObj->printDebug($returnAry,1,'xxxdateary');
		return $returnAry;
	}
//===========================================
	function sendMail($toLine,$subjectLine,$messageLine,$base){
		if ($subjectLine!=NULL){
			if ($messageLine != NULL){
				$fromLine=$base->systemProfileAry['domainname'];
				$headers="from: $fromLine";
				mail($toLine,$subjectLine,$messageLine,$headers);
				//if ($return){echo "return: $return<br>";}
				//echo "to: $toLine<br>";
				//echo "from: $fromLine<br>";
				//echo "subject: $subjectLine<br>";
				//$printMessage=str_replace("\n","<br>",$messageLine);
				//echo "message: $printMessage";
			}
		}
	}
//==========================================
	function saveCookie($cookieName,$cookieValue){
		if ($cookieName != NULL && $cookieValue !=NULL){
			$cookieTimeOut=time()+3600;	
			setcookie($cookieName,$cookieValue,$cookieTimeOut);
		}
	}
//============================================
	function convertTime($theTime,$base){
		$theTimeAry=explode(':',$theTime);
		$theTimeHours=$theTimeAry[0];
		$theTimeMinutes=$theTImeAry[1];
		$theTotalTime=$theTimeHours*60+$theTimeMinutes;	
		return $theTotalTime;
	}
//============================================
	function convertDate($theDate,$dateType,$base){
		$passAry=array('thedate'=>$theDate);
		$theDateAry=$this->getDateInfo($passAry,&$base);
		$theDay=$theDateAry['mday'];
		$theMonth=$theDateAry['mon'];
		$theYear=$theDateAry['year'];
		switch ($dateType){
			case 'internal':
				$theDateNew="$theYear-$theMonth-$theDay";
			break;	
			case 'date1':
				$theDateNew="$theMonth/$theDay/$theYear";
			break;
			default:
				$theDateNew=$theDate;
		}	
		return $theDateNew;
	}
//============================================
	function getNoDays($theDate1,$theDate2,$base){
		//echo "thedate1: $theDate1, thedate2: $theDate2\n";
		$passAry=array('thedate'=>$theDate1);
		$theDate1Ary=$this->getDateInfo($passAry,&$base);
		$passAry=array('thedate'=>$theDate2);
		$theDate2Ary=$this->getDateInfo($passAry,&$base);
		//foreach ($theDate1Ary as $id=>$value){echo "$id: $value\n";}
		//foreach ($theDate2Ary as $id=>$value){echo "$id: $value\n";}
		$value1=$theDate1Ary['0'];
		$value2=$theDate2Ary['0'];
		$secondsDiff=$value2-$value1;
		$minutesDiff=$secondsDiff/60;
		$hoursDiff=$minutesDiff/60;
		$daysDiff=$hoursDiff/24;
		return $daysDiff;
	}
//============================================
	function getLastDayOfWeek($theDate,$base){
		//xxxf - below needs to have a good timezone set
		$dateTimeZone = new DateTimeZone('PST');
		$theDateObj=new DateTime($theDate,$dateTimeZone);
		//- not sure why I cant do the below in the above object
		$passAry['thedate']=$theDate;
		$theDateAry = $this->getDateInfo($passAry,$base);
		$dayOfWeek=$theDateAry['wday'];
		$noDaysToAdd=6-$dayOfWeek;
		$theDateObj->modify('+'."$noDaysToAdd days");
		$endOfWeekDate=$theDateObj->format("m/d/Y");
		//echo "thedate: $theDate, dayofweek: $dayOfWeek, nodaystoadd: $noDaysToAdd, $endOfWeekDate\n";//xxxf
		return $endOfWeekDate;
	}
//============================================
	function getLastDayOfMonth($theDate,$base){
		//xxxf - below needs to have a good timezone set
		$dateTimeZone = new DateTimeZone('PST');
		$theDateObj=new DateTime($theDate,$dateTimeZone);
		//- not sure why I cant do the below in the above object
		$passAry['thedate']=$theDate;
		$theDateAry = $this->getDateInfo($passAry,$base);
		$dayOfMonth=$theDateAry['mday'];
		$monthNo=$theDateAry['mon'];
		$noDaysInMonth=substr("312831303130313130313031",(($monthNo-1)*2),2);
		$noDaysToAdd=$noDaysInMonth-$dayOfMonth;
		$theDateObj->modify('+'."$noDaysToAdd days");
		$endOfWeekDate=$theDateObj->format("m/d/Y");
		//echo "thedate: $theDate, dayofweek: $dayOfWeek, nodaystoadd: $noDaysToAdd, $endOfWeekDate\n";//xxxf
		return $endOfWeekDate;
	}
//============================================
	function getLastDayOfYear($theDate,$base){
		//xxxf - below needs to have a good timezone set
		$dateTimeZone = new DateTimeZone('PST');
		$theDateObj=new DateTime($theDate,$dateTimeZone);
		//- not sure why I cant do the below in the above object
		$passAry['thedate']=$theDate;
		$theDateAry = $this->getDateInfo($passAry,$base);
		$dayOfYear=$theDateAry['yday'];
		//xxxf - not always!!!
		$noDaysInYear=365;
		$noDaysToAdd=$noDaysInYear-$dayOfYear;
		$noDaysToAdd--;
		$theDateObj->modify('+'."$noDaysToAdd days");
		$tst=$theDateObj->format("d");
		$tst*=1;
		if ($tst != 31){$theDateObj->modify('+1 days');}
		$endOfYearDate=$theDateObj->format("m/d/Y");
		return $endOfYearDate;
	}
//============================================
	function copyInSession($base){
		$sessionName=$base->paramsAry['sessionname'];
		if ($sessionName != NULL){
			//- need to look if it is overlay or something!!!
			if(isset($_SESSION['sessionobj'])){
				$sessionAry=$_SESSION['sessionobj']->getSessionAry($sessionName);
				//$base->DebugObj->printDebug($sessionAry,1,"sessionname: $sessionName");//xxx
				//array_merge($base->paramsAry,$sessionAry);
				//xxxd !!! below may need modification - change params of a session, change session????
				$cnt=count($sessionAry);
				//echo "sessionname: $sessionName<br>";//xxxd
				$base->FileObj->writeLog('debug',"read in $cnt session records for $sessionName",&$base);
				if ($cnt>0){
					foreach ($sessionAry as $key=>$value){
						//echo "key: $key, value: $value<br>";//xxxd
						if (!array_key_exists($key,$base->paramsAry)){
							$base->paramsAry[$key]=$value;
							//echo "$key=>$value copied in<br>";//xxxd
						}
						else {
							$ofValue=$base->paramsAry[$key];
							//echo "$key=>$value not copied in. onfile: $key=>$ofValue <br>";//xxxd
						}
					}
				}
			}
			else {
				unset($base->paramsAry['sessionname']);	
			}
		}
	}
//=====================================================
	function openImageBuffer($bufferNo,$base){
		$this->imageBuffers[$bufferNo]=NewMagickWand();
		return true;
	}
//======================================================
	function readImage($bufferNo,$filePath,$base){
		$success=MagickReadImage($this->imageBuffers[$bufferNo], $filePath);
		return $success;
	}
//=======================================================
	function resizeImage($bufferNo,$width,$height,$base){
		//echo "buffer: $bufferNo, width: $width, height: $height<br>";
		if ($width == 0 && $height > 0 || $height == 0 && $width > 0){
			$origWidth=MagickGetImageWidth($this->imageBuffers[$bufferNo]);
			$origHeight=MagickGetImageHeight($this->imageBuffers[$bufferNo]);
			//echo "origwidth: $origWidth, origHeight: $origHeight";//xxxd
			if ($width==0 && $height >0){
				$pct=$height/$origHeight;
				$width=$origWidth*$pct;
				$width=round($width);
			}
			else {
				$pct=$width/$origWidth;
				$height=$origHeight*$pct;
				$height=round($height);
			}
		}
		//echo "width: $width, height: $height<br>";//xxxd
		$success=MagickScaleImage($this->imageBuffers[$bufferNo], $width, $height );
		return $success;
	}
//======================================================
	function shawdowImage($bufferNo,$opacity,$base){
		$xPos=100;
		$yPos=200;
		$sigma=300;
		$success=MagickShadowImage($this->imageBuffers[$bufferNo],$opacity,$sigma,$xPos,$yPos);
		return $success;
	}
//========================================================
	function writeImage($bufferNo,$filePath,$base){
		$success=MagickWriteImage($this->imageBuffers[$bufferNo], $filePath);
		return $success;
	}
//========================================================
	function getImageStats($bufferNo,$base){
		$returnAry=array();
		//$imageWidth=$this->imageBuffers[$bufferNo]->getImageWidth();
		$imageWidth=MagickGetImageWidth($this->imageBuffers[$bufferNo]);
		$imageHeight=MagickGetImageHeight($this->imageBuffers[$bufferNo]);
        //$imageHeight=$this->imageBuffers[$bufferNo]->getImageHeight();
		$returnAry['imageheight']=$imageHeight;
		$returnAry['imagewidth']=$imageWidth;
		return $returnAry;
	}
//=========================================================
  function tryToChangeMod($thePath,$modToUse,$base){
    //get owner of the file
//- get file uid
    $fileUID=fileOwner($thePath);
//- get my uid
    //xxxf - doesnt work??? $myUID=getmyuid();
    $idString=exec('id');
    $idStringAry=explode('(',$idString);
    $idString=$idStringAry[0];
    $idStringAry=explode('=',$idString);
    $myUID=$idStringAry[1];
//- if both are the same then do it
    $didIt=false;
    if ($fileUID == $myUID){
      $didIt=true;
      if (!chmod($thePath,$modToUse)){
        echo "error: cannot chmod $thePath to $modToUse";
        $didIt=false;
      }
    }
    return $didIt;
  }
//=======================================
	function rebuildViewJoin($base){
		$base->DebugObj->printDebug("debug001Obj:rebuildView",0); //xx (h)
		$dbTableProfileId=$base->paramsAry['dbtableprofileid'];
		$query="select * from dbcolumnprofileview where dbtableprofileid=$dbTableProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbcolumnname');
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($dataAry,1,'dtaary');//xxx
		$foreignFieldsAry=array();
		$foreignFiltersAry=array();
		$errorsFound=false;
		$innerJoinStrg=NULL;
		$outerJoinStrg=NULL;
		$joinAry=array();
//----- loop through all of the columns
		foreach ($dataAry as $columnName=>$columnAry){
			$columnNameAry=explode('_',$columnName.'_');
			$columnName=$columnNameAry[0];
			$dbTableName=$columnAry['dbtablename'];
			$foreignField_raw=$columnAry['dbcolumnforeignfield'];
			$foreignField=$base->UtlObj->returnFormattedData($foreignField_raw,'boolean','internal',&$base);
			$noViewLink_raw=$columnAry['dbcolumnnoviewlink'];
			$noViewLink=$base->UtlObj->returnFormattedData($noViewLink_raw,'boolean','internal',&$base);
//----- foreign field
			if ($foreignField){
				$foreignKeyName=$columnAry['dbcolumnforeignkeyname'];
				$foreignKeyAry=$dataAry[$foreignKeyName];
				$foreignKeyNoViewLink_raw=$foreignKeyAry['dbcolumnnoviewlink'];
				$foreignKeyNoViewLink=$base->UtlObj->returnFormattedData($foreignKeyNoViewLink_raw,'boolean','internal',&$base);
				if (!$foreignKeyNoViewLink){
					$foreignTable=$foreignKeyAry['dbcolumnforeigntable'];
					//- below is rare that they are different only example is jobxref table
					$foreignColumnName=$columnAry['dbcolumnforeigncolumnname'];
					if ($foreignColumnName == NULL){$foreignColumnName=$columnName;}
					//- below is used for select part
					$foreignFieldsAry[$foreignTable][$columnName]=$foreignColumnName;
//----- foreign key
					$mainTable=$foreignKeyAry['dbcolumnmaintable'];
					if ($mainTable == NULL){$mainTable=$dbTableName;}
					//$foreignFiltersAry[$foreignTable][$foreignKeyName]=$mainTable;//old
					$parentSelector_raw=$foreignKeyAry['dbcolumnparentselector'];
					$parentSelector=$base->UtlObj->returnFormattedData($parentSelector_raw,'boolean','internal',&$base);
					if (!array_key_exists($foreignKeyName,$joinAry)){
						$joinAry[$foreignKeyName]=1;
						$foreignKeyNameAry=explode('_',$foreignKeyName);
						$foreignKeyName=$foreignKeyNameAry[0];
						if ($parentSelector){
//- inner join
							$innerJoinStrg.=" inner join $foreignTable on ".$mainTable.".".$foreignKeyName."=".$foreignTable.".".$foreignKeyName;						
						}
						else {
//- left join		
							$outerJoinStrg.=" left join $foreignTable on ".$mainTable.".".$foreignKeyName."=".$foreignTable.".".$foreignKeyName;
						}
					}
				}
			}
		}
		$viewStmt="create view $dbTableName".'view'." as ";
		$viewStmt.=" select ";
//$base->DebugObj->printDebug($foreignFieldsAry,1,'xxx');
		$selectList="$dbTableName.*";
		$tableList="$dbTableName";
//- foreignfieldsary put in display
		$filterList=NULL;
		foreach ($foreignFieldsAry as $foreignTable=>$columnNameAry){
			$tableList.=",$foreignTable";
			if ($foreignTable == NULL){$errorsFound=true;}
			foreach ($columnNameAry as $columnName=>$foreignColumnName){
				//echo "columnname: $columnName, foreigncolumnname: $foreignColumnName<br>";//xxx
				if ($columnName == $foreignColumnName){
					$selectList.=','."$foreignTable.$columnName";
				}
				else {
					$selectList.=','."$foreignTable.$foreignColumnName as $columnName";
				}
				if ($selectList == NULL || $columnName == NULL){$errorsFound=true;}
			}
		}
//- foreignfiltersary put in where clause
		$firstTime=true;		
		foreach ($foreignFiltersAry as $foreignTable=>$foreignKeyAry){
			//echo "foreigntable: $foreignTable<br>";//xxx
			foreach ($foreignKeyAry as $foreignKeyName=>$mainTable){
				$foreignKeyNameAry=explode('_',$foreignKeyName);
				$foreignKeyName=$foreignKeyNameAry[0];
				if ($firstTime){$andIns=NULL;}
				else {$andIns=" and ";}	
				$filterList.="$andIns$foreignTable.$foreignKeyName=$mainTable.$foreignKeyName";
				if ($foreignTable == NULL || $foreignKeyName == NULL || $dbTableName == NULL){$errorsFound=true;}
				$firstTime=false;
			}						
		}
//- final construction
		if ($filterList != NULL){$filterListInsert='where '.$filterList;}
		else {$filterListInsert=NULL;}
		//$viewStmt.="$selectList from $tableList $filterListInsert";
		$viewStmt.="$selectList from $dbTableName $innerJoinStrg $outerJoinStrg $filterListInsert";
		//echo "view: $viewStmt<br>";//xxxf
		//exit();//xxxf
		$dropViewStmt='drop view '.$dbTableName.'view';
		if ($errorsFound){
			echo "Errors have been found!!!<br>";
			echo "$viewStmt<br>";
		}
		else {
			$dbTableNameView=$dbTableName.'view';
			$query="select * from pg_views where viewname='$dbTableNameView'";
			//echo "query: $query";//xxx
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$checkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($checkAry,1,'chk');//xxx
			$noFileColumns=count($checkAry);
			//echo "nofilecolumns: $noFileColumns";//xxx
			if ($noFileColumns>0){
				//echo "drop it";//xxx
				//echo "drop stmt: $dropViewStmt<br>";//xxx
				$base->DbObj->queryTable($dropViewStmt,'maint',&$base);
			}
			$base->DbObj->queryTable($viewStmt,'maint',&$base);
			//echo "view stmt: $viewStmt<br>";//xxx
		}		
		$base->DebugObj->printDebug("-rtn:rebuildView",0);
	}
//========================================================
	function reorderAlbum($base){
		$albumProfileId=$base->paramsAry['albumprofileid'];
		if ($albumProfileId != null){
			$incrNo=$base->paramsAry['pictureincr'];
			$this->reorderAlbumInt($albumProfileId,$incrNo,&$base);
		}
	}
//=========================================================
	function reorderAlbumInt($albumProfileId,$incrNo,$base){
		if ($incrNo == null){$incrNo=5;}
		$query="select pictureprofileid, picturename, pictureno from pictureprofile where albumprofileid=$albumProfileId order by pictureno";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$workAryNo=count($workAry);
		$pictureCtr=$incrNo;
		for ($theLp=0;$theLp<$workAryNo;$theLp++){
			$pictureRow=$workAry[$theLp];
			$theirPictureCtr=$pictureRow['pictureno'];
			if ($theirPictureCtr != $pictureCtr){
				$pictureRow['pictureno']=$pictureCtr;
				$writeRowsAry[]=$pictureRow;
			}
			$pictureCtr=$pictureCtr+$incrNo;
		}
		//$base->DebugObj->printDebug($workAry,1,'xxxfworkary');
		$noToUpdate=count($writeRowsAry);
		if ($noToUpdate>0){
			//$base->DebugObj->printDebug($writeRowsAry,1,'xxxfwriterowsary');//xxxf
			$dbControlsAry=array('dbtablename'=>'pictureprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$base->DbObj->writeToDb($dbControlsAry,&$base);
		}
	}
//==============================================================
	function breakOutSendData($base){
		$sendData=$base->paramsAry['senddata'];
		$workAry=explode('`',$sendData);
		$sendDataAry=array();
		foreach ($workAry as $ctr=>$theValue){
			$theValueAry=explode('|',$theValue);
			$aName=$theValueAry[0];
			$aValue=$theValueAry[1];
			$sendDataAry[$aName]=$aValue;
		}
		return $sendDataAry;
	}
//================================================================
	function formatNumber($theAmount,$theFormat,$base){
		$theFormatAry=explode("_",$theFormat);
		$theDecimals=$theFormatAry[0];
		$pos=strpos($theAmount,'.',0);
		if ($pos === false){
			$theAmount.=".";

			for ($lp=0;$lp<$theDecimals;$lp++){
				$theAmount.="0";
			}

		}
		//xxxf nothing happened here

		$theAmountAry=explode('.',$theAmount);
		$theLen=strlen($theAmountAry[1]);
		if ($theDecimals>$theLen){
			$theDiff=$theDecimals-$theLen;
			for ($lp=0;$lp<$theDiff;$lp++){
				$theAmount=$theAmount.'0';
			}
		}
		else if ($theLen<$theDecimals){
			$theDiff=$theDecimals-$theLen;
			$totalLen=strlen($theAmount);
			$useLen=$totalLen-$theDiff;
			$theAmount=substr($theAmount, 0, $useLen);
		}

		return $theAmount;
	}
//end of functions
}
?>
