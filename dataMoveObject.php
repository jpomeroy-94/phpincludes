<?php 
class dataMoveObject{
	var $fromConn = null;
	var $toDbConn = null;
//===========================================
	function dataMoveObject($fromConn,$toConn,$base) {
		$this->fromDbConn=$fromConn;
		$this->toDbConn=$toConn;
	}
//===========================================
	function copyContainer($passAry,$base){
		$containerName=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copycontainer containername: $containerName, fromjobprofileid: $fromJobProfileId, tojobprofileid: $toJobProfileId",&$base);//xxxf
//--- copy across containerprofile record
		$query="select * from containerprofile where jobprofileid=$fromJobProfileId and containername='$containerName'";
		$passAry=array();
		$containerAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
		$containerName=$containerAry[0]['containername'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copycontainer write container: $containerName",&$base);//good query
		$theCnt=count($containerAry);
		if ($theCnt == 1){
//- prepare for write
		$writeRowsAry=$containerAry;//make sure only 1 is written
		$writeRowsAry[0]['jobprofileid']=$toJobProfileId;
		$fromContainerProfileId=$writeRowsAry[0]['containerprofileid'];
		unset($writeRowsAry[0]['containerprofileid']);
//- setup and write
		$dbControlsAry=array('dbtablename'=>'containerprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,&$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copycontainer wrote new container successbool: $successBool",&$base);// works
		if ($successBool){
//--- get new containerprofileid of record just written
			$query="select containerprofileid  from containerprofile where jobprofileid=$toJobProfileId and containername='$containerName'";
			$passAry=array();
			$returnAry=$base->ClientObj->queryClientDbTableRead($query,$this->toDbConn,'read',$passAry,&$base);
			$toContainerProfileId=$returnAry[0]['containerprofileid'];
			$base->FileObj->writeLog('debug',"dataMoveObj.copycontainer got new tocontainerprofileid: $toContainerProfileId",&$base);//error returns a null
//--- copy across containerelementprofile records
			if ($toContainerProfileId != null){
			$passAry=array();
			$query="select * from containerelementprofile where containerprofileid=$fromContainerProfileId";
			$base->FileObj->writeLog('debug',"datamoveobj.copycontainer query: $query",&$base);
			$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
//- prepare for write
			$theCnt=count($writeRowsAry);
			$base->FileObj->writeLog('debug',"datamoveobj.copycontainer containerelements retrieved: $theCnt",&$base);
			$strg=null;
			for ($theLp=0;$theLp<$theCnt;$theLp++){
				$writeRowsAry[$theLp]['containerprofileid']=$toContainerProfileId;
				unset($writeRowsAry[$theLp]['containerelementprofileid']);  
				$containerElementName=$writeRowsAry[$theLp]['containerelementname'];
				$strg.="containerelement $theLp: $containerElementName\n";
			}
			$base->FileObj->writeLog('debug',"datamoveobj.copycontainer elements: $strg",&$base);
//- setup and write
			$dbControlsAry=array('dbtablename'=>'containerelementprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
			if ($successBool){
				$msg= "copied the container($containerName) from jobprofileid $fromJobProfileId to $toJobProfileId";
			}
			else {
				$msg="Error!!! Could not write one of the elements for container ($containerName)";
			}
		}
		else {
			$msg="Error!!! container($containerName) probably already on file!";
		}
		}
		else {
			$msg="Error!!! container($containerName) could not get new containerprofileid!";
		}
		}
		else {
			$msg="Error!!! $theCnt containers were selected, but only one should be!";
		}
//-
		return $msg;
	}
//===========================================
	function copyCss($passAry,$base){
		$prefix=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copyCss prefix: $prefix, fjpi: $fromJobProfileId, tjpi: $toJobProfileId",&$base);//xxxf
//---------------- cssprofile
		$query="select * from cssprofile where jobprofileid=$fromJobProfileId and prefix='$prefix'";
		$passAry=array();
		$cssAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copyCss src query: $query",&$base);//good query
//- prepare for write
		$strg=null;
		foreach ($cssAry as $ctr=>$theAry){
//--- copy over cssprofile
			$writeRowsAry=array(0=>$theAry);
			$writeRowsAry[0]['jobprofileid']=$toJobProfileId;
			$fromCssProfileId=$writeRowsAry[0]['cssprofileid'];
			unset($writeRowsAry[0]['cssprofileid']);
			$prefix=$writeRowsAry[0]['prefix'];
			$cssClass=$writeRowsAry[0]['cssclass'];
			$htmlTag=$writeRowsAry[0]['htmltag'];
			$cssId=$writeRowsAry[0]['cssid'];
//- write it
			$dbControlsAry=array('dbtablename'=>'cssprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
			$base->FileObj->writeLog('debug',"dataMoveObj.copyCss wrote $prefix(prefix), $cssClass(class), $htmlTag(html), $cssId(id) successbool: $successBool\n",&$base);
			if ($successBool){
//--- get the new cssprofileid after doing the copy
				$query="select cssprofileid from cssprofile where jobprofileid=$toJobProfileId and prefix='$prefix' and cssclass='$cssClass' and htmltag='$htmlTag' and cssid='$cssId'";
				$passAry=array();
				//$returnAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$returnAry=$base->ClientObj->queryClientDbTableRead($query,$this->toDbConn,'read',$passAry,&$base);
				$toCssProfileId=$returnAry[0]['cssprofileid'];
				$base->FileObj->writeLog('debug',"dataMoveObj.copyCss newly created tocssprofileid: $toCssProfileId",&$base);//error returns a null
				if ($toCssProfileId != null){
//--- copy over csselementprofile
				$passAry=array();
				$query="select * from csselementprofile where cssprofileid=$fromCssProfileId";
				$base->FileObj->writeLog('debug',"dataMoveObj.copyCss get css elements for cssprofileid: $fromCssProfileId query: $query",&$base);
				//$writeRowsAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
				$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
				//- prepare for write
				$theCnt=count($writeRowsAry);
				$base->FileObj->writeLog('debug',"dataMoveObject.copyCss cssselementprofile count: $theCnt",&$base);
				for ($theLp=0;$theLp<$theCnt;$theLp++){
					$writeRowsAry[$theLp]['cssprofileid']=$toCssProfileId;
					unset($writeRowsAry[$theLp]['csselementprofileid']);  
					$cssElementProperty=$writeRowsAry[$theLp]['csselementproperty'];
					$cssElementValue=$writeRowsAry[$theLp]['csselementvalue'];
					$base->FileObj->writeLog('debug',"dataMoveObj.copyCss csselement $theLp: $cssElementProperty, $cssElementValue, $toCssProfileId(cssprofileid)",&$base);
				}
//- setup and write
				$dbControlsAry=array('dbtablename'=>'csselementprofile');
				$dbControlsAry['writerowsary']=$writeRowsAry;
				$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
				if ($successBool){
					$msg= "copied the css($prefix) from jobprofileid $fromJobProfileId to $toJobProfileId";
				}
				else {
					$msg="Error!!! Could not write one of the elements for css ($prefix)";
					break;
				}
				}
				else {
					$msg="could not get cssprofileid for newly written css record";
				}
			}
			else {
				$msg="Error!!! css($prefix) probably already on file!";
				break;
			}
		}
//-
		return $msg;
	}
//===========================================
	function copyForm($passAry,$base){
		$formName=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copyform formname: $formName, fjpi: $fromJobProfileId, tjpi: $toJobProfileId",&$base);//xxxf
//---------------- formprofile
		$query="select * from formprofile where jobprofileid=$fromJobProfileId and formname='$formName'";
		$passAry=array();
		$formAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
		$formName=$formAry[0]['formname'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copyform write form: $formName",&$base);//good query
//- prepare for write
		$theCnt=count($formAry);
		if ($theCnt==1){
		$writeRowsAry=$formAry;
		$writeRowsAry[0]['jobprofileid']=$toJobProfileId;
		$fromFormProfileId=$writeRowsAry[0]['formprofileid'];
		unset($writeRowsAry[0]['formprofileid']);
//- setup and write
		$dbControlsAry=array('dbtablename'=>'formprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->setRemoteDb($this->toDbConn,&$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copyform setup toconn: $this->toDbConn into DbObj",&$base);
		$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copyForm wrote new form successbool: $successBool",&$base);// works
		if ($successBool){
			$query="select formprofileid  from formprofile where jobprofileid=$toJobProfileId and formname='$formName'";
			$passAry=array();
			$returnAry=$base->ClientObj->queryClientDbTableRead($query,$this->toDbConn,'read',$passAry,&$base);
			$toFormProfileId=$returnAry[0]['formprofileid'];
			$base->FileObj->writeLog('debug',"dataMoveObj.copyform got new toformprofileid: $toFormProfileId",&$base);//error returns a null
//-------------- formelementprofile
			$passAry=array();
			$query="select * from formelementprofile where formprofileid=$fromFormProfileId";
			$base->FileObj->writeLog('debug',"datamoveobj.copyform query: $query",&$base);
			$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
			//- prepare for write
			$theCnt=count($writeRowsAry);
			$base->FileObj->writeLog('debug',"datamoveobj.copyform formelements retrieved: $theCnt",&$base);
			$strg=null;
			for ($theLp=0;$theLp<$theCnt;$theLp++){
				$writeRowsAry[$theLp]['formprofileid']=$toFormProfileId;
				unset($writeRowsAry[$theLp]['formelementprofileid']);  
				$formElementName=$writeRowsAry[$theLp]['formelementname'];
				$strg.="formelement $theLp: $formElementName\n";
			}
			$base->FileObj->writeLog('debug',"datamoveobj.copyform elements: $strg",&$base);
//- setup and write
			$dbControlsAry=array('dbtablename'=>'formelementprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
			if ($successBool){
				$msg= "copied the form($formName) from jobprofileid $fromJobProfileId to $toJobProfileId";
			}
			else {
				$msg="Error!!! Could not write one of the elements for form ($formName)";
			}
		}
		else {
			$msg="Error!!! Form($formName) probably already on file!";
		}
		}
		else {
			$msg="Error!!! $theCnt forms where selected when only one should be!!!";
		}
//-
		return $msg;
	}
//===========================================	
	function copyMenu($passAry,$base){
		$menuName=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copyMenu menuname: $menuName, fjpi: $fromJobProfileId, tjpi: $toJobProfileId",&$base);//xxxf
//---------------- menuprofile
		$query="select * from menuprofile where jobprofileid=$fromJobProfileId and menuname='$menuName'";
		$passAry=array();
		$menuAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
		$menuName=$menuAry[0]['menuname'];
		$base->FileObj->writeLog('debug',"dataMoveObj.copyMenu write menu: $menuName",&$base);//good query
//- prepare for write
		$theCnt=count($menuAry);
		if ($theCnt == 1){
		$writeRowsAry=$menuAry;//make sure only 1 is written
		$writeRowsAry[0]['jobprofileid']=$toJobProfileId;
		$fromMenuProfileId=$writeRowsAry[0]['menuprofileid'];
		unset($writeRowsAry[0]['menuprofileid']);
//- setup and write
		$dbControlsAry=array('dbtablename'=>'menuprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->setRemoteDb($this->toDbConn,&$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copyMenu setup toconn: $this->toDbConn into DbObj",&$base);
		$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
		$base->FileObj->writeLog('debug',"dataMoveObj.copyMenu wrote new menu successbool: $successBool",&$base);// works
		if ($successBool){
			$query="select menuprofileid  from menuprofile where jobprofileid=$toJobProfileId and menuname='$menuName'";
			$passAry=array();
			$returnAry=$base->ClientObj->queryClientDbTableRead($query,$this->toDbConn,'read',$passAry,&$base);
			$toMenuProfileId=$returnAry[0]['menuprofileid'];
			$base->FileObj->writeLog('debug',"dataMoveObj.copyMenu got new tomenuprofileid: $toMenuProfileId",&$base);//error returns a null
//-------------- menuelementprofile
			$passAry=array();
			$query="select * from menuelementprofile where menuprofileid=$fromMenuProfileId";
			$base->FileObj->writeLog('debug',"datamoveobj.copyMenu query: $query",&$base);
			$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
			//- prepare for write
			$theCnt=count($writeRowsAry);
			$base->FileObj->writeLog('debug',"datamoveobj.copyMenu menuelements retrieved: $theCnt",&$base);
			$strg=null;
			for ($theLp=0;$theLp<$theCnt;$theLp++){
				$writeRowsAry[$theLp]['menuprofileid']=$toMenuProfileId;
				unset($writeRowsAry[$theLp]['menuelementprofileid']);  
				$menuElementName=$writeRowsAry[$theLp]['menuelementname'];
				$strg.="menuelement $theLp: $menuElementName\n";
			}
			$base->FileObj->writeLog('debug',"datamoveobj.copyMenu elements: $strg",&$base);
//- setup and write
			$dbControlsAry=array('dbtablename'=>'menuelementprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
			if ($successBool){
				$msg= "copied the menu($menuName) from jobprofileid $fromJobProfileId to $toJobProfileId";
			}
			else {
				$msg="Error!!! Could not write one of the elements for menu ($menuName)";
			}
		}
		else {
			$msg="Error!!! menu($menuName) probably already on file!";
		}
		}
		else {
			$msg="Error!!! $theCnt menus selected to be copied, only one should be!";
		}
//-
		return $msg;
	}
//===========================================
	function copyTable($passAry,$base){
		$tableName=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug3',"xxxf0: tablename: $tableName, fjpi: $fromJobProfileId, tjpi: $toJobProfileId",&$base);//xxxf
//---------------- tableprofile
		$query="select * from tableprofile where jobprofileid=$fromJobProfileId and tablename='$tableName'";
		$passAry=array();
		//$base->FileObj->writeLog('debug3',"xxxf0.5",&$base);
		//$tableAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$tableAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
		$base->FileObj->writeLog('debug3',"xxxf1: src query: $query",&$base);//good query
//- prepare for write
		$theCnt=count($tableAry);
		if ($theCnt == 1){
		$writeRowsAry=$tableAry;
		$writeRowsAry[0]['jobprofileid']=$toJobProfileId;
		$fromTableProfileId=$writeRowsAry[0]['tableprofileid'];
		unset($writeRowsAry[0]['tableprofileid']);
		$tableName=$writeRowsAry[0]['tablename'];
//- setup and write
		$dbControlsAry=array('dbtablename'=>'tableprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
		$base->FileObj->writeLog('debug3',"tablename: $tableName, tojobprofileid: $toJobProfileId, successbool: $successBool",&$base);// works
		if ($successBool){
			$query="select tableprofileid from tableprofile where jobprofileid=$toJobProfileId and tablename='$tableName'";
			$passAry=array();
			//$returnAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$returnAry=$base->ClientObj->queryClientDbTableRead($query,$this->toDbConn,'read',$passAry,&$base);
			//below picked up the wrong tableprofileid
			$toTableProfileId=$returnAry[0]['tableprofileid'];
			$base->FileObj->writeLog('debug3',"totableprofileid: $toTableProfileId",&$base);//error returns a null
//-------------- columnprofile
			$passAry=array();
			$query="select * from columnprofile where tableprofileid=$fromTableProfileId";
			$base->FileObj->writeLog('debug3',"get columns to move query: $query",&$base);
			//$writeRowsAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
//- prepare for write
			$theCnt=count($writeRowsAry);
			$base->FileObj->writeLog('debug3',"xxxf7 thecnt: $theCnt",&$base);
			for ($theLp=0;$theLp<$theCnt;$theLp++){
				$writeRowsAry[$theLp]['tableprofileid']=$toTableProfileId;
				unset($writeRowsAry[$theLp]['columnprofileid']);  
				$columnName=$writeRowsAry[$theLp]['columnname'];
				$base->FileObj->writeLog('debug3',"column $theLp: $columnName",&$base);
			}
//- setup and write
			$dbControlsAry=array('dbtablename'=>'columnprofile');
			$dbControlsAry['writerowsary']=$writeRowsAry;
			$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
			if ($successBool){
				$msg= "copied the table($tableName) from jobprofileid $fromJobProfileId to $toJobProfileId";
			}
			else {
				$msg="Error!!! Could not write one of the elements for table ($tableName)";
			}
		}
		else {
			$msg="Error!!! Table($tableName) probably already on file!";
		}
		}
		else {
			$msg="Error!!! $theCnt tables where selected, should only be 1!!!";
		}
//-
		return $msg;
	}
//===========================================
	function copyImage($passAry,$base){
		$base->FileObj->writeLog('debug','xxxf0: in it',&$base);
		$imageName=$passAry['objectname'];
		$fromJobProfileId=$passAry['fromjobprofileid'];
		$toJobProfileId=$passAry['tojobprofileid'];
		$base->FileObj->writeLog('debug',"imagename: $imageName, fjpi: $fromJobProfileId, tjpi: $toJobProfileId",&$base);//xxxf
//---------------- imageprofile
		$query="select * from imageprofile where jobprofileid=$fromJobProfileId and imagename='$imageName'";
		$passAry=array();
		$base->FileObj->writeLog('debug',"db: $this->fromDbConn, query: $query",&$base);
		//$imageAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
		$writeRowsAry=$base->ClientObj->queryClientDbTableRead($query,$this->fromDbConn,'read',$passAry,&$base);
//- prepare for write
		$strg=null;
		foreach ($writeRowsAry as $ctr=>$theImageAry){
			unset($writeRowsAry[$ctr]['imageprofileid']);
			$writeRowsAry[$ctr]['jobprofileid']=$toJobProfileId;
			$strg.="$ctr) set to tojobprofileid: $toJobProfileId, ";
		}
//- setup and write
		$dbControlsAry=array('dbtablename'=>'imageprofile');
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$successBool=$base->DbObj->writeToDbRemote($this->toDbConn,$dbControlsAry,$base);
	}
}
?>
