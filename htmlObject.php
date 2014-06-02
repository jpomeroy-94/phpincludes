<?php
class HtmlObject {
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
	var $metaProfileAry = array();
	//=======================================
	function HtmlObject($base) {
		$this->incCalls();
		//- check metaprofile
		$this->statusMsg='html Object is fired up and ready for work!';
	}
	//=======================================
	function getMetaProfile($base){
		$passAry=array();
		$viewName='metaprofileview';
		$delimit1='metaname';
		$passAry['viewname']=$viewName;
		$passAry['delimit1']=$delimit1;
		//$base->DebugObj->printDebug($passAry,1,'xxxdpassary');
		$this->metaProfileAry=$base->InitObj->getGenericProfile($passAry,&$base);
		//$base->DebugObj->printDebug($this->metaProfileAry,1,'xxxd');
		//exit();//xxxd
	}
	//=======================================
	function processHtmlFile($base){
		$base->DebugObj->printDebug("HtmlObj:processHtmlFile('base')",0);
		$base->FileObj->writeLog('debug1','!!processhtmlfile!!',&$base);
		$this->getMetaProfile(&$base);
		//
		$htmlName=$base->htmlProfileAry['htmlname'][0];
		//$htmlPathSt="$basePathSt/$htmlDirSt/$htmlNameSt";
		$workAry=$base->ClientObj->getSystemData(&$base);
		$baseLocal=$workAry['baselocal'];
		//- need to make below more generic
		$domainName=$workAry['domainname'];
		if ($domainName=='lindy' || $domainName=='lindy/webinit'||$domainName=='lindy/testing'){
			$libInsert=NULL;
		}
		else {$libInsert='/lib';}
		$htmlPath=$baseLocal.$libInsert.'/htmllib/'.$htmlName;
		if (array_key_exists('container',$base->paramsAry)){
			$containerOverrideName=$base->paramsAry['container'];
			if ($containerOverrideName != 'none'){
				$htmlAry=array();
				$htmlAry[]="!!insertcontainer_$containerOverrideName!!";
			}
			else {$htmlAry=array();}
		}
		else {
			$htmlAry=$base->FileObj->getFileArray($htmlPath);
		}
		$operAry=$base->currentOperationAry;
		$htmlAry_print=array();
		$htmlCnt=count($htmlAry);
		$base->FileObj->writeLog('debug1','xxxd2',&$base);//xxxd666
		for ($htmlCtr=0;$htmlCtr<$htmlCnt;$htmlCtr++){
			$htmlLineSt=$htmlAry[$htmlCtr];
			//echo "$htmlCtr, htmlline: $htmlLineSt";//xxxd
			if (strpos($htmlLineSt,'//',0) !== false){
				$htmlLineStAry=explode('//',$htmlLineSt);
				$htmlLineSt_withoutcomments=$htmlLineStAry[0];
				$htmlLineSt=trim($htmlLineSt_withoutcomments);
			}
			if (strpos($htmlLineSt,$this->delim,0) !== false) {
				$htmlLineAry=$this->convertHtmlLine($htmlLineSt,&$base);
				if (is_array($htmlLineAry)){
					$htmlAry_print=array_merge($htmlAry_print,$htmlLineAry);
				}
			}
			elseif (strpos($htmlLineSt,'<head',0) !== false){
					$htmlAry_print[]="$htmlLineSt";
					//- do meta stuff
					foreach ($this->metaProfileAry['dataary'] as $metaName=>$metaStuffAry){
						$metaContent=$metaStuffAry['metacontent'];
						$htmlAry_print[]="<meta name=\"$metaName\" content=\"$metaContent\"/>\n";
					}
					//- old crap
					$jsLevel=$base->jobProfileAry['jobjslevel'];
					if ($jsLevel == ''){
						$jsLevel=$base->jobProfileAry['companyjslevel'];
					}
					if ($jsLevel == ''){$jsLevel='nonstandardjs';}
					if ($jsLevel != 'standardjs'){
						$htmlAry_print[]='<script type="text/javascript" src="/includes.js/general.js"></script>'."\n";
					}
					//- prototype and yui
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/prototype-1.6.1.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/yui/build/yui/yui-min.js"></script>'."\n";
					//- my stuff
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/calendarObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/formObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/menuObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/containerObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/tableObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/ajax.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/utilObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/yuiObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/imageObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/userObject.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="/includes.js/albumObject.js"></script>'."\n";
					//- jquery
					//$htmlAry_print[]='<script type="text/javascript" src="includes.js/jquery-1.7.1.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript" src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>'."\n";
					$htmlAry_print[]='<script type="text/javascript"> j$ = jQuery.noConflict();</script>'."\n";
					//xxxf below causes an abort in ContainerObj.loadCss(sp?)
					//below caused aborts with old way of updating css
					$htmlAry_print[]='<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />'."\n";
					$htmlAry_print[]='<script type="text/javascript"> jQuery.noConflict();</script>'."\n";
					//- cant get to work $htmlAry_print[]='<script type="text/javascript" src="/includes.js/AjaxObject.js"></script>'."\n";
				} // end if (strpos
				else {$htmlAry_print[]="$htmlLineSt";}
		} // end for
		//- print it all after buffered
		$base->FileObj->writeLog('debug1',"xxxd9",&$base);
		$htmlPrintCnt=count($htmlAry_print);
		$base->FileObj->writeLog('debug1','xxxd10: htmlprintcnt: '+$htmlPrintCnt,&$base);
		for ($htmlCtr=0;$htmlCtr<$htmlPrintCnt;$htmlCtr++){echo $htmlAry_print[$htmlCtr];}
		$base->FileObj->writeLog('debug1','xxxd11',&$base);
		//xxxf
		/*
		//if ($containerOverrideName=='desktopttdtr'){
			$theJob=$base->paramsAry['job'];
			$thePath="/home/jeff/tmp/".$theJob."_".$containerOverrideName.".txt";
			$theFileAry=$htmlAry_print;
			$delim="\n";
			$theFile=implode($delim,$theFileAry);
			$base->FileObj->writeFile($thePath,$theFile,&$base);
		//}
		 */
	}
	//=======================================
	function convertHtmlLine($htmlLine, $base){
		$base->DebugObj->printDebug("HtmlObj:convertHtmlLine($htmlLine,'base')",0);
		//echo "htmlobj: xxxd2 htmlline: $htmlLine<br>";//xxxd66
		
		$returnAry=array();
		$insertCodeAry=$base->UtlObj->extractInsertCode($htmlLine);
		$pluginName=$insertCodeAry['insertcode'];
		$insertSubCode=$insertCodeAry['insertsubcode'];
		$paramFeed=array();
		$pluginName=strtolower($pluginName);
		$paramFeed['param_1']=$insertSubCode;
		$paramFeed['param_2']=$htmlLine;
		$paramFeed['param_3']=1;
		//echo "$pluginName, $insertSubCode, $htmlLine<br>";//xxx
		//echo "pluginname: $pluginName, htmlline: $htmlLine<br>";//xxxd
		if ($pluginName != ""){
			//echo "htmlobj: run pluginname: $pluginName<br>";//xxxd666
			$returnAry=$base->PluginObj->runTagPlugin($pluginName,$paramFeed,&$base);
			//echo "htmlobj: back from it";//xxxd666
			
			//xxxf666 temporary fix for capital objects problem
			$theCnt=count($returnAry);
			for ($lp=0;$lp<$theCnt;$lp++){
				//echo "xxxf: $returnAry[$lp]...";
				$returnAry[$lp]=str_replace("menuObj", "MenuObj", $returnAry[$lp]);
				$returnAry[$lp]=str_replace("yuiObj", "YuiObj",$returnAry[$lp]);
				$returnAry[$lp]=str_replace("tableObj", "TableObj",$returnAry[$lp]);
				//echo " $returnAry[$lp]<br>";
			}
			//xxxf666 end temporary fix
		}
		$base->DebugObj->printDebug("-rtn:convertHtmlLine",0); //xx (f)
		//echo "htmlobj: xxxd2-end<br>";//xxxd666
		return $returnAry;
	}
	//=======================================
	function buildOldImg($urlAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildOldImg($urlAry,'base')",0); //xx (h)
		$jobLink=$urlAry['joblink'];
		if ($jobLink == NULL){$jobLink=$urlAry['label'];}
		$jobLink_html=$base->UtlObj->returnFormattedData($jobLink,'varchar','html',&$base);
		$eventAttributes=$urlAry['htmlelementeventattributes'];
		$imageName=$urlAry['htmlelementimagename'];
		if ($imageName == NULL){$imageName=$urlAry['htmlelementname'];}
		$imageId=$urlAry['imageid'];
		if ($imageId != NULL){$imageIdInsert="id=\"$imageId\"";}
		else {$imageIdInsert=NULL;}
		$imageClass=$urlAry['htmlelementclass'];
		if ($imageClass != NULL){$imageClassInsert="class=\"$imageClass\"";}
		else {$imageClassInsert=NULL;}
		if ($eventAttributes != NULL){
			$eventAttributes_html=$base->UtlObj->returnFormattedData($eventAttributes,'varchar','html',&$base);
			$eventAttributesInsert=$eventAttributes_html;
		}
		else {$eventAttributesInsert=NULL;}
		//-
		$imageUseMap=$urlAry['imageusemap'];
		//echo "imageusemap: $imageUseMap";//xxx
		//$base->DebugObj->printDebug($urlAry,1,'urlary');//xxx
		if ($imageUseMap != NULL){$imageUseMapInsert="usemap=\"$imageUseMap\"";}
		else {$imageUseMapInsert=NULL;}
		//echo "name: $imageName, src: $jobLink_html<br>";//xxx
		//-
		$imageAlt=$urlAry['imagealt'];
		if ($imageAlt != null){$imageAltInsert="alt=\"$imageAlt\"";}
		else {$imageAltInsert=null;}
		$returnAry=array();
		$returnAry[]="<img name=\"$imageName\" src=$jobLink_html $imageAltInsert $imageClassInsert $imageIdInsert $imageUseMapInsert $eventAttributesInsert>\n";
		return $returnAry;
	}
	//=======================================
	function buildImgPass($passAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildImg($urlAry,'base')",0); //xx (h)
		$imageName=$passAry['imagename'];
		$passedImageEvents=$passAry['imageevents'];
		$passedImageId=$passAry['imageid'];
		$imageAry=$base->imageProfileAry[$imageName];
		if ($passedImageEvents != null){$imageAry['imageevents']=$passedImageEvents;}
		//-
		$imageSource_raw=$imageAry['imagesource'];
		$imageSource=$base->UtlObj->returnFormattedString($imageSource_raw,&$base);
		$imageSource_html=$base->UtlObj->returnFormattedData($imageSource,'varchar','html',&$base);
		//-
		$imageId=$imageAry['imageid'];
		if ($imageId == NULL){$imageId=$passedImageId;}
		if ($imageId != NULL){$imageIdInsert="id=\"$imageId\"";}
		else {$imageIdInsert="id=\"$imageName\"";}
		//echo "imageid: $imageId<br>";//xxxd
		//-
		$imageAlt=$imageAry['imagealt'];
		if ($imageAlt != null){$imageAltInsert="alt=\"$imageAlt\"";}
		else {$imageAltInsert=null;}
		//-
		$imageClass=$imageAry['imageclass'];
		if ($imageClass != NULL){$imageClassInsert="class=\"$imageClass\"";}
		else {$imageClassInsert=NULL;}
		//-
		$eventAttributes=$imageAry['imageevents'];
		if ($eventAttributes != NULL){
			$eventAttributes_html=$base->UtlObj->returnFormattedData($eventAttributes,'varchar','html',&$base);
			$eventAttributes_html=$base->UtlObj->returnFormattedString($eventAttributes_html,&$base);
			$eventAttributesInsert=$eventAttributes_html;
		}
		else {$eventAttributesInsert=NULL;}
		//$base->DebugObj->printDebug($eventAttributes_html,1,'xxxd');
		//-
		$imageUseMap=$imageAry['imageusemap'];
		if ($imageUseMap != NULL){$imageUseMapInsert="usemap=\"$imageUseMap\"";}
		else {$imageUseMapInsert=NULL;}
		$returnAry=array();
		//echo "image name: $imageName, src: $imageSource_html<br>";//xxxd
		$imageHtml="<img name=\"$imageName\" src=$imageSource_html $imageAltInsert $imageClassInsert $imageIdInsert $imageUseMapInsert $eventAttributesInsert>\n";
		$returnAry[]=$imageHtml;
		return $returnAry;
	}
	//=======================================
	function buildImg($imageName,$base){
		$base->DebugObj->printDebug("HtmlObj:buildImg($urlAry,'base')",0); //xx (h)
		$imageAry=$base->imageProfileAry[$imageName];
		//-
		$imageSource_raw=$imageAry['imagesource'];
		$imageSource=$base->UtlObj->returnFormattedString($imageSource_raw,&$base);
		$imageSource_html=$base->UtlObj->returnFormattedData($imageSource,'varchar','html',&$base);
		//-
		$imageId=$imageAry['imageid'];
		if ($imageId != NULL){$imageIdInsert="id=\"$imageId\"";}
		else {$imageIdInsert="id=\"$imageName\"";}
		//-
		$imageAlt=$imageAry['imagealt'];
		if ($imageAlt != null){$imageAltInsert="alt=\"$imageAlt\"";}
		else {$imageAltInsert=null;}
		//-
		$imageClass=$imageAry['imageclass'];
		if ($imageClass != NULL){$imageClassInsert="class=\"$imageClass\"";}
		else {$imageClassInsert=NULL;}
		//-
		$eventAttributes=$imageAry['imageevents'];
		if ($eventAttributes != NULL){
			$eventAttributes_html=$base->UtlObj->returnFormattedData($eventAttributes,'varchar','html',&$base);
			$eventAttributesInsert=$eventAttributes_html;
		}
		else {$eventAttributesInsert=NULL;}
		//-
		$imageUseMap=$imageAry['imageusemap'];
		if ($imageUseMap != NULL){$imageUseMapInsert="usemap=\"$imageUseMap\"";}
		else {$imageUseMapInsert=NULL;}
		$returnAry=array();
		//echo "image name: $imageName, src: $imageSource_html<br>";//xxx
		$imageHtml="<img name=\"$imageName\" src=$imageSource_html $imageAltInsert $imageClassInsert $imageIdInsert $imageUseMapInsert $eventAttributesInsert>\n";
		$returnAry[]=$imageHtml;
		return $returnAry;
	}
	//=======================================
	function buildInputSelect($elementAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildInputSelect($urlAry,'base')",0); //xx (h)
		$elementName=$elementAry['htmlelementname'];
		$elementLabel=$elementAry['htmlelementlabel'];
		$eventAttributes=$elementAry['htmlelementeventattributes'];
		$eventAttributes_html=$base->UtlObj->returnFormattedData($eventAttributes,'varchar','html',&$base);
		$elementClass=$elementAry['htmlelementclass'];
		if ($elementClass != ''){
			$insertClass="class=\"$elementClass\"";
		} else {$insertClass='';}
		//xxxdbugfix 100711 need to put in the id entered
		$elementId=$elementAry['htmlelementid'];
		if ($elementId == null){$elementId=$elementName;}
		$returnAry=array();
		$newLine="$elementLabel<input type=\"text\" name=\"$elementName\" id=\"$elementId\" $insertClass $eventAttributes_html>";
		$returnAry[]=$newLine;
		$base->DebugObj->printDebug("-rtn:buildInputSelect",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function buildCssLink($eleAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildCssLink($eleAry,'base')",0); //xx (h)
		$cssPath=$eleAry['joblink'];
		$returnAry[]="<link rel=\"stylesheet\" type=\"text/css\" href=\"$cssPath\">";
		return $returnAry;
	}
	//=======================================
	function buildUrl($urlAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildUrl($urlAry,'base')",0); //xx (h)
		$returnAry=array();
		$label_raw=$urlAry['label'];
		$label=$base->UtlObj->returnFormattedString($label_raw,&$base);
		$class=$urlAry['htmlelementclass'];
		$imageName=$urlAry['htmlelementimagename'];
		$savedTableName=$urlAry['htmlelementsavedtablename'];
		$htmlElementEventAttributes_raw=$urlAry['htmlelementeventattributes'];
		$htmlElementEventAttributes=$base->UtlObj->returnFormattedString($htmlElementEventAttributes_raw,&$base);
		//- target
		$htmlElementTarget=$urlAry['htmlelementtarget'];
		if ($htmlElementTarget != null){$targetInsert="target=\"$htmlElementTarget\"";}
		else {$targetInsert=null;}
		//echo "htmlelementtarget: $htmlElementTarget, targetinsert: $targetInsert<br>\n";//xxxf
		//- sent over from horizontal menus
		$menuElementAltInsert=$urlAry['menuelementaltinsert'];
		$bulletInsert=$urlAry['bulletpath'];
		//echo "htmlevent: $htmlElementEventAttributes<br>";//xxx
		//$base->DebugObj->printDebug($urlAry,1,'url');//xxx
		if ($imageName != NULL){
			$imageAry_html=$base->HtmlObj->buildImg($imageName,&$base);
			//!!! the below better be right !!!
			$label=$imageAry_html[0];
		}
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert='';}
		$id=$urlAry['htmlelementid'];
		if ($id != ''){$idInsert="id=\"$id\"";}
		else {$idInsert='';}
		$jobLink_raw=$urlAry['joblink'];
		$jobLink=$base->UtlObj->returnFormattedString($jobLink_raw,&$base);
		$pos=strpos($jobLink,'mapsprofileid',0);//xxxd
		//- add in session stuff if name in params
		$sessionName=$base->paramsAry['sessionname'];
		//echo "sessionname: $sessionName<br>";
		// below puts in a session argument when there should not be one
		if ($sessionName != NULL){
			if ($jobLink != NULL){
				$posHttp=strpos('x'.$jobLink,'http:',0);
				if ($posHttp<=0){
					$pos=strpos('x'.$jobLink,'sessionname',0);
					if ($pos <= 0){	$jobLink.="&sessionname=$sessionName";}
				}
				//echo "joblink: $jobLink<br>";//xxx
			}
			else { $jobLink="sessionname=$sessionName";}
		}
		//echo "joblink: $jobLink<br>";//xxx
		//- do conversions
		$itIsThere=true;
		while ($itIsThere){
			$extractedStr=$base->UtlObj->extractStr('%',$jobLink);
			//echo "extractedstr: $extractedStr<br>";//xxx
			if ($extractedStr != ''){
				//- get it from retrievevalue using dbfilename first
				$dbTableName=$urlAry['htmlelementdbtablename'];
				//echo "dbtablename: $dbTableName<br>";//xxx
				if ($dbTableName != ''){
					$overrideName=$base->UtlObj->retrieveValue('overridename_'.$dbTableName,&$base);
					$overrideValue=$base->UtlObj->retrieveValue('overridevalue_'.$dbTableName,&$base);
				} else {$overrideName = '';}
				if ($extractedStr == $overrideName && $overrideValue != NULL){
					//- got it overridename, overridevalue from session so do it
					$jobLink = $base->UtlObj->replaceStr($jobLink,'%'.$extractedStr.'%',$overrideValue,&$base);
					$base->UtlObj->saveValue($extractedStr.'-urlsave',$overrideValue,&$base);
				}
				else {
					//- get it from params since not in session
					$overrideValue=$base->paramsAry[$extractedStr];
					//echo "extractedstr: $extractedStr, overridevalue: $overrideValue<br>";//xxx
					if ($overrideValue == NULL && $savedTableName != NULL){
						//- get it from first row of table display since have table name
						$overrideValue=$base->insertedTablesAry[$savedTableName][0][$extractedStr];
					} // end if overridevalue==null savedtablename!=null
					if ($overrideValue == NULL){
						//- get it from save of this name last time as last resort
						$overrideValue=$base->UtlObj->retrieveValue($extractedStr.'-urlsave',&$base);
					} // end if overridevalue
					if ($overrideValue == NULL){
						//- if still null try to get it from default htmlprofileary
						$overrideValue=$base->htmlProfileAry['default'][$extractedStr];
						if ($overrideValue == NULL){$overrideValue='cantgetinHtmlObjbuildUrl';}
					} // end if overridevalue
					$jobLink = $base->UtlObj->replaceStr($jobLink,'%'.$extractedStr.'%',$overrideValue,&$base);
					$base->UtlObj->saveValue($extractedStr.'-urlsave',$overrideValue,&$base);
				} // end else
			}
			else {$itIsThere=false;}
		} // end while itisthere xxx
		//$pos=strpos('x'.$jobLink,'setup',0);
		//if ($pos>0){echo "joblink: $jobLink<br>";}
		$jobLinkAry=explode('&',$jobLink);
		$chkJobLink=$jobLinkAry[0];
		if ($chkJobLink=='#'){
			$insertHRef="href=\"#\"";
		}
		else {
			$jobLink_html=$base->UtlObj->returnFormattedData($jobLink,'url','html',&$base);
			$insertHRef="href=\"$jobLink_html\"";
		}
		//xxxp - removed <p $classInsert>...</p>
		$returnAry[]="$bulletInsert<a $insertHRef $targetInsert $idInsert $classInsert $menuElementAltInsert $htmlElementEventAttributes>$label</a>";
		$base->DebugObj->printDebug("-rtn:buildUrl",0); //xx (f)
		return $returnAry;
	}
	//=======================================xx needs to be totally rethought
	function buildDisplay($eleAry,$base){
		$base->DebugObj->printDebug("HtmlObj:buildDisplay($eleAry,'base')",0); //xx (h)
		$returnAry=array();
		$name=$eleAry['htmlelementname'];
		$label=$eleAry['label'];
		$dataName=$eleAry['joblink'];
		$dataNameAry=explode('_',$dataName);
		$dataKeyType=$dataNameAry[0];
		$dataKey=$dataNameAry[1];
		if ($dataKey == NULL){
			$dataKey=$dataName;
			$dataKeyType='old';
		}
		switch ($dataKeyType){
			case 'session':
				$userAry=$_SESSION['userobj']->getCurrentUserAry();
				//$base->DebugObj->printDebug($userAry,1,'usa');//xxx
				//echo "datakey: $dataKey";//xxx
				$dataValue=$userAry[$dataKey];
				break;
			case 'sessionfield':
				$dataValue=$base->UtlObj->retrieveValue($dataKey,&$base);
				break;
			case 'params':
				$dataValue=$base->paramsAry[$dataKey];
				break;
			case 'old':
				$dataValue=$base->UtlObj->retrieveValue($dataKey,&$base);
				if ($dataValue == NULL){
					$dataValue=$base->paramsAry[$dataKey];
				}
				break;
		}
		$class=$eleAry['htmlelementclass'];
		if ($class != ''){$classInsert="class=\"$class\"";}
		else {$classInsert=NULL;}
		$id=$eleAry['htmlelementid'];
		if ($id == NULL){$id=$name;}
		$idInsert="id=\"$id\"";
		$nameInsert="name=\"$name\"";
		$continue=true;
		$displayInsert=$label;
		$cnt=0;
		$displayInsert=str_replace('%'.$dataKey.'%',$dataValue,$displayInsert);
		$displayInsert=$base->UtlObj->returnFormattedString($displayInsert,&$base);// - more than one variable
		$returnAry[]="<div $nameInsert $idInsert $classInsert>$displayInsert</div>\n";
		$base->DebugObj->printDebug("-rtn:buildDisplay",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function buildMap($mapName,$base){
		$base->DebugObj->printDebug("HtmlObj:buildMap($mapName,'base')",0);
		//echo "mapname: $mapName";//xxx
		//$base->DebugObj->printDebug($base->mapProfileAry,1,'mapprofileary');//xxx
		$mapProfileId=$base->mapProfileId[$mapName]['mapprofileid'];
		$mapClass=$base->mapProfileAry['main'][$mapProfileId]['mapclass'];
		if ($mapClass != NULL){$mapClassInsert="class=\"$mapClass\"";}
		else {$mapClassInsert=NULL;}
		$mapId=$base->mapProfileAry['main'][$mapProfileId]['mapid'];
		if ($mapId == NULL){$mapId=$mapName;}
		if ($mapId != NULL){$mapIdInsert="id=\"$mapId\"";}
		else {$mapIdInsert=NULL;}
		$returnAry=array();
		$mapNameInsert="name=\"$mapName\"";
		$returnAry[]="<map $mapNameInsert $mapClassInsert $mapIdInsert>\n";
		$workAry=$base->mapProfileAry[$mapName];
		if (is_array($workAry)){
			foreach ($workAry as $colName=>$colAry){
				$mapElementHref=$colAry['mapelementhref'];
				if ($mapElementHref != NULL && $mapElementHref != '#'){
					$mapElementHref_html=$base->UtlObj->returnFormattedData($mapElementHref,'url','html',&$base);
					$mapElementHrefInsert="href=\"$mapElementHref_html\"";
				}
				else {$mapElementHrefInsert='href="#"';}
				$mapElementShape=$colAry['mapelementshape'];
				$mapElementShapeInsert="shape=\"$mapElementShape\"";
				$mapElementEventAttributes_raw=$colAry['mapelementeventattributes'];
				$mapElementEventAttributes=$base->UtlObj->returnFormattedString($mapElementEventAttributes_raw,&$base);
				$mapElementPointax=$colAry['mapelementpointax'];
				$mapElementPointay=$colAry['mapelementpointay'];
				$mapElementPointbx=$colAry['mapelementpointbx'];
				$mapElementPointby=$colAry['mapelementpointby'];
				$mapElementCoordsInsert="coords=\"$mapElementPointax,$mapElementPointay,$mapElementPointbx,$mapElementPointby\"";
				$returnAry[]="<area $mapElementShapeInsert $mapElementHrefInsert $mapElementEventAttributes $mapElementCoordsInsert>\n";
			}
		}
		$returnAry[]='</map>';
		//$base->DebugObj->printDebug($returnAry,1,'rtnary');//xxx
		$base->DebugObj->printDebug("-rtn:buildMap",0); //xx (f)
		return $returnAry;
	}
	//=======================================
	function buildAlbumTable($albumProfileId,$base){
		$albumAry=$base->albumProfileAry['main'][$albumProfileId];
		$albumName=$albumAry['albumname'];
		$picturesAry=$base->albumProfileAry[$albumName];
		//$base->DebugObj->printDebug($picturesAry,1,'xxxd');
		$albumClass=$albumAry['albumclass'];
		$albumId=$albumAry['albumid'];
		$albumUseThumbNail_raw=$albumAry['albumusethumbnail'];
		$albumUseThumbNail=$base->UtlObj->returnFormattedData($albumUseThumbNail_raw,'boolean','internal');
		$albumDirectory=$albumAry['albumdirectory'];
		$albumEventAttributes_raw=$albumAry['albumeventattributes'];
		$albumAlt=$albumAry['albumalt'];
		if ($albumAlt != null){$albumAltInsert="alt=\"$albumAlt\"";}
		else {$albumAltInsert=null;}
		$albumEventAttributes=$base->UtlObj->returnFormattedString($albumEventAttributes_raw,&$base);
		if ($albumId == NULL){$albumIdInsert=NULL;}
		else {$albumIdInsert="id=\"$albumId\"";}
		$pictureTextSuffix="text";
		$pictureTitleSuffix="title";
		if ($albumClass == NULL){
			$albumClassInsert=NULL;
			$pictureTextClassInsert=NULL;
			$pictureTitleClassInsert=NULL;
		}
		else {
			$albumClassInsert="class=\"$albumClass\"";
			$pictureTextClassInsert="class=\"$albumClass$pictureTextSuffix\"";
			$pictureTitleClassInsert="class=\"$albumClass$pictureTitleSuffix\"";
		}
		$albumColumns=$albumAry['albumcolumns'];
		$albumNameInsert="name=\"$albumName\"";
		$returnAry=array();
		$returnAry[]="\n<!-- menu element albumtable: $albumName -->\n";
		$returnAry[]="<table $albumNameInsert $albumClassInsert $albumIdInsert>\n";
		$colNo=0;
		$rowNo=0;
		$startInsert="<tr>\n";
		//echo "albumname: $albumName<br>";//xxxa
		$noPictures=count($picturesAry);
		if ($noPictures>0){
			$jsAlbumPicturesAry=array();
			$jsAlbumTitlesAry=array();
			$jsAlbumTextAry=array();//xxxdnew
			$jsMediaTypeAry=array();//xxxdnew
			$jsVideoIdAry=array();//xxxdnew
			//!!!xxxd - the below needs to update the arrays by picture order
			$pictureNo=0;
			foreach ($picturesAry as $pictureName=>$pictureAry){
				//echo "pname: $pictureName<br>";//xxxa
				if ($startInsert != NULL){
					$returnAry[]="$startInsert\n";
					$startInsert=NULL;
				}
				$base->paramsAry['pictureno']=$pictureNo;
				$pictureNo++;
				$albumEventAttributes=$base->UtlObj->returnFormattedString($albumEventAttributes_raw,&$base);
				$pictureDirectory=$pictureAry['picturedirectory'];
				if ($pictureDirectory == NULL){$pictureDirectory=$albumDirectory;}
				if ($albumUseThumbNail){$pictureDirectory.="/thumbnails";}
				$pictureFileName=$pictureAry['picturefilename'];
				$imageSource=$pictureDirectory.'/'.$pictureFileName;
				$sourceInsert="src=\"$imageSource\"";
				$pictureTitle=$pictureAry['picturetitle'];
				if ($pictureTitle==NULL){$pictureTitle=$pictureName;}
				if ($pictureTitle == 'none'){$pictureTitle='';}
				$pictureCaption=$pictureAry['picturetext'];
				$mediaType=$pictureAry['mediatype'];
				$videoId=$pictureAry['videoid'];
				$returnAry[]="<td>\n";
				$returnAry[]="<table $albumClassInsert $albumIdInsert $albumEventAttributes>\n<tr><td $pictureTitleClassInsert>\n<p $pictureTitleClassInsert>$pictureTitle</p>\n</td></tr>\n";
				$returnAry[]="<tr><td>\n<img $sourceInsert $albumAltInsert $albumClassInsert $albumIdInsert>\n</td></tr>\n";
				$returnAry[]="<tr><td $pictureTextClassInsert>\n<p $pictureTextClassInsert>$pictureCaption</p>\n</td></tr>\n</table>\n";
				$returnAry[]="</td>\n";
				$colNo++;
				if ($colNo>=$albumColumns){
					$returnAry[]="</tr>\n";
					$startInsert="<tr>";
					$colNo=0;
				} // if colno
				$jsAlbumPicturesAry[]=$imageSource;
				$jsAlbumTitlesAry[]=$pictureTitle;
				$jsAlbumCaptionsAry[]=$pictureCaption;//xxxdnew
				$jsMediaTypeAry[]=$mediaType;//xxxdnew
				$jsVideoIdAry[]=$videoId;//xxxdnew
			} // foreach
		} // if noPictures>0
		if ($startInsert == NULL){$returnAry[]="</tr>\n";}
		$returnAry[]="</table>\n";
		$returnAry[]="<!-- end menu element albumtable: $albumName -->\n";
		$returnAllAry=array('returnary'=>$returnAry);
		$albumPassAry=array();
		//$base->DebugObj->printDebug($jsAlbumPicturesAry,1,'xxxd');//xxxd
		$albumPassAry['jsalbumpicturesary']=$jsAlbumPicturesAry;
		$albumPassAry['jsalbumtitlesary']=$jsAlbumTitlesAry;
		$albumPassAry['jsalbumcaptionsary']=$jsAlbumCaptionsAry;
		$albumPassAry['jsmediatypeary']=$jsMediaTypeAry;//xxxdnew
		$albumPassAry['jsvideoidary']=$jsVideoIdAry;//xxxdnew
		$returnAllAry[$albumName]=$albumPassAry;
		$returnAllAry['albumname']=$albumName;
		//$base->DebugObj->printDebug($returnAry,1,'rtnaryxxxa');
		return $returnAllAry;
	}
	//=======================================
	function buildTableHeaders($tableName,$base){
		$base->DebugObj->printDebug("HtmlObj:buildTableHeaders('base')",0);
		$returnAry=array();
		$rowProfileAry=$base->rowProfileAry[$tableName];
		$tableProfile=$base->tableProfileAry[$tableName];
		$attributes="";
		$borderInt=$tableProfile['border'];
		if ($borderInt != ""){$attributes.=" border=\"$borderInt\"";}
		$cellSpacing=$tableProfile['cellspacing'];
		if ($cellSpacing != ""){$attributes.=" cellspacing=\"$cellSpacing\"";}
		$cellPadding=$tableProfile['cellpadding'];
		if ($cellPadding != ""){$attributes.=" cellpadding=\"$cellPadding\"";}
		$bgColor=$tableProfile['bgcolor'];
		if ($bgColor != ""){$attributes.=" bgcolor=\"$bgColor\"";}
		$align=$tableProfile['align'];
		if ($align != ""){$attributes.=" align=\"$align\"";}
		$jobSt=$base->jobProfileAry['jobstr'];
		$rowNo=0;
		$returnAry[]="<table $attributes>";
		$columnsAry=$base->columnProfileAry["tablename$tableNoInt"];
		$totCols=count($columnsAry);
		//  column heading - 1st column must have a name
		$rowNo=0;
		if ($columnsAry[0]['columnheading'] != ''){
			$rowNo++;
			$bgcolor=$rowProfileAry[$rowNo]['bgcolor'];
			if ($bgcolor != ""){$bgColorLine=" bgcolor=\"$bgcolor\"";}
			else {$bgColorLine="";}
			$color=$rowProfileAry[$rowNo]['color'];
			if ($color != ""){$colorLine=" color=\"$color\"";}
			else {$colorLine="";}
			$returnAry[]="<tr$bgColorLine$colorLine>";
			for ($colCtr=0;$colCtr<$totCols;$colCtr++){
				$columnHeading=$columnsAry[$colCtr]['columnheading'];
				$columnHeadingSpan=$columnsAry[$colCtr]['columnheadingspan'];
				if ($columnHeading != "") {
					if ($columnHeadingSpan > 0){$returnAry[]="<td colspan=$columnHeadingSpan".">";}
					else {$returnAry[]="<td>";}
					$returnAry[]=$columnHeading;
					$returnAry[]="</td>";
				}
			}
			$returnAry[]="</tr>";
		}
		//	column title
		$rowNo++;
		$fontSize=$rowProfileAry[$rowNo]['font'];
		if ($fontSize != ""){$fontLine=" <font size=\"$fontSize\"> ";$fontLineEnd="</font>";}
		else {$fontLine="";$fontLineEnd="";}
		//- bgcolor
		$bgcolor=$rowProfileAry[$rowNo]['bgcolor'];
		if ($bgcolor != ""){$bgColorLine=" bgcolor=\"$bgcolor\"";}
		else {$bgColorLine="";}
		//- color
		$color=$rowProfileAry[$rowNo]['color'];
		if ($color != ""){$colorLine=" color=\"$color\"";}
		else {$colorLine="";}
		//- print tr
		$returnAry[]="<tr$bgColorLine$colorLine>";
		for ($colCtr=0;$colCtr<$totCols;$colCtr++){
			$columnAry=$columnsAry[$colCtr];
			$columnTitleSt=$columnAry['columntitle'];
			$columnName=$columnAry['columnname'];
			$jobOverride=$base->paramsAry['jobstr'];
			if ($jobOverride != "") {$insLine="&jobstr=$jobOverride";}
			else {$insLine="";}
			$returnAry[]="<td>";
			$jobLocal=$base->systemAry['joblocal'];
			$returnAry[]="<a href=\"$jobLocal$jobSt&sort=$columnName$insLine\">";
			$returnAry[]="$fontLine$columnTitleSt$fontLineEnd";
			$returnAry[]="</a>";
			$returnAry[]="</td>";
		}
		$returnAry[]="</tr>";
		return $returnAry;
	}
	//=======================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
	//=======================================
	function incCalls(){$this->callNo++;}
}
?>
