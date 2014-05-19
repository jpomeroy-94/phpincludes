<?php
class xmlObject {
	var $calls = 0;
	var $statusMsg = '';
//========================================
	function xmlObject() {
		$this->incCalls();
		$this->statusMsg='plugin Object is fired up and ready for work!';
	}
//======================================== Array to XML by vantulder.net
function array2xml($array, $level=1) {
	//echo "xxxf: --------------- enter program -------------------<br>";
    $xml = null;
    if ($level==1) {
    	//echo "xxxf: level 1 so do init<br>";
    	$xmlAdd = '<?xml version="1.0" encoding="ISO-8859-1"?>'.
                "\n<array>\n";
        $this->displayXml($xmlAdd);
        $xml.=$xmlAdd;
    }
    //echo "xxxf: loop through array as key=>value<br>";
    foreach ($array as $key=>$value) {
    	$doUnsetKey=false;
       if (is_array($value)) {
    	//if ($level==1){
    		//echo "xxxf(should be only level1): key: $key=> value: $value<br>";
        	$key=$this->returnFormattedDataForXml($key,'key');
        	$xmlAdd="<$key>\n";
        	$doUnsetKey=true;
         	$this->displayXml($xmlAdd);
        	$xml.=$xmlAdd;
     	//}
         	//echo "xxxf: value is an array so loop through as key2=>value2<br>";
            $multi_tags = false;
            foreach($value as $key2=>$value2) {
            	//echo "xxxf: key: $key2=> value: $value2<br>";
            	$key2=$this->returnFormattedDataForXml($key2,'key');
                if (is_array($value2)) {
                	//echo "xxxf: value2 is an array<br>";
                    $xmlAdd = str_repeat("\t",$level)."<$key2>\n";
                    $nextLevel=$level+1;
                    //echo "xxxf: level is $level<br>";
                    //echo "xxxf: set nextlevel to $nextLevel<br>";
                    //echo "xxxf: -------------------------------- call array2xml($value2,$nextLevel<br>";
                    $xmlAdd .= $this->array2xml($value2, $nextLevel);
                    //echo "xxxf: -------------------return from call--------------------------<br>";
                    $xmlAdd .= str_repeat("\t",$level)."</$key2>\n";
                    $this->displayXml($xmlAdd);
                    $xml.=$xmlAdd;
                     $multi_tags = true;
                } else {
                	//echo "xxxf: value is not an array<br>";
                    if (trim($value2)!='') {
                        if (htmlspecialchars($value2)!=$value2) {
                        	//echo "xxxf: htmlspecialchars<br>";
                            $xmlAdd = str_repeat("\t",$level).
                                    "<$key2><![CDATA[$value2]]>".
                                    "</$key2>\n";
                            $this->displayXml($xmlAdd);
                            $xml .= $xmlAdd;
                         } else {
                         	//echo "xxxf: regular special characters<br>";
                            $xmlAdd = str_repeat("\t",$level).
                                    "<$key2>$value2</$key2>\n";
                            $this->displayXml($xmlAdd);
                            $xml .= $xmlAdd;
                         }
                    }
                    $multi_tags = true;
                }
            }
            if (!$multi_tags and count($value)>0) {
            	$nextLevel=$level+1;
            	//echo "xxxf: set nextlevel: $nextLevel<br>";
            	//echo "xxxf: multitags is set<br>";
                $xmlAdd = str_repeat("\t",$level)."<$key>\n";
                //echo "xxxf:-------------------- call array2xml($value,$nextLevel)<br>";
                $xmlAdd .= $this->array2xml($value, $nextLevel);
                //echo "xxxf: ------------------------------return from call------------------<br>";
                $xmlAdd .= str_repeat("\t",$level)."</$key>\n";
                $this->displayXml($xmlAdd);
                $xml .= $xmlAdd;
            }
         } else {
         	//echo "xxxf: value is not an array<br>";
            if (trim($value)!='') {
                if (htmlspecialchars($value)!=$value) {
                	//echo "xxxf: htmlspecial<br>";
                    $xmlAdd = str_repeat("\t",$level)."<$key>".
                            "<![CDATA[$value]]></$key>\n";
                    $this->displayXml($xmlAdd);
                    $xml .= $xmlAdd;
                 } else {
                 	//echo "xxxf: regular chars<br>";
                    $xmlAdd = str_repeat("\t",$level).
                            "<$key>$value</$key>\n";
                    $this->displayXml($xmlAdd);
                    $xml .= $xmlAdd;
                 }
            }
        }
       	if ($doUnsetKey){
       		$xmlAdd = "</$key>\n";
       		$this->displayXml($xmlAdd);
       		$xml .= $xmlAdd;
       	}
    }
    if ($level==1) {
        $xmlAdd = "</array>\n";
        $this->displayXml($xmlAdd);
        $xml.=$xmlAdd;
    }
    return $xml;
}
//-----
	function displayXml($xmlStuff){
		//$pxml=str_replace('<','{',$xmlStuff);
		//$pxml=str_replace('>','}',$pxml);
		//echo "$pxml<br>";
	}
//========================================
	function xml2Obj($xml,$base){
		//$xml="<one><onetwo>1two<onetwothree>1three</onetwothree><onetwofour>1four</onetwofour></onetwo></one>";//xxxf
		$tst=str_replace('<','{',$xml);
		$tst=str_replace('>','}',$tst);
		//xxxf - below does not work on hub
		$xmlObj=simplexml_load_string($xml);
		//echo "<br>xmlobj:<br>";
		//var_dump($xmlObj);exit();
		//echo "<br>xmlobj: $xmlObj<br>xml: $tst<br>";//xxxf
		return $xmlObj;
	}
//=======================================
	function xml2AryOther($xml,$base){
		$xmlparser = xml_parser_create();
		xml_parse_into_struct($xmlparser,$xml,$values);
		xml_parser_free($xmlparser);
		foreach ($values as $key=>$valueAry){
			echo "key: $key, valueary: $valueAry<br>";
		}
	}
//=======================================
	function xml2Ary($xml,$base){
		//$xmlAry=$this->xml2ArySlow($xml,&$base);
		$xmlObj=$this->xml2Obj($xml,&$base);
		$xmlAry=array();
		$xmlAry=$this->getXmlAry($xmlObj,&$base);
		return $xmlAry;
	}
//==========================================
	function getXmlAry($xmlObj,$base){
		$ctr=0;
		$xmlAry=array();
		foreach ($xmlObj->children() as $node1Obj){
			$node1Name=$node1Obj->getName();
			$chk=trim($node1Obj);
			$node1Len=strlen($chk);
			$node1Hex=bin2hex($node1Obj);
			if ($node1Len>0){$xmlAry[$node1Name]=$node1Obj;}
			else {
				//echo "create dir: $node1Name<br>";//xxxf
				$xmlAry[$node1Name]=array();
				foreach ($node1Obj->children() as $node2Obj){
					$node2Name=$node2Obj->getName();
					$chk=trim($node2Obj);
					$node2Len=strlen($chk);
					if ($node2Len>0){$xmlAry[$node1Name][$node2Name]=$chk;}
					else {
						//echo "create dir: $node1Name, $node2Name<br>";//xxxf
						$xmlAry[$node1Name][$node2Name]=array();
						foreach ($node2Obj->children() as $node3Obj){
							$node3Name=$node3Obj->getName();
							$chk=trim($node3Obj);
							$node3Len=strlen($chk);
							if ($node3Len>0){
								$xmlAry[$node1Name][$node2Name][$node3Name]=$chk;
							}
							else {
								//echo "create dir: $node1Name, $node2Name, $node3Name, node3obj: $node3Obj, chk: $chk, len: $node3Len<br>";
								$xmlAry[$node1Name][$node2Name][$node3Name]=array();
								foreach ($node3Obj->children() as $node4Obj){
									$node4Name=$node4Obj->getName();
									$chk=trim($node4Obj);
									$node4Len=strlen($chk);
									if ($node4Len>0){$xmlAry[$node1Name][$node2Name][$node3Name][$node4Name]=$node4Obj;}
									else {
										//echo "create dir: $node1Name, $node2Name, $node3Name, $node4Name, node4obj: $node4Obj, chk: $chk, len: $node4Len<br>";
										$xmlAry[$node1Name][$node2Name][$node3Name][$node4Name]=array();
										foreach ($node4Obj->children() as $node5Obj){
											$node5Name=$node5Obj->getName();
											$xmlAry[$nodeName][$node2Name][$node3Name][$node4Name][$node5Name]=$node5Obj;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $xmlAry;
	}
//=======================================
	function xml2ArySlow($xml,$base){
		//$xmlObj=$this->xml2Obj($xml,&$base);
		$xml="<one><two>twodata</two><three>threedata</three><four><five>fivedata</five></four></one>";
		$xmlAry=array();
		//$xmlAry=$this->getXmlAry($xmlObj,&$base);
		//echo "xml: $xml<br>";//xxxf
		$xmlAry=explode('<',$xml);
		$thePathAry=array();
		$lastEntryWasData=false;
		$noRows=count($xmlAry);
		for ($lp=0;$lp<$noRows;$lp++){
			$workAry=explode('>',$xmlAry[$lp]);
			$theDirName=$workAry[0];
			$theData=$workAry[1];
			$tst=substr($theDirName,0,1);
			if ($tst=='/'){$endOfDir=true;}
			else {$endOfDir=false;}
			$action="???";
			if ($theDirName==null){
				$action="error: null dir";
			}
			if ($endOfDir==false && $theData==null && $theDirName !=null){
				$thePathAry[]=$theDirName;
				$thePath=implode('/',$thePathAry);
				$action="create dir: $theDirName, new path: $thePath";
				$lastEntryWasData=false;
			}
//- </aaaaa> where aaaaa was a key to a directory
			if ($endOfDir==true && $lastEntryWasData==false){
				array_pop($thePathAry);
				$thePath=implode('/',$thePathAry);
				$action="end dir: $theDirName, reduced path: $thePath";
				$lastEntryWasData=false;
			}
//- </aaaaaa> where aaaaa was a key to data
			if ($endOfDir==true && $lastEntryWasData==true){
				$action="redundant end of data terminator, unchanged path: $thePath";
				$lastEntryWasData=false;
			}
			if ($endOfDir==false && $theData != null && $theDirName != null){
				$thePath=implode('/',$thePathAry);
				$action="write data $theData to path $thePath".'/'."$theDirName";
				$lastEntryWasData=true;
			}
			//$base->debugObj->printDebug($thePathAry,1,'xxxf');
			echo "thedir: $theDirName, thedata: $theData, $action<br>";//xxxf	
		}
		exit();//xxxf
		return $xmlAry;
	}
//=========================================
	function returnFormattedDataForJson($colValue,$colType){
		$newColValue=str_replace('|','pipeReplace',$colValue);
		return $newColValue;
	}
//========================================
	function returnFormattedDataForXml($colValue,$colType){
		switch ($colType){
			case 'key':
		       	$newColValue=trim($colValue);
       	       	$newColValue = strtolower($newColValue);
	   		    if(!preg_match("/^[a-zA-Z]/",$newColValue)){ $newColValue = "n$newColValue";}
        		$newColValue=str_replace(' ','spaceReplace',$newColValue);//works without this, but no spaces in testing
        		$newColValue=str_replace('/','slashReplace',$newColValue);//fixed an error
        		$newColValue=str_replace('|','pipeReplace',$newColValue);//fixed an error
				$newColValue=str_replace(':','colonReplace',$newColValue);//fixed an error
 				break;
			default:
				echo "utilObj.returnFormattedDataForXml invalid coltype: $colType, colvalue: $colValue<br>";
		}
		return $newColValue;
	}
//=========================================
	function array2Json($theArray,$base){
		$jsonText=json_encode($theArray);
		return $jsonText;
	}
//=========================================
	function json2Array($theJsonText,$base){
		$theArray=json_decode($theJsonText,1);	
		return $theArray;
	}
//-========================================
	function printHi(){
		echo "hi<br>";
	}
//========================================
	function incCalls(){
		$this->calls++;
	}
}
?>