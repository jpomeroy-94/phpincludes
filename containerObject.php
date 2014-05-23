<?php
class ContainerObject {
	var $statusMsg;
	var $callNo = 0;
	var $base;
	var $containerJsAry=array();
	var $containerProfileAry = array();
	var $containerElementProfileAry = array();
//====================================
	function ContainerObject() {
		$this->incCalls();
		$this->statusMsg='container Object is fired up and ready for work!';
	}
//------------------------------------
	function initContainer($base){
		$this->base=&$base;
		$this->containerJsAry[]="var ContainerObj = new ContainerObject();\n";
		$this->storeContainerData(&$base);		
	}
//====================================
	function getContainerJs($base){
		return $this->containerJsAry;	
	}
//====================================
	function storeContainerData($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxx');
		$job=$base->paramsAry['job'];
		if ($job == NULL){$job='main';}
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from containerprofileview where jobprofileid=$jobProfileId";
		$passAry=array('delimit1'=>'containername');
		$this->containerProfileAry=$base->DbObj->getSqlDbAry($query,$passAry,&$base);
		$query="select * from containerelementprofileview where jobprofileid=$jobProfileId order by containerelementno";
		$passAry=array('delimit1'=>'containername','delimit2'=>'containerelementname');
		$this->containerElementProfileAry=$base->DbObj->getSqlDbAry($query,$passAry,&$base);
	}
//====================================
	function getContainer($name,$base){
		if ($name==''){
			echo "ContainerObj.getContainer: no container name: $name !!!";
			$base->DebugObj->displayStack();
			exit();
		}
		if (array_key_exists($name,$this->containerProfileAry)){
			$returnContainerAry=$this->containerProfileAry[$name];
			$returnContainerElementAry=$this->containerElementProfileAry[$name];
			$returnAry=array('containerary'=>$returnContainerAry,'containerelementary'=>$returnContainerElementAry);	
		}
		else {$returnAry=array();}
		return $returnAry;
	}
//====================================
	function insertContainerHtml($containerName,$base){
		//echo "call to insert container html: $containerName<br>";//xxxd
		$ctr=0;
		$contentAry=array();
		$headerAry=array();
		$footerAry=array();
		//echo "$containerName<br>";//xxx
		//$base->DebugObj->printDebug($this->containerProfileAry,1,'xxx');
//- container
		$containerAry=$this->containerProfileAry[$containerName];
//- old containershow only used if containerformat is null
		$oldContainerShow_raw=$containerAry['containershow'];
		$oldContainerShow=$base->UtlObj->returnFormattedData($oldContainerShow_raw,'boolean','internal');
//- container divider
		$containerDividerShow_raw=$containerAry['containerdividershow'];
		$containerDividerShow=$base->UtlObj->returnFormattedData($containerDividerShow_raw,'boolean','internal');
//- container id
		$containerId=$containerAry['containerid'];
		if ($containerId != NULL){$containerIdInsert="id=\"$containerId\"";}
		else {$containerIdInsert=NULL;}
//- container class
		$containerClass=$containerAry['containerclass'];
		if ($containerClass != NULL){$containerClassInsert="class=\"$containerClass\"";}
		else {$containerClassInsert=NULL;}
//- container style sheet
		$containerStyleSheetNo=$containerAry['stylesheetno'];
		if ($containerStyleSheetNo==null){$containerStyleSheetNo=0;}
//- container event
		$containerEvent_raw=$containerAry['containerevent'];
		if ($containerEvent_raw == null){$containerEvent=null;}
		else {
			$containerEvent=$base->UtlObj->returnFormattedString($containerEvent_raw,&$base);
		}
//- container header/footer format
		$containerHeaderFooterFormat=$containerAry['containerheaderfooterformat'];
		switch ($containerHeaderFooterFormat){
			case 'doubleheaderfooter':
				$dblHeader=true;
				$dblFooter=true;
				break;
			case 'doubleheader':
				$dblHeader=true;
				$dblFooter=false;
				break;
			case 'doublefooter':
				$dblHeader=false;
				$dblFooter=true;
				break;
			default:
				$dblHeader=false;
				$dblFooter=false;
		}
//- container format
		$containerFormat=$containerAry['containerformat'];
		if ($containerFormat==null){
			if ($oldContainerShow){
				$containerFormat='containerheadercontentfooter';
			}
			else {
				$containerFormat='nocontainer';
			}
		}
		//echo "name: $containerName, format: $containerFormat<br>";//xxxd
		switch ($containerFormat){
			case 'containerheadercontentfooter':
				$containerShow=true;
				$contentShow=true;
				$headerShow=true;
				$footerShow=true;
			break;
			case 'containerheaderdetailfooter':
				$containerShow=true;
				$contentShow=false;
				$headerShow=true;
				$footerShow=true;
			break;
			case 'containerheaderdetail':
				$containerShow=true;
				$contentShow=false;
				$headerShow=true;
				$footerShow=false;
			break;
			case 'containerdetail':
				$containerShow=true;
				$contentShow=false;
				$headerShow=false;
				$footerShow=false;
			break;
			case 'nocontainer':
				$containerShow=false;
				$contentShow=false;
				$headerShow=false;
				$footerShow=false;
			break;
			default:
				exit('invalid container format: '.$containerFormat);	
		}
//- get div/span
		$containerHeaderFooterType=$containerAry['containerheaderfootertype'];
		if ($containerHeaderFooterType=='span'){$headerFooterDelim='span';}
		else {$headerFooterDelim='div';}
//- init
		$headerAry=array();
		$footerAry=array();
		$contentAry=array();
		//xxxd - need to send style sheet here
		if ($containerShow){
			$dividerMainBegin="\n<!-- begin container: $containerName -->\n";
			$dividerMainBegin.="<div $containerClassInsert $containerIdInsert $containerEvent>\n";
			$dividerMainEnd="</div>\n<!-- end container: $containerName -->\n";
		}
		else {$dividerMainBegin=null;$dividerMainEnd=null;}
//- container header
		$headerClass=$containerAry['containerheaderclass'];
		if ($headerClass != NULL){
			$headerClassInsert="class=\"$headerClass\"";
			$headerClassInsertMain="class=\"$headerClass".'main'."\"";
			$headerClassInsertLeft="class=\"$headerClass".'left'."\"";
			$headerClassInsertRight="class=\"$headerClass".'right'."\"";
		}
		else {$headerClassInsert=NULL;$headerClassInsertMain=null;$headerClassInsertLeft=null;$headerClassInsertRight=null;}
		$headerId=$containerAry['containerheaderid'];
		if ($headerId != NULL){$headerIdInsert="id=\"$headerId\"";}
		else {$headerIdInsert=NULL;}
		$headerEventInsert=$containerAry['containerheaderevent'];
		$headerEventInsert=$base->UtlObj->returnFormattedString($headerEventInsert,&$base);
		//xxxdf
		if ($dblHeader){
			$dividerHeaderBegin="\n<!-- begin header: $containerName -->\n";
			$dividerHeaderBegin.="<div $headerClassInsertMain>\n";
			$dividerHeaderBegin.="<$headerFooterDelim $headerClassInsertLeft></$headerFooterDelim>";
			$dividerHeaderBegin.="<$headerFooterDelim $headerIdInsert $headerClassInsert $headerEventInsert>";
			$dividerHeaderEnd="</$headerFooterDelim><$headerFooterDelim $headerClassInsertRight></$headerFooterDelim>\n";
			$dividerHeaderEnd.="</div>\n<!-- end header $containerName -->\n";
		}
		else {
			$dividerHeaderEnd="</div>\n<!-- end header $containerName -->\n";
			$dividerHeaderBegin="\n<!-- begin header: $containerName -->\n<div $headerIdInsert $headerClassInsert $headerEventInsert>\n";
		}
		if ($headerShow){
			$headerAry[]=$dividerHeaderBegin;
		}
		//xxxd - not sure if below should be put in as else
		//$headerAry[]=$dividerHeaderBeginComment
//- container footer
//xxxd - need to put in event
		$footerClass=$containerAry['containerfooterclass'];
		if ($footerClass != NULL){$footerClassInsert="class=\"$footerClass\"";}
		else {$footerClassInsert=NULL;}
		$footerId=$containerAry['containerfooterid'];
		if ($footerId != NULL){$footerIdInsert="id=\"$footerId\"";}
		else {$footerIdInsert=NULL;}
		$dividerFooterBeginComment="\n<!-- begin footer: $containerName -->\n";
		$dividerFooterBegin=$dividerFooterBeginComment."<div $footerIdInsert $footerClassInsert >\n";
		$dividerFooterEnd="</div >\n";
		$dividerFooterEndComment="<!-- end footer: $containerName -->\n";
		$dividerFooterEnd.=$dividerFooterEndComment;
		if ($footerShow){
			//echo "footershow: $footerShow for $containerName<br>";//xxxd
			$footerAry[]=$dividerFooterBegin;
		}
		//xxxd - not sure if below should be put in as else
		//$footerAry[]=$dividerFooterEndComment
//- container content
//xxxd - need to put in event(maybe)
		$contentClass=$containerAry['containercontentclass'];
		if ($contentClass != NULL){$contentClassInsert="class=\"$contentClass\"";}
		else {$contentClassInsert=NULL;}
		$contentId=$containerAry['containercontentid'];
		if ($contentId != NULL){$contentIdInsert="id=\"$contentId\"";}
		else {$contentIdInsert=NULL;}
		$dividerContentBeginComment="\n<!-- begin content: $containerName -->\n";
		$dividerContentBegin=$dividerContentBeginComment."<div $contentIdInsert $contentClassInsert  >\n";
		$dividerContentEnd="\n</div  >";
		$dividerContentEndComment="\n<!-- end content: $containerName -->";
		$dividerContentEnd.=$dividerContentEndComment;
		//echo "containername: $containerName, contentshow: $contentShow";//xxxf
		if ($contentShow){
			$contentAry[]=$dividerContentBegin;
		}
		//xxxd - not sure if below should be put in as else
		//$contentAry[]=$dividerContentBeginComment;	
//======================================= container elements
//$base->DebugObj->printDebug($this->containerElementProfileAry[$containerName],1,'xxxd');//xxxd
		$theCount=count($this->containerElementProfileAry[$containerName]);
		if ($theCount>0){
		foreach ($this->containerElementProfileAry[$containerName] as $elementName=>$containerElementAry){
		$containerElementName=$containerElementAry['containerelementname'];
		$containerElementType=$containerElementAry['containerelementtype'];
		$containerElementClass=$containerElementAry['containerelementclass'];
		$containerElementId=$containerElementAry['containerelementid'];
		$containerElementSeparator=$containerElementAry['containerelementseparator'];
		$containerElementLocation=$containerElementAry['containerelementlocation'];
		$containerElementEvent=$containerElementAry['containerelementevent'];
		if ($containerElementLocation=='content' || $containerElementLocation==''){$inContent=true;}
		else {
			$inContent=false;
			if ($containerElementLocation=='header'){$inHeader=true;$inFooter=false;}
			else {$inHeader=false;$inFooter=true;}
		}
		//echo "cname: $containerName, cename: $containerElementName, h: $inHeader, c: $inContent, f: $inFooter<br>";//xxxd
		switch ($containerElementSeparator){
			case 'div':
				$separator='div';
				break;
			case 'span':
				$separator='span';
				break;
			case 'none':
				$separator=null;
				break;
			default:
				$separator='div';
		}
		if ($containerElementClass != NULL){$classInsert="class=\"$containerElementClass\"";}
		else {$classInsert=NULL;}
		if ($containerElementId != NULL){
			$idInsert="id=\"$containerElementId\"";
			$idInsertTd="id=\"$containerElementId".'_td'."\"";
		}
		else {$idInsert=NULL;$idInsertTd=NULL;}
		//echo "idinsert: $idInsert, idinsert_td: $idInsertTd<br>";//xxxd
		if ($containerDividerShow){
			$dividerBegin="\n<!-- begin divider: $containerName($elementName) -->\n<$separator $classInsert $idInsert  >\n";
			$dividerEnd="\n</$separator  >\n<!-- end divider: $containerName($elementName) -->\n";
		}
		else {
			$dividerBegin="\n<!-- begin divider: $containerName($elementName) -->\n";
			$dividerEnd="\n<!-- end divider: $containerName($elementName) -->\n";
		}
		//echo "ContainerObj xxxd3: name: $containerName, elename: $containerElementName, containeelementtype: $containerElementType<br>";//xxxd
		$base->FileObj->writeLog('debug1',"name: $containerName, elename: $containerElementName, containeelementtype: $containerElementType",&$base);
		//echo "****************container name: $containerName, ele name: $containerElementName, type: $containerElementType************<br>";//xxxf24
		//exit();//xxxd
		//$theCnt=count($contentAry);echo "cnt: $theCnt<br>";
		switch ($containerElementType){
			case 'tag':
				/*
				//$base->DebugObj->printDebug($columnAry,1,'tag');//xxx
				$jobLink=$columnAry['joblink'];
				$jobLinkAry=explode('_',$jobLink);
				$pluginName=$jobLinkAry[0];
				$runName=$jobLinkAry[1];
				$htmlLine="!!$pluginName_$runName!!";
				$paramFeed['param_1']=$runName;
				$paramFeed['param_2']=$htmlLine;
				$paramFeed['param_3']=1;
				//echo "$pluginName, $runName, $htmlLine<br>";//xxx
				if ($pluginName != ""){
				$subReturnAry=$base->PluginObj->runTagPlugin($pluginName,$paramFeed,&$base);
				} else {$returnAry=array();}
				$returnAry[]=$dividerBegin;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$dividerEnd;
				//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxx
				//exit();
				 */
				break;
			case 'operation':
					$operationAry=array('operationname'=>'runoperation','pluginname'=>$containerElementName);
					$base->PluginObj->runOperationPlugin($operationAry,&$base);
				break;
			case 'para':
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->Plugin002Obj->insertParagraph($paramFeed,&$base);
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else if ($inHeader){$headerAry=$this->arrayMerge($headerAry,$subReturnAry);}
				else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
				break;
//- can go into header
			case 'htmlele':
				$htmlLine="!!inserthtmlelement_$containerElementName!!";
				$paramFeed=array('param_1'=>$containerElementName,'param_2'=>$htmlLine);
				$subReturnAry=$base->TagObj->insertHtmlElement($paramFeed,&$base);
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else if ($inHeader){$headerAry=$this->arrayMerge($headerAry,$subReturnAry);}
				else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
				break;
			case 'form':
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				//$base->DebugObj->printDebug($subReturnAry,1,'xxxd');
				//exit();//xxxd
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else if ($inHeader){$headerAry=$this->arrayMerge($headerAry,$subReturnAry);}
				else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
				break;
			case 'table':
				$containerElementNameAry=explode('_',$containerElementName);
				if ($containerElementNameAry[1] != ''){
					$base->paramsAry[$containerElementNameAry[1]]=$containerElementNameAry[2];
					$containerElementName=$containerElementNameAry[0];
				}
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->TagObj->insertTable($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				//$base->DebugObj->printDebug($contentAry,1,'xxxd');
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'noshowtable':
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->TagObj->insertTable($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				//- do not show table: $contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'stylesheet':
				$jobLink=$containerElementName;
				$elementAry=array('joblink'=>$jobLink);
				$subReturnAry=$base->HtmlObj->buildCssLink($elementAry,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'img':
				//echo "containername: $containerName, $containerElementName<br>";//xxx
				//- can go into header/content/footer
				$paramFeed=array('param_1'=>$containerElementName);
				$paramFeed['events']=$containerElementEvent;
				//echo "runname: $containerElementType<br>";//xxx
				//$base->DebugObj->printDebug($paramFeed,1,'xxxd');
				$subReturnAry=$base->Plugin002Obj->insertImg($paramFeed,&$base);
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else if ($inHeader){
					//??? $dmyWork=str_replace("\n",'',$subReturnAry[0]);
					//??? $subReturnAry[0]=$dmyWork;
					$headerAry=$this->arrayMerge($headerAry,$subReturnAry);
				}
				else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
				//$base->DebugObj->printDebug($contentAry,1,'contentinimagexxxd');
				break;
//- can go into header
			case 'menu':
				//echo "cont: $containerName, menu element name: $containerElementName<br>";//xxxd
				$paramFeed=array('param_1'=>$containerElementName);
				//$subReturnAry=array();
				//$subReturnAry=$base->Plugin002Obj->insertMenu($paramFeed,&$base);
				$subReturnAry=$base->MenuObj->insertMenu($paramFeed,&$base);
				//echo "incontent: $inContent, inheader: $inHeader, infooter: $inFooter";//xxxd
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$cnt=count($subReturnAry);
					if ($cnt>0){
						$contentAry=$this->arrayMerge($contentAry,$subReturnAry);	
					}			
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else {
					if ($inHeader){$headerAry=$this->arrayMerge($headerAry,$subReturnAry);}
					else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
				}
				//$base->DebugObj->printDebug($subReturnAry,1,'xxxd');
				//exit();//xxxd
				//echo 'xxxd1: done with menu';
				break;
			case 'initjs':
				//echo "containerelementtype: $containerElementType, name: $containerElementName<br>";//xxxd
				$containerElementNameAry=explode('_',$containerElementName);
				$containerElementName=$containerElementNameAry[0];
				$containerElementNameType=$containerElementNameAry[1];				
				$paramFeed=array('param_1'=>$containerElementName.'_'.$containerElementNameType);
				//$subReturnAry=array();
				$subReturnAry=$base->TagObj->insertDbTableInitJs($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'insertjavascriptinit':
				$containerElementNameAry=explode('_',$containerElementName);
				$containerElementName=$containerElementNameAry[0];
				$containerElementNameType=$containerElementNameAry[1];				
				$paramFeed=array();
				//$subReturnAry=array();
				$subReturnAry=$base->PluginObj->runTagPlugin('insertjavascriptinit',$paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}				
				break;
			case 'gettableforajax':
				//- need to make this work as plugin
				$containerElementNameAry=explode('_',$containerElementName);
				$containerElementName=$containerElementNameAry[0];
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->TableObj->getTableForAjax($paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			case 'getcssforajax':
				$containerElementNameAry=explode('_',$containerElementName);
				$paramFeed=array('param_1'=>$containerElementNameAry[0],'param_2'=>$containerElementNameAry[1]);
				$subReturnAry=$base->PluginObj->runTagPlugin($containerElementType,$paramFeed,&$base);
				$noCnt=count($subReturnAry);
				if ($noCnt>0){
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				}
				break;
			case 'getcalendarforajax':
				$containerElementNameAry=explode('_',$containerElementName);
				$paramFeed=array('param_1'=>$containerElementNameAry[0]);
				$subReturnAry=$base->PluginObj->runTagPlugin('getcalendarforajax',$paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			case 'copytabletocalendar':
				$containerElementNameAry=explode('_',$containerElementName);
				$tableToCopy=$containerElementNameAry[0];
				$contentAry[]='!!copytabletocalendar!!'."\n";
				$contentAry[]='tablename|'.$tableToCopy."\n";
				break;
			case 'loadformsetups':
				$paramFeed=array();
				$base->FormObj->loadFormSetups(&$base);
				//exit();
				break;
			case 'getformforajax':
				//- need to run this like plugin
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->FormObj->getFormForAjax($paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				//exit();
				break;
			case 'getmenuforajax':
				//- need to run this like plugin
				$paramFeed=array('param_1'=>$containerElementName);
				$subReturnAry=$base->MenuObj->getMenuForAjax($paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			case 'buildjsalbums':
				$paramFeed=array('param_1'=>$containerElementType);
				//$subReturnAry=array();
				$subReturnAry=$base->TagPlugin001Obj->buildJsAlbums($paramFeed,&$base);
				if (count($subReturnAry)>0){
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				}
				break;
			case 'loadformfragments':
				$paramFeed=array();
				$base->TagPlugin001Obj->loadFormFragments(&$base);
				break;			
			case 'title':
				$paramFeed=array('param_2'=>'!!inserttitle!!');
				$subReturnAry=$base->TagObj->insertTitle($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'style':
				$paraFeed=array();			
				$subReturnAry=$base->Plugin002Obj->insertStyle($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'container':
				$paramFeed=array();
				$paramFeed['param_1']=$containerElementName;			
				$subReturnAry=$base->TagPlugin001Obj->insertContainer($paramFeed,&$base);
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'errormessage':
				$errorMessageName=$containerElementName;
				$errorMessage_raw=$base->errorProfileAry[$errorMessageName];
				$errorMessage=$base->UtlObj->returnFormattedString($errorMessage_raw,&$base);
				if ($errorMessage_raw == null|| $errorMessage_raw == ''){
					$errorMessage_raw=$base->ErrorObj->retrieveError($errorMessageName,&$base);	
					//xxxd - below seems to lose data going from raw to formatted
					$errorMessage=$base->UtlObj->returnFormattedString($errorMessage_raw,&$base);
				}
				//echo "errormessageName: $containerElementName, errmsgraw: $errorMessage_raw, errmsg: $errorMessage<br>";//xxxd
				//$base->DebugObj->printDebug($base->errorProfileAry,1,'xxx');
				$theJob=$base->paramsAry['job'];
				if ($theJob=='copyjobs'){$spacer=null;}
				else {$spacer='&nbsp;';}				
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$contentAry[]="<table border=0 $classInsert $idInsert><tr><td $classInsert>";
				if ($errorMessage== null){$errorMessage=$spacer;}
				$contentAry[]=$errorMessage;
				$contentAry[]="</td></tr><tr>$spacer<td></td></tr><tr><td>$spacer</td></tr><tr><td>$spacer</td></tr><tr><td>$spacer</td></tr></table>";
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'url':
				$htmlElementAry=$base->htmlElementProfileAry[$containerElementName];
				$workAry=$base->HtmlObj->buildUrl($htmlElementAry,&$base);
				$contentAry=$this->arrayMerge($contentAry,$workAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'calendar':
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$subReturnAry=$base->CalendarObj->insertCalendarHtml($containerElementType,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}	
				break;
			case 'calendarv2':
				if ($containerShow){$contentAry[]=$dividerBegin;}
				$subReturnAry=$base->CalendarObj->insertCalendarHtmlV2($containerElementName,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				if ($containerShow){$contentAry[]=$dividerEnd;}
				break;
			case 'repeatingform':
				$subReturnAry=$this->insertRepeatingCategoryOld($containerElementName,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			case 'map':
				$paramFeed=array();
				$paramFeed['param_1']=$containerElementName;			
				$subReturnAry=$base->Plugin002Obj->insertMap($paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			case 'startautorotate':
				$paramFeed=array();
				$paramFeed['param_1']=$containerElementName;
				$subReturnAry=$base->TagPlugin001Obj->startAutoRotate($paramFeed,&$base);
				$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
				break;
			default:
				$paramFeed=array('param_1'=>$containerElementName);
				if ($containerElementEvent != ''){$paramFeed['event_1']=$containerElementEvent;}
				$subReturnAry=$base->PluginObj->runTagPlugin($containerElementType,$paramFeed,&$base);
				if ($inContent){
					if ($containerShow){$contentAry[]=$dividerBegin;}
					$contentAry=$this->arrayMerge($contentAry,$subReturnAry);
					if ($containerShow){$contentAry[]=$dividerEnd;}
				}
				else if ($inHeader){
					$dmyWork=str_replace("\n",'',$subReturnAry[0]);
					$subReturnAry[0]=$dmyWork;
					$headerAry=$this->arrayMerge($headerAry,$subReturnAry);
				}
				else {$footerAry=$this->arrayMerge($footerAry,$subReturnAry);}
			} // end switch containerelementtype
			$base->FileObj->writeLog('debug1',"containerobj: done with container element",&$base);
		} // end foreach containerele
		} // end if count>0
		if ($containerShow){
			if ($contentShow){$contentAry[]=$dividerContentEnd;}
			if ($footerShow){
				$footerAry[]=$dividerFooterEnd;	
			}
		}
//- put it all together
		if ($headerShow){
			$headerAry[]=$dividerHeaderEnd;
		}
		$returnAry=array();
//--- end changes
		$returnAry[]=$dividerMainBegin;
		$returnAry=$this->arrayMerge($returnAry,$headerAry);
		$returnAry=$this->arrayMerge($returnAry,$contentAry);
		$returnAry=$this->arrayMerge($returnAry,$footerAry);
		$returnAry[]=$dividerMainEnd;
	return $returnAry;
}
//=====================================
	function insertRepeatingCategoryOld($containerElementName,$base){
		//$base->DebugObj->printDebug($this->containerElementProfileAry,1,'xxxd');exit();//xxxd
		$returnAry=array();
		$formNameAry=array();
		foreach ($this->containerElementProfileAry['cssforms'] as $formName=>$dmyAry){
			$formNameAry[]=$formName;
		}
		$query_raw="select * from csselementprofileview where jobprofileid=%jobprofileid% order by prefix, cssclass, cssid, htmltag, csselementproperty";	
		$query=$base->UtlObj->returnFormattedString($query_raw,&$base);
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($this->containerElementProfileAry,1,'xxxd');
		$oldPrefix=NULL;$oldClass=NULL;$oldId=NULL;$oldHtmlTag=NULL;
		$paramFeed=array('param_1'=>$formNameAry[0]);
		$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
		$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
		$ctr=0;
		foreach ($dataAry as $ctr=>$cssElementAry){
			$base->paramsAry['ctr']=$ctr;
			$ctr++;
			$prefix=$cssElementAry['prefix'];if ($prefix==NULL){$prefix='none';}
			$theClass=$cssElementAry['cssclass'];if ($theClass==NULL){$theClass='none';}
			$theId=$cssElementAry['cssid'];if ($theId==NULL){$theId='none';}
			$htmlTag=$csELementAry['htmltag'];if ($htmlTag==NULL){$htmlTag='none';}
			$theProperty=$cssElementAry['csselementproperty'];
			$theValue=$cssElementAry['csselementvalue'];
			if ($prefix != $oldPrefix || $theClass != $oldClass || $theId != $oldId || $htmlTag != $oldHtmlTag){
				$sD1="<span class=\"smallFont\">";
				$de="</span>";
				$sD2="<span class=\"largeFont\"";
				$returnAry[]= "<div class=\"styleitem\">$sD1 prefix: $de $prefix   $sD1 class: $de $theClass     $sD1 id: $de $theId    $sD1 tag: $de $htmlTag</div><br>";
				$paramFeed=array('param_1'=>$formNameAry[1]);
				$paramFeed['usethisdataary']=$cssElementAry;
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
				//echo "insert csselementitem: $prefix, $theClass, $theId, $htmlTag<br>";
				$paramFeed=array('param_1'=>$formNameAry[2]);
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
			}
			//echo "$prefix, $theClass, $theId, $htmlTag: update $theProperty, $theValue<br>";
			$paramFeed=array('param_1'=>$formNameAry[3]);
			$paramFeed['usethisdataary']=$cssElementAry;
			$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
			$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
			$oldPrefix=$prefix;$oldClass=$theClass;$oldId=$theId;$oldHtmlTag=$htmlTag;
		}
		return $returnAry;
	}
//=====================================
	function insertRepeatingCategory($containerElementName,$base){
		//xxxd - add containerelementprofile, containerelementchildcontainer
//- init
		$returnAry=array();
		$formNameAry=array();
		$containerWithFormNames=$this->containerElementProfileAry['containerelementchildcontainer'];
		$query_raw=$this->containerElementProfileAry['containerelementsql'];
		$query_constraint_name=$this->containerElementProfileAry['containerelementsqlconstraint'];
		$queryConstraintNamesAry=explode('_',$queryConstraintName);
		$queryConstraintValuesAry=array();
		foreach ($queryConstraintNamesAry as $ctr=>$constraintName){
			$queryConstraintValue=$base->paramsAry[$constraintName];
			if ($queryConstraintValue != null){
				$queryConstraintValuesAry[$constraintName]=$queryConstraintValue;
			}
		}
//-
		foreach ($this->containerElementProfileAry[$containerWithFormNames] as $formName=>$dmyAry){
			$formNameAry[]=$formName;
		}
		//$query_raw="select * from csselementprofileview where jobprofileid=%jobprofileid% order by prefix, cssclass, cssid, htmltag, csselementproperty";	
		$query=$base->UtlObj->returnFormattedString($query_raw,&$base);
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($this->containerElementProfileAry,1,'xxxd');
		$oldPrefix=NULL;$oldClass=NULL;$oldId=NULL;$oldHtmlTag=NULL;
		$paramFeed=array('param_1'=>$formNameAry[0]);
		$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
		$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
		$ctr=0;
		foreach ($dataAry as $ctr=>$cssElementAry){
			$base->paramsAry['ctr']=$ctr;
			$ctr++;
			$prefix=$cssElementAry['prefix'];if ($prefix==NULL){$prefix='none';}
			$theClass=$cssElementAry['cssclass'];if ($theClass==NULL){$theClass='none';}
			$theId=$cssElementAry['cssid'];if ($theId==NULL){$theId='none';}
			$htmlTag=$csELementAry['htmltag'];if ($htmlTag==NULL){$htmlTag='none';}
			$theProperty=$cssElementAry['csselementproperty'];
			$theValue=$cssElementAry['csselementvalue'];
			if ($prefix != $oldPrefix || $theClass != $oldClass || $theId != $oldId || $htmlTag != $oldHtmlTag){
				$sD1="<span class=\"smallFont\">";
				$de="</span>";
				$sD2="<span class=\"largeFont\"";
				$returnAry[]= "<div class=\"styleitem\">$sD1 prefix: $de $prefix   $sD1 class: $de $theClass     $sD1 id: $de $theId    $sD1 tag: $de $htmlTag</div><br>";
				$paramFeed=array('param_1'=>$formNameAry[1]);
				$paramFeed['usethisdataary']=$cssElementAry;
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
				//echo "insert csselementitem: $prefix, $theClass, $theId, $htmlTag<br>";
				$paramFeed=array('param_1'=>$formNameAry[2]);
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
			}
			//echo "$prefix, $theClass, $theId, $htmlTag: update $theProperty, $theValue<br>";
			$paramFeed=array('param_1'=>$formNameAry[3]);
			$paramFeed['usethisdataary']=$cssElementAry;
			$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
			$returnAry=$this->arrayMerge($returnAry,$subReturnAry);
			$oldPrefix=$prefix;$oldClass=$theClass;$oldId=$theId;$oldHtmlTag=$htmlTag;
		}
		return $returnAry;
	}
//=====================================
	function arrayMerge($mainAry,$subAry){
		if (is_array($subAry)){
			$mainAry=array_merge($mainAry,$subAry);
		}
		return $mainAry;
	}
//=====================================
	function incCalls(){
		$this->callNo++;
	}
}
?>
