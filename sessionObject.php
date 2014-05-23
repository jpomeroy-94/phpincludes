<?php
class SessionObject{
	var $sessionAry = array();
	var $sessionDirName;
	//=====================================
	function SessionObject(){
		//echo 'so: init<br>';//xxxf
		$this->sessionDirName='default';
		$sessionAry[$this->sessionDirName]=array();
	}
	//=====================================
	function displayDebug($base){
		echo "<br>--- displaydebug ---<br>";
		$base->DebugObj->printDebug($this->sessionAry,1,'sessionaryxxxf');
	}
	//=====================================
	function displayDebug2(){
		echo "<br>--- displaydebug2 ---<br>";
		$theName= $this->sessionAry['comparedbtables']['albumprofile']['albumname']['dbcolumnname'];
		echo "dbcolumnname: $theName<br>";
	}
	//=====================================
	function displayDebug3(){
		echo "<br>--- displaydebug3 ---<br>";
		foreach ($this->sessionAry as $one=>$two){
			echo "$one, $two<br>";
			foreach ($two as $three=>$four){
				echo "...$three, $four<br>";
				foreach ($four as $five=>$six){
					echo "......$five, $six<br>";
					foreach ($six as $seven=>$eight){
						echo ".........$seven, $eight<br>";
						foreach ($eight as $nine=>$ten){
							echo "............$nine, $ten<br>";
						}
					}
				}
			}
		}
	}	
	//=====================================
	function setTestAry($base){
		echo "so: settestary<br>";//xxxf
		$this->testAry=array();
		$this->testAry['level2']=array();
		$this->testAry['level2']['level3']=array();
		$this->testAry['level2']['level3']['name1']='value1';
	}
	//=====================================
	function displayTestAry($base){
		echo "so: displaytestary<br>";//xxxf
		$base->DebugObj->printDebug($this->testAry,1,'xxxftestary');
	}
	//=====================================
	function clearDir($dirName){
		//echo "so: clear dir $dirName<br>";//xxxf
		$sessionAry[$dirName]=array();
	}
	//=====================================
	function saveMultiAry($name1,$name2,$name3,$theAry,$base){
		//echo "<br>so: savemultiary, $name, $name2, $name3<br>";//xxxf
		$lvl=0;
		if (!array_key_exists($name1,$this->sessionAry)){$this->sessionAry[$name1]=array();}
		if ($name2 != null){
			if (!array_key_exists($name2,$this->sessionAry[$name1])){
				$this->sessionAry[$name1][$name2]=array();
			}
			$lvl=1;
		}
		if ($name3 != null){
			if (!array_key_exists($name3,$this->sessionAry[$name1][$name2])){
				$this->sessionAry[$name1][$name2][$name3]=array();
			}
			$lvl=2;
		}
		switch ($lvl){
			case 0:
				$this->sessionAry[$name1]=$theAry;
				break;;
			case 1:
				$this->sessionAry[$name1][$name2]=$theAry;
				break;;
			case 2:
				$this->sessionAry[$name1][$name2][$name3]=$theAry;
				break;;
		}
		//echo "<br>--------- save multi aray ------------<br>";//xxxf
		//$this->displayDebug2();//xxxf
	}
	//=====================================
	function getMultiAry($name1,$name2,$name3,$base){
		//echo "so: getmultiary $name1, $name2, $name3<br>";//xxxf
		$theAry=array();
		if (array_key_exists($name1,$this->sessionAry)){
 			if ($name2 != null){
 				if (array_key_exists($name2,$this->sessionAry[$name1])){
					if ($name3 != null){
						if (array_key_exists($name3,$this->sessionAry[$name1][$name2])){
							$theAry=$this->sessionAry[$name1][$name2][$name3];
						}
					}
					else {
						$theAry=$this->sessionAry[$name1][$name2];	
					}
				}
			}
			else {
				$theAry=$this->sessionAry[$name1];
			}
		}
		return $theAry;
	}
	//=====================================
	function changeSessionDirName($newName){
		//echo "so: changesessiondirname: $newName<br>";//xxxf
		$this->sessionDirName=$newName;
		if (!in_array($newName,$this->sessionAry)){$this->sessionAry[$newName]=array();}	
	}
	//=====================================
	function getSessionValue($sessionSaveName){
		//echo "so: getsessionvalue: $sessionSaveName<br>";//xxxf
		$sessionSaveValue=$this->sessionAry[$this->sessionDirName][$sessionSaveName];
		return $sessionSaveValue;
	}	
	//=====================================
	function saveSessionValue($sessionSaveName,$sessionSaveValue)	{
		//echo "so: savesessionvalue, $sessionSaveName, $sessionSaveValue<br>";//xxxf
		if ($sessionSaveName == NULL){$sessionSaveName='null';}
		$this->sessionAry[$this->sessionDirName][$sessionSaveName]=$sessionSaveValue;
	}
	//=====================================
	function saveSessionAry($sessionName,$sessionAry){
		//echo "so: savesessionary: $sessionName <br>";//xxxf
		foreach ($sessionAry as $key=>$value){
			$this->sessionAry[$sessionName][$key]=$value;	
		}
	}
	//=====================================
	function saveNewSessionAry($sessionAry){
		//echo "so: savenewsessionary <br>";//xxxf
		$rndNumber=rand(1,1000);
		$sessionName='save'.$rndNumber;	
		$this->sessionAry[$sessionName]=array();
		$this->saveSessionAry($sessionName,$sessionAry);
		return $sessionName;
	}
		//=====================================
	function clearSessionAry($sessionName){
		//echo "so:clearsessionary: $sessionName<br>";//xxxf
		$this->sessionAry[$sessionName]=array();
	}
	//=====================================
	function getSessionAry($sessionName){
		//echo "so: getsessionary: $sessionName<br>";//xxxf
		$returnAry=$this->sessionAry[$sessionName];
		return $returnAry;	
	}
}
?>
