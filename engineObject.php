<?php 
class engineObject{
//-- objects
	var $initObj;
	var $htmlObj;
	var $calendarObj;
	var $dbObj;
	var $fileObj;
	var $utlObj;
	var $debugObj;
	var $tagObj;
	var $tableObj;
	var $menuObj;
	var	$formObj;
	var $pluginObj;
	var $plugin001Obj;
	var $plugin002Obj;
	var $operationPlugin001Obj;
	var $operPlugin002Obj;
	var $formElementObj;
	var $errorObj;
	var $globalDebugInt;
	var $theCart;
	var $userObj;
	var $sessionObj;
	var $containerObj;
	var $tagPlugin001Obj;
	var $clientObj;
	var $system001Obj;
	var $ajaxObj;
//-- arrays
	var $systemAry = array();
	var $paramsAry = array();
	var $jobProfileAry = array();
	var $htmlProfileAry = array();
	var $htmlElementProfileAry = array();
	var $dbTableProfileAry = array();
	var $systemProfileAry = array();
	var $tableProfileAry = array();
	var $columnProfileAry = array();	
	var $formProfileAry = array();
	var $formDataProfileAry = array();
	var $formElementProfileAry = array();
	var $rowProfileAry = array();
	var $pluginProfileAry = array();
	var $dataProfileAry = array();
	var $errorProfileAry = array();
	var $jobControlsAry = array();
	var $operationProfileAry = array();
	var $applicationProfileAry = array();
	var $currentOperationAry = array();
	var $applicationPassedAry = array();
	var $menuProfileAry = array();
	var $menuElementProfileAry = array();
	var $cssProfileAry = array();
	var $imageProfileAry = array();
	var $mapProfileAry = array();
	var $paragraphAry = array();
	var $sentenceAry = array();
	var $insertedTablesAry = array();
	var $deptAry = array();
	var $deptFunction = array();
	var $albumProfileAry = array();
//-- strings
	var $engineStatusSt;
	var $runType;
//=================================================================
	function engineObject($runType='html') {
		$this->runType=$runType;
		$this->engineStatus='engineObj is fired up and ready for work';
//- start base objects
		$this->errorObj = new errorObject();
		$this->debugObj = new debugObject();
		$this->clientObj = new clientObject(&$this);
		$this->initObj = new initObject();
		$this->fileObj = new fileObject();
		$this->dbObj = new dbObject(&$this);
		$this->utlObj = new utilObject();
		$this->pluginObj = new pluginObject();
		$this->xmlObj = new xmlObject();
//- start generic plugins
		$this->plugin001Obj = new plugin001Object();
		$this->plugin002Obj = new plugin002Object(&$this);
		$this->operationPlugin001Obj = new operationPlugin001Object(&$this);
		$this->operPlugin002Obj = new operPlugin002Object(&$this);
//- start user object if not there
		session_start();
		//- check user dbname to this dbname and recall if different
		if (!isset($_SESSION['userobj'])){
			$userObj = new userObject($this);
			$_SESSION['userobj']=$userObj;
		}
		else {
			$curDir=getcwd();
			$oldCurDir=$_SESSION['userobj']->getCurDir(&$this);
			//echo "oldcurdir: $oldCurDir, curdir: $curDir<br>";//
			if ($oldCurDir != $curDir){
				//echo "redo it";//xxx
				$userObj = new userObject($this);
				$_SESSION['userobj']=$userObj;
			}
		}
//- start session obect if not there
		if (!isset($_SESSION['sessionobj'])){
			$sessionObj = new sessionObject();
			$_SESSION['sessionobj']=$sessionObj;
		}
//- start html objects
		if ($runType == 'html'){
			//echo "xxf on line 109<br>";//xxxf
			$this->paramsAry = $this->utlObj->getParams();
			//xxxf
			//foreach ($this->paramsAry as $one=>$two){echo "$one: $two<br>";}
			foreach($this->paramsAry as $one=>$two){$dmyStrg.="$one: $two, ";}
			$this->fileObj->writeLog('jefftestxxx',$dmyStrg,&$this);
			$this->htmlObj = new htmlObject($this);
			$this->tagObj = new tagObject();
			$this->tableObj = new tableObject();
			$this->menuObj = new menuObject();
			$this->formObj = new formObject();
			$this->calendarObj = new calendarObject();
			$this->containerObj = new containerObject();
//- html plugin objects
			$this->tagPlugin001Obj = new tagPlugin001Object(&$this);
			//- below needs calendarObject 
			$this->ajaxObj = new ajaxObject(&$this);
			//$this->cartObj = $this->initObj->initCart($this);
		}
	}
//=========================================================
	function engage($fedJob='default'){
// - set basic who what where when stuff
		//echo "10";exit();//
		if ($this->runType == 'html'){
			$this->systemAry=$this->clientObj->getSystemData($this);
		}
		else {
			//- need to get hour adjust
			$this->systemAry=$this->clientObj->getSystemData($this);
			$this->systemAry['type']='appl';
		}
		//$strg=shell_exec('ps');
		$useJob=$this->paramsAry['job'];
		$this->fileObj->writeLog("init","engineObj.engage fedjob: $fedJob, job: $useJob, strg: $strg",&$this);
		if ($fedJob=='default'){$fedJob=$this->systemAry['fedjob'];}
// - special objects 
		$domainName=$this->systemAry['domainname'];
		$this->system001Obj = new system001Object();
// - load in all arrays from databases
		$this->getInitControls($fedJob);
// - security
		$okToContinue=true;
// - loop through operations
		if ($okToContinue){
			$this->getDbStuff();
//--- init debugging
			$dontErase=$this->paramsAry['donterase'];
			if (!($dontErase != null)){
				$this->utlObj->nullSessionFile('debug',&$this);
				$theJob=$this->paramsAry['job'];
				$this->utlObj->appendValue('debug',"erase logging at start of job: $theJob<br>",&$this);
			}
			else {unset ($this->paramsAry['donterase']);}
//--- see if from ajax and update with sendData['paramnames']/['paramvalues']
			$this->fileObj->writeLog('debug',"engineObj-> engage copying senddata and session into paramsary",&$this);
			if (array_key_exists('senddata',$this->paramsAry)){
				$this->ajaxObj->copyInParams(&$this);
			}
			$strg="job: $useJob---paramsary in engineobj just after senddata copied in---\n";
			foreach ($this->paramsAry as $name=>$value){
				//if ($name=='senddata'){$value="...";}
				$strg.="$name: $value\n";
			}
			$this->fileObj->writeLog('jefftest',$strg,&$this);
//--- copy in session stuff
			$this->fileObj->writeLog('debug',"copying in sessionname stuff",&$this);
			if (array_key_exists('sessionname',$this->paramsAry)){
				$this->utlObj->copyInSession(&$this);//
				//echo "copied in session:";
			}
//--- if mainline then get all databases not just current one
			if ($this->system001Obj != null){
				//echo "setupdbparams<br>";//
				$this->system001Obj->setupDbParams(&$this);
			}
			$this->dbObj->displayStatus("engObj_beforeopers",&$this);//xxxf
//--- loop through operations 
			$noOperations=count($this->operationProfileAry);
			if ($noOperations==0){
				$this->operationProfileAry[]=array('operationname'=>'runcgi');
				$this->operationProfileAry[]=array('operationname'=>'processhtml');
				$noOperations=2;
			}
			for ($operCtr=0;$operCtr<$noOperations;$operCtr++){
				$this->currentOperationAry=$this->operationProfileAry[$operCtr];
				$operAry=$this->currentOperationAry;
				//$this->debugObj->printDebug($operAry,1,'xxx');
				$operationName=$operAry['operationname'];
				$this->fileObj->writeLog('writedbfromajaxsimple','engineobj) jobname: '. $useJob.', operno: '.$operCtr.', opername: '.$operationName,&$this);//xxxf
				//echo "jobname: $job, operation name: $operationName";//xxxf
				
				switch ($operationName){
//--- run operation defined in cgi
					case 'runcgi':
						//xxxf
						$operStrg='';
						foreach ($this->paramsAry as $one=>$two){
							if ($one != 'senddata'){
								$operStrg.="$one: $two\n";
							}
						}
						$this->fileObj->writeLog('debug','engineObj runcgi: '.$operStrg,&$this);//xxxf
						$this->pluginObj->runOperationPlugin($operAry,&$this);
						break;
//process the html
					case 'processhtml':
					//echo 'do html stuff<br>';//xxxd
						$this->processHtml();
						break;
//run perl app
					case 'runperl':
						echo "runperl\n";
						break;
//run php app
					case 'runphpapp':
					//xxxr - this should be sorted !!!
					//$base->debugObj->printDebug($this->applicationProfileAry,1,'xxxf');
						foreach ($this->applicationProfileAry as $appName=>$appAry){
							$appName=$appAry['applicationpluginname'];
							$operAry['applicationpluginname']=$appName;
							$this->applicationPassedAry=$this->pluginObj->runAppPlugin($operAry,$this->applicationPassedAry,&$this);
						}
						break;
//do nothing
					case 'do nothing':
						break;
					default:
						$this->debugObj->placeCheck("invalid operation name: '$operationName', no: '$operCtr'"); //xx (c)
				} // end switch
			} // end for loop
		} // end ok to continue
	}
//=======================================
	function deprecatedinit($fedJobSt=""){
			$this->getInitControls($fedJobSt);
			$this->getDbStuff();
			if ($this->runType == 'html'){
				$this->dataProfileAry = $this->dbObj->getDataForForm(&$this);
			}
	}
//======================================
	function deprecatedUpdateData(){
		$operation=$this->paramsAry['operation'];
		if ($operation != ""){
			$operationStr=$this->jobProfileAry['operationstr'];
			if ($operationStr != ""){
				$this->pluginObj->runOperationPlugin($operationStr,&$this);
			}
		}
	}
//=======================================
	function displayHtmlInsert($pluginName,$param_1="",$param_2="",$param_3="",$param_4="",$param_5=""){
		$this->debugObj->printDebug("engObj:displayHtmlInsert($pluginName,$param_1,$param_2,$param_3,$param_4,$param_5)",0);
		$paramFeed=array();
		$pluginName=strtolower($pluginName);
		$paramFeed['param_1']=$param_1;
		$paramFeed['param_2']=$param_2;
		$paramFeed['param_3']=$param_3;
		$paramFeed['param_4']=$param_4;
		$paramFeed['param_5']=$param_5;
		if ($pluginName != ""){
			$returnAry=$this->pluginObj->runTagPlugin($pluginName,$paramFeed,&$this);
		}
		$noLines=count($returnAry);
		for ($ctr=0;$ctr<$noLines;$ctr++){echo $returnAry[$ctr];}
	}
//===================================
	function getInitControls($fedJob){
		if ($fedJob == null){$fedJob='main';}
		if (!array_key_exists('job',$this->paramsAry)){$this->paramsAry['job']=$fedJob;}
		//$this->debugObj->printDebug($this->paramsAry,1);//
//- systemprofile
		$this->systemProfileAry = $this->dbObj->getSimpleProfile('systemprofile',&$this);
		//$this->debugObj->printDebug($this->systemProfileAry,1,'systemProfileAry');
//- jobprofile xxx
		$this->jobProfileAry=$this->initObj->getJobProfile(&$this);
		//$this->debugObj->printDebug($this->jobProfileAry,1,'jobProfileAry');
//- containerProfile
		if ($this->runType == 'html'){
			$this->containerObj->initContainer(&$this);
		}
//- operationprofile - xxx
		$this->operationProfileAry=$this->initObj->getOperationProfile(&$this);
		//$this->debugObj->printDebug($this->operationProfileAry,1,'operationProfileAry');
//- applicationprofile
		$this->applicationProfileAry=$this->dbObj->getComplexProfile('applicationprofileview','jobname','applicationpluginname','',&$this);
		//$this->debugObj->printDebug($this->applicationProfileAry,1,'applicationprofileary');//xxx
//- menuprofile, menuelementprofile, submenuelementprofile
		$workAry=$this->initObj->getMenus($this);
		$this->menuProfileAry=$workAry['menuprofileary'];
		//$this->debugObj->printDebug($this->menuProfileAry,1,'menuprofileary');//xxxf
		$this->menuElementProfileAry=$workAry['menuelementprofileary'];
		//$this->debugObj->printDebug($this->menuElementProfileAry,1,'menuelementprofileary');
//- cssprofile
		$this->cssProfileAry=$this->initObj->getCssProfile($this);
		//$this->debugObj->printDebug($this->cssProfileAry,1,'cssprofileary');
//- imageprofile
		$this->imageProfileAry=$this->initObj->getImageProfile($this);
		//$this->debugObj->printDebug($this->imageProfileAry,1,'imageprofileary');
//- mapprofile, mapelementprofile
		$this->mapProfileAry=$this->initObj->getMapProfile($this);
		//$this->debugObj->printDebug($this->mapProfileAry,1,'mapprofileary');
//- deptprofile, deptfunctionprofile
		$this->deptProfileAry=$this->initObj->getDeptProfile($this);
		//$this->debugObj->printDebug($this->mapProfileAry,1,'mapprofileary');
//- calendarProfile
		if ($this->runType == 'html'){
			$this->calendarObj->initCalendar(&$this);
		}
	}
//================================================
//=============== Build rest of system arrays=====
//================================================
	function getDbStuff(){
//- errorProfile 
		$this->errorProfileAry['othererrorary']=array();
		$this->errorProfileAry['columnerrorary']=array();
//- htmlprofile xxx
		$this->htmlProfileAry=$this->initObj->getHtmlProfile(&$this);
//- update of htmlprofile also updates operationprofile-so may want to see
//	both here
		//$this->debugObj->printDebug($this->htmlProfileAry,1,'htmlProfileAry');
		//$this->debugObj->printDebug($this->operationProfileAry,1,'operationProfileAry');
//- htmlelementprofile xxx
		$this->htmlElementProfileAry=$this->initObj->getHtmlElementProfile(&$this);
		//$this->debugObj->printDebug($this->htmlElementProfileAry,1,'htmlElementProfileAry');
//- dbtableprofile xxx
		//$this->dbTableProfileAry=$this->initObj->getDbTableProfile(&$this);
		//$this->debugObj->printDebug($this->dbTableProfileAry,1,'dbTableProfileAry');
//- tableprofile  xxx
		$this->tableProfileAry=$this->initObj->getTableProfile(&$this);
		//$this->debugObj->printDebug($this->tableProfileAry,1,'tableProfileAry');
//- columnprofile  xxx
		$this->columnProfileAry=$this->initObj->getColumnProfile(&$this);
		//$this->debugObj->printDebug($this->columnProfileAry,1,'columnProfileAry');
//- formprofile xxx
		$this->formProfileAry=$this->initObj->getFormProfile(&$this);
		//$this->debugObj->printDebug($this->formProfileAry,1,'formProfileAry');
//- formdataprofile xxx
		$this->formDataProfileAry=$this->initObj->getFormDataProfile(&$this);
		//$this->debugObj->printDebug($this->formDataProfileAry,1,'formProfileAry');
//- formelementprofile xxx
		$this->formElementProfileAry=$this->initObj->getFormElementProfile(&$this);
		//$this->debugObj->printDebug($this->formElementProfileAry,1,'formElementProfileAry');
//- rowprofile xxx
		$this->rowProfileAry=$this->initObj->getRowProfile(&$this);
		//$this->debugObj->printDebug($this->rowProfileAry,1,'rowProfileAry');
//- pluginprofile xxx
		$this->pluginProfileAry=$this->initObj->getPluginProfile(&$this);
		//$this->debugObj->printDebug($this->pluginProfileAry,1,'pluginProfileAry');
//- paragraphprofile xxx
		$this->paragraphProfileAry=$this->initObj->getParagraphProfile(&$this);
		//$this->debugObj->printDebug($this->paragraphProfileAry,1,'paragraphProfileAry');
//- sentenceprofile xxx
		$this->sentenceProfileAry=$this->initObj->getSentenceProfile(&$this);
		//$this->debugObj->printDebug($this->sentenceProfileAry,1,'sentenceProfileAry');
		//$this->debugObj->printDebug($this->paragraphProfileAry,1,'paragraphProfileAry');
//- albumprofile
		$this->albumProfileAry=$this->initObj->getAlbumProfile(&$this);
		//$this->debugObj->printDebug($this->albumProfileAry,1,'albumProfileAry');
		//exit();
//- check if debug flag
		$tst=$this->paramsAry['d'];
		if ($tst == 'y'){
			$this->debugObj->setPrio(-1,-1);
		} //xx (s)
	}
//===============================================
//============process html=======================
//===============================================
	function processHtml(){
		$this->htmlObj->processHtmlFile(&$this);
	}
//---
	function status(){
		echo "<br>$this->engineStatus!\n";
		$this->fileObj->status();
		$this->dbObj->status();
		$this->htmlObj->status();
		$this->utlObj->status();
	}
}
?>
