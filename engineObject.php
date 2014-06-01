<?php 
class engineObject{
//-- objects
	var $InitObj;
	var $HtmlObj;
	var $CalendarObj;
	var $DbObj;
	var $FileObj;
	var $UtlObj;
	var $DebugObj;
	var $TagObj;
	var $TableObj;
	var $MenuObj;
	var	$FormObj;
	var $PluginObj;
	var $Plugin001Obj;
	var $Plugin002Obj;
	var $operationPlugin001Obj;
	var $OperPlugin002Obj;
	var $FormElementObj;
	var $ErrorObj;
	var $globalDebugInt;
	var $theCart;
	var $UserObj;
	var $SessionObj;
	var $ContainerObj;
	var $TagPlugin001Obj;
	var $ClientObj;
	var $System001Obj;
	var $AjaxObj;
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
		$this->ErrorObj = new ErrorObject();
		$this->DebugObj = new DebugObject();
		$this->ClientObj = new ClientObject(&$this);
		$this->InitObj = new InitObject();
		$this->FileObj = new FileObject();
		$this->DbObj = new DbObject(&$this);
		$this->UtlObj = new UtilObject();
		$this->PluginObj = new PluginObject();
		$this->XmlObj = new XmlObject();
//- start generic plugins
		$this->Plugin001Obj = new Plugin001Object();
		$this->Plugin002Obj = new Plugin002Object(&$this);
		$this->operationPlugin001Obj = new operationPlugin001Object(&$this);
		$this->OperPlugin002Obj = new OperPlugin002Object(&$this);
//- start user object if not there
		session_start();
		//- check user dbname to this dbname and recall if different
		if (!isset($_SESSION['userobj'])){
			$UserObj = new UserObject($this);
			$_SESSION['userobj']=$UserObj;
		}
		else {
			$curDir=getcwd();
			$oldCurDir=$_SESSION['userobj']->getCurDir(&$this);
			//echo "oldcurdir: $oldCurDir, curdir: $curDir<br>";//
			if ($oldCurDir != $curDir){
				//echo "redo it";//xxx
				$UserObj = new UserObject($this);
				$_SESSION['userobj']=$UserObj;
			}
		}
//- start session obect if not there
		if (!isset($_SESSION['sessionobj'])){
			$SessionObj = new SessionObject();
			$_SESSION['sessionobj']=$SessionObj;
		}
//- start html objects
		if ($runType == 'html'){
			//echo "xxf on line 109<br>";//xxxf
			$this->paramsAry = $this->UtlObj->getParams();
			//xxxf
			//foreach ($this->paramsAry as $one=>$two){echo "$one: $two<br>";}
			foreach($this->paramsAry as $one=>$two){$dmyStrg.="$one: $two, ";}
			$this->FileObj->writeLog('jefftestxxx',$dmyStrg,&$this);
			$this->HtmlObj = new HtmlObject($this);
			$this->TagObj = new TagObject();
			$this->TableObj = new TableObject();
			$this->MenuObj = new MenuObject();
			$this->FormObj = new FormObject();
			$this->CalendarObj = new CalendarObject();
			$this->ContainerObj = new ContainerObject();
//- html plugin objects
			$this->TagPlugin001Obj = new TagPlugin001Object(&$this);
			//- below needs CalendarObject 
			$this->AjaxObj = new AjaxObject(&$this);
			//$this->cartObj = $this->InitObj->initCart($this);
		}
	}
//=========================================================
	function engage($fedJob='default'){
// - set basic who what where when stuff
		//echo "10";exit();//
		if ($this->runType == 'html'){
			$this->systemAry=$this->ClientObj->getSystemData($this);
		}
		else {
			//- need to get hour adjust
			$this->systemAry=$this->ClientObj->getSystemData($this);
			$this->systemAry['type']='appl';
		}
		//$strg=shell_exec('ps');
		$useJob=$this->paramsAry['job'];
		$this->FileObj->writeLog("init","engineObj.engage fedjob: $fedJob, job: $useJob, strg: $strg",&$this);
		if ($fedJob=='default'){$fedJob=$this->systemAry['fedjob'];}
// - special objects 
		$domainName=$this->systemAry['domainname'];
		$this->System001Obj = new System001Object();
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
				$this->UtlObj->nullSessionFile('debug',&$this);
				$theJob=$this->paramsAry['job'];
				$this->UtlObj->appendValue('debug',"erase logging at start of job: $theJob<br>",&$this);
			}
			else {unset ($this->paramsAry['donterase']);}
//--- see if from ajax and update with sendData['paramnames']/['paramvalues']
			$this->FileObj->writeLog('debug',"engineObj-> engage copying senddata and session into paramsary",&$this);
			if (array_key_exists('senddata',$this->paramsAry)){
				$this->AjaxObj->copyInParams(&$this);
			}
			$strg="job: $useJob---paramsary in engineobj just after senddata copied in---\n";
			foreach ($this->paramsAry as $name=>$value){
				//if ($name=='senddata'){$value="...";}
				$strg.="$name: $value\n";
			}
			$this->FileObj->writeLog('jefftest',$strg,&$this);
//--- copy in session stuff
			$this->FileObj->writeLog('debug',"copying in sessionname stuff",&$this);
			if (array_key_exists('sessionname',$this->paramsAry)){
				$this->UtlObj->copyInSession(&$this);//
				//echo "copied in session:";
			}
//--- if mainline then get all databases not just current one
			if ($this->System001Obj != null){
				//echo "setupdbparams<br>";//
				$this->System001Obj->setupDbParams(&$this);
			}
			$this->DbObj->displayStatus("engObj_beforeopers",&$this);//xxxf
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
				//$this->DebugObj->printDebug($operAry,1,'xxx');
				$operationName=$operAry['operationname'];
				$this->FileObj->writeLog('writedbfromajaxsimple','engineobj) jobname: '. $useJob.', operno: '.$operCtr.', opername: '.$operationName,&$this);//xxxf
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
						$this->FileObj->writeLog('debug','engineObj runcgi: '.$operStrg,&$this);//xxxf
						$this->PluginObj->runOperationPlugin($operAry,&$this);
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
					//$base->DebugObj->printDebug($this->applicationProfileAry,1,'xxxf');
						foreach ($this->applicationProfileAry as $appName=>$appAry){
							$appName=$appAry['applicationpluginname'];
							$operAry['applicationpluginname']=$appName;
							$this->applicationPassedAry=$this->PluginObj->runAppPlugin($operAry,$this->applicationPassedAry,&$this);
						}
						break;
//do nothing
					case 'do nothing':
						break;
					default:
						$this->DebugObj->placeCheck("invalid operation name: '$operationName', no: '$operCtr'"); //xx (c)
				} // end switch
			} // end for loop
		} // end ok to continue
	}
//=======================================
	function deprecatedinit($fedJobSt=""){
			$this->getInitControls($fedJobSt);
			$this->getDbStuff();
			if ($this->runType == 'html'){
				$this->dataProfileAry = $this->DbObj->getDataForForm(&$this);
			}
	}
//======================================
	function deprecatedUpdateData(){
		$operation=$this->paramsAry['operation'];
		if ($operation != ""){
			$operationStr=$this->jobProfileAry['operationstr'];
			if ($operationStr != ""){
				$this->PluginObj->runOperationPlugin($operationStr,&$this);
			}
		}
	}
//=======================================
	function displayHtmlInsert($pluginName,$param_1="",$param_2="",$param_3="",$param_4="",$param_5=""){
		$this->DebugObj->printDebug("engObj:displayHtmlInsert($pluginName,$param_1,$param_2,$param_3,$param_4,$param_5)",0);
		$paramFeed=array();
		$pluginName=strtolower($pluginName);
		$paramFeed['param_1']=$param_1;
		$paramFeed['param_2']=$param_2;
		$paramFeed['param_3']=$param_3;
		$paramFeed['param_4']=$param_4;
		$paramFeed['param_5']=$param_5;
		if ($pluginName != ""){
			$returnAry=$this->PluginObj->runTagPlugin($pluginName,$paramFeed,&$this);
		}
		$noLines=count($returnAry);
		for ($ctr=0;$ctr<$noLines;$ctr++){echo $returnAry[$ctr];}
	}
//===================================
	function getInitControls($fedJob){
		if ($fedJob == null){$fedJob='main';}
		if (!array_key_exists('job',$this->paramsAry)){$this->paramsAry['job']=$fedJob;}
		//$this->DebugObj->printDebug($this->paramsAry,1);//
//- systemprofile
		$this->systemProfileAry = $this->DbObj->getSimpleProfile('systemprofile',&$this);
		//$this->DebugObj->printDebug($this->systemProfileAry,1,'systemProfileAry');
//- jobprofile xxx
		$this->jobProfileAry=$this->InitObj->getJobProfile(&$this);
		//$this->DebugObj->printDebug($this->jobProfileAry,1,'jobProfileAry');
//- containerProfile
		if ($this->runType == 'html'){
			$this->ContainerObj->initContainer(&$this);
		}
//- operationprofile - xxx
		$this->operationProfileAry=$this->InitObj->getOperationProfile(&$this);
		//$this->DebugObj->printDebug($this->operationProfileAry,1,'operationProfileAry');
//- applicationprofile
		$this->applicationProfileAry=$this->DbObj->getComplexProfile('applicationprofileview','jobname','applicationpluginname','',&$this);
		//$this->DebugObj->printDebug($this->applicationProfileAry,1,'applicationprofileary');//xxx
//- menuprofile, menuelementprofile, submenuelementprofile
		$workAry=$this->InitObj->getMenus($this);
		$this->menuProfileAry=$workAry['menuprofileary'];
		//$this->DebugObj->printDebug($this->menuProfileAry,1,'menuprofileary');//xxxf
		$this->menuElementProfileAry=$workAry['menuelementprofileary'];
		//$this->DebugObj->printDebug($this->menuElementProfileAry,1,'menuelementprofileary');
//- cssprofile
		$this->cssProfileAry=$this->InitObj->getCssProfile($this);
		//$this->DebugObj->printDebug($this->cssProfileAry,1,'cssprofileary');
//- imageprofile
		$this->imageProfileAry=$this->InitObj->getImageProfile($this);
		//$this->DebugObj->printDebug($this->imageProfileAry,1,'imageprofileary');
//- mapprofile, mapelementprofile
		$this->mapProfileAry=$this->InitObj->getMapProfile($this);
		//$this->DebugObj->printDebug($this->mapProfileAry,1,'mapprofileary');
//- deptprofile, deptfunctionprofile
		$this->deptProfileAry=$this->InitObj->getDeptProfile($this);
		//$this->DebugObj->printDebug($this->mapProfileAry,1,'mapprofileary');
//- calendarProfile
		if ($this->runType == 'html'){
			$this->CalendarObj->initCalendar(&$this);
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
		$this->htmlProfileAry=$this->InitObj->getHtmlProfile(&$this);
//- update of htmlprofile also updates operationprofile-so may want to see
//	both here
		//$this->DebugObj->printDebug($this->htmlProfileAry,1,'htmlProfileAry');
		//$this->DebugObj->printDebug($this->operationProfileAry,1,'operationProfileAry');
//- htmlelementprofile xxx
		$this->htmlElementProfileAry=$this->InitObj->getHtmlElementProfile(&$this);
		//$this->DebugObj->printDebug($this->htmlElementProfileAry,1,'htmlElementProfileAry');
//- dbtableprofile xxx
		//$this->dbTableProfileAry=$this->InitObj->getDbTableProfile(&$this);
		//$this->DebugObj->printDebug($this->dbTableProfileAry,1,'dbTableProfileAry');
//- tableprofile  xxx
		$this->tableProfileAry=$this->InitObj->getTableProfile(&$this);
		//$this->DebugObj->printDebug($this->tableProfileAry,1,'tableProfileAry');
//- columnprofile  xxx
		$this->columnProfileAry=$this->InitObj->getColumnProfile(&$this);
		//$this->DebugObj->printDebug($this->columnProfileAry,1,'columnProfileAry');
//- formprofile xxx
		$this->formProfileAry=$this->InitObj->getFormProfile(&$this);
		//$this->DebugObj->printDebug($this->formProfileAry,1,'formProfileAry');
//- formdataprofile xxx
		$this->formDataProfileAry=$this->InitObj->getFormDataProfile(&$this);
		//$this->DebugObj->printDebug($this->formDataProfileAry,1,'formProfileAry');
//- formelementprofile xxx
		$this->formElementProfileAry=$this->InitObj->getFormElementProfile(&$this);
		//$this->DebugObj->printDebug($this->formElementProfileAry,1,'formElementProfileAry');
//- rowprofile xxx
		$this->rowProfileAry=$this->InitObj->getRowProfile(&$this);
		//$this->DebugObj->printDebug($this->rowProfileAry,1,'rowProfileAry');
//- pluginprofile xxx
		$this->pluginProfileAry=$this->InitObj->getPluginProfile(&$this);
		//$this->DebugObj->printDebug($this->pluginProfileAry,1,'pluginProfileAry');
//- paragraphprofile xxx
		$this->paragraphProfileAry=$this->InitObj->getParagraphProfile(&$this);
		//$this->DebugObj->printDebug($this->paragraphProfileAry,1,'paragraphProfileAry');
//- sentenceprofile xxx
		$this->sentenceProfileAry=$this->InitObj->getSentenceProfile(&$this);
		//$this->DebugObj->printDebug($this->sentenceProfileAry,1,'sentenceProfileAry');
		//$this->DebugObj->printDebug($this->paragraphProfileAry,1,'paragraphProfileAry');
//- albumprofile
		$this->albumProfileAry=$this->InitObj->getAlbumProfile(&$this);
		//$this->DebugObj->printDebug($this->albumProfileAry,1,'albumProfileAry');
		//exit();
//- check if debug flag
		$tst=$this->paramsAry['d'];
		if ($tst == 'y'){
			$this->DebugObj->setPrio(-1,-1);
		} //xx (s)
	}
//===============================================
//============process html=======================
//===============================================
	function processHtml(){
		$this->HtmlObj->processHtmlFile(&$this);
	}
//---
	function status(){
		echo "<br>$this->engineStatus!\n";
		$this->FileObj->status();
		$this->DbObj->status();
		$this->HtmlObj->status();
		$this->UtlObj->status();
	}
}
?>
