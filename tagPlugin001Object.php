<?php
class tagPlugin001Object {
//04/28/13 added ajaxObj
	var $statusMsg;
	var $callNo = 0;
	var $base;
//====================================================
	function tagPlugin001Object($base) {
		$this->incCalls();
		$this->statusMsg='tag Plugin 001 Object is fired up and ready for work!';
		$this->base=$base;
	}
//====================================================
	function insertContainer($paramFeed,$base){
		$containerName=$paramFeed['param_1'];
		$returnAry=$base->containerObj->insertContainerHtml($containerName,&$base);
		return $returnAry;
	}
//====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//====================================================
	function buildJsAlbums($paramFeed,$base){
		foreach ($base->albumProfileAry['main'] as $albumProfileId=>$albumProfileAry){
			$passAry=$base->htmlObj->buildAlbumTable($albumProfileId,$base);
			$albumTableDisplayAry=$passAry['returnary'];
			$albumName=$passAry['albumname'];
			if (!array_key_exists('jsary',$base->albumProfileAry)){$base->albumProfileAry['jsary']=array();}
			$base->albumProfileAry['jsary'][$albumName]=$passAry[$albumName];
		}
	}
//====================================================deprecated
	function getTableForAjax($paramFeed,$base){
		echo "deprecated";
		$ajaxAry=array();
		$tableName=$paramFeed['param_1'];
		$ajaxAry[]="!!table!!\n";
		$ajaxAry[]="tablename|$tableName\n";
		//-get list of column names in dbtable
		$dbTableName=$base->tableProfileAry[$tableName]['tablename'];
		$ajaxAry[]="dbtablename|$dbTableName\n";
		$ajaxAry[]="pageno|1\n";
		$jsTableAry=$base->tableProfileAry['jstableary'];
		$jsTableSelectAry=$base->tableProfileAry['jstableselectary'];
		$columnCnt=count($jsTableAry[0]);
		$ajaxAry[]="columncnt|$columnCnt\n";
		$pageSize=$base->tableProfileAry[$tableName]['pagesize'];
		$ajaxAry[]="pagesize|$pageSize\n";
		$tableId = $base->tableProfileAry[$tableName]['tableid'];
		//echo "tablename: $tableName, tableid: $tableId<br>";//xxxf
		$ajaxAry[]="tableid|$tableId\n";
		$maxDataAry=count($jsTableAry);
		$ajaxAry[]="maxdataary|$maxDataAry\n";
		$cnt=count($jsTableAry);
		if ($cnt>0){
			foreach ($jsTableAry as $rowNo=>$valueAry){
				$attributes='';$ajaxAttributes='';
				$theComma=null;$ajaxDelim=null;
				foreach ($valueAry as $colName=>$colValue){
					$colValue_js=$base->utlObj->returnFormattedData($colValue,'varchar','js');
					$attributes .= $theComma.$colValue_js;
					$ajaxAttributes .= $ajaxDelim.$colValue;
					$theComma=',';$ajaxDelim="~";
				} // end foreach colname
				$ajaxLine="displayary|$ajaxAttributes\n";
				$ajaxAry[]=$ajaxLine;
				$jsTableSelectString=$jsTableSelectAry[$rowNo];
				$jsTableSelectString_js=$base->utlObj->returnFormattedData($jsTableSelectString,'varchar','js');
				$ajaxAry[]="selectary|$jsTableSelectString\n";
			} // end foreach rowno
		}
//-get list of column names in dbtable
		$nameString="";
		$delim="";
		$theDataAry=$base->tableProfileAry[$tableName]['jsalldataary'];
		//$base->debugObj->printDebug($base->tableProfileAry,1,'xxxf');
		if (count($theDataAry[0])>0){
			foreach ($theDataAry[0] as $name=>$theBody){
				$nameString.="$delim$name";
				$delim="~";
			}
		}
		$ajaxAry[]="datadef|$nameString\n";
//-get list of column names in table
		$workAry=$base->columnProfileAry['sortorderary_'.$tableName];
		$nameString="";
		$delim="";
		$theCnt=count($workAry);
		for ($lp=1;$lp<=$theCnt;$lp++){
			$tableColName=$workAry[$lp];
			$nameString.="$delim$tableColName";
			$delim="~";
		}
		$ajaxAry[]="tabledef|$nameString\n";
//-get all data from table
		foreach ($theDataAry as $rowNo=>$valueAry){
			$valueString=implode('~',$valueAry);
			$ajaxAry[]="dataary|$valueString\n";
		}
//-get list of column names in table
		//-get html code for empty version of table
		//$ajaxAry[]='!!html!!';//footer part of container still needs to be done
		return $ajaxAry;
	}	
//==========================================
	function loadFormFragments($base){
		$base->formObj->loadFormFragments(&$base);
	}
//============================================ current unused but being run
	function buildTmpList($base){
		/*
		//$base->debugObj->printDebug($base->systemAry,1,'xxxf');
		$path=$base->systemAry['tmplocal'];
		$tmpFiles=$base->fileObj->retrieveFileNames($path,'',&$base);	
		//$query="delete from variablepromptsprofile where variablepromptsname='tmpfilenames'";
		//$base->dbObj->queryTable($query,'delete',&$base);
		foreach ($tmpFiles as $ctr=>$fileName){
			//$query="insert into variablepromptsprofile (variablepromptsname,variablepromptslabel,variablepromptsvalue) values ('tmpfilenames','$fileName','$fileName')";
			//$base->dbObj->queryTable($query,'update',&$base);
		}
		*/
	}
//===============================================
	function startAutoRotate($passAry,$base){
		$menuName=$passAry['param_1'];
		$returnAry=array();
		$returnAry[]="<script type=\"text/javascript\">";
		$returnAry[]="menuObj.autoRotateImage('$menuName');";
		$returnAry[]="</script>";
		return $returnAry;
	}
//==================================================
	function insertJavascriptInit($passAry,$base){
		$returnAry=array();
		$returnAry[]="<script type=\"text/javascript\">\n";
		$returnAry[]="ajaxObj = new ajaxObject;\n";
		$returnAry[]="userObj = new userObject;\n";
		$returnAry[]="utilObj = new utilObject;\n";
		//$returnAry[]="alert ('init container');//xxxf\n";//xxxf
		$returnAry[]="containerObj = new containerObject();\n";
		//$returnAry[]="alert ('end init container');//xxxf\n";
		$returnAry[]="tableObj = new tableObject();\n";
		$returnAry[]="yuiObj = new yuiObject;\n";
		$returnAry[]="menuObj = new menuObject;\n";
		$returnAry[]="calendarObj = new calendarObject();\n";
		$returnAry[]="formObj = new formObject();\n";
		$returnAry[]="imageObj = new imageObject();\n";
		$returnAry[]="albumObj = new albumObject();\n";
		$returnAry[]="</script>\n";
		return $returnAry;
	}
//=================================================
	function getContainerViaAjax($passAry,$base){
		$jobName=$passAry['param_1'];
		$eventString_raw=$passAry['event_1'];
		if ($eventString_raw != ''){$eventString=$base->utlObj->returnFormattedString($eventString_raw,&$base);}
		else {$eventString='';}
		$jobNameAry=explode('_',$jobName);
		$jobName=$jobNameAry[0];
		$containerName=$jobNameAry[1];
		$loadId=$jobNameAry[2];
		$var1Name=$jobNameAry[3];
		$var1Value_raw=$jobNameAry[4];
		$var1Value=$base->utlObj->returnFormattedString($var1Value_raw,&$base);
		$var2Value=$base->utlObj->returnFormattedString($var2Value_raw,1,&$base);
		$var3Value=$base->utlObj->returnFormattedString($var3Value_raw,1,&$base);
		$var2Name=$jobNameAry[5];
		$var2Value_raw=$jobNameAry[6];
		$var3Name=$jobNameAry[7];
		$var3Value_raw=$jobNameAr[8];
		$varLine='';
		if ($var1Name != ''){
			$varLine.="?!$var1Name?!$var1Value";
			if ($var2Name != ''){
				$varLine.="?!$var2Name?!$var2Value";
				if ($var3Name != ''){
					$varLine.="?!$var3Name?!$var3Value";
				}
			}
		}
		$sessionName=$base->paramsAry['sessionname'];
		//$containerName=$passAry['params2'];
		$returnAry=array();
		$returnAry[]="<script type=\"text/javascript\">\n";
		//$returnAry[]="containerObj.getContainerFromServerSimple('$jobName','$containerName','post','$loadId','$sessionName');\n";
		$returnAry[]="menuObj.runBatchV2('gcfssv2?:$jobName?!$containerName?!$loadId?!$sessionName?!?!$varLine');";
		if ($eventString != ''){$returnAry[]=$eventString;}
		$returnAry[]="</script>\n";
		return $returnAry;
	}
//====================================================
	function getAlbumForAjax($passAry,$base){
		$albumName=$passAry['param_1'];
		$albumNameAry=explode('_',$albumName);
		$albumName=$albumNameAry[0];
		$albumProfileAry=$base->albumProfileAry[$albumName];
		$workAry=array();
		foreach ($albumProfileAry as $pictureName=>$pictureAry){
			$pictureNo=$pictureAry['pictureno'];
			$pictureDirectory=$pictureAry['picturedirectory'];
			$pictureFileName=$pictureAry['picturefilename'];
			$pictureSrc=$pictureDirectory.'/'.$pictureFileName;
			$pictureTitle=$pictureAry['picturetitle'];
			$pictureText=$pictureAry['picturetext'];
			$videoId=$pictureAry['videoid'];
			$workAry[$pictureNo]=array('pctno'=>$pictureNo,'src'=>$pictureSrc,'title'=>$pictureTitle,'text'=>$pictureText,'no'=>$pictureNo,'videoid'=>$videoId);
		}
		//- below assumes that the sorting is done by the first value: 'pctno'
		sort($workAry);
		$theSrcStrg=null;
		$theTitleStrg=null;
		$theTextStrg=null;
		$theVideoIdStrg=null;
		$theCnt=count($workAry);
		$delim=null;
		for ($lp=0;$lp<$theCnt;$lp++){
			$thisAry=$workAry[$lp];
			$thisSrc=$workAry[$lp]['src'];
			$thisTitle=$workAry[$lp]['title'];
			$thisText=$workAry[$lp]['text'];
			$thisVideoId=$workAry[$lp]['videoid'];
			$theSrcStrg.=$delim.$thisSrc;
			$theTitleStrg.=$delim.$thisTitle;
			$theTextStrg.=$delim.$thisText;
			$theVideoIdStrg.=$delim.$thisVideoId;
			$delim='~';
		}
		$returnAry=array("!!album!!\n","setalbumname|$albumName\n");
		$returnAry[]="loadalbumsrc|$theSrcStrg\n";
		$returnAry[]="loadalbumtitles|$theTextStrg\n";
		$returnAry[]="loadalbumcaptions|$theTitleStrg\n";
		$returnAry[]="loadvideoids|$theVideoIdStrg\n";
		return $returnAry;
	}
//============================================================
	function getAlbumForAjaxV2($passAry,$base){
		$albumName=$passAry['param_1'];
		$albumNameAry=explode('_',$albumName);
		$albumName=$albumNameAry[0];
		$albumProfileAry=$base->albumProfileAry[$albumName];
		$albumEtc=$base->albumProfileAry['mainv2'][$albumName];
		$returnAry=array("!!album!!\n","setalbumname|$albumName\n");
//- album profile
		foreach ($albumEtc as $etcName=>$etcValue){
			$returnAry[]="loadetc?%$etcName?%$etcValue\n";
		}		
//- picture profile
		$workAry=array();
		foreach ($albumProfileAry as $pictureName=>$pictureAry){
			$pictureNo=$pictureAry['pictureno'];
			$pictureDirectory=$pictureAry['picturedirectory'];
			$pictureFileName=$pictureAry['picturefilename'];
			$pictureSrc=$pictureDirectory.'/'.$pictureFileName;
			$pictureTitle=$pictureAry['picturetitle'];
			$pictureText=$pictureAry['picturetext'];
			$videoId=$pictureAry['videoid'];
			$workAry[$pictureNo]=array('pctno'=>$pictureNo,'src'=>$pictureSrc,'title'=>$pictureTitle,'text'=>$pictureText,'no'=>$pictureNo,'videoid'=>$videoId);
		}
		//- below assumes that the sorting is done by the first value: 'pctno'
		sort($workAry);
		$theSrcStrg=null;
		$theTitleStrg=null;
		$theTextStrg=null;
		$theVideoIdStrg=null;
		$theCnt=count($workAry);
		$delim=null;
		for ($lp=0;$lp<$theCnt;$lp++){
			$thisAry=$workAry[$lp];
			$thisSrc=$workAry[$lp]['src'];
			$thisTitle=$workAry[$lp]['title'];
			$thisText=$workAry[$lp]['text'];
			$thisVideoId=$workAry[$lp]['videoid'];
			$theSrcStrg.=$delim.$thisSrc;
			$theTitleStrg.=$delim.$thisTitle;
			$theTextStrg.=$delim.$thisText;
			$theVideoIdStrg.=$delim.$thisVideoId;
			$delim='~';
		}
		$returnAry[]="loadalbumsrc|$theSrcStrg\n";
		$returnAry[]="loadalbumtitles|$theTextStrg\n";
		$returnAry[]="loadalbumcaptions|$theTitleStrg\n";
		$returnAry[]="loadvideoids|$theVideoIdStrg\n";
		return $returnAry;
	}
//====================================================
	function saveParams($passAry,$base){
		$workAry=array();
		$saveName=$passAry['param_1'];
		foreach ($base->paramsAry as $paramName=>$paramValue){
			$workAry[$paramName]=$paramValue;
		}
		$base->utlObj->saveValue($saveName,$workAry,&$base);
	}
//==================================================== an operation runs this too
	function getParams($passAry,$base){
		$getName=$passAry['param_1'];
		$workAry=$base->utlObj->retrieveValue($getName,&$base);
		if ($workAry != null){
			foreach ($workAry as $paramName=>$paramValue){
				$base->paramsAry[$paramName]=$paramValue;
			}
		}
	}
//====================================================
	function updateJobStats($passAry,$base){
		//$base->debugObj->printDebug($passAry,1,'xxxf');
		$companyProfileId=$base->jobProfileAry['companyprofileid'];
		$jobStatName=$passAry['param_1'];
		$passAry=array('thedate'=>'today');
		$todayDateAry=$base->utlObj->getDateInfo($passAry,&$base);
		//$base->debugObj->printDebug($todayDateAry,1,'xxxf');
		$todayDate=$todayDateAry['date_v1'];
		$query="select * from jobstatsview where jobstatsdate = '$todayDate' and jobstatsname='$jobStatName' and companyprofileid=$companyProfileId";
		//echo "query: $query<br>";
		$result=$base->dbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$writeRowsAry=$base->utlObj->tableToHashAryV3($result,$passAry);
		$cnt=count($writeRowsAry);
		//echo "xxxf0: $cnt<br>";//xxxf
		if ($cnt>0){
			$theCnt=$writeRowsAry[0]['jobstatscnt'];
			$theCnt++;
			$writeRowsAry[0]['jobstatscnt']=$theCnt;
			$writeRowsAry[0]['jobstatsdate']=$base->utlObj->returnFormattedData($writeRowsAry[0]['jobstatsdate'],'date_dateconv1','internal',&$base);
			//$base->debugObj->printDebug($writeRowsAry,1,'xxxf3');
		}
		else {
			$theAry=array('jobstatsname'=>$jobStatName,'jobstatsdate'=>$todayDate,'jobstatscnt'=>1,'companyprofileid'=>$companyProfileId);
			$writeRowsAry[]=$theAry;
		}
		$dbControlsAry=array('dbtablename'=>'jobstats','writerowsary'=>$writeRowsAry);
		//$base->debugObj->printDebug($dbControlsAry,1,'xxxf');
		$success=$base->dbObj->writeToDb($dbControlsAry,&$base);
		//$errorStrg=$base->errorObj->retrieveAllErrors(&$base);
		//echo "errors: $errorStrg<br>";//xxxf
	}
//=========================================
	function insertParagraphV2($paramFeed,$base){
		$base->debugObj->printDebug("plugin002Obj:status()",0); //xx (h)
		$returnAry=array();
		$paragraphName=$paramFeed['param_1'];
		$paragraphAry=$base->paragraphProfileAry[$paragraphName];
//- get data maybe
		$paragraphDbTableName=$paragraphAry['paragraphdbtablename'];
		if ($paragraphDbTableName != null){
			$dbControlsAry=array('dbtablename'=>$paragraphDbTableName);
			$base->dbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$keyName=$dbControlsAry['keyname'];
			$keyValue=$base->paramsAry[$keyName];
			$dataRowsAry=array();
			$dataRowsAry[]=array($keyName=>$keyValue);
			$dbControlsAry['datarowsary']=$dataRowsAry;
			$workAry=$base->dbObj->readFromDb($dbControlsAry,&$base);
			$paragraphDataAry=$workAry[0];
		}
		else {
			$dataAry=array();
		}
		//-
		$paragraphClass=$paragraphAry['paragraphclass'];
		if ($paragraphClass == null){$paragraphClassInsert=null;}
		else {$paragraphClassInsert="class=\"$paragraphClass\"";}
		//-
		$paragraphId=$paragraphAry['paragraphid'];
		if ($paragraphId == null){$paragraphIdInsert=null;}
		else {$paragraphIdInsert="id=\"$paragraphId\"";}
		//echo "parainsert: $paragraphClassInsert<br>";//xxxf]]
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
		//$base->debugObj->printDebug($sentenceOrderAry,1,'sentenceorderary');//xxx
		$beginComment="<!-- start paragraph: $paragraphName -->";$endComment="<!-- end paragraph: $paragraphName -->";
		if ($paragraphType=='span'){$beginDivider="<span $paragraphClassInsert $paragraphIdInsert>";$endDivider="</span>";$divider_front='<span';$divider_back='</span>';}
		else if($paragraphType=='ul'){$beginDivider="<ul $paragraphClassInsert $paragraphIdInsert>";$endDivider='</ul>';$divider_front='<li';$divider_back='</li>';}
		else if($paragraphType=='ol'){$beginDivider="<ol $paragraphClassInsert $paragraphIdInsert>";$endDivider='</ol>';$divider_front='<li';$divider_back='</li>';}
		else {$beginDivider="<div $paragraphClassInsert $paragraphIdInsert>";$endDivider="</div>";$divider_front='<div';$divider_back='</div>';}
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
			$sentenceBreak=$base->utlObj->returnFormattedData( $sentenceBreak_array, 'boolean', 'internal');
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
			$sentenceText_good=$base->utlObj->returnFormattedStringDataFed($sentenceText,$paragraphDataAry,&$base);
			//echo "sentencetype: $sentenceType<br>";//xxx
			if ($showSentence){	
				if ($sentenceType == 'text'){
					$sentenceLine="$divider_front $insertSentenceClass $insertSentenceId>$sentenceText_good $divider_back $insertSentenceBreak";
					}
				else {
					$jobLink=$sentenceAry['sentenceurl'];
					$jobLink_html=$base->utlObj->returnFormattedData($jobLink,'url','html',&$base);
					$sentenceLine="<a href=\"$jobLink_html\" $insertSentenceId $insertSentenceClass>$sentenceText_good</a>$insertSentenceBreak";
				}
				$returnAry[]="$sentenceLine\n";
			}
		} // end next
		$returnAry[]=$endDivider."\n";
		$returnAry[]=$endComment."\n";
		} // end if
		//$base->debugObj->printDebug($returnAry,1,'rtn');//xxx
		$base->debugObj->printDebug("-rtn:xx",0); //xx (f)
		return $returnAry;
	}
//====================================================
	function updateSessionFromContainer($paramFeed,$base){
		$variableFeed=$paramFeed['param_1'];
		$variableFeedAry=explode('_',$variableFeed);
		$variableName=$variableFeedAry[0];
		$variableValue=$variableFeedAry[1];
		$base->paramsAry[$variableName]=$variableValue;
		$base->paramsAry['savetosession']=$variableName;
		$base->plugin001Obj->updateSession(&$base);
		//$base->debugObj->printDebug($base->paramsAry,1,'xxxf');exit();
	}
//======================================================
	function insertJQuery($paramFeed,$base){
		$preLine="<!--- start jquery -->\n";
		$endLine="<!--- end jquery --->\n";
		$theMainLine="$preLine<script type=\"text/javascript\">\njQuery.noConflict();\njQuery(document).ready(function(){\nINSERTJQUERYBODY});\n</script>\n$endLine";	
		$aLine="jQuery('INSERTSELECTOR').INSERTEVENT(function(event){\n\t\tevent.preventDefault();\n\t\tmenuObj.runBatchV2('INSERTCODES');\n\t})\n";
		$bLine="\tjQuery(\"INSERTSELECTOR\").INSERTEVENT();\n";
		$theLines="";
		$eventsAry=$base->cssProfileAry['events'];
		//$base->debugObj->printDebug($eventsAry,1,'xxxf');
		$noJQuery=count($eventsAry);
		for ($lp=0;$lp<$noJQuery;$lp++){
			$class=$eventsAry[$lp]['cssclass'];
			$id=$eventsAry[$lp]['cssid'];
			$htmlTag=$eventsAry[$lp]['htmltag'];
			$eventType=$eventsAry[$lp]['csseventtype'];
			$eventProgram=$eventsAry[$lp]['csseventprogram'];
			$eventCode=$eventsAry[$lp]['csseventcode'];
			if ($eventCode == ''){$eventCode='menu';}
			if ($id != '' && $id != 'none'){$idSelector="$htmlTag#$id";}
			else {$idSelector='';}
			if ($class != '' && $class != 'none'){$classSelector="$htmlTag.$class";}
			else {$classSelector='';}
			if ($idSelector != ''){
				switch ($eventCode){
					case 'menu':
						$newLine=str_replace('INSERTSELECTOR',$idSelector,$aLine);
						$newLine=str_replace('INSERTEVENT',$eventType,$newLine);
						$newLine=str_replace('INSERTCODES',$eventProgram,$newLine);
						break;
					default:
					//echo "bline: $bLine<br>";
						$newLine=str_replace('INSERTSELECTOR',$idSelector,$bLine);
					//echo "newLine1: $newLine<br>";
						$newLine=str_replace('INSERTEVENT',$eventType,$newLine);
					//echo "newLine2: $newLine<br>";
				}
				$theLines.="$newLine";
				//echo "add theline: $newLine";//xxxf
			}
			if ($classSelector != ''){
				switch ($eventCode){
					case 'menu':
						$newLine=str_replace('INSERTSELECTOR',$classSelector,$aLine);
						$newLine=str_replace('INSERTEVENT',$eventType,$newLine);
						$newLine=str_replace('INSERTCODES',$eventProgram,$newLine);
						break;
					default:
					//echo "bline: $bLine<br>";
						$newLine=str_replace('INSERTSELECTOR',$classSelector,$bLine);
					//echo "newline3: $newLine<br>";
						$newLine=str_replace('INSERTEVENT',$eventType,$newLine);
					//echo "newline4: $newLine<br>";
				}
				$theLines.="$newLine";
				//echo "add $theLine: $newLine<br>";//xxxf
			}
		}
		//echo "add paren: $theLines<br>";
		//echo "thelines: $theLines";//xxxf
		//exit();
		$theMainLine=str_replace('INSERTJQUERYBODY',$theLines,$theMainLine);
		//echo "themainline: $theMainLine<br>";
		//exit();//xxxf
		$returnAry=array($theMainLine);
		return $returnAry;
	}
//====================================================
	function insertStyleSheet($paramFeed,$base){
		$theName=$paramFeed['param_1'];
		//below should be "/stylesheets/$theName";
		$thePath="/includes.css/$theName";
		$theLine="<link rel=\"stylesheet\" type=\"text/css\" href=\"$thePath\">";
		$returnAry=array($theLine);
		return $returnAry;
	}
//=======================================================
	function insertStyleImport($paramFeed,$base){
		$theName=$paramFeed['param_1'];
		$theNameAry=explode('_',$theName);
		$theJobName=$theNameAry[0];
		$thePrefixStrg='';
		$theCnt=count($theNameAry);
		$delim="'";
		$comma="";
		for ($lp=1;$lp<$theCnt;$lp++){
			$thePrefixStrg.=$comma.$delim.$theNameAry[$lp].$delim;
			$comma=",";
		}
		//below needs to select within this company also - I think?
		$query="select * from csselementprofileview where jobname='$theJobName' and prefix in ($thePrefixStrg) order by cssclass, cssid, htmltag";
		//echo "query: $query";exit();//xxxf
		$passAry=array();
		$workAry=$base->dbObj->queryTableRead($query,$passAry,&$base);
		//echo "query: $query<br>";
		//$base->debugObj->printDebug($workAry,1,'xxxf');exit();
		$styleSheet='';$oldPrefix='';$oldCss='';$oldId='';$oldHtmlTag='';
		$cnt=count($workAry);$lp=0;
		foreach ($workAry as $no=>$cssAry){
			$lp++;
			$prefix=$cssAry['prefix'];
			$cssClass=$cssAry['cssclass'];
			if ($cssClass=='none'){
				$cssClass='';
			}
			$cssId=$cssAry['cssid'];
			if ($cssId == 'none'){
				$cssId='';
			}
			$htmlTag=$cssAry['htmltag'];
			if ($htmlTag == 'none'){
				$htmlTag='';
			}
			$theProperty=$cssAry['csselementproperty'];
			$theValue=$cssAry['csselementvalue'];
			$tmpSheet.="$prefix,$cssClass,$cssId,$htmlTag,$theProperty,$theValue\n";
			if ($cssClass != $oldClass && $oldClass != ''){
				if ($propertyStrg != ''){
					$propertyStrg="$oldHtmlTag"."."."$oldClass{\n".$propertyStrg."}\n";
					$styleSheet.=$propertyStrg;
					$tmpSheet.=$propertyStrg;
					$propertyStrg='';
				}
			}
			else if ($cssId != $oldId && $oldId != ''){
				if ($propertyStrg != ''){
					$propertyStrg="$oldHtmlTag"."#"."$oldId{\n".$propertyStrg."}\n";
					$styleSheet.=$propertyStrg;
					$tmpSheet.=$propertyStrg;
					$propertyStrg='';
				}
			}
			else if ($htmlTag != $oldHtmlTag && $oldHtmlTag != ''){
				if ($propertyStrg != ''){
					if ($oldClass==''){
						$useStrg='#'.$oldId;
					}
					else {$useStrg='.'.$oldClass;
					}
					$propertyStrg="$oldHtmlTag".$useStrg."{\n".$propertyStrg."}\n";
					$styleSheet.=$propertyStrg;
					$tmpSheet.=$propertyStrg;
					$propertyStrg='';
				}
			}
			$propertyStrg.=$theProperty.':'.$theValue.";\n";
			$oldClass=$cssClass;$oldId=$cssId;$oldHtmlTag=$htmlTag;$oldPrefix=$prefix;
		}
		if ($propertyStrg != ''){
			if ($oldClass==''){
				$useStrg='#'.$oldId;
			}
			else {$useStrg='.'.$oldClass;
			}
			$propertyStrg="$oldHtmlTag".$useStrg."{\n".$propertyStrg."}\n";
			$styleSheet.=$propertyStrg;
		}
		if ($styleSheet != ''){
			$styleSheet="<!-- import style sheet from $theJobName $thePrefixName -->\n"."<style>\n".$styleSheet."\n</style>\n<!-- end import style sheet -->\n";
			$returnAry=array($styleSheet);
		}
		return $returnAry;
	}
//====================================================
	function incCalls(){$this->callNo++;}
}
?>