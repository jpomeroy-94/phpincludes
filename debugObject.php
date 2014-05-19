<?php
//--- below is p saves
		//$base->debugObj->printDebug($x,1); //xxx (v)
		//$base->debugObj->printDebug("query: $query",1); //xxx (q)
		//$base->debugObj->printDebug($base->tableProfileAry,1); //xxx (p)
		//$base->debugObj->printDebug("xxx",0); //xxx (h)
		//$base->debugObj->printDebug("-",0); //xxx (f)
		//$base->debugObj->setPrio(-1,-1); //xxx (s)
		//$base->debugObj->resetPrio(); //xxx (u)
		//$base->debugObj->placeCheck("x"); //xxx (c)
class debugObject {
	var $statusMsg;
	var $callNo = 0;
	var $prioLmt = 0;
	var $queryLmt = 0;
	var $returnStk = NULL;
	var $returnStkCtr = 0;
	function debugObject() {
		$this->incCalls();
		$this->statusMsg='debug Object is fired up and ready for work!';
// xxxx: comment out/in to turn debugging on/off
		//$this->prioLmt = -2; // all methods excluding init's
		//$this->queryLmt = -2; // all querys excluding init's
		//$this->prioLmt = -3; // all methods
		//$this->queryLmt = -3; // all querys
	}
//=====================================================
	function setPrio($prioLmt,$queryLmt=0){
		$this->prioLmt=$prioLmt;
		$this->queryLmt=$queryLmt;
	}
//=====================================================
	function resetPrio(){
		$this->prioLmt=0;
		$this->queryLmt=0;
	}
//=====================================================
	function showQuery($query, $base, $prio){
		if ($prio > $this->queryLmt){echo "<br>query: $query";}
	}
//=====================================================
	function placeCheck($msg){
		$msg=str_replace('<','{',$msg);
		$msg=str_replace('>','}',$msg);
		echo "<bold>$msg</bold><br>";
	}
//=====================================================
	function displayStack(){
		$this->printDebug($this->returnStk,1,'--- return stack ---');
	}
//=====================================================
	function getLastStackEntry(){
		$workAry=$this->returnStk;
		$cnt=count($workAry);
		if ($cnt>1){
			array_pop($workAry);
		}
		$lastProg_raw=end($workAry);
		$lastProgAry=explode('(',$lastProg_raw);
		$lastProg=$lastProgAry[0];
		return $lastProg;
	}
//=====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//=====================================================
	function incCalls(){$this->callNo++;}
//=====================================================
	function printDebug($printVar,$prio=0, $name=""){
//---------- setup return stack
		if (strpos("$printVar",'rtn:',0)){
			unset($this->returnStk[$this->returnStkCtr]);
			$this->returnStkCtr--;
		}
		else {
			if (strpos("$printVar",':',0)){
				$this->returnStkCtr++;
				$this->returnStk[$this->returnStkCtr]=$printVar;
			} 
		}
//--- check run type
//xxx! - need to fix below - may debug in non html
		$runType='html';
		if ($runType != 'html'){
			$breakChar="\n";		
		}
		else {$breakChar='<br>';}
//-------------- print if high enough priority
		if ($prio==99){$breakChar="\n";}
		if ($prio > $this->prioLmt) {
		if (is_array($printVar)){
			echo "$breakChar<-- start array $name-->";
			foreach ($printVar as $key=>$value){
				$pvalue=str_replace("<","{",$value);
				$pvalue=str_replace(">","}",$pvalue);
				echo "$breakChar $key => $pvalue";
				if (is_array($value)){
					foreach ($value as $key2=>$value2){
						$pvalue2=str_replace("<","{",$value2);
						$pvalue2=str_replace(">","}",$pvalue2);
						echo "$breakChar --- $key2=>$pvalue2";
						if (is_array($value2)){
           					foreach ($value2 as $key3=>$value3){
								$pvalue3=str_replace("<","{",$value3);
								$pvalue3=str_replace(">","}",$pvalue3);
              						echo "$breakChar ------ $key3=>$pvalue3";
								if (is_array($value3)){
           							foreach ($value3 as $key4=>$value4){
										$value4=str_replace("<","{",$value4);
										$value4=str_replace(">","}",$value4);
              								echo "$breakChar --------- $key4=>$value4";
              								if (is_array($value4)){
           									foreach ($value4 as $key5=>$value5){
												$value5=str_replace("<","{",$value5);
												$value5=str_replace(">","}",$value5);
              										echo "$breakChar ------------ $key5=>$value5";
           									} //end foreach value4
           								}//end if value4
									} //end foreach value3
								} //end if value3
           					} //end foreach value2
						} //end if value2
       		  		} //end foreach value
          		} //end if value
			} //end foreach value
			echo "$breakChar<-- end of array $name --><br>";
			}//end if printvar
			else {
				if ($runType == 'html'){
					if ($prio == 0){echo "<font size=4>";}
					if ($prio == 9){echo "<font size=5>";}
				}//end if html
				$printVar=str_replace("<","{",$printVar);
				$printVar=str_replace(">","}",$printVar);
				echo "$breakChar $printVar";
				if ($runType=='html'){
					if (($prio == 0) || ($prio == 9)){echo "</font>";}
				}//end if html2
			}//end else
		}//???
	}//end all
}
?>
