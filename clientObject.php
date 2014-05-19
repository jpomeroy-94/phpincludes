<?php
class clientObject {
	var $statusMsg;
	var $callNo = 0;
	var $systemAry = array();
//=============================================
	function clientObject($base) {
		$this->incCalls();
		$this->statusMsg='file Object is fired up and ready for work!';
		$this->setClientData();
	}
//=============================================
	function incCalls(){
		$this->callNo++;
	}
//============================================
	function setClientData(){
		//-cant do it here $base->debugObj->printDebug("clientObj:setCLientData)",0);
		$curDir=getcwd();
		//echo "curdir: $curDir<br>";
		switch ($curDir){
//- lindy testing
			case '/home/jeff/web/Base/webinit':
				$domainName='lindy/webinit';
				break;
//- lindy testing
			case '/home/jeff/web/Base/testing':
				$domainName='lindy/testing';
				break;
//- lindy
			case '/home/jeff/web/Base':
				$domainName='lindy';
				break;
//- lindy as was once
			case '/var/www/html':
				$domainName='lindy';
				break;
//- urbanecosystems
			case '/usr/local/www/urbanecosystems.net/www':
				$domainName='urbanecosystems.net';
				break;
//- jeffreypomeroy.com
			case '/usr/local/www/jeffreypomeroy.com/www':
				$domainName='jeffreypomeroy.com';
				break;
//- workingmansdan.com
			case '/usr/local/www/workingmansdan.com/www':
				$domainName='workingmansdan.com';
				break;
//- beaverwoodcreations.com
			case '/usr/local/www/beaverwoodcreations.com/www':
				$domainName='beaverwoodcreations.com';
				break;
			case '/usr/local/www/alan/oaksbottomboys.com/www':
				$domainName='oaksbottomboys.com';
				break;
			case '/usr/local/www/allenbyrd/byrdland.net/www':
				$domainName='byrdland.net';
				break;
			case '/usr/local/apache2/htdocs':
				$domainName='ubu';
				break;
			default:
				exit('invalid domain!!: x'.$curDir.'x');
		}
		$this->systemAry=$this->getClientData($domainName,&$base);
		$this->systemAry['curdir']=$curDir;
	}
//=============================================================
	function getClientData($domainName,$base){
		switch ($domainName){
//- lindy testing
			case 'lindy/webinit':
				$htmlLocal='http://lindy/webinit';
				$baseLocal='/home/jeff/web';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal='/home/jeff/tmp';
				$fedJob='reedmain';
				$dbName='staging';
				$dbUserName='jeff';
				$dbPassword='lgttf5t';
				$dbHost=NULL;
				$hourAdj=0;
				break;
//- lindy testing
			case 'lindy/testing':
				$htmlLocal='http://lindy/testing';
				$baseLocal='/home/jeff/web';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal='/home/jeff/tmp';
				$fedJob='main';
				$dbName='testing';
				$dbUserName='postgres';
				$dbPassword=NULL;
				$dbHost=NULL;
				$hourAdj=0;
				break;
//- lindy
			case 'lindy':
				$htmlLocal='http://lindy/';
				$baseLocal='/home/jeff/web';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal='/home/jeff/tmp';
				$fedJob='main';
				$dbName='postgres';
				$dbUserName='postgres';
				$dbPassword='';	
				$dbHost=NULL;			
				$hourAdj=0;
				break;
//- urbanecosystems
			case 'urbanecosystems.net':
				$htmlLocal='http://urbanecosystems.net/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='urbanecomain';
				//$dbName='937_urbanecodb';
				$dbName='937_urbanecosysdb';
				//$dbUserName='937_urbanecodb';
				$dbUserName='937_jay';
				$dbPassword='lgttf5t';
				//$dbHost='pgsql82.hub.org';
				$dbHost='pgsql84.hub.org';
				$hourAdj=-7;
				break;
//- jeffreypomeroy.com
			case 'jeffreypomeroy.com':
				$htmlLocal='http://jeffreypomeroy.com/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				//$fedJob='clientadmindesktop';
				$fedJob='jeffsales';
				//$dbName='937_home';
				$dbName='937_jeffreypomeroy';
				//$dbUserName='937_postgres';
				$dbUserName='937_jeff';
				//$dbPassword=NULL;
				$dbPassword='lgttf72t';
				//$dbHost='pgsql82.hub.org';
				$dbHost='pgsql84.hub.org';
				$hourAdj=-7;
				break;
//- workingmansdan.com
			case 'workingmansdan.com':
				$htmlLocal='http://workingmansdan.com/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='workingmansmain';
				//$dbName='937_workingmansdan';
				//$dbUserName='937_workingdan';
				//$dbPassword='lgttf5t';
				//$dbHost='pgsql83.hub.org';
				$dbName='937_jeffreypomeroy';
				$dbUserName='937_jeff';
				$dbPassword='lgttf72t';
				$dbHost='pgsql84.hub.org';
				$hourAdj=-7;
				break;
//-beaverwoodcreations.com
			case'beaverwoodcreations.com':
				$htmlLocal='http://beaverwoodcreations.com/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='cedar';
//-temporary
				//$dbName='937_home';
				$dbName='937_jeffreypomeroy';
				//$dbUserName='937_postgres';
				$dbUserName='937_jeff';
				//$dbPassword=NULL;
				$dbPassword='lgttf72t';
				//$dbHost='pgsql82.hub.org';
				$dbHost='pgsql84.hub.org';
//-endtemporary
				$hourAdj=-7;
				break;
			case'oaksbottomboys.com':
				$htmlLocal='http://oaksbottomboys.com/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='oaksbottomboys';
//-temporary
				//$dbName='937_home';
				$dbName='937_jeffreypomeroy';
				//$dbUserName='937_postgres';
				$dbUserName='937_jeff';
				//$dbPassword=NULL;
				$dbPassword='lgttf72t';
				//$dbHost='pgsql82.hub.org';
				$dbHost='pgsql84.hub.org';
//-endtemporary
				$hourAdj=-7;
				break;
			case'byrdland.net':
				$htmlLocal='http://byrdland.net/';
				$baseLocal='/home/jeffreypomeroy.com/admin/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='oaksbottomboysrock';
				//-temporary
				//$dbName='937_home';
				$dbName='937_jeffreypomeroy';
				//$dbUserName='937_postgres';
				$dbUserName='937_jeff';
				//$dbPassword=NULL;
				$dbPassword='lgttf72t';
				//$dbHost='pgsql82.hub.org';
				$dbHost='pgsql84.hub.org';
				//-endtemporary
				$hourAdj=-7;
				break;
			case'ubu':
				$htmlLocal='http://ubu/';
				$baseLocal='/home/owner/base';
				$logLocal=$baseLocal.'/logs';
				$tmpLocal=$baseLocal.'/tmp';
				$fedJob='main';
				$dbName='postgres';
				$dbUserName='postgres';
				$dbPassword='postgres';
				$dbHost=NULL;
				$hourAdj=0;
				break;
			default:
				exit('invalid domain name: '. $domainName);
		}
		$jobLocal=$htmlLocal."/index.php?job=";
		$workAry=array();
		$workAry['type']='html';
		$workAry['htmllocal']=$htmlLocal;
		$workAry['joblocal']=$jobLocal;	
		$workAry['loglocal']=$logLocal;
		$workAry['tmplocal']=$tmpLocal;
		$workAry['domainname']=$domainName;
		$workAry['fedjob']=$fedJob;
		$workAry['houradj']=$hourAdj;
		$workAry['baselocal']=$baseLocal;
		$workAry['dbname']=$dbName;
		$workAry['dbusername']=$dbUserName;
		$workAry['dbpassword']=$dbPassword;
		$workAry['dbhost']=$dbHost;
		return $workAry;	
	}
//=============================================================
	function getSystemData($base){return $this->systemAry;}
//=============================================================
	function getBasePath($base){return $this->systemAry['baselocal'];}
//=============================================================
	function getClientConn($domainName,$base){
		$base->debugObj->printDebug("clientObj:getClientConn)",0);
		//echo "client: getclientconn(domainname): $domainName<br>";//xxx
		if ($domainName=='default'){
			$domainName=$this->systemAry['domainname'];
		}
		$workAry=$this->getClientData($domainName,&$base);
		//echo "inside domainname: $domainName<br>";//xxx
		//$base->debugObj->printDebug($workAry,1,'xxx');
		//exit('xxx');
		$dbUserName=$workAry['dbusername'];
		$dbName=$workAry['dbname'];
		$dbPassword=$workAry['dbpassword'];
		$dbHost=$workAry['dbhost'];
		if ($dbHost==NULL){$dbHostInsert=NULL;}
		else {$dbHostInsert="host=$dbHost";}
		if ($dbUserName != NULL){
			$theDbConn=pg_connect("dbname=$dbName $dbHostInsert user=$dbUserName password=$dbPassword");
//echo "thdbconn: conn: $theDbConn, dbname: $dbName, host:  $dbHostInsert,username: $dbUserName, userpwd: $dbPassword\n";
			if ($theDbConn == ""){
				echo "error in connect<br>";
				echo "dbname: $dbName<br>";
				echo "dbhostinsert: $dbHostInsert<br>";
				echo "user: $dbUserName<br>";
				$base->debugObj->displayStack();
			}
		}
		else {
			$theDbConn=pg_connect("dbname=$dbName");
		}
		//echo "thedbconn: $theDbConn<br>";//xxxdd
		//if ($theDbConn == NULL){exit();}
		$base->debugObj->printDebug("-rtn:getClientConn",0); //xx (f)
		return $theDbConn;
	}
//=========================================================
	function queryClientDbTable($query,$dbConn,$queryType,$base){
		$base->debugObj->printDebug("clientObj:queryClientDbTable)",0);
		//$queryType='read';
		//echo "queryclientdbtable/query: $query<br>";//xxxf
		//$base->debugObj->displayStack();
		$result=$base->dbObj->queryTableAnyDb($query,$queryType,$dbConn,$base,$prio=0);
		//echo 'done<br>';//xxxf
		$base->debugObj->printDebug("-rtn:queryClientDbTable",0); //xx (f)
		return $result;	
	}
//=============================================================
	function queryClientDbTableRead($query,$dbConn,$queryType,$passAry,$base){
			$result=$this->queryClientDbTable($query,$dbConn,'read',&$base);
			$returnAry=$base->utlObj->tableToHashAryV3($result,$passAry);
			return $returnAry;
	}
//=============================================================
	function getImageBase($base){
		$theBase=$this->systemAry['baselocal'];
		$imageBase=$theBase.'/images';
		return $imageBase;
	}
//==============================================================
	function getBase($base){
		$theBase=$this->systemAry['baselocal'];
		return $theBase;
	}
//==============================================================
	function getRawImageBase($base){
		$theBase=$this->systemAry['baselocal'];
		$rawImageBase=$theBase.'/rawimages';
		return $rawImageBase;	
	}
//==============================================================
	function getHtmlBase($base){
		$theHtmlBase=$this->systemAry['htmllocal'];
		return $theHtmlBase;	
	}
}
