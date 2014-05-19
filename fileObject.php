<?php
class fileObject {
// version: 1.1.1
	var $statusMsg;
	var $callNo = 0;
	var $ctr = 0;
	var $loggingIsOn = true;
//---
	function fileObject() {
		$this->incCalls();
		$this->statusMsg='file Object is fired up and ready for work!';
	}
//---
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//================================================
	function getFileArray($path) {
		$returnAry=array();
		if (is_file($path)){
			$handle=fopen($path,'r');
			//xxxxf666 below loops forever
			if ($handle != ""){
			while (!feof($handle)) {
   				$line = fgets($handle);
				$returnAry[]=$line;
				$ctr++;
				if ($ctr>10000){
					$returnAry[0]="counter>1000 so $path must be invalid!";
					break;
				}
			}
			} else {
				$returnAry[]="$path is not on file";
			}
 			fclose($handle);
		}
		else {echo "$path is not a file!!!";}
		return $returnAry;
	}
//================================================
	function getFile($path){
		$returnFile=null;
		if (is_file($path)){
			$handle=fopen($path,'r');
			while (!feof($handle)){
				$line=fgets($handle);
				$returnFile.=$line;
			}
		}
		return $returnFile;
	}
	//===============================================
	function writeFile($path,$theFile,$base){
		if(($fileHandle = fopen($path,'w')) === FALSE){
			echo "permissions problem with: $path<br>";
			die('failed to open file for writing');
		}
		//echo "filehamndle: $fileHandle";//xxxf
		fwrite($fileHandle, $theFile);
		fclose($fileHandle);
		//- xxxf dont understand how fwrite can work, but chmod does not?
		$returnBool=$base->utlObj->tryToChangeMod($path,0777,&$base);
	}
	//===============================================
	function retrieveFileNames($dirPath,$selectionString,$base){
		$returnAry=array();
		$dirHandle = opendir($dirPath);
		//echo "selection string: $selectionString<br>";//xxx
		while (($fileName = readdir($dirHandle)) !== false)
		{
			$chkPath=$dirPath.'/'.$fileName;
			if (is_file($chkPath)){
				$getIt=true;
			}
			else {$getIt=false;
			}
			if ($selectionString != NULL && $getIt === true){
				$pos=strpos('x'.$fileName,$selectionString,0);
				if ($pos>0){
					$getIt=true;
				}
				else {$getIt=false;
				}
				//echo "pos: $pos, sel: $selectionString, file: $fileName<br>";//xxx
  			}
  			if ($getIt){
	  			$returnAry[]=$fileName;
  			}
  		}
  		closedir($dirHandle);	
  		return $returnAry;
	}
//===============================================
	function retrieveFileNamesV2($passAry,$base){
		$returnAry=array();
		$dirPath=$passAry['directorypath'];
		$openErr=false;
		$dirHandle = @ opendir($dirPath) or $openErr=true;
		if (!$openErr){
		$imageFormat=$passAry['imageformat'];
		$nameFilter=$passAry['namefilter'];
		while (($fileName = readdir($dirHandle)) !== false)
  		{
  			if ($fileName != '.' && $fileName != '..' && $fileName != 'raw' && $fileName != 'thumbnails'){
   				$getIt=true;
  			}
  			else {$getIt=false;}
  			if ($nameFilter != NULL && $getIt === true){
	  			$pos=strpos('x'.$fileName,$nameFilter,0);
	  			if ($pos>0){$getIt=true;}
	  			else {$getIt=false;}
	  			//echo "pos: $pos, sel: $selectionString, file: $fileName<br>";//xxx	  			
  			}
  			if ($imageFormat != 'all' && $getIt===true){
  				$fileNameAry=explode('.',$fileName);
  				if ($imageFormat == $fileNameAry[1]){$getIt = true;}
  				else {$getIt = false;}	
  			}
  			//echo "getit: $getIt, filename: $fileName<br>";//xxxf
  			if ($getIt){
	  			$returnAry[]=$fileName;
  			}
  		}
  		closedir($dirHandle);	
  		}
  		return $returnAry;
	}
//========================================
	function writeLogError($logMsg,$base){$this->writeLog('error',$logMsg,&$base);}
//========================================
	function writeLog($logName,$logMsg,$base){
//---   control what is written in the log
		if ($logName == 'writedbfromajaxsimple' ||
			$logName=='error' )
		{
//- 
			$useLogName=$logName;
			$pos=strpos('x'.$useLogName,'.log',0);
			if ($pos<1){$useLogName.=".log";}
			$passAry=array('thedate'=>'today');
			if ($base->utlObj != null){
    			$dateAry=$base->utlObj->getDateInfo($passAry,&$base);
   				$currentDate=$dateAry['date_v1'];
   				$currentTime=$dateAry['time_v1'];
   				$domainName=$base->systemAry['domainname'];
   				//!!! cant do logging until systemAry is setup - may be called if called by generic method
   				if ($domainName != null){
	   				$logMsg=$currentDate.' '.$currentTime."($domainName/$logName)".': '.$logMsg;
   					$logPath=$base->systemAry['loglocal'];
 					$logPath.='/'.$useLogName;
					if(($fileHandle = fopen($logPath,'a')) === FALSE){
       					die('Failed to open file for writing: '.$logPath);
					}
					fwrite($fileHandle, $logMsg."\n");
        			fclose($fileHandle);
        			$returnBool=$base->utlObj->tryToChangeMod($logPath,0777,&$base);
   				}
			}
			else {
				echo "!!!!! base->utlObj is null. Tried to write log: $logMsg to $logName! last param(base) must be bad!";
			}
		}
	}
//-----------------------------------------
	function initLog($logName,$base){
		if ($this->loggingIsOn){
		$pos=strpos($logName,'.log',0);
		if ($pos<0){$logName.=".log";}
		$logPath=$base->systemAry['loglocal'];
		$logPath.='/'.$logName;
		if (($fileHandle = fopen($logPath,'w'))=== FALSE){
	       	die('Failed to open file for writing!');
		}
        fwrite($fileHandle, NULL);
        fclose($fileHandle);
		$returnBool=$base->utlObj->tryToChangeMod($logPath,0777,&$base);
		}
	}
//-------------------------------------------
	function runBashCommand($passAry,$base){
		$command=$passAry['bashcommand'];
		//ini_set('max_execution_time', $newTimeOut);
		$type='r';
		$this->writeLog('jefftest66',"command: $command",&$base);
		$success=$handle=popen("$command",$type);
		$theOutput=null;
		while(!feof($handle)) {$theOutput .= fread($handle,1024);}
		pclose($handle);
		//echo "success: $success\n command: $command\n output: $theOutput\n";//xxxf
		$this->ctr++;
		$outputAry=explode("\n",$theOutput);
		$returnAry['outputary']=$outputAry;
		$this->writeLog('jefftest66',"output: $theOutput",&$base);
		return $returnAry;
	}
//--------------------------------------------
	function moveFile($srcFile,$destFile,$base){
		$bashCmd="mv $srcFile $destFile";
	}
//--------------------------------------------
	function deleteFile($base){
		$this->writeLog('jefftest66','xxxf',&$base);
		$sendData=$base->paramsAry['senddata'];
		$workAry=explode('`',$sendData);
		$theCnt=count($workAry);
		$paramsUseAry=$base->paramsAry;
		for ($lp=0;$lp<$theCnt;$lp++){
			$workVar=$workAry[$lp];
			$workVarAry=explode('|',$workVar);
			$paramsName=$workVarAry[0];
			$paramsValue=$workVarAry[1];
			$paramsUseAry[$paramsName]=$paramsValue;
		}
		$imageDirectoryPath=$paramsUseAry['picturedirectory'];
		$imageFileName=$paramsUseAry['picturefilename'];
		$localPath=$base->clientObj->getBasePath(&$base);
		$fullPath=$localPath.'/'.$imageDirectoryPath.'/'.$imageFileName;
		$bashCmd="rm -f $fullPath";
		//$this->writeLog('jefftest66',"bashcmd: $bashCmd",&$base);
		$passAry=array();
		$passAry['bashcommand']=$bashCmd;
		$returnAry=$this->runBashCommand($passAry,&$base);
		echo "okdonothing|";
	}
//============================================================
	function turnOnLog($base){
		$this->loggingIsOn=true;
	}
//============================================================
	function turnOffLog($base){
		$this->loggingIsOn=false;	
	}
//---
	function incCalls(){$this->callNo++;}
}
?>
