<?php
class operationPlugin001Object {
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
	var $base;
	var $workAreaAry;
//========================================
	function operationPlugin001Object() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
//========================================
	function buildDbTableFileOld($base){
		$dbTableDefStrg=NULL;
		$query="select dbtablename,dbcolumnname,dbcolumntype from dbcolumnprofileview order by dbtablename";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtablename','delimit2'=>'dbcolumnname');
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$dbTableFile=NULL;
		$lineDelim="\n";
		foreach ($dataAry as $dbTableName=>$dbTableAry){
			$dbTableFile.="tablename:$dbTableName$lineDelim";
			$columnLine=NULL;
			$delim=NULL;
			$subDelim='%';
			foreach ($dbTableAry as $dbColumnName=>$dbColumnAry){
				$dbColumnType=$dbColumnAry['dbcolumntype'];
				$columnLine.="$delim$dbColumnName$subDelim$dbColumnType";
				$delim='~';	
			}
			$dbTableFile.="columns:$columnLine$lineDelim";
		}
		$tmpDirPath=$base->systemAry['tmplocal'];
		$domainName=$base->systemAry['domainname'];
		$fileName="$domainName".'_dbtabledefs';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			$base->FileObj->writeFile($pathToUse,$dbTableFile,&$base);
			$msgLine="<pre>dbtable file written to $pathToUse</pre>";
			//echo "msgline: $msgLine<br>";//xxxd
			$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//========================================
	function buildDbTableFile($base){
		$dbTableDefStrg=NULL;
		$dbName=$base->paramsAry['dbname'];
		if ($dbName == null){$dbName='lindy';}
		$pos=strpos($dbName,'/',0);
		//echo "pos: $pos<br>";//xxxd
		if ($pos>0){
			$dbNameAry=explode('/',$dbName);
			$dbName="$dbNameAry[0]|$dbNameAry[1]";
		}
		$errorMessageName='compareresults';
// dbtableprofile
		$query="select * from dbtableprofileview order by dbtablename";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtablename');
		$dbTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
// dbcolumnprofile
		$query="select * from dbcolumnprofileview order by dbtablename";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtablename','delimit2'=>'dbcolumnname');
		$dbColumnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);		
// combine and convert
		//$dbAry=array('dbtableprofile'=>$dbTableAry);
		$dbAry=array();
		$dbAry['dbtableprofile']=$dbTableAry;
		$dbAry['dbcolumnprofile']=$dbColumnAry;
		//$xmlFile=$base->XmlObj->array2xml($dbAry);
		$jsonFile=$base->XmlObj->array2Json($dbAry,&$base);
		//$base->DebugObj->printDebug($xmlFile,1,'xmlfilexxxd');
		$tmpDirPath=$base->systemAry['tmplocal'];
		//$fileName="$dbName".'_dbtabledefs.xml';
		$fileName="$dbName".'_dbtabledefs.json';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			//$base->FileObj->writeFile($pathToUse,$xmlFile,&$base);
			$base->FileObj->writeFile($pathToUse,$jsonFile,&$base);
			$msgLine="<pre>dbtable file written to $pathToUse</pre>";
			$base->ErrorObj->saveError($errorMessageName,$msgLine,&$base);
			//$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//========================================xxxd
	function convertTableToXmlFile($base){
		$dbTableDefStrg=NULL;
		$dbName=$base->paramsAry['dbname'];
		if ($dbName == null){$dbName='lindy';}
		$pos=strpos($dbName,'/',0);
		//echo "pos: $pos<br>";//xxxd
		if ($pos>0){
			$dbNameAry=explode('/',$dbName);
			$dbName="$dbNameAry[0]|$dbNameAry[1]";
		}		
		$errorMessageName='compareresults';
		$sortInsert=null;
		$debugFilter=null;
		$dbAry=array();
//- get parent table names
		$debugFilter="";//xxxf
		$dbTableNameOfParent=$base->paramsAry['dbtablename:parent'];
		$dbParentSelectorNameOfParent=$base->paramsAry['dbparentselectorname:parent'];
		$dbSelectorNameOfParent=$base->paramsAry['dbselectorname:parent'];
		$sortInsert=" order by $dbParentSelectorNameOfParent";
		$dbTableNameOfParentView=$dbTableNameOfParent.'view';
		$query="select * from $dbTableNameOfParentView $debugFilter $sortInsert";
		//echo "query: $query<br>";//xxxd
//- get parent table data
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>$dbParentSelectorNameOfParent);
		if ($dbSelectorNameOfParent != null){$passAry['delimit2']=$dbSelectorNameOfParent;}
		//$base->DebugObj->printDebug($passAry,1,'xxxd');
		$dbParentAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$dbAry[$dbTableNameOfParent]=$dbParentAry;

//- child
		$dbTableNameOfChild=$paramsAry['dbtablenameofchild'];
		if ($dbTableNameOfChild != null){
			$dbParentSelectorNameOfChild=$paramsAry['dbparentselectornameofchild'];
			$dbSelectorNameOfChild=$paramsAry['dbselectornameofchild'];
			$sortInsert=null;
// get data for child
			$query="select * from $dbTableNameOfChild $sortInsert";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>$parentSelectName,'delimit2'=>$chileSelectName);
			$dbChildAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
			$dbAry[$dbTableNameOfChile]=$dbChildAry;
		}
		//$base->DebugObj->printDebug($dbAry,1,'xxxd');exit();//xxxd
// combine and convert
//- plug which makes it work
		$xmlFile=$base->XmlObj->array2xml($dbAry);
		//$base->DebugObj->printDebug($xmlFile,1,'xmlfilexxxd');
		$tmpDirPath=$base->systemAry['tmplocal'];
		$fileName="$dbName".'_'.$dbTableNameOfParent.'.xml';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			$base->FileObj->writeFile($pathToUse,$xmlFile,&$base);
			$msgLine="<pre>dbtable file written to $pathToUse</pre>";
			$base->ErrorObj->saveError($errorMessageName,$msgLine,&$base);
			//$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//========================================xxxd
	function convertTableToJsonFile($base){
		$dbTableDefStrg=NULL;
		$dbName=$base->paramsAry['dbname'];
		if ($dbName == null){$dbName='lindy';}
		$pos=strpos($dbName,'/',0);
		//echo "pos: $pos<br>";//xxxd
		if ($pos>0){
			$dbNameAry=explode('/',$dbName);
			$dbName="$dbNameAry[0]|$dbNameAry[1]";
		}		
		$errorMessageName='compareresults';
		$sortInsert=null;
		$debugFilter=null;
		$dbAry=array();
//- get parent table names
		$debugFilter="";//xxxf
		$dbTableNameOfParent=$base->paramsAry['dbtablename:parent'];
		$dbParentSelectorNameOfParent=$base->paramsAry['dbparentselectorname:parent'];
		$dbSelectorNameOfParent=$base->paramsAry['dbselectorname:parent'];
		$sortInsert=" order by $dbParentSelectorNameOfParent";
		$dbTableNameOfParentView=$dbTableNameOfParent.'view';
		$query="select * from $dbTableNameOfParentView $debugFilter $sortInsert";
		//echo "query: $query<br>";//xxxd
//- get parent table data
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>$dbParentSelectorNameOfParent);
		if ($dbSelectorNameOfParent != null){$passAry['delimit2']=$dbSelectorNameOfParent;}
		//$base->DebugObj->printDebug($passAry,1,'xxxd');
		$dbParentAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$dbAry[$dbTableNameOfParent]=$dbParentAry;

//- child
		$dbTableNameOfChild=$paramsAry['dbtablenameofchild'];
		if ($dbTableNameOfChild != null){
			$dbParentSelectorNameOfChild=$paramsAry['dbparentselectornameofchild'];
			$dbSelectorNameOfChild=$paramsAry['dbselectornameofchild'];
			$sortInsert=null;
// get data for child
			$query="select * from $dbTableNameOfChild $sortInsert";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>$parentSelectName,'delimit2'=>$chileSelectName);
			$dbChildAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
			$dbAry[$dbTableNameOfChile]=$dbChildAry;
		}
		//$base->DebugObj->printDebug($dbAry,1,'xxxd');exit();//xxxd
// combine and convert
//- plug which makes it work
		$jsonFile=$base->XmlObj->array2Json($dbAry,&$base);
		//$base->DebugObj->printDebug($xmlFile,1,'xmlfilexxxd');
		$tmpDirPath=$base->systemAry['tmplocal'];
		$fileName="$dbName".'_'.$dbTableNameOfParent.'.json';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			$base->FileObj->writeFile($pathToUse,$jsonFile,&$base);
			$msgLine="<pre>dbtable file written to $pathToUse</pre>";
			$base->ErrorObj->saveError($errorMessageName,$msgLine,&$base);
			//$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//========================================
	function buildPluginFile($base){
		$pluginDefStrg=NULL;
		$query="select pluginname,plugintype,pluginobject,pluginmethod from pluginprofileview order by pluginname";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'pluginname');
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$pluginFile=NULL;
		$lineDelim="\n";
		foreach ($dataAry as $pluginName=>$pluginAry){
			$pluginFile.="tablename:$pluginName$lineDelim";
			$columnLine=NULL;
			$delim=NULL;
			$subDelim='%';
			foreach ($pluginAry as $pluginFieldName=>$pluginFieldValue){
				$columnLine.=$delim.$pluginFieldName.$subDelim.$pluginFieldValue;
				$delim='~';	
			}
			$pluginFile.="columns:$columnLine$lineDelim";
		}
		$tmpDirPath=$base->systemAry['tmplocal'];
		$domainName=$base->systemAry['domainname'];
		$fileName="$domainName".'_plugindefs';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			$base->FileObj->writeFile($pathToUse,$pluginFile,&$base);
			$msgLine="<pre>plugin file written to $pathToUse</pre>";
			//echo "msgline: $msgLine<br>";//xxxd
			$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//========================================
	function buildStandardPromptsFile($base){
		$standardDefStrg=NULL;
		$query="select standardpromptsname,standardpromptslabel,standardpromptsvalue from standardpromptsprofileview order by standardpromptsname";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'standardpromptsname');
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$standardFile=NULL;
		$lineDelim="\n";
		foreach ($dataAry as $standardName=>$standardAry){
			$standardFile.="standardname:$standardName$lineDelim";
			$columnLine=NULL;
			$delim=NULL;
			$subDelim='%';
			foreach ($standardAry as $standardFieldName=>$standardFieldValue){
				$columnLine.=$delim.$standardFieldName.$subDelim.$standardFieldValue;
				$delim='~';	
			}
			$standardFile.="columns:$columnLine$lineDelim";
		}
		$tmpDirPath=$base->systemAry['tmplocal'];
		$domainName=$base->systemAry['domainname'];
		$fileName="$domainName".'_standardpromptsdefs';
		$pathToUse=$tmpDirPath.'/'.$fileName;
		if ($pathToUse != NULL){
			$base->FileObj->writeFile($pathToUse,$standardFile,&$base);
			$msgLine="<pre>standard file written to $pathToUse</pre>";
			//echo "msgline: $msgLine<br>";//xxxd
			$base->errorProfileAry['compareresults']=$msgLine;
		}
	}
//===================================================
	function compareDbTableDefsDeprecated($base){
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
		$comparePath=$tmpLocal.'/'.$fileName;
		$dbTableCompareAry=$this->getDbTableAry($comparePath,&$base);
		$rtnMsg='<pre>'."\n";
		foreach ($dbTableCompareAry as $dbTableName=>$dbTableAry){
			$rtnMsg.="tablename: $dbTableName\n";
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$ctr=0;
			foreach ($dbTableAry as $dbColumnName=>$dbColumnType){
				$dbColumnType=str_replace("\n",'',$dbColumnType);
				$tstColumnType=$dbControlsAry['dbtablemetaary'][$dbColumnName]['dbcolumntype'];
				if ($dbColumnType != $tstColumnType){
					if ($ctr<300){
						$ctr++;
						$rtnMsg.="   $ctr) $dbColumnName($dbColumnType) ne $tstColumnName($tstColumnType)\n";
					}
				}
			}	
		}
		$rtnMsg.="</pre>\n";
		$base->errorProfileAry['compareresults']=$rtnMsg;
	}
//===================================================xxxd
	function compareDbTableDefs($base){
//	$dbColumnProfileAry{_new}	->	$dbTableName   ->	$dbColumnName -> dbcolumnprofileid,dbcolumnname,...
//	$dbTableProfileAry{_new} -> $dbTableName	->	dbtableprofileid { ->dbtableprofileid} ,dbtablename ...
		session_start();
		$_SESSION['sessionobj']->clearDir('comparedbtables');
		$sessionAry=array();
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
		//- get arrays from xml
		$comparePath=$tmpLocal.'/'.$fileName;
		$dbCompareXml=$base->FileObj->getFile($comparePath);
		//$dbCompareXmlAry=$base->XmlObj->xml2Ary($dbCompareXml,&$base);
		$dbCompareXmlAry=$base->XmlObj->json2Array($dbCompareXml,&$base);//xxxf
		$ctr=0;
		$rtnMsg='<pre>'."\n";
		$dbTableProfileAry_new=$dbCompareXmlAry['dbtableprofile'];
		$dbColumnProfileAry_new=$dbCompareXmlAry['dbcolumnprofile'];
		$query="select * from dbtableprofileview order by dbtablename";
		$passAry=array('delimit1'=>'dbtablename');
		$dbTableProfileAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$query="select * from dbcolumnprofileview order by dbtablename";
		$passAry=array('delimit1'=>'dbtablename','delimit2'=>'dbcolumnname');
		$dbColumnProfileAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$dontCheckAry=array();
		$dontCheckAry['companyname']='dont';
		$dontCheckAry['companyprofileid']='dont';
		$dontCheckAry['dbtableprofileid']='dont';
		$dontCheckAry['dbcolumnprofileid']='dont';
		$errAry=array();
		$ctr=0;
		$rtnMsg=null;
//- compare dbcolumnprofile_new to dbcolumnprofile
		foreach ($dbColumnProfileAry_new as $dbTableName => $dbColumnsAry_new){
			//echo "dbcolumnprofileary_new/dbtablename: $dbTableName<br>";//xxxd
			$errAry[$dbTableName]=array();
			if (array_key_exists($dbTableName,$dbColumnProfileAry)){
				//echo "$dbTableName exists on both<br>";//xxxd
			foreach ($dbColumnsAry_new as $dbColumnName=>$dbColumnAry_new){
				$errAry[$dbTableName][$dbColumnName]=array();
				if (array_key_exists($dbColumnName,$dbColumnProfileAry[$dbTableName])){
				foreach ($dbColumnAry_new as $fieldName=>$fieldValue_new){
					if (!array_key_exists($fieldName,$dontCheckAry)){
						$fieldValue=$dbColumnProfileAry[$dbTableName][$dbColumnName][$fieldName];
						if ($fieldValue_new != $fieldValue){
							$errAry[$dbTableName][$dbColumnName][$fieldName]=$fieldValue_new.'|'.$fieldValue;
						}
					}
				}
				}
				else {
					$errAry[$dbTableName][$dbColumnName]['doesnotexist']='columnreallydoesnt';
					$sessionAry['dbcolumns'][$dbTableName][$dbColumnName]=$dbColumnAry_new;//xxxf
				}
			}
			}
			else {
				//echo "$dbTableName does not exists on both<br>";//xxx
				$errAry[$dbTableName]['doesnotexist']='tablereallydoesnt';
				if (!array_key_exists('dbcolumns',$sessionAry)){$sessionAry['dbcolumns']=array();}
				if (!array_key_exists($dbTableName,$sessionAry['dbcolumns'])){$sessionAry['dbcolumns'][$dbTableName]=array();}
				if (!array_key_exists('dbtables',$sessionAry)){$sessionAry['dbtables']=array();}
				$sessionAry['dbcolumns'][$dbTableName]=$dbColumnsAry_new;
				//$base->DebugObj->printDebug($dbColumnsAry_new,1,'xxxddbtablename in columns');exit();//xxxd
			}
		}
		$sessionAry['dbtables']=$dbTableProfileAry_new;
		$sessionAry_xml=$base->XmlObj->array2Xml($sessionAry);
		$_SESSION['sessionobj']->saveSessionValue('comparedbtablesxml',$sessionAry_xml);
//- build error spot for web page
		$sessionName=$base->paramsAry['sessionname'];
		$rtnMsg="<div class=\"compareresultstitle\">Click on the line below to copy over changes!</div>\n";
		$dbName=$base->paramsAry['dbname'];
		$fileName=$base->paramsAry['filename'];
		$fileNameAry=explode('_',$fileName);
		$fromDbName=$fileNameAry[0];
		$fromDbName=str_replace('|','/',$fromDbName);
		$rtnMsg.="<div class=\"compareresultstitle\"><b>$fromDbName</b>-><b>$dbName</b></div>\n";//xxxf99
		foreach ($errAry as $dbTableName=>$dbTableAry){
			if (!array_key_exists('doesnotexist',$dbTableAry)){
				$missingColumnList=null;
				foreach ($dbTableAry as $dbColumnName=>$dbColumnValuesAry){
					//if ($dbTableName=='htmlprofile' && $dbColumnName=='background'){$checkit=true;echo 'xxxd0';}
					//echo "$dbTableName, $dbColumnName<br>";//xxxd
					$comma=null;
					if (!array_key_exists('doesnotexist',$dbColumnValuesAry)){
						$misMatchedFieldValues=null;
						$foundOne=false;
						foreach ($dbColumnValuesAry as $fieldName=>$fieldValues){
							//if ($checkit){echo "$fieldName, $fieldValue<br>";}
							$fieldValuesAry=explode('|',$fieldValues);
							$misMatchedFieldValues.="$fieldName: $fieldValuesAry[0] <> $fieldValuesAry[1],";
							$foundOne=true;
						}
						//$checkit=false;//xxxd
						if ($foundOne){
							$rtnMsg.="table <b>'$dbTableName'</b> column <b>'$dbColumnName'</b> has mismatches: <b>'$misMatchedFieldValues'</b> <a href=\"?job=maintsystemtables&operation=mainttables&dbtablename=$dbTableName&columnName=$dbColumnName&maintcode=mismatchcolumn&sessionname=$sessionName\">fix</a><br>";
							//echo "xxxd0,";
						}
					}
					else {
						$missingColumnList.=$dbColumnName.$comma;
						$comma=", ";
						//echo 'xxxd11,';
					}
				}
				if ($missingColumnList != null){
					$rtnMsg.="table <b>'$dbTableName'</b> is missing columns: <b>'$missingColumnList'</b> <a href=\"?job=maintsystemtables&operation=mainttables&dbtablename=$dbTableName&maintcode=missingcolumns&sessionname=$sessionName\">fix</a><br>";
					//echo "xxxd1,";
				}
			}
			else {
				$rtnMsg.="table <b>'$dbTableName'</b> is missing <a href=\"?job=maintsystemtables&operation=mainttables&dbtablename=$dbTableName&maintcode=missingtable&sessionname=$sessionName\">fix</a><br>";
			}
		}
		$base->ErrorObj->saveError('compareresults',$rtnMsg,&$base);
	}
//===================================================
	function getDbTableAry($path,$base){
		$fileAry_adj=array();
		$fileAry=$base->FileObj->getFileArray($path);
		$fileCnt=count($fileAry);
		$dmy=false;
		for ($lp=0;$lp<=$fileCnt;$lp=$lp+2){
			$tableFld=$fileAry[$lp];
			$tableFldAry=explode(':',$tableFld);
			$tableName_raw=$tableFldAry[1];
			$tableName=trim($tableName_raw);
			if ($tableName != NULL){
				$columnFld=$fileAry[$lp+1];
				$columnFldAry=explode(':',$columnFld);
				$columnList=$columnFldAry[1];
				$columnListAry=explode('~',$columnList);
				$columnListAry_adj=array();
				foreach ($columnListAry as $ctr=>$value){
					$valueAry=explode('%',$value);
					$columnName=$valueAry[0];
					$columnType=$valueAry[1];
					$columnListAry_adj[$columnName]=$columnType;	
				}
				$dmy=true;
				$fileAry_adj[$tableName]=$columnListAry_adj;
			}
		}
		return $fileAry_adj;
	}
//===================================================xxxd99
	function comparePluginDefs($base){
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
		$comparePath=$tmpLocal.'/'.$fileName;
		$dbCompareXml=$base->FileObj->getFile($comparePath);
		//$dbCompareAry=$base->XmlObj->xml2Ary($dbCompareXml,&$base);
		$dbCompareAry=$base->XmlObj->json2Array($dbCompareXml,&$base);
		$query="select * from pluginprofileview order by pluginname";
		$passAry=array('delimit1'=>'pluginname');
		$dbAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		//$base->DebugObj->printDebug($dbCompareAry,1,'xxxd');
		//$base->DebugObj->printDebug($dbAry,1,'xxxd');
		$otherDbAry=$dbCompareAry['pluginprofile'];
		$errorAry=array();
		foreach ($otherDbAry as $dbName=>$dbNameAry){
			if (array_key_exists($dbName,$dbAry)){
			foreach ($dbNameAry as $dbValueName=>$dbValue){
				switch ($dbValueName){
					case 'pluginprofileid':
						break;
					default:
						$thisDbValue=$dbAry[$dbName][$dbValueName];
						if ($thisDbValue != $dbValue){
							$errorAry[]="value:$dbName:$dbValueName:$dbValue:$thisDbValue";
						}
				}
			}
			}
			else {
				$errorLine="name:$dbName";
				foreach ($dbNameAry as $dbValueName=>$dbValue){
					if ($dbValueName != 'pluginprofileid'){
						$errorLine.=":$dbValueName:$dbValue";
					}
				}
				$errorAry[]=$errorLine;
			}
		}
		$sessionName=$base->paramsAry['sessionname'];
		if ($sessionName != null){$sessionInsert="&sessionname=$sessionName";}
		else {$sessionInsert=null;}
		//$base->DebugObj->printDebug($base->paramsAry,1,'paramsaryxxxd');
		$rtnMsg="<div class=\"compareresultstitle\">Click on 'fix' to copy over changes!</div>\n";
		$dbName=$base->paramsAry['dbname'];
		$fileName=$base->paramsAry['filename'];
		$fileNameAry=explode('_',$fileName);
		$fromDbName=$fileNameAry[0];
		$fromDbName=str_replace('|','/',$fromDbName);
		$rtnMsg.="<div class=\"compareresultstitle\">$fromDbName--->$dbName</div><br>\n";
		foreach ($errorAry as $ctr=>$errRow){
			$errRowAry=explode(':',$errRow);
			$errType=$errRowAry[0];
			$errName=$errRowAry[1];
			$errVName=$errRowAry[2];
			$errValueOld=$errRowAry[4];
			$errValueNew=$errRowAry[3];
			switch ($errType){
				case 'value':
					$urlString="<scan class=\"compareresults\">Update Plugin Field - name: <b>$errName</b> field: <b>$errVName</b> value: <b>$errValueNew</b> -> <b>$errValueOld</b></scan><a class=\"compareresults\" href=\"index.php?job=maintsystemtables&operation=fixpluginprofile&fixinput=$errRow$sessionInsert\">fix</a>\n";
					break;
				case 'name':
					$errCnt=count($errRowAry);
					$delim=':';
					$displayErrStrg=null;
					for ($lp=4;$lp<$errCnt;$lp++){
						$displayErrStrg.="$errRowAry[$lp]$delim";
						if ($delim==':'){$delim=',';}
						else {$delim=':';}
					}
					$urlString="<scan class=\"compareresults\">Add New Plugin - name '<b>$errName</b>' $displayErrStrg</scan><a class=\"compareresults\" href=\"index.php?job=maintsystemtables&operation=fixpluginprofile&fixinput=$errRow$sessionInsert\">fix</a>\n";
					break;
				default:
					echo "invalid err type: $errType";
			}
			$rtnMsg.=$urlString."<br>";
		}
		$base->ErrorObj->saveError('compareresults',$rtnMsg,&$base);
	}
//===================================================
	function comparePluginDefsDeprecated($base){
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
		$comparePath=$tmpLocal.'/'.$fileName;
		$passAry=$this->getPluginAry($comparePath,&$base);
		//$base->DebugObj->printDebug($passAry,1,'xxxd');
		$pluginCompareAry=$passAry['plugincompareary'];
		$pluginBaseAry=$passAry['pluginprofileary'];
		$rtnMsg='<pre>'."\n";
		foreach ($pluginCompareAry as $pluginName=>$pluginAry){
			$rtnMsg.="pluginname: $pluginName\n";
			$ctr=0;
			foreach ($pluginAry as $pluginFieldName=>$pluginFieldValue){
				$tstPluginFieldValue=$pluginBaseAry[$pluginName][$pluginFieldName];
				$pluginFieldValue=str_replace("\n",'',$pluginFieldValue);
				if ($pluginFieldValue != $tstPluginFieldValue){
					if ($ctr<300){
						$ctr++;
						$rtnMsg.="   $ctr) $pluginFieldName: $pluginFieldValue ne $tstPluginFieldValue\n";
					}
				}
			}	
		}
		$rtnMsg.="</pre>\n";
		$base->errorProfileAry['compareresults']=$rtnMsg;
	}
//===================================================
	function getPluginAryDeprecated($path,$base){
	//- turn file into array
		$pathAry=explode('.',$path);
		$suffix=$pathAry[1];
		if ($suffix == 'xml'){$doXml=true;}
		else {$doXml=false;}
		if ($doXml){
			$dbCompareXml=$base->FileObj->getFile($path);
			$fileAry_adj=$base->XmlObj->xml2Ary($dbCompareXml,&$base);
			$base->DebugObj->printDebug($fileAry_adj,1,'xxxf');
		}
		else {
		$fileAry_adj=array();
		$fileAry=$base->FileObj->getFileArray($path);
		$fileCnt=count($fileAry);
		$dmy=false;
		for ($lp=0;$lp<=$fileCnt;$lp=$lp+2){
			$tableFld=$fileAry[$lp];
			$tableFldAry=explode(':',$tableFld);
			$tableName_raw=$tableFldAry[1];
			$tableName=trim($tableName_raw);
			if ($tableName != NULL){
				$columnFld=$fileAry[$lp+1];
				$columnFldAry=explode(':',$columnFld);
				$columnList=$columnFldAry[1];
				$columnListAry=explode('~',$columnList);
				$columnListAry_adj=array();
				foreach ($columnListAry as $ctr=>$value){
					$valueAry=explode('%',$value);
					$columnName=$valueAry[0];
					$columnType=$valueAry[1];
					$columnListAry_adj[$columnName]=$columnType;
				}
				$dmy=true;
				$fileAry_adj[$tableName]=$columnListAry_adj;
			}
		}
		}
//- get pluginprofile array
		$query="select * from pluginprofileview";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$passAry['delimit1']='pluginname';
		$pluginProfileAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$passAry=array();
		$passAry['pluginprofileary']=$pluginProfileAry;
		$passAry['plugincompareary']=$fileAry_adj;
		return $passAry;
	}
//===================================================
	function compareStandardPromptsDefsDeprecated($base){
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
		$comparePath=$tmpLocal.'/'.$fileName;
		$passAry=$this->getStandardPromptsAry($comparePath,&$base);
		$standardPromptsCompareAry=$passAry['standardpromptscompareary'];
		$standardPromptsBaseAry=$passAry['standardpromptsprofileary'];
		$rtnMsg='<pre>'."\n";
		foreach ($standardPromptsCompareAry as $standardPromptsName=>$standardPromptsAry){
			$rtnMsg.="standardpromptsname: $standardPromptsName\n";
			$ctr=0;
			foreach ($standardPromptsAry as $standardPromptsFieldName=>$standardPromptsFieldValue){
				$tstStandardPromptsFieldValue=$standardPromptsBaseAry[$standardPromptsName][$standardPromptsFieldName];
				$standardPromptsFieldValue=str_replace("\n",'',$standardPromptsFieldValue);
				if ($standardPromptsFieldValue != $tstStandardPromptsFieldValue){
					if ($ctr<300){
						$ctr++;
						$rtnMsg.="   $ctr) $standardPromptsFieldName: $standardPromptsFieldValue ne $tstStandardPromptsFieldValue\n";
					}
				}
			}	
		}
		$rtnMsg.="</pre>\n";
		$base->errorProfileAry['compareresults']=$rtnMsg;
	}
//===================================================
	function compareStandardPromptsDefs($base){
		session_start();
		$_SESSION['sessionobj']->clearDir('comparestandardprompts');
		$sessionAry=array();
		$fileName=$base->paramsAry['filename'];
		$tmpLocal=$base->systemAry['tmplocal'];
//- get arrays from xml
		$comparePath=$tmpLocal.'/'.$fileName;
		$dbCompareXml=$base->FileObj->getFile($comparePath);
		//$theLen=count($dbCompareXml);
		//echo "cnt: $theLen<br> path: $comparePath<br> dbcomparexml: $tst<br>";//xxxd
		//exit();//xxxd
		//$dbCompareXmlAry=$base->XmlObj->xml2Ary($dbCompareXml,&$base);
		$dbCompareXmlAry=$base->XmlObj->json2Array($dbCompareXml,&$base);
		//$base->DebugObj->printDebug($dbCompareXmlAry,1,'xxxd0');//xxxd
		$ctr=0;
		$rtnMsg='<pre>'."\n";
//- put out file to compare
		$standardPromptsProfileAry_new=$dbCompareXmlAry['standardpromptsprofile'];
		$query="select * from standardpromptsprofileview order by standardpromptsname";
		$passAry=array('delimit1'=>'standardpromptsname','delimit2'=>'standardpromptsvalue');
		$standardPromptsProfileAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$dontCheckAry=array();
		$dontCheckAry['standardpromptsprofileid']='dont';
		$errAry=array();
		$ctr=0;
		$rtnMsg=null;
//- compare standardPromptsProfile_new to standardpromptsname
		foreach ($standardPromptsProfileAry_new as $standardPromptsName_new => $standardPromptsValueAry_new){
			//echo "name: $standardPromptsName_new<br>";//xxxd
			//$base->DebugObj->printDebug($standardPromptsProfileAry,1,'xxxd');exit();//xxxd
			$standardPromptsName_new=$base->UtlObj->returnFormattedData($standardPromptsName_new,'fromkey','xml',&$base);
			if (array_key_exists($standardPromptsName_new,$standardPromptsProfileAry)){
				$standardPromptsNameAry_old=$standardPromptsProfileAry[$standardPromptsName_new];
				foreach ($standardPromptsValueAry_new as $standardPromptsValue_new=>$standardPromptsAry_new){
					$standardPromptsValue_new=$base->UtlObj->returnFormattedData($standardPromptsValue_new,'fromkey','xml',&$base);
					$standardPromptsLabel_new=$standardPromptsAry_new['standardpromptslabel'];
//- now compare to current standardpromptsprofile
					if (array_key_exists($standardPromptsValue_new,$standardPromptsNameAry_old)){
						$standardPromptsValueAry_old=$standardPromptsNameAry_old[$standardPromptsValue_new];
						$standardPromptsLabel_old=$standardPromptsValueAry_old['standardpromptslabel'];
						if ($standardPromptsLabel_new != $standardPromptsLabel_old){
							$errAry[]="label:$standardPromptsName_new:$standardPromptsValue_new:$standardPromptsLabel_new:$standardPromptsLabel_old";
							//echo "name: $standardPromptsName_new, value: $standardPromptsValue_new, labels new: '$standardPromptsLabel_new', current: '$standardPromptsLabel_old' are different!!!<br>";
						}
					}
					else {
						$errAry[]="valuemissing:$standardPromptsName_new:$standardPromptsValue_new:$standardPromptsLabel_new";
						//echo "name: $standardPromptsName_new, has value '$standardPromptsValue_new' missing<br>";
					}
				}
			}
			else {
				//xxxd - need to get values of stuff
				foreach ($standardPromptsValueAry_new as $standardPromptsValue_new=>$standardPromptsAry_new){
					$standardPromptsValue_new=$base->UtlObj->returnFormattedData($standardPromptsValue_new,'fromkey','xml',&$base);
					$standardPromptsLabel_new=$standardPromptsAry_new['standardpromptslabel'];
					$errAry[]="namemissing:$standard$standardPromptsName_new:$standardPromptsValue_new:$standardPromptsLabel_new";
				}
				//echo "name: '$standardPromptsName_new' is missing, $standardPromptsLabel_new, $standardPromptsValue_new<br>";//xxxd
			}
		}
		//$base->DebugObj->printDebug($errAry,1,'xxxd');exit();//xxxd
		//exit();//xxxd
//- done with compare
		//$sessionAry_xml=$base->XmlObj->array2Xml($sessionAry);
		//$_SESSION['sessionobj']->saveSessionValue('comparedbtablesxml',$sessionAry_xml);
//- build error spot for web page
		$sessionName=$base->paramsAry['sessionname'];
		if ($sessionName != null){$sessionInsert="&sessionname=$sessionName";}
		else {$sessionInsert=null;}
		$rtnMsg="<div class=\"compareresultstitle\">Click on the line below to copy over changes!</div>\n";
		$dbName=$base->paramsAry['dbname'];
		$fileName=$base->paramsAry['filename'];
		$fileNameAry=explode('_',$fileName);
		$fromDbName=$fileNameAry[0];
		$fromDbName=str_replace('|','/',$fromDbName);
		$rtnMsg.="<div class=\"compareresultstitle\"><b>$fromDbName</b>-><b>$dbName</b></div>\n";//xxxd99
		foreach ($errAry as $ctr=>$errRow){
			$errRowAry=explode(':',$errRow);
			$errType=$errRowAry[0];
			$errName=$errRowAry[1];
			$errValue=$errRowAry[2];
			$errLabel=$errRowAry[3];
			$oldErrLabel=$errRowAry[4];
			switch ($errType){
				case 'label':
					$urlString="<span class=\"compareresults\">Update Prompt Label - name: <b>'$errName'</b>, label: <b>'$errLabel'</b> -> <b>'$oldErrLabel'</b>, value: <b>'$errValue'</b></span><a class=\"compareresults\" href=\"index.php?job=maintsystemtables&operation=fixstandardprompts&fixinput=$errRow$sessionInsert\">fix</a>\n";
					break;
				case 'namemissing':
					$urlString="<span class=\"compareresults\">Add New Prompt Group - name: <b>'$errName'</b>, label: <b>'$errLabel'</b>, value: <b>'$errValue'</b></span><a class=\"compareresults\" href=\"index.php?job=maintsystemtables&operation=fixstandardprompts&fixinput=$errRow$sessionInsert\">fix</a>\n";
					break;
				case 'valuemissing':
					$urlString="<span class=\"compareresults\">Add New Prompt - name: <b>'$errName'</b>, label: <b>'$errLabel'</b>, value: <b>'$errValue'</b></span><a class=\"compareresults\" href=\"index.php?job=maintsystemtables&operation=fixstandardprompts&fixinput=$errRow$sessionInsert\">fix</a>\n";
					break;
				default:
					echo "invalid err type: $errType";
			}
			$rtnMsg.=$urlString."<br>";
		}
		$base->ErrorObj->saveError('compareresults',$rtnMsg,&$base);
	}
//===================================================
	function fixStandardPrompts($base){
		$fixCodes=$base->paramsAry['fixinput'];
		$fixCodesAry=explode(':',$fixCodes);
		$stType=$fixCodesAry[0];
		$stName=$fixCodesAry[1];
		$stValue=$fixCodesAry[2];
		$stLabel=$fixCodesAry[3];
		//echo "type: $stType, name: $stName, value: $stValue, label: $stLabel<br>";//xxxd
		switch ($stType){
			case 'label':
				$query="select * from standardpromptsprofile where standardpromptsname='$stName' and standardpromptsvalue='$stValue'";
				$passAry=array();
				$writeRowsAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$writeRowsAry[0]['standardpromptslabel']=$stLabel;
				break;
			default:
				$theRowAry=array('standardpromptsname'=>$stName,'standardpromptsvalue'=>$stValue,'standardpromptslabel'=>$stLabel);
				$writeRowsAry=array();
				$writeRowsAry[]=$theRowAry;
		}
		$dbControlsAry=array('dbtablename'=>'standardpromptsprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');

		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//===================================================
	function fixPluginProfile($base){
		$fixCodes=$base->paramsAry['fixinput'];
		$fixCodesAry=explode(':',$fixCodes);
		$errType=$fixCodesAry[0];
		$errName=$fixCodesAry[1];
		$stValueName=$fixCodesAry[2];
		$stValue=$fixCodesAry[3];
		//echo "type: $errType, name: $errName, value: $stValue, label: $stLabel<br>";//xxxd
		switch ($errType){
			case 'value':
				$query="select * from pluginprofile where pluginname='$errName'";
				$passAry=array();
				$writeRowsAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$writeRowsAry[0][$stValueName]=$stValue;
				break;
			case 'name':
				$theRowAry=array();
				$theNamesAry=array();
				//$theRowAry['pluginname']=$errName;
				$theCnt=count($fixCodesAry);
				for ($lp=2;$lp<$theCnt;$lp=$lp+2){
					$nextLp=$lp+1;
					$theRowAry[$fixCodesAry[$lp]]=$fixCodesAry[$nextLp];
					$writeRowsAry=array();
					$writeRowsAry[0]=$theRowAry;
				}
				break;
			default:
				echo "error: $errType!";
				exit();
		}
		$dbControlsAry=array('dbtablename'=>'pluginprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//===================================================
	function getStandardPromptsAryDeprecated($path,$base){
	//- turn file into array
		$fileAry_adj=array();
		$fileAry=$base->FileObj->getFileArray($path);
		$fileCnt=count($fileAry);
		$dmy=false;
		for ($lp=0;$lp<=$fileCnt;$lp=$lp+2){
			$tableFld=$fileAry[$lp];
			$tableFldAry=explode(':',$tableFld);
			$tableName_raw=$tableFldAry[1];
			$tableName=trim($tableName_raw);
			if ($tableName != NULL){
				$columnFld=$fileAry[$lp+1];
				$columnFldAry=explode(':',$columnFld);
				$columnList=$columnFldAry[1];
				$columnListAry=explode('~',$columnList);
				$columnListAry_adj=array();
				foreach ($columnListAry as $ctr=>$value){
					$valueAry=explode('%',$value);
					$columnName=$valueAry[0];
					$columnType=$valueAry[1];
					$columnListAry_adj[$columnName]=$columnType;
				}
				$dmy=true;
				$fileAry_adj[$tableName]=$columnListAry_adj;
			}
		}
//- get standardpromptsprofile array
		$query="select * from standardpromptsprofileview";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$passAry['delimit1']='standardpromptsname';
		$standardPromptsProfileAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$passAry=array();
		$passAry['standardpromptsprofileary']=$standardPromptsProfileAry;
		$passAry['standardpromptscompareary']=$fileAry_adj;
		return $passAry;
	}
//==================================================
	function sendEmailFromForm($base){
		$base->DebugObj->printDebug("Plugin001Obj:sendEmailFromForm('base')",0);
		$params=$base->paramsAry;
		//echo '<br>---post---<br>';
		//foreach ($_POST as $id=>$vlu){echo "$id: $vlu<br>";}
		//echo '<br>---get---<br>';
		//foreach ($_GET as $id=>$vlu){echo "$id: $vlu<br>";}
		$formName=$params['form'];
 		$redir=$base->formProfileAry[$formName]['redirect'];
 		$dbTableName=$base->formProfileAry[$formName]['tablename'];
		$redirOvr=$base->paramsAry['returnovr'];
		if ($redirOvr != NULL){$redir=$redirOvr;}
		if ($redir == NULL){$dontReDirect=true;}
		else {$dontReDirect=false;}
		//- container
		$containerName=$paramsAry['container'];
		if ($containerName != NULL){$dontReDirect=true;}
		$jobLocal=$base->systemAry['joblocal'];
		if (substr($redir,0,3) == 'http'){ $urlReDir=$redir;}
		else {$urlReDir="$jobLocal$redir";}
//--- get email fields
		$theSubject=$base->formProfileAry[$formName]['formemailsubject'];
		$theTo=$base->formProfileAry[$formName]['formemail'];
		//$base->DebugObj->printDebug($base->formElementProfileAry[$formName],1,'xxxd');
		$theMessage=NULL;
		foreach ($params as $name=>$value){
			if (array_key_exists($name,$base->formElementProfileAry[$formName])){
				$theMessage.="$name: $value\n";
			}
		} 
//--- send mail
		$base->UtlObj->sendMail($theTo,$theSubject,$theMessage,&$base);
//--- redirect - if has tablename then will update table then redirect
//--- dont redirect if coming in from ajax
		if (!$dontReDirect){
			if ($dbTableName == NULL){
				$pos=strpos('x'.$urlReDir,'sessionname',0);
				if ($pos<=0){
					$sessionName=$base->paramsAry['sessionname'];
					if ($sessionName != NULL){$urlReDir.="&sessionname=$sessionName&donterase=1";}
				}
				$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
				$base->UtlObj->appendValue('debug',"header to $urlReDir_formatted<br>",&$base);
				header("Location: $urlReDir_formatted");
			}
		}
		else {echo 'done';}
	}
//==================================================
	function cloneRow($base){
		    	$debugIt=false;//xxxd
		$cloneTableName=$base->paramsAry['clonetablename'];
		$cloneTableIdName=$base->paramsAry['clonetableidname'];
		$cloneTableId=$base->paramsAry[$cloneTableIdName];
		$cloneSubTableName=$base->paramsAry['clonechildtablename'];
		$cloneSubTableIdName=$base->paramsAry['clonechildtableidname'];
		$cloneNewName=$base->paramsAry['clonenewname'];
		$cloneNewNameAry=explode(',',$cloneNewName);
		$cloneFieldName=$base->paramsAry['clonefieldname'];
		$cloneFieldNameAry=explode(',',$cloneFieldName);
		$jobProfileId=$base->paramsAry['jobprofileid'];
//- get old row to clone
		$query="select * from $cloneTableName where $cloneTableIdName=$cloneTableId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$cloneAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		if ($debugIt){echo 'debug<br>';$base->DebugObj->printDebug($cloneAry,1,"xxx: row to clone");}
//- get rid of id field so it will insert
//!!! need to overlay jobprofileid here since it may be coming from another job
		$cloneAry[0]['jobprofileid']=$jobProfileId;
		unset($cloneAry[0][$cloneTableIdName]);
//- change all fields set to be changed
		$noFields=count($cloneNewNameAry);
		for ($theLp=0;$theLp<$noFields;$theLp++){
			$newName=$cloneNewNameAry[$theLp];
			$newFieldName=$cloneFieldNameAry[$theLp];
			$cloneAry[0][$newFieldName]=$newName;
		}
//- insert the row as a new one
   		$dbControlsAry=array('dbtablename'=>$cloneTableName);
    	$dbControlsAry['writerowsary']=$cloneAry;
    	if ($debugIt){$base->DebugObj->printDebug($dbControlsAry,1,"xxx: new row to insert");}
    	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
//- get id for subgroups
    	$andInsert=' and ';
 		$query="select * from $cloneTableName where jobprofileid = $jobProfileId";
 		for ($theLp=0;$theLp<$noFields;$theLp++){
			$newName=$cloneNewNameAry[$theLp];
			$newFieldName=$cloneFieldNameAry[$theLp];
			$query.="$andInsert $newFieldName='$newName'";
			$andInsert=' and ';
		}
    	$debugIt=false;
		if ($debugIt) {echo "query to get new id: $query<br>";}
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dmyAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		if ($debugIt){$base->DebugObj->printDebug($dmyAry,1,"xxx: row to get new id from");}
		$newCloneTableId=$dmyAry[0][$cloneTableIdName];
		if ($debugIt){echo "new id: $newCloneTableId<br>";}
//- get all of the sub clone rows to be copied
    	$query="select * from $cloneSubTableName where $cloneTableIdName=$cloneTableId";
    	if ($debugIt){echo "query to get subgroups: $query<br>";}
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$cloneSubTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		if ($debugIt){$base->DebugObj->printDebug($cloneSubTableAry,1,'xxx: sub items to change');}
//- remove all ids and changpointers
		$cloneSubTableCnt=count($cloneSubTableAry);
		for ($theLp=0;$theLp<$cloneSubTableCnt;$theLp++){
			unset($cloneSubTableAry[$theLp][$cloneSubTableIdName]);
			$cloneSubTableAry[$theLp][$cloneTableIdName]=$newCloneTableId;	
		}
//- write them all out
  		$dbControlsAry=array('dbtablename'=>$cloneSubTableName);
    	$dbControlsAry['writerowsary']=$cloneSubTableAry;
    	if ($debugIt){$base->DebugObj->printDebug($dbControlsAry,1,'xxx: sub rows to write');}
      	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//==================================================
	function mergeTableDefs($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd');
		$dbTableName=$base->paramsAry['dbtablename'];
		$sourceDomainName=$base->paramsAry['sourcedomain'];
		$sourceFileName=$sourceDomainName.'_dbtabledefs';
		$tmpDirPath=$base->systemAry['tmplocal'];
		$thePath=$tmpDirPath.'/'.$sourceFileName;
		$sourceFileAry=$base->FileObj->getFileArray($thePath);
		$rowCnt=count($sourceFileAry);
		$dbSourceColumnsAry=array();
		$IGotIt=false;
		for ($mainLp=0;$mainLp<$rowCnt;$mainLp=$mainLp+2){
			$dbSourceTableName_raw=$sourceFileAry[$mainLp];
			$pos=strpos($dbSourceTableName_raw,':',0);
			if ($pos>0){
				$dbSourceTableNameAry=explode(':',$dbSourceTableName_raw);
				$dbSourceTableName=$dbSourceTableNameAry[1];	
			}
			else {$dbSourceTableName=$dbSourceTableName_raw;}
			$dbSourceTableName=trim($dbSourceTableName);
			//echo 'src: x'.$dbSourceTableName.'x, db: x'.$dbTableName.'x<br>';//xxxd
			if ($dbTableName == $dbSourceTableName){
				$IGotIt=true;
				$dbSourceColumns=$sourceFileAry[$mainLp+1];
				$wrkAry=explode(':',$dbSourceColumns);
				$dbSourceColumns=$wrkAry[1];
				//echo "orig: $dbSourceColumns<br>src: $dbSourceWorkColumns<br>";//xxxd
				$wrkAry=explode('~',$dbSourceColumns);
				$theCnt=count($wrkAry);
				$dbColumnSrcAry=array();
				for ($subLp=0;$subLp<$theCnt;$subLp++){
					$dbColumnLine=$wrkAry[$subLp];
					$dbColumnLineAry=explode('%',$dbColumnLine);
					//echo "dbcolumnline: $dbColumnLine<br>";
					$dbColumnSrcName=$dbColumnLineAry[0];
					$dbColumnSrcType=$dbColumnLineAry[1];
					$dbColumnSrcAry[$dbColumnSrcName]=$dbColumnSrcType;
					//echo "$dbColumnSrcName, $dbColumnSrcType<br>";//xxxd
				}
				//$base->DebugObj->printDebug($dbColumnSrcAry,1,'xxxd');
			} // end if dbtablename=dbsourcetablename
		} // end of for mainLp
		if ($IGotIt){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbColumnDestAry=$dbControlsAry['dbtablemetaary'];
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
			$query="select * from dbtableprofileview where dbtablename='$dbTableName'";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($dataAry,1,'xxxd');
			$dbTableProfileId=$dataAry[0]['dbtableprofileid'];
			if ($dbTableProfileId != NULL){
				$writeRowsAry=array();
				$query="select * from validateprofileview where validatename='All'";
				$result=$base->DbObj->queryTable($query,'read',&$base);
				$passAry=array();
				$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				//$base->DebugObj->printDebug($dataAry,1,'xxxd');
				$validateProfileId=$dataAry[0]['validateprofileid'];
				if ($validateProfileId == NULL){
					echo 'invalid validateprofileid when validatename is All, using 1 as default<br>';
					$validateProfileId=1;	
				}
				foreach ($dbColumnSrcAry as $dbColumnName=>$dbColumnSrcType){
					$dbColumnDestType=$dbColumnDestAry[$dbColumnName]['dbcolumntype'];	
					if ($dbColumnDestType == NULL) {
						$writeRowsAry[]=array('dbcolumnname'=>$dbColumnName,'dbcolumntype'=>$dbColumnSrcType,'dbtableprofileid'=>$dbTableProfileId,'validateprofileid'=>$validateProfileId);
					}
				}
				$dbControlsAry=array();
				$dbControlsAry['dbtablename']='dbcolumnprofile';
				$dbControlsAry['writerowsary']=$writeRowsAry;
				$noWritten=count($writeRowsAry);
				$base->errorProfileAry['compareresults']=$noWritten." added to table $dbTableName";
				$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
				//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd: to write');
			} //end if dbtableprofild ne null
			else { echo "table '$dbTableName' must exist to add columns!!!<br>";}
		}
		//$base->DebugObj->printDebug($sourceFileAry,1,'xxxd');
		//echo "source: $sourceDomainName, dbtablename: $dbTableName, srcpath: $thePath<br>";
	}
//=======================================
	function changeUser($base){
		$base->DebugObj->printDebug("Plugin001Obj:changeUser",0); //xx (h)
		$userName=$base->paramsAry['username'];
		$userPassword=$base->paramsAry['userpassword'];
		$userPassword=str_replace("\n",'',$userPassword);
		$userPassword=str_replace("\r",'',$userPassword);
		$userPassword=str_replace("\0",'',$userPassword);
		$_SESSION['userobj']->setUserFields($userName,$userPassword,&$base);
		$base->DebugObj->printDebug("-rtn:changeUser",0);
	}
//========================================
	function writeDbFromAjax($base){
		$ajaxFieldDelim="~";
		$ajaxLineDelim="`";
		$ajaxSubLineDelim="|";
		$sentData=$base->paramsAry['senddata'];
		//echo "sentdata: $sentData";//xxxd
		$sentDataAry=explode($ajaxLineDelim,$sentData);
		$theLen=count($sentDataAry);
		$dbTableDataAry=array();
		$dbTableName='none';
		$dbTableDefs='none';
		$gotTableData=false;
		$gotTableDefs=false;
		$gotTableName=false;
		$gotDelKeys=false;
		$gotKeyName=false;
		$statusKey='status:ok';
		$statusMsg=null;
		$updStrg=null;
		$updDelim=null;
		$keyName=null;
		$keyNameListAry=null;
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
			case 'deldata':
				$delList=$lineAry[1];
				$gotDelKeys=true;
			break;
			case 'keyname':
				$keyName=$lineAry[1];
				$gotKeyName=true;
			break;
			}
		}
		if ($gotTableData&&$gotTableDefs&&$gotTableName&&$gotKeyName){
			$tableDefsAry=explode($ajaxFieldDelim,$dbTableDefs);
			$dbControlsAry=array();
			$dbControlsAry['dbtablename']=$dbTableName;
			$writeRowsAry=array();
			$writeRowsTempIdAry=array();
			$noRows=count($dbTableDataAry);
			$noCols=count($tableDefsAry);
			for ($lp=0;$lp<$noRows;$lp++){
				$dataRow=$dbTableDataAry[$lp];
				$base->FileObj->writeLog('ajax',"datarow: $dataRow",&$base);//xxxd
				$dataRowAry_raw=explode($ajaxFieldDelim,$dataRow);
				$dataRowAry=array();
				for ($lp2=0;$lp2<$noCols;$lp2++){
					$dataRowAry[$tableDefsAry[$lp2]]=$dataRowAry_raw[$lp2];	
				}
				//- need to check tempprofileid and null out key if it is set
				$itIsTemp=$dataRowAry['tempprofileid'];
				$itIsTemp=$base->UtlObj->returnFormattedData($itIsTemp,'boolean','internal',&$base);
				if ($itIsTemp){
					$tempKeyId=$dataRowAry[$keyName];
					$dataRowAry['tempkeyid']=$tempKeyId;
					unset($dataRowAry[$keyName]);	
					$dataRowAry['tempprofileid']=false;				
				}
				$writeRowsAry[]=$dataRowAry;
			}
			$dbControlsAry['writerowsary']=$writeRowsAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
			$base->DbObj->writeToDb($dbControlsAry,&$base);
			$tempKeyIdAry=$base->ErrorObj->getKeyConv(&$base);
			$tempKeyConv=null;
			$delim=null;
			foreach ($tempKeyIdAry as $tempKeyId=>$realKeyId){
				$tempKeyConv.=$delim.$tempKeyId.'%'.$realKeyId;
				$delim='~';
			}
			$checkStrg=$base->ErrorObj->retrieveAllErrors(&$base);
			$base->FileObj->writeLog('ajax','checkstrg: '+$checkStrg,&$base);//xxxd
			if ($checkStrg!=null){
				$statusKey='status:error';
				$statusMsg.='statusmsg:'.$checkStrg;	
			}
			else {
				if ($noRows<2){$rowWords='row has';}
				else {$rowWords='rows have';}
				$statusMsg.="statusmsg:$noRows $rowWords been updated!\n";
			}
		}
		else {
				$errorFlg=false;
				$errMsg=null;
				if (!$gotTableData){$errMsg="There are no table rows to update!";}
				else if(!$gotTableDefs){$errMsg="There are no table definitions to use!";$errorFlg=true;}
				else if(!$gotTableName){$errMsg="The table name is missing from the transmission!";$errorFlg=true;}
				else if (!$gotKeyName){$errMsg="The key name is missing from the transmission!";$errorFlg=true;}
				if ($errorFlg){$statusKey='status:error';}
				else {$statusKey='status:ok';}
				if ($errMsg != null){
					$statusMsg.='statusmsg:'.$errMsg."\n";
				}
		}
//============= do deletes
		$delStrg="";$delDelim="";
		if ($gotDelKeys&&$gotTableDefs&&$gotTableName){
			//echo "dbtablename: $dbTableName\ndbtabledefs: $dbTableDefs\ndellist: $delList\n";
			//- delete em	
			$dbTableDefsAry=explode('~',$dbTableDefs);
			$delListAry=explode('~',$delList);
			//xxxr- no - get keyname from getdbtable
			//$keyName=$dbTableDefsAry[0];
			if ($dbTableName != NULL){
				foreach ($delListAry as $ctr=>$keyId){
					//echo "usekeyname: $useKeyName, usekeyvalue: $useKeyValue<br>";
					if ($keyId != NULL){
						$query="delete from $dbTableName where $keyName=$keyId";
						//echo "query: $query<br>";//xxx
						$result=$base->DbObj->queryTable($query,'updatequietly',&$base);
						$checkStrg=$base->ErrorObj->retrieveAllErrors(&$base);
						if ($checkStrg!=null){
							$statusKey='status:error';
							$statusMsg.='statusmsg'.$checkStrg."\n";	
						}
						else {
							$statusMsg.="statusmsg: $keyId key deleted\n";
							$delStrg.="$keyId$delDelim";	
							$delDelim=':';
						}
					}
					else {
						$statusKey='status:error';
						$statusMsg.="statusmsg:keyid is null for one row\n";	
					}
				}
			}
			else {
				$statusKey='status:error';
				$statusMsg.="statusmsg:Cant do deletes because one is null( dbtablename: $dbTableName, keyname: $keyName)\n";	
			}
		}
		echo "|!!message!!|$statusKey|$statusMsg|upd:$updStrg|del:$delStrg|tempkeyconv:$dbTableName:$tempKeyConv";
	}
//========================================
	function insertDbFromAjax($base){
		$ajaxFieldDelim="~";
		$ajaxLineDelim="`";
		$ajaxSubLineDelim="|";
		$sentData=$base->paramsAry['senddata'];
		//echo "sentdata: $sentData";//xxxf
		$sentDataAry=explode($ajaxLineDelim,$sentData);
		$theLen=count($sentDataAry);
		$dbTableDataAry=array();
		$dbTableName='none';
		$dbTableDefs='none';
		$gotTableData=false;
		$gotTableDefs=false;
		$gotTableName=false;
		$statusKey='okdonothing';
		$statusMsg=null;
		$updStrg=null;
		$updDelim=null;
		$errorString=null;
		$formName=null;
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
			default:
				$errorString.=$lineAry[1].',';
			}
		}
		if ($gotTableData&&$gotTableDefs&&$gotTableName){
			$tableDefsAry=explode($ajaxFieldDelim,$dbTableDefs);
			$dbControlsAry=array();
			$dbControlsAry['dbtablename']=$dbTableName;
			$writeRowsAry=array();
			$writeRowsTempIdAry=array();
			$theEmailMessage=null;
			$noRows=count($dbTableDataAry);
			$noCols=count($tableDefsAry);
			for ($lp=0;$lp<$noRows;$lp++){
				$dataRow=$dbTableDataAry[$lp];
				$base->FileObj->writeLog('ajax',"datarow: $dataRow",&$base);//xxxd
				$dataRowAry_raw=explode($ajaxFieldDelim,$dataRow);
				$dataRowAry=array();
				$theDelim=null;
				for ($lp2=0;$lp2<$noCols;$lp2++){
					$dataRowAry[$tableDefsAry[$lp2]]=$dataRowAry_raw[$lp2];	
					$theEmailMessage.="$theDelim$tableDefsAry[$lp2]: $dataRowAry_raw[$lp2]";
					$theDelim=', %cr%';
				}
				$writeRowsAry[]=$dataRowAry;
			}
			$dbControlsAry['writerowsary']=$writeRowsAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
			$base->DbObj->writeToDb($dbControlsAry,&$base);
			$checkStrg=$base->ErrorObj->retrieveAllErrors(&$base);
			$base->FileObj->writeLog('ajax','checkstrg: '+$checkStrg,&$base);//xxxd
			if ($checkStrg!=null){
				$statusKey='error';
				$statusMsg.=$checkStrg;	
			}
			else {
				if ($noRows<2){$rowWords='row has';}
				else {$rowWords='rows have';}
				$statusMsg.="$noRows $rowWords been updated!\n";
			}
		}
		else {
				$errorFlg=false;
				$errMsg=null;
				if (!$gotTableData){$errMsg="There are no table rows to update!";}
				else if(!$gotTableDefs){$errMsg="There are no table definitions to use!";$errorFlg=true;}
				else if(!$gotTableName){$errMsg="The table name is missing from the transmission!";$errorFlg=true;}
				if ($errorFlg){$statusKey='error';}
				else {$statusKey='okdonothing';}
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
				$theEmailMessage=$base->UtlObj->returnFormattedString($theEmailMessage,&$base);
				$base->UtlObj->sendMail($formEmail,$theEmailSubject,$theEmailMessage,&$base);
			}
		}
		echo "$statusKey|$statusMsg|upd:$updStrg|email:$emailStuff";
	}
//=================================================
	function generalTest($base){
			//$colRegEx='/[0-9]\/[0-9]\/2009/';
			//$colData='6/2/2009';
			$colRegEx='/9/';
			$colData=a;
			$tst = preg_match($colRegEx,$colData);
			echo "tst: $tst";
	}
//=================================================
	function deskTopAlert($applPassedAry,$base){
		$alertLog='desktopalert.log';
		$companyName=$applPassedAry['companyname'];
		$passAry=array('thedate'=>'today');
		$dateAry=$base->UtlObj->getDateInfo($passAry,$base);
		$todayDate=$dateAry['date_v1'];
		$todayTime=$dateAry['time_v1'];
		$todayHours=$dateAry['hours'];
		$todayMinutes=$dateAry['minutes'];
		//$todayHours=17;$todayMinutes=45;//xxx
		$todayTime="$todayHours:$todayMinutes:00";//xxxd
		$todayTotalMinutes=$todayHours*60+$todayMinutes;
		//$base->DebugObj->printDebug($dateAry,1,'xxx');
		$logEntry="-log on and check for alerts to send-";
		$base->FileObj->writeLog($alertLog,$logEntry,&$base);
		$query="select * from clientjobprofileview where clientjobdate='$todayDate' and clientjobemailsent is not true order by clientjobtime ";
		//echo "query: $query\n";//xxx
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$cnt=count($workAry);
		//echo "cnt selected: $cnt\n";//xxx
		$writeRowsAry=array();
		$doUpdate=false;
		foreach ($workAry as $ctr=>$entryAry){
			$theDate_raw=$entryAry['clientjobdate'];
			$theDate=$base->UtlObj->convertDate($theDate_raw,'date1',&$base);
			$entryAry['clientjobdate']=$theDate;
			$theTime=$entryAry['clientjobtime'];
			$firstName=$entryAry['clientfirstname'];
			$lastName=$entryAry['clientlastname'];
			$theAddress=$entryAry['clientaddress'];
			$theCity=$entryAry['clientcity'];
			$theState=$entryAry['clientstate'];
			$jobType=$entryAry['clientjobtype'];
			$clientJobNotify=$entryAry['clientjobnotify'];
			$emailGroup=$entryAry['emailgroup'];
			$employeeFirstName=$entryAry['employeefirstname'];
			$employeeLastName=$entryAry['employeelastname'];
			$theTotalMinutes=$base->UtlObj->convertTime($theTime,&$base);
			$cutoffTotalMinutes=$theTotalMinutes-$clientJobNotify;
			if ($cutoffTotalMinutes<=$todayTotalMinutes){$doEmail=true;}
			else {$doEmail=false;}
			if ($doEmail){
				//echo "doit: $employeeFirstName $employeeLastName start time: $theTime, start mins: $theTotalMinutes - notify mins: $clientJobNotify, = cutoff mins: $cutoffTotalMinutes, < now mins: $todayTotalMinutes\n";//xxxd
				$theSubject="$firstName $lastName $jobType: $theDate $theTime";
				$theMessage= "job......... $jobType\n";
				$theMessage.="time........ $theDate $theTime\n";
				$theMessage.="client...... $firstName $lastName\n";
				$theMessage.="address..... $theAddress\n";
				$theMessage.="             $theCity $theState $theZip\n\n";
				$theMessage.="associate... $employeeFirstName $employeeLastName\n";
				$entryAry['clientjobemailsent']=true;
				$writeRowsAry[]=$entryAry;
				$base->UtlObj->sendMail($emailGroup,$theSubject,$theMessage,&$base);
				$logEntry="send email to $emailGroup, $theSubject";
				$base->FileObj->writeLog($alertLog,$logEntry,&$base);
				$doUpdate=true;
			} else {
				//echo "dont: $employeeFirstName $employeeLastName start time: $theTime, start mins: $theTotalMinutes - notify mins: $clientJobNotify, = cutoff mins: $cutoffTotalMinutes, < now mins: $todayTotalMinutes\n";//xxxd	
			}
		}
		if ($doUpdate){
			$dbControlsAry=array('dbtablename'=>'clientjobprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$base->DbObj->writeToDb($dbControlsAry,&$base);
		}
	}
//==================================================
	function buildCssFromForm($base){
		$errorOccured=false;
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$formProfileId=$base->paramsAry['formprofileid'];
		if ($jobProfileId == null || $formProfileId == null){
			echo "jobprofile: $jobProfileId or formprofileid: $formProfileId is null!!!";
			exit();
		}
		if ($formProfileId != null){
			$query="select * from formprofileview where formprofileid=$formProfileId";
			$passAry=array();
			$dataAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$query="select * from formelementprofileview where formprofileid=$formProfileId";
			$dataElementAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			//echo 'validate fields<br>';
			$formPrefix=$dataAry[0]['formprefix'];
			//-xxxd test
			$formPrefix='form';
			//-
			if ($formPrefix != null){
				//$query="select * from cssPrefixProfileView where prefixname='$formPrefix'";
				//$prefixAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$formRowPixels=$prefixAry[0]['formrowpixels'];
				$formTopPixels=$prefixAry[0]['formtoppixels'];
				$formLeftPixels=$prefixAry[0]['formleftpixels'];
				//-xxxd test
				$formRowPixels=40;
				$formColPixels=7;
				$formTopPixels=20;
				$formLeftPixels=40;
				$formBetweenElementsPixels=20;
				//-
				if ($formRowPixels == null || $formColPixels == null || $formLeftPixels == null || $formTopPixels == null){
					$errorOccured=true;
				}
//------------------ build updateCssAry
				$updateCssAry=array();
				foreach ($dataElementAry as $ctr=>$elementAry){
					$tableRow=$elementAry['formelementtablerow'];
					$tableCol=$elementAry['formelementtablecol'];
					$elementCols=$elementAry['formelementcols'];
					$elementClass=$elementAry['formelementclass'];
					$elementName=$elementAry['formelementname'];
					if ($elementClass == null){
						$elementClass=$elementName;
					}
					if ($tableRow == null || 
						$tableCol == null || 
						$elementClass == null ||
						$elementCols == ''){
						echo "error(something is null): class: $elementClass, tablerow: $tableRow, tablecol: $tableCol, element cols: $elementCols<br>";	
						$errorOccured=true;
					}
					else {
						$tmpAry=array('class'=>$elementClass,'cols'=>$elementCols);
						$updateCssAry[$tableRow][$tableCol]=$tmpAry;
					}
				}
				//$base->DebugObj->printDebug($updateCssAry,1,'xxxd');//xxxd
				//exit();//xxxd
				if (!$errorOccured){
					//$base->DebugObj->printDebug($updateCssAry,1,'xxxd: updatecssary');
//------------------- use updateCssAry to update cssprofileary
					$noRows=count($updateCssAry);
					$topPixels=$formTopPixels;
					for ($rowLp=0;$rowLp<$noRows;$rowLp++){
						$elementNoCols=count($updateCssAry[$rowLp]);
						$leftPixels=$formLeftPixels;
						//echo "row: $rowLp, no cols: $elementNoCols<br>";//xxxd
						for ($elementColLp=0;$elementColLp<$elementNoCols;$elementColLp++){
							$formElementAry=$updateCssAry[$rowLp][$elementColLp];
							$theClass=$formElementAry['class'];
							//echo "class: $theClass, row: $rowLp, col: $elementColLp<br>";//xxxd
							//$base->DebugObj->printDebug($updateCssAry[$rowLp][$elementColLp],1,'xxxd');
							//$base->DebugObj->printDebug($formElementAry,1,'xxxd');//xxxd
							//echo "col lp: $elementColLp, nocols: $elementNoCols, class: $theClass<br>";//xxxd
							//- main css
							$useTheClass=$theClass.'_main';
							$cssProfileId=$this->buildCssProfileRow($jobProfileId, $formPrefix, $useTheClass, 'none','div',&$base);
							$this->buildCssElementProfileRow($cssProfileId,'position','absolute',&$base);
							$this->buildCssElementProfileRow($cssProfileId,'left',$leftPixels,&$base);
							$this->buildCssElementProfileRow($cssProfileId,'top',$topPixels,&$base);
//- main div:hover only if it exists!
							$readQuery="select * from cssprofileview where jobprofileid=$jobProfileId and prefix='$formPrefix' and cssclass='$useTheClass' and cssid='none' and htmltag='div:hover'";	
							$passAry=array();
							$dataElementAry=$base->DbObj->queryTableRead($readQuery,$passAry,&$base);
							$cssProfileId=$dataElementAry[0]['cssprofileid'];
							if ($cssProfileId != null){
								$this->buildCssElementProfileRow($cssProfileId,'position','absolute',&$base);
								$this->buildCssElementProfileRow($cssProfileId,'left',$leftPixels,&$base);
								$this->buildCssElementProfileRow($cssProfileId,'top',$topPixels,&$base);
							}
//- label css
							$useTheClass=$theClass.'_label';
							$cssProfileId=$this->buildCssProfileRow($jobProfileId, $formPrefix, $useTheClass, 'none','span',&$base);
							$this->buildCssElementProfileRow($cssProfileId,'display','inline',&$base);
/*
//- content css	--- need to work on this!!! xxxd
							$useTheClass=$theClass.'_content';
							$cssProfileId=$this->buildCssProfileRow($jobProfileId, $formPrefix, $useTheClass, 'none','span',&$base);
							$this->buildCssElementProfileRow($cssProfileId,'position','relative',&$base);
*/
//- setup for next field
							$noCols=$formElementAry['cols'];
							$elementLength_pixels=$noCols*$formColPixels;
							$leftPixels+=$elementLength_pixels+$formBetweenElementsPixels;
						}
						$topPixels+=$formRowPixels;
					}
				}
				else {
					echo 'an error has occurred!<br>';
				}
			}
		}
	}
//---
	function buildCssProfileRow($jobProfileId,$formPrefix,$useTheClass,$theId,$htmlTag,$base){
		$readQuery="select * from cssprofileview where jobprofileid=$jobProfileId and prefix='$formPrefix' and cssclass='$useTheClass' and cssid='$theId' and htmltag='$htmlTag'";	
		$passAry=array();
		//echo "formprefix: $formPrefix<br>";//xxxd
		//echo "get cssprofile query: $readQuery<br>";//xxxd
		$dataElementAry=$base->DbObj->queryTableRead($readQuery,$passAry,&$base);
		$cssProfileId=$dataElementAry[0]['cssprofileid'];
		if ($cssProfileId == null){
			$updateQuery="insert into cssprofile (jobprofileid, prefix,cssclass,cssid,htmltag) values ($jobProfileId, '$formPrefix', '$useTheClass', '$theId', '$htmlTag')";
			//echo "didnt find it: $updateQuery<br>";//xxxd
			$base->DbObj->queryTable($updateQuery,'update',&$base);	
			//echo "now try to get cssprofileid again: readQuery: $readQuery";		
			$dataElementAry=$base->DbObj->queryTableRead($readQuery,$passAry,&$base);
			$cssProfileId=$dataElementAry[0]['cssprofileid'];
			if ($cssProfileId == null){
				echo "cssprofileid is null, it shouldnt be!!!";
				exit();
			}
		}
		return $cssProfileId;
	}
//---
	function buildCssElementProfileRow($cssProfileId,$propertyName, $propertyValue,$base){
		$query="select * from csselementprofileview where cssprofileid=$cssProfileId and csselementproperty='$propertyName'";	
		$passAry=array();
		//echo "get csselementprofile query: $query<br>";
		$dataElementAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$cssElementProfileId=$dataElementAry[0]['csselementprofileid'];
		//echo "csselementprofileid: $cssElementProfileId<br>";//xxxd
		if ($cssElementProfileId == null){
			$updateQuery="insert into csselementprofile (cssprofileid,csselementproperty,csselementvalue) values ($cssProfileId, '$propertyName', '$propertyValue')";
			//echo "didnt find it query: $updateQuery<br>";//xxxd
			$okFlag=$base->DbObj->queryTable($updateQuery,'update',&$base);			
			$dataElementAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$cssElementProfileId=$dataElementAry[0]['csselementprofileid'];
		}
		else {
			$updateQuery="update csselementprofile set csselementvalue='$propertyValue' where csselementprofileid=$cssElementProfileId";
			//echo "on file query: $updateQuery<br>";//xxxd
			$okFlag=$base->DbObj->queryTable($updateQuery,'update',&$base);
		}
		return $cssProfileId;
	}
//==================================================
	function getJobDetails($base){
		$jobName=$base->paramsAry['jobname'];
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$query="select companyprofileid, companyname from jobprofileview where jobprofileid='$jobProfileId'";
		$passAry=array();
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$companyProfileId=$workAry[0]['companyprofileid'];
		$companyName=$workAry[0]['companyname'];
//- all containers info
		$query="select * from containerprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'containername');
		$this->workAreaAry['containersinfoary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- all containers elements
		$query="select * from containerelementprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'containername','delimit2'=>'containerelementno');
		$this->workAreaAry['containersary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- all menus info
		$query="select * from menuprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'menuname');
		$this->workAreaAry['menusinfoary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- menus elements
		$query="select * from menuelementprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'menuname','delimit2'=>'menuelementno');
		$this->workAreaAry['menusary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- tables
		$query="select * from tableprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'tablename');
		$this->workAreaAry['tableinfoary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- table columns
		$query="select * from columnprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'tablename','delimit2'=>'columnno');
		$this->workAreaAry['tableary']=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- the rest
		$this->workAreaAry['containersdisplayedary']=array();
		$elementName='bodycontainer';
		$elementType='container';
		$jobDetailString="--- company: $companyName($companyProfileId)   jobname: $jobName($jobProfileId)---<br>";
//- bodycontainer
		$jobDetailString.="<br>1) bodycontainer";
		$jobDetailString.=$this->getContainerString($elementName,1,&$base);
		$jobDetailString.="<br>--------------nonconnected containers------------------------------<br>";
		$this->workAreaAry['containersdisplayedary']['bodycontainer']='yo';
		foreach ($this->workAreaAry['containersary'] as $containerName=>$containerAry){
			if (!array_key_exists($containerName,$this->workAreaAry['containersdisplayedary'])){
				$jobDetailString.="<br>1) $containerName";
				$jobDetailString.=$this->getContainerString($containerName,1,&$base);
			}
		}
		$base->ErrorObj->saveError('jobdetails',$jobDetailString,&$base);
	}
//==================================================
	function getContainerString($containerName,$theLevel,&$base){
		$levelSpaceUnit="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$theLevelSpace=null;
		for ($dmy=0;$dmy<$theLevel;$dmy++){$theLevelSpace.=$levelSpaceUnit;}
		//$jobDetailString="<br>$theLevelSpace--- container $containerName ---";
		$containerAry=$this->workAreaAry['containersary'][$containerName];
		$containerInfoAry=$this->workAreaAry['containersinfoary'][$containerName];
		$containerShow_raw=$containerInfoAry['containershow'];
		$containerShow=$base->UtlObj->returnFormattedData($containerShow_raw,'boolean','sql',&$base);
		$containerDividerShow_raw=$containerInfoAry['containerdividershow'];
		$containerDividerShow=$base->UtlObj->returnFormattedData($containerDividerShow_raw,'boolean','sql',&$base);
		$containerFormat=$containerInfoAry['containerformat'];
		//- all classes and ids
		$containerClass=$containerInfoAry['containerclass'];
		$containerId=$containerInfoAry['containerid'];
		$containerHeaderClass=$containerInfoAry['containerheaderclass'];
		$containerHeaderId=$containerInfoAry['containerheaderid'];
		$containerFooterClass=$containerInfoAry['containerfooterclass'];
		$containerFooterId=$containerInfoAry['containerfooterid'];
		$containerContentClass=$containerInfoAry['containercontentclass'];
		$containerContentId=$containerInfoAry['containercontentid'];
		$jobDetailString.=" show: $containerShow, divshow: $containerDividerShow, format: $containerFormat, class: $containerClass, id: $containerId, hclass: $containerHeaderClass,
		hid: $containerHeaderId, cclass: $containerContentClass, cid: $containerContentId, fclass: $containerFooterClass, fid: $containerFooterId";
		$noEle=count($containerAry);
		for ($lp=1;$lp<=$noEle;$lp++){
			$eleName=$containerAry[$lp]['containerelementname'];
			$eleType=$containerAry[$lp]['containerelementtype'];
			$jobDetailString.="<br>$theLevelSpace$lp) $eleName($eleType)";
			$useTheLevel=$theLevel+1;
			switch ($eleType){
				case 'container':
					$jobDetailString.=$this->getContainerString($eleName,$useTheLevel,&$base);
					$this->workAreaAry['containersdisplayedary'][$eleName]='yo';
					break;
				case 'menu':
					$menuType=$this->workAreaAry['menusinfoary'][$eleName]['menutype'];
					$menuClass=$this->workAreaAry['menusinfoary'][$eleName]['menuclass'];
					$menuId=$this->workAreaAry['menusinfoary'][$eleName]['menuid'];
					$jobDetailString.="($menuType) class: $menuClass, id: $menuId ";
					$jobDetailString.=$this->getMenuString($eleName,$useTheLevel,&$base);
					break;
				case 'table':
					$jobDetailString.=$this->getTableString($eleName,$useTheLevel,&$base);
					break;
				default:
			}
		}
		$jobDetailString.="<br>";
		return $jobDetailString;
	}
//==================================================
	function getMenuString($menuName,$theLevel,&$base){
		$levelSpaceUnit="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$theLevelSpace=null;
		for ($dmy=0;$dmy<$theLevel;$dmy++){$theLevelSpace.=$levelSpaceUnit;}
		//$jobDetailString="<br>$theLevelSpace--- container $containerName ---";
		$menuAry=$this->workAreaAry['menusary'][$menuName];
		$noEle=count($menuAry);
		for ($lp=1;$lp<=$noEle;$lp++){
			$eleName=$menuAry[$lp]['menuelementname'];
			$eleType=$menuAry[$lp]['menuelementtype'];
			$eleUrl=$menuAry[$lp]['menuelementurl'];
			$eleClass=$menuAry[$lp]['menuelementclass'];
			$eleId=$menuAry[$lp]['menuelementid'];
			$eleEvents_raw=$menuAry[$lp]['menuelementeventattributes'];
			$eleEvents=str_replace('%sglqt%',"'",$eleEvents_raw);
			$eleEvents=str_replace('%dblqt%','"',$eleEvents);
			$jobDetailString.="<br>$theLevelSpace$lp) $eleName($eleType) ";
			$useTheLevel=$theLevel+1;
			switch ($eleType){
				case 'container':
					$jobDetailString.=$this->getContainerString($eleName,$useTheLevel,&$base);
					$this->workAreaAry['containersdisplayedary'][$eleName]='yo';
					break;
				case 'menu':
					$menuType=$this->workAreaAry['menusinfoary'][$eleName]['menutype'];
					$menuClass=$this->workAreaAry['menusinfoary'][$eleName]['menuclass'];
					$menuId=$this->workAreaAry['menusinfoary'][$eleName]['menuid'];
					$jobDetailString.="($menuType) class: $menuClass, id: $menuId ";
					$jobDetailString.=$this->getMenuString($eleName,$useTheLevel,&$base);
					break;
				case 'url':
					$jobDetailString.=" url: $eleUrl, event: $eleEvents, class: $eleClass, id: $eleId ";
				default:
			}
		}
		$jobDetailString.="<br>";
		return $jobDetailString;
	}
//==================================================
	function getTableString($tableName,$theLevel,&$base){
		$levelSpaceUnit="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$theLevelSpace=null;
		for ($dmy=0;$dmy<$theLevel;$dmy++){$theLevelSpace.=$levelSpaceUnit;}
		//$jobDetailString="<br>$theLevelSpace--- container $containerName ---";
		$tableAry=$this->workAreaAry['tableary'][$tableName];
		$noEle=count($tableAry);
		for ($lp=1;$lp<=$noEle;$lp++){
			$eleName=$tableAry[$lp]['columnname'];
			$eleType=$tableAry[$lp]['columntype'];
			$eleUrl=$tableAry[$lp]['joblink'];
			$eleEvents_raw=$tableAry[$lp]['columnevents'];
			$eleEvents=str_replace('%sglqt%',"'",$eleEvents_raw);
			$eleEvents=str_replace('%dblqt%','"',$eleEvents);
			$jobDetailString.="<br>$theLevelSpace$lp) $eleName($eleType)";
			$useTheLevel=$theLevel+1;
			switch ($eleType){
				case 'container':
					$jobDetailString.=$this->getContainerString($eleName,$useTheLevel,&$base);
					$this->workAreaAry['containersdisplayedary'][$eleName]='yo';
					break;
				case 'menu':
					$menuType=$this->workAreaAry['menusinfoary'][$eleName]['menutype'];
					$menuClass=$this->workAreaAry['menusinfoary'][$eleName]['menuclass'];
					$menuId=$this->workAreaAry['menusinfoary'][$eleName]['menuid'];
					$jobDetailString.="($menuType) class: $menuClass, id: $menuId ";
					$jobDetailString.=$this->getMenuString($eleName,$useTheLevel,&$base);
					break;
				case 'table':
					$jobDetailString.=$this->getTableString($eleName,$useTheLevel,&$base);
					break;
				case 'url':
					$jobDetailString.=" url: $eleUrl, event: $eleEvents";
					//$base->DebugObj->printDebug($tableAry[$lp],1,'xxxf');
					break;
				default:
			}
		}
		$jobDetailString.="<br>";
		return $jobDetailString;
	}
//==================================================
	function initContainers($base){
		$jobProfileId=$base->paramsAry['jobprofileid'];
		if ($jobProfileId == null){
			echo "operationplugin001object: initcontainers, jobprofileid is null";
			exit();
		}
		$writeRowsAry=array();
		$query="select * from containerprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'containername');
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$doUpdate=false;
		$addHeadingStuff=false;
		$statusStrg='';
//- containerprofile
		if (array_key_exists('headingcontainer',$workAry)){
			$statusStrg.='<br>Heading Container already exists!';
		}
		else {
			$doUpdate=true;
			$addHeadingStuff=true;
			$headingContainerAry=array();
			$headingContainerAry['containername']='headingcontainer';
			$headingContainerAry['jobprofileid']=$jobProfileId;
			$headingContainerAry['containershow']=false;
			$writeRowsAry[]=$headingContainerAry;
			$statusStrg.='<br>Heading container written';
		}
		if (array_key_exists('bodycontainer',$workAry)){
			$statusStrg.='<br>Body Container already exists!';
		}
		else {
			$doUpdate=true;
			$bodyContainerAry=array();
			$bodyContainerAry['containername']='bodycontainer';
			$bodyContainerAry['jobprofileid']=$jobProfileId;
			$bodyContainerAry['containershow']=false;
			$writeRowsAry[]=$bodyContainerAry;
			$statusStrg.='<br>Body container written';			
		}
		if ($doUpdate){
			$dbControlsAry=array('dbtablename'=>'containerprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
			$base->DbObj->writeToDb($dbControlsAry,&$base);
		}
//- containerelementprofile
		if ($addHeadingStuff){
			$query="select * from containerprofileview where containername='headingcontainer' and jobprofileid=$jobProfileId";
			$passAry=array('delimit1'=>'containername');
			$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$containerProfileId=$workAry['headingcontainer']['containerprofileid'];
			if ($containerProfileId == null){
				echo "operationPlugin001Obj.initContainers containerprofileid: $containerProfileId -should have been there!";
				exit();
			}
			else {
				$writeRowsAry=array();
				$headingElementAry=array();
				$headingElementAry['containerelementname']='thetitle';
				$headingElementAry['containerprofileid']=$containerProfileId;
				$headingElementAry['containerelementtype']='title';
				$headingElementAry['containerelementno']=1;
				$writeRowsAry[]=$headingElementAry;
				$statusStrg.="<br>thetitle(title) heading element written";
//-			
				$headingElementAry['containerelementname']='thestyle';
				$headingElementAry['containerprofileid']=$containerProfileId;
				$headingElementAry['containerelementtype']='style';
				$headingElementAry['containerelementno']=2;			
				$writeRowsAry[]=$headingElementAry;
				$dbControlsAry=array('dbtablename'=>'containerelementprofile');
				$dbControlsAry['writerowsary']=$writeRowsAry;
				$statusStrg.="<br>thestyle(style) heading element written";
				//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
				$base->DbObj->writeToDb($dbControlsAry,&$base);
			}
		}
		$base->ErrorObj->saveError('containerinit',$statusStrg,&$base);
	}
//==================================================
	function maintTables($base){
		$maintCode=$base->paramsAry['maintcode'];
		$dbTableName=$base->paramsAry['dbtablename'];
		$dbColumnName=$base->paramsAry['dbcolumnname'];
		session_start();
		$theAry_xml=$_SESSION['sessionobj']->getSessionValue('comparedbtablesxml');
		$theAry=$base->XmlObj->xml2Ary($theAry_xml,&$base);
		//$_SESSION['sessionobj']->saveSessionValue('comparedbtablesxml','');
		switch ($maintCode){
//- whole table is missing
			case 'missingtable':
//- see if table record missing or not - maybe just all of its column defs
				$query="select dbtableprofileid from dbtableprofileview where dbtablename='$dbTableName'";
				$passAry=array();
				$wrkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$cnt=count($wrkAry);
				//echo "query: $query, cnt: $cnt<br>";//xxxd
				if ($cnt<1){
//- get dbtable record
				$dbControlsAry=array('dbtablename'=>'dbtableprofile');
				$dbTableAry=$theAry['dbtables'][$dbTableName];
				//$base->DebugObj->printDebug($theAry,1,'xxxdf');//xxxd
				$tableRowAry=array();
				//xxxf22 - is it really an array below this for columnAry?????
				foreach ($dbTableAry as $columnName=>$columnAry){
					$columnValue=$columnAry[$columnName];
					$tableRowAry[$columnName]=$columnValue;
				}
				unset($tableRowAry['dbtableprofileid']);
				$companyName=$tableRowAry['companyname'];
				if ($companyName != null){
					$query="select companyprofileid from companyprofileview where companyname='$companyName'";
					$passAry=array();
					$wrkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
					$companyProfileId=$wrkAry[0]['companyprofileid'];
				}
				if ($companyProfileId == null){
					$query="select companyprofileid from companyprofileview";
					$passAry=array();
					$wrkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
					$companyProfileId=$wrkAry[0]['companyprofileid'];
				}
				$tableRowAry['companyprofileid']=$companyProfileId;
				$writeRowsAry=array();
				$writeRowsAry[]=$tableRowAry;
				$dbControlsAry['writerowsary']=$writeRowsAry;
				//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
				$base->DbObj->writeToDb($dbControlsAry,&$base);
				}
				else {
					echo "$dbTableName has dbtableprofile record so missing all columns, I guess!<br>";
				}
//!!!! --- want to fall through from missingtable to missingcolumns
			case 'missingcolumns':
//- columns of table are missing
				//echo "tablemaint: will fix missing columns for table: $dbTableName, $dbColumnName<br>";//xxxd
//- get meta info about dbcolumnsary
				$dbControlsAry=array('dbtablename'=>'dbcolumnprofile');
				//$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);				
//- get tableprofileid
				$query="select dbtableprofileid from dbtableprofile where dbtablename='$dbTableName'";
				$passAry=array();
				$wrkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$dbTableProfileId=$wrkAry[0]['dbtableprofileid'];
//- create records to write across
				if ($dbTableProfileId != null){
					//- get defs for dbcolumnprofile
					$dbTableAry=$theAry['dbcolumns'][$dbTableName];
					$writeRowsAry=array();
					foreach ($dbTableAry as $dbColumnName=>$dbColumnAry){
//- get validateprofileid
						$validateName=$dbColumnAry['validatename'];
						if ($validateName != null){
							$query="select validateprofileid from validateprofile where validatename='$validateName'";
							$passAry=array();
							$wrkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
							$validateProfileId=$wrkAry[0]['validateprofileid'];
							if ($validateProfileId != null){
//- build the array xxxd -dont have to do it now just the dbtable has it screwed up
//								$columnRowAry=array();
//								foreach ($dbColumnAry as $theName=>$theValue){
//									$columnRowAry[$theName]=$theValue;
//								}
//- make final changes
								unset($dbColumnAry['dbcolumnprofileid']);
								$dbColumnAry['dbtableprofileid']=$dbTableProfileId;
								$dbColumnAry['validateprofileid']=$validateProfileId;
								$writeRowsAry[]=$dbColumnAry;
							}
							else {echo "validateprofileid is null<br>";}
						}
						else {echo "validatename is null<br>";}
					}
//- setup and write column to new table
					$dbControlsAry['writerowsary']=$writeRowsAry;
					//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
					//exit();//xxxd
					$base->DbObj->writeToDb($dbControlsAry,&$base);
//- update the table in the actual database
					$base->paramsAry['dbtableprofileid']=$dbTableProfileId;
					$operationAry=array();
					$operationAry['operationname']='runoperation';
					$operationAry['pluginname']='updatedbtable';
					$base->PluginObj->runOperationPlugin($operationAry,&$base);
//- rebuild the view of the table in the actual database
					$operationAry['pluginname']='rebuildview';
					$base->PluginObj->runOperationPlugin($operationAry,&$base);
				}
				//$base->DebugObj->printDebug($dbColumnAry,1,'dbcolumnary: '.$dbColumnName);//xxxd
				break;
			case 'mismatchedcolumn':
//- table columns have fields that differ
				echo "there is a fild mismatch on table $dbTableName, column $dbColumnName<br>";
				break;
		}		
	}
//==================================================
	function plugIdsClasses($base){
		$job=$base->paramsAry['job'];
		$formProfileId=$base->paramsAry['formprofileid'];
		$query="select * from formelementprofileview where formprofileid=$formProfileId";
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$dbControlsAry=array('dbtablename'=>'formelementprofile');
		$writeRowsAry=array();
		foreach ($workAry as $ctr=>$elementAry){
			$formElementName=$elementAry['formelementname'];
			$formElementId=$formElementName.'id';
			$elementAry['formelementid']=$formElementId;
			$formElementClass=$formElementName;
			$elementAry['formelementclass']=$formElementClass;
			$writeRowsAry[$ctr]=$elementAry;
		}
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//===================================================
	function buildImageStats($base){
//- get input
//echo "xxxf0";exit();
		$base->FileObj->initLog('buildimagestats.log',&$base);
		$startTime=time();
		$base->FileObj->writeLog('buildimagestats.log','start image stats build',&$base);
		$newTimeOut=300;
		ini_set('max_execution_time', $newTimeOut);
		$sendData=$base->paramsAry['senddata'];
		$sendDataAry=explode('`',$sendData);
		$getStuffAry=array();
		foreach ($sendDataAry as $ctr=>$theValue){
			$theValueAry=explode('|',$theValue);
			$aName=$theValueAry[0];
			$aValue=$theValueAry[1];
			$getStuffAry[$aName]=$aValue;
		}
//- see if just one directory or a whole client
		//$base->DebugObj->printDebug($getStuffAry,1,'xxxf');exit();//xxxf
		if (array_key_exists('jeffclientdirectory',$getStuffAry)){
			$oneDirectory=true;
			$jeffClientDirectory=$getStuffAry['jeffclientdirectory'];
		}
		else {$oneDirectory=false;}
//- get client info
		$jeffClientProfileId=$getStuffAry['jeffclientprofileid'];
		$query="select * from jeffclientprofile where jeffclientprofileid=$jeffClientProfileId";
		$passAry=array();
		$clientAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- setup paths
		$clientKeyName=$clientAry[0]['jeffclientkeyname'];
		$systemAry=$base->ClientObj->getSystemData(&$base);
		$baseLocal=$systemAry['baselocal'];
		$baseImage="$baseLocal/images/$clientKeyName";
		$rawBaseImage="$baseLocal/rawimages/$clientKeyName";
//
//I. ========== remove old infor from jeffclientimagedetailprofile(images) xxxd: below is in error ==========
//
		//$query="delete from jeffclientimagedetailprofile where jeffclientimageprofileid in(select jeffclientimageprofileid $jeffClientProfileId";
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: I. ============ remove old info from jeffclientimagedetailprofile(images) ============",&$base);
		//xxxf22 - this needs to have: and with jeffclientdirectory = 'xxx/xxx/xxx'
		$innerQuery="select jeffclientdirectoryprofileid from jeffclientdirectoryprofile where jeffclientprofileid=$jeffClientProfileId";
		if ($oneDirectory){$innerQuery.=" and jeffclientdirectory = '$jeffClientDirectory'";}
		$query="delete from jeffclientimageprofile where jeffclientdirectoryprofileid in ($innerQuery)";
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
		//echo "query: $query\n";//xxxxd
		$result=$base->DbObj->queryTable($query,'delete',&$base);
//
//II. ========== remove old info from jeffclientdirectoryprofile ==========
//
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: II. ========== remove old info from jeffclientimageprofile ==========",&$base);
		//xxxf22 - this needs to have : and wih jeffclientdirectory = 'xxx/xxx/xxx'
		$query="delete from jeffclientdirectoryprofile where jeffclientprofileid=$jeffClientProfileId";
		if ($oneDirectory){$query.=" and jeffclientdirectory='$jeffClientDirectory'";}
		$result=$base->DbObj->queryTable($query,'delete',&$base);
		//echo "query: $query, xxxf0 early stop";exit();//xxxf
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
		//exit();//xxxd
//
// III. ========== update jeffclientimageprofile with directories and their totals ==========
//
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: III. ========== update jeffclientimageprofile with directories and their totals ==========",&$base);
//III. a. --- rawimages/<client> directory totals
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: III. a. --- raw directories",&$base);		
		$bashCommand="/home/jeff/bin/getimagedirectorytotals.bsh $rawBaseImage";
		if ($oneDirectory){$bashCommand.="/$jeffClientDirectory";}
		$passAry['bashcommand']=$bashCommand;
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: run bash command: getimagedata.bsh",&$base);
		$passAry=$base->FileObj->runBashCommand($passAry,&$base);
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
		$rawRowsAry=array();
		$outputAry=$passAry['outputary'];
		$theLen=count($outputAry);
		//print_r($outputAry);//xxxf
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: loop through each output line from bash string",&$base);
		foreach ($outputAry as $ctr=>$valueString){
			$valueAry=explode(',',$valueString);
			$thePath_raw=trim($valueAry[0]);
			$thePath=str_replace($baseLocal,'',$thePath_raw);
			$thePath=str_replace('rawimages','images',$thePath);
			//$thePath=str_replace($clientKeyName,'',$thePath);
			if ($thePath != null){
				$theCnt=trim($valueAry[1]);
				$theSize=trim($valueAry[2]);
				if ($theCnt>0){$theAvgSize=$theSize/$theCnt;}
				else {$theAvgSize=0;}
				$theAvgSize=round($theAvgSize);
				$aRow=array();
				//$aRow['jeffclientdirectory']=$thePath;
				$aRow['jeffclientrawdirectorycnt']=$theCnt;
				$aRow['jeffclientrawdirectorysize']=$theSize;
				$aRow['jeffclientrawavgsize']=$theAvgSize;
				//$aRow['jeffclientprofileid']=$jeffClientProfileId;
				$rawRowsAry[$thePath]=$aRow;
			}
		}
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
// III. b. --- images/<client> directory totals
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: III. b. --- main directories",&$base);
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: run bash command getimagedata.bsh",&$base);
		$bashCommand="/home/jeff/bin/getimagedirectorytotals.bsh $baseImage";
		$firstChar=substr($jeffClientDirectory,0,1);
		if ($firstChar != '/'){$jeffClientDirectory='/'.$jeffClientDirectory;}
		if ($oneDirectory){$bashCommand.="/$jeffClientDirectory";}
		$passAry['bashcommand']=$bashCommand;
		$passAry=$base->FileObj->runBashCommand($passAry,&$base);
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
		$writeRowsAry=array();
		$totalImageCnt=0;
		$totalImageSize=0;
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: loop through its output",&$base);
		$outputAry=$passAry['outputary'];
		foreach ($outputAry as $ctr=>$valueString){
			$valueAry=explode(',',$valueString);
			$thePath_raw=trim($valueAry[0]);
			$thePath=str_replace($baseLocal,'',$thePath_raw);
			if ($thePath != null){
				$theCnt=trim($valueAry[1]);
				$theSize=trim($valueAry[2]);
				if ($theCnt>0){$theAvgSize=$theSize/$theCnt;}
				else {$theAvgSize=0;}
				$theAvgSize=round($theAvgSize);
				$aRow=array();
				$usePath=str_replace('/images','',$thePath);
				$usePath=str_replace('/'.$clientKeyName,'',$usePath);
				$usePath=str_replace('//','/',$usePath);
				$aRow['jeffclientdirectory']=$usePath;
				$aRow['jeffclientdirectorycnt']=$theCnt;
				$totalImageCnt+=$theCnt;
				$aRow['jeffclientdirectorysize']=$theSize;
				$totalImageSize+=$theSize;
				$aRow['jeffclientprofileid']=$jeffClientProfileId;
				$aRow['jeffclientavgsize']=$theAvgSize;
				$rawRowAry=$rawRowsAry[$thePath];
				if (is_array($rawRowAry)){
					$rawDirectorySize=$rawRowAry['jeffclientrawdirectorysize'];
					$rawDirectoryCnt=$rawRowAry['jeffclientrawdirectorycnt'];
					$rawAvgSize=$rawRowAry['jeffclientrawavgsize'];
					$difDirectorySize=$rawDirectorySize-$theSize;
					$aRow['jeffclientrawdirectorysize']=$rawDirectorySize;
					$aRow['jeffclientrawdirectorycnt']=$rawDirectoryCnt;
					$aRow['jeffclientrawavgsize']=$rawAvgSize;
					$aRow['jeffclientdirectorydifsize']=$difDirectorySize;
					//echo "diff: $difDirectorySize\n";//xxxd
				}
				$writeRowsAry[]=$aRow;
			}
		}
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
		$dbControlsAry=array('dbtablename'=>'jeffclientdirectoryprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
//
// IV. ========== update jeffclientprofile with directory totals ==========
//
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: IV. ========== update jeffclientprofile with directory totals ==========",&$base);
		$clientAry[0]['jeffclientimagecnt']=$totalImageCnt;
		$clientAry[0]['jeffclientimagesize']=$totalImageSize;
		$dbControlsAry=array('dbtablename'=>'jeffclientprofile');
		$dbControlsAry['writerowsary']=$clientAry;
		//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: write all data to db",&$base);
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done",&$base);
//
//V. ========== update jeffclientimageprofile with individual image stats ==========
//
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: V. ========== update jeffclientimagedetailprofile with individual image stats ==========",&$base);
		$ctr=0;
		$query="select * from jeffclientdirectoryprofileview where jeffclientprofileid=$jeffClientProfileId";//xxxf22
		if ($oneDirectory){$query.=" and jeffclientdirectory='$jeffClientDirectory'";}
		$passAry=array();
		$directoryAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
//- setup column definitions
		$defsAry=array('jeffclientpermissions','jeffclientfileowner','jeffclientfilegroup','jeffclientfilesize',
			'jeffclientfiledate','jeffclientfiletime','jeffclientfilename');
		//$base->DebugObj->printDebug($directoryAry,1,'xxxd');exit();
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: loop through directory array",&$base);
		$fileCnt=0;
		$dirCnt=0;
		$imageCnt=0;
		//$base->DebugObj->printDebug($directoryAry,1,'xxxf');exit();
//- loop through each directory
		foreach ($directoryAry as $ctr=>$theDirAry){
			$dirCnt++;
			$writeRowsAry=array();
			$theDir=$theDirAry['jeffclientdirectory'];
			$thisTime=time();
			$diffTime=$thisTime-$startTime;
			//$base->FileObj->writeLog('buildimagestats.log',"$diffTime: ... run getimagedetaildata.bsh on: $rawBaseImage/$theDir",&$base);
			$jeffClientDirectoryProfileId=$theDirAry['jeffclientdirectoryprofileid'];
//- get raw image file stats
			$rawFullPath=$rawBaseImage.'/'.$theDir;
			$bashCommand="/home/jeff/bin/getimagedetaildata.pl $rawFullPath";
			$base->FileObj->writeLog('buildimagestats.log',"run $bashCommand",&$base);
			$passAry['bashcommand']=$bashCommand;
			$passAry=$base->FileObj->runBashCommand($passAry,&$base);
			//echo "bashcommand: $bashCommand\n";//xxxf
			//$base->DebugObj->printDebug($passAry,1,'xxxf');exit();
//- get destination file stats and reformat for easy access
			$destFullPath=$baseImage.'/'.$theDir;
			$bashCommand="/home/jeff/bin/getimagedetaildata.pl $destFullPath";
			//echo " bashcommand: $bashCommand<br>";//xxxf
			$base->FileObj->writeLog('buildimagestats.log',"run $bashCommand",&$base);
			$destPassAry=array('bashcommand'=>$bashCommand);
			$destPassAry=$base->FileObj->runBashCommand($destPassAry,&$base);
			$destFileAry=array();
			foreach ($destPassAry['outputary'] as $dmyctr=>$fileStrg){
				$fileAry=explode(',',$fileStrg);
				$fileName=$fileAry[6];
				$destFileAry[$fileName]=$fileStrg;
			}
			//print_r($passAry);//xxxf
//- loop through raw image file stats
			$thisTime=time();
			$diffTime=$thisTime-$startTime;
			$tstcnt=count($passAry['outputary']);
			//echo "tstcnt: $tstcnt\n";//xxxf
			if ($tstcnt>0){
				$ctr2=0;
				$wrkCnt=0;
				$workAry=$passAry['outputary'];
				foreach ($workAry as $ctr=>$fileString){
					$wrkCnt++;
					$base->FileObj->writeLog('buildimagestats.log',"$diffTime: $wrkCnt) $fileString",&$base);
					$fileString=str_replace("%sp%"," ",$fileString);
					$fileString=trim($fileString);
					$fileStringAry=explode(',',$fileString);
					$noElements=count($fileStringAry);
//- put image file stats into an array
					$rowAry=array();
					for ($lp=0;$lp<$noElements;$lp++){
						$valueName=$defsAry[$lp];
						if ($valueName != null){
							$rowAry[$valueName]=$fileStringAry[$lp];
						}
					}
//- get row width, height using imagicks
					$fileName=$rowAry['jeffclientfilename'];
					if ($fileName != null){
						$rawFilePath=$rawFullPath.'/'.$fileName;
						//echo "    filename: $fileName\n";//xxxf
						$fileNameAry=explode('.',$fileName);
						$fileSuffix=$fileNameAry[1];
						$fileSuffix=strtolower($fileSuffix);
						if ($fileSuffix == 'png' || $fileSuffix == 'bmp' || $fileSuffix == 'jpg' || $fileSuffix == 'tiff'){
							$thisTime=time();
							$diffTime=$thisTime-$startTime;
							$base->FileObj->writeLog('buildimagestats.log',"$diffTime: ... do image stats on $fileName",&$base);
							$imageCnt++;
							$success=$base->UtlObj->openImageBuffer(0, &$base);
							$success=$base->UtlObj->readImage(0, $rawFilePath, &$base);// filepath should be to images here
							$imageStatsAry=$base->UtlObj->getImageStats(0,&$base);
							$imageWidth=$imageStatsAry['imagewidth'];
							$imageHeight=$imageStatsAry['imageheight'];
							//echo "filepath: $rawFilePath, imagewidth: $imageWidth, imageheight: $imageHeight\n";exit();//xxxf
						}
						else {
							$thisTime=time();
							$diffTime=$thisTime-$startTime;
							$base->FileObj->writeLog('buildimagestats.log',"$diffTime: ... not an image: $fileName",&$base);
							$fileCnt++;
							$imageWidth=0;
							$imageHeight=0;
						}
//- fix the date of the file 
						$wrkDate=$rowAry['jeffclientfiledate'];
						$wrkDateAry=explode('-',$wrkDate);
						$newDate=$wrkDateAry[1].'/'.$wrkDateAry[2].'/'.$wrkDateAry[0];
						$rowAry['jeffclientfiledate']=$newDate;
//- put in special fields
//xxxf - just changed below from jeffclientimageprofileid -> jeffclientdirectoryprofileid
						//echo "jeffclientdirectoryprofileid: $jeffClientDirectoryProfileId\n";//xxxf
						$rowAry['jeffclientdirectoryprofileid']=$jeffClientDirectoryProfileId;
						$rowAry['jeffclientimagewidth']=$imageWidth;
						$rowAry['jeffclientimageheight']=$imageHeight;
//- get file stats of destination image
						$destFilePath=$destFullPath.'/'.$fileName;
						$fileString=$destFileAry[$fileName];
						$fileString=str_replace("%sp%"," ",$fileString);
						$fileString=trim($fileString);
						$fileStringAry=explode(',',$fileString);
						$noElements=count($fileStringAry);
					//echo "$fileString\n";//xxxd
//- get all info fed to us about destination image
						$destRowAry=array();
						for ($lp=0;$lp<$noElements;$lp++){
							//if ($lp>10){echo 'xxxf4';exit();}							
							$valueName=$defsAry[$lp];
							if ($valueName != null){
								$destRowAry[$valueName]=$fileStringAry[$lp];
							}
						}
						$newFileSize=$destRowAry['jeffclientfilesize'];
						$rowAry['jeffclientfilesizenew']=$destRowAry['jeffclientfilesize'];
						//echo "newsize: ".$destRowAry['jeffclientfilesize']."\n";//xxxd
//- fix the date for input
						$wrkDate=$destRowAry['jeffclientfiledate'];
						if ($wrkDate != null){
							$wrkDateAry=explode('-',$wrkDate);
							$newDate=$wrkDateAry[1].'/'.$wrkDateAry[2].'/'.$wrkDateAry[0];
							$rowAry['jeffclientfiledatenew']=$newDate;
						}
						$rowAry['jeffclientfiletimenew']=$destRowAry['jeffclientfiletime'];
//- get height, width of destination image
						$fileNameAry=explode('.',$fileName);
						$fileSuffix=$fileNameAry[1];
						$fileSuffix=strtolower($fileSuffix);
						if ($fileSuffix == 'png' || $fileSuffix == 'bmp' || $fileSuffix == 'jpg' || $fileSuffix == 'tiff'){
							$thisTime=time();
							$diffTime=$thisTime-$startTime;
							$base->FileObj->writeLog('buildimagestats.log',"$diffTime: ... do image stats on $fileName",&$base);
							$success=$base->UtlObj->openImageBuffer(0, &$base);
							$success=$base->UtlObj->readImage(0, $destFilePath, &$base);
							$imageStatsAry=$base->UtlObj->getImageStats(0,&$base);
							$imageWidth=$imageStatsAry['imagewidth'];
							$imageHeight=$imageStatsAry['imageheight'];
							$rowAry['jeffclientimagewidthnew']=$imageWidth;
							$rowAry['jeffclientimageheightnew']=$imageHeight;
						}
//- add rowAry with all of the data to writerowsary
						$writeRowsAry[]=$rowAry;
					}
				}
				$dbControlsAry=array('dbtablename'=>'jeffclientimageprofile');
				$dbControlsAry['writerowsary']=$writeRowsAry;
				//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();//xxxf
				$success=$base->DbObj->writeToDb($dbControlsAry,&$base);
				//$base->ErrorObj->printAllErrors(&$base);//xxxf
			}
		}
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('buildimagestats.log',"$diffTime: done looping through all directories and exitting",&$base);
		ini_set('max_execution_time', 60);
		echo "okmsg| directories: $dirCnt, images: $imageCnt, other files: $fileCnt";
	}
//==================================================
	function buildCssDisplay($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd');
		$cssPrefix=$base->paramsAry['prefix'];
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$errorMsgName=$base->paramsAry['errormsgname'];
		$space="&nbsp;";
		$space5=$space.$space.$space.$space.$space;
		$space10=$space5.$space5;
		switch ($cssPrefix){
			case 'all':
				$query="select * from csselementprofileview where jobprofileid=$jobProfileId order by prefix, cssclass, cssid, htmltag, csselementproperty";
				break;
			case '':
				break;
			default:
				$query="select * from csselementprofileview where jobprofileid=$jobProfileId and prefix='$cssPrefix' order by cssclass, cssid, htmltag, csselementproperty";
		}
		$passAry=array();
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		//echo "query: $query<br>";//xxxd
		//$base->DebugObj->printDebug($workAry,1,'xxxd');
		$displayStrg="==========$cssPrefix==========<br><br>";
		$displayStrg.="<b>prefix, class, id, html</b><br>";
		$oldCssPrefix=null;
		$oldCssClass=null;
		$oldCssId=null;
		$oldHtmlTag=null;
		foreach ($workAry as $ctr=>$cssAry){
			$cssPrefix=$cssAry['prefix'];
			if ($cssPrefix == null){$cssPrefix='none';}
			$cssElementProperty=$cssAry['csselementproperty'];
			$cssElementValue=$cssAry['csselementvalue'];
			$cssClass=$cssAry['cssclass'];
			$cssId=$cssAry['cssid'];
			$htmlTag=$cssAry['htmltag'];
			//if ($cssClass=='maindisplayimageheaderleft'){echo "xxxxxd";exit();}
			if ($cssId != $oldCssId || $cssPrefix != $oldCssPrefix || $cssClass != $oldCssClass || $htmlTag != $oldHtmlTag){
				$oldCssId=$cssId;
				$oldCssPrefix=$cssPrefix;
				$oldCssClass=$cssClass;
				$oldHtmlTag=$htmlTag;
				$newLine=sprintf("<br>%s, %s, %s, %s<br><br>",$cssPrefix,$cssClass,$cssId,$htmlTag);
				$displayStrg.=$newLine;
			}
			$newLine=sprintf("$space5%s: %s<br>",$cssElementProperty, $cssElementValue);
			$displayStrg.=$newLine;
			//echo "........................$cssElementProperty, $cssElementValue<br>";
		}
		//$pos=strpos($displayStrg,'maindisplayimageheaderleft',0);
		//$theLen=strlen($displayStrg);
		//echo "xxxd pos: $pos, len: $theLen<br>";//xxxd
		$base->ErrorObj->saveError($errorMsgName,$displayStrg,&$base);
		//$tst=$base->ErrorObj->retrieveError($errorMsgName,&$base);
		//echo $tst;
	}
	//==================================================
	function buildCssDisplayV2($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd');
		$cssPrefix=$base->paramsAry['cssprefix'];
		$checkCssPrefix=$cssPrefix;
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$reportLoadId=$base->paramsAry['reportloadid'];
		$base->UtlObj->breakOutSendData(&$base);
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');exit();
		$space="&nbsp;";
		$space5=$space.$space.$space.$space.$space;
		$space10=$space5.$space5;
//- properties
		switch ($checkCssPrefix){
			case 'all':
				$query="select * from csselementprofileview where jobprofileid=$jobProfileId order by prefix, cssclass, cssid, htmltag, csselementproperty";
				break;
			case '':
				break;
			default:
				$query="select * from csselementprofileview where jobprofileid=$jobProfileId and prefix='$cssPrefix' order by cssclass, cssid, htmltag, csselementproperty";
		}
		$passAry=array();
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		//echo "query: $query<br>";//xxxd
		//$base->DebugObj->printDebug($workAry,1,'xxxd');
		$displayStrg="==========$cssPrefix==========<br><br>";
		$displayStrg.="<table style=\"text-align:left\"><tr><th>prefix</th><th>class</th><th>id</th><th>html</th><th>property</th><th>value</th></tr>";
		$oldCssPrefix=null;
		$oldCssClass=null;
		$oldCssId=null;
		$oldHtmlTag=null;
		foreach ($workAry as $ctr=>$cssAry){
			$cssPrefix=$cssAry['prefix'];
			if ($cssPrefix == null){
				$cssPrefix='none';
			}
			$cssElementProperty=$cssAry['csselementproperty'];
			$cssElementValue=$cssAry['csselementvalue'];
			$cssClass=$cssAry['cssclass'];
			$cssId=$cssAry['cssid'];
			$htmlTag=$cssAry['htmltag'];
			//if ($cssClass=='maindisplayimageheaderleft'){echo "xxxxxd";exit();}
			if ($cssId != $oldCssId || $cssPrefix != $oldCssPrefix || $cssClass != $oldCssClass || $htmlTag != $oldHtmlTag){
				$oldCssId=$cssId;
				$oldCssPrefix=$cssPrefix;
				$oldCssClass=$cssClass;
				$oldHtmlTag=$htmlTag;
				$newLine="<tr><td>$cssPrefix</td><td>$cssClass</td><td>$cssId</td><td>$htmlTag</td>";
			}
			else {
				$newLine="<tr><td>$space</td><td>$space</td><td>$space</td><td>$space</td>";
			}
			$newLine.="<td>$cssElementProperty</td><td>$cssElementValue</td></tr>";
			$displayStrg.=$newLine;
			//echo "........................$cssElementProperty, $cssElementValue<br>";
		}
		$displayStrg.="</table><br><br>";
//- events
		switch ($checkCssPrefix){
			case 'all':
				$query="select * from csseventprofileview where jobprofileid=$jobProfileId order by prefix, cssclass, cssid, htmltag, csseventtype";
				break;
			case '':
				break;
			default:
				$query="select * from csseventprofileview where jobprofileid=$jobProfileId and prefix='$cssPrefix' order by cssclass, cssid, htmltag, csseventtype";
		}
		//echo "query: $query";exit();//xxxf
		$passAry=array();
		$workAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		//$base->DebugObj->printDebug($workAry,1,'xxxd');
		$displayStrg.="<table style=\"text-align:left\"><tr><th>prefix</th><th>class</th><th>id</th><th>html</th><th>event</th><th>action</th></tr>";
		$oldCssPrefix=null;
		$oldCssClass=null;
		$oldCssId=null;
		$oldHtmlTag=null;
		foreach ($workAry as $ctr=>$cssAry){
			$cssPrefix=$cssAry['prefix'];
			if ($cssPrefix == null){
				$cssPrefix='none';
			}
			$cssElementProperty=$cssAry['csseventtype'];
			$cssElementValue=$cssAry['csseventprogram'];
			$cssClass=$cssAry['cssclass'];
			$cssId=$cssAry['cssid'];
			$htmlTag=$cssAry['htmltag'];
			//if ($cssClass=='maindisplayimageheaderleft'){echo "xxxxxd";exit();}
			if ($cssId != $oldCssId || $cssPrefix != $oldCssPrefix || $cssClass != $oldCssClass || $htmlTag != $oldHtmlTag){
				$oldCssId=$cssId;
				$oldCssPrefix=$cssPrefix;
				$oldCssClass=$cssClass;
				$oldHtmlTag=$htmlTag;
				$newLine="<tr><td>$cssPrefix</td><td>$cssClass</td><td>$cssId</td><td>$htmlTag</td>";
			}
			else {
				$newLine="<tr><td>$space</td><td>$space</td><td>$space</td><td>$space</td>";
			}
			$newLine.="<td>$cssElementProperty</td><td>$cssElementValue</td></tr>";
			$displayStrg.=$newLine;
			//echo "........................$cssElementProperty, $cssElementValue<br>";
		}
		$displayStrg.="</table><br><br>";
//- end
		echo "okupd|$reportLoadId|$displayStrg";
		//$tst=$base->ErrorObj->retrieveError($errorMsgName,&$base);
		//echo $tst;
	}
//==================================================
	function buildNewContainer($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');//xxxf
		$containerName=$base->paramsAry['containername'];
		$typeOfContainer=$base->paramsAry['typeofcontainer'];
		$jobProfileId=$base->paramsAry['jobprofileid'];
		if ($containerName != null && $typeOfContainer != null){
			$writeRowAry=array();
			$theRow=array();
			$theRow['containername']=$containerName;
			$theRow['jobprofileid']=$jobProfileId;
			$doit=true;
			switch ($typeOfContainer){
				case 'transfercontainer':
					$theRow['containershow']=false;
					break;
				case 'holdingcontainer':
					$theRow['containerid']=$containerName.'id';
					$theRow['containerclass']=$containerName;
					$theRow['containerheaderid']=$containerName.'headerid';
					$theRow['containerheaderclass']=$containerName.'header';
					$theRow['containercontentid']=$containerName.'contentid';
					$theRow['containercontentclass']=$containerName.'content';
					$theRow['containerfooterclass']=$containerName.'footer';
					$theRow['containerfooterid']=$containerName.'footerid';
					$theRow['containershow']=true;
					break;
				default:
					$doit=false;
			}
			if ($doit){
				$dbControlsAry=array();
				$dbControlsAry['dbtablename']='containerprofile';
				$dbControlsAry['writerowsary']=array($theRow);
				$base->DbObj->writeToDb($dbControlsAry,&$base);
			}
		}
	}
//==================================================
	function buildHealthActionTable($workAry,&$base){
		//echo 'xxxf: in buildhealthactiontable';
//- get current date and time
		$passAry=array('thedate'=>'today');
		$nowTimeAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$nowDay=$nowTimeAry['mday'];
		$nowYear=$nowTimeAry['year'];
		$nowMonth=$nowTimeAry['mon'];
		$nowHour=$nowTimeAry['hours'];
		$nowMinute=$nowTimeAry['minutes'];
		$nowDate=$nowTimeAry['date_v1'];
		//echo "thedate: $nowDate\n";
//- get action profile file
		$query="select * from healthactionview where actionyear=$nowYear and actionmonth=$nowMonth and actionDay=$nowDay and actionperformed=false";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'healtheventprofileid','delimit2'=>'actionhour','delimit3'=>'actionminute');
		$actionAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
/*
		foreach ($actionAry as $one=>$two){
			echo "$one: $two\n";
			foreach ($two as $three=>$four){
				echo "... $three: $four\n";
				foreach ($four as $five=>$six){
					echo "...... $five: $six\n";
					foreach ($six as $seven=>$eight){
						echo "......... $seven: $eight\n";
					}
				}
			}
		}
		//exit();//xxxf
*/
//- get scheduleprofile file
		$query="select * from scheduleeventprofileview";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'scheduletype','delimit2'=>'schedulehour','delimit3'=>'scheduleminute');
		$scheduleAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$nowHour=19;
		$nowMinute=32;
		foreach ($scheduleAry as $scheduleType=>$array1){
		foreach ($array1 as $scheduleHour=>$array2){
		foreach ($array2 as $scheduleMinute=>$theAry){
			$healthEventProfileId=$theAry['healtheventprofileid'];
			$lastScheduleDate=$theAry['lastscheduledate'];
			$lastScheduleDate='2011-02-08';//xxxf
			$healthEventTitle=$theAry['healtheventtitle'];
			$healthType=$theAry['healthtype'];
			$healthUrgency=$theAry['healthurgency'];
			$healthNotes=$theAry['healthnotes'];
//- check if do it today
			$okToContinue=false;
			switch ($scheduleType){
				case 'dailyspecifictime':
					$okToContinue=true;
					break;
				case 'everyotherdayspecifictime':
					if ($lastScheduleDate == null){
						$lastScheduleDate=$base->UtlObj->getTodaysDate(&$base);
						$lastScheduleDate_internal=$base->UtlObj->returnFormattedData($lastScheduleDate,'date','internal');
						$theAry['lastscheduledate']=$lastScheduleDate;
						$dbControlsAry=array('dbtablename'=>'scheduleeventprofile');
						$dbControlsAry['writerowsary'][]=$theAry;
						$base->DbObj->writeToDb($dbControlsAry,&$base);
					}
					$noDays=$base->UtlObj->getNoDays($lastScheduleDate,$nowDate,&$base);
					if ($noDays>=1){$okToContinue=true;}
					break;
			}
//- check if do it now
			if ($okToContinue){
				$okToContinue=false;
				if ($scheduleHour < $nowHour || ($scheduleHour == $nowHour && $scheduleMinute <= $nowMinute)){$okToContinue=true;}
			}
			if ($okToContinue){
				if (array_key_exists($healthEventProfileId,$actionAry)){
					if (array_key_exists($scheduleHour,$actionAry[$healthEventProfileId])){
						if (array_key_exists($scheduleMinute,$actionAry[$healthEventProfileId][$scheduleHour])){
							$okToContinue=false;
						}
					}
				}
			}
				//- do it
				//echo "xxxf0\n";
			if ($okToContinue){
				echo "type: $scheduleType, $scheduleHour".':'."$scheduleMinute, hid: $healthEventProfileId\n";
				$newActionEntryAry=array();
				$newActionEntryAry['actionhour']=$scheduleHour;
				$newActionEntryAry['actionminute']=$scheduleMinute;
				$newActionEntryAry['actionyear']=$nowYear;
				$newActionEntryAry['actionmonth']=$nowMonth;
				$newActionEntryAry['actionday']=$nowDay;
				$newActionEntryAry['healtheventprofileid']=$healthEventProfileId;
				$newActionEntryAry['actionperformed']='false';
				$dbControlsAry=array('dbtablename'=>'healthaction');
				$dbControlsAry['writerowsary'][0]=$newActionEntryAry;
				$base->DbObj->writeToDb($dbControlsAry,&$base);
			}
		}
		}
		}
	}
//==================================================
	function changeClassification($base){
		$thingsToDoId=$base->paramsAry['thingstodoid'];
		$currentType=$base->paramsAry['type'];
		switch ($currentType){
			case 'computer':
				$type='computertoday';
				break;
			case 'computertoday':
				$type='computer';
				break;
			case 'general':
				$type='today';
				break;
			case 'today':
				$type='general';
				break;
			default:
				echo "invalid type: $type";
				exit();
		}
		$rowUpdate=array('thingstodoid'=>$thingsToDoId, 'type'=>$type);
		$writeRowsAry=array();
		$writeRowsAry[]=$rowUpdate;
		$dbControlsAry=array('dbtablename'=>'thingstodo','writerowsary'=>$writeRowsAry);
		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//==================================================
	function buildJobStatTotals($base){
		$jobStackLoadId=$base->paramsAry['loadid'];
		if ($jobStackLoadId == null){
			$jobStackLoadId="jobstatsoutputdscontentid";
		}
		$lineBreak="<br>";
		$sendData=$base->paramsAry['senddata'];
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		foreach ($sendDataAry as $name=>$value){
			$dmyAry=explode('|',$value);
			$workAry[$dmyAry[0]]=$dmyAry[1];
		}
		//foreach ($workAry as $tst=>$theValue){
			//$theLen=strlen($theValue);
			//$fileValue=$_FILES['file'];
			//$fileLen=strlen($fileValue);
			//echo "$tst: $theValue, file len: $fileLen\n";
		//}
		//exit();//xxxf
		$companyProfileId=$workAry['companyprofileid'];
		$jobName=$workAry['jobnameid'];
		if ($jobName == '' || $jobName == 'all'){
			$jobStatNameInsert='';
		}
		else {
			$jobStatNameInsert=" and jobstatsname= '$jobName'";
		}
		$jobStatNameSortInsert=" order by jobstatsname, jobstatsdate";
		$typeOfReport=$workAry['jobstattypeid'];
		$startDate=$workAry['startdateid'];
		$endDate=$workAry['enddateid'];
		//foreach ($workAry as $name=>$value){echo "$name: $value\n";}
		$passAry=array('thedate'=>'today');
		$thisDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$wDayNo=$thisDateAry['wday'];
		$mDayNo=$thisDateAry['mday'];
		$yearNo=$thisDateAry['year'];
		$monthNo=$thisDateAry['mon'];
		//echo "wdayno: $wDayNo, yearno: $yearNo, monthno: $monthNo\n";exit();//xxxf
		switch ($startDate){
			case 'yearbegin':
				$startYear=$thisDateAry['year'];
				$startDateUse='1/1/'.$startYear;
				break;
			case 'monthbegin':
				$startDateUse=$monthNo.'/1/'.$yearNo;
				break;
			case 'weekbegin':
				$startDateUse=$base->UtlObj->adjustTodayDate($wDayNo,&$base);
				break;
			case 'now':
				$startDateUse=$monthNo.'/'.$mDayNo.'/'.$yearNo;
				break;
			default:
				$startDateUse=$startDate;
		}
		//echo "startdate: $startDate, startdateuse: $startDateUse";exit();//xxxf
		switch ($endDate){
			case 'yearbegin':
				$endYear=$thisDateAry['year'];
				$endDateUse='1/1/'+$endYear;
				break;
			case 'monthbegin':
				$endDateUse=$monthNo.'/1/'.$yearNo;
				break;
			case 'weekbegin':
				$endDateUse=$base->UtlObj->adjustTodayDate($wDayNo,&$base);
				break;
			case 'now':
				$endDateUse=$monthNo.'/'.$mDayNo.'/'.$yearNo;
				break;
			default:
				$endDateUse=$endDate;			
		}
		//echo "startdateuse: $startDateUse, enddateuse: $endDateUse\n";//xxxf
		$query="select * from jobstatsview where (jobstatsdate >= '$startDateUse' and jobstatsdate <= '$endDateUse') and companyprofileid = $companyProfileId $jobStatNameInsert $jobStatNameSortInsert";
		//echo "$query,,,,,,\n";exit();//xxxf
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$theDisplayStrg=NULL;
		$totalWorkAry=array();
		$cnt=count($workAry);
		foreach ($workAry as $ctr=>$valueAry){
			$theDate_raw=$valueAry['jobstatsdate'];
			$theDate=$base->UtlObj->convertDate($theDate_raw,'date1',&$base);
			$theCnt=$valueAry['jobstatscnt'];
			//echo "typeofreport: $typeOfReport, ";//xxxf
			switch ($typeOfReport){
				case 'daily':
					$totalWorkAry[$theDate]=$theCnt;
					break;
				default:
					switch ($typeOfReport){
						case 'weekly':$useDate=$base->UtlObj->getLastDayOfWeek($theDate,&$base);break;
						case 'mnthly':$useDate=$base->UtlObj->getLastDayOfMonth($theDate,&$base);break;
						case 'yrly':$useDate=$base->UtlObj->getLastDayOfYear($theDate,&$base);break;
						default:
							echo "error in typeofreport: $typeOfReport!!!\n";
					}
					$oldCnt=$totalWorkAry[$useDate];
					if ($oldCnt == null){$oldCnt=0;}
					$theCnt+=($oldCnt*1);
					$totalWorkAry[$useDate]=$theCnt;
			}
		}
		//$base->DebugObj->printDebug($totalWorkAry,1,'xxxf');//xxxf22
			$totalWorkAryCnt=count($totalWorkAry);
			if ($totalWorkAryCnt<=5){$noCols=1;}
			else if ($totalWorkAryCnt<=10){$noCols=2;}
			else if ($totalWorkAryCnt<=15){$noCols=3;}
			else {$noCols=4;}
			$ctr=0;
			$col1=array();
			$col2=array();
			$col3=array();
			$col4=array();
			foreach ($totalWorkAry as $theDate=>$theCnt){
				$ctr++;
				$theDisplayStrg="$theDate: $theCnt";
				switch ($noCols){
					case 1:
						$col1[]=$theDisplayStrg;
						break;
					case 2:
						$dmyNo=$totalWorkAryCnt/2;
						if ($ctr<=$dmyNo){$col1[]=$theDisplayStrg;}
						else {$col2[]=$theDisplayStrg;}
						break;
					case 3:
						$dmyNo=$totalWorkAryCnt/3;
						if ($ctr<=($dmyNo)){$col1[]=$theDisplayStrg;}
						else if ($ctr<=($dmyNo*2)){$col2[]=$theDisplayStrg;}
						else {$col3[]=$theDisplayStrg;}
						break;
					case 4:
						$dmyNo=$totalWorkAryCnt/4;
						if ($ctr<=($dmyNo)){$col1[]=$theDisplayStrg;}
						else if ($ctr<=($dmyNo*2)){$col2[]=$theDisplayStrg;}
						else if ($ctr<=($dmyNo*3)){$col3[]=$theDisplayStrg;}
						else {$col4[]=$theDisplayStrg;}
				}
			}
			//$dmyCnt1=count($col1);$dmyCnt2=count($col2);$dmyCnt3=count($col3);$cmyCnt4=count($col4);
			//echo "nocols: $noCols, one: $dmyCnt1, two: $dmyCnt2, three: $dmyCnt3, four: $dmyCnt4";exit();//xxxf
			$theStrg="<table class=\"jobstatstotals\">";
			$loopCnt=count($col1);
			$loopCnt++;
			for ($loopCtr=0;$loopCtr<$loopCnt;$loopCtr++){
				$theStrg.="<tr><td><span class=\"jobstatstotals\">$col1[$loopCtr]</span></td><td><span class=\"jobstatstotals\">$col2[$loopCtr]</span></td><td><span class=\"jobstatstotals\">$col3[$loopCtr]</span></td><td><span class=\"jobstatstotals\">$col4[$loopCtr]</span></td></tr>";
			}
			$theStrg.="</table>";
		echo "loadinnerhtml|$theStrg|$jobStackLoadId";
	}
//==================================================
	function insertImageToAlbum($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');
		$base->FileObj->writeLog('insertimage',"xxxf0 enter operationPlugin001Obj.insertImageToAlbum",&$base);
		$bang='&#33;';
		$colon='&#58;';
		$errorFlg=false;
		//- get image info
		$transferName=$base->paramsAry['transfername'];
		$base->FileObj->writeLog('insertimage',"xxxf0.2 start waiting for FILES[$transferName][error] to be less than 1",&$base);
		for ($waitLp=0;$waitLp<50;$waitLp++){
			$errorFlg=$_FILES[$transferName]['error'];
			if ($errorFlg<1){break;}
			sleep(1);
		}
		$base->FileObj->writeLog('insertimage',"xxxf0.3 done waiting for FILES[$transferName][error] to be less than 1, errorflg: $errorFlg",&$base);
		$theName=$_FILES[$transferName]['name'];
		$base->FileObj->writeLog('insertimage',"xxxf0.4 the file upload name is: $theName",&$base);
		$onlyTheNameAry=explode('.',$theName);
		$onlyTheName=$onlyTheNameAry[0];
		$onlyTheSuffix=$onlyTheNameAry[1];
		$onlyTheSuffix_lc=strtolower($onlyTheSuffix);
		if (
			$onlyTheSuffix_lc == 'png' ||
		 	$onlyTheSuffix_lc == 'jpg' ||
		  	$onlyTheSuffix_lc == 'gif'
		  ){$isImage=true;}
		  else {$isImage=false;}
		$theSize=$_FILES[$transferName]['size'];
		$theType=$_FILES[$transferName]['type'];
		$tmpFilePath=$_FILES[$transferName]['tmp_name'];
		$base->FileObj->writeLog('insertimage',"xxxf1 transfername: $transferName, size: $theSize, type: $theType, tmpfilepath: $tmpFilePath",&$base);
		//- album image is going into
		$albumProfileId=$base->paramsAry['albumprofileid'];
		$printStrg="$theName<br>";
		//$printStrg="file$colon $theName, size$colon $theSize, type$colon $theType, transfer error$colon $errorFlg<br>";
		if ($isImage){
		if ($albumProfileId != null){
			$query="select * from albumprofileview where albumprofileid=$albumProfileId";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$useAlbumAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$albumImageSettings=$useAlbumAry[0]['albumimagesettings'];
			$albumImageLength=$useAlbumAry[0]['albumimagelength'];
			$destinationDir=$useAlbumAry[0]['albumdirectory'];
			//$printStrg.="destinationdir: $destinationDir<br>";//xxxf
			if ($destinationDir != null){
				$localPath=$base->ClientObj->getBasePath(&$base);
				$destinationPath=$localPath.'//'.$destinationDir.'//'.$theName;
				//$printStrg.="destination path$colon $destinationPath<br>";
				$base->FileObj->writeLog('insertimage',"xxxf2 start upload tmpfilepath: $tmpFilePath, destpath: $destinationPath",&$base);
				$successRtn=@move_uploaded_file($tmpFilePath,$destinationPath);//xxxf22
				$base->FileObj->writeLog('insertimage',"xxxf3 Done upload successrtn: $successRtn",&$base);
				if (!$successRtn){
					$errorFlg=true;
					if ($theSize==0){
						$printStrg.="ERROR$colon file was not uploaded$bang<br> Possibly the file size exceeded 2 meg which is maximum$bang";
					}
					else {
						$printStrg.="ERROR$colon in moving tmp path $tmpFilePath to dest path $destinationPath$bang";
					}
				}
				else {
					if ($albumImageSettings != 'nochange' && $albumImageSettings != null){
						$doit=false;
						$imageWork = new Imagick($destinationPath);
	            		$oldImageWidth=$imageWork->getImageWidth();
    	        		$oldImageHeight=$imageWork->getImageHeight();
    	        		$printStrg.="width$colon $oldImageWidth, height$colon $oldImageHeight<br>";//xxxf
 						if ($albumImageSettings == 'setheight'){
							$imageHeight=$albumImageLength;
							//$printStrg.="album set to modify image by setting height to $imageHeight<br>";
							$imageWidth=0;
							if ($imageHeight < $oldImageHeight && $imageHeight>0){$doit=true;}
							else {$printStrg.="desired height$colon $imageHeight is greater than current height, so image left UNCHANGED<br>";}
						}
						else if ($albumImageSettings == 'setwidth'){
							$imageWidth=$albumImageLength;
							//$printStrg.="album set to modify image by setting width to $imageWidth<br>";
							$imageHeight=0;
							if ($imageWidth < $oldImageWidth && $imageWidth>0){$doit=true;}
							else {$printStrg.="desired width$colon $imageWidth is greater than current width, so image left UNCHANGED<br>";}
						}
						else {
							$printStrg.="invalid albumimagesettings$colon $albumImageSettings<br>";
						}
						if ($doit){
							$base->FileObj->writeLog('insertimage',"xxxf4 reduce size of image width: $imageWidth, height: $imageHeight",&$base);
							$imageWork->thumbnailImage($imageWidth, $imageHeight);
							$newImageWidth=$imageWork->getImageWidth();
            				$newImageHeight=$imageWork->getImageHeight();
            				$printStrg.="new width$colon $newImageWidth, new height$colon $newImageHeight<br>";//xxxf
            				$base->FileObj->writeLog('insertimage',"xxxf5 write image",&$base);
            				$imageWork->writeImage();
            				$imageWork->destroy();
            				//xxxf - dont we have to write it? destroy it?
						}
					}
					else {
						$printStrg.="album is not setup to modify images on upload<br>";
					}
					$newImageAry=array(
						'albumprofileid'=>$albumProfileId,
						'picturedirectory'=>$destinationDir,
						'pictureno'=>9999,
						'picturefilename'=>$theName,
						'picturename'=>$onlyTheName
					);
					$dbControlsAry=array('dbtablename'=>'pictureprofile');
					$writeRowsAry=array($newImageAry);
					$dbControlsAry['writerowsary']=$writeRowsAry;
					//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');
					$theSuccess=$base->DbObj->writeToDb($dbControlsAry,&$base);
//- reorder the album here
					$base->UtlObj->reorderAlbumInt($albumProfileId,5,&$base);
					if ($theSuccess){
						$printStrg.="Successful upload and placed at END OF ALBUM$bang<br>";
					}
					else {
						$printStrg.="Successful file upload but file name ALREADY IN ALBUM$bang<br>";
					}
				}
			}
			else {
				$printStrg.="ERROR$colon destination directory field in Album has not been setup$bang<br>";
			}
		} else {
			$printStrg.="ERROR$colon invalid album$bang<br>";
		}
		} else {
			$printStrg.="ERROR$colon you can only upload images$bang<br>";
		}
		echo "<script language=\"javascript\" type=\"text/javascript\">";
		//echo "window.top.window.alert('$printStrg');";
		echo "window.top.window.MenuObj.runBatchV2('";
		echo "gcfss?:clientadminalbumv2?!albumtr_albumprofileid_uservalue?!albumhdcontentid?!albumprofileid?!uservalue??w??";
		echo "ldv?:updatemessageid?!$printStrg');";
		echo "</script>";
	}
//==================================================
	function incCalls(){
		$this->colNo++;
	}
}
?>
