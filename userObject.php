<?php
class UserObject{
	var $userAry = array();
	var $deptAry = array();
	//=====================================
	function UserObject($base){
//- below is deprecated old method
		$this->userAry['username']='guest';
		$this->userAry['userfirstname']='guest';
//- new userobject setup
		$this->initUserFields(&$base);
	}
//=========================================
	function displayUserSetups($base){
		$base->DebugObj->printDebug($this->userAry,1,'userary');	
	}
//=========================================
	function isAccessAll($base){
		$returnBoolean=$base->UtlObj->returnBoolean($this->userAry['profile']['accessallcompanies'],&$base);
		return $returnBoolean;
	}
//=========================================
	function initUserFields($base){
		$userName='guest';
//- get user stuff
		$query="select * from userprofileview where username='$userName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);	
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$this->userAry['profile']=$workAry;
		$curDir=getcwd();
		$this->userAry['curdir']=$curDir;
		//echo "xxxf8";
//- get xref stuff
		$query="select * from usercompanyxrefview where username='guest' or username='guest' and companyallowsaccesstoall=true";
		$result=$base->DbObj->queryTable($query,'read',&$base);	
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		//$base->DebugObj->printDebug($workAry,1,'xxxf');
		//exit('xxxf');	
		$this->userAry['profile']=array();
		$this->userAry['profile']['username']='guest';
		$this->userAry['profile']['userfirstname']='guest';
		//- need to put in domainname here
		$this->userAry['companyaccess']=array();
		$this->userAry['companyselect']=array();
		$gotAll=false;
		foreach ($workAry as $ctr=>$thisWorkAry){
			$userCompanyAccess=$base->UtlObj->returnBoolean($thisWorkAry['usercompanyaccess'],&$base);
			$userCompanySelect=$base->UtlObj->returnBoolean($thisWorkAry['usercompanyselect'],&$base);	
			$userCompanySelectOrder=$thisWorkAry['usercompanyselectorder'];
			$companyName=$thisWorkAry['companyname'];
			if ($companyName=='All'){$gotAll=true;}
			$companyProfileId=$thisWorkAry['companyprofileid'];
			if ($userCompanyAccess){$this->userAry['companyaccess'][$companyProfileId]=array('companyname'=>$companyName);}
			if ($userCompanySelect){
				if ($userCompanySelectOrder>0 && $userCompanySelectOrder<99){
					//xxxf22 - what if they both have the same select order!!!!
					$this->userAry['companyselect'][$userCompanySelectOrder]=$companyProfileId;	
				}	
			}	
		}
		if (!$gotAll){
			echo "need All company setup to go to guest!!!<br>";
			$query="select companyname,companyprofileid from companyprofile where companyname='All'";
			$result=$base->DbObj->queryTable($query,'read',&$base);	
			$passAry=array('delimit1'=>'companyname');
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
			//$base->DebugObj->printDebug($workAry,1,'xxxf');
			$companyProfileId=$workAry['All']['companyprofileid'];
			$this->userAry['companyaccess'][$companyProfileId]=array('companyname'=>'All');
			$this->userAry['companyselect'][]=$companyProfileId;			
		}
	}
//=====================================
	function getCurDir($base){
		$curDir=$this->userAry['curdir'];
		if ($curDir == null){$curDir='none';}
		return $curDir;
	}
//=====================================
	function getCompanySelects($base){
		$returnAry=$this->userAry['companyselect'];	
		$theLen=count($returnAry);
		if ($theLen<1){
			$this->initUserFields(&$base);
			$returnAry=$this->userAry['companyselect'];	
		}
		return $returnAry;
	}
//=====================================
	function setUserFields($userName,$userPassword,$base){
//- get user stuff
		$query="select * from userprofileview where username='$userName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);	
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
		$chkPassword=$workAry[0]['userpassword'];
		//echo "username: $userName, userpassword: $userPassword";//xxxf
		if ($chkPassword == $userPassword){
			unset($this->userAry['profile']);
			$this->userAry['profile']=$workAry[0];
			$accessAllCompanies=$base->UtlObj->returnboolean($workAry[0]['accessallcompanies'],&$base);
			//- below is old legacy which should be deprecated
			$this->userAry['username']=$this->userAry['profile']['username'];
			$this->userAry['userfirstname']=$this->userAry['profile']['userfirstname'];
//- get user xref stuff		
			if ($userName == 'admin'){$query="select * from usercompanyxrefview";}
			else {$query="select * from usercompanyxrefview where username='$userName' or where companyallowsaccesstoall=true";}
			$result=$base->DbObj->queryTable($query,'read',&$base);	
			$passAry=array();
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);
			$this->userAry['companyaccess']=array();
			$this->userAry['companyselect']=array();
			foreach ($workAry as $ctr=>$thisWorkAry){
				$userCompanyAccess=$base->UtlObj->returnBoolean($thisWorkAry['usercompanyaccess'],&$base);
				$userCompanySelect=$base->UtlObj->returnBoolean($thisWorkAry['usercompanyselect'],&$base);	
				$userCompanySelectOrder=$thisWorkAry['usercompanyselectorder'];
				$companyName=$thisWorkAry['companyname'];
				$companyProfileId=$thisWorkAry['companyprofileid'];
				if ($userCompanyAccess || $accessAllCompanies){$this->userAry['companyaccess'][$companyProfileId]=array('companyname'=>$companyName);}
				if ($userCompanySelect){
					if ($userCompanySelectOrder>=0 && $userCompanySelectOrder<99){
						$this->userAry['companyselect'][$userCompanySelectOrder]=$companyProfileId;	
					}	
				} // end if usercompanyselect	
			} // end foreach workary
//- if access all then fill in rest of companies
		} // end if pwd=chkpwd
		//$base->DebugObj->printDebug($workAry,1,'xxx');
		//$base->DebugObj->printDebug($this->userAry,1,'xxx');
		//exit('xxx');
	}	
//=====================================
	function getCurrentUserAry(){
		$currentUserAry= $this->userAry;
		//echo "retrieved username: $currentUserAry['userName']<br>";//xxx	
		return $currentUserAry;
	}	
//=====================================
	function updateCurrentUserAry($newUserAry){
		//echo "newname: $newName<br>";//xxx
		$this->userAry=$newUserAry;
	}
//=====================================
	function updateCurrentDeptAry($newDeptAry){
		$this->deptAry=$newDeptAry;	
	}
//=====================================
	function getCurrentDeptAry(){
		$deptAry=$this->deptAry;
		return $deptAry;	
	}
}
?>
