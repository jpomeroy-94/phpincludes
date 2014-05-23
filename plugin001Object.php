<?php
class Plugin001Object {
	var $statusMsg;
	var $callNo = 0;
	var $delim = '!!';
//========================================
	function Plugin001Object() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
//========================================
	function batchImages($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'paramsary');//xxx
		$imageBase="/usr/local/www/jeffreypomeroy.com/www/images";
		set_time_limit(6000);
//- from
		$fromDirectory_raw=$base->paramsAry['fromdirectory'];
		$pos=strpos($fromDirectory_raw,'jeffrey',0);
		if ($pos<=0){$fromDirectory="$imageBase/$fromDirectory_raw";}
		else {$fromDirectory=$fromDirectory_raw;}
//- to
		$toDirectory_raw=$base->paramsAry['todirectory'];
		$pos=strpos($toDirectory_raw,'jeffrey',0);
		if ($pos<=0){$toDirectory="$imageBase/$toDirectory_raw";}
		else {$toDirectory=$toDirectory_raw;}
//- backup
		$doBackup_raw=$base->paramsAry['copytoraw'];
		$doBackup=$base->UtlObj->returnFormattedData($doBackup_raw,'boolean','internal',&$base);
		$msgLine="<pre>\n";
//- copy backup over
		if ($doBackup){
			$bashCmd="cp $fromDirectory/* $toDirectory/raw";
			$passAry=array('pluginargs'=>$bashCmd);
			$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
			$bashCmd="chmod 777 $toDirectory/raw/*";
			$passAry=array('pluginargs'=>$bashCmd);
			$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
			$msgLine.="I. Copied $fromDirectory_raw to $toDirectory_raw/raw\n\n";
		}
//- image params
    	$imageWidth=$base->paramsAry['imagewidth'];
    	$imageHeight=$base->paramsAry['imageheight'];
//- thumbnail info
		$createThumbnail_raw=$base->paramsAry['createthumbnail'];
		$createThumbnail=$base->UtlObj->returnFormattedData($createThumbnail_raw,'boolean','internal',&$base);
		$imageThumbnailWidth=$base->paramsAry['thumbnailwidth'];
		$imageThumbnailHeight=$base->paramsAry['thumbnailheight'];
//- process image/thumbnail
    	$returnMsg=NULL;
		$msgLine.="II. Converted all images in $fromDirectory_raw to $toDirectory_raw";
		if ($createThumbnail){
			$msgLine.="<br>&nbsp;&nbsp;&nbsp; and to $toDirectory_raw".'/thumbnails'."\n\n";
		}
		else {$msgLine.="\n\n";}
   		$msgLine.="                          ------old------- ------new----- ----thumbnail----\n";
   		$msgLine.="name                       height   width  height   width    height   width\n";
    	if(is_dir($toDirectory)){
      		$dir = opendir($fromDirectory);
			$totTime=0;
      		while (($imageName = readdir($dir)) !== false){
        		if ($imageName != '.' && $imageName != '..'){
       				$imagePath="$fromDirectory/$imageName";
        			$toPath="$toDirectory/$imageName";
        			$toThumbnailPath="$toDirectory/thumbnails/$imageName";
            		$imageWork = new Imagick($imagePath);
            		if ($createThumbnail){$imageThumbnailWork = new Imagick($imagePath);}
            		$oldImageWidth=$imageWork->getImageWidth();
            		$oldImageHeight=$imageWork->getImageHeight();
            		//???$base->errorProfileAry['standardmessage1'];
					$imageName_pr=substr($imageName."                      ",0,25);
					$theLen=strlen($oldImageHeight);
					$theAdj=7-$theLen;
					$oldImageHeight_pr=substr("         ",0,$theAdj).$oldImageHeight;
					$theLen=strlen($oldImageWidth);
					$theAdj=7-$theLen;
					$oldImageWidth_pr=substr("         ",0,$theAdj).$oldImageWidth;
            		$msgLine.="$imageName_pr $oldImageHeight_pr $oldImageWidth_pr";
//- convert image and thumbnail
					if (($imageWidth<$oldImageWidth || $oldImageWidth==0) && ($imageHeight<$oldImageHeight || $imageHeight==0)){
           				$imageWork->thumbnailImage($imageWidth, $imageHeight);
						$endingMsg=NULL;
					} 
					else {$endingMsg=" unchanged";}
					if ($createThumbnail){
						if (($imageThumbnailWidth<$oldImageWidth || $imageThumbnailWidth==0) && ($imageThumbnailHeight<$oldImageHeight || $imageThumbnailHeight==0)){
           					$imageThumbnailWork->thumbnailImage($imageThumbnailWidth, $imageThumbnailHeight);
							$thumbnailEndingMsg=NULL;
						} 
						else {$thumbnailEndingMsg=" unchanged";}
					}
//- get new widths/heights and display
            		$newImageWidth=$imageWork->getImageWidth();
            		$newImageHeight=$imageWork->getImageHeight();
            		if ($createThumbnail){
	            		$newImageThumbnailWidth=$imageThumbnailWork->getImageWidth();
    	        		$newImageThumbnailHeight=$imageThumbnailWork->getImageHeight();
            		}
//- build report
//- width
					$theLen=strlen($newImageWidth);
					$theAdj=7-$theLen;
					$newImageWidth_pr=substr("         ",0,$theAdj).$newImageWidth;
//- height
					$theLen=strlen($newImageHeight);
					$theAdj=7-$theLen;
					$newImageHeight_pr=substr("         ",0,$theAdj).$newImageHeight;
					if ($createThumbnail){
//- thumbnail width
						$theLen=strlen($newImageThumbnailWidth);
						$theAdj=7-$theLen;
						$newImageThumbnailWidth_pr=substr("         ",0,$theAdj).$newImageThumbnailWidth;
//- thumbnail height
						$theLen=strlen($newImageThumbnailHeight);
						$theAdj=7-$theLen;
						$newImageThumbnailHeight_pr=substr("         ",0,$theAdj).$newImageThumbnailHeight;
					}
					else {$newImageThumbnailWidth_pr=NULL; $newImageThumbnailHeight_pr=NULL;$endingThumbnailMsg=NULL;}
            		$msgLine.=" $newImageHeight_pr $newImageWidth_pr  $endingMsg $newImageThumbnailHeight_pr $newImageThumbnailWidth_pr $endingThumbnailMsg\n";
            		$imageWork->writeImage($toPath);
	           		$imageWork->destroy();
	           		if ($createThumbnail){
		           		$imageThumbnailWork->writeImage($toThumbnailPath);
		           		$imageThumbnailWork->destroy();
	           		}
          		} // end if imagename
      		} // end while readdir
      		closedir($dir);
//- permissions
			$bashCmd="chmod 777 $toDirectory/*";
			$passAry=array('pluginargs'=>$bashCmd);
			$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
			if ($createThumbnail){
				$bashCmd="chmod 777 $toDirectory/*";
				$passAry=array('pluginargs'=>$bashCmd);
				$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
			}
			$base->Plugin001Obj->buildImagePathList(&$base);
			$msgLine.="</pre>";
       		$base->errorProfileAry['standardmsg1']=$msgLine;
    	} // end if isdir	
	}
//==================================================================
function batchImagesAjax($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'paramsary');//xxx
		$base->FileObj->initLog('batchimages.log',&$base);
		$startTime=time();
		$msgLine="name.................... rwidth rheight  ->   width height\n\n";
		//$msgLine=sprintf("%-25s%6s%6s%6s\n", 'name', 'rwidth', 'width', 'height');
		$sendData=$base->paramsAry['senddata'];
		$nonImageFileCnt=0;
		$dirCnt=0;
		$imageCnt=0;
		$workAry=array();
		$sendDataAry=explode('`',$sendData);
		foreach ($sendDataAry as $ctr=>$valueString){
			$valueAry=explode('|',$valueString);
			$theName=$valueAry[0];
			$theValue=$valueAry[1];
			$workAry[$theName]=$theValue;
		}
		//$base->FileObj->writeLog('jeff.txt','xxxf0',&$base);
		set_time_limit(6000);
    	$imageWidth=$workAry['resizeimageswidthid'];
    	$imageHeight=$workAry['resizeimagesheightid'];
    	$theBase=$base->ClientObj->getBase(&$base);
    	$imageBase=$base->ClientObj->getImageBase(&$base);
    	$rawImageBase=$base->ClientObj->getRawImagebase(&$base);
    	$fromDirectory=$workAry['frompathid'];
    	$toDirectory=$workAry['topathid'];
    	$pos=strpos($theBase,$fromFullDirectory,-1);
    	if ($pos<0){$fromFullDirectory=$theBase.'/'.$fromDirectory;}
    	else {$fromFullDirectory=$fromDirectory;}
    	$pos=strpos($theBase,$toFullDirectory,-1);
    	if ($pos<0){$toFullDirectory=$theBase.'/'.$toDirectory;}
    	else {$toFullDirectory=$toDirectory;}
		$thisTime=time();
		$diffTime=$thisTime-$startTime;
		$base->FileObj->writeLog('batchimages.log',"$diffTime: from: $fromFullDirectory, to: $toFullDirectory",&$base);
    	//echo "tofulldirectory: $toFullDirectory, fromfulldirectory: ($fromFullDirectory)___";//xxxf
		//exit();
    	if (!is_dir($toFullDirectory)){
    		mkdir($toFullDirectory,0777); 
    	}
//- process image
    	$returnMsg=NULL;
    	//$toPathId=$workAry['topathid'];
    	//echo "xxxf_1 fromdirectory: $fromDirectory, todirectory: $toDirectory, imagebase: $imageBase, topathid: $toPathId";
     	//echo "xxxf0, todirectory: $toDirectory";exit();
     	//$base->FileObj->writeLog('jeff.txt','xxxf10',&$base);//good
     	if(is_dir($toFullDirectory) && is_dir($fromFullDirectory)){
    		//$base->FileObj->writeLog('jeff.txt','xxxf10.5',&$base);//bad
       		$dir = opendir($fromFullDirectory);
       		$base->UtlObj->openImageBuffer(0,&$base);
			$totTime=0;
			$tstStrgConvert=",jpg,png,bmp,";
			$tstStrgCopy=",mov,wav,";
			//$base->FileObj->writeLog('jeff.txt','xxxf11',&$base);//bad
      		while (($imageName = readdir($dir)) !== false){
        		if ($imageName != '.' && $imageName != '..'){
        			//$base->FileObj->writeLog('jeff.txt',"xxxf12: $imageName",&$base);
        			$imageNameAry=explode('.',$imageName);
        			$imageNameAryCnt=count($imageNameAry);
        			$imageNameAryLastPos=$imageNameAryCnt-1;
        			$imageNameSuffix=$imageNameAry[$imageNameAryLastPos];
        			$imageNameSuffix=strtolower($imageNameSuffix);
        			$convPos=strpos($tstStrgConvert,$imageNameSuffix,0);
        			$copyPos=strpos($tstStrgCopy,$imageNameSuffix,0);
        			//echo "$imageName, $imageNameSuffix, $convPos, $copyPos\n";//xxxf
       				$imagePath="$fromFullDirectory/$imageName";
        			$toPath="$toFullDirectory/$imageName";
	           		//$imageWork = new Imagick($imagePath);
					$thisTime=time();
					$diffTime=$thisTime-$startTime;
					$base->FileObj->writeLog('batchimages.log',"$diffTime: $imageName",&$base);
        			if ($convPos>0){
        				$imageCnt++;
        				$thisTime=time();
						$diffTime=$thisTime-$startTime;
        				$base->FileObj->writeLog('batchimages.log',"$diffTime: --- read $imagePath",&$base);
 //- read old width, height
		           		$success=$base->UtlObj->readImage(0,$imagePath,&$base);
		           		$base->FileObj->writeLog('batchimages.log',"$diffTime: --- success: $success for $imagePath",&$base);
						$imageStatsAry=$base->UtlObj->getImageStats(0,&$base);
						$oldImageWidth=$imageStatsAry['imagewidth'];
						$oldImageHeight=$imageStatsAry['imageheight'];
		           		//$oldImageWidth=$imageWork->getImageWidth();
        	    		//$oldImageHeight=$imageWork->getImageHeight();
 //- convert image
       					$thisTime=time();
						$diffTime=$thisTime-$startTime;
        				$base->FileObj->writeLog('batchimages.log',"$diffTime: --- resize it",&$base);
		           		$success=$base->UtlObj->resizeImage(0,$imageWidth, $imageHeight, &$base);
	      				//$imageWork->thumbnailImage($imageWidth, $imageHeight);
//- read new width, height
						$imageStatsAry=$base->UtlObj->getImageStats(0,&$base);
						$newImageWidth=$imageStatsAry['imagewidth'];
						$newImageHeight=$imageStatsAry['imageheight'];  		
	            		//$newImageWidth=$imageWork->getImageWidth();
    	        		//$newImageHeight=$imageWork->getImageHeight();
        	    		//$msgLine.="$oldImageWidth, $oldImageHeight => $newImageWidth, $newImageHeight\n";
        	    		$msgLine.=sprintf("%-25s%5s%5s => %5s%5s\n",$imageName,$oldImageWidth, $oldImageHeight, $newImageWidth, $newImageHeight);
        	    		$thisTime=time();
						$diffTime=$thisTime-$startTime;
           				$base->FileObj->writeLog('batchimages.log',"$diffTime: --- write $toPath",&$base);
 		           		$base->UtlObj->writeImage(0,$toPath,&$base);
       					$thisTime=time();
						$diffTime=$thisTime-$startTime;
		           		$base->FileObj->writeLog('batchimages.log',"$diffTime: --- done writing",&$base);
		           		//echo "xxxf2";
	           		} else if ($copyPos>0){
	           			if ($imagePath != $toPath){
		           			$bashCommand="cp $imagePath $toPath";
   							$passAry=array('pluginargs'=>$bashCmd);
	    	       			$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
	           			}
	           			$nonImageFileCnt++;
	           		}
	           		else {$nonImageFileCnt++;}
	          	} // end if imagename
	      	} // end while readdir
	  		$thisTime=time();
			$diffTime=$thisTime-$startTime;
			$base->FileObj->writeLog('batchimages.log',"$diffTime: done",&$base);
      		closedir($dir);
      		//echo 'xxxf5';
 //- permissions
			$bashCmd="chmod 777 $toDirectory/*";
			$passAry=array('pluginargs'=>$bashCmd);
			$bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
			echo "okmsg|images resized: $imageCnt, nonimages found: $nonImageFileCnt\n\n$msgLine";
    	} // end if isdir	
    	else {
    		echo "either $toFullDirectory or $fromFullDirectory is not a directory";
    	}
	}
//========================================
	function batchMetaDbTable($base){
		$base->DebugObj->printDebug("Plugin001Obj:batchMetaDbTable('base')",0);
		$tableName=$base->paramsAry['tabletobatch'];
		$selectorNameAry=array('dbtablemetaname','dbtablemetacolumnname');
		$dbControlsAry=array();
		$writeRowsAry=array();
		$currentRowAry=array();
		$dbControlsAry['selectornameary']=$selectorNameAry;
		$dbControlsAry['dbtablename']='dbtablemetaprofile';
		$dbTableMetaStuff=$base->DbObj->getTableMetaStuff($tableName,&$base);
		//$base->DebugObj->printDebug($dbTableMetaStuff,1);
		$noRows=count($dbTableMetaStuff);
		for ($ctr=0;$ctr<$noRows;$ctr++){
			$workAry=$dbTableMetaStuff[$ctr];
			$currentRowAry=array('dbtablemetaname'=>$tableName,'dbtablemetacolumnname'=>$workAry['fieldname'],'dbtablemetatype'=>$workAry['type']);
//-->get it here
			$writeRowsAry[]=$currentRowAry;
		}
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$dbControlsAry['selectornameary']=$selectorNameAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//=======================================new 
	function updateDb($updatePass,$base){
		$base->DebugObj->printDebug("pl001Obj:updateDb($rowValuesAry,$oldRowValuesAry,'base')",0);
		$rowValuesAry=$updatePass['rowvaluesary'];
		$oldRowValuesAry=$updatePass['oldrowvaluesary'];
		$rowControlsAry=$updatePass['rowcontrolsary'];
    $selectorName=$rowControlsAry['selectorname'];
		$tableMetaAry=$rowControlsAry['tablemetadata'];
		$tableNameTypeAry=$rowControlsAry['tablenametype'];
		$selectorValue=$rowValuesAry[$selectorName];
    $tableName=$rowControlsAry['tablename'];
		$urlReDirect=$rowControlsAry['urlredirect'];
//->
		$query="update $tableName set ";
		$start=true;
	}
//=======================================
	function writeYourData($base){
		$base->DebugObj->printDebug("Plugin001Obj:writeYourData('base')",0);
		$dbControlsAry=array();
		$writeRowsAry=array();
		$currentRowAry=array();
		$selectorNameAry=array();
		$selectorNameAry[]='colname1';
		$dbControlsAry['selectornameary']=$selectorNameAry;
		$dbControlsAry['dbtablename']='testtable';
		$currentRowAry['colname1']='colval1';
		$currentRowAry['colname2']='colval2';
		$currentRowAry['colname3']='colval11';
		$writeRowsAry[]=$currentRowAry;
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//=======================================
	function updateDbFromForm($base){
		$base->DebugObj->printDebug("Plugin001Obj:updateDbFromForm('base')",0);
		$params=$base->paramsAry;
		//$base->DebugObj->setPrio(-1,-1);//xxx
		//$base->DebugObj->printDebug($params,1,'params');//xxxd
		$formName=$params['form'];
   		$dbTableName=$base->formProfileAry[$formName]['tablename'];
		$redir=$base->formProfileAry[$formName]['redirect'];
		$redirOvr=$base->paramsAry['returnovr'];
		if ($redirOvr != NULL){$redir=$redirOvr;}
		if ($redir == NULL){$dontReDirect=true;}
		else {$dontReDirect=false;}
		$jobLocal=$base->systemAry['joblocal'];
		if (substr($redir,0,3) == 'http'){ $urlReDir=$redir;}
		else {$urlReDir="$jobLocal$redir";}
		$allWriteRowsAry=array();
		//$base->DebugObj->printDebug($params,1,'xxx');//xxx
		foreach ($params as $name=>$value){
			$nameAry=explode('_',$name);
		 	$dbTableRow=$nameAry[1];
//--- null row means default table
		 	if ($dbTableRow == ''){
		 		$dbTableRow=0;
		 		$thisDbTableName=$dbTableName;
		 		$dbTableColumn=$nameAry[0];
		 	}
//--- not null is extra table: tablename_rowno_columnname
		 	else {
		 		$thisDbTableName=$nameAry[0];
		 		$dbTableRow=$nameAry[1];
		 		$dbTableColumn=$nameAry[2];
		 		$dbTableColumn2=$nameAry[3];
		 	}
		 	//echo "thisdbtablename: $thisDbTableName, name: $name<br>";//xxx
		 	if ($thisDbTableName != 'formelementprofile'){$testValue=$value;}
		 	else {$testValue='dmyoverride';}
		 	switch ($testValue){
		 		case 'delcntl':
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow]['overridecommand']='delete';
		 			break;
		 		case 'nocntl':
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow]['overridecommand']='dontupdate';
		 			break;
		 		default:
				 	$allWriteRowsAry[$thisDbTableName][$dbTableRow][$dbTableColumn]=$value;
		 	}
		 	if ($dbTableColumn2 != ''){
		 		$dbTableColumn2Value=$params[$dbTableColumn2];
		 		if ($dbTableColumn2Value != ''){
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow][$dbTableColumn2]=$dbTableColumn2Value;
		 		}	
		 	}
		} 
//--- call write
//$base->DebugObj->setPrio(-1,-1);//xxx
		$allSuccessfulUpdate=true;
		//$base->DebugObj->printDebug($allWriteRowsAry,1,'wra');//xxxd
		//exit();
		foreach ($allWriteRowsAry as $dbTableNameUse=>$writeRowsAryUse){
			$dbControlsAry = array();
			$dbControlsAry['writerowsary']=$writeRowsAryUse;
			$dbControlsAry['dbtablename']=$dbTableNameUse;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
			//$base->DebugObj->setPrio(-2,-2);//xxx
			$successfulUpdate=$base->DbObj->writeToDb($dbControlsAry,&$base);
			//$errStrg=$base->ErrorObj->retrieveAllErrors(&$base);//xxxd
			//echo "$errStrg<br>";//xxxd
			if (!$successfulUpdate){$allSuccessfulUpdate=false;}
			//echo "successfulupdate: $successfulUpdate<br>";//xxx
			//$base->DebugObj->setPrio(0,0);//xxx
			//$base->DebugObj->printDebug($base->errorProfileAry,1,'xxx');
		}
//--- redirect
		//echo "successupd: $successfulUpdate, dontredirect: $dontReDirect<br>";//xxx
		if ($successfulUpdate && !$dontReDirect) {
			$pos=strpos('x'.$urlReDir,'sessionname',0);
			if ($pos<=0){
				$sessionName=$base->paramsAry['sessionname'];
				if ($sessionName != NULL){$urlReDir.="&sessionname=$sessionName&donterase=1";}
			}
			$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
			//$base->DebugObj->printDebug($base->paramsAry,1,'xxxd: params');
			//echo "urlredir: $urlReDir, urlredir_fm: $urlReDir_formatted<br>";//
			//exit();
			$base->UtlObj->appendValue('debug',"header to $urlReDir_formatted<br>",&$base);
			header("Location: $urlReDir_formatted");
		}
	}
//======================================= 
	function insertDbFromForm($base){
		$base->DebugObj->printDebug("Plugin001Obj:insertDbFromForm('base')",0);
		$params=$base->paramsAry;
		//$base->DebugObj->printDebug($params,1,'params');//xxx
		$formName=$params['form'];
	    $dbTableName=$base->formProfileAry[$formName]['tablename'];
		$redir=$base->formProfileAry[$formName]['redirect'];
		$formEmail=$base->formProfileAry[$formName]['formemail'];
		$redirOvr=$base->paramsAry['returnovr'];
		if ($redirOvr != NULL){$redir=$redirOvr;}
		if ($redir == NULL){$dontDoRedir=true;}
		else {$dontDoRedir=false;}
		$jobLocal=$base->systemAry['joblocal'];
		if (substr($redir,0,3) == 'http'){ $urlReDir=$redir;}
		else {$urlReDir="$jobLocal$redir";}
		$allWriteRowsAry=array();
		foreach ($params as $name=>$value){
			$nameAry=explode('_',$name);
		 	$dbTableRow=$nameAry[1];
		 	if ($dbTableRow == ''){
		 		$dbTableRow=0;
		 		$thisDbTableName=$dbTableName;
		 		$dbTableColumn=$nameAry[0];
		 	}
		 	else {
		 		$thisDbTableName=$nameAry[0];
		 		$dbTableColumn=$nameAry[2];
		 		$dbTableColumn2=$nameAry[3];
		 	}
		 	if ($thisDbTableName != 'formelementprofile'){$testValue=$value;}
		 	else {$testValue='dmyoverride';}
		 	switch ($testValue){
		 		case 'delcntl':
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow]['overridecommand']='delete';
		 			break;
		 		case 'nocntl':
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow]['overridecommand']='dontupdate';
		 			break;
		 		default:
				 	$allWriteRowsAry[$thisDbTableName][$dbTableRow][$dbTableColumn]=$value;
		 	}
		 	$allWriteRowsAry[$thisDbTableName][$dbTableRow][$dbTableColumn]=$value;
		 	if ($dbTableColumn2 != ''){
		 		$dbTableColumn2Value=$params[$dbTableColumn2];
		 		if ($dbTableColumn2Value != ''){
		 			$allWriteRowsAry[$thisDbTableName][$dbTableRow][$dbTableColumn2]=$dbTableColumn2Value;
		 		}	
		 	}
		} 
	 	//$base->DebugObj->printDebug($allWriteRowsAry,1,'awra');//xxxf
		$allSuccessfulInsert=true;
		foreach ($allWriteRowsAry as $dbTableNameUse=>$writeRowsAryUse){
			$dbControlsAry = array();
			$dbControlsAry['writerowsary']=$writeRowsAryUse;
			$dbControlsAry['dbtablename']=$dbTableNameUse;
			//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx
			//$base->DebugObj->setPrio(-1,-1);//xxx
			$successfulInsert=$base->DbObj->insertToDb($dbControlsAry,&$base);
			//echo "successfulinsert: $successfulInsert<br>";//xxx
			//$base->DebugObj->setPrio(0,0);//xxx
			//$base->DebugObj->printDebug($base->errorProfileAry,1,'xxxd');
			if (!$successfulInsert){
				$allSuccessfulInsert=false;
			}
		}
//--- redirect
		//echo "allsuccessins: $allSuccessfulInsert<br>";//xxx
		if ($allSuccessfulInsert){
			if ($formEmail != NULL){
				$base->operationPlugin001Obj->sendEmailFromForm(&$base);
			}
			if (!$dontDoRedir) {
				$pos=strpos('x'.$urlReDir,'sessionname',0);
				if ($pos<=0){
					$sessionName=$base->paramsAry['sessionname'];
					if ($sessionName != NULL){$urlReDir.="&sessionname=$sessionName&donterase=1";}
				}
				$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
				$base->UtlObj->appendValue('debug',"header to $urlReDir_formatted<br>",&$base);
				header("Location: $urlReDir_formatted");
			}
		}
	}
//======================================= 
	function deleteDbFromForm($base){
		$base->DebugObj->printDebug("deleteDbFromForm('base')",0);
		$formName=$base->paramsAry['form'];
		$dbTableName=$base->formProfileAry[$formName]['tablename'];
		$redirectJob=$base->formProfileAry[$formName]['redirect'];
		$jobLocal=$base->systemAry['joblocal'];
		$redirectPath="$jobLocal$redirectJob";
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$base->paramsAry[$keyName];
		$query="delete from $dbTableName where $keyName=$keyValue";
		$result=$base->DbObj->queryTable($query,'update',&$base);
//--- redirect
		$urlReDir=$redirectPath;
		$pos=strpos('x'.$urlReDir,'sessionname',0);
		if ($pos<=0){
			$sessionName=$base->paramsAry['sessionname'];
			if ($sessionName != NULL){$urlReDir.="&sessionname=$sessionName&donterase=1";}
		}
		$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
		$base->DebugObj->printDebug("-rtn:DbObj:deleteDbFromForm",0); //xx (f)
		$base->UtlObj->appendValue('debug',"header to $urlReDir_formatted<br>",&$base);
		header("Location: $urlReDir_formatted");
	}
//======================================= 
	function deleteDbFromUrl($base){
		$base->DebugObj->printDebug("deleteDbFromUrl('base')",0);
		$dbTableName=$base->paramsAry['dbtablename'];
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$base->paramsAry[$keyName];
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxx');
		if (array_key_exists('deletename',$base->paramsAry)){
			$useKeyName=$base->paramsAry['deletename'];
			$useKeyValue=$base->paramsAry[$useKeyName];
		}
		else {
			$useKeyName=$keyName;
			$useKeyValue=$keyValue;
		}
		//echo "usekeyname: $useKeyName, usekeyvalue: $useKeyValue<br>";
		if ($dbTableName != NULL && $useKeyName != NULL && $useKeyValue != NULL){
			$query="delete from $dbTableName where $useKeyName=$useKeyValue";
			//echo "query: $query<br>";//xxx
			$result=$base->DbObj->queryTable($query,'update',&$base);
			unset($base->paramsAry[$keyName]);
		}
	}
//======================================= 
	function deleteDbFromAjax($base){
		$base->DebugObj->printDebug("deleteDbFromUrl('base')",0);
		$sendData=$base->paramsAry['senddata'];
		$sendDataAry=explode('`',$sendData);
		$workAry=array();
		foreach ($sendDataAry as $ctr=>$theValue){
			$theValueAry=explode('|',$theValue);
			$workKeyName=$theValueAry[0];
			$workKeyValue=$theValueAry[1];
			$workAry[$workKeyName]=$workKeyValue;
		}
		$dbTableName=$workAry['dbtablename'];
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$keyName=$dbControlsAry['keyname'];
		$keyValue=$workAry[$keyName];
		$childTable=$workAry['childtable'];
		if ($childTable != null){
			$query="select * from $childTable where $keyName=$keyValue";
			$passAry=array();
			$checkAry=$base->DbObj->queryTableRead($query,$passAry,&$base);
			$foreignCnt=count($checkAry);
		}
		if ($foreignCnt>0){
			echo "error|You cannot delete this record because their are dependencies from the table: $childTable";
		}
		else if ($dbTableName != NULL && $keyName != NULL && $keyValue != NULL){
			$query="delete from $dbTableName where $keyName=$keyValue";
			$result=$base->DbObj->queryTable($query,'update',&$base);
			echo "ok";
		}
		else {
			echo "error| dbtablename $dbTableName, keyname $keyName, keyvalue $keyvalue";
		}
	}
//=======================================
	function fixHtmlProfile($base){
		$base->DebugObj->printDebug("Plugin001Obj:fixHtmlProfile('base')",0); //xx (h)
		$this->fixTableProfile($base);
		$htmlAry=array();
		$query='select * from htmlelementprofile';
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$htmlElementProfileAry=$base->UtlObj->tableToHashAryV3($result);
		$htmlsWithElements=array();
//get htmlprofiles with elements
		foreach ($htmlElementProfileAry as $rowNo=>$valueAry){
			$htmlProfileId=$valueAry['htmlprofileid'];
			$htmlsWithElements[$htmlProfileId]='xx';
		}
//get htmlprofiles/jobprofiles
		$jobProfilesWithHtmls=array();
		$query='select * from htmlprofile';
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$htmlProfileAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($htmlProfileAry as $rowNo=>$valueAry){
			$htmlProfileId=$valueAry['htmlprofileid'];
			$jobProfileId=$valueAry['jobprofileid'];
			if (array_key_exists($htmlProfileId,$htmlsWithElements)){$hasElement=true;}
			else {$hasElement=false;}
			if (!array_key_exists($jobProfileId,$jobProfilesWithHtmls)){
				if ($hasElement){$jobProfilesWithHtmls[$jobProfileId]=$htmlProfileId;}
			}	
			else {
				if ($hasElement){
					$base->DebugObj->placeCheck("htmlprofileid w/ele dup: $htmlProfileId"); //xx (c)
				} // end if
			} // end else
		} // end foreach 
// final check to delete bad htmlprofiles
	foreach ($htmlProfileAry as $rowNo=>$valueAry){
		$htmlProfileId=$valueAry['htmlprofileid'];
		if (array_key_exists($htmlProfileId,$htmlsWithElements)){$hasElement=true;}
			else {$hasElement=false;}
		$jobProfileId=$valueAry['jobprofileid'];
		if (array_key_exists($jobProfileId,$jobProfilesWithHtmls)){
			$chkHtmlProfileId=$jobProfilesWithHtmls[$jobProfileId];
			if ($chkHtmlProfileId != $htmlProfileId){
				if ($hasElement){$insLine=" and has element";}
				else {$insLine=" ";}
				$base->DebugObj->placeCheck("dup $insLine: jobprofileid: $jobProfileId htmlprofileid: $htmlProfileId"); //xx (c)
				$query="delete from htmlprofile where htmlprofileid=$htmlProfileId";
				$result=$base->DbObj->queryTable($query,'read',&$base);
			} // end if
		} // end if
		else {
			$jobProfilesWithHtmls[$jobProfileId]=$htmlProfileId;
		} // end else
	} // end foreach
	}
//=======================================
	function fixTableProfile($base){
		$base->DebugObj->printDebug("Plugin001Obj:fixTableProfile('base')",0); //xx (h)
		$tableAry=array();
		$query='select * from columnprofile';
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$columnProfileAry=$base->UtlObj->tableToHashAryV3($result);
		$tablesWithElements=array();
//get tableprofiles with elements
		foreach ($columnProfileAry as $rowNo=>$valueAry){
			$tableProfileId=$valueAry['tableprofileid'];
			$tablesWithElements[$tableProfileId]='xx';
		}
//get tableprofiles/jobprofiles
		$jobProfilesWithTables=array();
		$query='select * from tableprofile';
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$tableProfileAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($tableProfileAry as $rowNo=>$valueAry){
			$tableProfileId=$valueAry['tableprofileid'];
			$jobProfileId=$valueAry['jobprofileid'];
			if (array_key_exists($tableProfileId,$tablesWithElements)){$hasElement=true;}
			else {$hasElement=false;}
			if (!array_key_exists($jobProfileId,$jobProfilesWithTables)){
				if ($hasElement){$jobProfilesWithTables[$jobProfileId]=$tableProfileId;}
			}	
			else {
				if ($hasElement){
					$base->DebugObj->placeCheck("tableprofileid w/ele dup: $tableProfileId"); //xx (c)
				} // end if
			} // end else
		} // end foreach 
// final check to delete bad tableprofiles
	foreach ($tableProfileAry as $rowNo=>$valueAry){
		$tableProfileId=$valueAry['tableprofileid'];
		if (array_key_exists($tableProfileId,$tablesWithElements)){$hasElement=true;}
			else {$hasElement=false;}
		$jobProfileId=$valueAry['jobprofileid'];
		if (array_key_exists($jobProfileId,$jobProfilesWithTables)){
			$chkTableProfileId=$jobProfilesWithTables[$jobProfileId];
			if ($chkTableProfileId != $tableProfileId){
				if ($hasElement){$insLine=" and has element";}
				else {$insLine=" ";}
				$base->DebugObj->placeCheck("dup table profile $insLine: jobprofileid: $jobProfileId tableprofileid: $tableProfileId"); //xx (c)
				$query="delete from tableprofile where tableprofileid=$tableProfileId";
				$result=$base->DbObj->queryTable($query,'read',&$base);
			} // end if
		} // end if
		else {
			$jobProfilesWithTables[$jobProfileId]=$tableProfileId;
		} // end else
	} // end foreach
	}
//=======================================
	function fixDbTableMetaProfile(){
		$base->DebugObj->printDebug("pl001Obj:fixDbTableMetaProfile()",0); //xx (h)
	}
//=======================================
	function limitSelection($base){
		$base->DebugObj->printDebug("PluginObj1:limitSelection('base')",0); //xx (h)
		$updateSessionName=$base->paramsAry['savetosession'];
		if ($updateSessionName != NULL){
			$updateSessionValue=$base->paramsAry[$updateSessionName];
			$updateSessionAry=array($updateSessionName=>$updateSessionValue);
			//$base->DebugObj->printDebug("$updateSessionName, $updateSessionValue",1,'usa');//xxx
			$sessionName=$base->paramsAry['sessionname'];
			if ($sessionName == NULL){
				$sessionName=$_SESSION['sessionobj']->saveNewSessionAry($updateSessionAry);
				$base->paramsAry['sessionname']=$sessionName;
			}
			else {$_SESSION['sessionobj']->saveSessionAry($sessionName,$updateSessionAry);}
		}
		$base->DebugObj->printDebug("-rtn: limitSelection",0); //xx (f)
	}
//=======================================
	function updateSession($base){
		$base->DebugObj->printDebug("PluginObj1:updateSession('base')",0); //xx (h)
		$updateSessionName=$base->paramsAry['savetosession'];
		if ($updateSessionName != NULL){
			$updateSessionValue=$base->paramsAry[$updateSessionName];
			$updateSessionAry=array($updateSessionName=>$updateSessionValue);
			$sessionName=$base->paramsAry['sessionname'];
			if ($sessionName == NULL){
				$sessionName=$_SESSION['sessionobj']->saveNewSessionAry($updateSessionAry);
				$base->paramsAry['sessionname']=$sessionName;
			}
			else {$_SESSION['sessionobj']->saveSessionAry($sessionName,$updateSessionAry);}
		}
		$base->DebugObj->printDebug("-rtn: limitSelection",0); //xx (f)
	}
//=================================
	function overlayRows($base){
		$base->DebugObj->printDebug("pl1:overlayFormElements()",0); //xx (h)
//- get dbtablestuff about table
		$dbTableMetaName='formelementprofile';
		$dbControlsAry=array('dbtablename'=>$dbTableMetaName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
//- get from elements
		$query="select * from formelementprofileview where jobname='updatedbtablemetaprofile' and formname='basicform'";
		$passAry=array('delimit1'=>'formelementname');
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$updateTable=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- get to elements
		$query="select * from formelementprofileview where jobname='insertdbtablemetaprofile' and formname='basicform'";
		$passAry=array();
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$insertTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
		$foreignKeyAry=$dbControlsAry['foreignkeyary'];
		$keyName=$dbControlsAry['keyname'];
		$selectorNameAry=$dbControlsAry['selectornameary'];
//- loop through to elements and update them
		foreach ($insertTableAry as $ctr=>$eleAry){
			$rowName=$eleAry['formelementname'];
			foreach ($eleAry as $colName=>$colValue){
				$updateIt=true;
//- check foreign key
					if (in_array($colName, $foreignKeyAry)){ $updateIt=false; }
//- check selector
				if(in_array($colName, $selectorNameAry)){ $updateIt=false; }
//- check keyname
				if ($colName == $keyName){$updateIt=false;}
//-check if to update
				if ($updateIt){
					$colValue=$insertTableAry[$ctr][$colName];
					$newValue=$updateTable[$rowName][$colName];
					if ($colValue != $newValue){
						$insertTableAry[$ctr][$colName]=$newValue;
					}
				}
			}
		}
//--- write data
		$dbTableName=$dbTableMetaName;
    $dbControlsAry=array('dbtablename'=>$dbTableName);
    $base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
    $writeRowsAry=$insertTableAry;
    $dbControlsAry['writerowsary']=$writeRowsAry;
    $base->DbObj->writeToDb($dbControlsAry,&$base);
//---
		$base->DebugObj->printDebug("-rtn:overlayRows",0); //xx (f)
	}
//=====================================
	function genericDmp($base){
		$fileName="smallchair.jpg";
		$fileName2="smallchair2.jpg";
		$fromPath="/home/jeff/web/rawimages/cedar/products";
		$fromFile="$fromPath/$fileName";
		$toPath="/home/jeff/web/images/cedar/products";
		$toFile="$toPath/$fileName";
		$toFile2="$toPath/$fileName2";
		//echo "thpath: $thePath<br>";
		//exit();
		echo 'do NewMagickWand, <br>';
		//$imageWork = NewMagickWand();
		$base->UtlObj->openImageBuffer(0,&$base);
		echo 'do MagickReadImage, <br>';
		//$tst=MagickReadImage($imageWork, $fromFile);
		$tst=$base->UtlObj->readImage(0,$fromFile,&$base);
		echo "return MagickReadImage(tst): $tst<br>";
		echo "do MagickResizeImage <br>";
		//$tst=MagickResizeImage($imageWork, 50, 50, MW_QuadraticFilter, .1);
		$width=0;
		$height=400;
		echo "width: $width, height: $height<br>";
		$tst=$base->UtlObj->resizeImage(0,$width,$height,&$base);
		echo "return MagickResizeImage(tst): $tst<br>";
		echo "do MagickWriteImage<br>";
		//chdir($toPath);
		//$tst=MagickWriteImage($imageWork, $toFile);		
		$tst=$base->UtlObj->writeImage(0,$toFile2,&$base);
		echo "return MagickWriteImage(tst): $tst<br>";
		exit();
	}
//=================================
	function overlayHtmlElements($base){
		$base->DebugObj->printDebug("pl1:overlayFormElements()",0); //xx (h)
//- get dbtablestuff about table
		$dbTableMetaName='htmlelementprofile';
		$dbControlsAry=array('dbtablename'=>$dbTableMetaName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
//- get html elements to copy
		$query="select * from htmlelementprofileview where jobname='listpeople'";
		//$passAry=array('delimit1'=>'htmlelementname');
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$updateTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- delete old html elements
		$query="delete from htmlelementprofile where htmlprofileid=(select htmlprofileid from htmlprofile where jobprofileid =
(select jobprofileid from jobprofile where jobname='listhtml'))";
		//$passAry=array();
		//$result=$base->DbObj->queryTable($query,'read',&$base);
//- loop through to elements and update them
		foreach ($updateTableAry as $ctr=>$eleAry){
			unset($eleAry['htmlelementprofileid']);
			unset($eleAry['jobname']);
			unset($eleAry['htmlname']);
			$eleAry['htmlprofileid']=990;
			$updateTableAry[$ctr]=$eleAry;
		}	
//--- write data
	$dbTableName=$dbTableMetaName;
    $dbControlsAry=array('dbtablename'=>$dbTableName);
    $base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
    $writeRowsAry=$updateTableAry;
    $dbControlsAry['writerowsary']=$writeRowsAry;
    $base->DbObj->writeToDb($dbControlsAry,&$base);
//---
		$base->DebugObj->printDebug("-rtn:overlayRows",0); //xx (f)
	}
//=======================================
	function rebuildAllViewsdeprecated($base){
		$base->DebugObj->printDebug("debug001Obj:rebuildAllViews",0); //xx (h)
		$query="select * from sqlprofile where sqltype='rebuildviews' order by sqlorder";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$workAry=$base->UtlObj->tableToHashAryV3($result);
		foreach ($workAry as $key=>$value){
			$query=$value['sqlcommand'];
		$result=$base->DbObj->queryTable($query,'run',&$base);
		}
		$base->DebugObj->printDebug("-rtn:rebuildAllViews",0);
	}
//=======================================
	function rebuildView($base){
		$base->DebugObj->printDebug("debug001Obj:rebuildView",0); //xx (h)
		$dbTableProfileId=$base->paramsAry['dbtableprofileid'];
		$query="select * from dbcolumnprofileview where dbtableprofileid=$dbTableProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbcolumnname');
		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($dataAry,1,'dtaary');//xxx
		$foreignFieldsAry=array();
		$foreignFiltersAry=array();
		$foreignNonFiltersAry=array();
		$errorsFound=false;
		foreach ($dataAry as $columnName=>$columnAry){
			$columnNameAry=explode('_',$columnName.'_');
			$columnName=$columnNameAry[0];
			$dbTableName=$columnAry['dbtablename'];
			$foreignField_table=$columnAry['dbcolumnforeignfield'];
			$foreignField=$base->UtlObj->returnFormattedData($foreignField_table,'boolean','internal',&$base);
			$noViewLink_raw=$columnAry['dbcolumnnoviewlink'];
			$noViewLink=$base->UtlObj->returnFormattedData($noViewLink_raw,'boolean','internal',&$base);
			if ($foreignField){
				$foreignKeyName=$columnAry['dbcolumnforeignkeyname'];
				$foreignTable=$dataAry[$foreignKeyName]['dbcolumnforeigntable'];
				$foreignKeyNoViewLink_raw=$dataAry[$foreignKeyName]['dbcolumnnoviewlink'];
				$foreignKeyNoViewLink=$base->UtlObj->returnFormattedData($foreignKeyNoViewLink_raw,'boolean','internal',&$base);
				if (!$foreignKeyNoViewLink){
					$mainTable=$dataAry[$foreignKeyName]['dbcolumnmaintable'];
					if ($mainTable == NULL){$mainTable=$dbTableName;}
					$foreignColumnName=$columnAry['dbcolumnforeigncolumnname'];
					//echo "table: $foreignTable, column: $foreignColumnName<br>";//xxx
					if ($foreignColumnName == NULL){$foreignColumnName=$columnName;}
					$foreignFieldsAry[$foreignTable][$columnName]=$foreignColumnName;
					$foreignFiltersAry[$foreignTable][$foreignKeyName]=$mainTable;
				}
			}
			$foreignKey_table=$columnAry['dbcolumnforeignkey'];
			$foreignKey=$base->UtlObj->returnFormattedData($foreignKey_table,'boolean','internal');
			if ($foreignKey && !$noViewLink){
				$dbColumnName=$columnAry['dbcolumnname'];
				$dbColumnParentSelector_table=$columnAry['dbcolumnparentselector'];
				$dbColumnParentSelector=$base->UtlObj->returnFormattedData($dbColumnParentSelector_table,'boolean','internal');	
				if (!$dbColumnParentSelector){
					$dbColumnMainTable=$columnAry['dbcolumnmaintable'];
					//- if not null, then in other table so assume parent selector of that table
					if ($dbColumnMainTable == NULL){
						//- figure this out later - how to display field if not null, but null if null
						//$foreignNonFiltersAry[$dbColumnName]='bypass';
					}
				}
			}
		}
		$viewStmt="create view $dbTableName".'view'." as ";
		$viewStmt.=" select ";
//$base->DebugObj->printDebug($foreignFieldsAry,1,'xxx');
		$selectList="$dbTableName.*";
		$tableList="$dbTableName";
//- foreignfieldsary put in display
		$filterList=NULL;
		foreach ($foreignFieldsAry as $foreignTable=>$columnNameAry){
			$tableList.=",$foreignTable";
			if ($foreignTable == NULL){$errorsFound=true;}
			foreach ($columnNameAry as $columnName=>$foreignColumnName){
				//echo "columnname: $columnName, foreigncolumnname: $foreignColumnName<br>";//xxx
				if ($columnName == $foreignColumnName){
					$selectList.=','."$foreignTable.$columnName";
				}
				else {
					$selectList.=','."$foreignTable.$foreignColumnName as $columnName";
				}
				if ($selectList == NULL || $columnName == NULL){$errorsFound=true;}
			}
		}
//- foreignfiltersary put in where clause
		$firstTime=true;		
		//$base->DebugObj->printDebug($foreignNonFiltersAry,1,'fnfa');//xxx
		foreach ($foreignFiltersAry as $foreignTable=>$foreignKeyAry){
			//echo "foreigntable: $foreignTable<br>";//xxx
			foreach ($foreignKeyAry as $foreignKeyName=>$mainTable){
				$foreignKeyNameAry=explode('_',$foreignKeyName);
				$foreignKeyName=$foreignKeyNameAry[0];
				if (!array_key_exists($foreignKeyName,$foreignNonFiltersAry)){
				//echo "$foreignKeyName, $foreignTable<br>";//xxx
				if ($firstTime){$andIns=NULL;}
				else {$andIns=" and ";}	
				$filterList.="$andIns$foreignTable.$foreignKeyName=$mainTable.$foreignKeyName";
				if ($foreignTable == NULL || $foreignKeyName == NULL || $dbTableName == NULL){$errorsFound=true;}
				$firstTime=false;
				}
			}						
		}
//- final construction
		if ($filterList != NULL){$filterListInsert='where '.$filterList;}
		else {$filterListInsert=NULL;}
		$viewStmt.="$selectList from $tableList $filterListInsert";
		//echo "view: $viewStmt<br>";//xxxd
		//exit();//xxxd
		$dropViewStmt='drop view '.$dbTableName.'view';
		if ($errorsFound){
			echo "Errors have been found!!!<br>";
			echo "$viewStmt<br>";
		}
		else {
			$dbTableNameView=$dbTableName.'view';
			$query="select * from pg_views where viewname='$dbTableNameView'";
			//echo "query: $query";//xxx
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$checkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($checkAry,1,'chk');//xxx
			$noFileColumns=count($checkAry);
			//echo "nofilecolumns: $noFileColumns";//xxx
			if ($noFileColumns>0){
				//echo "drop it";//xxx
				//echo "drop stmt: $dropViewStmt<br>";//xxx
				$base->DbObj->queryTable($dropViewStmt,'maint',&$base);
			}
			$base->DbObj->queryTable($viewStmt,'maint',&$base);
			//echo "view stmt: $viewStmt<br>";//xxx
		}		
		$base->DebugObj->printDebug("-rtn:rebuildView",0);
	}
//=======================================
	function copyInColumns($base){
		$base->DebugObj->printDebug("debug001Obj:copyInColumns",0); //xx (h)
		//$base->DebugObj->printDebug($base->formProfileAry,1,'fpa');//xxx
		$formProfileId=$base->paramsAry['formprofileid'];
		if ($formProfileId != NULL){
			$query="select * from formprofile where formprofileid=$formProfileId";	
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>'formprofileid');
			$thisFormAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$query="select * from formelementprofile where formprofileid=$formProfileId";	
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>'formelementname');
			$thisFormElementAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$dbTableName=$thisFormAry[$formProfileId]['tablename'];
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
			$operationName=$thisFormAry[$formProfileId]['formoperation'];
			$dbControlsAry=array();
			$dbControlsAry['writerowsary']=array();
			$dbControlsAry['dbtablename']='formelementprofile';
			$formElementCtr=0;
			foreach ($dbTableMetaAry as $columnName=>$columnAry){
				//- foreign field
				$foreignField_file=$columnAry['dbcolumnforeignfield'];
				$foreignField=$base->UtlObj->returnFormattedData($foreignField_file,'boolean','internal');
				//- foreign key
				$foreignKey_file=$columnAry['dbcolumnforeignkey'];
				$foreignKey=$base->UtlObj->returnFormattedData($foreignKey_file,'boolean','internal');
				$mainTable=$columnAry['dbcolumnmaintable'];
				//- key
				$key_file=$columnAry['dbcolumnkey'];
				$key=$base->UtlObj->returnFormattedData($key_file,'boolean','internal');
				$standardPromptsName=$columnAry['standardpromptsname'];
				if (!$foreignField && !$key && !($foreignKey && ($mainTable != NULL))){
					if (!array_key_exists($columnName,$thisFormElementAry)){
						$metaName=$columnAry['dbcolumnname'];
						$metaType=$columnAry['dbcolumntype'];
						$formElementName=$metaName;
						$formElementLabel=$metaName;
						$formElementOptionLabelName='';
						$formElementOptionSql='';
						$formElementOptionValue='';
						$formElementSubType='';
						$formElementCols='';
						$formElementFirstLabel='';
						if ($operationName == 'delete_db_from_form'){$formElementType="display";}
						else {
							if ($metaType == 'bool'){$metaType='boolean';}
							switch ($metaType){
							case 'boolean':
								$formElementType='select';
								$formElementOptionSql="select * from standardpromptsprofile where standardpromptsname=%sglqt%yesno%sglqt%";
								$formElementOptionLabelName='standardpromptslabel';
								$formElementOptionValueName='standardpromptsvalue';
								break;
							case 'numeric':
								if ($standardPromptsName !=NULL){
									$formElementType='select';
									$formElementOptionSql="select * from standardpromptsprofile where standardpromptsname=%sglqt%$standardPromptsName%sglqt% order by standardpromptsorder, standardpromptslabel";
									$formElementOptionLabelName='standardpromptslabel';
									$formElementOptionValueName='standardpromptsvalue';
									$formElementFirstLabel='-select value-';
								}
								else {
									$formElementType="input";
									$formElementSubType="text";
									$formElementCols=3;
								}
								break;
							case 'integer':
								if ($standardPromptsName !=NULL){
									$formElementType='select';
									$formElementOptionSql="select * from standardpromptsprofile where standardpromptsname=%sglqt%$standardPromptsName%sglqt% order by standardpromptsorder, standardpromptslabel";
									$formElementOptionLabelName='standardpromptslabel';
									$formElementOptionValueName='standardpromptsvalue';
									$formElementFirstLabel='-select value-';
								}
								else {
									$formElementType="input";
									$formElementSubType="text";
									$formElementCols=3;
								}
								break;
							case 'varchar':
								if ($standardPromptsName !=NULL){
									$formElementType='select';
									$formElementOptionSql="select * from standardpromptsprofile where standardpromptsname=%sglqt%$standardPromptsName%sglqt% order by standardpromptsorder, standardpromptslabel";
									$formElementOptionLabelName='standardpromptslabel';
									$formElementOptionValueName='standardpromptsvalue';
									$formElementFirstLabel='-select value-';
								}
								else {
									$formElementType="input";
									$formElementSubType="text";
								}
								break;
							default:
								$formElementType="input";
								$formElementSubType="text";
							} // end switch on formeltype
						} // end else
						$chkBadPos=strpos(('chk'.$metaName),'bad',0);
						$newLineAry=array('formprofileid'=>$formProfileId);
						$newLineAry['formelementno']=999;
						$newLineAry['formelementname']=$formElementName;
						$newLineAry['formelementtype']=$formElementType;
						$newLineAry['formelementsubtype']=$formElementSubType;
						$newLineAry['formelementlabel']=$formElementLabel;
						$newLineAry['formelementoptionsql']=$formElementOptionSql;
						$newLineAry['formelementoptionlabelname']=$formElementOptionLabelName;
						$newLineAry['formelementoptionvaluename']=$formElementOptionValueName;
						$newLineAry['formelementcols']=$formElementCols;
						$newLineAry['formelementfirstlabel']=$formElementFirstLabel;
						$dbControlsAry['writerowsary'][]=$newLineAry;
						$formElementCtr++;
					} // end if
				} // end for
			} // end foreach
			//$base->DebugObj->printDebug($dbControlsAry,1,'doit');//xxx
			if ($formElementCtr>0){
				$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);	
				//$base->DebugObj->printDebug($dbControlsAry['writerowsary'],1,&$base);//xxx
			}
		} // end if
		$base->DebugObj->printDebug("-rtn:copyInColumns",0);
	}
//=======================================
	function copyFromParent($base){
		$base->DebugObj->printDebug("debug001Obj:copyFromParent('base')",0); //xx (h)
		//$base->DebugObj->printDebug($base->jobProfileAry,1,'job');//xxx
		$query="select * from jobxrefview where jobprofileid='%jobprofileid%'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'jobprofileid');
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($workAry,1,'wa');//xxx
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$jobParentId=$workAry[$jobProfileId]['jobparentid'];
		$copyType=$base->paramsAry['copytype'];
		if ($copyType == 'tags'){
			$htmlProfileId=$base->paramsAry['htmlprofileid'];
			//echo "jobid: $jobProfileId, parentid: $jobParentId<br>";//xxx
			$query="select * from htmlelementprofileview where jobprofileid='$jobParentId'";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			//$base->DebugObj->printDebug($workAry,1,'workary');//xxx
			foreach ($workAry as $rowNo=>$htmlElementAry){
				unset($workAry[$rowNo]['htmlelementprofileid']);
				$workAry[$rowNo]['htmlprofileid']=$htmlProfileId;
			}
			$dbControlsAry=array('dbtablename'=>'htmlelementprofile');
			$dbControlsAry['writerowsary']=$workAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx
			$base->DbObj->writeToDb($dbControlsAry,&$base);
			//$base->DebugObj->printDebug($workAry,1,'workary2');//xxx			
		}
		if ($copyType == 'forms'){
			//echo "xxx";
			$formProfileId=$base->paramsAry['formprofileid'];
			if ($formProfileId != NULL){
				//echo "xxx2";
				$query="select * from formelementprofileview where jobprofileid='$jobParentId'";	
				$result=$base->DbObj->queryTable($query,'read',&$base);
				$passAry=array();
				$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				//$base->DebugObj->printDebug($workAry,1,'workary');//xxx
				foreach ($workAry as $rowNo=>$formElementAry){
					unset($workAry[$rowNo]['formelementprofileid']);
					$workAry[$rowNo]['formprofileid']=$formProfileId;
				}
				$dbControlsAry=array('dbtablename'=>'formelementprofile');
				$dbControlsAry['writerowsary']=$workAry;
				//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx
				//echo "writeit";//
				$base->DbObj->writeToDb($dbControlsAry,&$base);
				//$base->DebugObj->printDebug($workAry,1,'workary2');//xxx	
			}
		}
		$base->DebugObj->printDebug("-rtn:copyFromParent",0);
	}
//=======================================
	function updateDbTableDoQuery($query,$type,$base){
		$result=NULL;
		$result=$base->DbObj->queryTable($query,$type,&$base);
		//echo "query: $query<br>";	//xxx
		return $result;
	}
//=======================================
	function updateDbTable($base){
		$base->DebugObj->printDebug("debug001Obj:updateDbTable",0); //xx (h)
		//$base->DebugObj->printDebug($base->paramsAry,1,'params');// xxx
		$dbTableProfileId=$base->paramsAry['dbtableprofileid'];
		$query="select * from dbtableprofileview where dbtableprofileid=$dbTableProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'dbtableprofileid');
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$dbTableName=$workAry[$dbTableProfileId]['dbtablename'];
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
		$query="select * from pg_tables where tablename=%sglqt%$dbTableName%sglqt%";
		//echo "query: $query<br>";//xxx
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$checkAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$noFileColumns=count($checkAry);
		//echo "nofilecnt of old: $noFileColumns<br>";//xxx
		//$base->DebugObj->printDebug($noFileColumns,1,'nofilecolxxx');
		if ($noFileColumns==0){
			$query="create table $dbTableName () without oids";
			$result=$this->updateDbTableDoQuery($query,'util',&$base);
		}
		//- !!! need to create method getdbtabledatafromdb
		$currentDbStuffAry=$base->DbObj->getDbTableDataFromDb($dbTableName,&$base);
		//$base->DebugObj->printDebug($currentDbStuffAry,1,'tst');//xxx
		foreach ($dbTableMetaAry as $dbTableMetaColumnName=>$dbTableMetaAry){
			//echo "check: $dbTableMetaColumnName<br>";
			if (!array_key_exists($dbTableMetaColumnName,$currentDbStuffAry)){
				$dbTableMetaMaxLength=$dbTableMetaAry['dbcolumnmaxlength'];
				if ($dbTableMetaMaxLength == NULL){$dbTableMetaMaxLength=200;}
				$dbTableMetaDefault=$dbTableMetaAry['dbcolumndefault'];
				if ($dbTableMetaDefault != null){$dbTableMetaDefaultInsert="default '$dbTableMetaDefault'";}
				else {$dbTableMetaDefaultInsert=null;}
				$dbTableMetaForeignField_file=$dbTableMetaAry['dbcolumnforeignfield'];
				$dbTableMetaForeignField=$base->UtlObj->returnFormattedData($dbTableMetaForeignField_file,'boolean','internal');
				$dbTableMetaForeignKey_file=$dbTableMetaAry['dbcolumnforeignkey'];
				$dbTableMetaForeignKey=$base->UtlObj->returnFormattedData($dbTableMetaForeignKey_file,'boolean','internal');
				$dbTableMetaKey_file=$dbTableMetaAry['dbcolumnkey'];
				$dbTableMetaKey=$base->UtlObj->returnFormattedData($dbTableMetaKey_file,'boolean','internal');
				$dbTableMetaNotNull_file=$dbTableMetaAry['dbcolumnnotnull'];
				$dbTableMetaNotNull=$base->UtlObj->returnFormattedData($dbTableMetaNotNull_file,'boolean','internal');
				$dbTableMetaUnique_file=$dbTableMetaAry['dbcolumnunique'];
				$dbTableMetaUnique=$base->UtlObj->returnFormattedData($dbTableMetaUnique_file,'boolean','internal');
				$dbTableMetaForeignTable=$dbTableMetaAry['dbcolumnforeigntable'];
				$dbTableMetaMainTable=$dbTableMetaAry['dbcolumnmaintable'];
				if ($dbTableMetaMainTable == $dbTableName){$dbTableMetaMainTable=NULL;}
				$dbTableMetaType=$dbTableMetaAry['dbcolumntype'];
				if (!$dbTableMetaForeignField && $dbTableMetaMainTable == NULL){
					if ($dbTableMetaType == 'varchar'){
						$dbTableMetaType_modified=$dbTableMetaType."($dbTableMetaMaxLength)";
					}
					else {$dbTableMetaType_modified=$dbTableMetaType;}
					//echo "$dbTableMetaColumnName<br>";	
					// - add it
						$query="alter table $dbTableName add column $dbTableMetaColumnName $dbTableMetaType_modified $dbTableMetaDefaultInsert";
						$result=$this->updateDbTableDoQuery($query,'util',&$base);
						// - not null
					if ($dbTableMetaNotNull){
						$query="alter table $dbTableName alter column $dbTableMetaColumnName set not null";	
						//$base->DbObj->queryTable($query,'manage',&$base);
						//echo "$query<br>";//xxx
					}
					// - unique
					if ($dbTableMetaUnique){
						$query="alter table $dbTableName alter column $dbTableMetaColumnName set unique";	
						$result=$this->updateDbTableDoQuery($query,'util',&$base);
					}
					// - foreign key
					if ($dbTableMetaForeignKey && $dbTableMetaForeignTable != NULL && $dbTableMetaMainTable == NULL){
						$query="alter table $dbTableName add foreign key($dbTableMetaColumnName) references $dbTableMetaForeignTable($dbTableMetaColumnName)";
						$result=$this->updateDbTableDoQuery($query,'util',&$base);			
					}
					// - key
					if ($dbTableMetaKey){
						$query="alter table $dbTableName add primary key($dbTableMetaColumnName)";
						$result=$this->updateDbTableDoQuery($query,'util',&$base);
					}
				}
			}
		} // end foreach
		$base->DebugObj->printDebug("-rtn:updateDbTable",0);
	}
//=======================================
	function fixHtmlElement($base){
		$query="select * from htmlelementprofile order by htmlprofileid, htmlelementname";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$workAry=$base->UtlObj->tabletoHashAry($result);
		$oldHtmlProfileId='new';
		$oldHtmlElementName='new';
		foreach ($workAry as $key=>$valueAry){
			$htmlProfileId=$valueAry['htmlprofileid'];
			$htmlElementName=$valueAry['htmlelementname'];
			$htmlElementProfileId=$valueAry['htmlelementprofileid'];
			if ($htmlProfileId != $oldHtmlProfileId || $htmlElementName!=$oldHtmlElementName){
				$oldHtmlProfileId=$htmlProfileId;
				$oldHtmlElementName=$htmlElementName;
				echo "<br> save $key, $htmlElementProfileId, $htmlProfileId, $htmlElementName";
			}
			else {
				echo "<br> delete $key, $htmlElementProfileId, $htmlProfileId, $htmlElementName";
			}				
		}	
	}
//=======================================
	function buildMySqlScripts($base){
		$base->DebugObj->printDebug("Plugin001Obj:buildMySqlScripts('base')",0); //xx (h)
		//$base->DebugObj->printDebug($base->paramsAry,1,'par');//xxx
		$buildScript=NULL;
		for ($ctr=1;$ctr<50;$ctr++){
			$tableReferenceName='table'.$ctr;
			$tableName=$base->paramsAry[$tableReferenceName];
			if ($tableName != NULL){
				$workAry=$this->buildMySqlComponents($tableName,&$base);
				//below write out the file data script and concat the create table script	
				$thisBuildScript=$workAry['createdbscript'];
				$buildScript.="\n".$thisBuildScript;
				$theData=$workAry['thedata'];
				$path="/home/jeff/web/tomysql/$tableName".'.dta';
				$base->FileObj->writeFile($path,$theData,&$base);
			}	
		}
		$path="/home/jeff/web/tomysql/createtables.txt";
		$base->FileObj->writeFile($path,$buildScript,&$base);
		$base->DebugObj->printDebug("-rtn:buildMySqlScripts",0);
	}
//=======================================
	function buildMySqlComponents($dbTableName,&$base){
		$base->DebugObj->printDebug("Plugin001Obj:buildMySqlComponents($tableName,'base')",0); //xx (h)
		//- get table setup
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		//- build drop, create, load script
		$createDbTable=$this->buildCreateTable($dbControlsAry,&$base);
		$newLine="\n";
		$createDbTableScript="drop table $dbTableName;";
		$createDbTableScript.="$newLine$createDbTable;";
		$dataPath="/home/jeff/web/tomysql/$dbTableName.dta";
		$insertData="LOAD DATA LOCAL INFILE '$dataPath' INTO TABLE $dbTableName";
		$createDbTableScript.="$newLine$insertData;";
		$returnAry=array('createdbscript'=>$createDbTableScript);
		//- get data
		$theData=$this->getTableDataForMySql($dbControlsAry,&$base);
		$returnAry['thedata']=$theData;
		return $returnAry;
		$base->DebugObj->printDebug("-rtn:buildMySqlComponents",0);
	}
//========================================
	function getTableDataForMySql($dbControlsAry,&$base){
		$dbTableName=$dbControlsAry['dbtablename'];
		$query="select * from $dbTableName";
		$result=$base->DbObj->queryTable($query,'read',&$base);	
		$dataString=$base->UtlObj->tableToString($result,&$base);
		return $dataString;
	}
//========================================
	function buildCreateTable($dbControlsAry,&$base){
		$base->DebugObj->printDebug("Plugin001Obj:buildMySqlComponents($tableName,'base')",0); //xx (h)
		$dbTableName=$dbControlsAry['dbtablename'];
		//$base->DebugObj->printDebug($dbControlsAry,1,'dba');//xxx
		$sqlString="create table $dbTableName (";
		$insComma=NULL;
		foreach ($dbControlsAry['dbtablemetaary'] as $colName=>$colValueAry){
			$colType=$colValueAry['dbtablemetatype'];
			$colForeignKey_file=$colValueAry['dbtablemetaforeignkey'];
			$colForeignField_file=$colValueAry['dbtablemetaforeignfield'];
			$colForeignKey=$base->UtlObj->returnFormattedData($colForeignKey_file,'boolean','internal');
			$colForeignField=$base->UtlObj->returnFormattedData($colForeignField_file,'boolean','internal');
			if ($colName == $dbTableName.'id'){$colType='serial';}
			if (!$colForeignField){
			switch ($colType){
			case 'varchar':
				$insString="$colName varchar(200)";
				break;
			case 'numeric':
				$insString="$colName numeric";
				break;	
			case 'boolean':
				$insString="$colName numeric";
				break;
			case 'serial':
				$insString="$colName mediumint not null auto_increment";
				break;
			case 'date':
				$insString="$colName date";
			default:
				$insString="$colName $colType ???";
			} // end switch
			$sqlString.="$insComma\n $insString";
			$insComma=',';
			} // end !foreignfield
		}
		$keyName=$dbControlsAry['keyname'];
		$sqlString.="$insComma\n primary key($keyName)";
		$sqlString.=')';
		//echo "sqlstring: $sqlString<br>";//xxx
		return $sqlString;
		$base->DebugObj->printDebug("-rtn:buildMySqlComponents",0);
	}
//=======================================
	function toTableFromDbTable($base){
		$base->DebugObj->printDebug("debug001Obj:updateDbTable",0); //xx (h)
		$tableProfileId=$base->paramsAry['tableprofileid'];
		//echo "tableprofileid: $tableProfileId";//xxx
		if ($tableProfileId != NULL){
			$query="select * from tableprofile where tableprofileid=$tableProfileId";	
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>'tableprofileid');
			$thisTableAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$query="select * from columnprofile where tableprofileid=$tableProfileId";	
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>'columnname');
			$thisColumnAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$dbTableName=$thisTableAry[$tableProfileId]['dbtablename'];
			$dbControlsAry=array('dbtablename'=>$dbTableName);
			$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
			$dbTableMetaAry=$dbControlsAry['dbtablemetaary'];
			//$operationName=$thisTableAry[$tableProfileId]['formoperation'];
			$dbControlsAry=array();
			$dbControlsAry['writerowsary']=array();
			$dbControlsAry['dbtablename']='columnprofile';
			$columnCtr=0;
			foreach ($dbTableMetaAry as $columnName=>$columnAry){
				if (!array_key_exists($columnName,$thisColumnAry)){
						$columnName=$columnAry['dbcolumnname'];
						$columnType=$columnAry['dbcolumntype'];
						$columnTitle=$columnName;
						$chkBadPos=strpos(('chk'.$columnName),'bad',0);
						$newLineAry=array('tableprofileid'=>$tableProfileId);
						$newLineAry['columnno']=999;
						$newLineAry['rowno']=1;
						$newLineAry['columnname']=$columnName;
						$newLineAry['columntype']='text';
						$newLineAry['columntitle']=$columnName;
						$dbControlsAry['writerowsary'][]=$newLineAry;
						$columnCtr++;
					} // end if
			} // end foreach
			//$base->DebugObj->printDebug($dbControlsAry,1,'doit');//xxx
			if ($columnCtr>0){
				$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
				//$base->DebugObj->printDebug($dbControlsAry,1,'dbc');//xxx	
			}
		} // end if
		$base->DebugObj->printDebug("-rtn:updateDbTable",0);
	}
//======================================= soon to be deprecated
	function updateUserSession($base){
		$base->DebugObj->printDebug("Plugin001Obj:updateUserSession",0); //xx (h)
		$userName=$base->paramsAry['username'];
		$userPassword=$base->paramsAry['userpassword'];
		$userPassword=str_replace("\n",'',$userPassword);
		$userPassword=str_replace("\r",'',$userPassword);
		$userPassword=str_replace("\0",'',$userPassword);
		//echo "pluginoo1Obj.updateusersession: name: $userName, pwd: $userPassword<br>";//xxxf
		$_SESSION['userobj']->setUserFields($userName,$userPassword,&$base);//xxx temp override
/*
		$formName=$base->paramsAry['form'];
		$redirectJob=$base->formProfileAry[$formName]['redirect'];
		$jobLocal=$base->systemAry['joblocal'];
		$redirectPath="$jobLocal$redirectJob";
		$query="select * from userprofileview where username = '$userName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$userProfileAry=$base->UtlObj->tableRowToHashAry($result,$passAry,&$base);
		$checkPassword=$userProfileAry['userpassword'];
		$logName="security.log";
		if ($userPassword == $checkPassword){
			$logMsg="user logged in: $userName";
			$base->FileObj-> writeLog($logName,$logMsg,&$base);
			$_SESSION['userobj']->updateCurrentUserAry($userProfileAry);
			//$base->DebugObj->printDebug($userProfileAry,1,'upa');//xxx	
			$base->paramsAry['userflag']='doit';
			$deptAry=$base->InitObj->getDeptProfile(&$base);
			$_SESSION['userobj']->updateCurrentDeptAry($deptAry);
			//--- redirect
			$urlReDir=$redirectPath;
			$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
			$base->DebugObj->printDebug("-rtn:DbObj:deleteDbFromForm",0); //xx (f)
			header("Location: $urlReDir_formatted");
		}
		else {
			$logMsg="$domainName incorrect password entered for $userName: $userPassword";
			$base->FileObj-> writeLog($logName,$logMsg,&$base);
		}
*/
		$base->DebugObj->printDebug("-rtn:updateUserSession",0);
	}
//=======================================
	function testImageResize($base){
		$imageWidth=$base->paramsAry['imagewidth'];
		$imageHeight=$base->paramsAry['imageheight'];
		$imagePath=$base->paramsAry['imagepath'];
		if (is_file($imagePath)){
			$imageWork = new Imagick($imagePath);
			$toPath="images/tmp/testresize.jpg";
			$oldImageWidth=$imageWork->getImageWidth();
			$oldImageHeight=$imageWork->getImageHeight();
			if (($imageWidth<$oldImageWidth || $oldImageWidth==0) && ($imageHeight<$oldImageHeight || $imageHeight==0)){
				$imageWork->thumbnailImage($imageWidth, $imageHeight);
				$endingMsg=NULL;				
			}
			else {$endingMsg=" unchanged";}
			$newImageWidth=$imageWork->getImageWidth();
			$newImageHeight=$imageWork->getImageHeight();
			$imageWork->writeImage($toPath);
			$imageWork->destroy();
		}
		else {$base->errorProfileAry['errormsg']="not a valid path";}		
	}
//=======================================
	function insertCalendar($paramFeed,$base){
		$calendarAry=array();
		$returnAry=array();
		$calendarName=$paramFeed['param_1'];
		$calendarDbTableName=$base->calendarAry[$calendarName]['dbtablename'];
		$calendarType=$base->calendarAry[$calendarName]['calendartype'];
		switch ($calendarType){
			case 'fulldetail':
				$returnAry=$this->insertCalendarFullDetail($calendarName,&$base);
			break;
			case 'smallnodetail':
				$returnAry=array();		
			break;	
			default:
				$returnAry=array();
		}
		return $returnAry;
	}
//=======================================
	function insertCalendarFullDetail($calendarName,$base){
		$calendarAry=array();	
		$dbTableName=$base->calendarAry[$calendarName]['dbtablename'];
		$query="select * from $dbTableName where visibility=true";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		foreach ($workAry as $ctr=>$dateAry){
			$startDate=$dateAry['startdate'];
			$endDate=$dateAry['enddate'];
			$category=$dateAry['category'];
			$message=$dateAry['message'];			
		}
	}
//========================================
	function dbTableToAscii($base){
//- get dbtableprofile defs
		$dbTableName='dbtableprofile';
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbTableDefs=$dbControlsAry['dbtablemetaary'];
//- get dbcolumnprofile defs
		$dbTableName='dbcolumnprofile';
		$dbControlsAry=array('dbtablename'=>$dbTableName);
		$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
		$dbColumnDefs=$dbControlsAry['dbtablemetaary'];
//- get dbtableprofile
		$dbTableName=$base->paramsAry['dbtablename'];
		$query="select * from dbtableprofile where dbtablename='$dbTableName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dbTablesAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//$base->DebugObj->printDebug($dbTablesAry,1,'xxxe');
//- get dbcolumnprofile
		$query="select * from dbcolumnprofileview where dbtablename='$dbTableName'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$dbColumnsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);	
//- build ascii string
		$buildStrg=NULL;
		$buildSql="insert into dbtableprofile (";
		$buildColumns=NULL;
		$buildValues=NULL;
		foreach ($dbTablesAry as $ctr=>$dbTableAry){
      		foreach ($dbTableAry as $fieldName=>$fieldValue){
        		$fieldType=$dbTableDefs[$fieldName]['dbcolumntype'];
        		$isKey=$dbTableDefs[$fieldName]['dbcolumnkey'];
        		$isForeignKey=$dbTableDefs[$fieldName]['dbcolumnforeignkey'];
        		$isForeignField=$dbTableDefs[$fieldName]['dbcolumnforeignfield'];
        		if ($isForeignKey != 't' && $isKey != 't' && $isForeignField != 't'){
          			echo "$fieldName: $fieldValue, type: $fieldType, key: $isKey, fkey: $isForeignKey, ffield: $isForeignField<br>";//xxxe
		        	$fieldValue_sql=$base->UtlObj->returnFormattedData($fieldValue,$fieldType,'sql');
          			if ($buildColumns==NULL){
            			$buildColumns=$fieldName;
            			$buildValues=$fieldValue_sql;
          			}
          			else {
            			$buildColumns.=",$fieldName";
            			$buildValues.=",$fieldValue_sql";
          			} // end else
        		} // end if isfore...
      		} // end foreach table
		} // end foreach tables
		$buildSql.="$buildColumns) values ($buildValues)";
	}
//=======================================
	function insertMeta($paramFeed,$base){
		$jobName=$base->jobProfileAry['jobname'];
		$jobParentName=$base->jobProfileAry['jobparentname'];
		if ($jobParentName != NULL){$parentNameInsert=" or jobname='$jobParentName'";}
		else {$parentNameInsert=NULL;}
		$query="select * from metaprofileview where jobname='$jobName' $parentNameInsert";	
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$metasAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$returnAry=array();
		foreach ($metasAry as $ctr=>$metaAry){
			$metaName=$metaAry['metaname'];
			$metaContent=$metaAry['metacontent'];
			$metaLine="<meta name=\"$metaName\" content=\"$metaContent\" />\n";
			$returnAry[]=$metaLine;	
		}
		return $returnAry;
	}
//======================================= 
	function duplicateElement($base){
		$elementType=$base->paramsAry['elementtype'];
		switch ($elementType){
			case 'table':
			$parentId_key='tableprofileid';
			$childId_key='columnprofileid';
			$parentTableName='tableprofile';
			$childTableName='columnprofile';
			$parentName_key='tablename';
			$newParentName_key='newtablename';
			$doit=true;
			break;
			default:
			$doit=false;
		}
//- get old tableprofile
			$parentId=$base->paramsAry[$parentId_key];
			$newParentName=$base->paramsAry[$newParentName_key];
			if ($parentId == NULL || $newParentName == NULL){exit();}
			$query="select * from $parentTableName where $parentId_key=$parentId";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$writeRowsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- modify for new insert
			foreach ($writeRowsAry as $ctr=>$theAry){
				unset($writeRowsAry[$ctr][$parentId_key]);
				$writeRowsAry[$ctr][$parentName_key]=$newParentName;
			}
//- write new tableprofile
			$dbControlsAry=array('dbtablename'=>$parentTableName);
    	$dbControlsAry['writerowsary']=$writeRowsAry;
    	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
//- get new tableprofileid just written
			$query="select * from $parentTableName where $parentName_key='$newParentName'";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array('delimit1'=>$parentName_key);
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
			$newParentId=$workAry[$newParentName][$parentId_key];
//- get old columnprofile
			$query="select * from  $childTableName where $parentId_key=$parentId";
			$result=$base->DbObj->queryTable($query,'read',&$base);
			$passAry=array();
			$writeRowsAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//- modify for new insert
			foreach ($writeRowsAry as $ctr=>$rowAry){
				unset($writeRowsAry[$ctr][$childId_key]);
				$writeRowsAry[$ctr][$parentId_key]=$newParentId;
			}
//- write new columnprofile
			$dbControlsAry=array('dbtablename'=>$childTableName);
    	$dbControlsAry['writerowsary']=$writeRowsAry;
    	$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
	}
//=======================================
  function buildImagePathList($base){
    $usrAry=$_SESSION['userobj']->getCurrentUserAry();
    // - base has to be either:
    // - lindy: /home/jeff/web/images
    // - hub: /usr/local/www/jeffreypomeroy.com/www/images
	$systemAry=$base->ClientObj->getSystemData(&$base);
	$domainName=$systemAry['domainname'];
	$pos=strpos('x'.$domainName,'lindy',0);
	if ($pos>0){$imageBase='/home/jeff/web/images';}
	else {$imageBase='/usr/local/www/jeffreypomeroy.com/www/images';}
    //$base->DebugObj->printDebug($usrAry,1,'xxx');
    $domainNameAry=explode('.',$domainName);
    //- can have many companies on a db, but only one is the parent company as found in the domain name
    $useCompanyName=$domainNameAry[0];
    $useCompanyName='urbanecosystems';
    //- dont use below one
    //$companyName=$usrAry['profile']['companyname'];
    $bashCmd="cd $imageBase; find $useCompanyName -type d -print| grep -v \"raw\"|grep -v \"thumbnails\"| sort ";
    //echo "bashcmd: $bashCmd<br>";//xxxf
    $passAry=array('pluginargs'=>$bashCmd);
    $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
    $writeRowsAry=array();
	//$base->DebugObj->printDebug($bashResultsAry,1,'xxxd');
    foreach ($bashResultsAry['outputformatted'] as $ctr=>$thePathAry){
      $updateAry=array('variablepromptsname'=>$useCompanyName);
      $thePath=$thePathAry[0];
      if ($thePath != NULL){
        $updateAry['variablepromptslabel']=$thePath;
        $updateAry['variablepromptsvalue']=$thePath;
        $writeRowsAry[]=$updateAry;
      }
    }
    $dbControlsAry['writerowsary']=$writeRowsAry;
    $dbControlsAry['dbtablename']='variablepromptsprofile';
    $query="delete from variablepromptsprofile where variablepromptsname='$useCompanyName'";
    $result=$base->DbObj->queryTable($query,'delete',&$base);
    //echo "query: $query<br>";
    //$base->DebugObj->printDebug($dbControlsAry,1,'xxxd');
    $howItWent=$base->DbObj->writeToDb($dbControlsAry,&$base);
  }
//=======================================
  function createDirectory($base){
    $basePath=$base->paramsAry['basepath'];
    $newDir=$base->paramsAry['dirname'];
 	$systemAry=$base->ClientObj->getSystemData(&$base);
	$domainName=$systemAry['domainname'];
	if ($domainName == 'lindy'){$imageBase='/home/jeff/web/images';}
	else {$imageBase='/usr/local/www/jeffreypomeroy.com/www/images';}
    if ($newDir != NULL && $basePath != NULL){
      $newDirPath="$imageBase/$basePath/$newDir";
      $bashCmd="mkdir $newDirPath";
      $passAry=array('pluginargs'=>$bashCmd);
      $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
      $bashCmd="chmod 777 $newDirPath";
      $passAry=array('pluginargs'=>$bashCmd);
      $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
      $bashCmd="mkdir $newDirPath/raw";
      $passAry=array('pluginargs'=>$bashCmd);
      $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
      $bashCmd="mkdir $newDirPath/thumbnails";
      $passAry=array('pluginargs'=>$bashCmd);
      $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
      $bashCmd="chmod 777 $newDirPath/*";
      $passAry=array('pluginargs'=>$bashCmd);
      $bashResultsAry=$base->Plugin002Obj->runBashCommand($passAry,&$base);
      $base->Plugin001Obj->buildImagePathList(&$base);
    }
  }
//=======================================
	function sendEmailFromForm($base){
		$theSubject=$base->paramsAry['thesubject'];
 		$theMessage=$base->paramsAry['themessage'];
 		$base->UtlObj->sendMail('jay@urbanecosystems.net',$theSubject,$theMessage,&$base);     	
	}
//========================================
	function updateDbFromBlog($base){
		$base->DebugObj->printDebug("Plugin001Obj:updateDbFromBlog('base')",0);
		$dateColumnName=$base->paramsAry['datecolumnname'];
		$dateColumnValue=$base->paramsAry[$dateColumnName];
		$messageColumnName=$base->paramsAry['messagecolumnname'];
		$messageColumnValue=$base->paramsAry[$messageColumnName];
		$keyColumnName=$base->paramsAry['keycolumnname'];
		$formName=$base->paramsAry['form'];
    	$dbTableName=$base->formProfileAry[$formName]['tablename'];
 		if ($dateColumnValue != null){
			$theSql="select $keyColumnName,$messageColumnName from $dbTableName where $dateColumnName='$dateColumnValue'"; 
			$result=$base->DbObj->queryTable($theSql,'read',&$base);
			$passAry=array();
			$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry,&$base);	
		}
		else {$workAry=array();}
		$theTimeAry=localtime(time(),true);
		$systemAry=$base->ClientObj->getSystemData($base);
		$hourAdj=$systemAry['houradj'];
		$theHour=$theTimeAry['tm_hour'];
		$theHour=$theHour+$hourAdj;
		$theMin=$theTimeAry['tm_min'];
		$theMin=substr('0'.$theMin,-2,2);
		$theTime=$theHour.':'.$theMin;
		//$base->DebugObj->printDebug($theTimeAry,1,'xxx');
		$theMessage=$workAry[0][$messageColumnName];
		$theMessage.="%br%$theTime - ";
		$theMessage.="$messageColumnValue";
		$theKey=$workAry[0][$keyColumnName];
		$redir=$base->formProfileAry[$formName]['redirect'];
		if ($redir == NULL){$dontReDirect=true;}
		else {$dontReDirect=false;}
		$jobLocal=$base->systemAry['joblocal'];
		if (substr($redir,0,3) == 'http'){ $urlReDir=$redir;}
		else {$urlReDir="$jobLocal$redir";}
		$writeRowsAry=array();
		if ($theKey != null){$writeRowsAry[0][$keyColumnName]=$theKey;}
	 	$writeRowsAry[0][$dateColumnName]=$dateColumnValue;
	 	$writeRowsAry[0][$messageColumnName]=$theMessage;
	 	//$base->DebugObj->printDebug($writeRowsAry,1,'xxx');
//--- call write
		$dbControlsAry['writerowsary']=$writeRowsAry;
		$dbControlsAry['dbtablename']=$dbTableName;
		$successfulUpdate=$base->DbObj->writeToDb($dbControlsAry,&$base);
		//$successfulUpdate=true;
		if (!$successfulUpdate){$allSuccessfulUpdate=false;}
//--- redirect
		if ($successfulUpdate && !$dontReDirect) {
			$pos=strpos('x'.$urlReDir,'sessionname',0);
			if ($pos<=0){
				$sessionName=$base->paramsAry['sessionname'];
				if ($sessionName != NULL){$urlReDir.="&sessionname=$sessionName&donterase=1";}
			}
			$urlReDir_formatted=$base->UtlObj->returnFormattedString($urlReDir,&$base);
			$base->UtlObj->appendValue('debug',"header to $urlReDir_formatted<br>",&$base);
			header("Location: $urlReDir_formatted");
		}
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
