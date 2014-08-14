<?php
class OperPlugin002Object {
	// 1/20/13 inserttoalbum changes but possibly not
	// 1/29/13 batchtransactions fix for bad date which I shouldnt have had to do
	// put in thishadtobebad
	var $statusMsg;
	var $callNo = 0;
	var $dataMoveObj = '';
	// ====================================================
	function incCalls() {
		$this->callNo ++;
	}
	// ====================================================
	function OperPlugin002Object() {
		$this->incCalls ();
		$this->statusMsg = 'tag Object is fired up and ready for work!';
	}
	// ====================================================
	function insertImageToAlbumthishastobebad($base) {
		// echo "xxxf";
		$base->DebugObj->printDebug ( $base->paramsAry, 1, 'xxxf' );
	}
	// ====================================================
	function buildAlbumReport($base) {
		$base->FileObj->initLog ( 'jefftest', &$base );
		$colon = '&#58;';
		$thePipe = '&#124;';
		$localPath = $base->ClientObj->getBasePath ( &$base );
		$reportLoadId = $base->paramsAry ['loadid'];
		if ($reportLoadId == null) {
			$reportLoadId = 'albumreporthdcontentid';
		}
		// --- break out senddata
		$sendData = $base->paramsAry ['senddata'];
		$workAry = explode ( '`', $sendData );
		$theCnt = count ( $workAry );
		$paramsUseAry = $base->paramsAry;
		for($lp = 0; $lp < $theCnt; $lp ++) {
			$workVar = $workAry [$lp];
			$workVarAry = explode ( '|', $workVar );
			$paramsName = $workVarAry [0];
			$paramsValue = $workVarAry [1];
			$paramsUseAry [$paramsName] = $paramsValue;
		}
		$albumProfileId = $paramsUseAry ['albumprofileid'];
		$reportType = $paramsUseAry ['reporttype'];
		// $reportType='otherpictures';//xxxf22
		if ($reportType == null) {
			$reportType = 'totals';
		}
		// --- get all album info
		$query = "select * from albumprofileview where albumprofileid=$albumProfileId";
		$passAry = array ();
		$result = $base->DbObj->queryTable ( $query, 'select', &$base );
		$albumAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
		$albumImageLength = $albumAry [0] ['albumimagelength'];
		$albumImageSettings = $albumAry [0] ['albumimagesettings'];
		switch ($albumImageSettings) {
			case 'setheight' :
				$maxHeight = $albumImageLength;
				$maxWidth = 0;
				break;
			case 'setwidth' :
				$maxHeight = 0;
				$maxWidth = $albumImageLength;
				break;
			default :
				$maxHeight = 0;
				$maxWidth = 0;
		}
		// $base->DebugObj->printDebug($albumAry,1,'xxxf');
		$query = "select * from pictureprofileview where albumprofileid=$albumProfileId order by pictureno";
		$result = $base->DbObj->queryTable ( $query, 'select', &$base );
		$base->FileObj->writeLog ( 'jefftest', "query: $query", &$base );
		$pictureAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
		$pictureNamesAry = array ();
		foreach ( $pictureAry as $id => $thePictureAry ) {
			$pictureName = $thePictureAry ['picturename'];
			$pictureFileName = $thePictureAry ['picturefilename'];
			$pictureNamesAry [$pictureFileName] = $pictureName;
		}
		// --- get all directory info
		$albumDirectory = $albumAry [0] ['albumdirectory'];
		$albumDirectoryFullPath = $localPath . '/' . $albumDirectory;
		$base->FileObj->writeLog ( 'jefftest', "albumdirectoryfullpath: $albumDirectoryFullPath", &$base );
		if (is_dir ( $albumDirectoryFullPath )) {
			$base->FileObj->writeLog ( 'jefftest', "it is a directory", &$base );
			$totalSizeInAlbum;
			$totalCntInAlbum;
			$totalSizeNotInAlbum;
			$totalCntNotInAlbum;
			$theDir = opendir ( $albumDirectory );
			$otherFileNamesAry = array ();
			$base->FileObj->writeLog ( 'jefftest', "loop thru dir", &$base );
			$ctr = 0;
			$itemList = '';
			while ( ($theFileName = readdir ( $theDir )) !== false ) {
				$ctr ++;
				if ($ctr > 10) {
					$base->FileObj->writeLog ( 'jefftest', "item list: $itemList", &$base );
					$ctr = 0;
					$itemList = '';
				}
				$itemList .= $theFileName . ',';
				$fileSize = filesize ( "$albumDirectory/$theFileName" );
				if (array_key_exists ( $theFileName, $pictureNamesAry )) {
					$totalSizeInAlbum += $fileSize;
					$totalCntInAlbum ++;
				} else {
					if (is_file ( "$albumDirectoryFullPath/$theFileName" )) {
						$totalSizeNotInAlbum += $fileSize;
						$totalCntNotInAlbum ++;
						$theFileNameAry = explode ( '.', $theFileName );
						$theFileNameSuffix = $theFileNameAry [1];
						$theFileNameAlone = $theFileNameAry [0];
						$theOtherFileAry = array (
								'otherfilename' => $theFileName,
								'otherfilesize' => $fileSize,
								'otherfilesuffix' => $theFileNameSuffix,
								'otherfilenamealone' => $theFileNameAlone 
						);
						$otherFileNamesAry [$theFileName] = $theOtherFileAry;
					} else {
						$theOtherFileAry = array (
								'otherfilename' => $theFileName,
								'otherfilesize' => 0,
								'otherfilesuffix' => 'directory' 
						);
						$otherFileNameAry [$theFileName] = $theOtherFileAry;
					}
				}
			}
			closedir ( $theDir );
			$albumName = $albumAry [0] ['albumname'];
			$base->FileObj->writeLog ( 'jefftest', "albumname: $albumName, reporttype: $reportType", &$base );
			// --- do the different report types here
			switch ($reportType) {
				case 'totals' :
					// --- album totals
					$avgSizeInAlbum = round ( ($totalSizeInAlbum / $totalCntInAlbum) / 1000, 1 ) . 'k';
					$avgSizeNotInAlbum = round ( ($totalSizeNotInAlbum / $totalCntNotInAlbum) / 1000, 1 ) . 'k';
					$totalSizeInAlbumMeg = round ( ($totalSizeInAlbum / 1000000), 1 ) . 'm';
					$totalSizeNotInAlbumMeg = round ( ($totalSizeNotInAlbum / 1000000), 1 ) . 'm';
					$reportMsg .= "&nbsp;&nbsp;Statistics<br />album: $albumName<br \>directory: $albumDirectory<br \>------------------------------------------------------------<br \>";
					$reportMsg .= "All Pictures within Album<br \>";
					$reportMsg .= "... total size$colon $totalSizeInAlbumMeg<br \>";
					$reportMsg .= "... total cnt$colon $totalCntInAlbum<br \>";
					$reportMsg .= "... avg size$colon $avgSizeInAlbum<br><br \>";
					$reportMsg .= "All Pictures outside of Album<br \>";
					$reportMsg .= "... total size$colon $totalSizeNotInAlbumMeg<br \>";
					$reportMsg .= "... total cnt$colon $totalCntNotInAlbum<br \>";
					$reportMsg .= "... avg size$colon $avgSizeNotInAlbum";
					break;
				case 'albumpictures' :
					// --- display full list of album pictures
					// echo "xxxf1";exit();
					$reportMsg .= "&nbsp;&nbsp;Album Pictures<br />album: $albumName<br \>directory: $albumDirectory<br \>------------------------------------------------------------<br \>";
					$reportMsg .= "<table>\n<tr>\n<th>no</th><th>title</th><th>filename</th><th>size(k)</th><th>width</th><th>height</th><th>type</th>";
					$noPictures = count ( $pictureAry );
					$base->FileObj->initLog ( 'jefftest', &$base );
					for($pLp = 0; $pLp < $noPictures; $pLp ++) {
						$thePictureAry = $pictureAry [$pLp];
						$pictureFileName = $thePictureAry ['picturefilename'];
						$pictureFileNameAry = explode ( '.', $pictureFileName );
						$pictureSuffix = $pictureFileNameAry [count ( $pictureFileNameAry ) - 1];
						$pictureSuffix_lowercase = strtolower ( $pictureSuffix );
						$pos = strpos ( 'jpgpngbmptiff', $pictureSuffix_lowercase, 0 );
						if ($pos > - 1) {
							$isGoodFile = true;
						} else {
							$isGoodFile = false;
						}
						$pictureTitle = $thePictureAry ['picturetitle'];
						$pictureType = $thePictureAry ['picturetype'];
						$picturePath = "$localPath/$albumDirectory/$pictureFileName";
						// $base->FileObj->writeLog('jefftest99',$picturePath,&$base);
						if (file_exists ( $picturePath ) && ! is_dir ( $picturePath ) && $isGoodFile) {
							$thePicture = new Imagick ( $picturePath );
							$pictureFileSize = filesize ( $picturePath );
							$pictureSize = $thePicture->getImageSize ();
							$pictureWidth = $thePicture->getImageWidth ();
							$pictureHeight = $thePicture->getImageHeight ();
							$thePicture->destroy ();
						} else {
							$pictureFileSize = "?";
							$pictureSize = "?";
							$pictureWidth = "?";
							$pictureHeight = "?";
						}
						// - width, height checks
						if ($maxHeight > 0 && $pictureHeight > $maxHeight) {
							$usePictureHeight = "<p class=\"errorcolorrightjust\">$pictureHeight</p>";
						} else {
							$usePictureHeight = "<p class=\"okcolorrightjust\">$pictureHeight</p>";
						}
						if ($maxWidth > 0 && $pictureWidth > $maxWidth) {
							$usePictureWidth = "<p class=\"errorcolorrightjust\">$pictureWidth</p>";
						} else {
							$usePictureWidth = "<p class=\"okcolorrightjust\">$pictureWidth</p>";
						}
						// - active, hidden checks
						if ($pictureType == null) {
							$pictureType = '--none--';
						}
						if ($pictureType == 'active') {
							$usePictureType = "<p class=\"okcolor\">$pictureType</p>";
						} else {
							$usePictureType = "<p class=\"errorcolor\">$pictureType</p>";
						}
						// - picturefilesize
						$usePictureFileSize = "<p class=\"rightjustify\">" . round ( $pictureFileSize / 1000, 1 ) . "</p>";
						$reportMsg .= "<tr><td>$pLp</td><td>$pictureTitle</td><td>$pictureFileName</td><td>$usePictureFileSize</td><td>$usePictureWidth</td><td>$usePictureHeight</td><td>$usePictureType</td></tr>";
					}
					// echo "xxxf2";exit();
					$reportMsg .= "</table>\n";
					break;
				case 'otherpictures' :
					// --- display full list of pictures in directory not associated with an album
					$reportMsg .= "&nbsp;&nbsp;Other Pictures<br />album: $albumName<br \>directory: $albumDirectory<br \>------------------------------------------------------------<br \>";
					$reportMsg .= "<table><tr><th>file name</th><th>size(k)</th><th>type</th><th>width</th><th>height</th></tr>\n";
					foreach ( $otherFileNamesAry as $otherFileName => $otherFileAry ) {
						$otherFileSize = $otherFileAry ['otherfilesize'];
						$otherFileSuffix = $otherFileAry ['otherfilesuffix'];
						$imageSuffixList = 'jpgJPGpngPNGgifGIF';
						$pos = strpos ( $imageSuffixList, $otherFileSuffix, 0 );
						if ($pos > - 1) {
							$picturePath = "$localPath/$albumDirectory/$otherFileName";
							$thePicture = new Imagick ( $picturePath );
							$pictureFileSize = filesize ( $picturePath );
							$pictureSize = $thePicture->getImageSize ();
							$pictureWidth = $thePicture->getImageWidth ();
							$pictureHeight = $thePicture->getImageHeight ();
							$thePicture->destroy ();
							$otherMsg = "<p class=\"okcolor\">image</p>";
						} else {
							$otherMsg = "<p class=\"errorcolor\">other</p>";
							$pictureWidth = '-';
							$pictureHeight = '-';
						}
						$pictureWidthUse = "<p class=\"rightjustify\">$pictureWidth</p>";
						$pictureHeightUse = "<p class=\"rightjustify\">$pictureHeight</p>";
						$useOtherFileSize = "<p class=\"rightjustify\">" . round ( $otherFileSize / 1000, 1 ) . "</p>";
						$base->paramsAry ['picturedirectory'] = $albumDirectory;
						$base->paramsAry ['picturefilename'] = $otherFileName;
						$base->paramsAry ['picturetype'] = 'active';
						$base->paramsAry ['picturename'] = $otherFileNameAlone;
						$insertToAlbumAry = $base->HtmlObj->buildImg ( 'inserttoalbumbutton', &$base );
						// $base->DebugObj->printDebug($base->imageProfileAry,1,'xxxf');
						// foreach ($base->paramsAry as $theName=>$theValue){echo "$theName:$theValue,";}
						// exit();
						$insertToAlbumLine = $insertToAlbumAry [0];
						// $insertToAlbumLine=str_replace("<",'{',$insertToAlbumLine);//xxxf
						// $insertToAlbumLine=str_replace(">",'}',$insertToAlbumLine);//xxxf
						// $insertToAlbumLine='<pre>'.substr($insertToAlbumLine,0,10000).'</pre>';//xxxf
						$deleteAry = $base->HtmlObj->buildImg ( 'deletebutton', &$base );
						$deleteLine = $deleteAry [0];
						$reportMsg .= "<tr><td>$otherFileName</td><td>$useOtherFileSize</td><td>$otherMsg</td>";
						$reportMsg .= "<td>$pictureWidthUse</td><td>$pictureHeightUse</td><td>$insertToAlbumLine</td>";
						$reportMsg .= "<td>$deleteLine</td></tr>";
						$base->FileObj->writeLog ( 'jefftest', "inserttoalbumline: $insertToAlbumLine", &$base );
						// $pos=strpos($reportMsg,':',0);//xxxf
						// $reportMsg = str_replace(":", "%colon%", $reportMsg);
						// $pos2=strpos($reportMsg,':',0);//xxxf
						// $reportMsg="pos: $pos, pos2: $pos2";//xxxf
						// $pos=strpos($reportMsg,':',0);//xxxf
						// $reportMsg="pos: $pos";//xxxf
					}
					$reportMsg .= "</table>";
					$reportMsg = str_replace ( "|", $thePipe, $reportMsg );
					break;
				default :
			}
		} else {
			$reportMsg .= "This album has no directory or the directory is invalid! album directory: $albumDirectory";
			$base->FileObj->writeLog ( 'jefftest', "$reportMsg", &$base );
		}
		// --- end
		echo "okupd|$reportLoadId|$reportMsg";
		// exit();
	}
	// ====================================================
	function removeHiddenAlbumRows($base) {
		// --- break out senddata
		$sendData = $base->paramsAry ['senddata'];
		$workAry = explode ( '`', $sendData );
		$theCnt = count ( $workAry );
		$paramsUseAry = $base->paramsAry;
		for($lp = 0; $lp < $theCnt; $lp ++) {
			$workVar = $workAry [$lp];
			$workVarAry = explode ( '|', $workVar );
			$paramsName = $workVarAry [0];
			$paramsValue = $workVarAry [1];
			$paramsUseAry [$paramsName] = $paramsValue;
		}
		$albumProfileId = $paramsUseAry ['albumprofileid'];
		$query = "delete from pictureprofile where albumprofileid=$albumProfileId and picturetype='hidden'";
		$base->DbObj->queryTable ( $query, 'delete', &$base );
		echo "okdonothing";
	}
	// ====================================================
	function insertToAlbum($base) {
		// --- break out senddata
		$sendData = $base->paramsAry ['senddata'];
		$workAry = explode ( '`', $sendData );
		$theCnt = count ( $workAry );
		$paramsUseAry = $base->paramsAry;
		$dmy = null;
		for($lp = 0; $lp < $theCnt; $lp ++) {
			$workVar = $workAry [$lp];
			$workVarAry = explode ( '|', $workVar );
			$paramsName = $workVarAry [0];
			$paramsValue = $workVarAry [1];
			$paramsUseAry [$paramsName] = $paramsValue;
			$dmy .= $paramsName . ", ";
		}
		$albumProfileId = $paramsUseAry ['albumprofileid'];
		$pictureFileName = $paramsUseAry ['picturefilename'];
		$pictureDirectory = $paramsUseAry ['picturedirectory'];
		// echo "okmsg|albumprofileid: $albumProfileId, picturefilename: $pictureFileName, pictureDirectory: $pictureDirectory";
		$pictureNameAry = explode ( '.', $pictureFileName );
		$pictureName = $pictureNameAry [0];
		$dbControlsAry = array (
				'dbtablename' => 'pictureprofile' 
		);
		$writeRowsAry = array ();
		$theRow = array (
				'albumprofileid' => $albumProfileId,
				'picturefilename' => $pictureFileName,
				'picturedirectory' => $pictureDirectory,
				'picturename' => $pictureName,
				'pictureno' => 9999 
		);
		$writeRowsAry [] = $theRow;
		$dbControlsAry ['writerowsary'] = $writeRowsAry;
		// $base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();
		$successFlg = $base->DbObj->writeToDb ( $dbControlsAry, &$base );
		if ($successFlg) {
			$base->UtlObj->reorderAlbumInt ( $albumProfileId, 5, &$base );
			echo "okdonothing|";
		} else {
			echo "error|";
			$base->ErrorObj->printAllErrors ( &$base );
		}
	}
	// ====================================================
	function getJobObjects($base) {
		// - setup database stuff for the later building of the html
		$base->FileObj->writeLog ( 'debug', "--- enter jobobjects ---", &$base );
		$paramsStrg = "\n-----------start paramsary-------------";
		foreach ( $base->paramsAry as $name => $value ) {
			if ($name == 'senddata') {
				$value = '...';
			}
			$paramsStrg .= "\n$name: $value, ";
		}
		$paramsStrg .= "\n---------end paramsary----------";
		$base->FileObj->writeLog ( 'debug', "getjobobjects(paramsary): $paramsStrg", &$base ); // xxxflog
		$base->Plugin001Obj->updateSession ( &$base );
		$testsavetosession = $base->paramsAry ['savetosession'];
		$testsessionname = $base->paramsAry ['sessionname'];
		$base->FileObj->writeLog ( 'debug', "session name after updatesession: $testsessionname, savetosessionname: $testsavetosession", &$base ); // xxxf
		$fromDomainName = $base->paramsAry ['fromdomainname'];
		$toDomainName = $base->paramsAry ['todomainname'];
		$dbStrg = 'dbstatus: ';
		if ($fromDomainName != NULL) {
			$fromConn = $base->ClientObj->getClientConn ( $fromDomainName, &$base );
			$dbNo = 1;
			$base->System001Obj->setClientConn ( $fromConn, $dbNo, &$base );
			$dbStrg .= " set from to db1 using the name: $fromDomainName. ";
		}
		if ($toDomainName != NULL && $this->toDomainName != 'NULL') {
			$toConn = $base->ClientObj->getClientConn ( $toDomainName, &$base );
			$dbNo = 2;
			$base->System001Obj->setClientConn ( $toConn, $dbNo, &$base );
			$dbStrg .= " set to to db2 using the name: $toDomainName.";
		}
		// - build report if you have domain/company/job
		$fromCompanyProfileId = $base->paramsAry ['fromcompanyprofileid'];
		$fromJobProfileId = $base->paramsAry ['fromjobprofileid'];
		$returnMsg = null;
		// $base->FileObj->writeLog('debug','xxxf3: '.$dbStrg,&$base);//xxxf
		if ($fromDomainName != null && $fromCompanyProfileId != null && $fromJobProfileId != null) {
			$passAry = array ();
			// - containers xxxf22 - work in progress
			$theQuery = "select containername from containerprofileview where jobprofileid=$fromJobProfileId order by containername";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$containerAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - tables
			$theQuery = "select tablename from tableprofileview where jobprofileid=$fromJobProfileId order by tablename";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$tableAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			$base->FileObj->writeLog ( 'debug', 'xxxf3.5: ' . $dbStrg, &$base ); // xxxf
			                                                                     // - forms
			$theQuery = "select formname from formprofileview where jobprofileid=$fromJobProfileId order by formname";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$formAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - css
			$theQuery = "select distinct prefix from cssprofileview where jobprofileid=$fromJobProfileId order by prefix";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$cssAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - menu
			$theQuery = "select menuname from menuprofileview where jobprofileid=$fromJobProfileId order by menuname";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$menuAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - images
			$theQuery = "select imagename from imageprofileview where jobprofileid=$fromJobProfileId order by imagename";
			$result = $base->ClientObj->queryClientDbTable ( $theQuery, $fromConn, 'read', &$base );
			$imageAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
		}
		$toCompanyProfileId = $base->paramsAry ['tocompanyprofileid'];
		$toJobProfileId = $base->paramsAry ['tojobprofileid'];
		$base->FileObj->writeLog ( 'debug', "tojobprofileid: $toJobProfileId", &$base ); // xxxf
		if ($toDomainName != null && $toCompanyProfileId != null && $toJobProfileId != null) {
			$passAry = array ();
			// - containers
			$theQuery = "select containername from containerprofileview where jobprofileid=$toJobProfileId order by containername";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toContainerAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - tables
			$theQuery = "select tablename from tableprofileview where jobprofileid=$toJobProfileId order by tablename";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toTableAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - forms
			$theQuery = "select formname from formprofileview where jobprofileid=$toJobProfileId order by formname";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toFormAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - css
			$theQuery = "select distinct prefix from cssprofileview where jobprofileid=$toJobProfileId order by prefix";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toCssAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - menu
			$theQuery = "select menuname from menuprofileview where jobprofileid=$toJobProfileId order by menuname";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toMenuAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
			// - images
			$theQuery = "select imagename from imageprofileview where jobprofileid=$toJobProfileId order by imagename";
			$result = $base->DbObj->queryTable ( $theQuery, 'read', &$base );
			$toImageAry = $base->UtlObj->tableToHashAryV3 ( $result, $passAry );
		}
		// ===================== build return Message
		$sessionName = $base->paramsAry ['sessionname'];
		// - containers
		$returnMsg .= "<div class =\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Containers</div>";
		$breakCtr = 0;
		$returnMsg .= "<table><tr>";
		foreach ( $containerAry as $ctr => $theContainerAry ) {
			$containerName = $theContainerAry ['containername'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$containerName!objecttype!container|w";
			// $base->FileObj->writeLog('debug',$batchStrg,&$base);//xxxf
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$containerName</span><td>";
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		// - css
		$returnMsg .= "</div><div class=\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Css</div>";
		$returnMsg .= "<table><tr>";
		$breakCtr = 0;
		foreach ( $cssAry as $ctr => $theCssAry ) {
			$prefix = $theCssAry ['prefix'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$prefix!objecttype!css|w";
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$prefix</span><td>";
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		// - forms
		$returnMsg .= "</div><div class=\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Forms</div>";
		$returnMsg .= "<table><tr>";
		$breakCtr = 0;
		foreach ( $formAry as $ctr => $theFormAry ) {
			$formName = $theFormAry ['formname'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$formName!objecttype!form|w";
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$formName</span><td>";
			// $base->FileObj->writeLog('debug',"batch: $batchStrg",&$base);
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		// - menus
		$returnMsg .= "</div><div class=\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Menus</div>";
		$returnMsg .= "<table><tr>";
		$breakCtr = 0;
		foreach ( $menuAry as $theMenuAry ) {
			$menuName = $theMenuAry ['menuname'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$menuName!objecttype!menu|w";
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$menuName</span><td>";
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		// - tables
		$returnMsg .= "</div><div class=\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Tables</div>";
		$returnMsg .= "<table><tr>";
		$breakCtr = 0;
		foreach ( $tableAry as $ctr => $theTableAry ) {
			$tableName = $theTableAry ['tablename'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$tableName!objecttype!table|w";
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$tableName</span><td>";
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		// - images
		$returnMsg .= "</div><div class=\"jobobjectsmain\">";
		$returnMsg .= "<div class=\"jobobjectstitle\">Images</div>";
		$returnMsg .= "<table><tr>";
		$breakCtr = 0;
		foreach ( $imageAry as $ctr => $theImageAry ) {
			$imageName = $theImageAry ['imagename'];
			$batchStrg = "rmo2:clientadminsetup!copyjobobject!sessionname!$sessionName!fromjobprofileid!$fromJobProfileId!tojobprofileid!$toJobProfileId!objectname!$imageName!objecttype!image|w";
			$returnMsg .= "<td><span class=\"jobobjects\" onclick=\"MenuObj.runBatch('$batchStrg');\">$imageName</span><td>";
			$breakCtr ++;
			if ($breakCtr > 2) {
				$returnMsg .= "</tr><tr>";
				$breakCtr = 0;
			}
		}
		$returnMsg .= "</tr></table>";
		$returnMsg .= "</div>";
		// $base->FileObj->writeLog('jeffdebug.txt',$returnMsg,&$base);//xxxf
		// - destination stuff
		$toJobProfileId = $base->paramsAry ['tojobprofileid'];
		$toCompanyProfileId = $base->paramsAry ['tocompanyprofileid'];
		if ($toCompanyProfileId != null && $toJobProfileId != null) {
			// - null
		}
		// $base->FileObj->writeLog('jeffdebug.txt',$returnMsg,&$base);//xxxf
		$base->sentenceProfileAry ['objectlist'] ['objectlist'] ['sentencetext'] = $returnMsg;
	}
	// ====================================================
	function copyJobObject($base) {
		// - convert senddata
		$paramsStrg = "\n-----------start paramsary-------------";
		foreach ( $base->paramsAry as $name => $value ) {
			if ($name == 'senddata') {
				$value = '...';
			}
			$paramsStrg .= "\n$name: $value, ";
		}
		$paramsStrg .= "\n---------end paramsary----------";
		$base->FileObj->writeLog ( 'debug', "copyjobobjects(paramsary): $paramsStrg", &$base ); // xxxflog
		$errorFlg = false;
		$errorMsg = null;
		$objectName = $base->paramsAry ['objectname'];
		$objectType = $base->paramsAry ['objecttype'];
		// - get connects
		$fromDomainName = $base->paramsAry ['fromdomainname'];
		$toDomainName = $base->paramsAry ['todomainname'];
		if ($fromDomainName != NULL) {
			$fromConn = $base->ClientObj->getClientConn ( $fromDomainName, &$base );
		} else {
			$base->FileObj->writeLog ( 'debug', "copyjobobject  fromdomainname: $fromDomainName ", &$base ); // xxxf
			$errorFlg = true;
			$errorMsg = "fromdomainname bad: $fromDomainName";
		}
		if ($toDomainName != NULL) {
			$toConn = $base->ClientObj->getClientConn ( $toDomainName, &$base );
		} else {
			$base->FileObj->writeLog ( 'debug', "copyjobobject todomainname: $toDomainName", &$base ); // xxxf
			$errorFlg = true;
			$errorMsg = "todomainname bad: $toDomainName";
		}
		// - check jobprofileids
		$fromJobProfileId = $base->paramsAry ['fromjobprofileid'];
		$toJobProfileId = $base->paramsAry ['tojobprofileid'];
		if ($objectName == null || $objectType == null) {
			$errorFlg = true;
			$errorMsg = "objectname: $objectName, objecttype: $objectType";
		}
		if ($fromJobProfileId == null) {
			$errorFlg = true;
			$errorMsg = "no fromjobprofileid";
		} else if ($toJobProfileId == null) {
			$errorFlg = true;
			$errorMsg = "no tojobprofileid";
		}
		$base->FileObj->writeLog ( 'debug', "objectname: $objectName, objectype: $objectType, errorflg: $errorFlg, errormsg: $errorMsg", &$base ); // xxxf
		                                                                                                                                           // - doit if no errors
		if (! $errorFlg) {
			$objectType = $base->paramsAry ['objecttype'];
			$objectName = $base->paramsAry ['objectname'];
			$fromJobProfileId = $base->paramsAry ['fromjobprofileid'];
			$toJobProfileId = $base->paramsAry ['tojobprofileid'];
			$passAry = array (
					'fromjobprofileid' => $fromJobProfileId,
					'tojobprofileid' => $toJobProfileId 
			);
			$passAry ['objectname'] = $objectName;
			if ($this->dataMovObj == '') {
				$this->dataMoveObj = new dataMoveObject ( $fromConn, $toConn );
			}
			$base->FileObj->writeLog ( 'debug', "objectname: $objectName, objectype: $objectType, fromconn: $fromConn, toconn: $toConn", &$base ); // xxxf
			switch ($objectType) {
				case 'container' :
					$rtnMsg = $this->dataMoveObj->copyContainer ( $passAry, &$base );
					break;
				case 'css' :
					$rtnMsg = $this->dataMoveObj->copyCss ( $passAry, &$base );
					break;
				case 'form' :
					$rtnMsg = $this->dataMoveObj->copyForm ( $passAry, &$base );
					break;
				case 'menu' :
					$rtnMsg = $this->dataMoveObj->copyMenu ( $passAry, &$base );
					break;
				case 'table' :
					$rtnMsg = $this->dataMoveObj->copyTable ( $passAry, &$base );
					break;
				case 'image' :
					$rtnMsg = $this->dataMoveObj->copyImage ( $passAry, &$base );
					break;
				default :
					echo "error|Invalid objecttype: $objectType";
					exit ();
			}
		}
		$base->FileObj->writeLog ( 'debug', "objecttype: $objectType, rtnmsg: $rtnMsg", &$base ); // xxxf
		$theStatus = "$rtnMsg";
		echo "okupd|jobobjectstatusid|$theStatus";
	}
	// ====================================================
	function writePrintFile($base) {
		// --- break out senddata
		$sendData = $base->paramsAry ['senddata'];
		$workAry = explode ( '`', $sendData );
		$theCnt = count ( $workAry );
		$paramsUseAry = $base->paramsAry;
		for($lp = 0; $lp < $theCnt; $lp ++) {
			$workVar = $workAry [$lp];
			$workVarAry = explode ( '|', $workVar );
			$paramsName = $workVarAry [0];
			$paramsValue = $workVarAry [1];
			$paramsUseAry [$paramsName] = $paramsValue;
		}
		$strg = '';
		foreach ( $paramsUseAry as $name => $value ) {
			$value = substr ( $value, 0, 50 );
			$strg .= ", $name: $value";
		}
		$base->FileObj->writeLog ( 'debug1', $strg, &$base );
		echo "ok";
	}
	// ====================================================
	function getSupportStuff($base) {
		$useParamsAry = $base->AjaxObj->getAllParams ( &$base );
		$reportLoadId = $useParamsAry ['updateid'];
		$supportCode = $useParamsAry ['supportcode'];
		switch ($supportCode) {
			case 'menucodes' :
				$menuProgramCodeAry = $base->FileObj->getFileArray ( '/home/owner/base/lib/includes.js/MenuObject.js' );
				$theMenuLen = count ( $menuProgramCodeAry );

				$menuCodesAry = array ();
				$theStrg = '<table>' . "\n";
				for($menuLp = 0; $menuLp < $theMenuLen; $menuLp ++) {
					$theLine = $menuProgramCodeAry [$menuLp];
					$pos = strpos ( $theLine, '//@', 0 );
					
					if ($pos > - 1) {
						//echo "$pos, $theLine, $menuLp, $theMenuLen\n";//xxxf
						$theLineAry = explode ( ',', $theLine );
						$theCode = $theLineAry [0];
						$theCode = str_replace ( "//@", "", $theCode );
						$theCode = str_replace ( " ", "", $theCode );
						$theExample = $theLineAry [1];
						$theExample = str_replace ( "?", "&#63;", $theExample );
						$theExample = str_replace ( ":", "&#58;", $theExample );
						$theExample = str_replace ( "!", "&#33;", $theExample );
						$theDesc = $theLineAry [2];
						// echo "$theCode, $theExample, $theDesc<br>";//xxxf
						$menuCodeAry = array (
								'desc' => $theDesc,
								'example' => $theExample 
						);
						$menuCodesAry [] = $menuCodeAry;
						$theStrg .= "<tr><td>$theCode</td><td style=\"width:50px;\">$theExample</td><td>$theDesc</td></tr>\n";
					}
				}
				$theStrg .= "</table>\n";
				break;
			case 'TableObject.js' :
			case 'FormObject.js' :
			case 'MenuObject.js' :
			case 'imageObject.js' :
			case 'ContainerObject.js' :
				$thePath = "/home/owner/base/lib/includes.js/$supportCode";
				$programCodeAry = $base->FileObj->getFileArray ( $thePath );
				// $base->FileObj->writeLog('debug1',$programCodeAry[1],&$base);exit();
				$theStrg = "<table>";
				$theCodeLength = count ( $programCodeAry );
				$base->FileObj->writeLog ( 'debug5', 'thecodelength: ' . $theCodeLength, &$base );
				for($theLp = 0; $theLp < $theCodeLength; $theLp ++) {
					$theLine = $programCodeAry [$theLp];
					$theChk = strpos ( $theLine, '?@', 0 );
					if ($theLp > 28 && $theLp < 50) {
						// $base->FileObj->writeLog('debug1','theline: '$theLine,&$base);//xxxf
						$base->FileObj->writeLog ( 'debug5', $theLp . ') thechk... (' . $theChk . '), ' . $theLine, &$base );
					}
					if ($theChk > - 1) {
						$base->FileObj->writeLog ( 'debug1', 'thechk: ' + $theChk, &$base );
						$workAry = explode ( '?@', $theLine );
						$theLine = $workAry [1];
						$theLineAry = explode ( '?C', $theLine );
						$pos = strpos ( $theLine, 'function', 0 );
						if ($pos > - 1) {
							$useLp = $theLp;
							$styleInsert = "style=\"color:black;\"";
						} else {
							$useLp = '';
							$styleInsert = "style=\"color:gray;\"";
						}
						$theStrg .= "<tr><td>$useLp</td><td $styleInsert>$theLineAry[0]</td><td>$theLineAry[1]</td></tr>";
					}
				}
				$theStrg .= "</table>";
				break;
			case 'ascii.txt' :
				$thePath = "/home/owner/jeffstuff/computernotes/$supportCode";
				$theStrgAry = $base->FileObj->getFileArray ( $thePath );
				$theCnt = count ( $theStrgAry );
				for($lp = 0; $lp < $theCnt; $lp ++) {
					$theLine_raw = $theStrgAry [$lp];
					$theLine = str_replace ( '&', '&#38;', $theLine_raw );
					$theStrgAry [$lp] = $theLine;
				}
				$theStrg = implode ( $theStrgAry, '' );
				break;
			default :
				$thePath = "/home/owner/jeffstuff/computernotes/$supportCode";
				$theStrgAry = $base->FileObj->getFileArray ( $thePath );
				$theStrgAryLen = count ( $theStrgAry );
				for($lp = 0; $lp < $theStrgAryLen; $lp ++) {
					$theLine_raw = $theStrgAry [$lp];
					$theLine = str_replace ( '<', '&lt;', $theLine_raw );
					$theLine = str_replace ( '>', '&gt;', $theLine );
					$theLine = str_replace ( '|', '&#124;', $theLine );
					$theStrgAry [$lp] = $theLine;
				}
				$theStrg = implode ( $theStrgAry, "" );
				$theStrg = '<pre>' . $theStrg . '</pre>';
		}
		// $theStrg=count($menuCodesAry);
		// $theStrg=implode('<br>',$menuCodesAry);
		echo "okupd|$reportLoadId|$theStrg";
	}
	// =======================================================
	function updateCsv($base) {
		$base->FileObj->writeLog ( 'updatecsv', "---OperPlugin002Obj.updateCsv---", &$base );
		$strg = '';
		$sendDataAry = $base->UtlObj->breakOutSendData ( &$base );
		foreach ( $sendDataAry as $name => $value ) {
			$strg .= "$name($value),";
		}
		$base->FileObj->writeLog ( 'updatecsv', "xxxd2 senddataary: $strg", &$base );
		$csvPath = $sendDataAry ['csvfilepathid'];
		$csvCode = $sendDataAry ['csvtypeid'];
		$csvLoadId = $sendDataAry ['csvloadidid'];
		//$base->FileObj->writeLog ( 'updatecsv', "xxxd2: csvpath: $csvPath, csvcode: $csvCode, csvloadidid: $csvLoadId", &$base ); // xxxd
		$strg = '';
		// foreach ($base->sentenceProfileAry['uploadfielddefs'] as $name=>$value){$strg.="$name($value)";}
		$theCodeWorkAry = array ();
		$theCodes = $base->sentenceProfileAry ['uploadfielddefs'] [$csvCode] ['sentencetext'];
		//$base->DebugObj->printDebug($base->sentenceProfileAry['uploadfielddefs'],1,'xxxd');//xxxd
		//echo "$csvCode, $theCodes";exit();//xxxd
		//xxxd
		if ($theCodes != '') {
			$theCodes = str_replace ( '??', '?M', $theCodes );
			$theCodesAry = explode ( '?M', $theCodes );
			foreach ( $theCodesAry as $ctr => $codeStrg ) {
				$theCodeAry = explode ( '?:', $codeStrg );
				$theCode = $theCodeAry [0];
				$theCodeValues = $theCodeAry [1];
				$theCodeWorkAry [$theCode] = $theCodeValues;
				$strg .= "$theCode($theCodeValues),";
			}
		}
		$base->FileObj->writeLog ( 'updatecsv', "xxxd3: thecodename: $csvCode, thecodes: $theCodes, strg: $strg", &$base ); // xxxd
		$theColumnNames = $theCodeWorkAry ['columndefs'];
		$theColumnNamesAry = explode ( '?!', $theColumnNames );
		$dbTableName = $theCodeWorkAry ['dbtablename'];
		$accountNameSet = $theCodeWorkAry ['accountname'];
		$accountNameSetAry = explode ( '?!', $accountNameSet );
		$accountColumnName = $accountNameSetAry [0];
		$accountName = $accountNameSetAry [1];
		$startRow = $theCodeWorkAry ['startrow'];
		$theAccountNameFields = $theCodeWorkAry ['accountname'];
		$theAccountNameFieldsAry = explode ( '?!', $theAccountNameFields );
		$theAccountFieldName = $theAccountNameFieldsAry [0];
		$theAccountName = $theAccountNameFieldsAry [1];
		$strg = "codename: $csvCode, dbtablename: $dbTableName, startrow: $startRow, accountfieldname: $theAccountFieldName, accountname: $theAccountName, thecolumnnames: $theColumnNames";
		$base->FileObj->writeLog ( 'updatecsv', "xxxd4: $strg", &$base ); // xxxxd last record
		$csvAry = $base->FileObj->getFileArray ( $csvPath );
		$noLines = count ( $csvAry );
		$strg = "xxxd5: nolines: $noLines,from csvpath($csvPath) now do loop";
		$base->FileObj->writeLog ( 'updatecsv', "xxxd5: $strg", &$base );
		if ($noLines > 0) {
			// xxxd - need to do editting here to make sure it all works
			$theMsg = '';
			$ctr = 0;
			for($workLp = $startRow; $workLp < $noLines; $workLp ++) {
				$entryLine_raw = $csvAry [$workLp];
				$entryLine = str_replace ( '"', '', $entryLine_raw );
				$entryLineAry = explode ( ',', $entryLine );
				$noWords = count ( $theColumnNamesAry );
				$writeRowAry = array ();
				$strg = '';
				for($workLp2 = 0; $workLp2 < $noWords; $workLp2 ++) {
					$theName = $theColumnNamesAry [$workLp2];
					$theName = trim ( $theName );
					$theValue_raw = $entryLineAry [$workLp2];
					$theValue = trim ( $theValue_raw );
					$writeRowAry [$theName] = $theValue;
					// $base->FileObj->writeLog('debug1',"name: $theName, value: $theValue",&$base);//xxxd
				}
				$writeRowAry [$accountColumnName] = $accountName;
				$writeRowsAry [] = $writeRowAry;
				// $testit=$writeRowAry['umpquacredit'];
				// $base->FileObj->writeLog('debug1',"umpquacredit: $testit",&$base);
				$strg = '';
				// wont put in a total null record
				// $base->DebugObj->printDebug($writeRowAry,1,'xxxd');
			}
			$strg = "xxxd6 end of loop";
			$base->FileObj->writeLog ( 'updatecsv', "xxxd7: $strg", &$base );
			$dbControlsAry = array (
					'dbtablename' => $dbTableName,
					'writerowsary' => $writeRowsAry 
			);
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();//xxxf
			$totStrg = '';
			foreach ( $dbControlsAry ['writerowsary'] as $ctr => $anAry ) {
				$strg = '';
				foreach ( $anAry as $name => $value ) {
					$strg .= "$name($value),";
				}
				$strg = substr ( $strg, 0, 40 );
				$totStrg .= "$ctr) $strg\n";
			}
			$strg = '';
			foreach ( $dbControlsAry as $name => $value ) {
				$strg .= "$name($value),";
			}
			$base->FileObj->writeLog ( 'updatecsv', "xxxd8 do write: $strg", &$base );
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();//xxxf
			$successBool = $base->DbObj->writeToDb ( $dbControlsAry, &$base ); // xxxf
			$base->FileObj->writeLog ( 'updatecsv', "xxxd9 ---done with writetodb--- successbool: $successBool", &$base ); // xxxf update failed
			if ($successBool) {
				$theMsg = ($noLines - $startRow) . ' lines updated';
			} else {
				$theMsg = "There were errors in the csvData <br>";
				$theMsg2 = $base->ErrorObj->retrieveAllErrors ( &$base );
				$theMsg = $theMsg . $theMsg2;
			}
		} else {
			$theMsg = "invalid csv path: $csvPath";
		}
		$base->FileObj->writeLog ( 'updatecsv', "xxxd10: $theMsg", &$base );
		$statusKey = 'loadinnerhtml';
		echo "$statusKey|$theMsg|$csvLoadId";
	}
	// ========================================================
	function retrieveCsv($base) {
		$base->FileObj->writeLog ( 'retrievecsv', '!!OperPlugin002Obj.retrieveCsv!!', &$base );
		$paragraphName = $base->paramsAry ['paragraphname'];
		$sentenceName = $base->paramsAry ['sentencename'];
		$csvType = $base->paramsAry ['csvtype'];
		$csvPath = $base->paramsAry ['csvfilepath'];
		$csvAry = $base->FileObj->getFileArray ( $csvPath );
		// doesn't make it here
		$noEntries = count ( $csvAry );
		$base->FileObj->writeLog ( 'retrievecsv', 'csvary noentries: ' . $noEntries, &$base );
		if ($noEntries > 0) {
			if ($csvType != '') {
				$tableDefs = $base->sentenceProfileAry ['uploadfielddefs'] [$csvType] ['sentencetext'];
				$tableDefs = str_replace ( '??', '?M', $tableDefs );
				$tableDefsAry = explode ( '?M', $tableDefs );
				$csvControlDefs = array ();
				foreach ( $tableDefsAry as $ctr => $theValue ) {
					$theValueAry = explode ( '?:', $theValue );
					$csvControlsAry [$theValueAry [0]] = $theValueAry [1];
				}
			}
		}
		$columnDefs = $csvControlsAry ['columndefs'];
		$columnDefsAry = explode ( '?!', $columnDefs );
		$displayStrg = "<table><caption>- settings -</caption>";
		foreach ( $csvControlsAry as $name => $value ) {
			if ($name != 'columndefs') {
				$valueStrg = str_replace ( '?!', ',', $value );
				$displayStrg .= "<tr><td style=\"font-size:14\">$name</td><td style=\"font-size:12\">$value</td></tr> ";
			}
		}
		$displayStrg .= "</table><br><br><br><table><caption>- csv data - </caption><tr>";
		foreach ( $columnDefsAry as $ctr => $columnName ) {
			$displayStrg .= "<td style=\"font-size:14;\">$columnName</td>";
		}
		$csvCnt = count ( $csvAry );
		$csvCntStart = $csvCnt - 3;
		$displayStrg .= "</tr>";
		for($rowLp = 0; $rowLp < 4; $rowLp ++) {
			$csvRow = $csvAry [$rowLp];
			$csvRowAry = explode ( ',', $csvRow );
			$displayStrg .= "<tr>";
			foreach ( $csvRowAry as $ctr => $csvRowValue ) {
				if ($rowLp < 3) {
					$displayStrg .= "<td style=\"font-size:10\">$csvRowValue</td>";
				} else {
					$displayStrg .= "<td>-----</td>";
				}
			}
			$displayStrg .= "</tr>";
		}
		for($rowLp = $csvCntStart; $rowLp < $csvCnt; $rowLp ++) {
			$csvRow = $csvAry [$rowLp];
			$csvRowAry = explode ( ',', $csvRow );
			$displayStrg .= "<tr>";
			foreach ( $csvRowAry as $ctr => $csvRowValue ) {
				$displayStrg .= "<td style=\"font-size:10\">$csvRowValue</td>";
			}
			$displayStrg .= "</tr>";
		}
		$displayStrg .= "</table>";
		$theLen = strlen ( $displayStrg );
		$base->FileObj->writeLog ( 'retrievecsv', "$paragraphName, $sentenceName, $theLen", &$base );
		$base->sentenceProfileAry [$paragraphName] [$sentenceName] ['sentencetext'] = $displayStrg;
	}
	// ========================================================
	function batchUmpquaHistory($base) {
		$base->FileObj->writeLog ( 'debug1', '!!OperPlugin002Obj.batchUmpquaHistory!!', &$base );
		$scanCategoriesAry = $base->sentenceProfileAry ['umpquabatchcategories'];
		$workCategoriesAry = array ();
		foreach ( $scanCategoriesAry as $categoryName => $categoryAry ) {
			$scanValues = $categoryAry ['sentencetext'];
			$scanValuesAry = explode ( ',', $scanValues );
			$workCategoriesAry [$categoryName] = $scanValuesAry;
		}
		$workTypesAry = array ();
		$scanTypesAry = $base->sentenceProfileAry ['umpquabatchtypes'];
		foreach ( $scanTypesAry as $typeName => $typeAry ) {
			$scanValues = $typeAry ['sentencetext'];
			$scanValuesAry = explode ( ',', $scanValues );
			$workTypesAry [$typeName] = $scanValuesAry;
		}
		$query = "select umpquabankhistoryid,umpquacategory, umpquatype, umpquadescription, umpquadate from umpquabankhistoryview where umpquatype = '' or umpquacategory = '' ";
		$passAry = array ();
		$workAry = $base->DbObj->queryTableRead ( $query, $passAry, &$base );
		foreach ( $workAry as $rowCtr => $workRowAry ) {
			$description_raw = $workRowAry ['umpquadescription'];
			$description = strtolower ( $description_raw );
			$iGotIt = false;
			foreach ( $workTypesAry as $workType => $workTypeAry ) {
				if (! $iGotIt) {
					foreach ( $workTypeAry as $ctr => $workTypeScan ) {
						if (! $iGotIt) {
							$pos = strpos ( $description, $workTypeScan, 0 );
							if ($pos > - 1) {
								$workAry [$rowCtr] ['umpquatype'] = $workType;
								$iGotIt = true;
							}
						}
					}
				}
			}
			$iGotIt = false;
			foreach ( $workCategoriesAry as $workCategory => $workCategoryAry ) {
				if (! $iGotIt) {
					foreach ( $workCategoryAry as $ctr => $workCategoryScan ) {
						if (! $iGotIt) {
							$pos = strpos ( $description, $workCategoryScan, 0 );
							if ($pos > - 1) {
								$workAry [$rowCtr] ['umpquacategory'] = $workCategory;
								$iGotIt = true;
							}
						}
					}
				}
			}
			$theCat = $workAry [$rowCtr] ['umpquacategory'];
			$theType = $workAry [$rowCtr] ['umpquatype'];
			$theDate = $workAry [$rowCtr] ['umpquadate'];
			unset ( $workAry [$rowCtr] ['umpquadate'] );
			$base->FileObj->writeLog ( 'debug', "rowctr: $rowCtr, date: $theDate, type: $theType, cat: $theCat, desc: $description", &$base );
		}
		$dbControlsAry = array (
				'dbtablename' => 'umpquabankhistory',
				'writerowsary' => $workAry 
		);
		$successBool = $base->DbObj->writeToDb ( $dbControlsAry, &$base );
		$base->ErrorObj->printAllErrors ( &$base );
		if ($successBool) {
			echo "ok";
		} else {
			echo "error";
		}
	}
	// ========================================================
	function batchTransactions($base) {
		$base->FileObj->writeLog ( 'debug1', '!!OperPlugin002Obj.batchJeffTransactions!!', &$base );
		// - settings
		$settingsParagraphAry = $base->sentenceProfileAry ['settings'];
		$settingsSentenceAry = $settingsParagraphAry ['dbtablename'];
		$dbTableName = $settingsSentenceAry ['sentencetext'];
		if ($dbTableName == NULL) {
			$dbTableName = 'jefftransactions';
		}
		$dbTableNameView = $dbTableName . 'view';
		// - categories
		$scanCategoriesAry = $base->sentenceProfileAry ['transactionbatchcategories'];
		$workCategoriesAry = array ();
		foreach ( $scanCategoriesAry as $categoryName => $categoryAry ) {
			$scanValues = $categoryAry ['sentencetext'];
			$scanValuesAry = explode ( ',', $scanValues );
			$workCategoriesAry [$categoryName] = $scanValuesAry;
		}
		$workTypesAry = array ();
		$scanTypesAry = $base->sentenceProfileAry ['transactionbatchtypes'];
		foreach ( $scanTypesAry as $typeName => $typeAry ) {
			$scanValues = $typeAry ['sentencetext'];
			$scanValuesAry = explode ( ',', $scanValues );
			$workTypesAry [$typeName] = $scanValuesAry;
		}
		$query = "select * from $dbTableNameView where jefftype = '' or jeffcategory = '' ";
		$passAry = array ();
		$workAry = $base->DbObj->queryTableRead ( $query, $passAry, &$base );
		foreach ( $workAry as $rowCtr => $workRowAry ) {
			// - fix date from internal to entered external
			$workDt = $workRowAry ['jeffdate'];
			$workDt_fmt = $base->UtlObj->convertDate ( $workDt, 'date1', &$base );
			$workAry [$rowCtr] ['jeffdate'] = $workDt_fmt;
			// echo "workdtfmt: $workDt_fmt";exit();//xxxf
			$description_raw = $workRowAry ['jeffdesc'];
			$description = strtolower ( $description_raw );
			$iGotIt = false;
			foreach ( $workTypesAry as $workType => $workTypeAry ) {
				if (! $iGotIt) {
					foreach ( $workTypeAry as $ctr => $workTypeScan ) {
						if (! $iGotIt) {
							$pos = strpos ( $description, $workTypeScan, 0 );
							if ($pos > - 1) {
								$workAry [$rowCtr] ['jefftype'] = $workType;
								$iGotIt = true;
							}
						}
					}
				}
			}
			$iGotIt = false;
			foreach ( $workCategoriesAry as $workCategory => $workCategoryAry ) {
				if (! $iGotIt) {
					foreach ( $workCategoryAry as $ctr => $workCategoryScan ) {
						if (! $iGotIt) {
							$pos = strpos ( $description, $workCategoryScan, 0 );
							if ($pos > - 1) {
								$workAry [$rowCtr] ['jeffcategory'] = $workCategory;
								$iGotIt = true;
							}
						}
					}
				}
			}
		}
		// foreach ($workAry as $ctr=>$theAry){
		// $theDate=$theAry['jeffdate'];
		// echo "thedate: $theDate";exit();
		// }
		$dbControlsAry = array (
				'dbtablename' => $dbTableName,
				'writerowsary' => $workAry 
		);
		$successBool = $base->DbObj->writeToDb ( $dbControlsAry, &$base );
		if ($successBool) {
			echo "ok";
		} else {
			echo "error";
		}
		$base->ErrorObj->printAllErrors ( &$base ); // xxxf
	}
	// ====================================================
	function cloneStyleGroup($base) {
		$sendDataAry = $base->UtlObj->breakoutSendData ( &$base );
		$prefix = $sendDataAry ['newprefix'];
		$cssClass = $sendDataAry ['newcssclass'];
		$cssId = $sendDataAry ['newcssid'];
		$htmlTag = $sendDataAry ['newhtmltag'];
		$oldPrefix = $sendDataAry ['oldprefix'];
		$oldCssClass = $sendDataAry ['oldcssclass'];
		$oldCssId = $sendDataAry ['oldcssid'];
		$oldHtmlTag = $sendDataAry ['oldhtmltag'];
		$cssProfileId = $sendDataAry ['cssprofileid'];
		$jobProfileId = $sendDataAry ['jobprofileid'];
		$reportId = $sendDataAry ['reportid'];
		$prelimStrg = "$oldPrefix, $oldCssClass, $oldCssId, $oldHtmlTag => $prefix, $cssClass, $cssId, $htmlTag";
		// check if new clone is already on file
		$getCssIdQuery = "select cssprofileid from cssprofileview where jobprofileid=$jobProfileId and prefix='$prefix' and cssclass='$cssClass' and cssid='$cssId' and htmltag='$htmlTag'";
		$passAry = array ();
		$workAry = $base->DbObj->queryTableRead ( $getCssIdQuery, $passAry, &$base );
		$checkCssProfileId = $workAry [0] ['cssprofileid'];
		if ($cssProfileId == '') {
			$errorMsg = "source style group is not on file";
			$dontClone = true;
		} elseif ($checkCssProfileId != '') {
			$errorMsg = "destination style group is already on file";
			$dontClone = true;
		} else {
			$dontClone = false;
		}
		if (! $dontClone) {
			// create the new cssprofile cloned entry
			$dbControlsAry = array (
					'dbtablename' => 'cssprofile' 
			);
			$writeRowAry = array (
					'jobprofileid' => $jobProfileId,
					'prefix' => $prefix,
					'cssclass' => $cssClass,
					'cssid' => $cssId,
					'htmltag' => $htmlTag 
			);
			$writeRowsAry = array (
					0 => $writeRowAry 
			);
			$dbControlsAry ['writerowsary'] = $writeRowsAry;
			$successfullBool = $base->DbObj->writeToDb ( $dbControlsAry, &$base );
			if ($successfullBool) {
				$passAry = array ();
				$workAry = $base->DbObj->queryTableRead ( $getCssIdQuery, $passAry, &$base );
				$newCssProfileId = $workAry [0] ['cssprofileid'];
				if ($newCssProfileId == '') {
					$errorMsg = "cant create new style group, getcssidquery: $getCssIdQuery";
					$dontConvert = true;
				}
			} else {
				$errorMsg = "error in css style group creation";
				$dontConvert = true;
			}
		}
		if (! $dontConvert) {
			// read/write csselementprofile records
			$query = "select * from csselementprofileview where cssprofileid=$cssProfileId";
			$writeRowsAry = $base->DbObj->queryTableRead ( $query, $passAry, &$base );
			if (count ( $writeRowsAry ) > 0) {
				$elementNameStrg = '';
				$delim = '';
				foreach ( $writeRowsAry as $name => $valueAry ) {
					$elementName = $valueAry ['csselementproperty'];
					$elementNameStrg .= "" . $delim . '--- ' . $elementName;
					$delim = '<br>';
					$writeRowsAry [$name] ['cssprofileid'] = $newCssProfileId;
					unset ( $writeRowsAry [$name] ['csselementprofileid'] );
				}
				$dbControlsAry = array (
						'dbtablename' => 'csselementprofile' 
				);
				$dbControlsAry ['writerowsary'] = $writeRowsAry;
				// $base->DebugObj->printDebug($dbControlsAry,1,'xxxf');
				// exit();
				$base->DbObj->writeToDb ( $dbControlsAry, &$base );
				$errorMsg .= elementNameStrg;
			}
			// read/write csseventprofile records
			$query = "select * from csseventprofileview where cssprofileid=$cssProfileId";
			$writeRowsAry = $base->DbObj->queryTableRead ( $query, $passAry, &$base );
			if (count ( $writeRowsAry ) > 0) {
				$eventNameStrg = '';
				$delim = '';
				foreach ( $writeRowsAry as $name => $valueAry ) {
					$eventName = $valueAry ['csseventtype'];
					$eventNameStrg .= "" . $delim . '--- ' . $eventName . ' ';
					$delim = '<br> ';
					$writeRowsAry [$name] ['cssprofileid'] = $newCssProfileId;
					unset ( $writeRowsAry [$name] ['csseventprofileid'] );
				}
				$dbControlsAry = array (
						'dbtablename' => 'csseventprofile' 
				);
				$dbControlsAry ['writerowsary'] = $writeRowsAry;
				$base->DbObj->writeToDb ( $dbControlsAry, &$base );
			}
		}
		echo "loadinnerhtml|$prelimStrg<br><br>properties cloned<br>$elementNameStrg<br><br>events cloned<br>$eventNameStrg|$reportId";
	}
	// =======================================================
	function buildCssSheets($base) {
		$writePathBase = "/home/jeff/web/includes.css";
		$jobProfileId = $base->paramsAry ['jobprofileid'];
		$jobName = $base->paramsAry ['jobname'];
		$query = "select * from csselementprofileview where jobprofileid=$jobProfileId order by prefix,cssclass,cssid,htmltag";
		$passAry = array ();
		$workAry = $base->DbObj->queryTableRead ( $query, $passAry, &$base );
		$styleSheet = '';
		$oldPrefix = '';
		$oldCss = '';
		$oldId = '';
		$oldHtmlTag = '';
		$cnt = count ( $workAry );
		$lp = 0;
		foreach ( $workAry as $no => $cssAry ) {
			$lp ++;
			$prefix = $cssAry ['prefix'];
			$cssClass = $cssAry ['cssclass'];
			if ($cssClass == 'none') {
				$cssClass = '';
			}
			$cssId = $cssAry ['cssid'];
			if ($cssId == 'none') {
				$cssId = '';
			}
			$htmlTag = $cssAry ['htmltag'];
			if ($htmlTag == 'none') {
				$htmlTag = '';
			}
			$theProperty = $cssAry ['csselementproperty'];
			$theValue = $cssAry ['csselementvalue'];
			$tmpSheet .= "$prefix,$cssClass,$cssId,$htmlTag,$theProperty,$theValue\n";
			if ($prefix != $oldPrefix) {
				$writeName = $jobName . '_' . $oldPrefix . '.css';
				if ($styleSheet != '') {
					$writePath = $writePathBase . '/' . $writeName;
					$base->FileObj->writeFile ( $writePath, $styleSheet, &$base );
				}
				$styleSheet = '';
			} else if ($cssClass != $oldClass && $oldClass != '') {
				if ($propertyStrg != '') {
					$propertyStrg = "$oldHtmlTag" . "." . "$oldClass{\n" . $propertyStrg . "}\n";
					$styleSheet .= $propertyStrg;
					$tmpSheet .= $propertyStrg;
					$propertyStrg = '';
				}
			} else if ($cssId != $oldId && $oldId != '') {
				if ($propertyStrg != '') {
					$propertyStrg = "$oldHtmlTag" . "#" . "$oldId{\n" . $propertyStrg . "}\n";
					$styleSheet .= $propertyStrg;
					$tmpSheet .= $propertyStrg;
					$propertyStrg = '';
				}
			} else if ($htmlTag != $oldHtmlTag && $oldHtmlTag != '') {
				if ($propertyStrg != '') {
					if ($oldClass == '') {
						$useStrg = '#' . $oldId;
					} else {
						$useStrg = '.' . $oldClass;
					}
					$propertyStrg = "$oldHtmlTag" . $useStrg . "{\n" . $propertyStrg . "}\n";
					$styleSheet .= $propertyStrg;
					$tmpSheet .= $propertyStrg;
					$propertyStrg = '';
				}
			}
			$propertyStrg .= $theProperty . ':' . $theValue . ";\n";
			$oldClass = $cssClass;
			$oldId = $cssId;
			$oldHtmlTag = $htmlTag;
			$oldPrefix = $prefix;
		}
		if ($propertyStrg != '') {
			if ($oldClass == '') {
				$useStrg = '#' . $oldId;
			} else {
				$useStrg = '.' . $oldClass;
			}
			$propertyStrg = "$oldHtmlTag" . $useStrg . "{\n" . $propertyStrg . "}\n";
			$styleSheet .= $propertyStrg;
		}
		if ($styleSheet != '') {
			$writeName = $jobName . '_' . $oldPrefix . '.css';
			$writePath = $writePathBase . '/' . $writeName;
			$base->FileObj->writeFile ( $writePath, $styleSheet, &$base );
		}
		echo "ok";
	}
	// ====================================================
	function createCsvData($passedInfoAry, $base) {
		$nameAry = $base->FileObj->getFileArray ( '/home/jeff/etc/csvdatanames.txt' );
		// - payments
		$nameNoMax = count ( $nameAry ) - 2;
		$nameNo = 0;
		$csvFile = '';
		for($yearLp = 2012; $yearLp < 2014; $yearLp ++) {
			for($monthLp = 1; $monthLp < 13; $monthLp ++) {
				for($dayLp = 1; $dayLp < 29; $dayLp ++) {
					$theDate = "$monthLp/$dayLp/$yearLp";
					$theName = $nameAry [$nameNo];
					$theName = trim ( $theName );
					$theNameAry = explode ( '_', $theName );
					$theNameType = $theNameAry [1];
					$theName = $theNameAry [0];
					$nameNo ++;
					if ($nameNo > $nameNoMax) {
						$nameNo = 0;
					}
					$theAmount = rand ( 100, 200000 );
					$theAmount = $theAmount * .01;
					if ($theNameType == 'credit') {
						$theAmount = $theAmount * - 1;
					}
					$theAmount_format = number_format ( $theAmount, 2, '.', '' );
					// echo "amt: $theAmount, amtfmt: $theAmount_format\n";
					$theLine = '"' . $theDate . '","' . $theAmount_format . '"' . ',"*","","' . $theName . '"';
					$csvFile .= "$theLine\n";
				}
			}
		}
		$base->FileObj->writeFile ( "csvdata.csv", $csvFile, &$base );
		echo "done";
		// $base->DebugObj->printDebug($startDateAry,1,'xxxf');
	}
	// ====================================================
	function clearDataTable($base) {
		$sendDataAry = $base->UtlObj->breakOutSendData ( &$base );
		$dbTableName = $sendDataAry ['dbtablename'];
		$dmyTest = strtolower ( $dbTableName );
		$pos = strpos ( $dmyTest, 'demo', 0 );
		if ($pos > - 1) {
			$theMsg = "I will clear $dbTableName";
			if ($dbTableName == 'demotransactions') {
				$query = "delete from $dbTableName";
				$result = $base->DbObj->queryTable ( $query, 'delete', &$base );
			}
		} else {
			$theMsg = "$dbTableName is a restricted table";
		}
		echo "okmsg|$theMsg";
	}
	// ====================================================
	function getByrdlandStuff($base) {
		
		// - basic table setup for byrdlandevents
		$passAry = array ();
		$query = "select * from dbcolumnprofileview where dbtablename='byrdlandevents'";
		$metaDbAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$colAry = array ();
		foreach ( $metaDbAry as $theKey => $theValueAry ) {
			$colName = $theValueAry ['dbcolumnname'];
			$colType = $theValueAry ['dbcolumntype'];
			$colAry [$colName] = $colType;
		}
		$colAry_json = $base->XmlObj->array2Json ( $colAry, &$base );
		
		// - basic table setup for byrdlandyoutube
		
		$query = "select * from dbcolumnprofileview where dbtablename='byrdlandyoutubevideos'";
		$metaDbAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$colYoutubeAry = array ();
		foreach ( $metaDbAry as $theKey => $theValueAry ) {
			$colName = $theValueAry ['dbcolumnname'];
			$colType = $theValueAry ['dbcolumntype'];
			$colYoutubeAry [$colName] = $colType;
		}
		$colYoutubeAry_json = $base->XmlObj->array2Json ( $colYoutubeAry, &$base );
		
		// - basic table setup for byrdlandmembers
		
		$query = "select * from dbcolumnprofileview where dbtablename='byrdlandmembers'";
		$metaDbAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$colMembersAry = array ();
		foreach ( $metaDbAry as $theKey => $theValueAry ) {
			$colName = $theValueAry ['dbcolumnname'];
			$colType = $theValueAry ['dbcolumntype'];
			$colMembersAry [$colName] = $colType;
		}
		$colMembersAry_json = $base->XmlObj->array2Json ( $colMembersAry, &$base );
		
		// - events json
		
		$query = "select * from byrdlandeventsview order by eventdate, eventtime";
		$dbControlsAry = array (
				'dbtablename' => 'byrdlandevents' 
		);
		$base->DbObj->getDbTableInfo ( &$dbControlsAry, &$base );
		$passAry = array (
				'dbcontrolsary' => $dbControlsAry 
		);
		$eventAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$eventAry_json = $base->XmlObj->array2Json ( $eventAry, &$base );
		
		// - youtube json
		$dbControlsAry = array (
				'dbtablename' => 'byrdlandyoutubevideos' 
		);
		$base->DbObj->getDbTableInfo ( &$dbControlsAry, &$base );
		$passAry = array (
				'dbcontrolsary' => $dbControlsAry 
		);
		//
		$query = "select * from byrdlandyoutubevideos where youtubeshow order by youtubedesc";
		$youtubeAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		// $base->DebugObj->printDebug($youtubeAry,1,'xxxf');
		$youtubeAry_json = $base->XmlObj->array2Json ( $youtubeAry, &$base );
		// - byrdlandmembers json
		$passAry = array ();
		$query = "select * from byrdlandmembers order by membersorder";
		$membersAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$membersAry_json = $base->XmlObj->array2Json ( $membersAry, &$base );
		
		// - send string
		
		// - byrdlandevents
		$sendStrg = "chktbl?:byrdlandevents?:CREATE TABLE byrdlandevents (_d INTEGER PRIMARY KEY AUTOINCREMENT, eventdate TEXT NOT NULL,";
		$sendStrg = $sendStrg . " eventtime TEXT NOT NULL, eventdesc TEXT NOT NULL);\n";
		$sendStrg = $sendStrg . "metatbl?:byrdlandevents?:$colAry_json\n";
		$sendStrg = $sendStrg . "clrtbl?:byrdlandevents?:dmy\n";
		$sendStrg = $sendStrg . "instbl?:byrdlandevents?:$eventAry_json\n";
		// -byrdlandyoutube
		$sendStrg = $sendStrg . "drptbl?:byrdlandyoutubevideos?:dmy\n";
		$sendStrg = $sendStrg . "chktbl?:byrdlandyoutubevideos?:CREATE TABLE byrdlandyoutubevideos (_d INTEGER PRIMARY KEY AUTOINCREMENT,";
		$sendStrg = $sendStrg . " youtubedesc TEXT NOT NULL, youtubeid TEXT NOT NULL, youtubeshow boolean);\n";
		$sendStrg = $sendStrg . "metatbl?:byrdlandyoutubevideos?:$colYoutubeAry_json\n";
		$sendStrg = $sendStrg . "clrtbl?:byrdlandyoutubevideos?:dmy\n";
		$sendStrg = $sendStrg . "instbl?:byrdlandyoutubevideos?:$youtubeAry_json\n";
		// -byrdlandmembers
		$sendStrg = $sendStrg . "drptbl?:byrdlandmembers?:dmy\n";
		$sendStrg = $sendStrg . "chktbl?:byrdlandmembers?:CREATE TABLE byrdlandmembers (_d INTEGER PRIMARY KEY AUTOINCREMENT,";
		$sendStrg = $sendStrg . " membersorder INT NOT NULL, membersname TEXT NOT NULL, membersdesc TEXT NOT NULL);\n";
		$sendStrg = $sendStrg . "metatbl?:byrdlandmembers?:$colMembersAry_json\n";
		$sendStrg = $sendStrg . "clrtbl?:byrdlandmembers?:dmy\n";
		$sendStrg = $sendStrg . "instbl?:byrdlandmembers?:$membersAry_json"; // this is null xxxf
		echo "$sendStrg";
	}
	// ====================================================
	function getServerCmds($base) {
		$passAry = array ();
		
		// - loop through mediaprofilemobile, albumprofile tables
		
		$runStrgAry = array (
				"albumprofilemobile",
				"mediaprofilemobile" 
		);
		$tableCnt = count ( $runStrgAry );
		$sendStrg = "";
		$createTableAry = array ();
		for($mobileLp = 0; $mobileLp < $tableCnt; $mobileLp ++) {
			$dbTableName = $runStrgAry [$mobileLp];
			
			// - get table meta
			
			$query = "select * from dbcolumnprofileview where dbtablename='$dbTableName'";
			$metaDbAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
			$colCreateStrgBeg = "";
			$colCreateStrgMid = "";
			$colCreateStrgEnd = "";
			$colAry = array ();
			$foreignFieldAry = array ();
			$foreignKeyAry = array ();
			$theComma = "";
			$theComma2 = "";
			foreach ( $metaDbAry as $theKey => $theValueAry ) {
				// - get the stuff
				
				$colName = $theValueAry ['dbcolumnname'];
				$colType = $theValueAry ['dbcolumntype'];
				
				$colKey = $theValueAry ['dbcolumnkey'];
				
				$colForeignKey_raw = $theValueAry ['dbcolumnforeignkey'];
				$colForeignKey = $base->UtlObj->returnFormattedData ( $colForeignKey_raw, 'boolean', 'internal' );
				$colForeignTableName = $theValueAry ['dbcolumnforeigntable'];
				
				$colForeignField_raw = $theValueAry ['dbcolumnforeignfield'];
				$colForeignField = $base->UtlObj->returnFormattedData ( $colForeignField_raw, 'boolean', 'internal' );
				$colForeignKeyName = $theValueAry ['dbcolumnforeignkeyname'];
				
				$colNotNull = $theValueAry ['dbcolumnnotnull'];
				$colNotNull = $base->UtlObj->returnFormattedData ( $colNotNull, 'boolean', 'internal' );
				
				// - use it
				if ($colNotNull) {
					$colNotNullStrg = " NOT NULL ";
				} else {
					$colNotNullStrg = "";
				}
				// xxxf: need to change below for foreignkeys and foreignfields
				if ($colForeignField) {
					$foreignFieldAry [$colName] = "$colForeignKeyName";
				} else if ($colForeignKey) {
					$foreignKeyAry [$colName] = "$colForeignTableName";
				}
				$colAry [$colName] = $colType;
				
				if (! $colForeignField) {
					switch ($colType) {
						case 'varchar' :
							$colCreateStrgMid .= "$theComma $colName TEXT $colNotNullStrg";
							$theComma = ",";
							break;
						case 'date' :
							$colCreateStrgMid .= "$theComma $colName TEXT $colNotNullStrg";
							$theComma = ",";
							break;
						case 'integer' :
							$colCreateStrgMid .= "$theComma $colName INTEGER ";
							$theComma = ",";
							if ($colForeignKey) {
								$colCreateStrgEnd .= "$theComma $colName references $colForeignTableName($colName)";
								$theComma = ",";
							}
							break;
						case 'numeric' :
							$colCreateStrgMid .= "$theComma $colName TEXT ";
							$theComma = ",";
							break;
						case 'serial' :
							$colCreateStrgBeg = " $colName INTEGER PRIMARY KEY AUTOINCREMENT, ";
							break;
					}
				} else {
					$foreignFieldAry [$colName] = $colForeignKeyName;
				}
			}
			
			$foreignFieldStrg = "";
			$foreignTableStrg = "";
			$daComma = ",";
			foreach ( $foreignFieldAry as $colName => $foreignKeyName ) {
				$foreignTableName = $foreignKeyAry [$foreignKeyName];
				$foreignTableStrg .= "$daComma$foreignTableName";
				$foreignFieldStrg .= " $daComma$foreignTableName.$colName ";
			}
			
			$colCreateStrg = "create table $dbTableName (" . $colCreateStrgBeg . $colCreateStrgMid . $colCreateStrgEnd . ")";
			
			$dbTableViewName = $dbTableName . "view";
			
			$colCreateViewStrg = "create view $dbTableViewName as select * $foreignFieldStrg from $dbTableName $foreignTableStrg where $foreignTableName.$foreignKeyName = $dbTableName.$foreignKeyName";
			$colAry_json = $base->XmlObj->array2Json ( $colAry, &$base );
			// - build create table sql statement
			
			// - get table data
			
			$query = "select * from $dbTableName";
			$dbControlsAry = array (
					'dbtablename' => $dbTableName 
			);
			$base->DbObj->getDbTableInfo ( &$dbControlsAry, &$base );
			$passAry = array (
					'dbcontrolsary' => $dbControlsAry 
			);
			$dataAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
			$dataAry_json = $base->XmlObj->array2Json ( $dataAry, &$base );
			
			// - sendstrg commands to create table
			$sendStrg = $sendStrg . "drptbl?:$dbTableName?:dmy\n";
			$sendStrg = $sendStrg . "metatbl?:$dbTableName?:$colAry_json\n";
			$sendStrg = $sendStrg . "chktbl?:$dbTableName?:$colCreateStrg\n";
			$sendStrg = $sendStrg . "setview?:$colCreateViewStrg\n";
			// $sendStrg=$sendStrg."clrtbl?:$dbTableName?:dmy\n";
			$sendStrg = $sendStrg . "instbl?:$dbTableName?:$dataAry_json\n";
		}
		echo "$sendStrg";
	}
	function cloneForm($base) {
		$sendDataAry = $base->UtlObj->breakOutSendData ( &$base );
		// echo "xxxxxf";
		$fromFormId = $base->paramsAry ['formprofileid'];
		$toName = $base->paramsAry ['formnamenew'];
		// echo "fromformid: $fromFormId, toname: $toName<br>";
		// $base->DebugObj->printDebug($base->paramsAry,1,'operplugin002: xxxf');
		$query = "select * from formprofileview where formprofileid=$fromFormId";
		$passAry = array ();
		$formAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		
		$formAry [0] ['formprofileid'] = '';
		$formAry [0] ['formname'] = $toName;
		$dbControlsAry = array (
				'dbtablename' => 'formprofile' 
		);
		$dbControlsAry ['writerowsary'] = array ();
		$dbControlsAry ['writerowsary'] [0] = $formAry [0];
		// $base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();
		$base->DbObj->setRtnIdFlg ();
		$successBool = $base->DbObj->writeToDb ( $dbControlsAry, &$base );
		$formProfileId = $base->ErrorObj->retrieveError ( "newkeyid", &$base );
		if ($formProfileId != "") {
			$query = "select * from formelementprofileview where formprofileid=$fromFormId";
			$formEleAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
			foreach ( $formEleAry as $ctr => $theAry ) {
				//echo "ctr: $ctr, formprofileid: $formProfileId<br>";//xxxf
				$formEleAry [$ctr] ['formprofileid'] = $formProfileId;
				$formEleAry [$ctr] ['formelementprofileid'] = '';
			}
			$dbControlsAry=array();
			$dbControlsAry['dbtablename']='formelementprofile';
			$dbControlsAry['writerowsary']=$formEleAry;
			//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');exit();
			$successBool=$base->DbObj->writeToDb($dbControlsAry,&$base);
			//$base->DebugObj->printDebug ( $formEleAry, 1, 'formelearyxxxf' );
		} else {
			$base->ErrorObj->setError ( 'cloneform', "failure to write formobj: $toName" );
		}
	}
	function calcUmpquaBalances($base){
		$query="select * from umpquabankhistory order by umpquadate, umpquabankhistoryid";
		$passAry=array();
		$workAry = $base->DbObj->getSqlDbAry ( $query, $passAry, &$base );
		$totalSeriesBal=0;
		$totalCheckingBal=0;
		foreach ($workAry as $ctr=>$umpquaAry){
			//--- need to reconvert the date to mm/dd/yyyy format
			$theCredit=$umpquaAry['umpquacredit'];
			$theDebit=$umpquaAry['umpquadebit'];
			$theDate=$umpquaAry['umpquadate'];
			$theDate=$base->UtlObj->convertDate($theDate,'date1',&$base);
			$umpquaAry['umpquadate']=$theDate;
			$theId=$umpquaAry['umpquabankhistoryid'];
			$theAccountType=$umpquaAry['umpquaaccount'];
			if ($theAccountType=="tseries"){
				$totalSeriesBal+=($theCredit-$theDebit);
				$totalBal=$totalSeriesBal;
			}
			else {
				$totalCheckingBal+=($theCredit-$theDebit);
				$totalBal=$totalCheckingBal;
			}
			$useTotalBal=$base->UtlObj->formatNumber($totalBal,"2_",&$base);
			$umpquaAry['umpquabalance']=$useTotalBal;
			$workAry[$ctr]=$umpquaAry;
			//$pos=strpos("514_515_516_519",$theId,0);
			//echo "$theId, $theCredit, $theDebit, $useTotalBal\n";
		}
		$dbControlsAry=array("dbtablename"=>"umpquabankhistory");
		$dbControlsAry['writerowsary']=$workAry;
		//$base->DebugObj->printDebug($dbControlsAry,1,'xxxf');
		$base->DbObj->writeToDb($dbControlsAry,&$base);
		$base->ErrorObj->printAllErrors(&$base);
		//
		echo "done";exit();
	}
	// ====================================================
	function status() {
		$this->incCalls ();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
	// ====================================================
	
	// end of functions
}
?>
