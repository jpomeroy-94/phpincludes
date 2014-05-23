<?php
class Plugin002Object {
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
	var $base;
	var $theCtr=0;
//========================================
	function Plugin002Object() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
	function buildFormFromDbTable($base){
		$base->DebugObj->printDebug("Plugin002Obj:buildFormFromDbTable('base')",0);
		$dbTableName=$base->paramsAry['tablename'];
		$selectorName1=$base->paramsAry['selectorname1'];
		$selectorName2=$base->paramsAry['selectorname2'];
		$foreignKeyName1=$base->paramsAry['foreignkeyname1'];
		$foreignKeyName2=$base->paramsAry['foreignkeyname2'];
		$rebuildTable=$base->paramsAry['rebuildtable'];
//-
		$dbTableMetaSelectorsAry=array();
		if ($selectorName1 != ""){$dbTableMetaSelectorsAry[$selectorName1]='parent';}
		if ($selectorName2 != ""){$dbTableMetaSelectorsAry[$selectorName2]='';}
		$dbTableMetaForeignKeyAry=array();
		if ($foreignKeyName1 != ""){$dbTableMetaForeignKeyAry[$foreignKeyName1]='';}
		if ($foreignKeyName2 != ""){$dbTableMetaForeignKeyAry[$foreignKeyName2]='';}
//- program stuff
		$listjobname=$base->paramsAry['listjobname'];
		$insertjobname=$base->paramsAry['insertjobname'];
		$updatejobname=$base->paramsAry['updatejobname'];
		$deletejobname=$base->paramsAry['deljobname'];	
//- list column fields
		$inclFieldNames=$base->paramsAry['listjobinfields'];
		$inclFieldNamesDmyAry=explode(",",$inclFieldNames); 	
		$inclFieldNamesAry=array();
		foreach ($inclFieldNamesDmyAry as $key=>$name){
			$inclFieldNamesAry[$name]="";
		}
//- excluded fields for update
		$updateJobExFields=$base->paramsAry['updatejobexfields'];
		$updateJobExFieldsAry=explode(",",$updateJobExFields);
//- excluded fields for insert
		$insertJobExFields=$base->paramsAry['insertjobexfields'];
		$insertJobExFieldsAry=explode(",",$insertJobExFields);
//- run buildDbMetaTable of table 
		if ($dbTableName != ""){
			if($rebuildTable == 'yes'){
				$passAry=array();
				$passAry['dbtablename']=$dbTableName;
				$passAry['dbtablemetaselectorsary']=$dbTableMetaSelectorsAry;
				$passAry['dbtablemetaforeignkeyary']=$dbTableMetaForeignKeyAry;
				//$this->buildDbMetaTable($passAry,&$base);
			}
		}
//- run buildWebTableSetups for table display
		if ($listjobname != ""){$this->buildWebTableSetups($listjobname,$updatejobname,$insertjobname,$deletejobname,$inclFieldNamesAry,&$base);}
//- run buildWebFormSetups for table update
		if ($updatejobname != ""){$this->buildWebFormSetups($updatejobname,$listjobname,$updateJobExFieldsAry,'update_db_from_form',&$base);}
//- run buildWebFormSetups for table insert
		if ($insertjobname != ""){$this->buildWebFormSetups($insertjobname,$listjobname,$insertJobExFieldsAry,'insert_db_from_form',&$base);}
//- run buildWebFormSetups for table delete
		if ($deletejobname != ""){$this->buildWebFormSetups($deletejobname,$listjobname,array(),'delete_db_from_form',&$base);}
		$base->DebugObj->printDebug("-rtn:buildFormFromDbTable",0); //xx (f)
	}
	//======================================= deprecated because uses dbtablemetaprofile
		function deprecatedbuildWebTableSetups($jobname,$updatejobname,$insertjobname,$deletejobname,$allowFieldsAry,$base){
		$base->DebugObj->printDebug("Plugin002Obj:buildWebTableSetups($jobname,$updatejobname,$insertjobname,$allowFieldsAry,'base')",0); //xx (h)
//---------------------------------------------get init stuff
		$firstField=$allowFieldsAry['0'];
		if ($firstField == '*'){$allFlg=true;}
		else {$allFlg=false;}
		$noFields=count($allowFieldsAry);
		$tableName=$base->paramsAry['tablename'];
		$tableNameView=$tableName.'view';
		$jobName=$jobname; // make them all jobName in future
//--------------------------------------------get table definitions
		$dbControlsAry=array();
		$dbControlsAry=array('dbtablename'=>$tableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$tableMetaStuffAry=$dbControlsAry['dbtablemetaary'];
		$noTableStuff=count($tableMetaStuffAry);
//-------------------------------------delete htmlprofile,htmlelementprofile
		$query="select htmlprofileid from htmlprofileview where jobname='$jobname'";
		$result=$base->DbObj->queryTable($query,'select',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($workAry as $ctr=>$rowAry){
			$htmlProfileId=$rowAry['htmlprofileid'];
			$query="delete from htmlelementprofile where htmlprofileid=$htmlProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
			$query="delete from htmlprofile where htmlprofileid=$htmlProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
		}
//------------------------------------delete tableprofile,columnprofile
		$query="select tableprofileid from tableprofileview where jobname='$jobname'";
		$result=$base->DbObj->queryTable($query,'select',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($workAry as $ctr=>$rowAry){
			$tableProfileId=$rowAry['tableprofileid'];
			$query="delete from columnprofile where tableprofileid=$tableProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
			$query="delete from tableprofile where tableprofileid=$tableProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
		}
//---------------------------------------delete jobprofile
		$query="delete from jobprofile where jobname='$jobname'";
		$result=$base->DbObj->queryTable($query,'delete',&$base);
//-------------------------------------insert jobprofile
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	$dbControlsAry['writerowsary'][]=array('jobname'=>$jobname);
	$dbControlsAry['dbtablename']='jobprofile';
	$dbControlsAry['selectornameary']=array('0'=>'jobname');
	$dbControlsAry['keyname']='jobprofileid';
	$successfulUpdate=$base->DbObj->writeToDb($dbControlsAry,&$base);
	$dbControlsAry['selectorary']=array('jobname'=>$jobname);
	$dbControlsAry['useselect']=true;
	$workAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
	$jobProfileId=$workAry[0]['jobprofileid'];
//-------------------------------------insert htmlprofile
   	$dbControlsAry=array();
	$pos=strpos($tableName,'profile',0);
	//if ($pos !== false){ $workHtmlName='admindisplay.htm';}
	//else {$workHtmlName='basicdisplay.htm';}
	$workHtmlName='basicdisplay.htm';
	$dbControlsAry['writerowsary']=array();
	$rowAry=array();
	$rowAry['jobprofileid']=$jobProfileId;
	//$rowAry['htmlno']=1;
	$rowAry['htmlname']=$workHtmlName;
	$rowAry['htmltitle']=$jobname;
	$dbControlsAry['writerowsary'][]=$rowAry;
	$dbControlsAry['dbtablename']='htmlprofile';
	$writeUpdateFlag=$base->DbObj->writeToDb($dbControlsAry,&$base);
	$dbControlsAry['useselect']=true;
	$dbControlsAry['selectorary']=array('jobprofileid'=>$jobProfileId);
	$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
	$htmlProfileId=$rowsAry[0]['htmlprofileid'];
	//---- init of rows to write
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	//------------------------------------insert htmlelementprofile (cssstylesheet)
	$name='cssstylesheet';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
	$rowAry['type']='csslink';
	$rowAry['joblink']='/styles/basicdisplay.css';
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//------------------------------------insert htmlelementprofile (return to main)
	$name='returntoedit';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
	$rowAry['label']='Return to main';
	$rowAry['joblink']='mainmenu';
	$rowAry['type']='url';
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (insert row)
	$name='insertrow';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
	$rowAry['label']="Insert record into $tableName";
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['joblink']=$insertjobname;
	$rowAry['type']='url';
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (firstpagebutton)
	$name='firstpagebutton';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['type']='image';
	$rowAry['joblink']='/images/firstpagebutton.bmp';
	$rowAry['htmlelementeventattributes']="onclick=%dblqt%pageFirst();%dblqt%";
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (pageup)
	$name='pageup';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['type']='image';
	$rowAry['joblink']='/images/Previous.gif';
	$rowAry['htmlelementeventattributes']="onclick=%dblqt%pagePrevious();%dblqt%";
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (selectit)
	$name='selectit';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['type']='inputselect';
	$rowAry['htmlelementeventattributes']="onkeyup=%dblqt%pageSelect(%sglqt%selectit%sglqt%);%dblqt%";
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (pagedown)
	$name='pagedown';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['type']='image';
	$rowAry['joblink']='/images/Next.gif';
	$rowAry['htmlelementeventattributes']="onclick=%dblqt%pageNext();%dblqt%";
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//-------------------------------------insert htmlelementprofile (lastpagebutton)
	$name='lastpagebutton';
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
//xx if you dont build the insert fnction then this is null!!!
	$rowAry['type']='image';
	$rowAry['joblink']='/images/lastpagebutton.bmp';
	$rowAry['htmlelementeventattributes']="onclick=%dblqt%pageLast();%dblqt%";
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
//------------------------------------write htmlelementprofile rows
	$dbControlsAry['dbtablename']='htmlelementprofile';
	$successfulUpdate=$base->DbObj->writeToDb($dbControlsAry,&$base);
//------------------------------------- insert tableprofile
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	$rowAry=array();
	$rowAry['jobprofileid']=$jobProfileId;
	$rowAry['tablename']='basictable';
	$rowAry['dbtablename']=$tableName;
	$rowAry['tableid']=$tableName;
	$rowAry['pagesize']=13;
	//$rowAry['background']='/images/paper001.jpg';
	//$rowAry['tablewidth']='100%';
	$dbControlsAry['writerowsary'][]=$rowAry;
	$dbControlsAry['dbtablename']='tableprofile';
	//$base->DebugObj->printDebug($dbControlsAry,1,'dbca');//xxx
	//$base->DebugObj->setPrio(-1,-1);//xxx
	$wroteOk=$base->DbObj->writeToDb($dbControlsAry,&$base);
	//echo "wroteok: $wroteOk";//xxx
	//$base->DebugObj->printDebug($base->errorProfileAry,1,'error');//xxx
	$dbControlsAry['useselect']=true;
	$dbControlsAry['selectorary']=array('jobprofileid'=>$jobProfileId,'tablename'=>'basictable');
	$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
	//$base->DebugObj->printDebug($rowsAry,1,'ra');//xxx
	$tableProfileId=$rowsAry[0]['tableprofileid'];
//------------------------------ insert columnprofile (allowFieldsAry)
		$columnNo=0;
		if ($allFlg){$useAry=$tableMetaStuffAry;}
		else {$useAry=$allowFieldsAry;}
		$dbControlsAry=array();
		$dbControlsAry['dbtablename']='columnprofile';
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbControlsAry['writerowsary']=array();
		foreach ($useAry as $name=>$dmy){
			if (array_key_exists($name,$tableMetaStuffAry)){
				$columnNo++;
				$rowAry=array();
				$rowAry['tableprofileid']=$tableProfileId;
				$rowAry['columnname']=$name;
				$rowAry['columntitle']=$name;
				$rowAry['columntype']='text';
				$rowAry['columnno']=$columnNo;	
				$rowAry['selectmode']=true;
				$dbControlsAry['writerowsary'][]=$rowAry;
			} //end if
		} //end foreach
//----------------------------------------insert columnprofile 'upd' column
		$columnNo++;
		$rowAry=array();
		$rowAry['tableprofileid']=$tableProfileId; 
		$rowAry['columnno']=$columnNo; 
		$rowAry['columnname']=$tableName.'id_1'; 
		$rowAry['columntype']='url'; 
//xx - if you don't build the updatejobname then this is null!
		$rowAry['joblink']=$updatejobname; 
		$rowAry['urlname']='upd';
		$dbControlsAry['writerowsary'][]=$rowAry;
//------------------------------------------insert columnprofile 'del' column
		$columnNo++;
		$rowAry=array();
		$rowAry['tableprofileid']=$tableProfileId;
		$rowAry['columnno']=$columnNo;
		$rowAry['columnname']=$tableName.'id_2';
		$rowAry['columntype']='url';
//xxa - if you dont build the deletejobname routine then this is null
		$rowAry['joblink']=$deletejobname;
		$rowAry['urlname']='del';
		$dbControlsAry['writerowsary'][]=$rowAry;
		$writeStatus=$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->DebugObj->printDebug("-rtn:buildWebTableSetups",0); //xx (f)
	}
//======================================= deprecated because uses dbtablemetaprofile   
	function deprecatedbuildWebFormSetups($jobName,$redirectJobName,$exFieldsAry,$operationName,$base){
		$base->DebugObj->printDebug("Plugin002Obj:buildWebFormSetups($jobName,$redirectJobName,$exFieldsAry,$operationName,'base')",0); //xx (h)
//-----------------------------------------------do init stuff
		$tableName=$base->paramsAry['tablename'];
//-----------------------------------------------get table stuff 
		$tableMetaStuffAry=$this->getTableMetaStuff($tableName,'asctr',&$base);
		$noTableStuff=count($tableMetaStuffAry);
//---------------------------------------delete formprofile,formelementprofile
		$query="select formprofileid from formprofileview where jobname='$jobName'";
		$result=$base->DbObj->queryTable($query,'select',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($workAry as $ctr=>$retrievedRowAry){
			$formProfileId=$retrievedRowAry['formprofileid'];
			$query="delete from formelementprofile where formprofileid=$formProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
			$query="delete from formprofile where formprofileid=$formProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
		}
//---------------------------------------delete htmlelementprofile, htmlprofile
		$query="select htmlprofileid from htmlprofileview where jobname='$jobName'";
		$result=$base->DbObj->queryTable($query,'select',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($workAry as $ctr=>$rowAry){
			$htmlProfileId=$rowAry['htmlprofileid'];
			$query="delete from htmlelementprofile where htmlprofileid=$htmlProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
			$query="delete from htmlprofile where htmlprofileid=$htmlProfileId";
			$result=$base->DbObj->queryTable($query,'delete',&$base);
		}
//-------------------------------------------insert/update jobprofile
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	$dbControlsAry['writerowsary'][]=array('jobname'=>$jobName);
	$dbControlsAry['dbtablename']='jobprofile';
	$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
	$writeStatus=$base->DbObj->writeToDb($dbControlsAry,&$base);
	$dbControlsAry['selectorary']=array('jobname'=>$jobName);
	$dbControlsAry['useselect']=true;
	$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
	$jobProfileId=$rowsAry[0]['jobprofileid'];
//--------------------------------------------insert htmlprofile
	$dbControlsAry=array();
	$pos=strpos($tableName,'profile',0);
	//if ($pos !== false){ $workHtmlName='admindisplay.htm';}
	//else {$workHtmlName='basicdisplay.htm';}
	$workHtmlName='basicdisplay.htm';
	$dbControlsAry['writerowsary']=array();
	$dbControlsAry['writerowsary'][]=array('jobprofileid'=>$jobProfileId,'htmlname'=>'basicform.htm','htmltitle'=>$jobName);
	$dbControlsAry['dbtablename']='htmlprofile';
	$writeStatus=$base->DbObj->writeToDb($dbControlsAry,&$base);
	$dbControlsAry['selectorary']=array('jobprofileid'=>$jobProfileId);
	$dbControlsAry['useselect']=true;
	$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
	$htmlProfileId=$rowsAry[0]['htmlprofileid'];
//--------------------------------------------- insert htmlelementprofile
	$name='returntolist';
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	$rowAry=array();
	$rowAry['htmlprofileid']=$htmlProfileId;
	$rowAry['label']='Return to display';
	$rowAry['joblink']=$redirectJobName;
	$rowAry['type']='url';
	$rowAry['htmlelementno']=1;
	$rowAry['htmlelementname']=$name;
	$dbControlsAry['writerowsary'][]=$rowAry;
	$dbControlsAry['dbtablename']='htmlelementprofile';
	$writeStatus=$base->DbObj->writeToDb($dbControlsAry,&$base);
//---------------------------------------------insert formprofile
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
// removed 'selectorname'=$selectorName
	$dbControlsAry['writerowsary'][]=array('jobprofileid'=>$jobProfileId,'formno'=>1,'formname'=>'basicform','tablename'=>$tableName,'redirect'=>$redirectJobName,'formoperation'=>$operationName,'formtableformat'=>'2');
	$dbControlsAry['dbtablename']='formprofile';
	$writeStatus=$base->DbObj->writeToDb($dbControlsAry,&$base);
	$dbControlsAry['selectorary']=array('jobprofileid'=>$jobProfileId);
	$dbControlsAry['useselect']=true;
	$rowsAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
//----------------------------------------------insert formelementprofile 
	$formProfileId=$rowsAry[0]['formprofileid'];
	$formName='basicform';
		$formElementNo=0;
//!!! always exclude first column: {tablename}id
	$dbControlsAry=array();
	$dbControlsAry['writerowsary']=array();
	$dbControlsAry['dbtablename']='formelementprofile';
	for ($ctr=1;$ctr<$noTableStuff;$ctr++){
			$metaName=$tableMetaStuffAry[$ctr]['dbcolumnname'];
			$metaType=$tableMetaStuffAry[$ctr]['dbcolumntype'];
			$formElementName=$metaName;
			$formElementLabel=$metaName;
			$formElementOptionLabelName='';
			$formElementOptionSql='';
			$formElementOptionValue='';
			$formElementSubType='';
			if ($operationName == 'delete_db_from_form'){$formElementType="display";}
			else {
				switch ($metaName){
				case 'jobprofileid':
					$formElementType='select';
					$formElementOptionSql='select jobname,jobprofileid from jobprofile order by jobname';
					$formElementOptionLabelName='jobname';
					$formElementOptionValueName='jobprofileid';
					break;
				case 'formprofileid':
					$formElementType='select';
					$formElementOptionSql='select jobname,formname,formprofileid from formprofileview order by jobname, formname';
					$formElementOptionLabelName='jobname,formname';
					$formElementOptionValueName='formprofileid';
					break;
				case 'htmlprofileid':
					$formElementType='select';
					$formElementOptionSql='select jobname,htmlname,htmlprofileid from htmlprofileview order by jobname, htmlname';
					$formElementOptionLabelName='jobname,htmlname';
					$formElementOptionValueName='htmlprofileid';
					break;
				case 'tableprofileid':
					$formElementType='select';
					$formElementOptionSql='select jobname,tablename,tableprofileid from tableprofileview order by jobname, tablename';
					$formElementOptionLabelName='jobname,tablename';
					$formElementOptionValueName='tableprofileid';
					break;
				default:	
//xx fix why this is 'bool'
					if ($metaType == 'bool'){$metaType='boolean';}
					switch ($metaType){
					case 'boolean':
						$formElementType='select';
						$formElementOptionSql="select selectoptionlabel,selectoptionvalue from selectoptionprofile where selectoptionname=''yesno'' order by selectoptionorder";
						$formElementOptionLabelName='selectoptionlabel';
						$formElementOptionValueName='selectoptionvalue';
						break;
					case 'numeric':
						$formElementType="input";
						$formElementSubType="text";
						break;
					default:
						$formElementType="input";
						$formElementSubType="text";
					} // end switch on formeltype
				} // end switch on name
			} // end else
				$chkBadPos=strpos(('chk'.$metaName),'bad',0);
				if (!in_array($metaName,$exFieldsAry) && $chkBadPos<=0){
				$formElementNo++;
				$dbControlsAry['writerowsary'][]=array('formprofileid'=>$formProfileId,'formelementno'=>$formElementNo,'formelementname'=>$formElementName,'formelementtype'=>$formElementType,'formelementsubtype'=>$formElementSubType,'formelementlabel'=>$formElementLabel,'formelementoptionsql'=>$formElementOptionSql,'formelementoptionlabelname'=>$formElementOptionLabelName,'formelementoptionvaluename'=>$formElementOptionValueName);
			} // end if
		} // end for
		$formElementNo++;
		switch ($operationName){
			case 'delete_db_from_form':
				$requestValue="delete from table";
				break;
			case 'update_db_from_form':
				$requestValue="update table";
				break;
			case 'insert_db_from_form':
				$requestValue="insert into table";
				break;
			default:
				$requestValue="submitrequest";
		}
		$formElementName=$requestValue;	
		$formElementLabel=$requestValue;
//need to put in form name
		$dbControlsAry['writerowsary'][]=array('formprofileid'=>$formProfileId,'formno'=>1,'formelementno'=>$formElementNo,'formelementname'=>$formElementName,'formelementtype'=>'button','formelementsubtype'=>'submit','formelementlabel'=>$formElementLabel);
		$writtenRowsAry=$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->DebugObj->printDebug("-rtn:Plugin002Obj:buildWebFormSetups",0); //xx (f)
 }
 //======================================= 
	function getTableMetaStuff($dbTableName,$flag,$base){
		$base->DebugObj->printDebug("Plugin002Obj:getTableMetaStuff($dbTableName,'base')",0);
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableReturnAry=$dbControlsAry['dbtablemetaary'];
		$returnAry=array();
 		$query="select * from $dbTableName";
		$res=$base->DbObj->queryTable($query,'retrieve',&$base);
	 	$noFields = pg_num_fields($res);
 	 	for ($ctr = 0; $ctr < $noFields; $ctr++) {
  	 	$fieldName = pg_field_name($res, $ctr);
   	 	$fieldPrintLen = pg_field_prtlen($res, $fieldName);
   	 	$fieldMaxLen = pg_field_size($res, $ctr);
   	 	$fieldType = pg_field_type($res, $ctr);
			if ($flag == 'asname') {
				if (!array_key_exists($fieldName,$dbTableReturnAry)){
		 			$returnAry[$fieldName]=array("dbtablemetacolumnname"=>$fieldName,"dbtablemetatype"=>$fieldType);
				}
				else {
					$returnAry[$fieldName]=$dbTableReturnAry[$fieldName];
				}
			}
			else {
				if (!in_array($fieldName,$dbTableReturnAry)){
		 			$returnAry[$ctr]=array("dbtablemetacolumnname"=>$fieldName,"dbtablemetatype"=>$fieldType);
				}
				else {
					$returnAry[$ctr]=$dbTableReturnAry[$fieldName];
				}
			}
	 	}
		$base->DebugObj->printDebug("-rtn:getTableMetaStuff",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function runBashCommand($applicationPassedAry,$base){
		$base->DebugObj->printDebug("Plugin002Obj:runBashCommand($applicationPassedAry,'base')",0);
		$command=$applicationPassedAry['pluginargs'];
		$type='r';
		$handle=popen("$command",$type);
        $output = '';
		while(!feof($handle)) {$output .= fread($handle, 1024);}
        pclose($handle);
          $output=explode("\n",$output);
          $output_formatted=array();
          foreach ($output as $rowNo=>$valueStr){
           	$pos=strpos($valueStr,'  ',0);
            $ctr=0;
           	$valueStr=trim($valueStr);
             while ($pos!==false && $ctr<10){
            	$ctr++;
           		$valueStr=str_replace('  ',' ',$valueStr); 
           		$pos=strpos($valueStr,'  ',0);          		
            }
            $valueAry=explode(' ',$valueStr);
            $outputFormatted[$rowNo]=$valueAry;
            }
            $applicationPassedAry=array('outputformatted'=>$outputFormatted);
            $base->DebugObj->printDebug("-rtn:runBashCommand",0); //xx (f)
            return $applicationPassedAry;
	}
	//=======================================
	function storeBashCommandResults($applicationPassedAry,$base){
		$base->DebugObj->printDebug("Plugin002Obj:storeBashCommandResults($applicationPassedAry,'base')",0);
		$resultsTableName=$applicationPassedAry['pluginargs'];
		$dbControlsAry=array('dbtablename'=>$resultsTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$outputFormatted=$applicationPassedAry['outputformatted'];
		$writeRowsAry=array();
		foreach ($outputFormatted as $rowNo=>$rowValueAry){
			if ($rowNo>0){
				$foundSomeData=false;
				$rowWork=array();
				foreach ($dbTableMetaAry as $columnName=>$columnValueAry){
					$inputCode=$columnValueAry['inputcode'];
					if ($inputCode=='d'){
						$inputValue=date("m/d/20y");
						}
					else {
						$inputValue=$outputFormatted[$rowNo][$inputCode];
						if ($inputValue != ''){$foundSomeData=true;}	
					}
					$rowWork[$columnName]=$inputValue;		
				}
				if ($foundSomeData){$writeRowsAry[]=$rowWork;}
			}
		}
		$dbControlsAry['writerowsary']=$writeRowsAry;
		//$base->DebugObj->printDebug($dbControlsAry,1,'dbca');//xxx
		//$base->DebugObj->setPrio(-1,-1);//xxx
		$successfullUpdate=$base->DbObj->writeToDb($dbControlsAry,&$base);
		//$base->DebugObj->setPrio(0,0);//xxx
		$base->DebugObj->printDebug("-rtn:storeBashCommandResults",0); //xx (f)
		return $applicationPassedAry;
	}
	//======================================= soon to be deprecated
	function deprecatedmoveElementPos($base){
		$base->DebugObj->printDebug("Plugin002Obj:moveFormElementPos('base')",0); //xx (h)
		$formProfileId=$base->paramsAry['formprofileid'];
		$selectFormElementProfileId=$base->paramsAry['formelementprofileid'];
		$direction=$base->paramsAry['direction'];
		$query="select formelementno,formelementprofileid,formelementname,formprofileid from formelementprofile where formprofileid=$formProfileId order by formelementno";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$formNoAry=$base->UtlObj->tableToHashAryV3($result);
		$lastFormElementProfileId=0;
		$noRows=count($formNoAry);
		$bypass=false;
		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
			$formElementProfileId=$formNoAry[$rowCtr]['formelementprofileid'];
			$formElementNo=$rowCtr+1;
			if (!$bypass){
				$formNoAry[$rowCtr]['formelementno']=$formElementNo;
			}
			else {$bypass=false;}	
			if ($selectFormElementProfileId == $formElementProfileId){
				if ($direction == 'up' && $rowCtr>0){
					$formNoAry[$rowCtr]['formelementno']=($formElementNo-1);
					$formNoAry[$rowCtr-1]['formelementno']=$formElementNo;
				}
				if ($direction == 'down' && $rowCtr<($noRows-1)){
					$formNoAry[$rowCtr]['formelementno']=$formElementNo+1;
					$formNoAry[$rowCtr+1]['formelementno']=$formElementNo;
					$bypass=true;	
				}
			} // end else
		} // end for
		$dbControlsAry=array('dbtablename'=>'formelementprofile');
		$dbControlsAry['writerowsary']=$formNoAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->DebugObj->printDebug("-rtn:moveFormElementPos",0); //xx (f)
	}
	//======================================= replaces moveElementPos($base);
	function moveElementCtr($base){
		$base->DebugObj->printDebug("Plugin002Obj:moveElementCtr('base')",0); //xx (h)
		$dbTableName=$base->paramsAry['dbtablename'];
		$parentDbTableName=$base->paramsAry['parentdbtablename'];
		$dbTablePrefix=str_replace('profile','',$dbTableName);
		$parentDbTablePrefix=str_replace('profile','',$parentDbTableName);
		$parentKeyId=$base->paramsAry['parentkeyid'];
		$keyId=$base->paramsAry['keyid'];
		$direction=$base->paramsAry['direction'];
		$increment=$base->paramsAry['increment'];
		if ($increment<1){$increment=1;}
		if (array_key_exists('parentkeyid2',$base->paramsAry)){
			$parentKeyId2=$base->paramsAry['parentkeyid2'];
			$parentKeyId2_sqlformat = $base->UtlObj->returnFormattedData ($parentKeyId2,'numeric','sql');
			$parentKeyName2=$base->paramsAry['parentkeyname2'];	
			if ($parentKeyId2_sqlformat == 'NULL' || $parentKeyId2_sqlformat == NULL){
				$insertParentKey2=" and $parentKeyName2 is NULL";
			} else {
				$insertParentKey2=" and $parentKeyName2=$parentKeyId2_sqlformat";
			}
		} 
		else { $insertParentKey2='';}
		$query="select ".$parentDbTableName."id,".$dbTablePrefix."no,".$dbTablePrefix."profileid,".$dbTablePrefix."name from ".$dbTableName." where ".$parentDbTablePrefix."profileid=$parentKeyId $insertParentKey2 order by ".$dbTablePrefix."no";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$elementNoAry=$base->UtlObj->tableToHashAryV3($result);
		$lastElementProfileId=0;
		$noRows=count($elementNoAry);
		$bypass=false;
		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
			$elementKeyId=$elementNoAry[$rowCtr][$dbTableName.'id'];
			$elementNo=$rowCtr+1;
			if (!$bypass){
				$elementNoAry[$rowCtr][$dbTablePrefix.'no']=$elementNo;
			}
			else {$bypass=false;}	
			if ($keyId == $elementKeyId){
				if ($direction == 'up' && $rowCtr>0){
					$elementNoAry[$rowCtr][$dbTablePrefix.'no']=($elementNo-1);
					$elementNoAry[$rowCtr-1][$dbTablePrefix.'no']=$elementNo;
				}
				if ($direction == 'down' && $rowCtr<($noRows-1)){
					$elementNoAry[$rowCtr][$dbTablePrefix.'no']=$elementNo+1;
					$elementNoAry[$rowCtr+1][$dbTablePrefix.'no']=$elementNo;
					$bypass=true;	
				}
			} // end if keyid==elementkeyid
		} // end for rowctr
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$dbControlsAry['writerowsary']=$elementNoAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->DebugObj->printDebug("-rtn:moveFormElementPos",0); //xx (f)
	}
	//=======================================
	function cloneJob($base){
		$base->DebugObj->printDebug("Plugin002Obj:cloneJob('base')",0); //xx (h)
		$paramsAry=$base->paramsAry;
		$sourceJobName=$paramsAry['sourcejobname'];
		$newJobName=$paramsAry['newjobname'];
	    $dbControlsAry=array('dbtablename'=>'jobprofile');
	    $dbControlsAry['selectorary']=array('jobname'=>$sourceJobName);
   		$dbControlsAry['useselect']=true;
    	$dataAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
    	$sourceJobProfileId=$dataAry[0]['jobprofileid'];
    	//echo "sourcejobname: $sourceJobName, sourcejobprofileid: $sourceJobProfileId<br>";//xxx
    	//$base->DebugObj->printDebug($dataAry,1,'dataary');//xxx
		$query="select * from jobprofile where jobname='$newJobName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$tstAry=$base->UtlObj->tableToHashAryV3($result);
		$noFiles=count($tstAry);
		if ($noFiles>0){echo "$newJobName: is already on file!";}
		else {$this->doCloning($sourceJobProfileId,$newJobName,&$base);}
		$base->DebugObj->printDebug("-rtn:cloneJob",0); //xx (f)
	}
	//========================================
	function doCloning($sourceJobProfileId,$newJobName,&$base){
		$base->DebugObj->printDebug("Plugin002Obj:doCloning($sourceJobProfileId,$newJobName,&'base')",0); //xx (h)
	//--- copy/clone jobprofile old to new
		$query="select * from jobprofile where jobprofileid=$sourceJobProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$writeRowsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		unset($writeRowsAry[0]['jobprofileid']);
		$writeRowsAry[0]['jobname']=$newJobName;
		$dbControlsAry=array('dbtablename'=>'jobprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$dbTableName='jobprofile';$selColName='jobname';$selColValue=$newJobName;$keyColName='jobprofileid';
		$newJobProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
		$newJobProfileId=$newJobProfileIdAry[0];
		//$base->DebugObj->printDebug($newJobProfileIdAry,1,'njpa');//xxx
		//echo "newjobprofileid: $newJobProfileId<br>";//xxx
 	//----------------------------------- copy/clone htmlprofile need to fix for multiple html records!!!
 		//- get it
 		$dbTableName='htmlprofile';
 		$selectColName='jobprofileid';$selectColValue=$sourceJobProfileId;
 		//echo "source sourcejobprofileid: $sourceJobProfileId<br>";//xxx
 		//$base->DebugObj->setPrio(-1,-1);//xxx
  		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
   		$sourceHtmlProfileIdAry=array();
  		foreach ($writeRowsAry as $rowNo=>$rowValueAry){
  			$sourceHtmlProfileIdAry[]=$rowValueAry['htmlprofileid'];
  		}
 		//-change it
		$updateColumnName='jobprofileid';$updateColumnValue=$newJobProfileId;$delColumnName='htmlprofileid';
		//$base->DebugObj->printDebug($writeRowsAry,1,'wra1');//xxx
		$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
		//$base->DebugObj->printDebug($writeRowsAry,1,'wra2');//xxx
  		//-write it
 		$dbControlsAry=array('dbtablename'=>$dbTableName);
 		$dbControlsAry['writerowsary']=$writeRowsAry;
 		$base->DbObj->writeToDb($dbControlsAry,&$base);
  		//- get key for it
 		$selColName='jobprofileid';$selColValue=$newJobProfileId;$keyColName='htmlprofileid';
		$newHtmlProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
		//$base->DebugObj->setPrio(-1,-1);//xxx
 	//---------------------------- copy/clone htmlelementprofile
 		//- get them
 		$noRows=count($sourceHtmlProfileIdAry);
 		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
 			$sourceHtmlProfileId=$sourceHtmlProfileIdAry[$rowCtr];
 			$newHtmlProfileId=$newHtmlProfileIdAry[$rowCtr];
 	  		$dbTableName='htmlelementprofile';$selectColName='htmlprofileid';$selectColValue=$sourceHtmlProfileId;
 			$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
  			if (count($writeRowsAry)>0){
				//- change them
	 			$updateColumnName='htmlprofileid';$updateColumnValue=$newHtmlProfileId;$delColumnName='htmlelementprofileid';
				$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
 	 			//-write them
 				$dbControlsAry=array('dbtablename'=>$dbTableName);
 				$dbControlsAry['writerowsary']=$writeRowsAry;
 				$base->DbObj->writeToDb($dbControlsAry,&$base);
 			}
 		}
 	//---------------------------- copy/clone tableprofile xx - has an error
		//- get it
		$dbTableName='tableprofile';$selectColName='jobprofileid';$selectColValue=$sourceJobProfileId;
  		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
  		$sourceTableProfileIdAry=array();
  		$noRows=count($writeRowsAry);
  		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
  			$sourceTableProfileIdAry[]=$writeRowsAry[$rowCtr]['tableprofileid'];
  		}
	   	$sourceTableProfileId=$sourceTableProfileIdAry[0];
 		if ($sourceTableProfileId != ''){
	 		//- change it
	 		$updateColumnName='jobprofileid';$updateColumnValue=$newJobProfileId;$delColumnName='tableprofileid';
			$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
			//- write it
 			$dbControlsAry=array('dbtablename'=>$dbTableName);
 			$dbControlsAry['writerowsary']=$writeRowsAry;
 			$base->DbObj->writeToDb($dbControlsAry,&$base);
			//- get keys for it
 			$selColName='jobprofileid';$selColValue=$newJobProfileId;$keyColName='tableprofileid';
			$newTableProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
	//----------------------------- copy/clone columnprofile
			//- get them
			$noIdRows=count($newTableProfileIdAry);
			for ($idRowsCtr=0;$idRowsCtr<$noIdRows;$idRowsCtr++){
				$sourceTableProfileId=$sourceTableProfileIdAry[$idRowsCtr];
				$newTableProfileId=$newTableProfileIdAry[$idRowsCtr];
		 		$dbTableName='columnprofile';$selectColName='tableprofileid';$selectColValue=$sourceTableProfileId;
		 		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
	 			$noRows=count($writeRowsAry);
	 			if ($noRows>0){
			 		//- change them
	 				$updateColumnName='tableprofileid';$updateColumnValue=$newTableProfileId;$delColumnName='columnprofileid';
					$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
			 		//-write them
		 			$dbControlsAry=array('dbtablename'=>$dbTableName);
		 			$dbControlsAry['writerowsary']=$writeRowsAry;
		 			$base->DbObj->writeToDb($dbControlsAry,&$base);
		 		} // end if norows>0
			} // end for		 		
 		} // end if id != ''
 //---------------------------- copy/clone menuprofile -new
		//- get it
		$dbTableName='menuprofile';$selectColName='jobprofileid';$selectColValue=$sourceJobProfileId;
  		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
  		$sourceTableProfileIdAry=array();
  		$noRows=count($writeRowsAry);
  		for ($rowCtr=0;$rowCtr<$noRows;$rowCtr++){
  			$sourceTableProfileIdAry[]=$writeRowsAry[$rowCtr]['menuprofileid'];
  		}
	   	$sourceTableProfileId=$sourceTableProfileIdAry[0];
 		if ($sourceTableProfileId != ''){
	 		//- change it
	 		$updateColumnName='jobprofileid';$updateColumnValue=$newJobProfileId;$delColumnName='menuprofileid';
			$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
			//- write it
 			$dbControlsAry=array('dbtablename'=>$dbTableName);
 			$dbControlsAry['writerowsary']=$writeRowsAry;
 			$base->DbObj->writeToDb($dbControlsAry,&$base);
			//- get keys for it
 			$selColName='jobprofileid';$selColValue=$newJobProfileId;$keyColName='menuprofileid';
			$newTableProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
	//----------------------------- copy/clone menuelementprofile
			//- get them
			$noIdRows=count($newTableProfileIdAry);
			for ($idRowsCtr=0;$idRowsCtr<$noIdRows;$idRowsCtr++){
				$sourceTableProfileId=$sourceTableProfileIdAry[$idRowsCtr];
				$newTableProfileId=$newTableProfileIdAry[$idRowsCtr];
		 		$dbTableName='menuelementprofile';$selectColName='menuprofileid';$selectColValue=$sourceTableProfileId;
		 		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
	 			$noRows=count($writeRowsAry);
	 			if ($noRows>0){
			 		//- change them
	 				$updateColumnName='menuprofileid';$updateColumnValue=$newTableProfileId;$delColumnName='menuelementprofileid';
					$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
			 		//-write them
		 			$dbControlsAry=array('dbtablename'=>$dbTableName);
		 			$dbControlsAry['writerowsary']=$writeRowsAry;
		 			$base->DbObj->writeToDb($dbControlsAry,&$base);
		 		} // end if norows>0
			} // end for		 		
 		} // end if id != ''
	//----------------------------- copy/clone formprofile 
		//- get it
		$dbTableName='formprofile';$selectColName='jobprofileid';$selectColValue=$sourceJobProfileId;
 		$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
 		$sourceFormProfileAry=array();
 		foreach ($writeRowsAry as $rowCtr=>$rowValueAry){
  			$sourceFormProfileIdAry[]=$rowValueAry['formprofileid'];
 		}
 		if ($sourceFormProfileIdAry[0] != ''){
			//- change it
 			$updateColumnName='jobprofileid';$updateColumnValue=$newJobProfileId;$delColumnName='formprofileid';
			$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
 		//- write it
  			$dbControlsAry=array('dbtablename'=>$dbTableName);
 			$dbControlsAry['writerowsary']=$writeRowsAry;
 			$base->DbObj->writeToDb($dbControlsAry,&$base);
  		//- get key for it
 			$selColName='jobprofileid';$selColValue=$newJobProfileId;$keyColName='formprofileid';
			$newFormProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
	//---------------------------------- copy/clone formelementprofile
			$noForms=count($newFormProfileIdAry);
			for ($formCtr=0;$formCtr<$noForms;$formCtr++){
				$sourceFormProfileId=$sourceFormProfileIdAry[$formCtr];
				$newFormProfileId=$newFormProfileIdAry[$formCtr];
				//- get them
 				$dbTableName='formelementprofile';$selectColName='formprofileid';$selectColValue=$sourceFormProfileId;
 				$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
 				$noRows=count($writeRowsAry);
 				if ($noRows>0){
	 			//- change them
 					$updateColumnName='formprofileid';$updateColumnValue=$newFormProfileId;$delColumnName='formelementprofileid';
					$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
 				//-write them
 					$dbControlsAry=array('dbtablename'=>$dbTableName);
 					$dbControlsAry['writerowsary']=$writeRowsAry;
 					$base->DbObj->writeToDb($dbControlsAry,&$base);
 				} // end if norows>0
 	//---------------------------------- copy/clone formdataprofile
				//- get them
 				$dbTableName='formdataprofile';$selectColName='formprofileid';$selectColValue=$sourceFormProfileId;
 				$writeRowsAry=$this->getCloneData($dbTableName,$selectColName,$selectColValue,&$base);
 				$noRows=count($writeRowsAry);
 				if ($noRows>0){
	 				//- change them
 					$updateColumnName='formprofileid';$updateColumnValue=$newFormProfileId;$delColumnName='formdataprofileid';
					$writeRowsAry=$this->changeCloneData($writeRowsAry,$updateColumnName,$updateColumnValue,$delColumnName,&$base);
 					//-write them
 					$dbControlsAry=array('dbtablename'=>$dbTableName);
 					$dbControlsAry['writerowsary']=$writeRowsAry;
 					$base->DbObj->writeToDb($dbControlsAry,&$base);
 				} // end if norows>0
 			} // end if id != ''
 		} // end for
 		$base->DebugObj->printDebug("-rtn:doCloning",0); //xx (f)
 	}
	//----------------------------------------
	function getCloneData($dbTableName,$selColName,$selColValue,$base){
		$base->DebugObj->printDebug("Plugin002Obj:getCloneData($dbTableName,$selColName,$selColValue,'base')",0); //xx (h)
		$query="select * from $dbTableName where $selColName=$selColValue";
 		$result=$base->DbObj->queryTable($query,'read',&$base);
 		$passAry=array();
 		//$passAry['delim1']='columnname';
 		$writeRowsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
 		$base->DebugObj->printDebug("-rtn:getCloneData",0); //xx (f)
 		return $writeRowsAry;
	}
	//----------------------------------------
	function changeCloneData($dataAry,$updateColumnName,$updateColumnValue,$delColumnName,$base){
		$base->DebugObj->printDebug("Plugin002Obj:changeCloneData($dataAry,$updateColumnName,$updateColumnValue,$delColumnName,'base')",0); //xx (h)
		foreach ($dataAry as $rowNo=>$valueAry){
			//- get rid of keyid so will not overlay old, but build new
 			unset($dataAry[$rowNo][$delColumnName]);
 			//- change the updatecolumn with the updatename
 			$dataAry[$rowNo][$updateColumnName]=$updateColumnValue;	
 		}
 		$base->DebugObj->printDebug("-rtn:changeCloneData",0); //xx (f)	
		return $dataAry;
	}
	//----------------------------------------
	function restoreFromBackup($base){
		$companyName=$base->paramsAry['companyname'];
		$fileName=$companyName.'_jobs.txt';
		$dirPath=$base->systemAry['tmplocal'];
		$filePath=$dirPath.'/'.$fileName;
		$restoreFileAry=$base->FileObj->getFileArray($filePath);
		$base->FileObj->initLog('companyrestore.log',&$base);
		$base->FileObj->writeLog('companyrestore.log','log for restore of '.$fileName,&$base);
		$keyValuesAry=array();
		//$base->DebugObj->printDebug($restoreFileAry,1,'xxxrfa');
		//exit();
		foreach ($restoreFileAry as $ctr=>$runLine){
			$runLineWork=str_replace(":hover","%colonhover%",$runLine);
			$this->theCtr=$ctr;
			$runLineAry=explode(':',$runLineWork);
			$aryCount=count($runLineAry);
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
				$errorMsg=$this->theCtr.') ERROR: null value for conversion of ' . $convStrgAry;
				$base->FileObj->writeLog('companyrestore.log',$errorMsg,&$base);
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
		$base->FileObj->writeLog('companyrestore.log',$query,&$base);
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$dbKeyValue=$workAry['0'][$dbKeyName];
		//$dbKeyValue=rand(1,99999);//xxx
		$base->FileObj->writeLog($this->theCtr.') companyrestore.log','read keyid: '.$dbKeyValue,&$base);
		//echo "...key read: $dbKeyValue<br>";
		return $dbKeyValue;	
	}
	//----------------------------------------
	function restoreFromBackupDoQuery($query,$base){
		$base->FileObj->writeLog('companyrestore.log',$query,&$base);
		$base->DbObj->queryTable($query,'updatenoconversion',&$base);
		//echo "do query: $query<br>";//xxx
	}
	//----------------------------------------
	function deprecatedbuildCompanyBackup($base){
		$companyName=$base->paramsAry['companyname'];
		if ($companyName == NULL){$this->errorOut('company name is missing');}
		$jobOverride=$base->paramsAry['joboverride'];
		if ($jobOverride==NULL){$jobOverride='none';}
	//- build deletes
		$companyString=$this->buildJobDeletes($companyName,$jobOverride,&$base);
		$companyString.=$this->buildJobBackups($companyName,$jobOverride,&$base);
		$tmpLocal=$base->systemAry['tmplocal'];
		$fullPath="$tmpLocal/$companyName".'_jobs.txt';
		$base->FileObj->writeFile($fullPath,$companyString,&$base);
		//echo 'xxx1';
		$base->errorProfileAry['returnmsg']="<pre>$fullPath has been written</pre>";
	}
//-----------------------------------------
	function deprecatedbuildJobDeletes($companyName,$jobOverride,$base){
		if ($jobOverride != 'none'){$overrideInsert=" and jobname='$jobOverride'";}
		else {$overrideInsert=NULL;}
		$query="select * from jobprofileview where companyname='$companyName' $overrideInsert order by jobdeleteorder";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'jobname');
		$jobDeletesAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$returnStrg="comment:delete jobs for company: $companyName";
		foreach ($jobDeletesAry as $jobName=>$jobAry){
			$returnStrg.="\n";
			$returnStrg.="comment:delete job $jobName in all tables";
			$returnStrg.="\n";
			$returnStrg.=$this->buildJobDelete($jobName,$jobAry,&$base);
		}
		return $returnStrg;
	}
//------------------------------------------
	function deprecatedbuildJobBackups($companyName,$jobOverride,$base){
		if ($jobOverride != 'none'){$overrideInsert=" and jobname='$jobOverride'";}
		else {$overrideInsert=NULL;}
		$returnStrg="comment:read company: $companyName";
		$returnStrg.="\n";
		$returnStrg.=$this->buildCompanyRead($companyName,&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:read users for company: $companyName";	
		$returnStrg.="\n";
		$returnStrg.=$this->buildUserReads($companyName,&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:read all operations soon to be applications";	
		$returnStrg.="\n";
		$returnStrg.=$this->buildOperReads(&$base);
		$returnStrg.="\n";
		$returnStrg.="comment:do insert and reads for all jobs for company: $companyName";	
		$query="select * from jobprofileview where companyname='$companyName' $overrideInsert order by jobrestoreorder";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'jobname');
		$jobRestoresAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		foreach ($jobRestoresAry as $jobName=>$jobAry){
			$returnStrg.="\n";
			$returnStrg.="comment:do insert and reads on all tables for job: $jobName";	
			$returnStrg.="\n";
			$returnStrg.=$this->buildJobInsertRead($jobName,$jobAry,&$base);
		}
		return $returnStrg;
	}
//-----------------------------------------
	function deprecatedbuildCompanyRead($companyName,&$base){
		$query="select * from companyprofile where companyname='$companyName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'companyname');
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		//$base->DebugObj->printDebug($workAry,1,'xxxwary');
		$companyProfileId=$workAry[$companyName]['companyprofileid'];
		$companyReadString="readqry:companyprofile:companyprofileid:$companyProfileId:select companyprofileid from companyprofile where companyname='$companyName'\n";
		return $companyReadString;
	}
//------------------------------------------
	function buildUserReads($companyName,&$base){
		$query="select userprofileid,username from userprofileview where companyname='$companyName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
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
		return $returnStrg;
	}
//-----------------------------------------
	function buildOperReads($base){
		$query="select operationprofileid,operationname from operationprofile";	
		$result=$base->DbObj->queryTable($query,'read',&$base);
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
		return $returnStrg;
	}	
//-----------------------------------------
	function deprecatedbuildJobDelete($jobName,$jobAry,&$base){
		$query="select * from dbtableprofile where dbtabletype='jobtable' order by dbtabledeleteorder";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtablename');
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$jobString=NULL;
		foreach ($workAry as $dbTableName=>$dbTableAry){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$parentColName=$dbControlsAry['parentselectorname'];
			$parentTableName=$dbControlsAry['dbtablemetaary'][$parentColName]['dbcolumnforeigntable'];
			if ($dbTableName == 'jobprofile'){
				$query="delete from jobprofile where jobname='$jobName'";
			}
			//- special cases for: deptprofile, joboperationxref
			elseif ($dbTableName == 'deptprofile' || $dbTableName == 'joboperationxref'){
				$query="delete from $dbTableName where jobprofileid=any (select jobprofileid from jobprofile where ";
				$query.=" jobname='$jobName')";
			}
			else {
				if ($parentTableName != 'jobprofile'){
					$query="delete from $dbTableName where $parentColName=any (select $parentColName from $parentTableName,jobprofile where";
					$query.=" jobprofile.jobprofileid = $parentTableName.jobprofileid and jobprofile.jobname='$jobName')";
				}
				else {
					$query="delete from $dbTableName where jobprofileid=any (select jobprofileid from jobprofile where ";
					$query.=" jobname='$jobName')";
				}
			}
			$jobString.="delqry:$query\n";
		}	
		return $jobString;
	}
	//-----------------------------------------
	function buildJobInsertRead($jobName,$jobAry,&$base){
		$query="select * from dbtableprofile where dbtabletype='jobtable' order by dbtableupdateorder";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtablename');
		$dbTablesAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$jobString=NULL;
		foreach ($dbTablesAry as $dbTableName=>$dbTableAry){
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbTableNameView=$dbTableName.'view';
			$query="select * from $dbTableNameView where jobname='$jobName'";
			$result=$base->DbObj->queryTable($query,'read',&$base);
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
				$jobString.="insqry:$insertQuery\n";
				$jobString.="readqry:$dbTableName:$theKeyName:$theKeyValue:$readQuery\n";					
			}
		}
		//exit();//xxx
		return $jobString;
	}
	//----------------------------------------
	function changeCompany($base){
		$base->DebugObj->printDebug("Plugin002Obj:changeCompanyJob('base')",0); //xx (h)
		//$base->DebugObj->printDebug($base->paramsAry,1,'para');//xxx
		$writeRowsAry=array();
		$updateAry=array();
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$jobName=$base->paramsAry['jobname'];
		$companyProfileId=$base->paramsAry['companyprofileid'];
		if ($jobProfileId != NULL && $companyProfileId != NULL){
			$updateAry=array('jobprofileid'=>$jobProfileId,'companyprofileid'=>$companyProfileId);
			$updateAry['jobname']=$jobName;
   			$dbControlsAry=array('dbtablename'=>'jobprofile');
		    $writeRowsAry[0]=$updateAry;
     		$dbControlsAry['writerowsary']=$writeRowsAry;
     		//$base->DebugObj->printDebug($dbControlsAry,1,'wra');//xxx
     		//$base->DebugObj->setPrio(-1,-1);//xxx
    		$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
    		//$base->DebugObj->setPrio(0,0);//xxx
		}
 		$base->DebugObj->printDebug("-rtn:changeCompanyJob",0); //xx (f)	
	}
	//----------------------------------------
	function getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,$base){
		$base->DebugObj->printDebug("Plugin002Obj:getRowKey($dbTableName,$selColName,$selColValue,$keyColName,'base')",0); //xx (h)
		$query="select $keyColName from $dbTableName where $selColName='$selColValue'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		 foreach ($dataAry as $rowNo=>$rowValueAry){
     		$profileId=$dataAry[$rowNo][$keyColName];
     		$returnAry[]=$profileId;	
      	}
		$base->DebugObj->printDebug("-rtn:getRowKey",0); //xx (f) 
     	return $returnAry;
	}
//--------------------------------------------------
	function deleteJob($base){
		$base->DebugObj->printDebug("Plugin002Obj:deleteJob('base')",0); //xx (h)
		$deleteJobProfileId=$base->paramsAry['jobtodelete'];
//-
		if ($deleteJobProfileId != ''){
			$this->doDeleteJob($deleteJobProfileId,&$base);			
		}
		else {echo "null job!!!";}
	}
//--------------------------------------------------
	function changeMessagestatus($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'params');//xxx
		$messageStatus=$base->paramsAry['status'];
		$urbanEcoMessagesId=$base->paramsAry['urbanecomessagesid'];
		if ($messageStatus != NULL && $urbanEcoMessagesId != NULL){
			$query="update urbanecomessages set messagestatus='save' where urbanecomessagesid=$urbanEcoMessagesId";
			$base->DbObj->queryTable($query,'update',&$base);
		}
	}
//--------------------------------------------------
	function setBoolean($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'params');//xxx
		$dbTableName=$base->paramsAry['dbtablename'];
		$dbTableColumnName=$base->paramsAry['dbtablecolumnname'];
		$direction=$base->paramsAry['direction'];
		if ($direction == 0){$updateInsert=" set $dbTableColumnName=FALSE";}
		else {$updateInsert=" set $dbTableColumnName=TRUE";}
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$base->paramsAry[$keyName];
		if ($keyName != NULL && $keyValue != NULL){
			$query="update $dbTableName $updateInsert where $keyName=$keyValue";
			//echo "<br>query: $query<br>";
			$base->DbObj->queryTable($query,'update',&$base);
		}
	}
	//----------------------------------------
	function doDeleteJob($deleteJobProfileId,$base){
		$rtnMsg='<pre>';
		$dbTableName='formprofile';$selColName='jobprofileid';
		$selColValue=$deleteJobProfileId;$keyColName='formprofileid';
		$deleteFormProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
//-
		$dbTableName='tableprofile';$selColName='jobprofileid';
		$selColValue=$deleteJobProfileId;$keyColName='tableprofileid';
		$deleteTableProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
//-		
		$dbTableName='htmlprofile';$selColName='jobprofileid';
		$selColValue=$deleteJobProfileId;$keyColName='htmlprofileid';
		$deleteHtmlProfileIdAry=$this->getRowKeys($dbTableName,$selColName,$selColValue,$keyColName,&$base);
//-
		foreach ($deleteFormProfileIdAry as $rowNo=>$deleteFormProfileId){
			if ($deleteFormProfileId > 0){
				$query="delete from formelementprofile where formprofileid=$deleteFormProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
				$query="delete from formdataprofile where formprofileid=$deleteFormProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
				$query="delete from formprofile where jobprofileid=$deleteJobProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
			}
		}
		foreach ($deleteTableProfileIdAry as $rowNo=>$deleteTableProfileId){
			if ($deleteTableProfileId>0){
				$query="delete from columnprofile where tableprofileid=$deleteTableProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
				$query="delete from tableprofile where tableprofileid=$deleteTableProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
			}
		}
		foreach ($deleteHtmlProfileIdAry as $rowNo=>$deleteHtmlProfileId){
			if ($deleteHtmlProfileId>0){
				$query="delete from htmlelementprofile where htmlprofileid=$deleteHtmlProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
				$query="delete from htmlprofile where htmlprofileid=$deleteHtmlProfileId";
				$rtnMsg.="$query\n";
				$base->DbObj->queryTable($query,'delete',&$base);
			}
		}
		$query="delete from joboperationxref where jobprofileid=$deleteJobProfileId";
		$rtnMsg.="$query\n";
		$base->DbObj->queryTable($query,'delete',&$base);
		$query="delete from jobprofile where jobprofileid=$deleteJobProfileId";
		$rtnMsg.="$query\n";
		$base->DbObj->queryTable($query,'delete',&$base);
		$rtnMsg.="</pre>";
		$base->errorProfileAry['returnmsg']=$rtnMsg;
		$base->DebugObj->printDebug("-rtn:deleteJob",0); //xx (f)	
	}
	//=======================================
	function insertMenu($paramFeed,$base){
		$base->DebugObj->printDebug("insertMenu($paramFeed,'base')",0); //xx (h)
		$menuName=$paramFeed['param_1'];
		$menuAry=$base->menuProfileAry[$menuName];
		$menuElementsAry=$base->menuElementProfileAry[$menuName];
		$sortOrder=$base->menuProfileAry['sortorder'][$menuName];
		$menuType=$menuAry['menutype'];
		//echo "name: $menuName, type: $menuType<br>";//xxxf
		switch ($menuType){
			case 'horizontal':
				$returnAry=$this->insertMenuHorizontal($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'vertical':
				$returnAry=$this->insertMenuVertical($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'horizontaldropdown':
				$returnAry=$this->insertMenuHorizontalDropDown($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'rotate':
				$returnAry=$this->insertMenuRotate($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'fixed':
				$returnAry=$this->insertMenuFixed($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'albumfixed':
				$returnAry=$this->insertMenuFixed($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'album':
				$returnAry=$this->insertMenuAlbum($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			default:
				//echo "menuname: $menuName, menutype: $menuType is invalid!!!<br>";
			break;
		}
		$base->DebugObj->printDebug("-rtn:insertMenu",0); //xx (f)	
		return $returnAry;
	}
	//---------------------------------------//xxxf
	function insertMenuAlbum($sortOrder,$menuAry,$menuElementsAry,&$base){
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuElementAry=array();
		$menuMaxElements=$menuAry['menumaxelements'];
		$menuName=$menuAry['menuname'];
		$menuType=$menuAry['menutype'];
		$menuPrevEvent=$menuAry['menupreviousevent'];
		$menuPrevEvent=$base->UtlObj->returnFormattedString($menuPrevEvent,&$base);
		$menuNextEvent=$menuAry['menunextevent'];
		$menuNextEvent=$base->UtlObj->returnFormattedString($menuNextEvent,&$base);
		//echo "menuname: $menuName<br>";//xxx
//- class
		$menuClass=$menuAry['menuclass'];
		if ($menuClass != NULL){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert='';}
//- selectedclass
		$menuSelectedClass=$menuAry['menuselectedclass'];
		if ($menuSelectedClass != ''){$menuSelectedClassInsert="class=\"$menuSelectedClass\"";}
		else {$menuSelectedClassInsert=NULL;}
//- nonselectedclass	//- setup nonselectedclass
		$menuNonSelectedClass=$menuAry['menunonselectedclass'];
		if ($menuNonSelectedClass != ''){$menuNonSelectedClassInsert="class=\"$menuNonSelectedClass\"";}
		else {$menuNonSelectedClassInsert=NULL;}
//- paging class
		$menuPagingClass=$menuAry['pagingclass'];
		if ($menuPagingClass != NULL){$menuPagingClassInsert="class=\"$menuPagingClass\"";}
		else {$menuPagingClassInsert=$menuClassInsert;}
//- id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=NULL;}
		$menuIdInsert="id=\"$menuId\"";
//- bullet 
		$menuBulletPath=$menuAry['menubulletpath'];
		if ($menuBulletPath == NULL){$menuBulletInsert=NULL;}
		else {$menuBulletInsert="<td $menuClassInsert><img src=\"$menuBulletPath\" $menuClassInsert></td>";}
//- title
		$menuTitle=$menuAry['menutitle'];
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitle == NULL){$menuTitleInsert=NULL;}
		else {
			if ($menuTitleClass==NULL){$menuTitleClassInsert=NULL;}
			else {$menuTitleClassInsert="class=\"$menuTitleClass\"";}
			$menuTitleInsert="<caption $menuTitleClassInsert>$menuTitle</caption>";
		}
//- delimiter
		$menuDelimiter=$menuAry['menudelimiter'];
//- alt
		$menuAlt=$menuAry['menualt'];
		if ($menuAlt == NULL){$menuAltInsert=NULL;}
		else {$menuAltInsert="title=\"$menuAlt\"";}
//- album
		$albumProfileId=$menuAry['albumprofileid'];
		$albumName=$base->albumProfileAry['main'][$albumProfileId]['albumname'];
		$albumAry=$base->albumProfileAry[$albumName];
		$albumSortAry=array();
		foreach ($albumAry as $ctr=>$pictureAry){
			$albumSortAry[]=$pictureAry['picturename'];
		}
//- event
		$menuEvent_raw=$menuAry['menuevent'];
		$menuEvent=$base->UtlObj->returnFormattedString($menuEvent_raw,&$base);
//- start building menu
//- heading
		$returnAry[]="\n<!-- start albummenu: $menuName -->\n";
//- setup <table ...
		$returnAry[]="<table $menuClassInsert $menuIdInsert $menuEvent>\n";
//- setup title
		$returnAry[]=$menuTitleInsert."\n";
//- setup table cells holding menu items
		$allDone=false;
		$noElements=count($sortOrder);
		if ($menuMaxElements >0 && $menuMaxElements>$noElements){$menuMaxElements=0;}
		//$base->DebugObj->printDebug($menuElementsAry,1,'mea3');//xxx
		//$base->DebugObj->printDebug($sortOrder,1,'sortorder');//xxx
//- loop through menu rows
		$firstTime=true;
		for ($rowCtr=1;$rowCtr<=$noElements;$rowCtr++){
			$menuElementCtr=$sortOrder[$rowCtr];
			$menuElementAry=$menuElementsAry[$menuElementCtr];
			//$base->DebugObj->printDebug($menuElementAry,1,'mea2');//xxx
			$menuElementName=$menuElementAry['menuelementname'];
			if ($rowCtr==1){
				$lastId=$menuElementAry['menuelementid'];
				//echo "lastId: $lastId, menuelementname: $menuElementName<br>";
				if ($lastId==NULL){$lastId=$menuElementName;}
			}
			if ($menuMaxElements > 0 && $noElements > $menuMaxElements && $rowCtr==$menuMaxElements){
				$returnAry[]="<tr><td $menuClassInsert>"."\n<a href=\"#\" $menuIdInsert $menuPagingClassInsert onclick=\"pageNextV2('$menuName');\">-more-</a>\n</td></tr>\n";
				$allDone=true;
			}
			$menuElementUrl_raw=$menuElementAry['menuelementurl'];
			$menuElementUrl=$base->UtlObj->returnFormattedString($menuElementUrl_raw,&$base);
//- get class
			$menuElementClass=$menuElementAry['menuelementclass'];
			$menuElementClass_td=$menuElementClass.'_td';
			if ($menuElementClass != NULL){
				$menuElementClassInsert=" class=\"$menuElementClass\"";
				$menuElementClassTdInsert=" class=\"$menuElementClass_td\"";
			}
			else {$menuElementClass=$menuClass;$menuElementClassInsert=$menuClassInsert;$menuElementTdClassInsert=NULL;}
//- selected class - has a selected field
			$menuElementSelectedFieldName=$menuElementAry['menuelementselectedfieldname'];
			$menuElementSelectedFieldValue=$menuElementAry['menuelementselectedfieldvalue'];
			if ($menuElementSelectedFieldName != NULL){
				$testForSelectedClass=$base->paramsAry[$menuElementSelectedFieldName];
				if ($menuElementSelectedFieldName == 'always'){$doit=true;}
				elseif ($testForSelectedClass == $menuElementSelectedFieldValue && $menuSelectedClassInsert != NULL){
					$doit=true;
				}
				else {$doit=false;}
				if ($doit){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;	
				}
				else {
					$useMenuElementClassInsert=$menuClassInsert;
					$useMenuElementClass=$menuClass;
				}
			}
//- selected class - no selected field, so look at jobname
			else {
				$jobName=$base->jobProfileAry['jobname'];
				$menuElementUrlAry=explode('&',$menuElementUrl);
				$menuElementUrlTest=$menuElementUrlAry[0];
				if ($menuElementUrlTest == $jobName && $menuSelectedClassInsert != NULL){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;
				}
				else {
					$useMenuElementClassInsert=$menuElementClassInsert;
					$useMenuElementClass=$menuElementClass;
				}
			}
//- get id
			$menuElementId=$menuElementAry['menuelementid'];
			if ($menuElementId==NULL){$menuElementId=$menuElementAry['menuelementname'];}
			$menuElementIdInsert="id=\"$menuElementId\"";
			$menuElementIdTdInsert="id=\"$menuElementId_td\"";
//- get label and modify and add events if needed
			$menuElementLabel_raw=$menuElementAry['menuelementlabel'];
			$menuElementLabel=$base->UtlObj->returnFormattedString($menuElementLabel_raw,&$base);
			$menuElementEventAttributes_raw=$menuElementAry['menuelementeventattributes'];
			$menuElementEventAttributes=$base->UtlObj->returnFormattedString($menuElementEventAttributes_raw,&$base);
			$useMenuElementLabel_div="<div $useMenuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementLabel_div="<div $menuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementType=$menuElementAry['menuelementtype'];
			if ($menuElementType == NULL){$menuElementType='url';}
//- change label positions with !!xxx!!
			if (strpos($menuElementLabel,'!!',0) !== false) {
				$doLabelInsert=true;
				$menuLineAry=$base->HtmlObj->convertHtmlLine($menuElementLabel,&$base);
				//echo "menulineary: $menuLineAry<br>";//xxx
			} // end if strpos!!
			else {$doLabelInsert=false;}
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementNo=$menuElementAry['menuelementno'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;			
			//echo "name: $menuElementName, type: $menuElementType<br>";//xxx
			//$base->DebugObj->printDebug($menuElementAry,1,'mea');//xxx
//- menu delimeter
			if (!$firstTime && $menuDelimiter != NULL){
				$returnAry[]="<tr><td class=\"menudelimiter\"><div class=\"menudelimiter\">$menuDelimiter</div></td></tr>\n";
			}
//- get target
			$menuElementTarget=$menuElementAry['menuelementtarget'];
			if ($menuElementTarget != null){$menuElementTargetInsert=" target = \"$menuElementTarget\" ";}
			else {$menuElementTargetInsert=null;}
			switch ($menuElementType){
//- element is url
			case 'url':
				//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
				$htmlElementAry=array();
				//$base->DebugObj->printDebug($albumAry,1,'xxxf');
				//exit();
				$pictureName=$albumSortAry[($rowCtr-1)];
				$imagePath=$albumAry[$pictureName]['picturedirectory'];
				$imageName=$albumAry[$pictureName]['picturefilename'];
				$imagePath.='/'.$imageName;
				$menuImageId=$menuAry['menuimageid'];
				$useNo=$rowCtr-1;
				if ($menuImageId != null){
					$useId="id=\"$menuImageId".'_'."$useNo\"";
				}
				else {
					$useId=null;
				}
				$htmlElementAry['label']="<img class=\"$menuElementClass\" $useId src=\"$imagePath\"/>";
				$htmlElementAry['htmlelementclass']=$menuElementClass;
				$workNo=$menuElementNo--;
				if ($menuId != null){
					$workId=$menuId.'_'.$workNo;//xxxff
				}
				else {$workId=null;}
				$htmlElementAry['htmlelementid']=$workId;
				$htmlElementAry['joblink']=$menuElementUrl;	
				$htmlElementAry['htmlelementeventattributes']=$menuElementEventAttributes;
				$htmlElementAry['htmlelementtarget']=$menuElementTarget;
				$workAry=$base->HtmlObj->buildUrl($htmlElementAry,&$base);
				$menuElementUrl_html=$base->UtlObj->returnFormattedData($menuElementUrl,'url','html',&$base);
				if (!$allDone){
					$returnAry[]='<tr>';
					$returnAry[]="<td $useMenuElementClassInsert>\n";
					//$returnAry[]="<li>";
					$returnAry[]=$menuBulletInsert;
					$returnAry=array_merge($returnAry,$workAry);
					//$returnAry[]="</li>";
					$returnAry[]="\n</td></tr>\n";
				} // end if !alldone
				$jsMenuAry[]="$menuBulletInsert<a href=\"$menuElementUrl_html\" $menuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementTargetInsert>$menuElementLabel</a>";
				break;
			case 'table':
				$tableName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$tableName);
				$menuElementDisplayAry=$base->TagObj->insertTable($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'menu':
				$menuName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$menuName);
				$menuElementDisplayAry=$this->insertMenu($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'map':
				$mapProfileId=$menuElementAry['mapprofileid'];
				$mapName=$base->mapProfileAry['main'][$mapProfileId]['mapname'];
				$menuElementDisplayAry=$base->HtmlObj->buildMap($mapName,&$base);
				$returnAry[]='<tr>';
				$returnAry[]="<td $menuElementClassInsert>\n";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'form':
				$passAry=array();
				$passAry['param_1']=$menuElementName;
				$subReturnAry=$base->TagObj->insertForm($passAry,&$base);
				$returnAry[]='<tr><td>';
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]='</td></tr>';
				//$base->DebugObj->printDebug($subReturnAry,1,'srtn');//xxx
				//exit();//xxx			
				break;	
			case 'repeatingform':
				$passAry=array();
				$query_raw=$menuElementAry['menuelementsql'];
				//-below let querytable do the formatting
				//$query=$base->UtlObj->returnFormattedString($query_raw,&$base);
				$result=$base->DbObj->queryTable($query_raw,'read',&$base);
				$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				$passAry['param_1']=$menuElementName;
				foreach ($workAry as $ctr=>$workRowAry){
					//echo "$ctr<br>";//xxx
					$base->paramsAry['ctr']=$ctr;
					$workRowAry['ctr']=$ctr;
					$passAry['usethisdataary']=$workRowAry;
					$tabIndexBase=($ctr+1)*20;
					$passAry['tabindexbase']=$tabIndexBase;
					//$base->DebugObj->printDebug($workRowAry,1,'xxxworkrowary');
					//echo "build a form<br>";//xxxd
					$subReturnAry=$base->TagObj->insertForm($passAry,&$base);
					//$base->DebugObj->printDebug($subReturnAry,1,'xxx');
					unset ($passAry['usethisdata']);
					$returnAry[]="<tr><td>\n";
					$headingStr="<!-- Form Number: $ctr -->\n";
					//echo "$headingStr<br>";//xxx
					$returnAry[]=$headingStr;
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="</tr></td>\n";
				}
				//$base->DebugObj->printDebug($returnAry,1,'rtnary');//xxx
				//exit(0);//xxx
				break;
				case 'album':
					$albumProfileId=$menuElementAry['albumprofileid'];
					//echo "albumprofileid: $albumProfileId<br>";//xxx
					$passAry=$base->HtmlObj->buildAlbumTable($albumProfileId,&$base);
					$albumTableDisplayAry=$passAry['returnary'];
					$albumName=$passAry['albumname'];
					if (!array_key_exists('jsary',$base->albumProfileAry)){$base->albumProfileAry['jsary']=array();}
					$base->albumProfileAry['jsary'][$albumName]=$passAry[$albumName];
					//$base->DebugObj->printDebug($albumTableDisplayAry,1,'atdaxxxa');
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$returnAry=array_merge($returnAry,$albumTableDisplayAry);
					$returnAry[]="</td></tr>\n";
				break;
				case 'image':
					$imageName=$menuElementName;
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$subReturnAry=$base->HtmlObj->buildImg($imageName,&$base);
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="<span $menuElementClassInsert>$menuElementLabel</span";
					$returnAry[]="</td></tr>\n";
				break;
			default:
//- element is text
				if (!$allDone){
					//$base->DebugObj->printDebug($menuElementAry,1,'mea');//xxx
					//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
					$returnAry[]='<tr>';
					if ($doLabelInsert){
						$returnAry[]="<td $menuElementIdInsert $useMenuElementClassInsert>\n";
						//$returnAry[]="<li>";
						$returnAry=array_merge($returnAry,$menuLineAry);
						//$returnAry[]="</li>";
						$returnAry[]="</td>\n";
					} // end dolabelfirst
					else {
						$returnAry[]="$menuBulletInsert<td $menuElementIdTdInsert $menuElementClassTdInsert>$useMenuElementLabel_div</td>\n";
					} // end else for dolabelfirst
					$returnAry[]='</tr>';
				} // end if !alldone
				$jsMenuAry[]="$menuElementLabel_div";
			} // end switch menuelementtype
			$firstTime=false;
		} // end for rowctr = 1 - 99
		$returnAry[]="</table>\n";
//- create special table for previous, next
		$useMenuClass=$menuClass."_prevnext";
		$useMenuClassInsert="class=\"$useMenuClass\"";
		$usePrevMenuClass=$menuClass."_prev";
		$usePrevMenuClassInsert="class=\"$usePrevMenuClass\"";
		$useNextMenuClass=$menuClass."_next";
		$useNextMenuClassInsert="class=\"$useNextMenuClass\"";
		$prevNextTable="<table $useMenuClassInsert><tr>\n";
		$prevNextTable.="<td $usePrevMenuClassInsert><div $usePrevMenuClassInsert $menuPrevEvent>&nbsp;</div></td>\n";
		$prevNextTable.="<td $useNextMenuClassInsert><div $useNextMenuClassInsert $menuNextEvent>&nbsp;</div></td>\n</tr>";
		$prevNextTable.="</table>\n";
		$returnAry[]=$prevNextTable;
		$returnAry[]="<!-- end albummenu: $menuName -->\n";
		//$returnAry[]='</ul>';
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		//xxxf - the below should be done by a foreach to get new entries automatically
		$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuselectedclass']=$menuSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menunonselectedclass']=$menuNonSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;	
		$base->menuProfileAry['jsmenusary'][$menuName]['albumname']=$albumName;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuimageid']=$menuImageId;
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=$menuMaxElements;
		//- end of auto change need		
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']='verticle';
		if ($menuMaxElements == NULL){$menuMaxElements=0;}
		$base->menuProfileAry['jsmenusary'][$menuName]['lastid']=$lastId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastmenuelementno']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;
		//$base->DebugObj->printDebug($base->albumProfileAry['jsary'],1,'xxxf');
		if (!array_key_exists('jsary',$base->albumProfileAry)){$base->albumProfileAry['jsary']=array();}
		$passAry=$base->HtmlObj->buildAlbumTable($albumProfileId,&$base);
		$base->albumProfileAry['jsary'][$albumName]=$passAry[$albumName];
		$base->DebugObj->printDebug("rtn: insertmenuvertical",0);//xx
		return $returnAry;				
	}
	//---------------------------------------
	function insertMenuRotatedeprecated($sortOrder,$menuAry,$menuElementsAry,&$base){
		//$base->DebugObj->printDebug($menuAry,1,'mea');//xxx
		$menuName=$menuAry['menuname'];
		$menuClass=$menuAry['menuclass'];
//- title id
		$menuTitleId=$menuAry['menutitleid'];
		if ($menuTitleId==NULL){$menuTitleId=$menuName.'title';}
		$menuTitleIdInsert="id=\"$menuTitleId\"";
//- text id
		$menuTextId=$menuAry['menutextid'];//xxxnew
		if ($menuTextId==NULL){$menuTextIdInsert=NULL;}
		else {$menuTextIdInsert="id=\"$menuTextId\"";}
//- text class
		$menuTextClass=$menuAry['menutextclass'];
		if ($menuTextClass!=NULL){$menuTextClassInsert="class=\"$menuTextClass\"";}
		else {$menuTextClassInsert=NULL;}
//- title class
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitleClass!=NULL){$menuTitleClassInsert="class=\"$menuTitleClass\"";}
		else {$menuTitleClassInsert=NULL;}
//- menuchangetype
		$menuChangeType=$menuAry['menuchangetype'];
		if ($menuChangeType == null){$menuChangeType='button';}
//- menudisplaytype
		$menuDisplayType=$menuAry['menudisplaytype'];
//-------- MenuObject xxxxf
//- class
		$MenuObjectClass=$menuAry['menuobjectclass'];
		if ($MenuObjectClass == NULL){$MenuObjectClass=$menuClass.'object';}
		$MenuObjectClassInsert="class=\"$MenuObjectClass\"";
//- id xxxf
		$MenuObjectId=$menuAry['menuobjectid'];
		if ($MenuObjectId == NULL){$MenuObjectId=$menuId.'object';}
		$MenuObjectIdInsert="id=\"$MenuObjectId\"";
//-------- menuParam xxxf
		$menuParamClass=$menuAry['menuparamclass'];
		if ($menuParamClass == NULL){$menuParamClass=$menuClass.'param';}
		$menuParamClassInsert="class=\"$menuParamClass\"";
//- id xxxf
		$menuParamId=$menuAry['menuparamid'];
		if ($menuParamId == NULL){$menuParamId=$menuId.'param';}
		$menuParamIdInsert="id=\"$menuParamId\"";
//-------- menuEmbed
//- class xxxf
		$menuEmbedClass=$menuAry['menuembedclass'];
		if ($menuEmbedClass == NULL){$menuEmbedClass=$menuClass.'embed';}
		$menuEmbedClassInsert="class=\"$menuEmbedClass\"";
//- id xxxf
		$menuEmbedId=$menuAry['menuembedid'];
		if ($menuEmbedId == NULL){$menuEmbedId=$menuId.'embed';}
		$menuEmbedIdInsert="id=\"$menuEmbedId\"";
//- previous event
		$menuPreviousEvent=$menuAry['menupreviousevent'];
		$menuPreviousEvent=$base->UtlObj->returnFormattedString($menuPreviousEvent,&$base);
		if ($menuPreviousEvent==null){$menuPreviousEvent="onclick=\"previousPictureV2('$menuName','$menuTextId');\"";}
//- next event
		$menuNextEvent=$menuAry['menunextevent'];
		$menuNextEvent=$base->UtlObj->returnFormattedString($menuNextEvent,&$base);
		if ($menuNextEvent==null){$menuNextEvent="onclick=\"nextPictureV2('$menuName','$menuTextId')\"";}
//echo "name: $menuName, display: $menuDisplayType<br>";//xxxf
		switch ($menuDisplayType){
			case 'none':
				$doCaption=false;
				$doTitle=false;
				$doPaging=false;
				break;;
			case 'caption':
				$doCaption=true;
				$doTitle=false;
				$doPaging=true;
				break;;
			case 'title':
				$doCaption=false;
				$doTitle=true;
				$doPaging=true;
				break;;
			case 'titlecaption':
				$doCaption=true;
				$doTitle=true;
				$doPaging=true;
				break;;
			default:
				$doCaption=false;
				$doTitle=false;
				$doPaging=true;
		}
		//- source id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuId=$menuName;}
		$menuIdInsert="id=\"$menuId\"";
		$menuImageId=$menuAry['menuimageid'];
		$menuImageIdInsert="id=\"$menuImageId\"";
		$menuPagingClass=$menuAry['menupagingclass'];
		$menuType=$menuAry['menutype'];
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuTitleAry=array();
		$jsMenuTextAry=array();//xxxnew
		$jsMenuElementAry=array();
		$returnAry[]="<table id=\"rotationtable\"><tr><td>";
		$firstTime=true;
		foreach ($menuElementsAry as $menuElementId=>$menuElementAry){
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$menuElementClass=$menuElementAry['menuelementclass'];
			$menuElementNo=$menuElementAry['menuelementno'];
			if ($menuElementClass == null){$menuElementClass=$menuClass;}
			$menuElementId=$menuElementAry['menuelementid'];
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;
			$albumName=$menuElementAry['albumname'];
			//echo "albumname: $albumName<br>";//xxx
			if ($albumName != NULL){
				$albumPicturesAry=$base->albumProfileAry[$albumName];
				$jsAlbumPicturesAry=array();
				$jsAlbumTitlesAry=array();	
				$jsAlbumTextAry=array();//xxxnew
				//$base->DebugObj->printDebug($albumProfileAry,1,'xxxfipic');//xxxf
				$cnt=count($albumPicturesAry);
				if ($cnt>0){
				foreach ($albumPicturesAry as $albumPictureName=>$albumPictureAry){
					$pictureFileName=$albumPictureAry['picturefilename'];
					$pictureFileNameAry=explode('.',$pictureFileName);
					$smallPictureFileName=$pictureFileNameAry[0].'.'.$pictureFileNameAry[1];
					$thumbnailPictureFileName=$pictureFileNameAry[0].'_TT.'.$pictureFileNameAry[1];
					$sourcePath=$albumPictureAry['picturedirectory']."/$smallPictureFileName";
					$pictureTitle=$albumPictureAry['picturetitle'];
					if ($pictureTitle == NULL){$pictureTitle=$albumPictureName;}
					$pictureText=$albumPictureAry['picturetext'];// xxxnew this and next
					$pictureText=str_replace(chr(0x0a),'',$pictureText);
					$pictureText=str_replace(chr(0x0d),'',$pictureText);
					if ($pictureText == NULL){$pictureText=$albumPictureName;}
//echo "title: $pictureTitle, text: $pictureText<br>";//xxxd
					$pictureId=$albumPictureAry['pictureid'];
					if ($pictureId == NULL){$pictureId=$menuAry['menuimageid'];}
					if ($pictureId == NULL){$pictureId=$menuName;}
					$pictureIdInsert="id=\"$pictureId\"";
					$pictureClass=$albumPictureAry['pictureclass'];
					if ($pictureClass == NULL) {$pictureClass=$menuAry['menuclass'];}
					$pictureClassInsert="class=\"$pictureClass\"";
					if ($firstTime){
						//echo "menuname: $menuName, docaption: $doCaption, dotitle: $doTitle<br>";//xxxf
						$firstTime=false;
						//echo "titlid: $menuTitleIdInsert<br>";//xxx
						$returnAry[]="<table id=\"allimage\" $pictureClassInsert><tr><td $menuTitleClassInsert >\n";
						//$returnAry[]="<div $menuTitleIdInsert $pictureClassInsert>\n";
						if ($doTitle){
							$returnAry[]="<div $menuTitleClassInsert $menuTitleIdInsert>";
							$returnAry[]="$pictureTitle";
							$returnAry[]="</div>\n";
						}
						$returnAry[]="</td></tr>\n";
						//- the image
						$returnAry[]="<tr><td $pictureClassInsert>\n";
						$returnAry[]="<!-- do the local image !>\n";
						$returnAry[]="<img src=\"$sourcePath\" $menuImageIdInsert $pictureClassInsert>\n";
						//- do the object
						$returnAry[]="<!-- youtube object -->\n";
						$returnAry[]="<div class=\"$MenuObjectClass\" id=\"$MenuObjectId".'div'."\">\n";
						$returnAry[]="<object $MenuObjectInsert $MenuObjectIdInsert>\n";
						$returnAry[]="<param name=\"movie\" id=\"$menuParamId\" value=\"\"/>";
						$returnAry[]="<embed class=\"$menuEmbedClass\" id=\"$menuEmbedId\"/>\n";
						$returnAry[]="</object>\n</div>\n";
						//- below needs to be turned on with a switch
						$returnAry[]="</td></tr><tr><td>\n";
						if ($doCaption){
							$returnAry[]="<div $menuTextIdInsert $menuTextClassInsert>$pictureText</div>\n";
						}
						$returnAry[]="</td></tr><tr><td>\n";
						if ($doPaging){
//- standard button
							$returnAry[]="<table class=\"standardbutton\"><tr><td class=\"prevstandardbutton\"><div class=\"prevstandardbutton\" $menuPreviousEvent>Previous</div></td>\n";
							$returnAry[]="<td class=\"nextstandardbutton\"><div class=\"nextstandardbutton\" $menuNextEvent>Next</div>\n";
							$returnAry[]="</td></tr></table>\n";
						}
						$returnAry[]="</td></tr></table>\n";
					} // end if firsttime
					$jsAlbumPicturesAry[]=$sourcePath;
					$jsAlbumTitlesAry[]=$pictureTitle;
					$jsAlbumTextAry[]=$pictureText;
				} // end foreach albumpicturesary
				} // end if cnt>0
				$jsMenuAry[]=$jsAlbumPicturesAry;
				$jsMenuTitleAry[]=$jsAlbumTitlesAry;
				$jsMenuTextAry[]=$jsAlbumTextAry;//xxxnew
			} // end if albumname
		} // end foreach menuelementsary
		//echo "menuid: $menuId<br>";//xxx
		$returnAry[]='</td></tr></table>';
		$menuMaxElements=count($jsMenuAry);
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		//$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		//$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		if ($menuMaxElements == NULL){$menuMaxElements=0;}
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=$menuMaxElements;
		//echo "name: $menuName, max: $menuMaxElements<br>";//xxxf
		//$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']='rotate';
		//$base->menuProfileAry['jsmenusary'][$menuName]['menutitleid']=$menuTitleId;
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['titles']=$jsMenuTitleAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['text']=$jsMenuTextAry;//xxxnew
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;//xxxnew
		//$base->menuProfileAry['jsmenusary'][$menuName]['menuchangetype']=$menuChangeType;//xxxnew
		//$base->menuProfileAry['jsmenusary'][$menuName]['menuimageid']=$menuImageId;//xxxnew
		$moveOverAry=array();
		$moveOverAry[]='menuimageid';
		$moveOverAry[]='menuchangetype';
		$moveOverAry[]='menutitleid';
		$moveOverAry[]='menuid';
		$moveOverAry[]='menupagingclass';
		$moveOverAry[]='menuclass';
		$moveOverAry[]='menutitle';
		$moveOverAry[]='menutextid';
		$moveOverAry[]='menutextclass';
		foreach ($moveOverAry as $ctr=>$menuElementName){
			$base->menuProfileAry['jsmenusary'][$menuName][$menuElementName]=$menuAry[$menuElementName];
		}
		return $returnAry;
	}
	//---------------------------------------
	function insertMenuHorizontal($sortOrder,$menuAry,$menuElementsAry,$base){
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuElementAry=array();
		$jobName=$base->jobProfileAry['jobname'];
		$menuName=$menuAry['menuname'];
		$menuDelimiter=$menuAry['menudelimiter'];
//- menuclass
		$menuClass=$menuAry['menuclass'];
		if ($menuClass != NULL){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert=NULL;}
//- menuselectedclass
		$menuSelectedClass=$menuAry['menuselectedclass'];
		if ($menuSelectedClass != NULL){$menuSelectedClassInsert="class=\"$menuSelectedClass\"";}
		else {$menuSelectedClassInsert=NULL;}
//- menunonselectedclass
		$menuNonSelectedClass=$menuAry['menunonselectedclass'];
		if ($menuNonSelectedClass != NULL){$menuNonSelectedClassInsert="class=\"$menuNonSelectedClass\"";}
		else {$menuNonSelectedClassInsert=NULL;}
		//echo "0) menunonselectedclass: $menuNonSelectedClass<br>";//xxx
//- menupagingclass
//$base->DebugObj->printDebug($menuAry,1,'menua');//xxx
		$menuPagingClass=$menuAry['pagingclass'];
		if ($menuPagingClass != NULL){$menuPagingClassInsert="class=\"$menuPagingClass\"";}
		else {$menuPagingClassInsert=NULL;}
//- menuid
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=NULL;}
		//echo "menuid: $menuId<br>";//xxx
		else {$menuIdInsert="id=\"$menuId\"";}
//- menutitleid
		$menuTitleId=$menuAry['menutitleid'];
		if ($menuTitleId==NULL){$menuTitleId=$menuId.'title';}
		$menuTitleIdInsert="id=\"$menuTitleId\"";
//- build menu		
		$returnAry[]="<!-- horizontal menu: $menuName -->\n";
		$returnAry[]="<table $menuClassInsert $menuIdInsert>\n";
		$returnAry[]='<tr>'."\n";
//- loop through elements
		$firstTime=true;
		$noElements=count($sortOrder);
		for ($rowCtr=1;$rowCtr<=$noElements;$rowCtr++){
			$menuElementProfileId=$sortOrder[$rowCtr];
			$menuElementAry=$menuElementsAry[$menuElementProfileId];
			$menuElementUrl=$menuElementAry['menuelementurl'];
			$menuElementLabel_raw=$menuElementAry['menuelementlabel'];
			$menuElementLabel=$base->UtlObj->returnFormattedString($menuElementLabel_raw,&$base);
			$menuElementClass=$menuElementAry['menuelementclass'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$menuElementId=$menuElementAry['menuelementid'];
			$menuElementType=$menuElementAry['menuelementtype'];
			$menuElementName=$menuElementAry['menuelementname'];
			$menuElementImageName=$menuElementAry['menuelementimagename'];
			$menuElementEventAttributes_raw=$menuElementAry['menuelementeventattributes'];
			$menuElementEventAttributes=$base->UtlObj->returnFormattedString($menuElementEventAttributes_raw,&$base);
			$menuElementEventAttributes=$base->UtlObj->returnFormattedStringDataFed($menuElementEventAttributes_raw,$menuElementAry,&$base);
			$menuElementCheckSecurity_raw=$menuElementAry['menuelementchecksecurity'];
			$menuElementCheckSecurity=$base->UtlObj->returnFormattedData($menuElementCheckSecurity_raw,'boolean','internal');
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementNo=$menuElementAry['menuelementno'];
			//echo "$rowCtr, $menuElementName, $menuElementType";//xxx
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;			
			if ($menuElementAlt==null){$menuElementAltInsert=null;}
			else {$menuElementAltInsert="title=\"$menuElementAlt\"";}
//- lastid
			if ($rowCtr==1){
				$lastId=$menuElementAry['menuelementid'];
				//echo "lastId: $lastId, menuelementname: $menuElementName<br>";
				if ($lastId==NULL){$lastId=$menuElementName;}
			}
//- menuelementclass
			if ($menuElementClass != NULL){
				$menuElementClassInsert="class=\"$menuElementClass\"";
				$menuElementTdClassInsert="class=\"$menuElementClass_td\"";
			}
			else {
				$menuElementClass=$menuClass;
				$menuElementClassInsert=$menuClassInsert;
				$menuElementTdClassInsert=NULL;
			}
//- menuelementid
			if ($menuElementId != NULL){$menuElementIdInsert="id=\"$menuElementId\"";}
			else {$menuElementIdInsert=NULL;}
//- apply menu class selected if url before & is same as this jobname
			$menuElementSelectedFieldName=$menuElementAry['menuelementselectedfieldname'];
			$menuElementSelectedFieldValue=$menuElementAry['menuelementselectedfieldvalue'];
			if ($menuElementSelectedFieldName != NULL){
				$testForSelectedClass=$base->paramsAry[$menuElementSelectedFieldName];
				//echo "name: $menuName sname: $menuElementSelectedFieldName test: $testForSelectedClass, value: $menuElementSelectedFieldValue<br>";//xxx
				if ($menuElementSelectedFieldName == 'always'){$doit=true;}
				elseif ($testForSelectedClass == $menuElementSelectedFieldValue && $menuSelectedClassInsert != NULL){$doit=true;}
				else {$doit=false;}
				if ($doit){
					$menuElementClassInsert=$menuSelectedClassInsert;	
					$menuElementClass=$menuSelectedClass;	
				}
			}
//- id
			$menuElementId=$menuElementAry['menuelementid'];
			if ($menuElementId==NULL){$menuElementId=$menuElementAry['menuelementname'];}
			$menuElementIdInsert="id=\"$menuElementId\"";
//- delimiter
			if (!$firstTime && $menuDelimiter != ''){$returnAry[]="</td><td $menuClassInsert>$menuDelimiter</td>";}
//- element body based on type
//echo "check: $menuElementCheckSecurity<br>";//xxx
			if ($menuElementCheckSecurity){
				$paramsAry=array('componentcheck'=>'true');
				//-xxx below needs to be fixed for turning menu elements on/off
				//$okToContinue=$base->UtlObj->validateUserCompany($paramsAry,&$base);
				$okToContinue=true;
				if (!$okToContinue){$menuElementType='text';$menuElementEventAttributes=NULL;}
				//echo "ok: $okToContinue<br>";//xxx
			}
		//echo "name: $menuName, ename: $menuElementName, type: $menuElementType<br>";//xxx		
			switch ($menuElementType){
			case 'image':
				//echo "menuelementeventattributes: $menuElementEventAttributes, meear: $menuElementEventAttributes_raw<br>";//xxxf
				//$base->DebugObj->printDebug($menuElementAry,1,'mea');
				$passAry=array('imagename'=>$menuElementImageName);
				if ($menuElementEventAttributes_raw != null){$passAry['imageevents']=$menuElementEventAttributes;}
				//$base->DebugObj->printDebug($passAry,1,'xxxf');
				//print_r($passAry);
				//exit();//xxxf
				$workAry=$base->HtmlObj->buildImgPass($passAry,&$base);
				//print_r($workAry);
				//$base->DebugObj->printDebug($workAry,1,'xxxf');exit();//xxxf
				//$workAry=$base->HtmlObj->buildImg($menuElementImageName,&$base);
				//$base->DebugObj->printDebug($workAry,1,'xxx');
				$returnAry[]="<td $menuIdInsert $menuElementClassInsert>";
				$returnAry=array_merge($returnAry,$workAry);
				$imageAry=$base->imageProfileAry[$menuElementImageName];
				$imageSource=$imageAry['imagesource'];
				$jsMenuAry[]=$imageSource;
				$returnAry[]='</td>'."\n";
			break;
			case 'url':
				$htmlElementAry=array();
				$htmlElementAry['label']=$menuElementLabel;
				$htmlElementAry['htmlelementclass']=$menuElementClass;
				$htmlElementAry['htmlelementid']=$menuElementId;
				$htmlElementAry['joblink']=$menuElementUrl;
				$htmlElementAry['htmlelementimagename']=$menuElementImageName;
				$htmlElementAry['htmlelementeventattributes']=$menuElementEventAttributes;
				$htmlElementAry['menuelementaltinsert']=$menuElementAltInsert;
				$workAry=$base->HtmlObj->buildUrl($htmlElementAry,&$base);
				//$base->DebugObj->printDebug($workAry,1,'work');//xxxa
				//if ($menuElementClass == NULL){$menuElementClassInsert=NULL;}
				//else {$menuElementClassInsert="class=\"$menuElementClass\"";}
				$returnAry[]="<td $menuIdInsert $menuElementClassInsert>";
				$returnAry=array_merge($returnAry,$workAry);
				$returnAry[]='</td>'."\n";
				//echo "menuelementurl: $menuElementUrl<br>";//xxx
				$jobLocal=$base->systemAry['joblocal'];
				$pos=strpos('x'.$menuElementUrl,'http',0);
				if ($pos>0 || $menuElementUrl == '#'){$jsMenuAry[]=$menuElementUrl;}
				else {$jsMenuAry[]="$jobLocal$menuElementUrl";}
				break;
			case 'displaydata':
				$returnAry[]="<td $menuIdInsert $menuElementClassInsert>";
				$theDisplay=$base->HtmlObj->buildDisplay($menuElementAry,&$base);
				$returnAry[]=$theDisplay;
				//-possible problem below!!!
				$jsMenuAry[]=$theDisplay;
				$returnAry[]='</td>'."\n";
				break;
			case 'displayliteral':
				if ($menuElementClass != ''){
					$returnValue[]="<div class=$class>$menuElementLabel</div>";
				}
				else {$returnValue=$menuElementLabel;}
				$returnAry[]="<td $menuIdInsert $menuElementClassInsert>";
				$returnAry[]=$returnValue;
				$jsMenuAry[]=$menuElementLabel;
				$returnAry[]='</td>'."\n";
				break;
			case 'inputselect':
				$htmlElementAry=array();
				$htmlElementAry['label']=$menuElementLabel;
				$htmlElementAry['htmlelementclass']=$menuElementClass;
				$htmlElementAry['htmlelementid']=$menuElementId;
				$htmlElementAry['htmlelementname']=$menuElementName;
				$htmlElementAry['joblink']=$menuElementUrl;
				$htmlElementAry['htmlelementeventattributes']=$menuElementEventAttributes;
				$workAry=$base->HtmlObj->buildInputSelect($htmlElementAry,&$base);
				$returnAry[]="<td $menuIdInsert $menuElementClassInsert>";
				$returnAry=array_merge($returnAry,$workAry);
				$jsMenuAry[]='inputselect';
				$returnAry[]="</td>\n";
				break;
			case 'text':
				$returnAry[]="<td $menuIdInsert $menuElementTdClassInsert>\n";
				//echo "label: $menuElementLabel<br>";//xxx
				$menuLabelInsert="<p $menuElementIdInsert $menuElementClassInsert $menuElementEventAttributes>$menuElementLabel</p>";
				$returnAry[]="$menuLabelInsert\n";
				$jsMenuAry[]=$menuLabelInsert;
				$returnAry[]="</td>\n";
			break;
			case 'form':
				$returnAry[]="<td $menuIdInsert $menuElementTdClassInsert>\n";
				$paramsAry=array('param_1'=>$menuElementName);
				$subReturnAry=$base->TagObj->insertForm($paramsAry,&$base);
				//$base->DebugObj->printDebug($base->formProfileAry,1,'formprofileary');//xxx
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]="</td>\n";
			break;
			default:
				//echo "invalidate menu type: $menuElementName, $menuElementType<br>";
			}
			$firstTime=false;
		}
		$returnAry[]='</tr>'."\n";
		$returnAry[]='</table>'."\n";
		$returnAry[]="<!-- end horizontal menu: $menuName -->\n";
//- javascript table
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		//echo "paging: $menuPagingClass<br>";//xxx
		$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutitleid']=$menuTitleId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastid']=$lastId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastmenuelementno']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']='horizontal';
		$base->menuProfileAry['jsmenusary'][$menuName]['menuselectedclass']=$menuSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menunonselectedclass']=$menuNonSelectedClass;	
		//echo "1) nonselectedclass: $menuNonSelectedClass<br>";//xxx	
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;
		return $returnAry;	
	}
	//---------------------------------------
	function insertMenuHorizontalDropDown($sortOrder,$menuAry,$menuElementsAry,$base){
		$base->DebugObj->printDebug("Plugin002Obj:insertMenuHorizontalDropDown($menuAry,$menuElementsAry,'base')",0); //xx (h)
		$returnAry=array();
		$menuClass=$menuAry['menuclass'];
		$menuDelimiter=$menuAry['menudelimiter'];
		if ($menuClass != ''){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert='';}
		$menuId=$menuAry['menuid'];
		if ($menuId == ''){$menuId=$menuAry['menuname'];}
		$menuIdInsert="id=\"$menuId\"";
		$returnAry[]="<table $menuClassInsert $menuIdInsert>";
		$returnAry[]='<tr>';
		$firstTime=true;
		$noMainElements=count($sortOrder);
//--- loop through each column
		for ($colCtr=1; $colCtr<=$noMainElements; $colCtr++){
			$menuElementProfileId_title=$sortOrder[$colCtr][0];
			$menuElementAry_title=$menuElementsAry[$menuElementProfileId_title];
			$menuElementUrl_title=$menuElementAry_title['menuelementurl'];
			$menuElementLabel_title=$menuElementAry_title['menuelementlabel'];
			$menuElementClass_title=$menuElementAry_title['menuelementclass'];
			if ($menuElementClass_title != ''){$menuElementClassTitleInsert=" class=\"$menuElementClass_title\"";}
			else {$menuElementClassTitleInsert='';}
			$menuElementId_title=$menuElementAry_title['menuelementid'];
			if ($menuElementId_title==''){$menuElementId_title=$menuElementAry_title['menuelementname'];}
			$menuElementIdTitleInsert="id=\"$menuElementId_title\"";
			//if (!$firstTime && $menuDelimiter != ''){$returnAry[]="<td $menuClassInsert>$menuDelimiter</td>";}
			$firstTime=false;
			$progRunInsert=" onmouseover=\"showHide('sub$menuElementId_title','visible')\"";
			$progRunInsert.=" onmouseout=\"showHide('sub$menuElementId_title','hidden')\"";
			$menuTitleInsert="<div class=\"title$menuElementClass_title\" id=\"title$menuElementId_title\" $progRunInsert>$menuElementLabel_title<br></div>";
			$noSubElements=count($sortOrder[$colCtr])-1;
			$menuElementsSubInsert="<div id=\"sub$menuElementId_title\" class=\"sub$menuElementClass_title\" $progRunInsert>";
//--- loop through each drop down row
			for ($subCtr=1;$subCtr<=$noSubElements;$subCtr++){
				$menuElementProfileId_sub=$sortOrder[$colCtr][$subCtr];
				$menuElementAry_sub=$menuElementsAry[$menuElementProfileId_sub];
				$menuElementUrl_sub=$menuElementAry_sub['menuelementurl'];
				$menuElementLabel_sub=$menuElementAry_sub['menuelementlabel'];
				$menuElementId_sub=$menuElementAry_sub['menuelementid'];
				if ($menuElementId_sub != NULL){$menuElementIdInsert_sub="id=\"$menuElementId_sub\"";}
				else {$menuElementIdInsert_sub=NULL;}
				$menuElementClass_sub=$menuElementAry_sub['menuelementclass'];
				if ($menuElementClass_sub != NULL){$menuElementClassInsert_sub="class=\"$menuElementClass_sub\"";}
				else {$menuElementClassInsert_sub=NULL;}
				$menuElementsSubInsert.="<a href=\"$menuElementUrl_sub\" $menuElementIdInsert_sub $menuElementClassInsert_sub>";
				$menuElementsSubInsert.="$menuElementLabel_sub</a><br>\n";
			}
			$menuElementsSubInsert.="</div>";
			$returnAry[]="<td>\n<span id=\"$menuId$colCtr\">$menuTitleInsert\n $menuElementsSubInsert \n </span>\n</td>";	
		}
		$returnAry[]='</tr>';
		$returnAry[]='</table>';
		return $returnAry;
	}
	//---------------------------------------
		function insertMenuFixed($sortOrder,$menuAry,$menuElementsAry,$base){
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuElementAry=array();
		$jsMenuTitleAry=array();
		$jsMenuTextAry=array();//xxxdnew
		$jsMenuMediaTypeAry=array();
		$jsMenuVideoIdAry=array();
		$menuMaxElements=$menuAry['menumaxelements'];
		$menuName=$menuAry['menuname'];
		$menuType=$menuAry['menutype'];
		$videoHeight=$menuAry['videoheight'];
		$videoWidth=$menuAry['videowidth'];
//- menuclass
		$menuClass=$menuAry['menuclass'];
		if ($menuClass != NULL){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert='';}
//- menuselectedclass
		$menuSelectedClass=$menuAry['menuselectedclass'];
		if ($menuSelectedClass != ''){$menuSelectedClassInsert="class=\"$menuSelectedClass\"";}
		else {$menuSelectedClassInsert=NULL;}
//- menunonselectedclass
		$menuNonSelectedClass=$menuAry['menunonselectedclass'];
		if ($menuNonSelectedClass != ''){$menuNonSelectedClassInsert="class=\"$menuNonSelectedClass\"";}
		else {$menuNonSelectedClassInsert=NULL;}
//--------- menupaging
//- class
		$menuPagingClass=$menuAry['pagingclass'];
		if ($menuPagingClass != NULL){$menuPagingClassInsert="class=\"$menuPagingClass\"";}
		else {$menuPagingClassInsert=$menuClassInsert;}
//- id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=NULL;}
		else {$menuIdInsert="id=\"$menuId\"";}
//-------- menutitle
//- class
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitleClass == NULL){$menuTitleClass=$menuClass.'title';}
		$menuTitleClassInsert="class=\"$menuTitleClass\"";
//- id
		$menuTitleId=$menuAry['menutitleid'];
		if ($menuTitleId == NULL){$menuTitleId=$menuId.'title';}
		$menuTitleIdInsert="id=\"$menuTitleId\"";
//-------- menutext
//- class
		$menuTextClass=$menuAry['menutextclass'];
		if ($menuTextClass == NULL){$menuTextClass=$menuClass.'text';}
		$menuTextClassInsert="class=\"$menuTextClass\"";
//- id
		$menuTextId=$menuAry['menutextid'];
		if ($menuTextId == NULL){$menuTextId=$menuId.'text';}
		$menuTextIdInsert="id=\"$menuTextId\"";
//-------- menuPicture
//- class
		$menuPictureClass=$menuAry['menuimageclass'];
		if ($menuPictureClass == NULL){$menuPictureClass=$menuClass.'picture';}
		$menuPictureClassInsert="class=\"$menuPictureClass\"";
//- id
		$menuPictureId=$menuAry['menuimageid'];
		if ($menuPictureId == NULL){$menuPictureId=$menuId.'picture';}
		$menuPictureIdInsert="id=\"$menuPictureId\"";
//-------- MenuObject xxxxf
//- class
		$MenuObjectClass=$menuAry['menuobjectclass'];
		if ($MenuObjectClass == NULL){$MenuObjectClass=$menuClass.'object';}
		$MenuObjectClassInsert="class=\"$MenuObjectClass\"";
//- id xxxf
		$MenuObjectId=$menuAry['menuobjectid'];
		if ($MenuObjectId == NULL){$MenuObjectId=$menuId.'object';}
		$MenuObjectIdInsert="id=\"$MenuObjectId\"";
//-------- menuLocalObject xxxxf
//- class
		$menuLocalObjectClass=$menuAry['menulocalobjectclass'];
		if ($menuLocalObjectClass == NULL){$menuLocalObjectClass=$menuClass.'localobject';}
		$menuLocalObjectClassInsert="class=\"$menuLocalObjectClass\"";
//- id xxxf
		$menuLocalObjectId=$menuAry['menulocalobjectid'];
		if ($menuLocalObjectId == NULL){$menuLocalObjectId=$menuId.'localobject';}
		$menuLocalObjectIdInsert="id=\"$menuLocalObjectId\"";
//-------- menuParam xxxf
		$menuParamClass=$menuAry['menuparamclass'];
		if ($menuParamClass == NULL){$menuParamClass=$menuClass.'param';}
		$menuParamClassInsert="class=\"$menuParamClass\"";
//- id xxxf
		$menuParamId=$menuAry['menuparamid'];
		if ($menuParamId == NULL){$menuParamId=$menuId.'param';}
		$menuParamIdInsert="id=\"$menuParamId\"";
//------- menuLocalParam
//- id xxxf
		$menuLocalParamId=$menuAry['menulocalparamid'];
		if ($menuLocalParamId == NULL){$menuLocalParamId=$menuId.'localparam';}
		$menuLocalParamIdInsert="id=\"$menuLocalParamId\"";
//-------- menuEmbed
//- class xxxf
		$menuEmbedClass=$menuAry['menuembedclass'];
		if ($menuEmbedClass == NULL){$menuEmbedClass=$menuClass.'embed';}
		$menuEmbedClassInsert="class=\"$menuEmbedClass\"";
//- id xxxf
		$menuEmbedId=$menuAry['menuembedid'];
		if ($menuEmbedId == NULL){$menuEmbedId=$menuId.'embed';}
		$menuEmbedIdInsert="id=\"$menuEmbedId\"";
//-------- menuLocalEmbed
//- class xxxf
		$menuLocalEmbedClass=$menuAry['menulocalembedclass'];
		if ($menuLocalEmbedClass == NULL){$menuLocalEmbedClass=$menuClass.'localembed';}
		$menuLocalEmbedClassInsert="class=\"$menuLocalEmbedClass\"";
//- id xxxf
		$menuLocalEmbedId=$menuAry['menulocalembedid'];
		if ($menuLocalEmbedId == NULL){$menuLocalEmbedId=$menuId.'localembed';}
		$menuLocalEmbedIdInsert="id=\"$menuLocalEmbedId\"";
//-------- menuimage
//- id
		$menuImageId=$menuAry['menuimageid'];
		if ($menuImageId == NULL){$menuImageIdInsert=$menuId.'image';}
		else {$menuImageIdInsert="id=\"$menuImageId\"";}
//- <table ...
		$returnAry[]="<table $menuClassInsert $menuIdInsert>";
		//$base->DebugObj->printDebug($returnAry,1,'xxxd');
//- table cells holding menu items
		$elementFirstTime=true;
		$noElements=count($sortOrder);
		if ($menuMaxElements >0 && $menuMaxElements>$noElements){$menuMaxElements=0;}
// loop through rows
		for ($rowCtr=1;$rowCtr<=$noElements;$rowCtr++){
			//echo "***** $rowCtr ******<br>";//xxxd
			$menuElementCtr=$sortOrder[$rowCtr];
			if ($menuElementCtr == null){
				echo 'Plugin002Obj.insertMenuFixed menuElementCtr is null!!!';
				exit();
			}
			$menuElementAry=$menuElementsAry[$menuElementCtr];
			$menuElementName=$menuElementAry['menuelementname'];
			if ($rowCtr==1){
				$lastId=$menuElementAry['menuelementid'];
				//echo "lastId: $lastId, menuelementname: $menuElementName<br>";
				if ($lastId==NULL){$lastId=$menuElementName;}
			}
			$menuElementUrl=$menuElementAry['menuelementurl'];
			$menuElementLabel=$menuElementAry['menuelementlabel'];
			$menuElementType=$menuElementAry['menuelementtype'];
			$menuElementClass=$menuElementAry['menuelementclass'];
			if ($menuElementClass != NULL){$menuElementClassInsert=" class=\"$menuElementClass\"";}
			else {$menuElementClassInsert=NULL;}
			$menuElementEventAttributes_raw=$menuElementAry['menuelementeventattributes'];
			$menuElementEventAttributes=$base->UtlObj->returnFormattedString($menuElementEventAttributes_raw,&$base);
//- determine class by job
			$jobName=$base->jobProfileAry['jobname'];
			$menuElementUrlAry=explode('&',$menuElementUrl);
			$menuElementUrlTest=$menuElementUrlAry[0];
			$menuElementId=$menuElementAry['menuelementid'];
			$menuElementNo=$menuElementAry['menuelementno'];
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;			
			if ($menuElementId==NULL){$menuElementIdInsert=NULL;}
			else {$menuElementIdInsert="id=\"$menuElementId\"";}
			//echo "$menuElementName, $menuElementType<br>";//xxxd
			switch ($menuElementType){
				case 'image':
					$menuElementImageName=$menuElementAry['menuelementimagename'];
					$imageAry=$base->imageProfileAry[$menuElementImageName]	;
					$imageUseMap=$imageAry['imageusemap'];
					if ($imageUseMap != NULL){$useMapInsert="usemap=\"#$imageUseMap\"";}
					else {$useMapInsert=NULL;}
					$imagePath=$imageAry['imagesource'];
					$jsMenuAry[]=$imagePath;
					$jsMenuTitleAry[]='title place filler';
					$imagePathInsert="src=\"$imagePath\"";
					//$base->DebugObj->printDebug($imageAry,1,'img');//xxx
					$menuElementDisplayAry=array();
					$menuElementDisplayAry[]="<img $menuElementClassInsert $menuImageIdInsert $imagePathInsert $useMapInsert>";
					break;
				case 'map':
					$mapProfileId=$menuElementAry['mapprofileid'];
					$mapName=$base->mapProfileAry['main'][$mapProfileId]['mapname'];
					//echo "mapname: $mapName<br>";//xxx
					$menuElementDisplayAry=$base->HtmlObj->buildMap($mapName,&$base);
					$elementFirstTime=true; // keep turning it on for maps
					//$base->DebugObj->printDebug($menuElementDisplayAry,1,'mapary');
					break;
					//xxxa
				case 'paragraph':
					$paragraphName=$menuElementName;
					$paramAry=array('param_1'=>$paragraphName);
					$menuElementDisplayAry=$base->Plugin002Obj->insertParagraph($paramAry,&$base);
					$workStrg_raw=implode('',$menuElementDisplayAry);
					$removeString=chr(0x0d).chr(0x0a);
					$workStrg=str_replace($removeString,"",$workStrg_raw);
					$jsMenuAry[]=$workStrg;
					break;
				case 'album':
					$menuElementDisplayAry=array();
					$albumName=$menuElementAry['albumname'];
					$menuElementTitleDisplayAry=array();
					//echo "xxxf: albumname: $albumName<br>";//xxxf
					if ($albumName != NULL){
						$albumPicturesAry=$base->albumProfileAry[$albumName];
						$jsAlbumPicturesAry=array();
						$jsAlbumTitlesAry=array();	
						$jsAlbumTextAry=array();//xxxdnew
						$jsAlbumMediaTypeAry=array();
						$jsAlbumVideoIdAry=array();//new
						//- need to put in videoId
						$cnt=count($albumPicturesAry);
						//$base->DebugObj->printDebug($base->albumProfileAry,1,'xxxf');
						if ($cnt>0){
						$albumPictureFirstTime=true;
						foreach ($albumPicturesAry as $albumPictureName=>$albumPictureAry){
							$pictureFileName=$albumPictureAry['picturefilename'];
							$pictureFileNameAry=explode('.',$pictureFileName);
							$smallPictureFileName=$pictureFileNameAry[0].'.'.$pictureFileNameAry[1];
							$thumbnailPictureFileName=$pictureFileNameAry[0].'_TT.'.$pictureFileNameAry[1];
							$sourcePath=$albumPictureAry['picturedirectory']."/$smallPictureFileName";
							$pictureTitle=$albumPictureAry['picturetitle'];
							$pictureTitle=$base->UtlObj->returnFormattedString($pictureTitle,&$base);
							if ($pictureTitle == NULL){$pictureTitle=$albumPictureName;}
							$pictureText=$albumPictureAry['picturetext'];//xxxdnew
							$mediaType=$albumPictureAry['mediatype'];
							$pictureId=$albumPictureAry['pictureid'];
							$videoId=$albumPictureAry['videoid'];//new
							if ($pictureId == NULL){$pictureId=$menuImageId;}
							if ($pictureId == NULL){$pictureId=$menuAry['menuid'];}
							if ($pictureId == NULL){$pictureIdInsert=NULL;}
							else {$pictureIdInsert="id=\"$pictureId\"";}
							$pictureClass=$albumPictureAry['pictureclass'];
							if ($pictureClass == NULL) {$pictureClass=$menuAry['menuclass'];}
							$pictureClassInsert="class=\"$pictureClass\"";
							if ($albumPictureFirstTime){
								//xxxd
								$albumPictureFirstTime=false;
								$menuElementDisplayAry=array();
								$menuElementDisplayAry[]="\n<!-- fixed menu: $menuName -->\n";
								//xxxd- already done above $menuElementDisplayAry[]="<table $menuClassInsert $menuIdInsert>\n<tr><td $menuClassInsert >\n";
								$menuElementDisplayAry[]="<div $menuTitleClassInsert $menuTitleIdInsert>";
								$menuElementDisplayAry[]="$pictureTitle";
								$menuElementDisplayAry[]="</div>\n";
								//$menuElementDisplayAry[]="</td></tr>\n";
								//$menuElementDisplayAry[]="<tr><td $menuClassInsert>\n";
								//- do the picture
								$menuElementDisplayAry[]="<!-- local picture -->\n";
								$menuElementDisplayAry[]="<div $menuPictureClassInsert id=\"$menuPictureId".'div'."\">\n";
								$menuElementDisplayAry[]="<img src=\"$sourcePath\" $menuPictureClassInsert $menuPictureIdInsert>\n";
								$menuElementDisplayAry[]="</div>\n";
								/*
								//- do the youtube object
								$menuElementDisplayAry[]="<!-- youtube object -->\n";
								$menuElementDisplayAry[]="<div class=\"$MenuObjectClass\" id=\"$MenuObjectId".'div'."\">\n";
								$menuElementDisplayAry[]="<object $MenuObjectInsert $MenuObjectIdInsert>\n";
								$menuElementDisplayAry[]="<param name=\"movie\" id=\"$menuParamId\" value=\"\"/>";
								$menuElementDisplayAry[]="<embed class=\"$menuEmbedClass\" id=\"$menuEmbedId\"/>\n";
								$menuElementDisplayAry[]="</object>\n</div>\n";
								*/
								//- do the local object
								/*
								$menuElementDisplayAry[]="<!-- local movie object -->\n";
								$menuElementDisplayAry[]="<div class=\"$menuLocalObjectClass\" id=\"$menuLocalObjectId".'div'."\">\n";
								$menuElementDisplayAry[]="<object $menuLocalObjectInsert $menuLocalObjectIdInsert classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">\n";
								$menuElementDisplayAry[]="<param name=\"src\" id=\"$menuLocalParamId\" value=\"\"/>\n";
								$menuElementDisplayAry[]="<param name=\"autoplay\" value=\"true\">\n";
								$menuElementDisplayAry[]="<param name=\"controller\" value=\"false\">\n";
								$menuElementDisplayAry[]="<embed class=\"$menuLocalEmbedClass\" id=\"$menuLocalEmbedId\"src=\"$sourcePath\" width=\"10\" height=\"10\" autoplay=\"true\" controller=\"false\" pluginspage=\"http://www.apple.com/quicktime/download/\">\n";
								$menuElementDisplayAry[]="</embed>\n</object>\n</div>\n";
								*/
								//- do the local object test code
								/*
								$menuElementDisplayAry[]="<!-- local movie object -->\n";
								$menuElementDisplayAry[]="<div class=\"$menuLocalObjectClass\" id=\"$menuLocalObjectId".'div'."\">\n";
								$menuElementDisplayAry[]="<object $menuLocalObjectInsert $menuLocalObjectIdInsert width=\"0\" height=\"0\" classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">\n";
								$menuElementDisplayAry[]="<param name=\"src\" id=\"$menuLocalParamId\" value=\"plug.mov\">\n";
								$menuElementDisplayAry[]="<param name=\"autoplay\" value=\"false\">\n";
								$menuElementDisplayAry[]="<param name=\"controller\" value=\"true\">\n";
								$menuElementDisplayAry[]="<embed class=\"$menuLocalEmbedClass\" id=\"$menuLocalEmbedId\" src=\"/images/oaksbottomboys/oaksbottomvideos/allen.mov\" width=\"0\" height=\"0\"\n";
								$menuElementDisplayAry[]="autoplay=\"false\" controller=\"false\"\n";
								$menuElementDisplayAry[]="pluginspage=\"http://www.apple.com/quicktime/download/\">\n";
								$menuElementDisplayAry[]="</embed>\n";
								$menuElementDisplayAry[]="</object>\n";
								$menuElementDisplayAry[]="</div>\n";
								*/
								//-
								$menuElementDisplayAry[]="<div $menuTextClassInsert $menuTextIdInsert>";
								$menuElementDisplayAry[]="$pictureText";
								$menuElementDisplayAry[]="</div>\n";
								//xxxd done at end: $menuElementDisplayAry[]="</td></tr>\n";
								//xxxd done at end: $menuElementDisplayAry[]="</table>\n";
							} // end if albumpicturefirsttime
							$jsAlbumPicturesAry[]=$sourcePath;
							//xxxf- below need to edit title and text for carraige returns
							$pos=strpos($pictureText,"\n",0);
							if ($pos>-1){
								$pictureText=str_replace("\n","<br>",$pictureText);
							}
							$jsAlbumTitlesAry[]=$pictureTitle;
							$jsAlbumTextAry[]=$pictureText;//xxxdnew
							$jsAlbumMediaTypeAry[]=$mediaType;
							$jsAlbumVideoIdAry[]=$videoId;
						} // end foreach albumpicturesary
						} // end if cnt>0
						$jsMenuAry[]=$jsAlbumPicturesAry;
						//$base->DebugObj->printDebug($jsAlbumPicturesAry,1,'jsapa');//xxx
						$jsMenuTitleAry[]=$jsAlbumTitlesAry;
						$jsMenuTextAry[]=$jsAlbumTextAry;//xxxdnew
						$jsMenuMediaTypeAry[]=$jsAlbumMediaTypeAry;
						$jsMenuVideoIdAry[]=$jsAlbumVideoIdAry;
					} // end if albumname
					break;
				default:
					$menuElementDisplay="<div $menuElementClassInsert $menuIdInsert>'error only works with images'</div>";
			} // end switch elementtype
				//echo "name: $menuName, type: $menuType, elename: $menuElementName, eletype: $menuElementType<br>";//xxxd
			if ($elementFirstTime){
				//echo "-doit<br>";//xxx
				$returnAry[]='<tr>';				
				$returnAry[]="<td $menuElementClassInsert $menuElementIdInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td>";
				$returnAry[]="</tr>\n";
				$elementFirstTime=false;
			} // end if elementfirsttime
		} // end for rowctr=1 < noelements
		//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxx
		$returnAry[]='</table>';
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutitleid']=$menuTitleId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutextid']=$menuTextId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']=$menuType;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuselectedclass']=$menuSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menunonselectedclass']=$menuNonSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['videoheight']=$videoHeight;
		$base->menuProfileAry['jsmenusary'][$menuName]['videowidth']=$videoWidth;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupictureclass']=$menuPictureClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupictureid']=$menuPictureId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuobjectclass']=$MenuObjectClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuobjectid']=$MenuObjectId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuparamclass']=$menuParamClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuparamid']=$menuParamId;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuembedclass']=$menuEmbedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuembedid']=$menuEmbedId;
		if ($menuMaxElements == NULL){$menuMaxElements=0;}
		//echo "menuname: $menuName, maxsize: $menuMaxElements<br>";//xxx
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=$menuMaxElements;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastid']=$lastId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastmenuelementno']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['titles']=$jsMenuTitleAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['text']=$jsMenuTextAry;//xxxdnew
		$base->menuProfileAry['jsmenusary'][$menuName]['media']=$jsMenuMediaTypeAry;//xxxdnew
		$base->menuProfileAry['jsmenusary'][$menuName]['video']=$jsMenuVideoIdAry;//xxxdnew
		//$base->DebugObj->printDebug($base->menuProfileAry['jsmenusary'][$menuName],1,'xxx');
		$base->DebugObj->printDebug("rtn: insertmenuvertical",0);//xx
		return $returnAry;				
	}
	//--------------------------------------- xxxd
	function insertMenuVerticaldeprecated($sortOrder,$menuAry,$menuElementsAry,$base){
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuElementAry=array();
		$menuMaxElements=$menuAry['menumaxelements'];
		$menuName=$menuAry['menuname'];
		$menuType=$menuAry['menutype'];
		//echo "menuname: $menuName<br>";//xxx
	//- class
		$menuClass=$menuAry['menuclass'];
		if ($menuClass != NULL){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert='';}
	//- selectedclass
		$menuSelectedClass=$menuAry['menuselectedclass'];
		if ($menuSelectedClass != ''){$menuSelectedClassInsert="class=\"$menuSelectedClass\"";}
		else {$menuSelectedClassInsert=NULL;}
	//- nonselectedclass	//- setup nonselectedclass
		$menuNonSelectedClass=$menuAry['menunonselectedclass'];
		if ($menuNonSelectedClass != ''){$menuNonSelectedClassInsert="class=\"$menuNonSelectedClass\"";}
		else {$menuNonSelectedClassInsert=NULL;}
	//- paging class
		$menuPagingClass=$menuAry['pagingclass'];
		if ($menuPagingClass != NULL){$menuPagingClassInsert="class=\"$menuPagingClass\"";}
		else {$menuPagingClassInsert=$menuClassInsert;}
	//- id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=NULL;}
		$menuIdInsert="id=\"$menuId\"";
	//- bullet 
		$menuBulletPath=$menuAry['menubulletpath'];
		if ($menuBulletPath == NULL){$menuBulletInsert=NULL;}
		else {$menuBulletInsert="<td $menuClassInsert><img src=\"$menuBulletPath\" $menuClassInsert></td>";}
	//- title
		$menuTitle=$menuAry['menutitle'];
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitle == NULL){$menuTitleInsert=NULL;}
		else {
			if ($menuTitleClass==NULL){$menuTitleClassInsert=NULL;}
			else {$menuTitleClassInsert="class=\"$menuTitleClass\"";}
			$menuTitleInsert="<caption $menuTitleClassInsert>$menuTitle</caption>";
		}
	//- delimiter
		$menuDelimiter=$menuAry['menudelimiter'];
	//- alt
		$menuAlt=$menuAry['menualt'];
		if ($menuAlt == NULL){$menuAltInsert=NULL;}
		else {$menuAltInsert="title=\"$menuAlt\"";}
	//- event
		$menuEvent_raw=$menuAry['menuevent'];
		$menuEvent=$base->UtlObj->returnFormattedString($menuEvent_raw,&$base);
//- start building menu
	//- heading
		$returnAry[]="\n<!-- start verticalmenu: $menuName -->\n";
	//- setup <table ...
		$returnAry[]="<table $menuClassInsert $menuIdInsert $menuEvent>";
	//- setup title
		$returnAry[]=$menuTitleInsert;
	//- setup table cells holding menu items
		$allDone=false;
		$noElements=count($sortOrder);
		if ($menuMaxElements >0 && $menuMaxElements>$noElements){$menuMaxElements=0;}
		//$base->DebugObj->printDebug($menuElementsAry,1,'mea3');//xxx
		//$base->DebugObj->printDebug($sortOrder,1,'sortorder');//xxx
//- loop through menu rows
		$firstTime=true;
		for ($rowCtr=1;$rowCtr<=$noElements;$rowCtr++){
			$menuElementCtr=$sortOrder[$rowCtr];
			$menuElementAry=$menuElementsAry[$menuElementCtr];
			//$base->DebugObj->printDebug($menuElementAry,1,'mea2');//xxx
			$menuElementName=$menuElementAry['menuelementname'];
			if ($rowCtr==1){
				$lastId=$menuElementAry['menuelementid'];
				//echo "lastId: $lastId, menuelementname: $menuElementName<br>";
				if ($lastId==NULL){$lastId=$menuElementName;}
			}
			if ($menuMaxElements > 0 && $noElements > $menuMaxElements && $rowCtr==$menuMaxElements){
				$returnAry[]="<tr><td $menuClassInsert><a href=\"#\" $menuIdInsert $menuPagingClassInsert onclick=\"pageNextV2('$menuName');\">-more-</a></td></tr>";
				$allDone=true;
			}
			$menuElementUrl_raw=$menuElementAry['menuelementurl'];
			$menuElementUrl=$base->UtlObj->returnFormattedString($menuElementUrl_raw,&$base);
//- get class
			$menuElementClass=$menuElementAry['menuelementclass'];
			$menuElementClass_td=$menuElementClass.'_td';
			if ($menuElementClass != NULL){
				$menuElementClassInsert=" class=\"$menuElementClass\"";
				$menuElementClassTdInsert=" class=\"$menuElementClass_td\"";
			}
			else {$menuElementClass=$menuClass;$menuElementClassInsert=$menuClassInsert;$menuElementTdClassInsert=NULL;}
//- selected class - has a selected field
			$menuElementSelectedFieldName=$menuElementAry['menuelementselectedfieldname'];
			$menuElementSelectedFieldValue=$menuElementAry['menuelementselectedfieldvalue'];
			if ($menuElementSelectedFieldName != NULL){
				$testForSelectedClass=$base->paramsAry[$menuElementSelectedFieldName];
				if ($menuElementSelectedFieldName == 'always'){$doit=true;}
				elseif ($testForSelectedClass == $menuElementSelectedFieldValue && $menuSelectedClassInsert != NULL){
					$doit=true;
				}
				else {$doit=false;}
				if ($doit){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;	
				}
				else {
					$useMenuElementClassInsert=$menuClassInsert;
					$useMenuElementClass=$menuClass;
				}
			}
//- selected class - no selected field, so look at jobname
			else {
				$jobName=$base->jobProfileAry['jobname'];
				$menuElementUrlAry=explode('&',$menuElementUrl);
				$menuElementUrlTest=$menuElementUrlAry[0];
				if ($menuElementUrlTest == $jobName && $menuSelectedClassInsert != NULL){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;
				}
				else {
					$useMenuElementClassInsert=$menuElementClassInsert;
					$useMenuElementClass=$menuElementClass;
				}
			}
//- get id
			$menuElementId=$menuElementAry['menuelementid'];
			if ($menuElementId==NULL){$menuElementId=$menuElementAry['menuelementname'];}
			$menuElementIdInsert="id=\"$menuElementId\"";
			$menuElementIdTdInsert="id=\"$menuElementId_td\"";
//- get label and modify and add events if needed
			$menuElementLabel_raw=$menuElementAry['menuelementlabel'];
			$menuElementLabel=$base->UtlObj->returnFormattedString($menuElementLabel_raw,&$base);
			$menuElementEventAttributes_raw=$menuElementAry['menuelementeventattributes'];
			$menuElementEventAttributes=$base->UtlObj->returnFormattedString($menuElementEventAttributes_raw,&$base);
			$useMenuElementLabel_div="<div $useMenuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementLabel_div="<div $menuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementType=$menuElementAry['menuelementtype'];
			if ($menuElementType == NULL){$menuElementType='url';}
//- change label positions with !!xxx!!
			if (strpos($menuElementLabel,'!!',0) !== false) {
				$doLabelInsert=true;
				$menuLineAry=$base->HtmlObj->convertHtmlLine($menuElementLabel,&$base);
				//echo "menulineary: $menuLineAry<br>";//xxx
			} // end if strpos!!
			else {$doLabelInsert=false;}
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementNo=$menuElementAry['menuelementno'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;			
			//echo "name: $menuElementName, type: $menuElementType<br>";//xxx
			//$base->DebugObj->printDebug($menuElementAry,1,'mea');//xxx
			if (!$firstTime && $menuDelimiter != NULL){
				$returnAry[]="<tr><td class=\"menudelimiter\"><div class=\"menudelimiter\">$menuDelimiter</div></td></tr>\n";
			}
			//- xxxf problem needs to be fixed later
			if ($menuElementType == 'paragraph'){$menuElementType='para';}
			switch ($menuElementType){
//- element is url
			case 'url':
				//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
				$htmlElementAry=array();
				$htmlElementAry['label']=$menuElementLabel;
				$htmlElementAry['htmlelementclass']=$menuElementClass;
				$htmlElementAry['joblink']=$menuElementUrl;	
				$htmlElementAry['htmlelementeventattributes']=$menuElementEventAttributes;
				$workAry=$base->HtmlObj->buildUrl($htmlElementAry,&$base);
				$menuElementUrl_html=$base->UtlObj->returnFormattedData($menuElementUrl,'url','html',&$base);
				if (!$allDone){
					$returnAry[]='<tr>';
					$returnAry[]="<td $useMenuElementClassInsert>";
					//$returnAry[]="<li>";
					$returnAry[]=$menuBulletInsert;
					$returnAry=array_merge($returnAry,$workAry);
					//$returnAry[]="</li>";
					$returnAry[]='</tr>';
				} // end if !alldone
				$jsMenuAry[]="$menuBulletInsert<a href=\"$menuElementUrl_html\" $menuElementClassInsert $menuElementIdInsert $menuAltInsert>$menuElementLabel</a>";
				break;
			case 'para':
				$menuElementName=$menuElementAry['menuelementname'];
				$menuElementNameAry=explode('_',$menuElementName);
				$menuElementName=$menuElementNameAry[0];
				$paramFeed=array('param_1'=>$menuElementName);
				//$base->DebugObj->printDebug($paramFeed,1,'xxxf');exit();//xxxf
				$subReturnAry=$base->Plugin002Obj->insertParagraph($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]="</td></tr>";
				break;
			case 'table':
				$tableName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$tableName);
				$menuElementDisplayAry=$base->TagObj->insertTable($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'menu':
				$menuName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$menuName);
				$menuElementDisplayAry=$this->insertMenu($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'map':
				$mapProfileId=$menuElementAry['mapprofileid'];
				$mapName=$base->mapProfileAry['main'][$mapProfileId]['mapname'];
				$menuElementDisplayAry=$base->HtmlObj->buildMap($mapName,&$base);
				$returnAry[]='<tr>';
				$returnAry[]="<td $menuElementClassInsert>\n";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'form':
				$passAry=array();
				$passAry['param_1']=$menuElementName;
				$subReturnAry=$base->TagObj->insertForm($passAry,&$base);
				$returnAry[]='<tr><td>';
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]='</td></tr>';
				//$base->DebugObj->printDebug($subReturnAry,1,'srtn');//xxx
				//exit();//xxx			
				break;	
			case 'repeatingform':
				$passAry=array();
				$query_raw=$menuElementAry['menuelementsql'];
				//-below let querytable do the formatting
				//$query=$base->UtlObj->returnFormattedString($query_raw,&$base);
				//!!! - below needs to check if using db2
				$useOtherDb_raw=$base->formProfileAry[$menuElementName]['formuseotherdb'];
				$useOtherDb=$base->UtlObj->returnFormattedData($useOtherDb_raw,'boolean','internal');
				if ($useOtherDb){$base->DbObj->setUseOtherDb(&$base);}
				$result=$base->DbObj->queryTable($query_raw,'read',&$base,0);
				$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				//echo "query: $query_raw";//xxxf
				//$base->DebugObj->printDebug($workAry,1,'xxxf');
				$passAry['param_1']=$menuElementName;
				foreach ($workAry as $ctr=>$workRowAry){
					//echo "$ctr<br>";//xxx
					$base->paramsAry['ctr']=$ctr;
					$tst=$base->paramsAry['ctr'];//xxxf
					//echo "ctr in paramsary in vmenu: $tst<br>";//xxxf
					$workRowAry['ctr']=$ctr;
					$passAry['usethisdataary']=$workRowAry;
					$tabIndexBase=($ctr+1)*10;
					$passAry['tabindexbase']=$tabIndexBase;
					//$base->DebugObj->printDebug($workRowAry,1,'xxxworkrowary');
					//echo "build a form<br>";//xxxd
					$subReturnAry=$base->TagObj->insertForm($passAry,&$base);
					unset ($passAry['usethisdata']);
					$returnAry[]="<tr><td>\n";
					$headingStr="<!-- Form Number: $ctr -->\n";
					//echo "$headingStr<br>";//xxx
					$returnAry[]=$headingStr;
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="</tr></td>\n";
				}
				//- need to write how many forms have been created here
				$base->formProfileAry[$menuElementName]['formcount']=$ctr;
				//$base->DebugObj->printDebug($returnAry,1,'rtnary');//xxx
				//exit(0);//xxx
				break;
				case 'album':
					$albumProfileId=$menuElementAry['albumprofileid'];
					//echo "albumprofileid: $albumProfileId<br>";//xxx
					$passAry=$base->HtmlObj->buildAlbumTable($albumProfileId,&$base);
					$albumTableDisplayAry=$passAry['returnary'];
					$albumName=$passAry['albumname'];
					if (!array_key_exists('jsary',$base->albumProfileAry)){$base->albumProfileAry['jsary']=array();}
					$base->albumProfileAry['jsary'][$albumName]=$passAry[$albumName];
					//$base->DebugObj->printDebug($albumTableDisplayAry,1,'atdaxxxa');
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$returnAry=array_merge($returnAry,$albumTableDisplayAry);
					$returnAry[]="</td></tr>\n";
				break;
				case 'image':
					$imageName=$menuElementName;
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$subReturnAry=$base->HtmlObj->buildImg($imageName,&$base);
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="<span $menuElementClassInsert>$menuElementLabel</span";
					$returnAry[]="</td></tr>\n";
				break;
			default:
//- element is text
				if (!$allDone){
					//$base->DebugObj->printDebug($menuElementAry,1,'mea');//xxx
					//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
					$returnAry[]='<tr>';
					if ($doLabelInsert){
						$returnAry[]="<td $menuElementIdInsert $useMenuElementClassInsert>\n";
						//$returnAry[]="<li>";
						$returnAry=array_merge($returnAry,$menuLineAry);
						//$returnAry[]="</li>";
						$returnAry[]="</td>\n";
					} // end dolabelfirst
					else {
						$returnAry[]="$menuBulletInsert<td $menuElementIdTdInsert $menuElementClassTdInsert>$useMenuElementLabel_div</td>\n";
					} // end else for dolabelfirst
					$returnAry[]='</tr>';
				} // end if !alldone
				$jsMenuAry[]="$menuElementLabel_div";
			} // end switch menuelementtype
			$firstTime=false;
		} // end for rowctr = 1 - 99
		//exit(0);//xxx
		$returnAry[]="</table>\n";
		$returnAry[]="<!-- end verticalmenu: $menuName -->\n";
		//$returnAry[]='</ul>';
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuselectedclass']=$menuSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menunonselectedclass']=$menuNonSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']='verticle';
		$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;		
		if ($menuMaxElements == NULL){$menuMaxElements=0;}
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=$menuMaxElements;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastid']=$lastId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastmenuelementno']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;
		$base->DebugObj->printDebug("rtn: insertmenuvertical",0);//xx
		return $returnAry;				
	}
//=======================================
	function insertStyle($paramFeed,$base){
		$base->DebugObj->printDebug("Plugin002Obj:status()",0); //xx (h)
		$returnAry=array();
		//$base->DebugObj->printDebug($base->cssProfileAry,1,'xxxcssprofileary');
		$returnAry[]="\n<style>\n";
		$tempAry=array('id','class','none');
		$theCnt=count($tempAry);
		if ($theCnt>0){
			foreach ($tempAry as $rowNo=>$selectorType){
				$theCnt=count($base->cssProfileAry[$selectorType]);
				$insertPrefix=NULL;
				//$base->DebugObj->printDebug($base->cssProfileAry[$selectorType],1,'xxx: '.$selectorType);
				if ($theCnt>0){
					foreach ($base->cssProfileAry[$selectorType] as $styleSelector=>$styleTags){
						//$base->DebugObj->printDebug($styleTags,1,'xxx');
						//- delim
						if ($selectorType == 'id'){$delim='#';}
						else {$delim='.';}
						//- prefix
						$prefix=$base->cssProfileAry['prefix'][$selectorType][$styleSelector];
						$prefix='none';// for now prefix is always none
						if ($prefix==NULL || $prefix=='none'){$insertPrefix=NULL;}
						else {$insertPrefix=$prefix.' ';}
						//- tags
						foreach ($styleTags as $tag=>$tagAry){
							if ($tag=='none'){$tag=NULL;}
							if ($styleSelector == 'none'){$styleSelectorUse=NULL;}
							else {$styleSelectorUse=$styleSelector;}
							//- build selector line
							$returnAry[]="$insertPrefix$tag$delim$styleSelectorUse{\n";
							//- build each property line
							foreach ($tagAry as $property_raw=>$value){
								$propertyAry=explode('_',$property_raw);
								$property=$propertyAry[0];
								$styleLine="$property:$value;\n";
								$returnAry[]=$styleLine;
							}
							$returnAry[]="}\n";
						} // end foreach styletags
					} // end foreach cssprofileary[selectortype]
				} // end count of above	
			} // end foreach tempary
		} // end count of above
		$returnAry[]='</style>'."\n";		
		return $returnAry;
		$base->DebugObj->printDebug("-rtn:xx",0); //xx (f)
	}
//=======================================
	function insertAlbumEntries($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'par');//xxx
		$albumProfileId=$base->paramsAry['albumprofileid'];
		$dirPath=$base->paramsAry['dirpath'];
		$nameFilter=$base->paramsAry['namefilter'];
		$imageFormat=$base->paramsAry['imageformat'];
//- get any album that is already on file
		$query="select * from pictureprofileview where albumprofileid='$albumProfileId'";
		//echo "query: $query<br>";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'picturefilename');
		$currentPictureAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$cnt=count($currentPictureAry);//xxxf
		//echo "currentpictureary cnt: $cnt<br>";//xxxf
		$largestNumber=0;
		foreach ($currentPictureAry as $pictureFileName=>$pictureAry){
			$pictureNo=$pictureAry['pictureno'];
			if ($pictureNo>$largestNumber){$largestNumber=$pictureNo;}		
		}
		//$base->DebugObj->printDebug($dataAry,1,'xxxa:dataary');
		//echo "id: $albumProfileId, dir: $dirPath, filter: $nameFilter, format: $imageFormat<br>";//xxx
		$passAry=array('directorypath'=>$dirPath,'namefilter'=>$nameFilter,'imageformat'=>$imageFormat);
		$returnAry=$base->FileObj->retrieveFileNamesV2($passAry,&$base);
		sort($returnAry);
		$cnt=count($returnAry);
		if ($cnt>200){$cnt=200;}
		//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxx
		$writeRowsAry=array();
		$pictureAry=array('albumprofileid'=>$albumProfileId);
		$albumEntryCtr=0;
		$dupeCheck=array();
		$processTypeCheck=array();
		for ($ctr=0;$ctr<$cnt;$ctr++){
			$pictureFileName=$returnAry[$ctr];
			$pictureFileNameTest=strtoupper($pictureFileName);
			$pos=strpos($pictureFileNameTest,'_SM');
			if ($pos){
				$lastCharBefore=$pos;
				$firstCharAfter=$pos+3;
				$lastChar=strlen($pictureFileName);
				$firstChar=0;
				$preFileName=substr($pictureFileName,$firstChar,$lastCharBefore);
				$postFileName=substr($pictureFileName,$firstCharAfter,$lastChar);
				$pictureFileName=$preFileName.$postFileName;
			}
//--- see if <name>.jpg, <name>_sm.jpg, <name>_tbnl.jpg already done - only one line for all
			$doit=true;
			$err=0;
			if (array_key_exists($pictureFileName,$processTypeCheck)){$doit=false;$err=1;}
			if (array_key_exists($pictureFileName,$currentPictureAry)){$doit=false;$err=2;}
			//if ($pictureFileName=='allenatoaksbottom.png') {
			//echo "$pictureFileName, doit: $doit, err: $err<br>";//xxxf
			//$base->DebugObj->printDebug($currentPictureAry,1,'xxxf');//xxxf
			//}
			if ($doit){
			$processTypeCheck[$pictureFileName]='xxx';
			$pictureAry['picturefilename']=$pictureFileName;
			$pictureFileNameAry=explode('.',$pictureFileName);
			$pictureName=$pictureFileNameAry[0];
			$pictureNameSuffix=0;
//--- see if <samename>.jpg and <samename>.png, etc. exists - then dupe it
			$iAmDone=false;
			while (!$iAmDone){
				if ($pictureNameSuffix>0){$usePictureName=$pictureName.'_'.$pictureNameSuffix;}
				else {$usePictureName=$pictureName;}
				if (in_array($usePictureName,$dupeCheck)){$pictureNameSuffix++;}
				else {
					$iAmDone=true;
					$dupeCheck[$usePictureName]='xxx';	
				}
			}
			$pictureAry['picturename']=$usePictureName;
			$pictureAry['picturedirectory']=$dirPath;
			$pictureAry['picturetype']='active';
			$pictureAry['pictureno']=($ctr+1)*10+$largestNumber;
			$writeRowsAry[]=$pictureAry;
			}
		}
		$dbControlsAry=array('dbtablename'=>'pictureprofile');
    	$dbControlsAry['writerowsary']=$writeRowsAry;
    	//$base->DebugObj->printDebug($dbControlsAry,1,'db');//xxx
    	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//=======================================
	function clearSessionBuffer($base){
		$sessionName=$base->paramsAry['sessionname'];
		if ($sessionName != NULL){
			$sessionUpdateName=$base->paramsAry['savetosession'];
			if ($sessionUpdateName != NULL){
				$oldSessionAry=$_SESSION['sessionobj']->getSessionAry($sessionName);
				$sessionUpdateValue=$oldSessionAry[$sessionUpdateName];
			}
			$_SESSION['sessionobj']->clearSessionAry($sessionName);
			if ($sessionUpdateName != NULL){
				$updateSessionAry=array();
				$updateSessionAry[$sessionUpdateName]=$sessionUpdateValue;
				$_SESSION['sessionobj']->saveSessionAry($sessionName,$updateSessionAry);	
			}
		}
	}
//=======================================
	function insertMeta($paramFeed,$base){
		$base->DebugObj->printDebug("insertMeta('base')",0); //xx 	
	}
//=======================================
	function insertMap($paramFeed,$base){
		$base->DebugObj->printDebug("insertMap('base')",0); //xx	
		$mapName=$paramFeed['param_1'];
		$returnAry=$base->HtmlObj->buildMap($mapName,&$base);
		$base->DebugObj->printDebug("-rtn:insertmap"); //xx
		return $returnAry;
	}
//=======================================
	function insertImg($paramFeed,$base){
		$base->DebugObj->printDebug("insertImg('base')",0); //xx
		$imageName=$paramFeed['param_1'];
		$urlAry=array();
		//echo "name: $imageName<br>";//xxx
		$imageAry=$base->imageProfileAry[$imageName];
		$urlAry['label']=$imageAry['imagelabel'];
		$imageSource_raw=$imageAry['imagesource'];
		$imageSource=$base->UtlObj->returnFormattedString($imageSource_raw,&$base);
		$imageAlt=$imageAry['imagealt'];
		$urlAry['joblink']=$imageSource;
		$urlAry['imageusemap']=$imageAry['imageusemap'];
		$urlAry['htmlelementimagename']=$imageName;
		$urlAry['imagealt']=$imageAlt;
		$urlAry['htmlelementclass']=$imageAry['imageclass'];
//- events
		$eventFromOther=$paramFeed['events'];
		$eventFromImg=$imageAry['imageevents'];
		if ($eventFromImg == null){$useEvent=$eventFromOther;}
		else {$useEvent=$eventFromImg;}
		$urlAry['htmlelementeventattributes']=$useEvent;
		//$base->DebugObj->printDebug($imageAry,1,'urlary');//xxx
		//-
		$imageClass=$imageAry['imageclass'];
//- image id
    	$imageId=$imageAry['imageid'];
    	if ($imageId == NULL){$imageIdInsert=NULL;}
    	else{$imageIdInsert="id=\"$imageId\"";}
//- imagetitle id
    	if ($imageId == NULL){$imageTitleIdInsert=NULL;}
    	else{$imageTitleIdInsert="id=\"$imageId".'title'."\"";}
//- imagecaption
    	if ($imageId == NULL){$imageCaptionIdInsert=NULL;}
    	else{$imageCaptionIdInsert="id=\"$imageId".'caption'."\"";}
//-
		$urlAry['imageid']=$imageId;
		if ($imageClass != NULL){$imageClassInsert="class=\"$imageClass\"";}
		else {$imageClassInsert=NULL;}
		$imageType=$imageAry['imagetype'];
		$imageTitle=$imageAry['imagetitle'];
		if ($imageTitle==NULL){$imageTitle=$imageName;}
		$imageText=$imageAry['imagetext'];
		$imageAry_html=$base->HtmlObj->buildOldImg($urlAry,&$base);
		$returnAry=array();
		switch ($imageType){
			case 'image':
				$returnAry=$imageAry_html;
				break;
			case 'textonright':
				$returnAry[]="<table $imageClassInsert>\n<tr><td xyz=8>\n";
				$returnAry=array_merge($returnAry,$imageAry_html);
				$returnAry[]="</td><td>\n";
				$returnAry[]="<table $imageClassInsert $imageIdInsert><tr><td>\n";
				$returnAry[]="<div $imageClassInsert $imageTitleIdInsert>$imageTitle</div>\n";
				$returnAry[]="</td></tr><tr><td>\n";
				$returnAry[]="<div $imageClassInsert $imageIdInsert>$imageText</div>\n";
				$returnAry[]="</td></tr></table>\n";
				$returnAry[]="</td></tr></table>\n";
				break;
			case 'textonleft':
				break;
			case 'textbelow':
				break;
	      	case 'titleattop':
    		    $imageTitleClass=$imageClass.'title';
        		$imageTitleClassInsert="class=\"$imageTitleClass\"";
    		    $returnAry[]="<table $imageClassInsert>\n<tr><td $imageTitleClassInsert>\n";
        		$returnAry[]="<div $imageTitleClassInsert $imageTitleIdInsert>$imageTitle</div>\n";
        		$returnAry[]="</td></tr>\n";
        		$returnAry[]="<tr><td $imageClassInsert>";
        		$returnAry=array_merge($returnAry,$imageAry_html);
        		$returnAry[]="</td></tr></table>\n";
        		break;
			default:		
				$returnAry=$imageAry_html;
		}
		$base->DebugObj->printDebug("-rtn:insertimg",0); //xx
		return $returnAry;
	}
//========================================= plugin: operation
	function insertImages($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'par');//xxx
		$selectionString=$base->paramsAry['selectionstring'];
		$sourceDir=$base->paramsAry['sourcedirectory'];
		$prefixName=$base->paramsAry['prefixname'];
		$imageClass=$base->paramsAry['imageclass'];
		$imageFormat=$base->paramsAry['imageformat'];
		$returnAry=$base->FileObj->retrieveFileNames($sourceDir,$selectionString,&$base);
		sort($returnAry);
		$cnt=count($returnAry);
		if ($cnt>20){$cnt=20;}
		$writeRowsAry=array();
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$imageAry['jobprofileid']=$jobProfileId;
		$imageAry['imagetype']='image';
		$imageAry['imageclass']=$imageClass;
		$imageCtr=0;
		for ($ctr=0;$ctr<$cnt;$ctr++){
			$imageFileName=$returnAry[$ctr];
			if ($imageFormat != 'all'){
				$imageFileNameAry=explode('.',$imageFileName);
				$checkImageFormat=$imageFileNameAry[1];
				//echo "check: $checkImageFormat, want: $imageFormat<br>";//xxx
				if ($checkImageFormat == $imageFormat){$doit=true;}
				else {$doit=false;}
			}
			else {$doit=true;}
			//echo "doit: $doit<br>";//xxx
			if ($doit){
				$imageName=$prefixName.$imageCtr;
				$imageCtr++;
				$imageAry['imagename']=$imageName;
				$imageAry['imagesource']="$sourceDir/$imageFileName";
				$writeRowsAry[]=$imageAry;
			}			
		}
		$dbControlsAry=array('dbtablename'=>'imageprofile');
    	$dbControlsAry['writerowsary']=$writeRowsAry;
    	//$base->DebugObj->printDebug($dbControlsAry,1,'db');//xxx
    	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//=========================================
	function insertParagraph($paramFeed,$base){
		$base->DebugObj->printDebug("Plugin002Obj:status()",0); //xx (h)
		$returnAry=array();
		$paragraphName=$paramFeed['param_1'];
		//$base->DebugObj->printDebug($base->paragraphProfileAry,1,'par');//xxx
		$paragraphAry=$base->paragraphProfileAry[$paragraphName];
		//-
		$paragraphClass=$paragraphAry['paragraphclass'];
		//echo "name: $paragraphName, class: $paragraphClass<br>";//xxxf
		if ($paragraphClass == null){$paragraphClassInsert=null;}
		else {$paragraphClassInsert="class=\"$paragraphClass\"";}
		//echo "$paragraphClassInsert<br>";//xxx
		//-
		$paragraphVisibility=$paragraphAry['paragraphvisibility'];
		if ($paragraphVisibility == NONE){$paragraphVisibility='none';}
		//-
		$paragraphValue=$paragraphAry['paragraphvalue'];
		//-
		$paragraphType=$paragraphAry['paragraphtype'];
		if ($paragraphType == NULL){$paragraphType='div';}
		//-
		$showParagraph=false;
		if ($paragraphVisibility != 'always'){
			if(is_object($base->cartObj)){$noCartItems=$base->cartObj->count_items();}
			else $noCartItems=0;
		}
		$base->paramsAry['nocartitems']=$noCartItems;
		switch ($paragraphVisibility){
			case 'nocartitemsgt':
				if ($noCartItems > $paragraphValue){$showParagraph=true;}
				break;
			case 'nocartitemseq':
				if ($noCartItems == $paragraphValue){$showParagraph=true;}
				break;
			case 'nocartitemslt':
				if ($noCartItems < $paragraphValue){$showParagraph=true;}
				break;
			case 'always':
				$showParagraph=true;
				break;
			default:
				$showParagraph=true;
		}
		if ($showParagraph){
		$sentencesAry=$base->sentenceProfileAry[$paragraphName];
		$sentenceOrderAry=$base->sentenceProfileAry['sortorderary_'.$paragraphName];
		//$base->DebugObj->printDebug($sentenceOrderAry,1,'sentenceorderary');//xxx
		$beginComment="<!-- start paragraph: $paragraphName -->";$endComment="<!-- end paragraph: $paragraphName -->";
		if ($paragraphType=='span'){$beginDivider="<span $paragraphClassInsert>";$endDivider="</span>";$divider_front='<span';$divider_back='</span>';}
		else if($paragraphType=='ul'){$beginDivider="<ul $paragraphClassInsert>";$endDivider='</ul>';$divider_front='<li';$divider_back='</li>';}
		else if($paragraphType=='ol'){$beginDivider="<ol $paragraphClassInsert>";$endDivider='</ol>';$divider_front='<li';$divider_back='</li>';}
		else {$beginDivider="<div $paragraphClassInsert>";$endDivider="</div>";$divider_front='<div';$divider_back='</div>';}
		//need to do front and back dividers xxx
		$returnAry[]=$beginComment."\n";
		$returnAry[]=$beginDivider."\n";
		$noSentences=count($sentenceOrderAry);
		for ($lp=1;$lp<=$noSentences;$lp++){
			$sentenceName=$sentenceOrderAry[$lp];
			$sentenceAry=$sentencesAry[$sentenceName];
			$sentenceText=$sentenceAry['sentencetext'];
			$sentenceUrl=$sentenceAry['sentenceurl'];
			$sentenceVisibility=$sentenceAry['sentencevisibility'];
			$sentenceValue=$sentenceAry['sentencevalue'];
			$sentenceBreak_array=$sentenceAry['sentencebreak'];
			$sentenceBreak=$base->UtlObj->returnFormattedData( $sentenceBreak_array, 'boolean', 'internal');
			$showSentence=false;
			switch ($sentenceVisibility){
				case 'nocartitemsgt':
					if ($noCartItems > $sentenceValue){$showSentence=true;}
				break;
				case 'nocartitemseq':
					if ($noCartItems == $sentenceValue){$showSentence=true;}
				break;
				case 'nocartitemslt':
					if ($noCartItems < $sentenceValue){$showSentence=true;}
				break;
				case 'always':
					$showSentence=true;
					break;
				default:
					$showSentence=true;
			}
			if ($sentenceBreak){
				$insertSentenceBreak="<br>";
			}
			else {
				$insertSentenceBreak=NULL;
			}
			$sentenceClass=$sentenceAry['sentenceclass'];
			if ($sentenceClass == NULL){$sentenceClass=$paragraphClass;}
			if ($sentenceClass != NULL){$insertSentenceClass=" class=\"$sentenceClass\"";}
			else {$insertSentenceClass=NULL;}
			$sentenceId=$sentenceAry['sentenceid'];
			if ($sentenceId != NULL){$insertSentenceId=" id=\"$sentenceId\"";}
			else {$insertSentenceId=NULL;}
			$sentenceType=$sentenceAry['sentencetype'];
			$sentenceText_good=$base->UtlObj->returnFormattedString($sentenceText,&$base);
			//xxxf22
			//echo "sentenceName: $sentenceName before: $sentenceText, after: $sentenceText_good<br>";//xxxf22
			//echo "sentencetype: $sentenceType<br>";//xxx
			if ($showSentence){	
				if ($sentenceType == 'text'){
					$sentenceLine="$divider_front $insertSentenceClass $insertSentenceId>$sentenceText_good $divider_back $insertSentenceBreak";
					}
				else {
					$jobLink=$sentenceAry['sentenceurl'];
					$jobLink_html=$base->UtlObj->returnFormattedData($jobLink,'url','html',&$base);
					$sentenceLine="<a href=\"$jobLink_html\" $insertSentenceId $insertSentenceClass>$sentenceText_good</a>$insertSentenceBreak";
				}
				$returnAry[]="$sentenceLine\n";
			}
		} // end next
		$returnAry[]=$endDivider."\n";
		$returnAry[]=$endComment."\n";
		} // end if
		//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxxf
		$base->DebugObj->printDebug("-rtn:xx",0); //xx (f)
		return $returnAry;
	}
//======================================= deprecated - has dbtablemetaprofile stuff
	function deprecatedreadDbUpdate($base){
		$base->DebugObj->printDebug("Plugin002Obj:readDbUpdate('base')",0); //xx (h)
		$dbTableName=$base->paramsAry['dbtablemetaname'];
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$query="select * from $dbTableName";
		$res=$base->DbObj->queryTable($query,'retrieve',&$base);
	 	$noFields = pg_num_fields($res);
	 	$writeRowsAry=array();
 	 	for ($ctr = 0; $ctr < $noFields; $ctr++) {
 	  	 	$fieldName = pg_field_name($res, $ctr);
	   	 	$fieldType = pg_field_type($res, $ctr);
	   	 	if (!array_key_exists($fieldName,$dbTableMetaAry)){
	   	 		$pos=strpos('x'.$fieldName,'bad',0);
	   	 		//echo "xxx: $fieldName, $pos<br>";
	   	 		if ($pos<1){
	   	 		$writeRowsAry[]=array('dbtablemetaname'=>$dbTableName,'dbcolumnname'=>$fieldName,'dbcolumntype'=>$fieldType,'validateprofileid'=>1);
	   	 		}
	   	 	}
 	 	}
 	 	$dbControlsAry=array('dbtablename'=>'dbtablemetaprofile');
 	 	$dbControlsAry['writerowsary']=$writeRowsAry;
 	 	//$base->DebugObj->printDebug($dbControlsAry,1,'dbca');
 	 	$base->DbObj->writeToDb($dbControlsAry,&$base);
 	 	//$base->DebugObj->printDebug($writeRowsAry,1,'wra');//xxx
		$base->DebugObj->printDebug("-rtn:setPrio",0); //xx (f)
	}
//=======================================
	function setPrio($base){
		$base->DebugObj->printDebug("Plugin002Obj:setPrio('base')",0); //xx (h)
		$newPrio=$base->paramsAry['newpriority'];
		$dbTableName=$base->paramsAry['dbtablename'];
		if ($dbTableName == ''){$dbTableName='thingstodo';}
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$base->paramsAry[$keyName];
		$dataRowsAry[]=array($keyName=>$keyValue);
		$dbControlsAry['datarowsary']=$dataRowsAry;
		$dataAry=$base->DbObj->readFromDb($dbControlsAry,&$base);
		$priority=$dataAry[0]['priority'];
		//echo "priority: $priority, newpriority: $newPrio";exit();//xxxf
		$colRegEx='/^[0-9]*$/';
		$itIsAnInteger = $base->UtlObj->checkData($colRegEx,$priority);
		if ($itIsAnInteger){
			if ($newPrio != NULL){$priority=$newPrio;}
			if ($priority<=0){$priority=0;}
			if ($priority>4){$priority=4;}
			$dataAry[0]['thingstodopriority']=$priority;
			//fix date
			$entryDate_raw=$dataAry[0]['entrydate'];
			$entryDate=$base->UtlObj->convertDate($entryDate_raw,'date1',&$base);
			$dataAry[0]['entrydate']=$entryDate;
			$dbControlsAry['writerowsary']=$dataAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();
		}
		//$base->DebugObj->printDebug($dbControlsAry['writerowsary'],1,'db');//xxxf
		//exit();
	  	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);	
	  	if ($successBool != 1){
	  		echo "error: ".$successBool;
	  	}
	  	else {
		  	echo 'okdonothing';	
	  	} 
		$base->DebugObj->printDebug("-rtn:setPrio",0); //xx (f)
	}
//===============internal methods================
//=======================================
	function status(){
		$base->DebugObj->printDebug("Plugin002Obj:status()",0); //xx (h)
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
		$base->DebugObj->printDebug("-rtn:xx",0); //xx (f)
	}
//=======================================
	function errorOut($errorMsg){
		echo "fatal error: $errorMsg!!!!!";
		exit();
	}
//=======================================
	function incCalls(){$this->callNo++;}
//=======================================
	function d($theVariable,$theVariableName){
		$this->base->DebugObj->printDebug($theVariable,1,$theVariableName);
	}
}
