<?php
class DbObject {
	// 1/20/13 make insert/update/validate all load in dbcolumnprofile[dbcolumndefault] if colValue is null
	// 2/1/13 changed if (colvalue == NULL) to if (colValueLen == 0)
	var $statusMsg;
	var $callNo = 0;
	var $dbConn;
	var $toDbConn;
	var $useOtherDb = false;
	var $dontResetUseOtherDb = false;
	var $insertTotal=0;
	var $retrieveTotal=0;
	var $updateTotal=0;
	var $otherTotal=0;
	var $deleteTotal=0;
	var $dbDebugFlg=false;//xxxf
	var $rtnIdFlg=false;
	//=======================================
	function DbObject($base) {
		$this->incCalls();
		$this->connMain(&$base);
		$this->statusMsg='db Object is fired up and ready for work!';
	}
	//=======================================
	function displayStatus($msg,$base){
		//echo "$msg: db1: $this->dbConn, db2: $this->toDbConn, useotherdb: $this->useOtherDb, druodb: $this->dontResetUseOtherDb<br>";
	}
	//======================================= xxxd
	function setUseOtherDb($base){
		$lastProg=$base->DebugObj->getLastStackEntry();
		$base->FileObj->writeLog("debug","DbObj, setUseOtherDb($lastProg)",&$base);
		$this->useOtherDb=true;
		$this->dontResetUseOtherDb=false;
		$base->FileObj->writeLog("debug","DbObj, setUseOtherDb reset dontResetUseOtherDb",&$base);
	}
	//=======================================
	function setUseOtherDbNoReset($base){
		$lastProg=$base->DebugObj->getLastStackEntry();
		$base->UtlObj->appendValue("debug","DbObj,setUseOtherDbNoReset: call setUseOtherDb($lastProg)<br>",&$base);
		$this->setUseOtherDb(&$base);
		$base->UtlObj->appendValue("debug","DbObj,setUseOtherDbNoReset: set dontResetUseOtherDb($lastProg)<br>",&$base);
		$this->dontResetUseOtherDb=true;
	}
	//=======================================
	function unsetUseOtherDb($base){
		$lastProg=$base->DebugObj->getLastStackEntry();
		$base->UtlObj->appendValue("debug","DbObj, unset useOtherDb and unset dontResetUseOtherDb($lastProg)<br>",&$base);
		$this->useOtherDb=false;
		$this->dontResetUseOtherDb=false;
	}
	//=======================================
	function getDataForForm($passAry,$base){
		$base->DebugObj->printDebug("DbObj:getDataForForm($formName,'base')",0);
		//echo "dbobj.getdataforform: formname: $formName, db: $this->dbConn, db2: $this->toDbConn, useothdb: $useOtherDb, dnt rst: $dontResetUseOtherDb<br>";//xxxd
		// - figure out what to do
		$jobType=$passAry['jobtype'];
		if ($jobType == NULL){$jobType = 'default';}
		switch ($jobType){
			// ====================
			case 'feed':
				$rowAry=$passAry['usethisdataary'];
				//$base->DebugObj->printDebug($passAry,1,'xxxbeginfeed');
				$formName=$passAry['formname'];
				$returnAry=array();
				// - get dbtablename and edit it
				$dbTableName=$base->formProfileAry[$formName]['tablename'];
				if ($dbTableName != NULL){
					$pos=strpos($dbTableName,'_',0);
					if ($pos>0){
						$dmy=substr($dbTableName,0,$pos);
						$dbTableName=$dmy;
					}
					// - setup dbcontrols for table
					$dbControlsAry=array('dbtablename'=>$dbTableName);
					//$this->getDbTableMetaInfo(&$dbControlsAry,&$base); // xxx - restore if problems
					$this->getDbTableInfo(&$dbControlsAry,&$base);
					$newRowAry=$this->getDataForForm_convdata($rowAry,$dbControlsAry,&$base);
					//$base->DebugObj->printDebug($rowAry,1,'xxxrowary');
					$workAry=array();
					$workAry[]=$newRowAry;
					$dataRowsAry=array($dbTableName=>$workAry);
					$returnAry=array('dbcontrolsary'=>$dbControlsAry,'datarowsary'=>$dataRowsAry);
				} else {$returnAry=array();}
				break;
				//====================
			default:
				//$base->DebugObj->printDebug($passAry,1,'passary');//xxx
				$returnAry=$this->getDataForFormOld($passAry,&$base);
				//$base->DebugObj->printDebug($returnAry,1,'rtnary');//xxx
		}
		$base->DebugObj->printDebug("-rtn:getDataForForm",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function getDataForForm_convdata($rowAry,$dbControlsAry,&$base){
		$noRow=count($rowAry);
		if ($noRow>0){
			foreach ($rowAry as $colName=>$colValue){
				//$convCode=$dbControlsAry['dbtablemetaary'][$colName]['dbcolumnconversionname']; //xxx restore if problems
				$convCode=$dbControlsAry['dbtablemetaary'][$colName]['dbcolumnconversionname'];
				if ($colValue != NULL){
					switch ($convCode){
						case 'dateconv1':
							//echo "colvalue: $colValue<br>";//xxx
							$newColValue=substr($colValue,5,2).'/'.substr($colValue,8,2).'/'.substr($colValue,0,4);
							$rowAry[$colName]=$newColValue;
							break;
						case 'dateconv2':
							break;
					} // end switch convcode
				} // end if colvalue ne null
			} // end foreach rowsary[0] as colname, colvalue
		} // end if norow>0
		return $rowAry;
	}
	//=======================================
	function getDataForFormOld($passAry,$base){
		$base->DebugObj->printDebug("DbObj:getDataForForm($formName,'base')",0);
		$formName=$passAry['formname'];
		//echo "xxxf1: formname: $formName<br>";
		$returnAry=array();
		// - get dbtablename and edit it
		$dbTableName=$base->formProfileAry[$formName]['tablename'];
		$useOtherDb_raw=$base->formProfileAry[$formName]['formuseotherdb'];
		$useOtherDb=$base->UtlObj->returnFormattedData($useOtherDb_raw,'boolean','internal');
		if ($useOtherDb){
			$this->setUseOtherDb(&$base);
		}
		if ($dbTableName != ''){
			$pos=strpos($dbTableName,'_',0);
			if ($pos>0){
				$dmy=substr($dbTableName,0,$pos);
				$dbTableName=$dmy;
			}
			// - setup dbcontrols for table
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			//$this->getDbTableMetaInfo(&$dbControlsAry,&$base); // xxx - restore if problems
			$this->getDbTableInfo(&$dbControlsAry,&$base);
			//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx
			// - setup selector for read
			$selectorNameAry=$dbControlsAry['selectornameary'];
			if ($selectorNameAry != ""){
				$selectorAry=array();
				foreach ($selectorNameAry as $ctr=>$selectorName){
					$selectorValue=$paramsAry[$selectorName];
					if ($selectorValue != ""){
						$selectorAry[$selectorName]=$selectorValue;
					}
				}
				$dbControlsAry['selectorary']=$selectorAry;
			}
			// - get key, then use it to do read
			$keyName=$dbControlsAry['keyname'];
			$keyValue=$base->paramsAry[$keyName];
			//echo "xxxf2: dbtablename: $dbTableName, name: $keyName, value: $keyValue<br>";
			if ($keyValue != ""){
				$dbControlsAry['useselect']==false;
				$dbControlsAry['datarowsary'][]=array($keyName=>$keyValue);
				//xxxd - need to pass whic db to use here
				if ($useOtherDb){
					$this->setUseOtherDb(&$base);
				}
				$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
				//$base->DebugObj->printDebug($rowsAry,1,'rowsary');//xxxd
				foreach ($rowsAry[0] as $colName=>$colValue){
					//$convCode=$dbControlsAry['dbtablemetaary'][$colName]['dbcolumnconversionname']; //xxx restore if problems
					$convCode=$dbControlsAry['dbtablemetaary'][$colName]['dbcolumnconversionname'];
					//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();//xxxf
					//echo "name: $colName, value: $colValue, convcode: $convCode\n";//xxxf
					if ($colValue != NULL){
						switch ($convCode){
							case 'dateconv1':
								//echo "colvalue: $colValue<br>";//xxx
								$newColValue=substr($colValue,5,2).'/'.substr($colValue,8,2).'/'.substr($colValue,0,4);
								$rowsAry[0][$colName]=$newColValue;
								break;
							case 'dateconv2':
								break;
						} // end switch convcode
					} // end if colvalue ne null
				} // end foreach rowsary[0] as colname, colvalue
			} // end if keyvalue != null
			// - no key, then nothing - default prompting uses key to get one row
			else {
				$rowsAry=array();
			} // end else no key so null
			$returnAry=array('datarowsary'=>array(),'dbcontrolsary'=>$dbControlsAry);
			$returnAry['datarowsary'][$dbTableName]=$rowsAry;
			//-------------------- get individual data references in form
			if (array_key_exists($formName,$base->formDataProfileAry)){
				if (array_key_exists('read',$base->formDataProfileAry[$formName])){
					foreach ($base->formDataProfileAry[$formName]['read'] as $ctr=>$readDataValueAry){
						$sqlCommand=$readDataValueAry['formdatasqlcommand'];
						$thisDbTableName=$readDataValueAry['formdatadbtablename'];
						if ($thisDbTableName == ''){$thisDbTableName='none';}
						if ($useOtherDb){
							$this->setUseOtherDb(&$base);
						}
						$result=$base->DbObj->queryTable($sqlCommand,'read',&$base);
						$resultAry=$base->UtlObj->tableToHashAryV3($result);
						$returnAry['datarowsary'][$thisDbTableName]=$resultAry;
					} // end foreach 'read'
				} // end arraykeyexists('read')
			} // end if arraykeyexists('formname')
		} // end if dbtablename ne null
		$base->DebugObj->printDebug("-rtn:getDataForForm",0); //xx (f)
		//$base->DebugObj->printDebug($returnAry,1,'xxxf');exit();//xxxf
		return $returnAry;
	}
	//=======================================
	function insertDbFromForm($base){
		$base->DebugObj->printDebug("DbObj:insertDbFromForm('base')",0);
		$base->Plugin001Obj->insertDbFromForm($base);
		$base->DebugObj->printDebug("-rtn:DbObj:insertDbFromForm",0); //xx (f)
	}
	//=======================================
	function buildList($tableName, $base){
		$base->DebugObj->printDebug("DbObj:buildList($urlNoSt, 'base')",0);
		$returnAry=array();
		if ($tableName != ""){
			$query = "select * from $tableName order by label";
			$result=$this->queryTable($query,'retrieve',&$base);
			$workAry=$base->UtlObj->tableToHashAryV3($result);
			$noCnt=count($workAry);
			for ($ctr=0;$ctr<$noCnt;$ctr++){
				$url=$workAry[$ctr]['url'];
				$label=$workAry[$ctr]['label'];
				$returnAry[]="<tr><td><a href=\"$url\">$label</a>";
			}
		}
		$base->DebugObj->printDebug("-rtn:DbObj:buildList",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function buildMenuList($tableName,$base){
		$base->DebugObj->printDebug("DbObj:buildMenuList('base')",0);
		$jobSt=$base->paramsAry['job'];
		$query="select * from staticmenuprofile where jobname='$jobSt' order by label";
		$result=$this->queryTable($query,'retrieve',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		$returnAry=$base->HtmlObj->buildTableHeaders($tableName,&$base);
		$noCnt=count($workAry);
		for ($ctr=0;$ctr<$noCnt;$ctr++){
			$jobLink=$workAry[$ctr]['joblink'];
			$pos=strpos($jobLink,'jobname',0);
			$url_raw="%joblocal%$jobLink";
			$url=$base->UtlObj->returnFormattedString($url_raw,&$base);
			$label=$workAry[$ctr]['label'];
			$returnAry[]="<tr><td><a href=\"$url\">$label</a></td></tr>";
		}
		$returnAry[]="</table>";
		$returnAry[]="</center>";
		$base->DebugObj->printDebug("-rtn:DbObj:buildMenuList",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function connMain($base){
		$domainName='default';
		$this->dbConn=$base->ClientObj->getClientConn($domainName,&$base);
	}
	//======================================= deprecated
	function getConn($dbName, $dbUser, $dbPwd, $base){
		$theDbConn=pg_connect("dbname=$dbName user=$dbUser password=$dbPwd");
		return $theDbConn;
	}
	//=======================================
	function getSimpleProfile($tableName,$base){
		$base->DebugObj->printDebug("DbObj:getSimpleProfile($tableName,'base')",-2);
		$returnAry=array();
		$job = $base->paramsAry['job'];
		$query="select * from $tableName".'view';
		switch ($tableName){
			case 'systemprofile':
				$query.=" where domainname='home'";
				break;
			default:
				$query.=" where jobname='$job'";
		}
		$result=$this->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error7 query: $query<br>";}
		else {$returnAry=$base->UtlObj->tableRowToHashAry($result);}
		$base->DebugObj->printDebug("-rtn:DbObj:getSimpleProfile",-2); //xx (f)
		return $returnAry;
	}
	//=======================================
	function getSimpleMultiProfile($tableName,$base){
		$base->DebugObj->printDebug("DbObj:getSimpleMultiProfile($tableName,'base')",-2);
		$returnAry=array();
		$jobSt = $base->jobSt;
		$query="select * from $tableName";
		switch ($tableName){
			case 'systemprofile':
				$query.=" where domainname='home'";
				break;
			default:
				$query.=" where jobname='$jobSt'";
		}
		$result=$this->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error2";}
		else {$returnAry=$base->UtlObj->tableToHashAryV3($result);}
		$base->DebugObj->printDebug("-rtn:DbObj:getSimpleMultiProfile",-2); //xx (f)
		return $returnAry;
	}
	//=======================================simple
	function getPluginProfile($base){
		$base->DebugObj->printDebug("DbObj:getPluginProfile('base')",-2);
		$returnAry=array();
		$workAry=array();
		$newAry=array();
		$jobSt = $base->jobSt;
		//- operation
		$query="select * from pluginprofile where plugintype='operation'";
		$result=$this->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error3";}
		else {$workAry=$base->UtlObj->tableToHashAryV3($result);}
		$cnt=count($workAry);
		for ($lp=0;$lp<$cnt;$lp++){
			$newAry=$workAry[$lp];
			$returnAry['operation'][$newAry['pluginname']]=$newAry;
		}
		//- tag
		$query="select * from pluginprofile where plugintype='tag'";
		$result=$this->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error4";}
		else {$workAry=$base->UtlObj->tableToHashAryV3($result);}
		$cnt=count($workAry);
		for ($lp=0;$lp<$cnt;$lp++){
			$newAry=$workAry[$lp];
			$returnAry['tag'][$newAry['pluginname']]=$newAry;
		}
		//- app
		$query="select * from pluginprofile where plugintype='app'";
		$result=$this->queryTable($query,'retrieve',$base,-2);
		if (!$result) {echo "Error5";}
		else {$workAry=$base->UtlObj->tableToHashAryV3($result);}
		$cnt=count($workAry);
		for ($lp=0;$lp<$cnt;$lp++){
			$newAry=$workAry[$lp];
			$returnAry['app'][$newAry['pluginname']]=$newAry;
		}
		$base->DebugObj->printDebug("-rtn:DbObj:getPluginProfile",-2); //xx (f)
		return $returnAry;
	}
	//======================================
	function getComplexProfile($dbTableName,$select1,$delimit1,$delimit2='',$base){
		$base->DebugObj->printDebug("DbObj:getComplexProfile($tableName,$delimit1,$delimit2,'base')",-2);
		$returnAry=array();
		if ($select1=='jobname'){$useSelect1='job';}
		else {$useSelect1='jobname';}
		$selectValue1=$base->paramsAry[$useSelect1];
		$query="select * from $dbTableName where $select1='$selectValue1'";
		$result=$this->queryTable($query,'retrieve',&$base,-2);
		$passAry=array();
		if ($delimit1 != ''){$passAry['delimit1']=$delimit1;}
		if ($delimit2 != ''){$passAry['delimit2']=$delimit2;}
		$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$base->DebugObj->printDebug("-rtn:DbObj:getDbTableProfile",-2); //xx (f)
		return $returnAry;
	}
	//=======================================
	function getTableData($tableName,$base){
		$base->DebugObj->printDebug("DbObj:getTableData($tableName,'base')",0);
		$sendDataAry=$base->UtlObj->breakOutSendData($base);
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');exit();//xxxf
		$strg="xxxf: job: $useJob---paramsary in DbObject when getting table data---\n";
		foreach ($base->paramsAry as $name=>$value){
			//if ($name=='senddata'){$value="...";}
			$strg.="$name: $value\n";
		}
		$base->FileObj->writeLog('xxxf',$strg,&$base);
		$returnAry=array();
		//echo "dbobj.gettabledata: tablename: $tableName, db: $this->dbConn, db2: $this->toDbConn, useothdb: $useOtherDb, dnt rst: $dontResetUseOtherDb<br>";//xxxd
		$tableProfile=$base->tableProfileAry[$tableName];
		//$base->DebugObj->printDebug($base->tableProfileAry,1,'xxx');
		//- get values
		$dbTableName=$tableProfile['dbtablename'];
		//echo "dbtablename: $dbTableName<br>";//xxx
		$dbTableNameView=$dbTableName.'view';
		$sortKey1=$tableProfile['sortkey1'];
		$sortKey2=$tableProfile['sortkey2'];
		$sortKey3=$tableProfile['sortkey3'];
		$defaultDisplay=$tableProfile['defaultdisplay'];
		if ($defaultDisplay == NULL){$defaultDisplay='showall';}
		//- get table definition
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		//$this->getDbTableMetaInfo(&$dbControlsAry,&$base); //xxx - restore if problems
		$this->getDbTableInfo(&$dbControlsAry,&$base);
		//$base->DebugObj->printDebug($dbControlsAry,1,'xxx');
		//exit();//xxx
		$selectKey1=$tableProfile['selectkey1'];
		//$base->DebugObj->printDebug($tableProfile,1,'xxxd');
		$base->FileObj->writeLog('xxxf','xxxf1',&$base);
		if ($selectKey1 != NULL){
			$selectKey1Type=$dbControlsAry['dbtablemetaary'][$selectKey1]['dbcolumntype'];
			if ($tableName == 'cssprofile'){
				$base->FileObj->writeLog('jefftest',"xxxf2 selectkey1: $selectKey1, selectkey1type: $selectKey1Type",&$base);
			}
			//echo "name: $selectKey1, type: $selectKey1Type<br>";//xxx
			//exit();//xxx
			if ($selectKey1Type == 'boolean'){$selectKey1Value='true';}
			else {$selectKey1Value=$base->paramsAry[$selectKey1];}
			if ($selectKey1Value == null){
				$selectKey1Value=$sendDataAry[$selectKey1];
				$base->FileObj->writeLog('xxx',"get from send data selectkey1: $selectKey1, selectkey1value: $selectKey1Value",&$base);
			}
			//echo "dbobj L391: selectkey1: $selectKey1, selectkey1value: $selectKey1Value<br>";//xxxf
		}
		$base->FileObj->writeLog('debug',"DbObj.getTableData: selectkey1: $selectKey1, selectkey1type: $selectKey1Type, selectkey1value: $selectKey1Value",&$base);
		if ($tableName == 'cssprofile'){
			$strg='';
			foreach ($base->paramsAry as $name=>$value){
				$strg.=$name.': '.$value.", ";
			}
			$base->FileObj->writeLog('jefftest',$tableName.': '.$strg,&$base);
		}
		$selectKey2=$tableProfile['selectkey2'];
		if ($selectKey2 != NULL){
			$selectKey2Type=$dbControlsAry['dbtablemetaary'][$selectKey2]['dbcolumntype'];
			if ($selectKey2Type == 'boolean'){$selectKey2Value='true';}
			else {$selectKey2Value=$base->paramsAry[$selectKey2];}
			if ($selectKey2Value == null){
				$selectKey2Value=$sendDataAry[$selectKey2];
			}
		}
		$selectKey3=$tableProfile['selectkey3'];
		if ($selectKey3 != NULL){
			$selectKey3Type=$dbControlsAry['dbtablemetaary'][$selectKey3]['dbcolumntype'];
			if ($selectKey3Type == 'boolean'){$selectKey3Value='true';}
			else {$selectKey3Value=$base->paramsAry[$selectKey3];}
			if ($selectKey3Value == null){
				$selectKey3Value=$sendDataAry[$selectKey3];
			}
		}
		//- start sql statement with filter
		$base->FileObj->writeLog('xxxf','xxxf1',&$base);
		$query="select *";
		$query.=" from $dbTableNameView";
		if ($selectKey1 != NULL){
			if ($selectKey1Value != NULL && $selectKey1Value != 'NULL'){
				$hasFilter = true;
				$query.=" where $selectKey1='$selectKey1Value'";
				if ($selectKey2 != NULL){
					if ($selectKey2Value != NULL && $selectKey2Value != 'NULL'){
						$query.=" and $selectKey2='$selectKey2Value'";
					}
				}
				if ($selectKey3 != NULL && $selectKey3 != 'NULL'){
					if ($selectKey3Value != NULL){
						$query.=" and $selectKey3='$selectKey3value'";
					}
				}
			}
			else {$hasFilter = false;}
		}
		//- add sorts to sql statement
		$sortInsert=' order by ';
		$useComma='';
		//- from cgi
		if (array_key_exists('sort',$base->paramsAry)){
			$sortOverride=$base->paramsAry['sort'];
			if (array_key_exists($sortOverride,$dbControlsAry['dbtablemetaary'])){
				$query.=$sortInsert.$sortOverride;
				$sortInsert=NULL;
				$useComma=",";
			}
		}
		//- sort from tableprofile setups
		if ($sortKey1 != ''){
			//echo 'xxx1: '.$sortKey1;
			$pos=strpos($sortKey1,'descending');
			if ($pos>0){
				$sortKey1=str_replace('descending','',$sortKey1);
				$sortKey1Suffix=' desc';
			}
			if (array_key_exists($sortKey1,$dbControlsAry['dbtablemetaary'])){
				//echo 'xxx2';
				$query.=$sortInsert.$useComma.$sortKey1.$sortKey1Suffix;
			}
			else {echo "sortkey: $sortKey1 is not on file!";}
		}
		if ($sortKey2 != ''){
			if (array_key_exists($sortKey2,$dbControlsAry['dbtablemetaary'])){
				$query.=','.$sortKey2;
			}
			else {echo "sortkey: $sortKey2 is not on file!";}
		}
		if ($sortKey3 != ''){
			if (array_key_exists($sortKey3,$dbControlsAry['dbtablemetaary'])){
				$query.=','.$sortKey3;
			}
			else {echo "sortkey: $sortKey3 is not on file!";}
		}
		$base->FileObj->writeLog('dbobject',"tablename: $tableName, dbtablename: $dbTableName,  query: $query",&$base);
		//- run query
		//$base->DebugObj->printDebug($base->paramsAry,1,'paa');//xxx
		//echo "dbobj, gettabledata L460: query: $query";//xxxf
		//echo "hasfilter: $hasFilter, defaultdisplay: $defaultDisplay\n";//xxxf
		$base->FileObj->writeLog('xxxf',"hasfilter: $hasFilter, defaultdisplay: $defaultDisplay",&$base);
		if ($hasFilter || $defaultDisplay == 'showall'){
			//which db do we get the data from? xxxd
			$tableUseOtherDb_raw=$tableProfile['tableuseotherdb'];
			$tableUseOtherDb=$base->UtlObj->returnFormattedData($tableUseOtherDb_raw,'boolean','internal');
			//echo "tablename: $tableName, db: $this->dbConn, db2: $this->toDbConn, tableuseotherdb: $tableUseOtherDb<br>";//xxxd
			if ($tableUseOtherDb){
				$this->setUseOtherDb(&$base);
			}
			//$this->dbDebugFlg=true;//xxxf
			//echo "DbObj L469: query: $query<br>";//xxxf
			$pos=strpos('csseventprofile',$query,0);
			if ($pos>-1){
				$base->FileObj->writeLog('dbobject','the query: $query',&$base);//xxxf
			}
			//exit();//xxxf
			$result=$this->queryTable($query,'retrieve',&$base);
			//$this->dbDebugFlg=false;//xxxf
			//- convert to simple array using row numbers 0,1,2
			if (!$result) {echo "Error6";}
			else {
				//echo "call hash program<br>";//xxx
				//$dbTableAry=$base->UtlObj->tableToHashAryV3($result); // xxx - restore if problems
				$passAry=array('dbcontrolsary'=>$dbControlsAry);
				$dbTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				//$base->DebugObj->printDebug($dbTableAry,1,'xxx');
				//exit();//xxx
			}
		} else {$dbTableAry=array();}
		//$base->DebugObj->printDebug($dbTableAry,1,'xxx');
		//exit();//xxx
		//- build return array
		//- general
		$returnAry['keyname']=$dbControlsAry['keyname'];
		$returnAry['dbtablename']=$dbControlsAry['dbtablename'];
		$returnAry['parentselectorname']=$dbControlsAry['parentselectorname'];
		//- definitions
		$returnAry['dbtablemetaary']=$dbControlsAry['dbtablemetaary'];
		$subReturnAry=array();
		//- select keys
		$subReturnAry['selectkey1']=$selectKey1;
		$subReturnAry['selectkey2']=$selectKey2;
		$subReturnAry['selectkey3']=$selectKey3;
		$subReturnAry['selectkey1type']=$selectKey1Type;
		$subReturnAry['selectkey2type']=$selectKey2Type;
		$subReturnAry['selectkey3type']=$selectKey3Type;
		$subReturnAry['selectkey1value']=$selectKey1Value;
		$subReturnAry['selectkey2value']=$selectKey2Value;
		$subReturnAry['selectkey3value']=$selectKey3Value;
		//- sort keys
		$subReturnAry['sortkey1']=$sortKey1;
		$subReturnAry['sortkey2']=$sortKey2;
		$subReturnAry['sortkey3']=$sortKey3;
		$returnAry['thefilters']=$subReturnAry;
		//- data
		//$base->DebugObj->printDebug($dbTableAry,1,'xxxd');
		$returnAry['dbtableary']=$dbTableAry;
		$base->DebugObj->printDebug("-rtn:DbObj:getTableData",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function updateTablesBatch($base){
		$base->DebugObj->printDebug("DbObj:updateTablesBatch('base')",0);
		$paramsAry=$base->paramsAry;
		//$tableName=$base->formProfileAry['formname1']['tablename'];
		//$tableMetaData=$base->formProfileAry['formname1']['tablemetadata'];
		$tableName=$paramsAry['dbtablename'];
		$tableMetaData=$this->getTableMetaStuff($tableName,'asctr',&$base);
		$inputString=$paramsAry['textinput'];
		$inputRawAry=explode("\n",$inputString);
		$cntInput=count($inputRawAry);
		for ($ctr=0;$ctr<$cntInput;$ctr++){
			$inputRowAry=explode(',',$inputRawAry[$ctr]);
			$cntInputRowAry=count($inputRowAry);
			echo "<br>";
			$query="insert into $tableName ";
			$nameList="";
			$valueList="";
			for ($ctr2=0;$ctr2<$cntInputRowAry;$ctr2++){
				$ctr3=$ctr2+1;
				$updateValue=$inputRowAry[$ctr2];
				$fieldName=$tableMetaData[($ctr3)]['dbcolumnname'];
				$fieldType=$tableMetaData[($ctr3)]['dbcolumntype'];
				if ($updateValue != ""){
					if (substr($fieldType,0,3) == 'var'){$updateValue="'$updateValue'";}
					if (substr($fieldType,0,4) == 'date'){$updateValue="'$updateValue'";}
					if ($nameList != "") {
						if ($ctr2 != $cntInputRowAry) {
							$nameList .= ", ";
							$valueList .= ", ";
						}
					}
					$nameList.=$fieldName;
					$valueList.=$updateValue;
				} // end if != ''
			} // end of for - fields
			$nameList="($nameList)";
			$valueList="($valueList)";
			$query.=" $nameList values $valueList";
			$result=$this->queryTable($query,'insert',&$base);
		} // end of for - rows
		$base->DebugObj->printDebug("-rtn:DbObj:updateTablesBatch",0); //xx (f)
	}
	//=======================================
	function updateMultiRows($base){
		$base->DebugObj->printDebug("DbObj:updateMultiRows",0);
		$formAry=$base->formProfileAry['formname1'];
		$selectName=$formAry['selectorname'];
		$tableName=$formAry['tablename'];
		$doTheUpdateAry=array();
		foreach ($base->paramsAry as $fullParamName=>$paramValue){
			$thePos=strpos($fullParamName,'_',0);
			if ($thePos>0){
				$theLen=strlen($fullParamName);
				$paramName=substr($fullParamName,0,$thePos);
				$paramNo=substr($fullParamName,($thePos+1),($theLen-$thePos));
				$doTheUpdateAry[$paramNo][$paramName]=$paramValue;
			}
		}
		$this->doDbTableUpdateFromAry($base,$tableName,$selectName,$doTheUpdateAry);
		$base->DebugObj->printDebug("-rtn:DbObj:updateMultiRows",0); //xx (f)
	}
	//=======================================
	function doDbTableUpdateFromAry($base,$tableName,$selectName,$dataAry){
		$base->DebugObj->printDebug("DbObj:doDbTableUpdateFromAry($base,$tableName,$selectName,$dataAry)",0);
		$tableTypes=$base->formProfileAry['formname1']['tablenametype'];
		foreach ($dataAry as $key=>$rowValues){
			$selectValue=$rowValues[$selectName];
			//-update it
			if ($selectValue != ""){
				$query="update $tableName set ";
				$first=true;
				foreach ($rowValues as $colName=>$colValue){
					$colType=$tableTypes[$colName];
					if ($colType == 'varchar' || $colType == 'text'){$colValue="'$colValue'";}
					else {
						if ($colValue == ''){$colValue='NULL';}
					}
					if ($colValue == "t"){$colValue="true";}
					if ($colValue == "f"){$colValue="false";}
					if ($colName != $selectName){
						if ($first){$cma="";}
						else {$cma=",";}
						$first=false;
						$query.=" $cma$colName=$colValue";
					} // end if
				} // end foreach
				$query.=" where $selectName=$selectValue";
			} //end if
			else {
				//-insert it
				$query="insert into $tableName (";
				$first=true;
				foreach ($rowValues as $colName=>$colValue){
					$colType=$tableTypes[$colName];
					if ($colType == 'varchar' || $colType == 'text'){$colValue="'$colValue'";}
					else {
						if ($colValue == ''){$colValue='NULL';}
					}
					if ($colName != $selectName){
						if ($first){
							$first=false;
							$queryNames=$colName;
							$queryValues=$colValue;
						} //end if
						else {
							$queryNames.=",$colName";
							$queryValues.=",$colvalue";
						} // end else
					} // end if
				} // end foreach
				$query.=" ($queryNames) values ($queryValues)";
			} // end else
			//echo "<br>query: $query";
			$this->queryTable($query,'update',&$base);
		} // end foreach
		$base->DebugObj->printDebug("-rtn:DbObj:doDbTableUpdateFromAry",0); //xx (f)
	}
	//=======================================
	function buildDbMetaTable($passAry,&$base){
		$base->DebugObj->printDebug("DbObj:buildDbTableMetaTable($passAry,&'base')",0); //xx (h)
		//- get basic stuff
		$dbTableName=$passAry['dbtablename'];
		$dbTableMetaKeyName=$dbTableName.'id';
		$dbTableMetaSelectorsAry=$passAry['dbcolumnselectorsary'];
		$dbTableMetaForeignKeyAry=$passAry['dbcolumnforeignkeyary'];
		//- get the table stuff
		$dbMetaStuff=$this->getTableMetaStuff($dbTableName,'asctr',&$base);
		//- build file i/o interface
		$dbControlsAry=array();
		$dbControlsAry['dbtablename']='dbcolumnprofile';
		$dbControlsAry['selectornameary']=array('0'=>'dbcolumnname','1'=>'dbcolumnname');
		$dbControlsAry['selectorary']=array('dbcolumnname'=>'error','dbcolumnname'=>'error');
		$dbControlsAry['keyname']='dbcolumnprofileid';
		//- add data to file i/o interface
		$dbControlsAry['writerowsary']=array();
		foreach ($dbMetaStuff as $key=>$valueary){
			$dbTableMetaColumnName=$valueary['dbcolumnname'];
			//-determine type
			$dbTableMetaType_fromtable=$valueary['dbcolumntype'];
			$dbTableMetaType=$base->UtlObj->returnFormattedData($dbTableMetaType_fromtable,'in','dbtype');
			//- determine if selector
			if (array_key_exists($dbTableMetaColumnName,$dbTableMetaSelectorsAry)){
				$dbTableMetaSelector=true;
				$selectorType=$dbTableMetaSelectorsAry[$dbTableMetaColumnName];
				if ($selectorType == 'parent'){
					$dbTableMetaParentSelector=true;}
					else {$dbTableMetaParentSelector=false;}
			}
			else {
				$dbTableMetaSelector=false;
				$dbTableMetaParentSelector=false;
			}
			//- determine if key
			if ($dbTableMetaColumnName == $dbTableMetaKeyName){
				$dbTableMetaKey='true';
			}
			else {
				$dbTableMetaKey=false;
			}
			//-put it together
			$rowToWrite=array();
			$rowToWrite['dbtablemetaname']=$dbTableName;
			$rowToWrite['dbcolumnname']=$dbTableMetaColumnName;
			$rowToWrite['dbcolumntype']=$dbTableMetaType;
			$rowToWrite['dbcolumnselector']=$dbTableMetaSelector;
			$rowToWrite['dbcolumnparentselector']=$dbTableMetaParentSelector;
			$rowToWrite['dbcolumnkey']=$dbTableMetaKey;
			//
			$dbControlsAry['writerowsary'][]=$rowToWrite;
		}
		//- add in foreign key rows
		foreach ($dbTableMetaForeignKeyAry as $key=>$value){
			$rowToWrite=array();
			$rowToWrite['dbcolumnname']=$dbTableName;
			$rowToWrite['dbcolumnname']=$key;
			$rowToWrite['dbcolumntype']='varchar';
			$rowToWrite['dbcolumnselector']=false;
			$rowToWrite['dbcolumnkey']=false;
			$rowToWrite['dbcolumnforeignkey']=true;
			$dbControlsAry['writerowsary'][]=$rowToWrite;
		}
		//- delete old
		$query="delete from dbtablemetaprofile where dbtablemetaname='$dbTableName'";
		$base->DbObj->queryTable($query,'delete',&$base);
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->DebugObj->printDebug("-rtn:buildDbMetaTable",0); //xx (f)
	}
	//======================================= deprecated 8/20/7
	function deprecatedgetDbTableMetaDataFromDb($dbTableName,$base){
		$base->DebugObj->printDebug("DbObj:getDbTableMetaDataFromDb('base')",0);
		$returnAry=array();
		$query="select * from $dbTableName";
		$res=$this->queryTable($query,'retrieve',&$base,-1);
		if ($res){
			$noFields = pg_num_fields($res);
			for ($ctr = 0; $ctr < $noFields; $ctr++) {
				$fieldName = pg_field_name($res, $ctr);
				$fieldType = pg_field_type($res, $ctr);
				$returnAry[$fieldName]=$fieldType;
			}
		}
		$base->DebugObj->printDebug("-rtn:getDbTableMetaDataFromDb",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function getDbTableDataFromDb($dbTableName,$base){
		$base->DebugObj->printDebug("DbObj:getDbTableMetaDataFromDb('base')",0);
		$returnAry=array();
		$query="select * from $dbTableName";
		$res=$this->queryTable($query,'retrieve',&$base,-1);
		if ($res){
			$noFields = pg_num_fields($res);
			for ($ctr = 0; $ctr < $noFields; $ctr++) {
				$fieldName = pg_field_name($res, $ctr);
				$fieldType = pg_field_type($res, $ctr);
				$returnAry[$fieldName]=$fieldType;
			}
		}
		$base->DebugObj->printDebug("-rtn:getDbTableMetaDataFromDb",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function retrieveAryFromDb($query,$passAry,$base,$prio=0){
		$result=$this->queryTable($query,'read',&$base,$prio);
		$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		return $returnAry;
	}
	//=======================================
	function queryTableRead($query,$passAry,$base){
		$result=$this->queryTable($query,'read',&$base);
		$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		return $returnAry;
	}
	//=======================================
	function queryTable($query,$job,$base,$prio=0){
		$base->DebugObj->printDebug("DbObj:queryTable($query,$job,'base')",-2);
		if ($this->dbDebugFlg == true){echo 'xxxf: DbObj.queryTable at beginning, query: '+$query;}
		$insertQuery='';
		$dontConvert=false;
		$doItQuietly=true;
		switch ($job){
			case 'checkon':
				break;
			case 'select':
				$this->selectTotal++;
				break;
			case 'read':
				$this->readTotal++;
				break;
			case 'maint':
				$this->maintTotal++;
				$doItQuietly=false;
				break;
			case 'updatenotquietly':
				$this->updateTotal++;
				$doItQuietly=false;
				break;
			case 'util':
				$this->updateTotal++;
				$doItQuietly=false;
				break;
			case 'update':
				$this->updateTotal++;
				break;
			case 'updatequietly':
				$this->updateTotal++;
				$doItQuietly=true;
				break;
			case 'updatenoconversion':
				$this->updateTotal++;
				$dontConvert=true;
				break;
			case 'updateliteral':
				$this->updateTotal++;
				break;
			case 'insertliteral':
				$this->insertTotal++;
				break;
			case 'insert':
				$this->insertTotal++;
				break;
			case 'delete':
				$this->deleteTotal++;
				break;
			case 'retrieve':
				$this->retrieveTotal++;
				$dateQuery='set datestye = sql';
				//$returnResult=pg_query($this->dbConn,"$dateQuery");
				break;
			default:
				$this->otherTotal++;
				echo "!!! $job is invalid !!!";
				exit();
				break;
		}
		$percentPos=strpos($query,'%',0);
		$chkQuery=strtolower($query);
		$convertErrorFlg=false;
		if ($prio>0){echo "dontconvert: $dontConvert<br>";}
		if (!$dontConvert){
			$tstPos=strpos($query,'%',0);
			if ($tstPos>0){
				unset($base->errorProfileAry['converterror']);
				$oldQuery=$query;
				$query=$base->UtlObj->returnFormattedString($oldQuery,&$base);
				/*
				 $pos=strpos($query,'jobstats',0);//xxxf22
				 if ($pos>0){
				 $base->DebugObj->printDebug($base->paramsAry,1,'paramsaryxxxf22');
				 echo "db:qt old: $oldQuery, query: $query";exit();//xxxf22
				 }
				 */
				//echo "dbobj xxxf7.5";
				$base->UtlObj->appendValue('debug',"convert query to: $query<br>",&$base);
				if (array_key_exists('converterror',$base->errorProfileAry)){$convertErrorFlg=true;}
			}
			$newQuery=str_replace('~','%',$query);
			$query=$newQuery;
		} // end if !dontconvert
		$base->DebugObj->showQuery("$query",$base,$prio);
		//echo "query: $query<br>";//xxxd
		$returnResult=array();
		//xxxf
		if (!$convertErrorFlg){
			if ($this->useOtherDb){
				if ($this->toDbConn != null){
					$useDbConn=$this->toDbConn;
					$lastProg=$base->DebugObj->getLastStackEntry();
					$base->UtlObj->appendValue('debug',"use todbconn ($lastProg) $query<br>",&$base);
				}
				else {
					$useDbConn=$this->dbConn;
					$lastProg=$base->DebugObj->getLastStackEntry();
					$base->UtlObj->appendValue('debug',"use dbconn want to use todbconn ($lastProg) $query<br>",&$base);
				}
				if (!($this->dontResetUseOtherDb)){
					$this->unsetUseOtherDb(&$base);
				}
			}
			else {
				$useDbConn=$this->dbConn;
				//- below can happen before utilObj is even fired up so check for it
				if ($base->UtlObj != null){
					$lastProg=$base->DebugObj->getLastStackEntry();
					$base->UtlObj->appendValue('debug',"use dbconn ($lastProg) $query<br>",&$base);//xxxg
				}
			}
			$pos=strpos(('x'.$query),'insert',0);
			if ($pos>0){$query.="";}
			$returnResult=pg_query($useDbConn,"$query");
			$base->FileObj->writeLog('debug',"query: $query \n useotheredb: $this->useOtherDb, conn: $this->dbConn, toconn: $this->toDbConn, useconn: $useDbConn",&$base);
			if ($returnResult == null){
				$lastError=pg_last_error($useDbConn);
				if (!$doItQuietly){
					$base->DebugObj->placeCheck("!!!DbObj.queryTable: SQL Error:($lastError) $query"); //xx (c)
					$base->DebugObj->displayStack();
				}
				$workStrg="dbConn: $this->dbConn, toDbConn: $this->toDbConn, useOtherDb: $this->useOtherDb";
				$base->ErrorObj->saveError('sqlerror',"workstrg: $workStrg, sql error($lastError): $query",&$base);
				$base->FileObj->writeLog('debug',"workstrg: $workStrg, lasterror: $lastError",&$base);
			}
		}
		$base->DebugObj->printDebug("-rtn:DbObj:queryTable",-2); //xx (f)
		return $returnResult;
	}
	//=======================================
	function queryTableAnyDb($query,$queryType,$dbConn,$base,$prio=0){
		$base->DebugObj->printDebug("DbObj:queryTable($query,$job,'base')",-2);
		$insertQuery='';
		$dontConvert=false;
		switch ($queryType){
			case 'update':
				$this->updateTotal++;
				break;
			case 'updatenoconversion':
				$this->updateTotal++;
				$dontConvert=true;
				break;
			case 'updateliteral':
				$this->updateTotal++;
				break;
			case 'insertliteral':
				$this->insertTotal++;
				break;
			case 'insert':
				$this->insertTotal++;
				break;
			case 'delete':
				$this->deleteTotal++;
				break;
			case 'retrieve':
				$this->retrieveTotal++;
				$dateQuery='set datestye = sql';
				//$returnResult=pg_query($this->dbConn,"$dateQuery");
				break;
			default:
				$this->otherTotal++;
				break;
		}
		$percentPos=strpos($query,'%',0);
		$chkQuery=strtolower($query);
		if (!$dontConvert){
			$tstPos=strpos($query,'%',0);
			if ($tstPos>0){
				$query=$base->UtlObj->returnFormattedString($query,&$base);
			}
			$query=str_replace('~','%',$query);
		} // end if !dontconvert
		$base->DebugObj->showQuery("$query",$base,$prio);
		$returnResult=pg_query($dbConn,"$query");
		if ($returnResult === false && $prio>=0){
			$base->ErrorObj->saveError('sqlerror',$query,&$base);
			$base->DebugObj->placeCheck("!!!DbObj.queryTableAnyDb: SQL Error: $query, dontconvert: $dontConvert, querytype: $queryType"); //xx (c)
			$base->DebugObj->displayStack();
			exit('early termination');
		}
		$base->DebugObj->printDebug("-rtn:DbObj:queryTable",-2); //xx (f)
		return $returnResult;
	}
	//======================================
	function updateJobOverride($base){
		$base->DebugObj->printDebug("DbObj:updateJobOverride('base')",0);
		$overrideName=$base->paramsAry['overridename'];
		$overrideValue=$base->paramsAry['overridevalue'];
		$query="update systemprofile set overridename='$overrideName',overridevalue='$overrideValue' where domainname='home'";
		$returnResult=$this->queryTable($query,'update',&$base);
		$base->systemProfileAry['overridename']=$overrideName;
		$base->systemProfileAry['overridevalue']=$overrideValue;
		$base->paramsAry[$overrideName]=$overrideValue;
		$base->DebugObj->printDebug("-rtn:DbObj:updateJobOverride",0); //xx (f)
	}
	//======================================
	function readFromDb($dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:readFromDb($dbControlsAry,'base')",0);
		$useSelect=$dbControlsAry['useselect'];
		$this->getDbTableInfo(&$dbControlsAry,&$base);
		if (!$useSelect) {
			$noRows=count($dbControlsAry['datarowsary']);
			for ($ctr=0;$ctr<$noRows;$ctr++){
				$rowAry=$this->readDbRow($ctr,$dbControlsAry,&$base);
				$dbControlsAry['datarowsary'][$ctr]=$rowAry;
			}
		}
		else {
			$rowsAry=$this->readDbRows($dbControlsAry,&$base);
			$dbControlsAry['datarowsary']=$rowsAry;
		}
		$base->DebugObj->printDebug("-rtn:DbObj:readFromDb",0); //xx (f)
		return $dbControlsAry['datarowsary'];
	}
	//======================================
	function writeToDbRemote($useToDbConn,$dbControlsAry,$base){
		$this->setRemoteDb($useToDbConn,&$base);
		$this->setUseOtherDbNoReset(&$base);
		$successBool=$this->writeToDb($dbControlsAry,&$base);
		$this->unsetUseOtherDb(&$base);
		return $successBool;
	}
	//======================================
	function writeToDb($dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:writeToDb($dbControlsAry,'base')",0);
		$base->FileObj->writeLog('debug',"enter writetodb",&$base);
		$base->UtlObj->appendValue('debug',"call writetodb<br>",&$base);
		$writeRowsAry=$dbControlsAry['writerowsary'];
		//xxxf
		foreach ($writeRowsAry[0] as $one=>$two){$base->FileObj->writeLog('debug2','name: '.$one.', value: '.$two,&$base);}
		$dbTableName=$dbControlsAry['dbtablename'];
		$this->getDbTableInfo(&$dbControlsAry,&$base);
		//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx
		$keyName=$dbControlsAry['keyname'];
		$updatedRowsAry=array();
		$selectorNameAry=$dbControlsAry['selectornameary'];
		$noRows=count($writeRowsAry);
		$base->FileObj->writeLog('debug',"no rows: $noRows",&$base);
		$allSuccessfulUpdate=true;
		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
			//$base->FileObj->writeLog('debug',"rowctr: $rowCtr",&$base);//xxxf
			//echo "rownctr: $rowCtr,";//xxxd
			$currentRowAry=$writeRowsAry[$rowCtr];
			$overrideCommand=$currentRowAry['overridecommand'];
			if ($overrideCommand == ''){
				//---setup selector
				foreach ($selectorNameAry as $ctr=>$selName){
					$newSelValue=$currentRowAry[$selName];
					$dbControlsAry['selectorary'][$selName]=$newSelValue;
				} // end foreach
				//--- update/insert
				//$base->FileObj->writeLog('debug',"do update/insert",&$base);
				if ($currentRowAry[$keyName] != "" && $currentRowAry[$keyName] != 'NULL'){
					$successfulUpdate=$this->updateDbRow($currentRowAry,$dbControlsAry,&$base);
					//echo "$successfulUpdate,";
					if (!$successfulUpdate){
						$allSuccessfulUpdate=false;
						$base->FileObj->writeLog('db',"update error dbtablename: $dbTableName, keyname: $keyName($currentRowAry[$keyName])",&$base);
					}
				} // end if keyname!=""
				else {
					$successfulUpdate=$this->insertDbRow($currentRowAry,$dbControlsAry,&$base);
					if (!$successfulUpdate){
						$allSuccessfulUpdate=false;
						$errorStrg=$base->ErrorObj->retrieveAllErrors(&$base);
						$base->FileObj->writeLog('debug',"dbobj.insertrow: fail errorstrg: $errorStrg ",&$base);
					}
				} // end else keyname == ""
			} // end if overridecommand == ""
			//--- delete/do nothing because of override
			else {
				if ($overrideCommand == 'delete'){
					$keyName=$dbControlsAry['keyname'];
					$dbTableName=$dbControlsAry['dbtablename'];
					$keyValue=$currentRowAry[$keyName];
					$query="delete from $dbTableName where $keyName=$keyValue";
					if ($keyValue != ''){
						$result=$base->DbObj->queryTable($query,'delete',&$base);
					}
					//delete it
				} // end delete it
				else {
					//do nothing;
				} // end else do nothing
			} // end else overridecommand != ""
		}
		$base->DebugObj->printDebug("-rtn:writeToDb",0); //xx (f)
		return $allSuccessfulUpdate;
	}
	//======================================
	function updateToDb($dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:updateToDb($dbControlsAry,'base')",0);
		$foundError=false;
		$writeRowsAry=$dbControlsAry['writerowsary'];
		$dbTableName=$dbControlsAry['dbtablename'];
		$this->getDbTableInfo(&$dbControlsAry,&$base);
		$selectorNameAry=$dbControlsAry['selectornameary'];
		//---------------------------> setup selectorAry using selectorNameAry
		$noRows=count($writeRowsAry);
		for ($ctr=0;$ctr<$noRows;$ctr++){
			$currentRowAry=$writeRowsAry[$ctr];
			if ($selectorNameAry != ""){
				$selectorAry=array();
				foreach ($selectorNameAry as $ctr=>$selectorName){
					$selectorValue=$currentRowAry[$selectorName];
					if ($selectorValue != ""){
						$selectorAry[$selectorName]=$selectorValue;
					}
					else {
						$base->errorProfileAry['othererrorary'][]="selector $selectorName is null";
						$foundError=true;
					}
				}
				$dbControlsAry['selectorary']=$selectorAry;
			}
			if (!$foundError){
				$successfulUpdate=$this->updateDbRow($currentRowAry,$dbControlsAry,&$base);
				if (!($successfulUpdate)){$foundError=true;}
			}
		}
		if ($foundError){$allSuccessfulUpdate=false;}
		else {$allSuccessfulUpdate=true;}
		$base->DebugObj->printDebug("-rtn:DbObj:updateToDb",0); //xx (f)
		return $allSuccessfulUpdate;
	}
	//======================================
	function insertToDb($dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:insertToDb($dbControlsAry,'base')",0);
		$returnStatus=false;
		$foundError=false;
		$writeRowsAry=$dbControlsAry['writerowsary'];
		$dbTableName=$dbControlsAry['dbtablename'];
		$this->getDbTableInfo(&$dbControlsAry,&$base);
		$selectorNameAry=$dbControlsAry['selectornameary'];
		$noRows=count($writeRowsAry);
		$insertedAllOk=true;
		//echo "allok: $insertedAllOk<br>";//xxx
		for ($ctr=0;$ctr<$noRows;$ctr++){
			$currentRowAry=$writeRowsAry[$ctr];
			$overrideCommand=$currentRowAry['overridecommand'];
			if ($overrideCommand == NULL){
				if ($selectorNameAry != ""){
					$selectorAry=array();
					foreach ($selectorNameAry as $ctr=>$selectorName){
						$selectorValue=$currentRowAry[$selectorName];
						if ($selectorValue != ""){
							$selectorAry[$selectorName]=$selectorValue;
						}
					}
					$dbControlsAry['selectorary']=$selectorAry;
				}
				$insertedOk=$this->insertDbRow($currentRowAry,$dbControlsAry,&$base);
				//echo "insertedok: $insertedOk<br>";//xxx
				if (!($insertedOk)){$insertedAllOk=false;}
			}
		}
		$base->DebugObj->printDebug("-rtn:DbObj:insertToDb",0); //xx (f)
		return $insertedAllOk;
	}
	//======================================= soon to be deprecated
	function getDbTableMetaInfo($dbControlAry,$base){
		$base->DebugObj->printDebug("DbObj:getDbTableMetaInfo($dbControlAry,'base')",0);
		if (!(array_key_exists('dbtablemetaary',$dbControlAry))){
			$dbTableName=$dbControlAry['dbtablename'];
			$returnAry=array();
			$query="select * from dbtablemetaprofileview where dbtablemetaname='$dbTableName'";
			//echo "query: $query";//xxx
			$result=$this->queryTable($query,'read',&$base);
			if ($result === false) {
				$base->DebugObj->placeCheck("Error: The table $dbTableName is not defined in the dbtablemetaprofile table!!!"); //xx (c)
				$base->DebugObj->displayStack(); //xx (c)
			} // end if false
			$passAry=array('delimit1'=>'dbcolumnname');
			$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($returnAry,1,'ret');//xxx
			$noRetrieved=count($returnAry);
			if ($noRetrieved > 0){
				$dbTableAccessAry=$this->getSelectorNameAry($returnAry,&$base);
				$dbControlAry['selectornameary']=$dbTableAccessAry['selectornameary'];
				$dbControlAry['keyname']=$dbTableAccessAry['keyname'];
				$dbControlAry['parentselectorname']=$dbTableAccessAry['parentselectorname'];
				$dbControlAry['foreignkeyary']=$dbTableAccessAry['foreignkeyary'];
				$dbControlAry['dbtablemetaary']=$returnAry;
			} // end if noretrieve >
			else {
				$dbControlAry['dbtablemetaary']=array();
				$base->DebugObj->placeCheck("1: Error! Table '$dbTableName' is not defined!!!"); //xx (c)
				$base->DebugObj->displayStack();
			}
		} // end if !
		$base->DebugObj->printDebug("-rtn:getDbTableMetaInfo",0); //xx (h)
	}
	//=======================================
	function getDbTableInfo($dbControlAry,$base){
		$base->DebugObj->printDebug("DbObj:getDbTableInfo($dbControlAry,'base')",0);
		if (!(array_key_exists('dbtablemetaary',$dbControlAry))){
			$dbTableName=$dbControlAry['dbtablename'];
			$returnAry=array();
			$query="select * from dbcolumnprofileview where dbtablename='$dbTableName'";
			//echo "query: $query";//xxx
			$result=$this->queryTable($query,'read',&$base);
			if ($result === false) {
				$base->DebugObj->placeCheck("Error: The table $dbTableName is not defined in the dbtablemetaprofile table!!!"); //xx (c)
				$base->DebugObj->displayStack(); //xx (c)
			} // end if false
			$passAry=array('delimit1'=>'dbcolumnname');
			$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($returnAry,1,'ret');//xxx
			$noRetrieved=count($returnAry);
			if ($noRetrieved > 0){
				$dbTableAccessAry=$this->getDbTableKeyFields($returnAry,&$base);
				$dbControlAry['selectornameary']=$dbTableAccessAry['selectornameary'];
				$dbControlAry['keyname']=$dbTableAccessAry['keyname'];
				$dbControlAry['parentselectorname']=$dbTableAccessAry['parentselectorname'];
				$dbControlAry['foreignkeyary']=$dbTableAccessAry['foreignkeyary'];
				$dbControlAry['dbtablemetaary']=$returnAry;
			} // end if noretrieve >
			else {
				$dbControlAry['dbtablemetaary']=array();
				$base->DebugObj->placeCheck("1: Error! Table '$dbTableName' is not defined!!!"); //xx (c)
				$base->DebugObj->displayStack();
			}
		} // end if !
		$base->DebugObj->printDebug("-rtn:getDbTableInfo",0); //xx (h)
	}
	//===========================================
	function getDbTableKeyFields($dbColumnsAry,$base){
		$base->DebugObj->printDebug("DbObj:getSelectorNameAry($dbTableMetaAry,'base')",0);
		$keyName="none";
		$possibleKeyName="none";
		$parentSelectorName="none";
		$foreignKeyAry=array();
		$selectorNameAry=array();
		$foundSelector=false;
		//- begin of loop
		foreach ($dbColumnsAry as $key=>$valueAry){
			//- check for selector
			$chkIt=$valueAry['dbcolumnselector'];
			$chkIt=$base->DbObj->returnBoolByType($chkIt,'bool');
			if ($chkIt===true){
				$selectorNameAry[]=$key;
				$foundSelector=true;
				$parentChk=$base->DbObj->returnBoolByType($valueAry['dbcolumnparentselector']);
				if ($parentChk === true){$parentSelectorName=$key;}
			} // end if
			//- check for foreign key
			$chkIt=$valueAry['dbcolumnforeignfield'];
			$foreignChk=$base->DbObj->returnBoolByType($valueAry['dbcolumnforeignfield']);
			if ($foreignChk===true){
				$foreignKeyAry[]=$key;
			} // end if
			//- check for possible key (ends in id)
			$keyLength=strlen($key);
			$startPos=$keyLength-2;
			$chkIt=substr($key,$startPos,2);
			//- get first one with id only
			if ($chkIt == 'id' && $possibleKeyName=="none"){$possibleKeyName=$key;}
			//- check for key
			$chkIt=$valueAry['dbcolumnkey'];
			$chkIt=$base->DbObj->returnBoolByType($chkIt,'bool');
			if ($chkIt===true){$keyName=$key;}
		} // end foreach loop
		if ($keyName == 'none'){
			if ($possibleKeyName != 'none'){$keyName=$possibleKeyName;}
		}
		$dbTableAccessAry=array('selectornameary'=>$selectorNameAry,'keyname'=>$keyName,'parentselectorname'=>$parentSelectorName,'foreignkeyary'=>$foreignKeyAry);
		$base->DebugObj->printDebug("-rtn:getSelectorNameAry",0); //xx (f)
		return $dbTableAccessAry;
	}
	//=========================================== soon to be deprecated
	function getSelectorNameAry($dbTableMetaAry,$base){
		$base->DebugObj->printDebug("DbObj:getSelectorNameAry($dbTableMetaAry,'base')",0);
		$keyName="none";
		$possibleKeyName="none";
		$parentSelectorName="none";
		$foreignKeyAry=array();
		$selectorNameAry=array();
		$foundSelector=false;
		//- begin of loop
		foreach ($dbTableMetaAry as $key=>$valueAry){
			//- check for selector
			$chkIt=$valueAry['dbcolumnselector'];
			$chkIt=$base->DbObj->returnBoolByType($chkIt,'bool');
			if ($chkIt===true){
				$selectorNameAry[]=$key;
				$foundSelector=true;
				$parentChk=$base->DbObj->returnBoolByType($valueAry['dbcolumnparentselector']);
				if ($parentChk === true){$parentSelectorName=$key;}
			} // end if
			//- check for foreign key
			$chkIt=$valueAry['dbcolumnforeignfield'];
			$foreignChk=$base->DbObj->returnBoolByType($valueAry['dbcolumnforeignfield']);
			if ($foreignChk===true){
				$foreignKeyAry[]=$key;
			} // end if
			//- check for possible key (ends in id)
			$keyLength=strlen($key);
			$startPos=$keyLength-2;
			$chkIt=substr($key,$startPos,2);
			//- get first one with id only
			if ($chkIt == 'id' && $possibleKeyName=="none"){$possibleKeyName=$key;}
			//- check for key
			$chkIt=$valueAry['dbcolumnkey'];
			$chkIt=$base->DbObj->returnBoolByType($chkIt,'bool');
			if ($chkIt===true){$keyName=$key;}
		} // end foreach loop
		if ($keyName == 'none'){
			if ($possibleKeyName != 'none'){$keyName=$possibleKeyName;}
		}
		$dbTableAccessAry=array('selectornameary'=>$selectorNameAry,'keyname'=>$keyName,'parentselectorname'=>$parentSelectorName,'foreignkeyary'=>$foreignKeyAry);
		$base->DebugObj->printDebug("-rtn:getSelectorNameAry",0); //xx (f)
		return $dbTableAccessAry;
	}
	//===========================================
	function readDbRow($currentRowNo,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:readDbRow($currentRowAry,$dbControlsAry,'base')",0); //xx (h)
		$result=$base->DbObj->getDbStuff($currentRowNo,$dbControlsAry,&$base);
		$returnAry=$base->UtlObj->tableRowToHashAry($result,$dbControlsAry);
		$base->DebugObj->printDebug("-rtn:readDbRow",0); //xx (f)
		return $returnAry;
	}
	//===========================================
	function readDbRows($dbControlsAry,$base){
		$base->DebugObj->printDebug("readDbRows($dbControlsAry,'base')",0); //xx (h)
		$result=$base->DbObj->getDbStuff(0,$dbControlsAry,&$base);
		$passAry=array('dbcontrolsary'=>$dbControlsAry);
		$returnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$base->DebugObj->printDebug("-rtn:readDbRows",0); //xx (f)
		return $returnAry;
	}
	//===========================================
	function getDbStuff($rowCtr,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:getDbStuff($rowCtr,$dbControlsAry,'base')",0);
		$useSelect=$dbControlsAry['useselect'];
		$dbTableName=$dbControlsAry['dbtablename'];
		$dbTableNameView=$dbTableName."view";
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$query="select * from $dbTableNameView where ";
		if (!$useSelect){
			$keyName=$dbControlsAry['keyname'];
			$keyValue=$dbControlsAry['datarowsary'][$rowCtr][$keyName];
			$query .= "$keyName=$keyValue";
		}
		else {
			$selectorAry=$dbControlsAry['selectorary'];
			$noSelectors=count($selectorAry);
			$andSepar=" ";
			foreach ($selectorAry as $selectorName=>$selectorValue){
				$selectorType=$dbTableMetaAry[$selectorName]['dbcolumntype'];
				$selectorValue_sql=$base -> UtlObj -> returnFormattedData( $selectorValue, $selectorType, 'sql');
				if ($selectorValue == 'initialized'){
					$base->DebugObj->placeCheck("Error at approx 1494 selector value is initialized"); //xx (c)
				}
				$insertQuery .= "$andSepar$selectorName=$selectorValue_sql";
				$andSepar=" and ";
			}
			$query.=$insertQuery;
		}
		//echo "query: $query<br>";//xxxd
		$result=$this->queryTable($query,'read',&$base);
		//echo "got data <br>";//xxxd
		$base->DebugObj->printDebug("-rtn:getDbStuff",0); //xx (f)
		return $result;
	}
	//========================================
	function updateDbRow($currentRowAry,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:updateDbRow($currentRowAry,$oldRowAry,$controlsDb,'base')",0);
		$base->UtlObj->appendValue('debug',"update db row<br>",&$base);
		$selectorNameAry=$dbControlsAry['selectornameary'];
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$dbTableName=$dbControlsAry['dbtablename'];
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$currentRowAry[$keyName];
		// - check if it is ok to update
		$okToUpdate=$this->checkOkToUpdate($currentRowAry,$dbControlsAry,&$base);
		//echo "oktoupdate: $okToUpdate";//xxx
		// - validate the data
		if ($okToUpdate) {
			$itAllValidates=$base->DbObj->validateData($currentRowAry,$dbControlsAry,&$base);
		}
		else {
			$base->ErrorObj->saveError('arowtoupdate','it is not ok to update some rows',&$base);
		}
		//$base->DebugObj->printDebug($currentRowAry,1,'xxxf');exit();
		// - build the sql statement
		//echo "oktoupdate: $okToUpdate, itallvalidates: $itAllValidates<br>";//xxxf
		//exit();//xxxf
		//echo "xxxf20";
		//echo "oktoupdate: $okToUpdate, itallvalidates: $itAllValidates\n";//xxxf
		if ($okToUpdate && $itAllValidates) {
			//echo "do itxxxf";
			$query="update $dbTableName set ";
			$start=true;
			$colValues="";
			$foundDiff=false;
			foreach ($dbTableMetaAry as $colName=>$colInfo){
				//echo "colname: $colName<br>";//xxx
				if (array_key_exists($colName,$currentRowAry)){
					//echo "arraykeyexists";
					if ($colName != $keyName){
						//echo "it is not keyname";
						$foreignField=$dbTableMetaAry[$colName]['dbcolumnforeignfield'];
						$foreignField=$this->returnBoolByType($foreignField,'bool');
						$foreignKey=$dbTableMetaAry[$colName]['dbcolumnforeignkey'];
						$foreignKey=$this->returnBoolByType($foreignKey,'bool');
						$mainTable=$dbTableMetaAry[$colName]['dbcolumnmaintable'];
						if ($foreignKey && $mainTable != NULL){$dontDoIt=true;}
						else {$dontDoIt=false;}
						//if ($colName == 'jobprofileid'){
						//$base->DebugObj->printDebug($dbTableMetaAry[$colName],1,'dbtablemeta');//xxx
						//}
						if ($foreignField === false && $dontDoIt === false){
							//xxxf22 new by jeff 10/3/11
							$base->FileObj->writeLog('debugx',"colname: $colName",&$base);//xxxf
							$columnEvent=$dbTableMetaAry[$colName]['dbcolumnevent'];
							$colValue_raw=$currentRowAry[$colName];
							$columnDefault=$dbTableMetaAry[$colName]['dbcolumndefault'];
							//why can't I just use NULL and not have zero have problems
							$colValueLen=strlen($colValue_raw);
							if ($colValueLen == 0){
								$colValue_raw=$columnDefault;
							}
							if ($columnEvent != null){
								$colValue=$this->doEvent($colValue_raw,$columnEvent,$currentRowAry,&$base);
							}
							else {
								$colValue=$colValue_raw;
							}
							//xxxf22 end changes
							//$colType=$dbTableMetaAry[$colName]['dbtablemetatype']; //xxx - restore if errors
							$colType=$dbTableMetaAry[$colName]['dbcolumntype'];
							$colValue_sqlformat=$base->UtlObj->returnFormattedData($colValue,$colType,'sql');
							//if ($colName=='thingstodopriority'){
								//echo "type: $colType, valueraw: $colValue_raw value: $colValue, valuesql: $colValue_sqlformat";exit();//xxxf
							//}
							if ($start){$cma="";}
							else {$cma=",";}
							$query.= "$cma$colName=$colValue_sqlformat";
							$start=false;
							$foundDiff=true;
						} // end if foreignkey not true
					} // end if colvalue not keyname
				} // end if key exists
				else {
					//echo "colname: ".'x'.$colName.'x';
					//$base->DebugObj->printDebug($currentRowAry,1,'cra');//xxx
				}
			} // end for each colname colinfo
			$query .= " where $keyName = $keyValue";
			$base->UtlObj->appendValue('debug',"call to querytable with $query<br>",&$base);
			//echo "query: $query";exit();//xxxf
			$result=$this->queryTable($query,'updatenoconversion',&$base);
			$base->UtlObj->appendValue('debug',"return from querytable<br>",&$base);
		} // end if oktoupdate and all validates
		if ($okToUpdate && $itAllValidates){$itWasUpdated=true;}
		else {$itWasUpdated=false;}
		$base->DebugObj->printDebug("-rtn:updateDbRow",0); //xx (f)
		return $itWasUpdated;
	}
	//==============================================
	function doEvent($colValue_raw,$columnEvent,$currentRowAry,$base){
		$colName=$currentRowAry['dbcolumnname'];
		$colEventAry=explode(',',$columnEvent);
		$colEventType=$colEventAry[0];
		$colEventCheckCol=$colEventAry[1];
		$colEventQuickAction=$colEventAry[1];
		$colEventCheckType=$colEventAry[2];
		$colEventCheckValue=$colEventAry[3];
		$colEventAction=$colEventAry[4];
		$colEventCheckColValue=$currentRowAry[$colEventCheckCol];
		$base->FileObj->writeLog('debugx',$columnEvent,&$base);//xxxf
		$doit=false;
		switch ($colEventType){
			case 'ifnulland':
//---
				if ($colValue_raw == null){
					switch ($colEventCheckType){
						case '=':
							if ($colEventCheckColValue == $colEventCheckValue){$doit=true;}
							break;
						case '!=':
							if ($colEventCheckColValue != $colEventCheckValue){$doit=true;}
							break;
						default:
							$base->FileObj->writeLog('error',"dbobj.doEvent) invalid event string: $colEvent for column $colName",&$base);
					}
					if ($doit){
						switch ($colEventAction){
							case 'today':
								$colValue=$base->UtlObj->getTodaysDate(&$base);
								$base->FileObj->writeLog('debugx',"!!!coleventaction: $colEventAction, colvalue: $colvalue",&$base);
								break;
							default:
							$base->FileObj->writeLog('error',"dbobj.doEvent) invalid event string: $colEvent for column $colName",&$base);	
							$colValue=$colValue_raw;
						}
					}
				}
				else {
					$colValue=$colValue_raw;
				}
				$base->FileObj->writeLog('debugx',"ifnulland) colvalue: $colValue",&$base);
				break;
			case 'ifnull':
//---
				if ($colValue_raw == null){
					switch ($colEventQuickAction){
						case 'today':
							$colValue=$base->UtlObj->getTodaysDate(&$base);
							break;
						default:
							$colValue=$colValue_raw;
							$base->FileObj->writeLog('error',"dbobj.doEvent) invalid event string: $colEvent for column $colName",&$base);
					}
				}
				else {
					$colValue=$colValue_raw;
				}
				break;
			default:
				$base->FileObj->writeLog('error',"dbobj.doEvent) invalid event string: $colEvent for column $colName",&$base);
		}
		$base->FileObj->writeLog('debug1',"doit: $doit, type: $colEventType, coleventaction: $colEventAction, colvalueraw: $colValue_raw, colvalue: $colValue",&$base);
		return $colValue;
	}
	//=======================================
	function insertDbRow($currentRowAry,$dbControlsAry,$base){
		//$base->DebugObj->printDebug($currentRowAry,1,'xxxf0');
		$base->DebugObj->printDebug("DbObj:insertDbRow($currentRowAry,$dbControlsAry,'base')",0);
		$selectorAry=$dbControlsAry['selectorary'];
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$dbTableName=$dbControlsAry['dbtablename'];
		$keyName=$dbControlsAry['keyname'];
		$noSelectors=count($selectorAry);
		if ($noSelectors>0){
			$okToInsert=$base->DbObj->checkOkToInsert($currentRowAry,$dbControlsAry,&$base);
		}
		else {$okToInsert=true;}
		//echo "oktoinsert: $okToInsert<br>";//xxx
		if ($okToInsert) {
			$itAllValidates=$base->DbObj->validateData($currentRowAry,$dbControlsAry,&$base);
		}
		else {
			$base->ErrorObj->saveError('rowtoinsert','It is not ok to insert some rows',&$base);
		}
		//echo "oktoinsert: $okToInsert, itallvalidates: $itAllValidates<br>";//xxxf
		//$base->DebugObj->printDebug($base->errorProfileAry,1,'xxxd');
		$base->FileObj->writeLog('debug',"dbtablename: $dbTableName, oktoinsert: $okToInsert, itallvalidates: $itAllValidates",&$base);//xxxf
		if ($okToInsert && $itAllValidates) {
			$base->FileObj->writeLog('debug',"going to do it",&$base);//xxxf
			$tempKeyId=$currentRowAry['tempkeyid'];
			$query="insert into $dbTableName (";
			$start=true;
			$colValues="";
			foreach ($dbTableMetaAry as $colName=>$colInfo){
				//$colValue=$currentRowAry[$colName];
				//$base->FileObj->writeLog('debugx',"colname: $colName",&$base);//xxxf
				$columnEvent=$dbTableMetaAry[$colName]['dbcolumnevent'];
				$colValue_raw=$currentRowAry[$colName];
				$columnDefault=$dbTableMetaAry[$colName]['dbcolumndefault'];
				if ($colValue_raw == NULL){$colValue_raw=$columnDefault;}
				if ($columnEvent != null){
					$colValue=$this->doEvent($colValue_raw,$columnEvent,$currentRowAry,&$base);
				}
				else {
					$colValue=$colValue_raw;
				}
				//if ($colName=='picturetype'){
					//echo "error|picturetype colvalue: $colValue, columndefault: $columnDefault";exit();//xxxf
				//}
				//xxxf22
				$colType=$dbTableMetaAry[$colName]['dbcolumntype'];
				$colValue_sqlformat=$base->UtlObj->returnFormattedData($colValue,$colType,'sql');
				//$base->FileObj->writeLog('debug',"name: $colName, colvalueraw: $colValue_raw, colvalue: $colValue, colvaluesql: $colValue_sqlformat",&$base);//xxxf
				$foreignField=$dbTableMetaAry[$colName]['dbcolumnforeignfield'];
				$foreignField=$this->returnBoolByType($foreignField,'bool');
				$foreignKey=$dbTableMetaAry[$colName]['dbcolumnforeignkey'];
				$foreignKey=$this->returnBoolByType($foreignKey,'bool');
				$mainTable=$dbTableMetaAry[$colName]['dbcolumnmaintable'];
				if ($foreignKey && $mainTable != NULL){$dontDoIt=true;}
				else {$dontDoIt=false;}
				if ($foreignField === true){$dontDoIt=true;}
				if ($colName == $keyName){$dontDoIt=true;}
				//echo "$colName, $colValue, $colValue_sqlformat, $dontDoIt\n";//xxxf
				//if ($colValue != NULL && $dontDoIt === false){
				if ($dontDoIt === false){
					if (!$start){$cma=",";}
					else {$cma="";}
					$start=false;
					$query .= "$cma$colName";
					$colValues.="$cma$colValue_sqlformat";
				} //end if != ''
			} //end foreach
			$query .= ") values ($colValues)";

			//$base->FileObj->writeLog('debug',"query: $query",&$base);//xxxf
			$result=$this->queryTable($query,'updatenoconversion',&$base);
			if ($tempKeyId != null){
				$query="select max($keyName) from $dbTableName";
				$keyResult=$this->queryTable($query,'read',&$base);
				$passAry=array();
				$keyAry=$base->UtlObj->tableRowToHashAry($keyResult,$dbControlsAry);
				$realKeyId=$keyAry['max'];
				$base->ErrorObj->saveKeyConv($tempKeyId,$realKeyId,&$base);
			}
			$rtnIdFlg=$this->getRtnIdFlg();
			if ($rtnIdFlg){
				$query="select max($keyName) from $dbTableName";
				$keyResult=$this->queryTable($query,'read',&$base);
				$passAry=array();
				$keyAry=$base->UtlObj->tableRowToHashAry($keyResult,$dbControlsAry);
				$realKeyId=$keyAry['max'];
				$base->ErrorObj->saveError('newkeyid',$realKeyId);
			}			

		} //end if oktoinsert
		else {
			//if ($okToInsert) {echo "there are validation errors!!!";}
			//else {echo "it is not ok to insert to $dbTableName!!!";}
			//$base->DebugObj->displayStack();//xxx
			$okToInsert=false;
			$base->FileObj->writeLog('debug',"did not do insert",&$base);//xxxf
		}
		$base->DebugObj->printDebug("-rtn:insertDbRow",0); //xx (f)
		return $okToInsert;
	}
	function setRtnIdFlg(){
		$this->rtnIdFlg=true;
	}
	function resetRtnIdFlg(){
		$this->rtnIdFlg=false;
	}
	function getRtnIdFlg(){
		return $this->rtnIdFlg;
	}
	//======================================= deprecated but used
	function returnSglQtByType($colType){
		switch ($colType){
			case 'varchar':
				$sglQt="'";
				break;
			case 'text':
				$sglQt="'";
				break;
			case 'date':
				$sglQt="'";
				break;
			default:
				$sglQt="";
		}
		return $sglQt;
	}
	//=======================================
	//-postgres stores true/false as 't'/'f'
	//run this on any boolean pulled from postgres
	//
	function returnBoolByType($colValue,$colType='bool'){
		if (substr($colType,0,4) == 'bool'){
			switch ($colValue){
				case 't':
					$colValue=true;
					break;
				case 1;
				$colValue=true;
				break;
				case 'f':
					$colValue=false;
					break;
				case '':
					$colValue=false;
					break;
				case 0:
					$colValue=false;
					break;
				default:
			}
		}
		return $colValue;
	}
	//=======================================
	function returnBoolByTypeAsc($colValue,$colType){
		if (substr($colType,0,4) == 'bool'){
			if ($colValue === true || $colValue == 't' || $colValue == 1){$colValue='true';}
			if ($colValue === false || $colValue == 'f' || $colValue == 0 || $colValue == ''){$colValue='false';}
		}
		return $colValue;
	}
	//=======================================
	function checkOkToUpdate($currentRowAry,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:checkOkToUpdate($currentRowAry,$dbControlsAry,'base')",0); //xx (h)
		//- assume that all selectors are varchar <- !!! not the case
		//-        that keyname is numeric always
		// - need to fix this routine - if no update of any selector field, then no need to check
		$selectorAry=$dbControlsAry['selectorary'];
		$checkError=false;
		$needToCheck=false;
		$noSelectors=count($selectorAry);
		if ($noSelectors>0){
			foreach ($selectorAry as $selectorName=>$selectorValue){
				if ($selectorValue == 'error'){$checkError=true;}
				$selectorValue_tobeupdated=$currentRowAry[$selectorName];
				if ($selectorValue_tobeupdated != NULL){$needToCheck=true;}
			}
		}
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$currentRowAry[$keyName];
		if ($keyValue == NULL){$checkError=true;}
		$tableName=$dbControlsAry['dbtablename'];
		if ($tableName == NULL){$checkError=true;}
		$okToUpdate=false;
		$countSelector=count($selectorAry);
		if ($needToCheck){
			if (!$checkError){
				$tableNameView=$tableName.'view';
				$query="select * from $tableNameView where $keyName != $keyValue ";
				$errorMsgRtn = NULL;
				foreach ($selectorAry as $selName=>$selValue){
					$query .= " and $selName='$selValue'";
					if ($errorMsgRtn != ""){$errorMsgRtn .= ", ";}
					$errorMsgRtn .= "$selName is $selValue";
				}
				$result=$base->DbObj->queryTable($query,'checkon',&$base);
				$checkAry=$base->UtlObj->tableRowToHashAry($result);
				if (count($checkAry)==0){$okToUpdate=true;}
				else {
					//xxxd - need to change
					$errorStrg='update failure: '.$keyName.'('.$keyValue.') is not on file! ';
					$theComma=null;
					foreach ($selectorAry as $selName=>$selValue){
						$errorStrg.=$selName.'('.$selValue.')'.$theComma;
						$theComma=', ';
					}
					$base->ErrorObj->saveError('updateerror',$errorStrg,&$base);
					$base->DebugObj->printDebug("query: $query",0); //xx (q)
					$base->DebugObj->printDebug($checkAry,0,"checkAry: should be empty"); //xx (v)
				}
			} // end if !checkerror
			else {
				$base->DebugObj->placeCheck("Fatal Table Def Error keyname: $keyName, keyvalue: $keyValue, tableName: $tableName, number of selectors: $noSelectors"); //xx (c)
			} // end else if !checkerror
		} // end if need to check
		else {$okToUpdate=true;}
		$base->DebugObj->printDebug("-rtn:checkOkToUpdate",0); //xx (f)
		return $okToUpdate;
	}
	//=======================================
	//- assume that all selectors are varchar <- !!! not necessarily the case
	//- assume that keyname is numeric
	function checkOkToInsert($currentRowAry,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:checkOkToInsert($currentRowAry,$dbControlsAry,'base')",0); //xx (h)
		$selectorAry=$dbControlsAry['selectorary'];
		//$base->DebugObj->printDebug($selectorAry,1,'sel');//xxx
		$tableName=$dbControlsAry['dbtablename'];
		$tableNameView=$tableName."view";
		$query="select * from $tableName where ";
		$queryView="select * from $tableNameView where ";
		$first=true;
		$needView=false;
		$selectorCnt=count($selectorAry);
		//echo "yyyselectorcnt: $selectorCnt";//xxx
		if ($selectorCnt>0){
			foreach ($selectorAry as $selName=>$selValue){
				if ($selName == 'jobname'){$needView=true;}
				if ($selName == 'formname'){$needView=true;}
				if ($first){$andInsert=" ";}
				else {$andInsert=" and ";}
				$first=false;
				$query .= "$andInsert$selName='$selValue'";
				$queryView .= "$andInsert$selName='$selValue'";
			}
			if ($needView == true){$useQuery=$queryView;}
			else {$useQuery=$query;}
			//echo "usequery: $useQuery<br>";//xxx
			$result=$base->DbObj->queryTable($useQuery,'checkon',&$base);
			$checkAry=$base->UtlObj->tableRowToHashAry($result);
			//$base->DebugObj->printDebug($checkAry,1,'ckary');//xxx
			if (count($checkAry)==0){$okToInsert=true;}
			else {
				$okToInsert=false;
				$errorStrg='insert conflict: ';
				$theComma=null;
				foreach ($selectorAry as $selName=>$selValue){
					$errorStrg.=$selName.'('.$selValue.')'.$theComma;
					$theComma=', ';
				}
				$base->ErrorObj->saveError('inserterror',$errorStrg,&$base);
			}
		}
		else {
			// always ok to insert if no selectors
			$okToInsert=true;
			//echo "xxx";
		}
		$base->DebugObj->printDebug("-rtn:checkOkToInsert",0); //xx (f)
		return $okToInsert;
	}
	//=======================================
	function validateData($currentRowAry,$dbControlsAry,$base){
		$base->DebugObj->printDebug("DbObj:validateData($currentRowAry,$dbControlsAry,'base')",0); // (h)
		//$base->DebugObj->printDebug($currentRowAry,1,'xxxf');
		$allValidated=true;
		//$this->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableName=$dbControlsAry['dbtablename'];
		$base->FileObj->writeLog('debug2',"validate for dbtablename: $dbTableName",&$base);
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		//$debugStrg='';
		foreach ($dbTableMetaAry as $colName=>$colProfileAry){
			//- get column values
			$colType=$colProfileAry['dbcolumntype'];
			$colRegEx=$colProfileAry['validateregex'];
			$colErrMsg=$colProfileAry['validateerrormsg'];
			$colNotNull_raw=$colProfileAry['dbcolumnnotnull'];
			$colNotNull=$base->UtlObj->returnFormattedData($colNotNull_raw,'boolean','internal');
			$colDefaultData=$colProfileAry['dbcolumndefault'];
			//$colEvent=$colProfileAry['dbcolumnevent'];
			$colData=$currentRowAry[$colName];
			if ($colData == NULL){$colData=$colDefaultData;}
			//- validate column
			if ($colRegEx == "" && $colType == 'numeric'){
				$colRegEx='/^[0-9]*$/';
				$colErrMsg="Must be an integer";
				$base->FileObj->writeLog('db',"error: $colName($colData), $colErrMsg",&$base);
			}
			if ($colNotNull && $colData == NULL){
				$allValidated=false;
				//$base->errorProfileAry['columnerrorary'][$colName]='required';//xxx
				$base->ErrorObj->saveError($colName,'required',&$base);
				$base->FileObj->writeLog('db',"error: $colName($colData), required",&$base);
				//echo "colname: $colName, colerrmsg: $colErrMsg";//xxx
			} elseif ($colRegEx != "" && $colData != "") {
				$tst = preg_match($colRegEx,$colData);
				//echo "colname: $colName, colregex: $colRegEx, coldata: $colData, tst: $tst<br>";//xxx
				if ($tst == 0){
					$base->ErrorObj->saveError($colName,$colErrMsg."($colData)",&$base);
					//echo "colname: $colName, coldata: $colData, colerrmsg: $colErrMsg, preg: $colRegEx";//xxxf
					//$base->DebugObj->printDebug($currentRowAry,1,'xxxf');exit();
					$base->FileObj->writeLog('updatecsv',"error: $colName($colData), $colErrMsg",&$base);
					$allValidated=false;
				} // end tst for error
			} // end if colregex and coldata ne null
		} // end foreach dbtablemetaary
		//$base->paramsAry['debug']='jeff';//xxx
		if (array_key_exists('debug',$base->paramsAry)){
			$base->ErrorObj->printAllErrors(&$base);
		}
		$base->DebugObj->printDebug("-rtn:validateData",0); //xx (f)
		$base->FileObj->writeLog('debug2',"allvalidated: $allValidated",&$base);//xxxf
		return $allValidated;
	}
	//=======================================
	function getSqlDbAry($query,$passAry,$base){
		//$this->containerProfileAry=$base->DbObj->getSqlDbAry($query,$passAry,&$base);
		$dataAry=array();
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		return $dataAry;
	}
	//=======================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
	//=======================================
	function setRemoteDb($toDbConn,$base){
		if ($toDbConn == null){
			$base->FileObj->writeLogError("DbObj.setRemoteDb toDbConn is null",&$base);
		}
		$this->toDbConn=$toDbConn;
		$base->FileObj->writeLog("debug","set this->todbconn: $this->toDbConn",&$base);
		//echo "dbobj.setremotedb: tablename: $tableName, db: $this->dbConn, db2: $this->toDbConn, useothdb: $useOtherDb, dnt rst: $dontResetUseOtherDb<br>";//xxxd
	}
	//=======================================
	function incCalls(){$this->callNo++;}
}
?>
