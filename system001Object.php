<?php
class System001Object {
// version: 1.1.1
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
	var $base;
	var $companyAry = array();
	var $jobAry = array();
	var $dbConnAry = array();
	var $backupKeysAry = array();
	var	$toDomainName;
	var	$fromDomainName;
	var $toCompanyName;
	var	$toCompanyProfileId;
	var $fromCompanyName;
	var	$fromCompanyProfileId;
	var $fromDbConn;
	var $toDbConn;
	var $dbConn;
	var $maxDelRestoreLoopLevels=6;
	var $backupCompanyName;
	var $backupCompanyProfileId;
	var $ctr=0;
//========================================
	function System001Object() {
		//$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
		$curDir=getcwd();
		//if ($curDir !='/usr/local/www/jeffreypomeroy.com/www'){
			//if ($curDir !='/home/jeff/web/Base'){exit();}
		//}
	}
//----------------------------------------
	function errorOut($errMsg){
		exit($errMsg);	
	}
//========================================================
//------------------buildCompanyBackup--------------------
//========================================================
	function buildCompanyBackup($base){
		$base->DebugObj->printDebug("System001Obj:buildCompanyBackup)",0);
		$whatToDo=$base->paramsAry['whattodo'];
		$domainName=$base->paramsAry['domainname'];
		if ($domainName != NULL){
			$theDbConn=$base->ClientObj->getClientConn($domainName,&$base);
			if ($theDbConn == NULL){exit("the connection for $domainName is null");}
			$this->dbConnAry[0]=$theDbConn;
		}
		switch ($whatToDo){
			case 'selectdomainname':
				//dont have to do anything for this
			break;
			case 'selectcompanyprofileid':
				//dont have to do anything for this
			break;	
			case 'backupjobs':
				//$base->DebugObj->printDebug($base->paramsAry,1,'xxx');
				$this->doBackupJobs(&$base);
			break;
		}
	}
//========================================
	function doBackupJobs($base){
		$companyName=$base->paramsAry['companyname'];
		$domainName_raw=$base->paramsAry['domainname'];
		$domainName=str_replace('/','_',$domainName_raw);
		if ($companyName != NULL && $domainName != NULL){
//- get companyprofileid and save
			$this->backupCompanyName=$companyName;
			$query="select companyprofileid from companyprofile where companyname='$companyName'";
			$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
			$passAry=array();
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
			$this->backupCompanyProfileId=$workAry[0]['companyprofileid'];
//- get job list from params
			$jobListAry=$this->buildJobListAry(&$base);
//- build deletes which is beginning of file
			$companyString=$this->buildJobDeletes($jobListAry,&$base);
//- build backups and append to file
			$companyString.=$this->buildJobBackups($jobListAry,&$base);
//- write file
			$tmpLocal=$base->systemAry['tmplocal'];
			$fullPath=$tmpLocal.'/'.$companyName.'_jobs.txt';
			$base->FileObj->writeFile($fullPath,$companyString,&$base);
			$base->errorProfileAry['returnmsg']="<pre>$fullPath has been written</pre>";
		}
		$base->DebugObj->printDebug("-rtn:buildCompanyBackup",0); //xx (f)
	}
//-----------------------------------------
	function buildJobDeletes($jobListAry,$base){
		$base->DebugObj->printDebug("System001Obj:buildJobDeletes)",0);
		$returnStrg="comment:read company id for : $this->backupCompanyName";
		$returnStrg.="\n";
		$returnStrg.=$this->buildCompanyRead($this->backupCompanyName,&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:delete jobs for company: $this->backupCompanyName";
		foreach ($jobListAry as $ctr=>$jobName){
			$returnStrg.="\n";
			$returnStrg.="comment:delete job $jobName in all tables";
			$returnStrg.="\n";
			$returnStrg.=$this->buildJobDelete($jobName,&$base);
		}
		$base->DebugObj->printDebug("-rtn:buildJobDeletes",0); //xx (f)
		return $returnStrg;
	}
//------------------------------------------
	function buildJobBackups($jobListAry,$base){
		$base->DebugObj->printDebug("System001Obj:buildJobBackups)",0);
		$companyName=$base->paramsAry['companyname'];
		$returnStrg.="comment:read users for company: $companyName";	
		$returnStrg.="\n";
		$returnStrg.=$this->buildUserReads($companyName,&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:read all operations soon to be applications";	
		$returnStrg.="\n";
		$returnStrg.=$this->buildOperReads(&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:read all jobprofiles that are parents to other jobprofiles";
		$returnStrg.="\n";
		$returnStrg.=$this->buildJobParentReads(&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:do insert and reads for all jobs for company: $companyName";	
		foreach ($jobListAry as $ctr=>$jobName){
			$returnStrg.="\n";
			$returnStrg.="comment:do insert and reads on all tables for job: $jobName";	
			$returnStrg.="\n";
			//xxx - need to resume here what to do about: jobAry
			$returnStrg.=$this->buildJobInsertRead($jobName,&$base);
		}
		$base->DebugObj->printDebug("-rtn:buildJobBackups",0); //xx (f)
		return $returnStrg;
	}
//-----------------------------------------
	function buildCompanyRead($companyName,&$base){
		$base->DebugObj->printDebug("System001Obj:buildCompanyRead)",0);
		$query="select companyprofileid from companyprofile where companyname='$companyName'";
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$companyProfileId=$workAry[0]['companyprofileid'];
		$companyReadString="readqry:companyprofile:companyprofileid:$companyProfileId:select companyprofileid from companyprofile where companyname='$companyName'\n";
		$base->DebugObj->printDebug("-rtn:buildCompanyRead",0); //xx (f)
		return $companyReadString;
	}
//------------------------------------------
	function buildUserReads($companyName,&$base){
		$base->DebugObj->printDebug("System001Obj:buildUserReads)",0);
		$query="select userprofileid,username from userprofileview where companyname='$companyName'";
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$returnStrg=NULL;
		foreach ($workAry as $ctr=>$userAry){
			$userProfileId=$userAry['userprofileid'];
			$userName=$userAry['username'];
			if ($returnStrg != NULL){$returnStrg.="\n";}
			$query="select userprofileid from userprofile where username='$userName'";
			$returnStrg.="readqry:userprofile:userprofileid:$userProfileId:$query";			
		}
		$base->DebugObj->printDebug("-rtn:buildUserReads",0); //xx (f)
		return $returnStrg;
	}
//-----------------------------------------
	function buildOperReads($base){
		$base->DebugObj->printDebug("System001Obj:buildOperReads)",0);
		$query="select operationprofileid,operationname from operationprofile";	
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$returnStrg=NULL;
		foreach ($workAry as $ctr=>$operAry){
			$operationName=$operAry['operationname'];
			$operationProfileId=$operAry['operationprofileid'];
			if ($returnStrg != NULL){$returnStrg.="\n";}
			$query="select operationprofileid from operationprofile where operationname='$operationName'";
			$returnStrg.="readqry:operationprofile:operationprofileid:$operationProfileId:$query";			
		}
		$base->DebugObj->printDebug("-rtn:buildOperReads",0); //xx (f)
		return $returnStrg;
	}	
//-----------------------------------------
	function buildJobParentReads($base){
		$base->DebugObj->printDebug("System001Obj:buildJobParentReads)",0);
		//xxx-need to select within a company for this - fixed
		$query="select distinct jobparentid from jobxrefview where companyprofileid=$this->backupCompanyProfileId order by jobparentid";	
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$returnStrg=NULL;
		foreach ($workAry as $ctr=>$jobAry){
			$jobParentId=$jobAry['jobparentid'];
			if ($jobParentId != NULL){
				$query="select jobname from jobprofile where jobprofileid=$jobParentId";
				$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
	 			$passAry=array();
 				$work2Ary=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
 				$jobParentName=$work2Ary[0]['jobname'];
				if ($returnStrg != NULL){$returnStrg.="\n";}
				//xxx - need to select using a company also - fixed
				$query="select jobprofileid from jobprofile where jobname='$jobParentName' and companyprofileid=~companyprofile_$this->backupCompanyProfileId~";
				$returnStrg.="readqry:jobprofile:jobprofileid:$jobParentId:$query";
			}
		}
		$base->DebugObj->printDebug("-rtn:buildJobParentReads",0); //xx (f)
		return $returnStrg;
	}	
//-----------------------------------------
	function buildJobDelete($jobName,&$base){
		$base->DebugObj->printDebug("System001Obj:buildJobDelete)",0);
		$query="select * from dbtableprofile where dbtabletype='jobtable' order by dbtabledeleteorder";
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array('delimit1'=>'dbtablename');
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$jobString=NULL;
		foreach ($workAry as $dbTableName=>$dbTableAry){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$parentColName=$dbControlsAry['parentselectorname'];
			$parentTableName=$dbControlsAry['dbtablemetaary'][$parentColName]['dbcolumnforeigntable'];
			if ($dbTableName == 'jobprofile'){
				//xxx - need to select using company also
				$query="delete from jobprofile where jobname='$jobName' and companyprofileid=~companyprofile_$this->backupCompanyProfileId~";
			}
			//- special cases for: deptprofile, joboperationxref
			elseif ($dbTableName == 'deptprofile' || $dbTableName == 'joboperationxref'){
				//xxx - need to select using company also
				$query="delete from $dbTableName where jobprofileid=any (select jobprofileid from jobprofile where ";
				$query.=" jobname='$jobName' and companyprofileid=~companyprofile_$this->backupCompanyProfileId~)";
			}
			else {
				if ($parentTableName != 'jobprofile'){
					//xxx - add in company
					$query="delete from $dbTableName where $parentColName=any (select $parentColName from $parentTableName,jobprofile where";
					$query.=" jobprofile.jobprofileid = $parentTableName.jobprofileid and jobprofile.jobname='$jobName' and companyprofileid=~companyprofile_$this->backupCompanyProfileId~)";
				}
				else {
					//xxx - add in company
					$query="delete from $dbTableName where jobprofileid=any (select jobprofileid from jobprofile where ";
					$query.=" jobname='$jobName' and companyprofileid=~companyprofile_$this->backupCompanyProfileId~)";
				}
			}
			$jobString.="delqry:$query\n";
		}
		$base->DebugObj->printDebug("-rtn:buildJobDelete",0); //xx (f)	
		return $jobString;
	}
	//-----------------------------------------
	function buildJobInsertRead($jobName,&$base){
		$base->DebugObj->printDebug("System001Obj:buildJobInsertRead)",0);
		$query="select * from dbtableprofile where dbtabletype='jobtable' order by dbtableupdateorder";
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array('delimit1'=>'dbtablename');
		$dbTablesAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$jobString=NULL;
		foreach ($dbTablesAry as $dbTableName=>$dbTableAry){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbTableNameView=$dbTableName.'view';
			//xxx - need to add in companyname
			$query="select * from $dbTableNameView where jobname='$jobName' and companyprofileid=$this->backupCompanyProfileId";
			$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
			$passAry=array();
			$dataWorkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- title stuff
			$columnsWorkAry=array();
			$theComma=NULL;
			//$jobString.="columns:";
//- column names
			$columnNames=NULL;
			$selectorAry=array();
			foreach ($dbControlsAry['dbtablemetaary'] as $dbColumnName=>$dbColumnAry){
					$dbColumnType=$dbColumnAry['dbcolumntype'];
					$dbColumnKey=$base->UtlObj->returnFormattedData($dbColumnAry['dbcolumnkey'],'boolean','internal');
					$dbColumnForeignKey=$base->UtlObj->returnFormattedData($dbColumnAry['dbcolumnforeignkey'],'boolean','internal');
					$dbColumnForeignTable=$dbColumnAry['dbcolumnforeigntable'];
					$dbColumnForeignField=$base->UtlObj->returnFormattedData($dbColumnAry['dbcolumnforeignfield'],'boolean','internal');
					$dbColumnMainTable=$dbColumnAry['dbcolumnmaintable'];
					$dbColumnForeignColumnName=$dbColumnAry['dbcolumnforeigncolumnname'];
					if ($dbColumnMainTable != NULL && $dbColumnMainTable != $dbTableName){
						$dbColumnForeignField=true;
					}
					$dbColumnSelector=$base->UtlObj->returnFormattedData($dbColumnAry['dbcolumnselector'],'boolean','internal');
					if (!$dbColumnKey && !$dbColumnForeignField){
							$columnNames.=$theComma.$dbColumnName;
							$theComma=",";
							if ($dbColumnForeignKey){
								$useValue="$dbColumnForeignTable";	
							}
							else {
								$useValue='basic';
							}
							$columnsWorkAry[$dbColumnName]=$useValue;
							if ($dbColumnSelector){$selectorAry[$dbColumnName]=$useValue;}
					}
			}
//- data
			foreach ($dataWorkAry as $rowNo=>$dbColumnDataAry){
				$theKeyName=$dbControlsAry['keyname'];
				$theKeyValue=$dbColumnDataAry[$theKeyName];
//- build read statement
				$readQuery="select $theKeyName from $dbTableName where ";
				$theAnd=NULL;
				foreach ($selectorAry as $selectorColumnName=>$selectorColumnControlType){
					if ($selectorColumnControlType == 'basic'){
						$selectorColumnValue_sql=$base->UtlObj->returnFormattedData($dbColumnDataAry[$selectorColumnName],$dbControlsAry['dbtablemetaary'][$selectorColumnName]['dbcolumntype'],'sql');	
					}
					else {
						$columnKeyValue=$dbColumnDataAry[$selectorColumnName];
						$selectorColumnValue_sql='~'.$selectorColumnControlType.'_'.$columnKeyValue.'~';
					}
					$readQuery.="$theAnd $selectorColumnName=$selectorColumnValue_sql";
					$theAnd=" and ";	
				}
//- build insert statement
				$insertQuery="insert into $dbTableName ($columnNames) values (";
				$theComma=NULL;
				foreach ($columnsWorkAry as $columnName=>$columnControlType){
					if ($columnControlType == 'basic'){
						$columnType=$dbControlsAry['dbtablemetaary'][$columnName]['dbcolumntype'];
						$columnData_raw=$dbColumnDataAry[$columnName];
						//- found single quotes in column data - big no no must be %sglqt%
						$columnData_temp=str_replace("'",'%sglqt%',$columnData_raw);
						$columnData_temp2=str_replace(chr(0xa),'',$columnData_temp);
						$columnData_lessraw=str_replace(chr(0xd),'',$columnData_temp2);
						//- dont autoconvert if varchar to save the %xxx% stuff
						if ($columnType == 'varchar'){$columnData="'$columnData_lessraw'";}
						else {$columnData=$base->UtlObj->returnFormattedData($columnData_lessraw,$columnType,'sql');}
					}
					else {
//- all foreign references are numeric per my definition!!!
//- so if it does not exist(some dont have to be there) then it is NULL
						$columnKeyValue=$dbColumnDataAry[$columnName];
						if ($columnKeyValue == NULL){$columnData='NULL';}
						else {$columnData='~'.$columnControlType.'_'.$columnKeyValue.'~';}
					}	
					$insertQuery.=$theComma.$columnData;
					$theComma=",";
				}
				$insertQuery.=')';
//- update jobString
				//below can go over the maximum size for a field in an array
				//- will do continue if over 1000 bytes
				$jobStringLine="insqry:$insertQuery";
				$theLen=strlen($jobStringLine);
				if ($theLen>1000){
					$newJobStringLine=substr($jobStringLine,0,1000);
					$theRemainingLen=$theLen-1000;
					$newJobStringLineRemaining=substr($jobStringLine,1000,$theRemainingLen);	
					$jobString.='pre:'.$newJobStringLine."\n".$newJobStringLineRemaining."\n";
				}
				else {$jobString.=$jobStringLine."\n";}
				//$jobString.="insqry:$insertQuery\n";
				$jobString.="readqry:$dbTableName:$theKeyName:$theKeyValue:$readQuery\n";
			}
		}
		//exit();//xxx
		$base->DebugObj->printDebug("-rtn:buildJobInsertRead",0); //xx (f)
		return $jobString;
	}
//========================================================
//------------restoreFromBackup---------------------------
//========================================================
function restoreFromBackup($base){
		//exit('needs testing before being allowed to run');//xxx
		$domainName=$base->paramsAry['domainname'];
		if ($domainName != NULL){
			$theDbConn=$base->ClientObj->getClientConn($domainName,&$base);
			if ($theDbConn == NULL){exit("the connection for $domainName is null");}
			$this->dbConnAry[0]=$theDbConn;
		}
		else {exit('no domain is setup');}
		$companyName=$base->paramsAry['companyname'];
		$fileName=$companyName.'_jobs.txt';
		$restoreSystemAry=$base->ClientObj->getClientData($domainName,&$base);
		$dirPath=$restoreSystemAry['tmplocal'];
		$filePath=$dirPath.'/'.$fileName;
		$restoreFileAry=$base->FileObj->getFileArray($filePath);
		$base->FileObj->initLog('companyrestore.log',&$base);
		$base->FileObj->writeLog('companyrestore.log','log for restore of '.$fileName,&$base);
		$keyValuesAry=array();
		//$base->DebugObj->printDebug($restoreFileAry,1,'xxxrfa');
		//exit();
		$preRunLine=NULL;
		$theCr=pack('c1',10);
		foreach ($restoreFileAry as $ctr=>$runLine){
			$runLine=str_replace($theCr,'',$runLine);
			$this->ctr=$ctr;
			//- check for a line that was too long
			//echo "runline: $runLine<br>";//xxxd
			$prefix=substr($runLine,0,4);
			if ($prefix=='pre:'){
				$theRemainingLength=strlen($runLine)-4;
				$preRunLine=substr($runLine,4,$theRemainingLength);	
				$runLine='comment:truncation prefix line';
			}
			else {
				if ($preRunLine != NULL){
					$runLine=$preRunLine.$runLine;
					$preRunLine=NULL;
				}	
			}
			//echo "$ctr: $runLine<br>";//xxxd
			//echo "----------<br>";//xxxd
			$runLineWork=str_replace(":hover","%colonhover%",$runLine);
			$runLineAry=explode(':',$runLineWork);
			$aryCount=count($runLineAry);
			//$pos=strpos($runLineAry[1],'Plants',0);
			//if ($pos>0){$base->DebugObj->printDebug($runLineAry,1,'xxx');}
			//xxx runLineAry[1] is truncated at this point
			$noSubFields=count($runLineAry);
			$runType=$runLineAry[0];
			switch ($runType){
				case 'readqry':
					if ($aryCount>5){
						for ($ctr=5;$ctr<$aryCount;$ctr++){
							$runLineAry[4].=':'.$runLineAry[$ctr];	
						}
					}
					$dbTableName=$runLineAry[1];
					$dbKeyName=$runLineAry[2];
					$oldKeyValue=$runLineAry[3];
					$query_raw=$runLineAry[4];
					$query=$this->restoreFromBackupReturnFormattedQuery($query_raw,$keyValuesAry,&$base);
					$newKeyValue=$this->restoreFromBackupDoRead($dbKeyName,$query,&$base);
					if (array_key_exists($dbTableName,$keyValuesAry)){
						$keyValuesAry[$dbTableName][$oldKeyValue]=$newKeyValue;	
					}
					else {
						$keyValuesAry[$dbTableName]=array($oldKeyValue=>$newKeyValue);	
					}
					break;
				case 'delqry':
					if ($aryCount>2){
						for ($ctr=2;$ctr<$aryCount;$ctr++){
							$runLineAry[1].=':'.$runLineAry[$ctr];	
						}
					}
					$query_raw=$runLineAry[1];
					$query=$this->restoreFromBackupReturnFormattedQuery($query_raw,$keyValuesAry,&$base);
					$this->restoreFromBackupDoQuery($query,&$base);
					break;
				case 'insqry':
					if ($aryCount>2){
						for ($ctr=2;$ctr<$aryCount;$ctr++){
							$runLineAry[1].=':'.$runLineAry[$ctr];	
						}
					}
					$query_raw=$runLineAry[1];
					//$pos=strpos($query_raw,'Plants',0);
					//$pos=2;
					//if ($pos>0){$base->DebugObj->printDebug($runLineAry,1,'xxx');}
					//xxx - runlineary has trucated string at this position
					$query=$this->restoreFromBackupReturnFormattedQuery($query_raw,$keyValuesAry,&$base);
					$this->restoreFromBackupDoQuery($query,&$base);
					break;
				default:
			}
		}
		//exit();
	}
	//----------------------------------------
	function restoreFromBackupReturnFormattedQuery($query_raw,$keyValueAry,&$base){
		$queryAry=explode('~',$query_raw);
		$queryCnt=count($queryAry);
		for ($ctr=1;$ctr<$queryCnt;$ctr=$ctr+2){
			$convStrg=$queryAry[$ctr];
			$convStrgAry=explode('_',$convStrg);
			$dbTableName=$convStrgAry[0];
			$oldKeyValue=$convStrgAry[1];
			$newKeyValue=$keyValueAry[$dbTableName][$oldKeyValue];
			//- all conversions are foreign keys which are all integers
			//- so always make them 'NULL' if the are null
			if ($newKeyValue == NULL){
				$newKeyValue='NULL';
				$errorMsg='ERROR: null value for conversion of old values: ' . $convStrgAry[0] . ', ' . $convStrgAry[1];
				$base->FileObj->writeLog('companyrestore.log',$this->ctr.') '.$errorMsg,&$base);
			}
			$queryAry[$ctr]=$newKeyValue;
		}
		$query_wip=implode('',$queryAry);
		$query=str_replace("%colonhover%",":hover",$query_wip);
		return $query;
	}
	//----------------------------------------
	function restoreFromBackupDoRead($dbKeyName,$query,$base){
		//echo "do read: $query<br>";
		$base->FileObj->writeLog('companyrestore.log',$this->ctr.') '.$query,&$base);
		$result=$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$dbKeyValue=$workAry['0'][$dbKeyName];
		//$dbKeyValue=rand(1,99999);//xxx
		$base->FileObj->writeLog('companyrestore.log',$this->ctr.') '.'read keyid: '.$dbKeyValue,&$base);
		//echo "...key read: $dbKeyValue<br>";
		return $dbKeyValue;	
	}
	//----------------------------------------
	function restoreFromBackupDoQuery($query,$base){
		$base->FileObj->writeLog('companyrestore.log',$query,&$base);
		//xxxf !!!!!
		$base->ClientObj->queryClientDbTable($query,$this->dbConnAry[0],'updatenoconversion',&$base);
		//echo "do query: $query<br>";//xxx
	}
//========================================================
//-----------------copyJobs-------------------------------
//========================================================
	function copyJobs($base){
		$base->DebugObj->printDebug("System001Obj:copyJobs)",0);
		$base->UtlObj->clearSessionBuffer(&$base);
		$todaysDate=$base->UtlObj->getTodaysDate(&$base);
		$base->UtlObj->saveValue('debug','<br> enter copyjobs at: '.$todaysDate.'<br>',&$base);
		for ($ctr=0;$ctr<15;$ctr++){echo "&nbsp;<p>";}
		$formName=$base->paramsAry['whichform'];
		$this->toDomainName=$base->paramsAry['todomainname'];
		$this->fromDomainName=$base->paramsAry['fromdomainname'];
		$this->toCompanyProfileId=$base->paramsAry['tocompanyprofileid'];
		$this->fromCompanyProfileId=$base->paramsAry['fromcompanyprofileid'];
		if ($this->fromDomainName != NULL && $this->fromDomainName != 'NULL'){
			//echo "fromdomainname: $this->fromDomainName<br>";//xxx
			$this->fromDbConn=$base->ClientObj->getClientConn($this->fromDomainName,&$base);
			$this->dbConnAry[0]=$this->fromDbConn;
		}
		if ($this->toDomainName != NULL && $this->toDomainName != 'NULL'){
			//echo "todomainname: $this->toDomainName<br>";//xxx
			$this->toDbConn=$base->ClientObj->getClientConn($this->toDomainName,&$base);
			$this->dbConnAry[1]=$this->toDbConn;
		}
		switch ($formName){
		case 'selectfromdomainname':
			break;
		case 'selecttodomainname':
			break;
		case 'selectfromcompanyname':
			break;
		case 'selecttocompanyname':
			break;
		case 'fromjobnames':
			$base->UtlObj->appendValue('debug','--- run doFromJobNames ---<br>',&$base);
			$this->doFromJobNames(&$base);
			break;
		case 'tojobnames':
			$base->UtlObj->appendValue('debug','--- run tojobnames ---<br>',&$base);
			$this->doToJobNames(&$base);
			break;
		case 'backupdb':
			$this->backupDb(&$base);
			break;
		default:
			exit("no provision for: $formName");
		}
		$base->DebugObj->printDebug("-rtn:copyJobs",0); //xx (f)
	}
//-------------------------------------
	function backupDb($base){
		$base->DebugObj->printDebug("System001Obj:backupDb)",0);
		$fileName=$base->paramsAry['filename'];
		$domainName=$base->paramsAry['fromdomainname'];
		$currentSystemAry=$base->ClientObj->getClientData($domainName,&$base);
		$baseLocal=$currentSystemAry['baselocal'];
		$dbName=$currentSystemAry['dbname'];
		$dbUserName=$currentSystemAry['dbusername'];
		if ($dbUserName == NULL){$dbUserNameInsert=NULL;}
		else {$dbUserNameInsert=" -U $dbUserName";}
		if ($fileName != NULL){
			$backupDbPath=$baseLocal.'/dbbackup/'.$fileName;
			$theCommand="pg_dump $dbName $dbUserNameInsert -c > $backupDbPath";
			echo "thecmd: $theCommand<br>";
			$returnStatus=popen($theCommand,"r");
		}	
		$base->DebugObj->printDebug("-rtn:backupDb",0); //xx (f)
	}
//-------------------------------------
	function doToJobNames($base){
		$base->DebugObj->printDebug("System001Obj:doToJobNames)",0);
		$jobListAry=$this->buildJobListAry(&$base);	
		//$base->DebugObj->printDebug($jobListAry,1,'xxxd');
		foreach ($jobListAry as $ctr=>$jobName){
			$this->deleteJob($jobName,&$base);
		}
		$base->DebugObj->printDebug("-rtn:doToJobNames",0); //xx (f)
	}
//-------------------------------------
	function doFromJobNames($base){
		$base->DebugObj->printDebug("System001Obj:doFromJobNames)",0);
		$base->UtlObj->appendValue('debug','o build $this->buildJobListAry from params jobname_n<br>',&$base);
		$jobListAry=$this->buildJobListAry(&$base);
		$deleteAllJobs_params=$base->paramsAry['deletealljobs'];
		if ($deleteAllJobs_params != NULL){$deleteAllJobs=true;}
		else {$deleteAllJobs=false;}
//- get restore list from 'fromdomain, fromcompany'
		$base->UtlObj->appendValue('debug','o select all (from) jobs for a company and make it fromDataAry <br>',&$base);
		$theQuery="select * from jobprofileview where companyprofileid=$this->fromCompanyProfileId";
		if ($this->fromDbConn != NULL){
			$result=$base->ClientObj->queryClientDbTable($theQuery,$this->fromDbConn,'read',&$base);
		}
		else {$result=NULL;}
		$passAry=array('delimit1'=>'jobname');
		$fromDataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- get delete list from 'todomain, tocompany'
		$base->UtlObj->appendValue('debug','o select all (to) jobs for the (to) company and make it toDataAry<br>',&$base);
		$theQuery="select * from jobprofileview where companyprofileid='$this->toCompanyProfileId'";
		if ($this->toDbConn != NULL){
			$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);
		}
		else {$result=NULL;}
		$passAry=array('delimit1'=>'jobname');
		$toDataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($toDataAry,1,'xxxdtoda');
//-
		$this->jobAry['jobdeleteorder']=array();
		$this->jobAry['jobrestoreorder']=array();
		$base->UtlObj->appendValue('debug','o create jobAry[jobdeleteorder/jobrestoreorder][$jobName][completerow] from $jobListAry/$toDataAry/$fromDataAry <br>',&$base);
//- todomain deletes if you are deleting all of them
		if ($deleteAllJobs){
			foreach ($toDataAry as $jobName=>$toJobAry){
				$jobDeleteOrder=$toJobAry['jobdeleteorder'];
				if (!is_array($this->jobAry['jobdeleteorder'][$jobDeleteOrder])){
					$this->jobAry['jobdeleteorder'][$jobDeleteOrder]=array();
				}
				$this->jobAry['jobdeleteorder'][$jobDeleteOrder][$jobName]=$toDataAry[$jobName];	
			}
		}
		foreach ($jobListAry as $ctr=>$jobName){
//- todomain deletes
			//echo 'x'.$jobName.'x';//xxxd
			if (array_key_exists($jobName,$toDataAry) && !$deleteAllJobs){
				//echo "xxxd: in toDataAry";				
				$jobDeleteOrder=$toDataAry[$jobName]['jobdeleteorder'];
				if (!is_array($this->jobAry['jobdeleteorder'][$jobDeleteOrder])){
					$this->jobAry['jobdeleteorder'][$jobDeleteOrder]=array();
				}
				$this->jobAry['jobdeleteorder'][$jobDeleteOrder][$jobName]=$toDataAry[$jobName];
			}
//- fromomain restores
			$jobRestoreOrder=$fromDataAry[$jobName]['jobrestoreorder'];
			if (!is_array($this->jobAry['jobrestoreorder'][$jobRestoreOrder])){
				$this->jobAry['jobrestoreorder'][$jobRestoreOrder]=array();
			}
			$this->jobAry['jobrestoreorder'][$jobRestoreOrder][$jobName]=$fromDataAry[$jobName];
		}
		//$base->DebugObj->printDebug($this->jobAry,1,'xxx');
		$passAry=array();
		$base->UtlObj->appendValue('debug','<br>*** run moveJobsOver ***<br><br>',&$base);
		$this->moveJobsOver(&$base);
		$base->DebugObj->printDebug("-rtn:doFromJobNames",0); //xx (f)
	}
//---------------------------------------------------
	function buildJobListAry($base){
		$base->DebugObj->printDebug("System001Obj:buildJobListAry)",0);
		$returnAry=array();
		foreach ($base->paramsAry as $name=>$value){
			$valueAry=explode('_',$name);
			if ($valueAry[0] == 'jobname'){$returnAry[]=$value;}
		}
		//xxxxf - need to order the jobs so parents are written in first
		//- I will try to do it so they are in order in the check boxes	
		$base->DebugObj->printDebug("-rtn:buildJobListAry",0); //xx (f)
		return $returnAry;
	}
//----------------------------------------------------
	function moveJobsOver($base){
			$base->DebugObj->printDebug("System001Obj:moveJobsOver)",0);
			$delJobAry=$this->jobAry['jobdeleteorder'];
			for ($delCtr=0;$delCtr<=$this->maxDelRestoreLoopLevels;$delCtr++){
				$base->UtlObj->appendValue('debug','o check job delete level ('.$delCtr.')<br>',&$base);
				//$base->DebugObj->printDebug($this->jobAry,1,'xxxd');exit('xxxd');
				if (is_array($this->jobAry['jobdeleteorder'][$delCtr])){
					$delJobAry=$this->jobAry['jobdeleteorder'][$delCtr];
					foreach ($delJobAry as $jobName=>$subJobAry){
						$this->deleteJob($jobName,&$base);
						$companyName=$subJobAry['companyname'];
					}
				}				
			}
			//$base->DebugObj->printDebug($this->jobAry,1,'xxxd');
			//- put operation profile xref into backupKeysAry - since may be different ids
			//- done below to fix problem
			$this->backupKeysAry=$this->getOperationProfileXref(&$base); 
			$this->getJobParentIds(&$base);
			for ($restoreCtr=0;$restoreCtr<=$this->maxDelRestoreLoopLevels;$restoreCtr++){
				$base->UtlObj->appendValue('debug','o check restore level ('.$restoreCtr.')<br>',&$base);
				if (is_array($this->jobAry['jobrestoreorder'][$restoreCtr])){
					$restoreJobAry=$this->jobAry['jobrestoreorder'][$restoreCtr];
					foreach ($restoreJobAry as $jobName=>$subJobAry){
						$companyName=$subJobAry['companyname'];
						$this->restoreJob($jobName,&$base);
					}
				}					
			}
			$base->DebugObj->printDebug("-rtn:moveJobsOver",0); //xx (f)
			//echo "save: $displayStrg<br>";
			//$base->DebugObj->printDebug($this->jobAry,1,'xxxd');			
	}
//========================================================
	function getClientConn($dbNo,$base){
		$base->DebugObj->printDebug("System001Obj:getClientConn)",0);
		//$base->DebugObj->printDebug($this->dbConnAry,1,'xxxxd');
		if ($dbNo>0 && $dbNo<10){
			$returnDbConn=$this->dbConnAry[($dbNo-1)];	
			return $returnDbConn;
		}
		$base->DebugObj->printDebug("-rtn:getClientConn",0); //xx (f)
	}
//========================================================
	function setClientConn($theConn,$dbNo,$base){
		if ($dbNo>0 && $dbNo<10){
			$this->dbConnAry[($dbNo-1)]=$theConn;
		}
	}
//=========================================================
	function deleteJob($jobName,$base){
		$base->DebugObj->printDebug("System001Obj:deleteJob)",0);
		$displayStrg="<br><div class=\"level2\"> *** delete $jobName from $this->toDomainName($this->toCompanyProfileId) ***</div><br>";
		$base->UtlObj->appendValue('debug',$displayStrg,&$base);
		$displayStrg2="<div class=\"level2\">Delete $jobName from $this->toDomainName($this->toCompanyProfileId) </div>";
		$base->errorProfileAry['deleterestorestatus'].=$displayStrg2;
		$base->UtlObj->appendValue('debug','<br>o select all dbtableprofiles with dbtabletype: jobtable<br><br>',&$base);
		$theQuery="select * from dbtableprofileview where dbtabletype='jobtable' order by dbtabledeleteorder";
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);
		$passAry=array();
		$dbTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$cntTables=count($dbTableAry);
		for ($tableCtr=0;$tableCtr<$cntTables;$tableCtr++){
			$dbTableName=$dbTableAry[$tableCtr]['dbtablename'];
			$dbTableDeleteOrder=$dbTableAry[$tableCtr]['dbtabledeleteorder'];
			$dbTableName_view=$dbTableName.'view';
			$dbTableInfo=$this->getDbTableInfo($this->toDbConnect,$dbTableName,&$base);	
			$keyName=$dbTableInfo['keyname'];
			$selectorNameAry=$dbTableInfo['selectornameary'];
			$parentSelectorName=$dbTableInfo['parentselectorname'];
			//xxx- $toCompanyProfileId
			$delSelectQuery="select * from $dbTableName_view where jobname='$jobName' and companyprofileid=$this->toCompanyProfileId";
			$result=$base->ClientObj->queryClientDbTable($delSelectQuery,$this->toDbConn,'read',&$base);
			$passAry=array();
			$delSelectAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$base->UtlObj->appendValue('debug',"o 'to' table: $dbTableName($dbTableDeleteOrder)<br>",&$base);
			//echo "keyname: $keyName<br>";//xxxd
			foreach ($delSelectAry as $ctr=>$thisDelSelectAry){
				$thisRowsKey=$thisDelSelectAry[$keyName];
				//$base->DebugObj->printDebug($thisDelSelectAry,1,'xxxd');
				//-xxx check $toCompanyProfileId to see if it gets deleted
				$delQuery="delete from $dbTableName where $keyName=$thisRowsKey";
				//echo "query1: $delQuery<br>";//xxxd
				//-xxxf: leave on file for the main job
				//if ($dbTableName != 'jobprofile'){
				$result=$base->ClientObj->queryClientDbTable($delQuery,$this->toDbConn,'delete',&$base);
				//}
				$base->UtlObj->appendValue('debug',"- query: $delQuery<br>",&$base);	
			}		
		}
		$base->UtlObj->appendValue('debug',"<br>*** end deleteJob ***<br><br>",&$base);
		$base->DebugObj->printDebug("-rtn:deleteJob",0); //xx (f)
	}
//=========================================================
	function getOperationProfileXref($base){
		$returnAry=array();
		$theQuery="select * from operationprofile";
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->fromDbConn,'updatenoconversion',&$base);
		$passAry=array('delimit1'=>'operationname');	
		$fromWorkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'updatenoconversion',&$base);	
		$toWorkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
		foreach ($fromWorkAry as $operationName=>$operationAry){
			$fromId=$operationAry['operationprofileid'];
			$toId=$toWorkAry[$operationName]['operationprofileid'];
			$returnAry['operationprofile_'.$fromId]=$toId;
		}
		return $returnAry;	
	}
//========================================================
	function getJobParentIds(&$base){
		$base->DebugObj->printDebug("System001Obj:getJobParentIds)",0);	
		$theQuery="select distinct jobparentid from jobxrefview where companyprofileid=$this->fromCompanyProfileId order by jobparentid";
		$displayStrg="<br><div class=\"level2\">get jobparentids: $theQuery</div><br>";
		$base->UtlObj->appendValue('debug',$displayStrg,&$base);
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->fromDbConn,'updatenoconversion',&$base);	
		$passAry=array();
		$jobParentListAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($jobParentListAry,1,'xxxd');
		foreach ($jobParentListAry as $ctr=>$jobParentAry){
			$jobParentId=$jobParentAry['jobparentid'];
			$theQuery2="select jobname from jobprofile where jobprofileid=$jobParentId";
			$result=$base->ClientObj->queryClientDbTable($theQuery2,$this->fromDbConn,'updatenoconversion',&$base);	
			$passAry=array();
			$jobParentListAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$jobName=$jobParentListAry[0]['jobname'];
			$theQuery3="select jobprofileid from jobprofileview where jobname='$jobName' and companyprofileid=$this->toCompanyProfileId";
			//echo "thequery3: $theQuery3<br>";//xxx
			$displayStrg="write: backupKeysAry['jobprofile_'.$jobParentId] = $newJobParentId;";
			$base->UtlObj->appendValue('debug',$displayStrg,&$base);
			$result=$base->ClientObj->queryClientDbTable($theQuery3,$this->toDbConn,'updatenoconversion',&$base);	
			$passAry=array();
			$newJobParentListAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($newJobParentListAry,1,'xxx');
			//- below comes up null xxx
			$newJobParentId=$newJobParentListAry[0]['jobprofileid'];
			//echo "jobparentid: $jobParentId, newjobparentid: $newJobParentId, name: $jobName<br>";//xxx
			if ($newJobParentId != NULL){
				$this->backupKeysAry['jobprofile_'.$jobParentId]=$newJobParentId;
				$displayStrg="write: backupKeysAry['jobprofile_'.$jobParentId] = $newJobParentId;";
				$base->UtlObj->appendValue('debug',$displayStrg,&$base);
			}
		}	
		$base->DebugObj->printDebug("-rtn:getJobParentIds",0); //xx (f)
	}
//=========================================================
	function restoreJob($jobName,$base){
		$base->DebugObj->printDebug("System001Obj:restoreJob)",0);
		$displayStrg="<br><div class=\"level2\">*** restore $jobName from $this->fromDomainName($this->fromCompanyProfileId) to $this->toDomainName($this->toCompanyProfileId) ***</div><br>";
		$base->UtlObj->appendValue('debug',$displayStrg,&$base);
		$displayStrg2="<div class=\"level2\"> Restore $jobName from $this->fromDomainName($this->fromCompanyProfileId) to $this->toDomainName($this->toCompanyProfileId)</div>";
		$base->errorProfileAry['deleterestorestatus'].=$displayStrg2;
//- get list of tables to restore from 'to' domain
		$theQuery="select * from dbtableprofileview where dbtabletype='jobtable' order by dbtableupdateorder";
		$displayStrg="<div class=\"level2\"> get list of tables: $theQuery</div>";
		$base->UtlObj->appendValue('debug',$displayStrg,&$base);
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);
		$passAry=array();
		$dbTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($dbTableAry,1,'xxx');
		//exit();
//- xxx error below, should not replace whole directory!!!
//- put operation profile xref into backupKeysAry - since may be different ids
//-moved		$this->backupKeysAry=$this->getOperationProfileXref(&$base); 
//- loop through table list
		$cntTables=count($dbTableAry);
		$displayStrg="<div class=\"level1\">number of tables to restore: $cntTables</div>";
		$base->UtlObj->appendValue('debug',$displayStrg,&$base);
		for ($tableCtr=0;$tableCtr<$cntTables;$tableCtr++){
//- get info on table to restore from 'to' domain
			$dbTableName=$dbTableAry[$tableCtr]['dbtablename'];
			$dbTableUpdateOrder=$dbTableAry[$tableCtr]['dbtableupdateorder'];
			$dbTableName_view=$dbTableName.'view';
			$dbTableInfo=$this->getDbTableInfo($this->toDbConnect,$dbTableName,&$base);	
			$keyName=$dbTableInfo['keyname'];
			$selectorNameAry=$dbTableInfo['selectornameary'];
			$parentSelectorName=$dbTableInfo['parentselectorname'];
//- get data to restore from 'from' domain
//xxx - need to select with jobname and company
			$restoreRowsQuery="select * from $dbTableName_view where jobname='$jobName' and companyprofileid=$this->fromCompanyProfileId";
			$result=$base->ClientObj->queryClientDbTable($restoreRowsQuery,$this->fromDbConn,'read',&$base);
			$passAry=array();
			$restoreRowsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$base->UtlObj->appendValue('debug',"o (from) table: $dbTableName($dbTableUpdateOrder)<br>",&$base);
//- loop through each data restore row
			foreach ($restoreRowsAry as $ctr=>$restoreRowAry){
				$keyValue=$restoreRowAry[$keyName];
				$theQuery="insert into $dbTableName (";
				$columnList=NULL;
				$valueList=NULL;
				$cmma=NULL;
				foreach ($restoreRowAry as $columnName=>$columnValue){
					if (array_key_exists($columnName,$dbTableInfo['elementsary'])){
//- get needed fields
						$columnType=$dbTableInfo['elementsary'][$columnName]['dbcolumntype'];
						$dbColumnParentSelector_raw=$dbTableInfo['elementsary'][$columnName]['dbcolumnparentselector'];
						$dbColumnParentSelector=$base->UtlObj->returnFormattedData($dbColumnParentSelector_raw,'boolean','internal',&$base);
						//- foreign key
						$dbColumnForeignKey_raw=$dbTableInfo['elementsary'][$columnName]['dbcolumnforeignkey'];
						$dbColumnForeignKey=$base->UtlObj->returnFormattedData($dbColumnForeignKey_raw,'boolean','internal',&$base);
						//- foreign table
						$dbColumnForeignTable=$dbTableInfo['elementsary'][$columnName]['dbcolumnforeigntable'];
//- replace foreign key with new one
						if ($dbColumnForeignKey){
							if ($dbColumnParentSelector){
								if ($dbColumnForeignTable == 'companyprofile'){
									$columnValue_new=$this->toCompanyProfileId;
								} else {
									$columnValue_new=$this->backupKeysAry[$dbColumnForeignTable.'_'.$columnValue];
								}
								$base->UtlObj->appendValue('debug'," - get $columnValue_new from backupkeysary[$dbColumnForeignTable".'_'."$columnValue]<br>",&$base);				
								if ($columnValue_new == NULL){
									$base->UtlObj->appendValue('debug'," - *** fatal error1: parent foreign new value is null ***<br>",&$base);				
									exit("error1: columnvalue_new: $columnValue_new, dbtablename: $dbTableName, columnname: $columnName, dbcolumnforeigntable: $dbColumnForeignTable, columnvalue: $columnValue");
								}
								$columnValue=$columnValue_new;
							}
							else {
								//xxx figure out why below errors out
								$columnValue_new=$this->backupKeysAry[$dbColumnForeignTable.'_'.$columnValue];
								$base->UtlObj->appendValue('debug'," - get $columnValue_new from backupkeysary[$dbColumnForeignTable".'_'."$columnValue]<br>",&$base);				
								if ($columnValue_new == NULL){
									$base->UtlObj->appendValue('debug'," - *** warning error2: nonparent foreigntable new value is null ***<br>",&$base);				
									//exit("error2: columnvalue null for: $dbTableName, $columnName, $dbColumnForeignTable, $columnValue");
								}
								$columnValue=$columnValue_new;
							}
						}
//- modify data according to type and build into query	xxxe	
						switch ($columnType){
							case 'varchar':
//- need to do below because I put sgl qts in database so cant move them unless they are symbolic
			                	$getOut=false;
                				while (!$getOut) {
									$theLen=strlen($columnValue);
                  					$pos=strpos('x'.$columnValue,"'",0);
                  					if ($pos>0){
                  						$preColumnValue=substr($columnValue,0,$pos-1);
                  						$remLen=$theLen-$pos;
                  						$postColumnValue=substr($columnValue,$pos,$remLen);
                  						$newColumnValue=$preColumnValue.'%sglqt%'.$postColumnValue;
                  						$columnValue=$newColumnValue;
                  					}
                  					else {$getOut=true;}
                				}
								$columnList.=$cmma." $columnName";
								$valueList.=$cmma."'$columnValue'";
								$cmma=",";
							break;
							case 'numeric':
								if ($columnValue == NULL){$columnValue='NULL';}
								$columnList.=$cmma." $columnName";
								$valueList.=$cmma." $columnValue";
								$cmma=",";	
							break;
							case 'boolean':
								$columnValue_sql=$base->UtlObj->returnFormattedData($columnValue,'boolean','sql');
								$columnList.=$cmma." $columnName";
								$valueList.=$cmma." $columnValue_sql";
								$cmma=",";
							break;
							case 'integer':
								if ($columnValue == NULL){$columnValue='NULL';}
								$columnList.=$cmma." $columnName";
								$valueList.=$cmma." $columnValue";
								$cmma=",";									
							break;	
							default:
							exit("error with: $columnName, $columnType<br>");
						} //end switch
					} //end if
				} // end foreach columnname
				$theQuery.="$columnList) values ($valueList)";
				//xxx
				//echo "query: $theQuery<br>";
				$base->UtlObj->appendValue('debug',"&nbsp;&nbsp;- query: $theQuery<br>",&$base);
//- update to 'to' domain
				//- since updating what was just read, then write without changes(e.g. no conversion)
				$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'updatenoconversion',&$base);	
//- read from the query to get the id
				$theQuery="select $keyName from $dbTableName where ";
				$theAnd=NULL;
				foreach ($selectorNameAry as $ctr=>$selectorName){
					$selectorValue=$restoreRowAry[$selectorName];
					//- below needs to be the new jobprofileid not the old one!!! xxxe
					$dbColumnParentSelector_raw=$dbTableInfo['elementsary'][$selectorName]['dbcolumnparentselector'];
					$dbColumnParentSelector=$base->UtlObj->returnFormattedData($dbColumnParentSelector_raw,'boolean','internal',&$base);
					//- foreign key
					$dbColumnForeignKey_raw=$dbTableInfo['elementsary'][$selectorName]['dbcolumnforeignkey'];
					$dbColumnForeignKey=$base->UtlObj->returnFormattedData($dbColumnForeignKey_raw,'boolean','internal',&$base);
					//- foreign table
					$dbColumnForeignTable=$dbTableInfo['elementsary'][$selectorName]['dbcolumnforeigntable'];
					//if ($dbColumnParentSelector && $dbColumnForeignKey){
					if ($dbColumnForeignKey){
						if ($dbColumnForeignTable == 'companyprofile'){
							$selectorValue_new=$this->toCompanyProfileId;
						} else {
							$selectorValue_new=$this->backupKeysAry[$dbColumnForeignTable.'_'.$selectorValue];
						}
						$base->UtlObj->appendValue('debug'," - get $selectorValue_new from backupkeysary[$dbColumnForeignTable".'_'."$selectorValue]<br>",&$base);				
						if ($selectorValue_new == NULL){
							$base->UtlObj->appendValue('debug'," - *** fatal error3: value is null ***<br>",&$base);				
							exit("error3: columnvalue null for: $dbTableName, $columnName, $dbColumnForeignTable, $selectorValue");
						}
						$selectorValue=$selectorValue_new;
					}
					$selectorType=$dbTableInfo['elementsary'][$selectorName]['dbcolumntype'];
					switch ($selectorType){
						case 'varchar':
							$selectorValue="'$selectorValue'";
							break;
						case 'numeric':
							break;
						case 'integer':
							break;
						default:
							echo "selector error -> name:$selectorName, type: $selectorType<br>";
							foreach ($dbTableInfo['elementsary'] as $one=>$two){
								echo "$one: $two<br>";
							}
							exit("invalid selector type '$selectorType' for $dbTableName, $selectorName");	
					} // end switch selectortype
					$theQuery.="$theAnd $selectorName=$selectorValue";
					$theAnd=' and ';
				} // end foreach theselectorary
				$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);	
				$passAry=array();
				$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				$newKeyValue=$workAry[0][$keyName];
				$base->UtlObj->appendValue('debug',"- query(newKey: $newKeyValue): $theQuery<br>",&$base);
				if ($newKeyValue == NULL){
					$base->UtlObj->appendValue('debug'," *** fatal error4: new key value is null ***<br>",&$base);
					exit("*** fatal error: new key value is null ***");
				}			
				//- write the newkey to the backupKeysAry dbtablename_<oldkey> => newkey
				$theBackupKeyName=$dbTableName.'_'.$keyValue;
				$this->backupKeysAry[$theBackupKeyName]=$newKeyValue;
				$base->UtlObj->appendValue('debug'," - save backupkeysary[$theBackupKeyName]=$newKeyValue<br>",&$base);				
				//do- if overwrite something there then error out
			} // end foreach restorerowary		
		} // end foreach restorerowsary
		$base->UtlObj->appendValue('debug',"<br>*** end restoreJob ***<br><br>",&$base);
		$base->DebugObj->printDebug("-rtn:restoreJob",0); //xx (f)
	}
//=========================================================
	function getDbTableInfo($dbConn,$dbTableName,$base){
		$base->DebugObj->printDebug("System001Obj:getDbTableInfo)",0);
		$returnAry=array();
		$theQuery="select * from dbcolumnprofileview where dbtablename='$dbTableName'";
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);	
		$selectorNameAry=array();
		$keyName=NULL;
		$parentSelectorName=NULL;
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$elementsAry=array();
		foreach ($workAry as $ctr=>$dbColumnAry){
			$dbColumnName=$dbColumnAry['dbcolumnname'];
			//- key
			$dbColumnKey_raw=$dbColumnAry['dbcolumnkey'];
			$dbColumnKey=$base->UtlObj->returnFormattedData($dbColumnKey_raw,'boolean','internal',&$base);
			//- selector
			$dbColumnSelector_raw=$dbColumnAry['dbcolumnselector'];
			$dbColumnSelector=$base->UtlObj->returnFormattedData($dbColumnSelector_raw,'boolean','internal',&$base);
			//- parent selector
			$dbColumnParentSelector_raw=$dbColumnAry['dbcolumnparentselector'];
			$dbColumnParentSelector=$base->UtlObj->returnFormattedData($dbColumnParentSelector_raw,'boolean','internal',&$base);
			//- foreign field
			$dbColumnForeignField_raw=$dbColumnAry['dbcolumnforeignfield'];
			$dbColumnForeignField=$base->UtlObj->returnFormattedData($dbColumnForeignField_raw,'boolean','internal',&$base);
			//- foreign key
			$dbColumnForeignKey_raw=$dbColumnAry['dbcolumnforeignkey'];
			$dbColumnForeignKey=$base->UtlObj->returnFormattedData($dbColumnForeignKey_raw,'boolean','internal',&$base);
			//- foreign table
			$dbColumnForeignTable=$dbColumnAry['dbcolumnforeignkey'];
			//- main table
			$dbColumnMainTable=$dbColumnAry['dbcolumnmaintable'];
			//- type
			$dbColumnType=$dbColumnAry['dbcolumntype'];
			//- setup keys			
			if ($dbColumnKey){$keyName=$dbColumnName;}
			if ($dbColumnSelector){$selectorNameAry[]=$dbColumnName;}
			if ($dbColumnParentSelector){$parentSelectorName=$dbColumnName;}
			//- update as valid sql column if passes test
			if (!$dbColumnKey && !$dbColumnForeignField){
				if (!$dbColumnForeignKey || ($dbColumnMainTable==NULL)){
					$elementsAry[$dbColumnName]=$dbColumnAry;
				}
			}
			//$saveStrg="$dbTableName, $dbColumnName: key:($dbColumnKey), selector($dbColumnSelector), parentselector($dbColumnParentSelector)<br>";			
			//$base->UtlObj->appendValue('debug',$saveStrg,&$base);
		}
		$returnAry['dbtablename']=$dbTableName;
		$returnAry['selectornameary']=$selectorNameAry;
		$returnAry['parentselectorname']=$parentSelectorName;
		$returnAry['keyname']=$keyName;
		$returnAry['elementsary']=$elementsAry;
		//echo "dbtablename: $dbTableName<br>";//xxxd
		$base->DebugObj->printDebug("-rtn:getDbTableInfo",0); //xx (f)
		return $returnAry;
	}
//=================================================
	function setupDbParams($base){
		$dbName=$base->paramsAry['dbname'];
		//echo "xxxf0 dbname: $dbName<br>";
		if ($dbName != null){
			$base->UtlObj->appendValue("debug","System001Obj, setupDbParams: setup toDbConn for dbname: $dbName<br>",&$base);
			$this->toDomainName=$dbName;
			$this->toDbConn=$base->ClientObj->getClientConn($this->toDomainName,&$base);
			$base->DbObj->setRemoteDb($this->toDbConn,&$base);
		}
		else {
			$base->UtlObj->appendValue("debug","System001Obj, setupDbParams: dbname is null so dont setup todomain, current todomain: $this->toDomainName<br>",&$base);			
		}
		$base->Plugin001Obj->updateSession(&$base);
	}
//================================================
	function clientLoginAjax($base){
		//echo "I am in clientLoginAjax";//xxxf	
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxxf');
		//$base->FileObj->writeLog('jefftest66',"xxxf0: enter clientLoginAjax",&$base);
		$sendData=$base->paramsAry['senddata'];
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		foreach ($sendDataAry as $ctr=>$valueStr){
			$valueStrAry=explode('|',$valueStr);
			$theName=$valueStrAry[0];
			$theValue=$valueStrAry[1];
			$workAry[$theName]=$theValue;
		}
		$theUser=$workAry['usernameid'];
		$theUserAry=explode('@',$theUser);
		$theUserName=$theUserAry[0];
		$theDomainName=$theUserAry[1];
		if ($theDomainName == ''){
			$checkDir=getcwd();
			if ($checkDir == '/home/jeff/web/Base'){$theDomainName='lindy';}
			else {$theDomainName='jeffreypomeroy.com';}
		}
		$thePassword=$workAry['userpasswordid'];
		//$systemAry=$base->ClientObj->getClientData($theDomainName,&$base);
		//$dbName=$systemAry['dbname'];
		$base->paramsAry['dbname']=$theDomainName;
		$this->setupDbParams(&$base);
		$theQuery="select * from userprofileview where username='$theUserName'";
		$result=$base->ClientObj->queryClientDbTable($theQuery,$this->toDbConn,'read',&$base);
		$passAry=array();	
		$loginAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($loginAry,1,'xxxf');
		$checkPassword=$loginAry[0]['userpassword'];
		if ($checkPassword != null){
			if ($thePassword == $checkPassword){
				$theDelim='`';
				$returnStrg='!!user!!';
				foreach ($loginAry[0] as $name=>$value){
					$returnStrg.=$theDelim.'etchash|'.$name.'|'.$value;
				}
				//echo "returnstrg: $returnStrg";//xxxf
				//$useDelim='';
				//foreach ($workAry[0] as $name=>$value){
				//	$returnStrg.=$theDelim.'etchash|'.$name.'|'.$value;
				//}
				//$base->DebugObj->printDebug($loginAry,1,'xxxf');
				$returnStrg.=$theDelim.'etchash|domainname|'.$theDomainName;
				echo $returnStrg;
			}
			else {
				echo "error|login error";
			}
		}
		else {
			echo "error|login error!";
		}
	}
}
?>
