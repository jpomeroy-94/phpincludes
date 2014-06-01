<?php
class TagObject {
	var $statusMsg;
	var $callNo = 0;
//====================================================
	function TagObject() {
		$this->incCalls();
		$this->statusMsg='tag Object is fired up and ready for work!';
	}
//====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//====================================================
	function incCalls(){$this->callNo++;}
//==================================================== 
	function insertTitle($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertTitle($paramFeed,'base')",0); //xx (h)
		$htmlLine=strtolower($paramFeed['param_2']);
		$htmlName='default';
		$returnAry=array();
		$title_raw=$base->htmlProfileAry[$htmlName]['htmltitle'];
		$title=$base->UtlObj->returnFormattedString($title_raw,&$base);
		$title='<title>'.$title.'</title>';
		$newHtmlLine=$base->UtlObj->replaceStr($htmlLine,'!!inserttitle!!',$title,&$base);
		$returnAry[]=$newHtmlLine."\n";	
//- icon 
        $iconLine="<link rel=\"shortcut icon\" href=\"/favicon.ico\" type=\"image/x-icon\"/>\n";
        $returnAry[]=$iconLine;
		$base->DebugObj->printDebug("-rtn:insertTitle",0); //xx (f)
		return $returnAry;
	}
//==================================================== 
	function insertForm($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertForm($formNoSt,'base')",0);
		//$base->DebugObj->printDebug($paramFeed,1,'xxxparamfeed');
		$formName_raw=$paramFeed['param_1'];
		$formNameAry=explode('_',$formName_raw);
		$formName=$formNameAry[0];
		$formName=strtolower($formName);	
		//echo "formname: $formName<br>";//xxx
		if ($formName == ""){$formName="none";}
//- setup passAry 
		if (array_key_exists('usethisdataary',$paramFeed)){
			$passAry=array('usethisdataary'=>$paramFeed['usethisdataary'],'jobtype'=>'feed');
		}
		else {$passAry=array();}
		if (array_key_exists('tabindexbase',$paramFeed)){
			$tabIndexBase=$paramFeed['tabindexbase'];
		}
		else {$tabIndexBase=0;}
		//$base->DebugObj->printDebug($passAry,1,'xxxpassary');
		$passAry['formname']=$formName;
//- get the data for the form
		$rowNo=0; // only get rowno 0 for form
		$dontGetData_file=$base->formProfileAry[$formName]['formdontreaddata'];
		$dontGetData=$base->UtlObj->returnFormattedData($dontGetData_file,'boolean','internal');
//- get the data if that is the way to do it
		//$base->DebugObj->printDebug($passAry,1,'xxxpassary');//xxxd
		//$base->DebugObj->printDebug($getAllDataAry['datarowsary'],1,'getalldataary');//xxxd
//- get dbcontrolsary that was gotten when getting data
		//$base->FileObj->writeLog('formdb.txt','do we get data for form: '.$formName,&$base);
		if (!$dontGetData){
			//echo 'get data for form: '+$formName;//xxx
			$getAllDataAry=$base->DbObj->getDataForForm($passAry,&$base);
			$dbControlsAry=$getAllDataAry['dbcontrolsary'];
			$dbTableName=$dbControlsAry['dbtablename'];
			if ($dbTableName == NONE){$dbTableName='none';}
			$dataRowsAry=$getAllDataAry['datarowsary'];
			$dbControlsAry['datarowsary']=$dataRowsAry;
		}
		else {
			$dbControlsAry=array();
			$dataRowsAry=array();
			$dbControlsAry['datarowsary']=$dataRowsAry;	
		}
		$noRows=count($dataRowsAry[$dbTableName]);
		if ($noRows==0){$thereIsData=false;}
		else {$thereIsData=true;}
//- there is no data, but we wanted to get some so lets try other ways
		if (!$thereIsData && !$dontGetData){
			//echo "formname: $formName<br>";//xxx
			//$base->DebugObj->printDebug($base->formElementProfileAry[$formName],1,'xxx');
			foreach ($base->formElementProfileAry[$formName] as $formElName=>$formElAry){
				$formElValue=$base->paramsAry[$formElName];
				if ($formElValue == ""){$formElValue=$base->UtlObj->retrieveValue($formElName.'_'.$dbTableName,&$base);}
				$formElValueSelect=$base->paramsAry[$formElName . '_select'];
				if ($formElValue == '' && $formElValueSelect != ''){
					$formElValue=$formElValueSelect;
				}
				else {
					if ($formElValue != '' && $formElValueSelect != ''){
							if (strpos($formElValueSelect,$formElValue,0)>=0){
								$formElValue=$formElValueSelect;
							} // end if
					} // end if != '' && != ''
				} // end else
				$dbControlsAry['datarowsary'][$dbTableName][0][$formElName]=$formElValue;
			} // end foreach formelementprofileary
		} // end if != true
		//$base->DebugObj->printDebug($dbControlsAry['datarowsary'],1,'xxx');
		$dbControlsAry['tabindexbase']=$tabIndexBase;
		$returnAry=$base->FormObj->buildForm($formName, $dbControlsAry, &$base);
		$base->DebugObj->printDebug("-rtn:insertForm",0); //xx (f)
		//exit();//xxx
		return $returnAry;
	}
//==================================================== 
	function insertUrlList($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertUrlList($paramFeed,'base')",0);
		$tableName=$paramFeed['param_1'];
		$returnAry=$base->DbObj->buildList($tableName,&$base);
		return $returnAry;
	}
//====================================================
	function insertMenuList($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertMenuList('base')",0);
		$tableName=$paramFeed['param_1'];
		$returnAry=$base->DbObj->buildMenuList($tableName,&$base);
		return $returnAry;
	}
//====================================================
	function insertRevolvingForm($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertRevolvingForm($subJob,'base')",0);
		$subJob=$paramFeed['param_1'];
		$returnAry=array();
		$formElementAry=$base->formElementProfileAry['formname1'];
		$formAry=$base->formProfileAry['formname1'];
		$tableRows=$formAry['formtablerows'];
		$tableCols=$formAry['formtablecols'];
		$displayAry=array();
		$noFormElements=count($formElementAry);
		$eleCtr=0;
		for ($ctr=0;$ctr<$noFormElements;$ctr++){
			$elementAry=$formElementAry[$ctr];
			$tableRow=$elementAry['formelementtablerow'];
			$tableCol=$elementAry['formelementtablecol'];
			if (($tableRow>0 && $tableRow<=$tableRows) && ($tableCol>0 && $tableCol<=$tableCols)){
				$displayAry[$tableRow][$tableCol][$eleCtr]=$elementAry;
				$eleCtr++;
			} //end if
		} //end for
		$dataAry=$base->DbObj->getDataForForm($base,true);
		$returnAry=$base->FormObj->buildMultiRowForm($base,$displayAry,$dataAry);
		return $returnAry;
	}
//==================================================== 
	function insertHtmlElement($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertHtmlElement($htmlNo,'base')",0);
		$htmlElementName=$paramFeed['param_1'];
		$htmlLine=$paramFeed['param_2'];
		//echo "name: $htmlElementName, line: $htmlLine,";//xxx
		$htmlElementNameAry=explode('_',$htmlElementName);
		if ($htmlElementNameAry[0] == 'generichtml'){$htmlElementName=$base->paramsAry[$htmlElementName];}
		//echo " new name: $htmlElementName<br>";//xxx
		$returnAry=array();
		$elementAry=$base->htmlElementProfileAry[$htmlElementName];
		$type=$elementAry['type'];
		switch ($type){
		case 'csslink':
			$returnAry=$base->HtmlObj->buildCssLink($elementAry,&$base);
			break;
		case 'image':
			$returnAry=$base->HtmlObj->buildOldImg($elementAry,&$base);
			break;
		case 'url':
		//$base->DebugObj->printDebug($elementAry,1,'eleary');//xxx
			$returnAry=$base->HtmlObj->buildUrl($elementAry,&$base);
			break;
		case 'displaydata':
			$returnAry=$base->HtmlObj->buildDisplay($elementAry,&$base);
			//$base->DebugObj->printDebug($returnAry,1,'xxxd');
			break;
		case 'sqltotal':
      		$query=$elementAry['htmlelementsql'];
      		$conversion=$elementAry['htmlelementconversion'];
      		$result=$base->DbObj->queryTable($query,'read',&$base);
      		$passAry=array();
      		$dataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
      		$amountAry=$dataAry[0];
//xxx might be other than sum!!!
      		$amount=$amountAry['sum'];
      		$elementLabel=$elementAry['label'];
      		$elementClass=$elementAry['htmlelementclass'];
      		if ($elementClass != NULL){$classInsert="class=\"$elementClass\"";}
      		else {$classInsert=NULL;}
      		if ($conversion == 'money1'){
      			//- doesnt work on lindy
        		//$amount_formatted=money_format("$%.2n",($amount*.01));
        		$amount_formatted='$'.($amount*.01);
      		}
      		elseif ($conversion == 'date1') {
        		$amountAry=explode('-',$amount);
        		$amount_formatted="$amountAry[1]/$amountAry[2]/$amountAry[0]";
      		}
      		else {$amount_formatted=$amount;}
      		
      		$returnAry[]="<div $classInsert>$elementLabel $amount_formatted</div>";
      		break;
		case 'displayliteral':
		//echo "xxx";
			$class=$elementAry['htmlelementclass'];
			if ($class != null){$classInsert="class=\"$class\"";}
			else {$classInsert=null;}
			$id=$elementAry['htmlelementid'];
			if ($id != null){$idInsert="id=\"$id\"";}
			else {$idInsert=null;}
			$label=$elementAry['label'];
			$label_formatted=$base->UtlObj->returnFormattedString($label,&$base);
			//$base->DebugObj->printDebug("$label, $label_formatted",1,'label');//xxx
			$events_raw=$elementAry['htmlelementeventattributes'];
			$eventsInsert=$base->UtlObj->returnFormattedString($events_raw,&$base);
			if ($class != ''){
				$returnValue="<div $classInsert $idInsert $eventsInsert>$label_formatted</div>";	
			}
			else {$returnValue=$label_formatted;}
			$htmlLineNew=str_replace("!!inserthtmlelement_$htmlElementName!!",$returnValue,$htmlLine);
			$returnAry[]=$htmlLineNew;
			break;
		case 'inputselect':
			$returnAry=$base->HtmlObj->buildInputSelect($elementAry,&$base);
			break;
		default:
			$returnAry[]="<!-- invalid html element name: $htmlElementName, type: $type -->";
		}
		$base->DebugObj->printDebug('-rtn:insertHtmlElement',0);
		return $returnAry;
	}
//==================================================== 
	function insertTable($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertTable($paramFeed,'base')",0);
		$tableName=$paramFeed['param_1'];
		$tableType=$base->tableProfileAry[$tableName]['tabletype'];
		$tableRepeatNo=$base->tableProfileAry[$tableName]['tablerepeatno'];
		if ($tableType == NULL){$tableType='datadriven';}
		if ($tableName == 'generictable'){
			$tableName=$base->paramsAry['generictablename'];
			if ($tableName == NULL){$tableType='error no generictablename';}
	    	else {$tableType=$base->tableProfileAry[$tableName]['tabletype'];}
			$paramFeed['param_1']=$tableName;
		}
//--- place holder
		switch ($tableType){
		case 'placeholder':
			if ($tableRepeatNo<1){
				$returnAry=$this->insertPlaceHolderTable($paramFeed,&$base);
			}
			else {
				$returnAry=array();
				for ($ctr=0;$ctr<$tableRepeatNo;$ctr++){
					$base->paramsAry['ctr']=$ctr;
					$subReturnAry=$this->insertPlaceHolderTable($paramFeed,&$base);
					$returnAry=array_merge($returnAry,$subReturnAry);
				}
			}
			break;
		case 'datadriven':
//--- data driven
		$returnAry=array();
		$jsTableAry=array();
		$jsTableSelectAry=array();
		$htmlLineSt=$paramFeed['param_2'];
		//echo "tablename: $tableName<br>";//xxx
		//$base->DebugObj->printDebug($base->tableProfileAry,1,'xxx');
		//exit('xxx');
		$rowProfileAry=$base->rowProfileAry[$tableName];
		$tableProfileAry=$base->tableProfileAry[$tableName];
		$pageSize=$tableProfileAry['pagesize'];
		$pageNo=$base->paramsAry['pageno'];//xxxdproject: check pageno 
		if ($pageNo == ''){$pageNo=1;}
		$attributes="";
		$tableClass=$tableProfileAry['tableclass'];
		$tableEvenRowClass=$tableProfileAry['tableevenrowclass'];
		if ($tableEvenRowClass != NULL){$evenRowClassInsert="class=\"$tableEvenRowClass\"";}
		else {$evenRowClassInsert=NULL;}
		$tableOddRowClass=$tableProfileAry['tableoddrowclass'];
		if ($tableOddRowClass != NULL){$oddRowClassInsert="class=\"$tableOddRowClass\"";}
		else {$tableOddRowClass=NULL;}
		$saveForReference_table=$tableProfileAry['tablesaveforreference'];
		$saveForReference=$base->UtlObj->returnFormattedData($saveForReference_table,'boolean','internal');
		//echo "saveforreference: $saveForReference, internal: $saveForReference_internal";//xxx
		if ($tableClass != NULL){
			$attributes.=" class=\"$tableClass\""; 
			$tableClassInsert=" class=\"$tableClass\"";
		}
		else {
			$tableClassInsert=NULL;
		}
		$tableId=$tableProfileAry['tableid'];
		if ($tableId != NULL){$attributes.=" id=\"$tableId\"";}
		else {$attributes.=" id=\"$tableName\"";}
		$cellSpacing=$tableProfileAry['cellspacing'];
		if ($cellSpacing != ""){$attributes.=" cellspacing=\"$cellSpacing\"";}
		$cellPadding=$tableProfileAry['cellpadding'];
		if ($cellPadding != ""){$attributes.=" cellpadding=\"$cellPadding\"";}
		$background=$tableProfileAry['background'];
		if ($background != ""){$attributes.=" background=\"$background\"";}
		$align=$tableProfileAry['align'];
		if ($align != ""){$attributes.=" align=\"$align\"";}
		$width=$tableProfileAry['tablewidth'];
		if ($width != ''){$attributes.=" width=\"$width\"";}
		$frame=$tableProfileAry['tableframe'];
		if ($frame != ''){$attributes.=" frame=\"$frame\"";}
		else {
			$borderInt=$tableProfileAry['border'];
			if ($borderInt != ""){$attributes.=" border=\"$borderInt\"";}
		}
		$maxTableRows=$tableProfileAry['tablemaxrows'];
		$minTableRows=$tableProfileAry['tableminrows'];
		$tableTitle=$tableProfileAry['tabletitle'];
		$job=$base->paramsAry['job'];
		$rowNo=0;
		$returnAry[]="<!- data driven table: $tableName -->\n";
		$returnAry[]= "<table $attributes>\n";
		if ($tableTitle != null){
			$returnAry[]="<caption>$tableTitle</caption>\n";
		}
		$columnsAry=$base->columnProfileAry[$tableName];
		//echo "tablename: $tableName<br>";//xxx
		//$base->DebugObj->printDebug($base->columnProfileAry,1,'xxx');
		$columnsSortOrder=$base->columnProfileAry['sortorderary_'.$tableName];
		//$base->DebugObj->printDebug($base->columnProfileAry,1,'xxxd');
		$workAry=$this->breakOutTable($columnsAry,$columnsSortOrder,&$base);
		$columnsAry=$workAry['columnsary'];
		//$base->DebugObj->printDebug($columnsAry,1,'col');//xxx
		$columnsSortOrder=$workAry['columnssortorderary'];
		//$base->DebugObj->printDebug($columnsSortOrder,1,'sort');//xxx
		$totCols=count($columnsAry);
		//echo "totcols: $totCols<br>";//xxx
//-------------------------------------------------  column heading
// - 1st column must have a name
			$rowNo=0;
			$firstColumnName=$columnsSortOrder[1];
			if ($columnsAry[$firstColumnName]['columnheading'] != ''){
				$rowNo++;
				$bgcolor=$rowProfileAry[$rowNo]['bgcolor'];
				if ($bgcolor != ""){$bgColorLine=" bgcolor=\"$bgcolor\"";}
				else {$bgColorLine="";}
				$color=$rowProfileAry[$rowNo]['color'];
				if ($color != ""){$colorLine=" color=\"$color\"";}
				else {$colorLine="";}
				$returnAry[]= "<tr$bgColorLine$colorLine $tableClassInsert>";
				for ($colCtr=1;$colCtr<=$totCols;$colCtr++){
					$columnName=$columnsSortOrder[$colCtr];
					$columnHeading=$columnsAry[$columnName]['columnheading'];
					$columnHeadingSpan=$columnsAry[$columnName]['columnheadingspan'];
					$columnHeadingClass=$columnsAry[$columnName]['columnheadingclass'];
					if ($columnHeadingClass != NULL){$classInsert="class=\"$columnHeadingClass\"";}
					else {$classInsert=NULL;}
					if ($columnHeading != "") {
						if ($columnHeadingSpan > 0){$returnAry[]= "<td colspan=$columnHeadingSpan".">";}
						else {$returnAry[]="<td>";}
						$returnAry[]= "<div $classInsert> $columnHeading</div>";
						$jsTableColumnAry=array();
						$jsTableColumnAry[]="$columnHeading";
						$returnAry[]= "</td>";	
					}
				}
				$returnAry[]= "</tr>\n";
				$jsTableAry[]=$jsTableColumnAry;
				$jsTableSelectAry[]='_';
			}
//-----------------------------------------------------column title
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
		$returnAry[]= "<tr$bgColorLine$colorLine $tableClassInsert>";
		$jsTableColumnAry=array();
//- columnsAry is 1->end
		for ($colCtr=1;$colCtr<=$totCols;$colCtr++){
			$columnName=$columnsSortOrder[$colCtr];
			$columnAry=$columnsAry[$columnName];
			$columnTitleSt_raw=$columnAry['columntitle'];
			$columnTitleSt=$base->UtlObj->returnFormattedString($columnTitleSt_raw,&$base);
			$columnName=$columnAry['columnname'];
			$columnType=$columnAry['columntype'];
			$columnTitleClass=$columnAry['columntitleclass'];
			if ($columnTitleClass != null){$columnTitleClassInsert="class=\"$columnTitleClass\"";}
			else {$columnTitleClassInsert=null;}
			if ($columnType == 'url'){
				$columnSortName=$columnAry['urlname'];
				$extractedStr=$base->UtlObj->extractStr('%',$columnSortName);
				if ($extractedStr != ''){$columnSortName=$extractedStr;}
			} else {$columnSortName=$columnName;}
			//$jobOverride=$base->paramsAry['jobname'];
			//if ($jobOverride != "") {$insLine="&jobname=$jobOverride";}
			//else {$insLine="";}
			$insLine=NULL;
			$returnAry[]= "<th $tableClassInsert>";
			$jobLocal=$base->systemAry['joblocal'];
			//$columnTitle= "<a href=\"$jobLocal$job&sort=$columnSortName$insLine\" $columnTitleClassInsert>";
			$columnTitle= "<a href=\"#\" $columnTitleClassInsert>";
			$columnTitle.= "$fontLine$columnTitleSt$fontLineEnd";
			$columnTitle.= "</a>";
			$returnAry[]=$columnTitle;
			$jsTableColumnAry[]="$columnTitle";
			$returnAry[]= "</th>";
		}
		$returnAry[]= "</tr>\n";
		$jsTableAry[]=$jsTableColumnAry;
		$jsTableSelectAry[]='_';
//----------------------------------------------------  get the data
	$debugStrg='paramsary: ';
	foreach ($base->paramsAry as $name=>$theValue){$debugStrg.=$name.',';}
	$base->FileObj->writeLog('debug',$debugStrg,&$base);
	$passDataAry=$base->DbObj->getTableData($tableName,&$base);
	$cnt=count($passDataAry['dbtableary']);
	$base->FileObj->writeLog('dbobject','cnt: '.$cnt,&$base);
//--- end get the data
	if ($saveForReference===true){
		//echo "save for reference: $tableName";//xxx
		$base->insertedTablesAry[$tableName]=$passDataAry['dbtableary'];
	}
	//echo "insert $tableName into insertedtablesary";//xxx
	//if ($tableName = 'parenttable'){$base->DebugObj->printDebug($base->insertedTablesAry,1,'pass');}//xxx
	$columnDataAry=$passDataAry['dbtableary'];
	$dbTableMetaAry=$passDataAry['dbtablemetaary'];
	$parentSelectorName=$passDataAry['parentselectorname'];
	$keyName=$passDataAry['keyname'];
	$dbTableName=$passDataAry['dbtablename'];
	$theFiltersAry=$passDataAry['thefilters'];
		$dataCnt=count($columnDataAry);
		//echo "datacnt: $dataCnt<br>";//xxx
		$dataCutoffCnt=$dataCnt;
		if ($maxTableRows>0){
			if ($maxTableRows<$dataCnt){$dataCnt=$maxTableRows;}
		}
		if ($minTableRows>0){
			if ($minTableRows>$dataCnt){$dataCnt=$minTableRows;}
		}
		if ($pageSize<=0 || $pageSize>$dataCnt){
			 $pageSize=$dataCnt;
		}
//--------------------------------------------------------- loop through data
		$rowIsOdd=false;
		//echo "$tableName: $dataCnt<br>";//xxxd
		//$base->DebugObj->printDebug($passDataAry,1,'xxxd');
		for ($dataCtr=0;$dataCtr<$dataCnt;$dataCtr++){
			//echo "datactr: $dataCtr<br>";//xxx
			$base->paramsAry['tablerowno']=$dataCtr;//xxx
			if ($rowIsOdd){
				$rowIsOdd=false;
				$rowClassInsert=$evenRowClassInsert;
			}
			else {
				$rowIsOdd=true;
				$rowClassInsert=$oddRowClassInsert;
			}
			//xxxdproject: pageno display
			//if ($dataCtr<=($pageSize-1)){$returnAry[]= "<tr $tableClassInsert>";}
			if ($dataCtr>=(($pageSize-1)*($pageNo-1)) && $dataCtr<=(($pageSize-1)*$pageNo)){$returnAry[]= "<tr $tableClassInsert>";}
			$overrideJob="";
			$parentSelectorValue=$columnDataAry[$dataCtr][$parentSelectorName];
			$jsTableColumnAry=array();
			$selectData='';
// columnsAry is 1->end
			for ($colCtr=1;$colCtr<=$totCols;$colCtr++){
				//echo "row: $dataCtr, col: $colCtr<br>";//xxx
					$colAccessName=$columnsSortOrder[$colCtr];
					$columnAry=$columnsAry[$colAccessName];
					$colName=$columnAry['columnname'];
					//echo "tablename: $tableName, colname: $colName<br>";//xxxd
					//$base->DebugObj->printDebug($columnAry,1,'xxxd columnary');
					$columnSave_raw=$columnAry['columnsave'];
					$columnSave=$base->UtlObj->returnFormattedData($columnSave_raw,'boolean','internal');
					$columnDoFormat_raw=$columnAry['columndoformat'];
					$columnDoFormat=$base->UtlObj->returnFormattedData($columnDoFormat_raw,'boolean','internal');
					$urlName=$columnAry['urlname'];
					$columnClass=$columnAry['columnclass'];
					if ($columnClass == NULL){$columnClass=$tableClass;}
					if ($columnClass!=NULL){$columnClassInsert="class=\"$columnClass\"";}
					else {$columnClassInsert=NULL;}
					$columnEvents_raw=$columnAry['columnevents'];
					$columnEvents=$base->UtlObj->returnFormattedString($columnEvents_raw,&$base);
					if ($columnEvents!=NULL){$columnEventsInsert="$columnEvents";}
					else {$columnEventsInsert=NULL;}
					//echo "colname: $colName, urlname: $urlName<br>";//xxx
					$selectAble_db=$columnAry['selectmode'];
					$selectAble=$base->UtlObj->returnFormattedData($selectAble_db,'boolean','internal');
					$pos=strpos($colName,'_',0);
					if ($pos>0){$colName=substr($colName,0,$pos);}
					$parentSelectorFlag=$base->DbObj->returnBoolByType($dbTableMetaAry[$colName]['dbcolumnparentselector']);
					$columnTypeSt=$columnAry['columntype'];
					$colNameAry=explode('/',$colName);
					if ($colNameAry[1] != NULL){
						$colDataSt=$columnDataAry[$dataCtr][$colNameAry[0]].'/'.$columnDataAry[$dataCtr][$colNameAry[1]];	
					}
					else {
						$colDataSt=$columnDataAry[$dataCtr][$colName];
					}
					//echo "tablename: $tableName: $colDataSt, datactr: $dataCtr, colname: $colName<br>";//xxxd
					if ($columnSave){
						$base->paramsAry[$colName]=$colDataSt;	
					}
					$colMaxSize=$columnAry['columnmaxsize'];
					$colMinSize=$columnAry['columnminsize'];
					if ($colMinSize>0){
						$colDataStLen=strlen($colDataSt);
						if ($colDataStLen<$colMinSize){
							//echo "xxx1:<pre> $colDataSt</pre><br>";
							$diffSize=($colMinSize-$colDataStLen);							
							for ($lp=0;$lp<$diffSize;$lp++){$colDataSt.=' ';}
							$colDataSt.='&nbsp;';	
							//echo "xxx2:<pre> $colDataSt</pre><br>";
						}	
					}
					if ($parentSelectorFlag){
						$selectorColName=$colName;
						$overrideJob=$colDataSt;
					}
					$useColData = '';
					if ($colDataSt == ""){
						$colDisplayDataSt="&nbsp;";
					}
					//echo "ctr: $colCtr, type: $columnTypeSt<br>";//xxx
						switch ($columnTypeSt){
							case 'img':
								$colDisplayDataSt="<img src=\"$colDataSt\" $columnClassInsert $columnEventsInsert>";
								break; 
							case 'boolean':
									if ($colDataSt == 'f'||$colDataSt == 'false'||$colDataSt == '0'|| $colDataSt == NULL){$colDisplayDataSt='';}
									else {$colDisplayDataSt='t';}
									//echo "$colDataSt:$colDisplayDataSt, ";//xxx
									break;
							case 'bool':
									$colDataSt=trim($colDataSt);
									if ($colDataSt == 'f'||$colDataSt == 'false'||$colDataSt == '0'|| $colDataSt == NULL){$colDisplayDataSt='';}
									else {$colDisplayDataSt='t';}
									//echo "$colDataSt -> $colDisplayDataSt ";//xxx
									break;
							case 'email':
									$colDisplayDataSt="<div $columnClassInsert>$colDataSt</div>";
									break;
							case 'url':
									//- joblink
									$jobLinkSt_raw=$columnAry['joblink'];
									$oldJobLinkSt=$jobLinkSt_raw;
									$pos=strpos('x'.$jobLinkSt_raw,'sessionname',0);
									if ($pos<=0){
										$sessionValue=$base->paramsAry['sessionname'];
										//- dont have to worry about session stuff if joblink is # because only goes to javascript
										if ($sessionValue != NULL && $jobLinkSt_raw != '#'){
											$jobLinkSt_raw.="&sessionname=$sessionValue";
										}	
									}	
									$jobLinkSt=$base->UtlObj->returnFormattedStringDataFed($jobLinkSt_raw,$columnDataAry[$dataCtr],&$base);
									//echo "before: $jobLinkSt_raw, after: $jobLinkSt<br>";//xxx
									//- url 
									$urlNameSt_raw=$columnAry['urlname'];
									$urlNameSt=$base->UtlObj->returnFormattedStringDataFed($urlNameSt_raw,$columnDataAry[$dataCtr],&$base);
									$useColData=$urlNameSt;
									//- column events
									$columnEvents_raw=$columnAry['columnevents'];
									//- convert the datactr here in the table object
									$columnEvents=str_replace('%datactr%',$dataCtr,$columnEvents_raw);
									//echo "columnevents: $columnEvents, raw: $columnEvents_raw<br>";//xxxd
									$columnEvents=$base->UtlObj->returnFormattedStringDataFed($columnEvents,$columnDataAry[$dataCtr],&$base);
									//echo "new columnevents: $columnEvents<br>";//xxxd
									//$base->DebugObj->printDebug($columnDataAry[$dataCtr],1,'xxxd');
									//- final check of joblink
									$pos=strpos('x'.$jobLinkSt,'http',0);
									//echo "pos: $pos, joblinkst: $jobLinkSt<br>";//xxxd
									$columnTarget=$columnAry['urltarget'];
									//echo "name: $columnName, target: $columnTarget<br>";//xxxd
									if ($columnTarget=='newpage'){
										$colTargetInsert="target=\"_blank\"";
									} else {$colTargetInsert=null;}
									//$base->DebugObj->printDebug($columnAry,1,'xxxd');//xxxd
									if ($pos>0 or $jobLinkSt=='#'){
										$colDisplayDataSt="<a href=\"$jobLinkSt\" $columnClassInsert $tableIdInsert $colTargetInsert $columnEvents>$urlNameSt</a>";
									}
									else {
										$jobLocal=$base->systemAry['joblocal'];
										$colDisplayDataSt="<a href=\"$jobLocal$jobLinkSt&$colName=$colDataSt\" $colTargetInsert $columnClassInsert>$urlNameSt</a>";
									}
									break;
							case 'urlovr':
									$jobLinkSt=$columnAry['joblink'];
									$urlNameSt=$columnAry['urlname'];
									if ($urlNameSt == 'clr'){$insLine="overridevalue=";}
									else {$insLine="overridename=$parentSelectorName&overridevalue=$parentSelectorValue";}
									$jobLocal=$base->systemAry['joblocal'];
									$colDisplayDataSt="<a href=\"$jobLocal$jobLinkSt&$insLine\">$urlNameSt</a>";
									$useColData="$urlNameSt";
									break;
							case 'phone':
									if ($colDataSt != NULL){
										$colDisplayDataSt=substr($colDataSt,0,3).'.'.substr($colDataSt,3,3).'.'.substr($colDataSt,6,4);
									}
									else {$colDisplayDataSt=NULL;}
									break;
							default:
							// types: text/pre
							//echo "tablename: $tableName, $colDataSt<br>";//xxxd
									$columnEvents_raw=$columnAry['columnevents'];
									$columnEvents=$base->UtlObj->returnFormattedStringDataFed($columnEvents_raw,$columnDataAry[$dataCtr],&$base);
									if ($colMaxSize>0){
										$useColDataSt=chunk_split($colDataSt,$colMaxSize,'<br>');
									}
									else {$useColDataSt=$colDataSt;}
									if ($useColDataSt==null){$useColDataSt='&nbsp;';}
									else {
										$colType=$dbTableMetaAry[$colName]['dbcolumntype'];
										$colConversion=$dbTableMetaAry[$colName]['dbcolumnconversionname'];
										//$base->DebugObj->printDebug($dbTableMetaAry[$colName],1,'xxx');
										if ($colConversion != null){$colType.="_$colConversion";}
										//echo "xxx: $colName $useColDataSt, $colType, $colConversion<br>";//xxxd
										$useColDataSt=$base->UtlObj->returnFormattedData($useColDataSt,$colType,'table',&$base);
										//xxxf
										//echo "usecoldatast: $useColDataSt<br>";//xxxd
									}
									//xxxf below converts %cr% which screws up ajax so put in plug
									$useColDataSt=str_replace('%cr%','%br%',$useColDataSt);
									$useColDataSt=$base->UtlObj->returnFormattedString($useColDataSt,&$base);
									if ($columnTypeSt == 'pre'){
										$colDisplayDataSt="<pre $columnClassInsert $columnEvents>$useColDataSt</pre>";
									} else {
										$colDisplayDataSt="<p $columnClassInsert $columnEvents>$useColDataSt</p>";
									}
									//xxxf
									break;
						}
						//echo "coldisplaydatast: $colDisplayDataSt<br>";//xxx
						if ($rowClassInsert == NULL){$useClassInsert=$columnClassInsert;}
						else {$useClassInsert=$rowClassInsert;}
						//xxxdproject: check for page no
					//if ($dataCtr<=$pageSize-1){$returnAry[]="<td $useClassInsert>$colDisplayDataSt</td>";}
					if ($dataCtr>=(($pageSize-1)*($pageNo-1)) && $dataCtr<=(($pageSize-1)*$pageNo)){$returnAry[]="<td $useClassInsert>$colDisplayDataSt</td>";}
					//xxxd
					//echo "$colName, selectable: $selectAble, usecoldatast: $useColData, coldatast: $colDataSt<br>";//xxxd
					if ($selectAble){
						if ($useColData != ''){$selectData.=$useColData.'_';}
						else {$selectData.=$colDataSt.'_';}
					} // end if selectable
					$jsTableColumnAry[]="$colDisplayDataSt";
				} // end for colctr
				//echo "<br>";//xxx
				//xxxdproject:  check page size
			//if ($dataCtr<=$pageSize-1){$returnAry[]="</tr>\n";}
			if ($dataCtr>=(($pageSize-1)*($pageNo-1)) && $dataCtr<=(($pageSize-1)*$pageNo)){$returnAry[]="</tr>\n";}
			$jsTableAry[]=$jsTableColumnAry;
			//xxxd
			$jsTableSelectAry[]=$selectData;
		} // end for datactr
		$returnAry[]= "</table>\n";
		//xxx - recent changes: 5/6/9
		//echo "xxx: enter js stuff for : $tableName<br>";//xxx
		//if (!array_key_exists('jstableary',$base->tableProfileAry)){
		$base->tableProfileAry['jstableary'][$tableName]=$jsTableAry;
		$base->tableProfileAry['jstableselectary'][$tableName]=$jsTableSelectAry;
		//-xxxr below should go away eventually
		$base->tableProfileAry[$tableName]['jsalldataary']=$columnDataAry;
		//}
		$base->tableProfileAry['jsdatadefs'][$tableName]=$dbTableMetaAry;//xxx
		$base->tableProfileAry['etc'][$tableName]['keyname']=$keyName;
		$base->tableProfileAry['etc'][$tableName]['dbtablename']=$dbTableName;
		$base->tableProfileAry['thefilters'][$tableName]=$theFiltersAry;
		//$base->DebugObj->printDebug($dbTableMetaAry,1,'xxx');
		$base->tableProfileAry['jsalldataary'][$tableName]=$columnDataAry;
		//$base->DebugObj->printDebug($jsTableAry,1,'xxx: in inserttable looking at jstableary');//xxx
		//$base->DebugObj->displayStack();//xxx
		break;
		default:
			$returnAry[]="<table><tr><td><!- table type error: $tableType -></td></tr></table>";
		} // end switch
		$tableDontPrint_raw=$base->tableProfileAry[$tableName]['tabledontprint'];
		$tableDontPrint=$base->UtlObj->returnFormattedData($tableDontPrint_raw,'boolean','internal',&$base);
		if ($tableDontPrint){$doReturnAry = array();}
		else {$doReturnAry=$returnAry;}
		return $doReturnAry;
	}
//=====================================================
	function insertPlaceHolderTable($paramFeed,&$base){
	//echo "TagObj xxxd5: run static table<br>";//xxxd1
	$returnAry=array();
	//$base->DebugObj->printDebug($paramFeed,1,'paramfeed');//xxx
	$tableName=$paramFeed['param_1'];
	//echo "tablename: $tableName<br>";//xxxd
	$placeHolderNameAry=array();
	$placeHolderTypeAry=array();
	$placeHolderClassAry=array();
	$placeHolderIdAry=array();
	$placeHolderEventAry=array();
	$placeHolderSpanAry=array();
	$placeHolderColAry=array();
	//$base->DebugObj->printDebug($base->columnProfileAry,1,'col');//xxx
// - build xref
	$maxRow=0;
	$maxCol=0;
	$base->FileObj->writeLog('debug1',"start placeholder table: $tableName",&$base);
	if (count($base->columnProfileAry[$tableName])){
	foreach ($base->columnProfileAry[$tableName] as $columnName=>$columnAry){
		$columnType=$columnAry['columntype'];
		$columnClass=$columnAry['columnclass'];
		$columnId=$columnAry['columnid'];
		$columnEvent=$columnAry['columnevents'];
		$rowNo=$columnAry['rowno'];
		$colNo=$columnAry['columnno'];
		$colSpan=$columnAry['columnheadingspan'];
		$rowSpan=$columnAry['rowspan'];
		//echo "rowno: $rowNo, colno: $colNo<br>";//xxx
		if ($rowNo>$maxRow){$maxRow=$rowNo;}
		if ($colNo>$maxCol){$maxCol=$colNo;}
		$placeHolderNameAry[($rowNo-1)][($colNo-1)]=$columnName;
		$placeHolderTypeAry[($rowNo-1)][($colNo-1)]=$columnType;
		$placeHolderClassAry[($rowNo-1)][($colNo-1)]=$columnClass;
		$placeHolderIdAry[($rowNo-1)][($colNo-1)]=$columnId;
		$placeHolderEventAry[($rowNo-1)][($colNo-1)]=$columnEvent;
		$placeHolderSpanAry[($rowNo-1)][($colNo-1)]['colspan']=$colSpan;
		$placeHolderSpanAry[($rowNo-1)][($colNo-1)]['rowspan']=$rowSpan;
		$placeHolderColAry[($rowNo-1)][($colNo-1)]=$columnAry;
	}
	}
	else {$placeHolderAry=array();}
// - build html from xref
	//echo "maxrow: $maxRow, maxcol: $maxCol<br>";//xxx
//- class
	$tableClass_raw=$base->tableProfileAry[$tableName]['tableclass'];
	$tableClass=$base->UtlObj->returnFormattedString($tableClass_raw,&$base);
	if ($tableClass != NULL){$tableClassInsert="class=\"$tableClass\"";}
	else {$tableClassInsert=NULL;}
//- id
	$tableId_raw=$base->tableProfileAry[$tableName]['tableid'];
	$tableId=$base->UtlObj->returnFormattedString($tableId_raw,&$base);
	if ($tableId != NULL){$tableIdInsert="id=\"$tableId\"";}
	else {$tableIdInsert=NULL;}
//- defaultdisplay
	$defaultDisplay=$base->tableProfileAry[$tableName]['defaultdisplay'];
	if ($defaultDisplay == 'dontbuildtable'){$dontBuildTable=true;}
	else {$dontBuildTable=false;}
//- table variables
	if ($dontBuildTable){
		$tableStart=NULL;
		$tableRow=NULL;
		$tableRowEnd=NULL;
		$tableCell=NULL;
		$tableCellEnd=NULL;
		$tableEnd=NULL;
	}
	else {
		$tableStart="<table $tableClassInsert $tableIdInsert>\n";
		$tableRow="<tr>\n";
		$tableRowEnd="</tr>\n";
		$tableCell="<td $classInsert $spanInsert>\n";
		$tableCellEnd="</td>\n";
		$tableEnd="</table>\n";
	}
//- <table>
	$returnAry[]=$tableStart;
	$dontDoAnything=false;
	//$base->DebugObj->printDebug($placeHolderNameAry,1,'xxxd');
	for ($rowCtr=0;$rowCtr<$maxRow;$rowCtr++){
		$returnAry[]=$tableRow;
		for ($colCtr=0;$colCtr<$maxCol;$colCtr++){
			//echo "row: $rowCtr, col: $colCtr<br>";//xxxd
//- extract from internally built arrays
			$runName=$placeHolderNameAry[$rowCtr][$colCtr];
			$runType=$placeHolderTypeAry[$rowCtr][$colCtr];
			//echo "TagObj xxxd5.5 rowctr: $rowCtr, colctr: $colCtr, runname: $runName, runtype: $runType<br>";//xxxd
			$runClass=$placeHolderClassAry[$rowCtr][$colCtr];
			$runId=$placeHolderIdAry[$rowCtr][$colCtr];
			$runEvent=$placeHolderEventAry[$rowCtr][$colCtr];
			$columnAry=$placeHolderColAry[$rowCtr][$colCtr];
			//xxxd - setup for default table values
			$runNameAry=explode('_',$runName);
			$runName=$runNameAry[0];
			$htmlLine=$runNameAry[1];
			$htmlLine2=$runNameAry[2];
			$paramFeed['param_1']=$runName;
			$paramFeed['param_2']=$htmlLine;
			$paramFeed['param_3']=$htmlLine2;
			//echo "$rowCtr, $colCtr, $runName<br>";//xxxd
//- class insert
			if ($runClass != NULL){
				$runClass_html=$base->UtlObj->returnFormattedData($runClass,'varchar','html',&$base);
				$classInsert="class=$runClass_html";
			}
			else {$classInsert=$tableClassInsert;}
//- id insert
			if ($runId != NULL){
				$runId_html=$base->UtlObj->returnFormattedData($runId,'varchar','html',&$base);
				$idInsert="id=$runId_html";
			}
			else {$idInsert=null;}
//- event insert
			if ($runEvent != null){
				$eventInsert=$base->UtlObj->returnFormattedString($runEvent,&$base);
				$eventInsert=$base->UtlObj->returnFormattedData($eventInsert,'varchar','html',&$base);
			}
			else {$eventInsert=null;}
//- colspan insert
			$colSpan=$placeHolderSpanAry[$rowCtr][$colCtr]['colspan'];
			if ($colSpan>1){
				$colSpan_html=$base->UtlObj->returnFormattedData($colSpan,'numeric','html',&$base);
				$colSpanInsert="colspan=$colSpan_html";
			}
			else {$colSpanInsert=NULL;}
//- rowspan insert
			$rowSpan=$placeHolderSpanAry[$rowCtr][$colCtr]['rowspan'];
			if ($rowSpan>1){
				$rowSpan_html=$base->UtlObj->returnFormattedData($rowSpan,'numeric','html',&$base);
				$rowSpanInsert="rowspan=$rowSpan_html";
			}
			else {$rowSpanInsert=NULL;}
//- table cell with inserts
			if ($dontBuildTable){$tableCell=NULL;}
			else {$tableCell="<td $eventInsert $idInsert $classInsert $colSpanInsert $rowSpanInsert>\n";}
//- table cell contents by run type
			$dontDoAnything=false;
			//echo "tablename: $tableName, colname: $columnName, colctr: $colCtr, rowctr: $rowCtr, runtype: $runType<br>";//xxxd
			//$base->DebugObj->printDebug($returnAry,1,'xxxd');//xxxd
			$base->FileObj->writeLog('debug1',"runtype: $runType",&$base);
			//echo "tagobj xxxd6 run $runType<br>";
			switch ($runType){
				case '':
					break;
				case 'operation':
					$operationAry=array('pluginname'=>$runName,'operationname'=>'runoperation');
					$base->PluginObj->runOperationPlugin($operationAry,&$base);
					break;
				case 'errormessage':
					$errorMessageName=$runName;
					$errorMessage_raw=$base->ErrorObj->retrieveError($errorMessageName,&$base);
					//echo "name: $errorMessageName, errraw: $errorMessage_raw<br>";//xxxd
					$errorMessage=$base->UtlObj->returnFormattedString($errorMessage_raw,&$base);
					if ($errorMessage_raw == null|| $errorMessage_raw == ''){
						$errorMessage_raw=$base->ErrorObj->retrieveError($errorMessageName,&$base);	
						$errorMessage=$base->UtlObj->returnFormattedString($errorMessage_raw,&$base);
					}
					//echo "name: $errorMessageName, err: $errorMessage<br>";//xxxd
					$returnAry[]=$tableCell;
					$returnAry[]=$errorMessage;
					$returnAry[]=$tableCellEnd;
					break;
				case 'tag':
				//$base->DebugObj->printDebug($columnAry,1,'tag');//xxx
				$jobLink=$columnAry['joblink'];
				$jobLinkAry=explode('_',$jobLink);
				$pluginName=$jobLinkAry[0];
				$runName=$jobLinkAry[1];
				$htmlLine="!!$pluginName_$runName!!";
				$paramFeed['param_1']=$runName;
				$paramFeed['param_2']=$htmlLine;
				$paramFeed['param_3']=1;
				//echo "$pluginName, $runName, $htmlLine<br>";//xxxd
				if ($pluginName != ""){
				$subReturnAry=$base->PluginObj->runTagPlugin($pluginName,$paramFeed,&$base);
				} else {$returnAry=array();}
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxx
				//exit();
				break;
			case 'para':
				//echo "paragraph: $runName<br>";//xxxd
				$paramFeed=array('param_1'=>$runName);
				$subReturnAry=$base->Plugin002Obj->insertParagraph($paramFeed,&$base);
				//$base->DebugObj->printDebug($subReturnAry,1,'rtn');//xxxd
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				//$base->DebugObj->printDebug($subReturnAry,1,'rtn');//xxx
				break;
			case 'htmlele':
				$htmlLine="!!inserthtmlelement_$runName!!";
				$paramFeed=array('param_1'=>$runName,'param_2'=>$htmlLine);
				$subReturnAry=$base->TagObj->insertHtmlElement($paramFeed,&$base);
				//$base->DebugObj->printDebug($subReturnAry,1,'srt');//xxx
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'form':
				$paramFeed=array('param_1'=>$runName);
				$subReturnAry=$base->TagObj->insertForm($paramFeed,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'table':
				$paramFeed=array('param_1'=>$runName);
				$subReturnAry=$base->TagObj->insertTable($paramFeed,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'stylesheet':
				$jobLink=$columnAry['joblink'];
				$elementAry=array('joblink'=>$jobLink);
				$subReturnAry=$base->HtmlObj->buildCssLink($elementAry,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'img':
				$paramFeed=array('param_1'=>$runName);
				//echo "runname: $runName<br>";//xxx
				$subReturnAry=$base->Plugin002Obj->insertImg($paramFeed,&$base);
				//$base->DebugObj->printDebug($subReturnAry,1,'sra');
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'initjs':
				$paramFeed=array('param_1'=>$runName);
				//$subReturnAry=array();
				$subReturnAry=$base->TagObj->insertDbTableInitJs($paramFeed,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'title':
				$paramFeed=array('param_2'=>'!!inserttitle!!');
				$subReturnAry=$base->TagObj->insertTitle($paramFeed,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'style':
				$paraFeed=array();			
				$subReturnAry=$base->Plugin002Obj->insertStyle($paramFeed,&$base);
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				break;
			case 'deprecatederrormessage':
				$errorMessageName=$runName;
				$errorMessage_raw=$base->errorProfileAry[$errorMessageName];
				$errorMessage=$base->UtlObj->returnFormattedString($errorMessage_raw,&$base);
				echo "yoxxxerrormessgeName: $runName, errmsgraw: $errorMessage_raw, errmsg: $errorMessage<br>";//xxxd
				$base->DebugObj->printDebug($base->errorProfileAry,1,'xxxd');
				$returnAry[]=$tableCell;
				$returnAry[]="<table $classInsert><tr><td>";
				$returnAry[]=$errorMessage;
				$returnAry[]="</td></tr></table>";
				$returnAry[]=$tableCellEnd;
				break;
			case 'url':
				//$base->DebugObj->printDebug($columnAry,1,'colary');//xxx
				$jobLink_raw=$columnAry['joblink'];
				$pos=strpos('x'.$jobLink_raw,'sessionname',0);
				if ($pos<=0){
					$sessionValue=$base->paramsAry['sessionname'];
					if ($sessionValue != NULL){$jobLink_raw.="&sessionname=$sessionValue";}	
				}	
				$oldJobLink=$jobLink_raw;
				$urlName_raw=$columnAry['urlname'];
				$imageName=$columnAry['imagename'];
				if ($imageName != NULL){
					$imageSource=$base->imageProfileAry[$imageName]['imagesource'];	
					$imageClass=$base->imageProfileAry[$imageName]['imageclass'];
					if ($imageClass == NULL){$imageClassInsert=$classInsert;}
					else {$imageClassInsert="class=\"$imageClass\"";}
					$imageSourceCommand="<img src=\"$imageSource\" $imageClassInsert>";
				}
				else {$imageSourceCommand=NULL;}
				//- events
				$events=$columnAry['columnevents'];
				$events=$base->UtlObj->returnFormattedString($events,&$base);
				//below shows error!!!
				$urlName=$base->UtlObj->returnFormattedString($urlName_raw,&$base);
				if ($urlName == NULL){$urlName=$imageSourceCommand;}
				$jobLink=$base->UtlObj->returnFormattedString($jobLink_raw,&$base);
				$pos=strpos('x'.$jobLink,'http',0);
				//echo "urlnameraw: $urlName_raw, urlname: $urlName, joblink: $jobLink<br>";//xxx
				//- it is assumed that the events are put onto the <td>
				if ($pos>0 || $jobLink=='#'){
					$colDisplayData="<a href=\"$jobLink\" $columnClassInsert $tableIdInsert>$urlName</a>";
				}
				else {
					$jobLocal=$base->systemAry['joblocal'];
					$colDisplayData="<a href=\"$jobLocal$jobLink\" $columnClassInsert $tableIdInsert>$urlName</a>";
				}
				$returnAry[]=$tableCell;
				$returnAry[]="$colDisplayData\n";
				$returnAry[]=$tableCellEnd;
				break;
			case 'calendar':
				$returnAry[]=$tableCell;
				$subReturnAry=$base->CalendarObj->insertCalendarHtml($runName,&$base);
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;	
				break;
			case 'text':
				$returnAry[]=$tableCell;
				$jobLink_raw=$columnAry['joblink'];
				$theText=$jobLink_raw;
				$returnAry[]=$theText;
				$returnAry[]=$tableCellEnd;
				break;
			default:
				//xxxd - fix for bad table setups 'menu' -> 'insertmenu'
				$oldRunType=$runType;
				if ($runType=='menu'){$runType='insertmenu';}
				//$base->DebugObj->printDebug($paramFeed,1,'xxxd paramfeed');
				$paramFeed1=$paramFeed['param_1'];
				//xxxd - toplogo does not have a run type!!!!!!
				if ($runType != ''){
					$base->FileObj->writeLog('debug1',"run tag plugin, runtype: $runType, paramfeed1: $paramFeed1",&$base);
					$subReturnAry=$base->PluginObj->runTagPlugin($runType,$paramFeed,&$base);
					$base->FileObj->writeLog('debug1',"end run tag plugin, runtype: $runType, paramfeed1: $paramFeed1",&$base);
				}
				else {
					//- this program assumes that every row/column is full - but may not always be
					if ($runName != ''){
						echo "error runName: $runName, runtype: $runType, col: $colCtr, row: $rowCtr in table $tableName";
						$base->DebugObj->printDebug($placeHolderNameAry,1,'placeholdernameary');
						$base->DebugObj->printDebug($placeHolderTypeAry,1,'placeholdertypeary');
						exit();//xxxf
					}
				}
				$returnAry[]=$tableCell;
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]=$tableCellEnd;
				//$cnt=count($returnAry);
				//echo "oldRunType: $oldRunType, runtype: $runType, paramfeed1: $paramFeed1, rtnarycnt: $cnt<br>";//xxxd
				//echo "error in runtype: $runType<br>";//xxx
				//$dontDoAnything=true;
				//$returnAry[]=$runName.':'.$runType;
			}
		}	
		//echo "TagObj xxxf6: return from it<br>";
		$returnAry[]=$tableRowEnd;
	}
	//echo "TagObj xxxd5: all done with statit table<br>";//xxxd666
	$returnAry[]=$tableEnd;
	//echo "TagObj xxxd5: return from it";
	//$base->DebugObj->printDebug($returnAry,1,'rtnary');//xxx
	//$cnt=count($returnAry);//xxxd
	//echo "tablename: $tableName, rtnarycnt: $cnt<br>";//xxxd
	//$base->DebugObj->printDebug($returnAry,1,'xxxd');
	//echo "xxxd1 back from it\n";
	return $returnAry;
}
//=====================================================
	function breakOutTable($columnsAry,$columnsSortOrderAry,$base){
		$returnAry=array();
		$newColumnsAry=array();
		$newColumnsSortOrderAry=array(0=>'unused');
		//$base->DebugObj->printDebug($columnsSortOrderAry,1,'sortorder');//xxx
		$columnCnt=count($columnsSortOrderAry);
		//echo "columncnt: $columnCnt<br>";//xxx
		for ($ctr=1;$ctr<=$columnCnt;$ctr++){
			//echo "ctr: $ctr<br>";//xxx
			$columnName=$columnsSortOrderAry[$ctr];
			$columnAry=$columnsAry[$columnName];
			$columnMulti_file=$columnAry['columnmulti'];
			//echo "columnmulti: $columnMulti_file<br>";//xxx
			$columnMulti=$base->UtlObj->returnFormattedData($columnMulti_file,'boolean','internal');
			if ($columnMulti){
				$columnSelect=$columnAry['columnselect'];
				$query=$base->UtlObj->returnFormattedString($columnSelect,&$base);
				$result=$base->DbObj->queryTable($query,'read',&$base);
				$passAry=array();
				$workAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
				//$base->DebugObj->printDebug($workAry,1,'colary');//xxx
				$columnCnt=count($workAry);
				for ($ctr2=0;$ctr2<$columnCnt;$ctr2++){
					$columnAry_new=$columnAry;
					$columnName_new=$columnName . '_' . $ctr2;
					//echo "columnnamenew: $columnName_new<br>";//xxx
					$urlName=$columnAry_new['urlname'];
					$useAry=$workAry[$ctr2];
					//$base->DebugObj->printDebug($useAry,1,'usear');//xxx
					$urlName_new=$base->UtlObj->returnFormattedStringDataFed($urlName,$useAry,&$base);
					$joblink=$columnAry['joblink'];
					//echo "joblink: $joblink<br>";//xxx
					$joblink_new=$base->UtlObj->returnFormattedStringDataFed($joblink,$useAry,&$base);
					//echo "joblink_new: $joblink_new<br>";//xxx
					$columnAry_new['joblink']=$joblink_new;
					$columnAry_new['urlname']=$urlName_new;
					$columnAry_new['columnname']=$columnName_new;
					//echo "columnname: $columnName_new, joblink: $joblink_new, urlname: $urlName_new<br>";//xxx
					$newColumnsAry[$columnName_new]=$columnAry_new;
					$newColumnsSortOrderAry[]=$columnName_new;	
				}
			}
			else {
				$newColumnsAry[$columnName]=$columnAry;
				$newColumnsSortOrderAry[]=$columnName;
			}
		}	
		$returnAry['columnsary']=$newColumnsAry;
		unset($newColumnsSortOrderAry[0]);
		$returnAry['columnssortorderary']=$newColumnsSortOrderAry;
		//$base->DebugObj->printDebug($returnAry,1,'rtn');//xxx
		return $returnAry;		
	}
//=====================================================
	function insertParams($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertBody($param_1,'base')",0);
		$returnAry=array();
		$paramType=$paramFeed['param_1'];
		$htmlLine=$paramFeed['param_2'];
		$htmlProfile=$base->htmlProfileAry['default'];
		$attributes="";
		switch ($paramType){
			case 'body':
				$link=$htmlProfile['link'];
				if ($link != ""){$attributes.=" link=\"$link\"";}
				$background=$htmlProfile['background'];
				if ($background != ""){$attributes.=" background=\"$background\"";}
				$bgcolor=$htmlProfile['bgcolor'];
				if ($bgcolor != ""){$attributes.=" bgcolor=\"$bgcolor\"";}
				$text=$htmlProfile['text'];
				if ($text != ""){$attributes.=" text=\"$text\"";}
				$vlink=$htmlProfile['vlink'];
				if ($vlink != ""){$attributes.=" vlink=\"$vlink\"";}
				break;;
			default:
		}
		$posBef=strpos($htmlLine,'!!',0);
		$posAft=strpos($htmlLine,'!!',$posBef+1)+2;
		$htmlLineLen=strlen($htmlLine);
		$htmlLineBef=substr($htmlLine,0,$posBef);
		$htmlLineAft=substr($htmlLine,$posAft,$htmlLineLen-$posAft);
		$newHtmlLine=$htmlLineBef.$attributes.$htmlLineAft;
		$returnAry[]=$newHtmlLine;
		return $returnAry;
	}
//===================================================== 
	function insertDbTableInitJs($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertDbTableInitJs($paramFeed,'base')",0); //xx (h)
		//echo "xxx: in insertdbtableinitjs  <br>";
		//$base->DebugObj->printDebug($paramFeed,1,'xxx');
		//$base->DebugObj->printDebug($base->menuProfileAry['jsmenusary'],1,'xxx');
		$ajaxDelim='~';
		$param1=$paramFeed['param_1'];
		$param1Ary=explode('_',$param1);
		$fileName=$param1Ary[0];
		$fileType=$param1Ary[1];
		//echo "name: $fileName, type: $fileType<br>";//xxxd
		if ($fileType == NULL){$fileType='form';}
		$returnAry=array();
		$ajaxAry=array();
		$ajaxAry[]="\n".'!!end!!';
		$returnAry[]='<script type="text/javascript">'."\n";
		$returnAry[]='//================== begin custom script'."\n";
		$workAry=$base->ClientObj->getSystemData(&$base);
		$htmlLocal=$workAry['htmllocal'];
		$returnAry[]="var serverUrl='$htmlLocal';\n";
		//$returnAry[]="alert('doing init');\n";
		$returnAry[]='//------------------- util'."\n";
		$returnAry[]='var UtilObj = new UtilObject;'."\n";
//---------------------------------------- containers
		$returnAry[]='//--------------------containers'."\n";
		$containerJsAry=$base->ContainerObj->getContainerJs(&$base);
		//$returnAry[]="alert('xxx1');\n";
		$returnAry=array_merge($returnAry,$containerJsAry);
		//$returnAry[]="alert('xxx2');\n";
		$returnAry[]='var containerAry = Array();'."\n";
		foreach ($base->ContainerObj->containerProfileAry as $containerName=>$thisContainerAry){
			$saveInHtml_raw=$thisContainerAry['containersaveinhtml'];
			$saveInHtml=$base->UtlObj->returnFormattedData($saveInHtml_raw,'boolean','internal');
			if ($saveInHtml){
				$paramFeed=array();
				$containerHtmlAry=$base->ContainerObj->insertContainerHtml($containerName,&$base);
				$containerHtmlString=implode('',$containerHtmlAry);				
				$containerHtmlString=str_replace("\n",NULL,$containerHtmlString);
				$containerHtmlString=str_replace("'","\'",$containerHtmlString);
				$containerHtmlString=str_replace(chr(0x0d),"<br>",$containerHtmlString);
				$returnAry[]="containerAry['$containerName']='$containerHtmlString'\n";
			}
		}
//------------------------------------- yui object
	$returnAry[]='//----------------------- yui'."\n";
	$returnAry[]='var YuiObj = new YuiObject;'."\n";
//------------------------------------- js web table data fields 
		$returnAry[]='//-----------setup for table'."\n";
		$tableJsAry=$base->TableObj->getTableJs(&$base);
		//$returnAry[]="alert ('xxx1');\n";
		$returnAry=array_merge($returnAry,$tableJsAry);
		//$returnAry[]="alert ('xxx2');\n";
		$ajaxAry[]='!!table!!';
		//- old 
		$pageNo=$base->paramsAry['pageno'];
		if ($pageNo == ''){$pageNo=1;}
		$returnAry[]="var pageNo=$pageNo;\n";
		$returnAry[]='var columnCnt;'."\n";
		$returnAry[]='var pageSize;'."\n";
		$returnAry[]='var tableId;'."\n";
		$returnAry[]='var maxDataAry;'."\n";
		$returnAry[]='var dataAry = new Array();'."\n";
		$returnAry[]='var selectAry = new Array();'."\n";
		//- new
		$returnAry[]='//----- new table'."\n";
		$returnAry[]='var tableAry = new Array();'."\n";
		foreach ($base->tableProfileAry as $tableName=>$dmyAry){
			$returnAry[]="var aTableAry = new Array();\n";
			$returnAry[]="tableAry['$tableName'] = aTableAry;\n";
			$returnAry[]="var displayAry = new Array()\n";
			$returnAry[]="tableAry['$tableName']['displayary'] = displayAry;\n";	
			$returnAry[]="var selectAry = new Array()\n";
			$returnAry[]="tableAry['$tableName']['selectary'] = selectAry;\n";
			$returnAry[]="var dataAry = new Array()\n";
			$returnAry[]="tableAry['$tableName']['dataary'] = dataAry;\n";
			$returnAry[]="var etcAry = new Array()\n";
			$returnAry[]="tableAry['$tableName']['etc'] = etcAry;\n";
			//$returnAry[]="alert ('setup etc for $tableName');\n";//xxx
			$returnAry[]="tableAry['$tableName']['etc']['pageno']=1;\n";
			$returnAry[]="tableAry['$tableName']['etc']['columncnt']=0;\n";
			$returnAry[]="tableAry['$tableName']['etc']['pagesize']=1;\n";
			$returnAry[]="tableAry['$tableName']['etc']['tableid']='';\n";
			$returnAry[]="tableAry['$tableName']['etc']['maxdataary']=1;\n";
		}
		if ($fileType == 'table'){
			$returnAry[]='//----- old table'."\n";
			$returnAry[]='pageNo = 1;';
			$ajaxAry[]='pageNo|1';
			$jsTableAry=$base->tableProfileAry['jstableary'][$fileName];//xxx
			//$base->DebugObj->printDebug($jsTableAry,1,'xxx');
			$jsTableSelectAry=$base->tableProfileAry['jstableselectary'][$fileName];//xxx
			$columnCnt=count($jsTableAry[0]);
			$returnAry[]="columnCnt = $columnCnt;\n";
			$ajaxAry[]='columnCnt|'.$columnCnt;
			$pageSize=$base->tableProfileAry[$fileName]['pagesize'];
			if ($pageSize == null){
				echo "TagObj.insertdbtableinitjs: pagesize is null for table $tableName";
			}
			//echo "filename: $fileName, size: $pageSize<br>";//xxx
			$returnAry[]="pageSize = $pageSize;\n";
			$ajaxAry[]='pageSize|'.$pageSize;
			$tableId = $base->tableProfileAry[$fileName]['tableid'];
			//echo "filename: $fileName, tableid: $tableId<br>";//xxx
			//$base->DebugObj->printDebug($base->tableProfileAry,1,'xxx');
			$returnAry[]="tableId = '$tableId';\n";
			$ajaxAry[]='tableId|'.$tableId;
			$maxDataAry=count($jsTableAry);
			$returnAry[]="maxDataAry = $maxDataAry;\n";
			$ajaxAry[]='maxDataAry|'.$maxDataAry;
			$cnt=count($jsTableAry);
			if ($cnt>0){
			foreach ($jsTableAry as $rowNo=>$valueAry){
				$attributes='';$ajaxAttributes='';
				$theComma=null;$ajaxDelim=null;
				foreach ($valueAry as $colName=>$colValue){
					$colValue_js=$base->UtlObj->returnFormattedData($colValue,'varchar','js');
					$attributes .= $theComma.$colValue_js;
					$ajaxAttributes .= $ajaxDelim.$colValue;
					$theComma=',';$ajaxDelim="~";
				} // end foreach colname
				$tableLine="dataAry[dataAry.length] = new Array($attributes);\n";
				$returnAry[]=$tableLine;
				$ajaxLine='tableAry|'.$ajaxAttributes;
				$ajaxAry[]=$ajaxLine;
				//echo "tableline: $tableLine<br>";
				//echo "ajaxline: $ajaxLine<br>";
				$jsTableSelectString=$jsTableSelectAry[$rowNo];
				$jsTableSelectString_js=$base->UtlObj->returnFormattedData($jsTableSelectString,'varchar','js');
				$returnAry[]="selectAry[selectAry.length] = $jsTableSelectString_js;\n";
				$ajaxAry[]='selectAry|'.$jsTableSelectString;
			} // end foreach rowno
			}
		} 
		if ($fileType == 'form') {
//---------------------------------- js form validation fields
			$ajaxAry[]='!!form!!';
			$formAry_js=$base->FormObj->getFormJs();
			$returnAry=array_merge($returnAry,$formAry_js);
			$dbTableMetaName=$base->formProfileAry[$fileName]['tablename'];
			if ($dbTableMetaName != ''){
				$dbControlsAry=array('dbtablename'=>$dbTableMetaName);
				$base->DbObj->getDbTableInfo(&$dbControlsAry,&$base);
//- loop through table
				foreach ($dbControlsAry['dbtablemetaary'] as $colName=>$colAry){
					$dbTableMetaNotNull=$colAry['dbcolumnnotnull'];
					$dbTableMetaNotNull_jsformat=$base->UtlObj->returnFormattedData($dbTableMetaNotNull,'boolean','js');
					$validateRegEx=$colAry['validateregex'];
					$validateRegEx_jsformat=$base->UtlObj->returnFormattedData($validateRegEx,'varchar','js');
					$validateKeyMap=$colAry['validatekeymap'];
					$validateKeyMap_jsformat=$base->UtlObj->returnFormattedData($validateKeyMap,'varchar','js');
					$validateErrorMsg=$colAry['validateerrormsg'];
					$validateErrorMsg_jsformat=$base->UtlObj->returnFormattedData($validateErrorMsg,'varchar','js');
					$colName_jsformat=$base->UtlObj->returnFormattedData($colName,'varchar','js');
					$returnAry[]="validateArray[validateArray.length] = new Array(validateArray.length, $colName_jsformat, $dbTableMetaNotNull_jsformat, $validateRegEx_jsformat, $validateKeyMap_jsformat, $validateErrorMsg_jsformat);\n";
					$returnAry[]="FormObj.setupValidations('$fileName', $colName_jsformat, $dbTableMetaNotNull_jsformat, $validateRegEx_jsformat, $validateKeyMap_jsformat, $validateErrorMsg_jsformat);\n";
					$ajaxAry[]='validateArray|'.validateArray.length.$ajaxDelim. $colName_jsformat.$ajaxDelim. $dbTableMetaNotNull_jsformat.$ajaxDelim. $validateRegEx_jsformat.$ajaxDelim. $validateKeyMap_jsformat.$ajaxDelim. $validateErrorMsg_jsformat;
				} // end foreach dbcontrolsary
			} // end if tablename no null
		} // end else is form
//---------------------------------- menu list - any that are setup(just menus being used)
		$returnAry[]='//------------------------setup for menus'."\n";
		$ajaxAry[]='!!menus!!';
		$returnAry[]='var menuAry = Array();'."\n";
		$returnAry[]="var MenuObj = new MenuObject;\n";
		//$base->DebugObj->printDebug($base->menuProfileAry['jsmenusary'],1,'xxx');
		$jsMenusAry = $base->menuProfileAry['jsmenusary'];
		//$base->DebugObj->printDebug($base->menuProfileAry,1,'xxxd');exit();
		if ($jsMenusAry == NULL){
			$returnAry[]='//- no menus to worry about'."\n";
		}
		else {
			$wholeMenusAry=array();
			//$base->DebugObj->printDebug($jsMenusAry,1,'xxxd');
			foreach ($jsMenusAry as $menuName=>$menuAry){
				$returnAry[]="//- $menuName\n";
				$returnAry[]="MenuObj.initMenu('$menuName');\n";
				$ajaxAry[]='init|'.$menuName;
				$returnAry[]="menuAry['$menuName']=new Array();\n";
				$menuAry['pageno']=1;
				$menuType=$menuAry['menutype'];
				$menuChangeType=$menuAry['menuchangetype'];
//- batch update	xxxd work in process
				$batchAry=array();
				$batchAry[]='pageno';
				$batchAry[]='maxpagesize';
				$batchAry[]='menuclass';
				$batchAry[]='menupagingclass';
				$batchAry[]='menuselectedclass';
				$batchAry[]='menunonselectedclass';
				$batchAry[]='menutype';
				$batchAry[]='lastid';
				$batchAry[]='menutitleid';
				$batchAry[]='menuid';
				$batchAry[]='menupictureclass';//???????????
				$batchAry[]='menupictureid';//?????????????
				$batchAry[]='menuobjectclass';
				$batchAry[]='menuobjectid';
				$batchAry[]='menuparamclass';
				$batchAry[]='menuparamid';
				$batchAry[]='menuembedclass';
				$batchAry[]='menuembedid';
				$batchAry[]='menutextid';			
				$batchAry[]='videoheight';
				$batchAry[]='videowidth';
				$batchAry[]='menutitleid';
				$batchAry[]='menuimageid';
				$batchAry[]='albumname';
				if (!is_array($menuAry)){$menuAry=array();}
				foreach ($batchAry as $ctr=>$menuFieldName){
					$menuFieldValue=$menuAry[$menuFieldName];
					$returnAry[]="menuAry['$menuName']['$menuFieldName']='$menuFieldValue';\n";
					$returnAry[]="MenuObj.loadEtc('$menuFieldName','$menuFieldValue');\n";
					//xxxd - below was fixed 12/14/2009 it should have screwed everything up!!!
					$ajaxAry[]='set|'.$menuName.'|'.$menuFieldName.'|'.$menuFieldValue;	
					//echo "$menuFieldName, $menuFieldValue<br>";//xxxd	
				}
//---
				$returnAryLine=NULL;
				$titleAryLine=NULL;
				$firstTime=true;
				if ($menuType == 'rotate' || $menuType =='albumfixed' ){
					//-
					$returnAry[]="menuAry['$menuName']['menuelementno']=0;\n";
					$returnAry[]="MenuObj.loadEtc('menuelementno',0);\n";
					$ajaxAry[]='set|'.$menuName.'|'.'menuelementno|0';
					//-
					$returnAry[]="menuAry['$menuName']['elementsary']=new Array();\n";
					$ajaxAry[]='init|'.$menuName.'|elementsary';
					//-
					$returnAry[]="menuAry['$menuName']['titlesary']=new Array();\n";
					$ajaxAry[]='init|'.$menuName.'|titlesary';
					//-
					$returnAry[]="menuAry['$menuName']['textary']=new Array();\n";//xxxnew
					$ajaxAry[]='init|'.$menuName.'|textary';
					//-
					$returnAry[]="menuAry['$menuName']['etc']=new Array();\n";
					$ajaxAry[]='init|'.$menuName.'|etc';
//xxx last left off inserting ajax stuff
// - revolving menu
//$base->DebugObj->printDebug($menuAry,1,'men');//xxx
					foreach ($menuAry['elements'] as $menuElementNo=>$menuElementAry){
						$returnAryLine=NULL;
						$returnAryLine_ajax=NULL;
						$titleAryLine=NULL;
						$titleAryLine_ajax=NULL;
						$textAryLine=NULL;
						$textAryLine_ajax=NULL;
						$tdAryLine=NULL;
						$tdAryLine_ajax=NULL;
						$firstTime=true;
						$maxPageSize=0;
						foreach ($menuElementAry as $menuSubElementNo=>$menuSubElement_raw){
							$menuSubElement=str_replace("'","\'",$menuSubElement_raw);
							//echo "raw: $menuSubElement_raw, mod: $menuSubElement<br>";//xxx
							if ($firstTime){
								$commaInsert=NULL;
								$ajaxInsert=NULL;
								$firstTime=false;
								} // end if firsttime
							else {$commaInsert=', ';$ajaxInsert='~ ';}
							//- xxx did I change the above to pipe, is that good?
							$titleSubElement_raw=$menuAry['titles'][$menuElementNo][$menuSubElementNo];
							$titleSubElement=preg_replace("/!+/","!",$titleSubElement_raw);
							$titleAryLine.="$commaInsert'$titleSubElement'";
							$titleAryLine_ajax.="$ajaxInsert$titleSubElement";
							$textSubElement_raw=$menuAry['text'][$menuElementNo][$menuSubElementNo];//xxxnew
							$textSubElement=preg_replace("/!+/","!",$textSubElement_raw);
							$textAryLine.="$commaInsert'$textSubElement'";//xxxnew
							$textAryLine_ajax.="$ajaxInsert$textSubElement";
							$returnAryLine.="$commaInsert'$menuSubElement'";
							$returnAryLine_ajax.="$ajaxInsert$menuSubElement";
							$maxPageSize++;
						} // end foreach menuelementary
						//echo "returnaryline_ajax: $returnAryLine_ajax<br>";//xxx
						//-
						$returnAry[]="menuAry['$menuName']['elementsary'][$menuElementNo]=new Array ($returnAryLine);\n";
						$returnAryLine=str_replace("'","",$returnAryLine);
						$returnAryLine=str_replace(",","~",$returnAryLine);
						$returnAry[]="MenuObj.setArrays('elements',$menuElementNo,'','$returnAryLine');\n";
						$ajaxAry[]='initset|'.$menuName.'|elementsary|'.$menuElementNo.'|'.$returnAryLine_ajax;
						//-
						$returnAry[]="menuAry['$menuName']['titlesary'][$menuElementNo]=new Array ($titleAryLine);\n";
						$titleAryLine=str_replace("'","",$titleAryLine);
						$titleAryLine=str_replace(",","~",$titleAryLine);
						$returnAry[]="MenuObj.setArrays('titles',$menuElementNo,'','$titleAryLine');\n";
						$ajaxAry[]='initset|'.$menuName.'|titlesary|'.$menuElementNo.'|'.$titleAryLine_ajax;
						//-
						$returnAry[]="menuAry['$menuName']['textary'][$menuElementNo]=new Array ($textAryLine);\n";
						$textAryLine=str_replace("'","",$textAryLine);
						$textAryLine=str_replace(",","~",$textAryLine);
						$returnAry[]="MenuObj.setArrays('text',$menuElementNo,'','$textAryLine');\n";
						$ajaxAry[]='initset|'.$menuName.'|textary|'.$menuElementNo.'|'.$textAryLine_ajax;
						//-
						$returnAry[]="menuAry['$menuName']['etc'][$menuElementNo]=new Array();\n";
						$returnAry[]="MenuObj.makeHash('etchash',$menuElementNo);\n";
						$ajaxAry[]='init|'.$menuName."|etc|$menuElementNo";
						//-
						$returnAry[]="menuAry['$menuName']['etc'][$menuElementNo]['maxpagesize']=$maxPageSize;\n";
						$returnAry[]="MenuObj.setArrays('etchash',$menuElementNo,'maxpagesize',$maxPageSize);\n";
						$ajaxAry[]='set|'.$menuName.'|etchash|'.$menuElementNo.'|maxpagesize|'.$maxPageSize;
					} // end foreach menuary['elements']
					$eleCnt=count($menuAry['elementsother']);
					for ($eleLp=0;$eleLp<$eleCnt;$eleLp++){
						$elementsOtherAry=$menuAry['elementsother'][$eleLp];
						$eleString=null;
						$separ=null;
						if (count($elementsOtherAry)>0){
							foreach ($elementsOtherAry as $name=>$value){
								$eleString.=$separ.$name.'|'.$value;
								$separ='~';
							}	
							$returnAry[]="MenuObj.setArrays('elementsother',$eleLp,'','$eleString');\n";
						}
					}
				}
				else {
// - simple menu
//$base->DebugObj->printDebug($menuAry,1,'menu');//xxx
					foreach ($menuAry['elements'] as $menuElementNo=>$menuElement_raw){
						$menuElement=str_replace("'","\'",$menuElement_raw);
						//echo "tdeleemetn: $tdElement, menuelement: $menuElementNo<br>";//xxx
						if ($firstTime){$commaInsert=NULL;$firstTime=false;}
						else {$commaInsert=', ';}
						$returnAryLine.="$commaInsert'$menuElement'";
						$menuTitleElement=NULL;
						$titleAryLine.="$commaInsert'placeholder'";
					}
					$returnAry[]="menuAry['$menuName']['elementsary']=new Array ($returnAryLine);\n";
					$returnAryLine=str_replace("'",'',$returnAryLine);
					$returnAryLine=str_replace(",",'~',$returnAryLine);
					$returnAry[]="MenuObj.setArrays('elements','','','$returnAryLine');\n";
					$ajaxAry[]=$menuName.'|elementsary|'.$returnAryLine;
					//echo "returnaryline: $returnAryLine<br>";//xxx
					$returnAry[]="menuAry['$menuName']['titlesary']=new Array ($titleAryLine);\n";
					$titleAryLine_forObj=str_replace("'",'',$titleAryLine);
					$titleAryLine_forObj=str_replace(",",'~',$titleAryLine_forObj);
					$returnAry[]="MenuObj.setArrays('titles','','','$titleAryLine_forObj');\n";
					$ajaxAry[]=$menuName.'|titlesary|'.$titleAryLine;
//-
					$eleCnt=count($menuAry['elementsother']);
					//$base->DebugObj->printDebug($menuAry['elementsother'],1,'xxx');
					for ($eleLp=0;$eleLp<$eleCnt;$eleLp++){
						$elementsOtherAry=$menuAry['elementsother'][$eleLp];
						if ($elementsOtherAry != null){
							$eleString=null;
							$separ=null;
							foreach ($elementsOtherAry as $name=>$value){
								$eleString.=$separ.$name.'|'.$value;
								$separ='~';
							}	
							$returnAry[]="MenuObj.setArrays('elementsother',$eleLp,'','$eleString');\n";
						}
					}
				}
			}
		}
//--- whole menus
		//- whole menus
		$returnAry[]='//------------------------setup for whole menus'."\n";
		$ajaxAry[]='!!wholemenu!!';
		$returnAry[]='var wholeMenuAry = Array();'."\n";
//xxxf - error below
		foreach ($base->menuProfileAry as $menuName=>$thisMenuAry){
			$saveAsCookie_raw=$thisMenuAry['menusaveascookie'];
			$saveAsCookie=$base->UtlObj->returnFormattedData($saveAsCookie_raw,'boolean','internal');
			if ($saveAsCookie){
				$paramFeed=array();
				$paramFeed['param_1']=$menuName;
				$menuHtmlAry=$base->Plugin002Obj->insertMenu($paramFeed,&$base);
				$menuHtmlString=implode('',$menuHtmlAry);				
				$menuHtmlString=str_replace("\n",NULL,$menuHtmlString);
				$menuHtmlString=str_replace("'","\'",$menuHtmlString);
				$returnAry[]="wholeMenuAry['$menuName']='$menuHtmlString';\n";
				$ajaxAry[]=$menuName.'|'.$menuHtmlString;
			}
		}
//--- image inits
		$imagesAry=$base->imageProfileAry;
		$returnAry[]="//-------------------------- do inits of images\n";
		$returnAry[]='var imgAry = Array();'."\n";
		$returnAry[]='var imgSettingsAry = Array();'."\n";
		//$base->DebugObj->printDebug($base->cssProfileAry,1,'css');//xxx
		foreach ($imagesAry as $imageName=>$imageAry){
			$imageId=$imageAry['imageid'];
			if ($imageId != NULL){
				$leftPos=$base->cssProfileAry['id'][$imageId]['none']['left'];
				$topPos=$base->cssProfileAry['id'][$imageId]['none']['top'];
				$moveXRate=$imageAry['movex'];
				if ($moveXRate == NULL){$moveXRate=0;}
				$moveYRate=$imageAry['movey'];
				if ($moveYRate==NULL){$moveYRate=0;}
				$errorRate=$imageAry['errorrate'];
				if ($errorRate==NULL){$errorRate=0;}
				if ($leftPos == NULL){$leftPos="-50px";}
				if ($topPos == NULL){$topPos="200px";}
		        $returnAry[]="var imgObject = document.getElementById('$imageId');\n";
    		    $returnAry[]="if (imgObject != null){\n";
   			    $returnAry[]="  imgObject.style.left = '$leftPos';\n";
        		$returnAry[]="  imgObject.style.top = '$topPos';\n";
        		$returnAry[]="  imgAry['$imageId']=imgObject;\n";
        		$returnAry[]="}\n";
        		$returnAry[]="else {imgAry['$imageId']='';}\n";
				$returnAry[]="var settingsAry = Array();\n";
				$returnAry[]="settingsAry['movex']=$moveXRate;\n";
				$returnAry[]="settingsAry['movey']=$moveYRate;\n";
				$returnAry[]="settingsAry['errorrate']=$errorRate;\n";
				$returnAry[]="settingsAry['xdirection']=1;\n";
				$returnAry[]="settingsAry['ydirection']=1;\n";
				$returnAry[]="settingsAry['xctr']=0;\n";
				$returnAry[]="settingsAry['yctr']=0;\n";
				$leftPos_numeric=$base -> UtlObj -> returnFormattedData( $leftPos, 'numeric', 'internal');
				$topPos_numeric=$base -> UtlObj -> returnFormattedData( $topPos, 'numeric', 'internal');
				$returnAry[]="settingsAry['leftpos']=$leftPos_numeric;\n";
				$returnAry[]="settingsAry['toppos']=$topPos_numeric;\n";
				$returnAry[]="imgSettingsAry['$imageId']=settingsAry;\n";
 			}
		}
//- calendar inits
		$calendarAry_js=$base->CalendarObj->getCalendarJs(&$base);
		//$base->DebugObj->printDebug($calendarAry_js,1,'xxx');
		//exit();//xxx
		$returnAry=array_merge($returnAry,$calendarAry_js);
//- album inits
		$jsAry=$base->albumProfileAry['jsary'];
		$returnAry[]="//-------------------albums\n";
		$returnAry[]="var albumsAry = new Array();\n";
		//xxxd right here we need to have albums created in menuAry
		$noJs=count($jsAry);
		if ($noJs>0){		
		foreach ($jsAry as $albumName=>$albumAry){
			$returnAry[]="albumsAry['$albumName'] = new Array();\n";
			//- 
			//- pictureSrc
			$jsAlbumPicturesAry=$albumAry['jsalbumpicturesary'];
		if (!is_array($jsAlbumPicturesAry)){$jsAbumPicturesAry=array();}
			$cma=NULL;
			$pictureSrcStrg=NULL;
			foreach ($jsAlbumPicturesAry as $ctr=>$pictureSrc_raw){
				$pictureSrc=str_replace('/thumbnails',NULL,$pictureSrc_raw);
				$pictureSrcStrg.="$cma'$pictureSrc'";
				$cma=", ";	
			}
			//- captionStrg
			$jsAlbumCaptionsAry=$albumAry['jsalbumcaptionsary'];
		if (!is_array($jsAlbumCaptionsAry)){$jsAlbumCaptionsAry=array();}
			$cma=NULL;
			$captionStrg=NULL;
			foreach ($jsAlbumCaptionsAry as $ctr=>$caption){
				$captionStrg.="$cma'$caption'";
				$cma=", ";	
			}
			//- titleStrg
			$jsAlbumTitlesAry=$albumAry['jsalbumtitlesary'];
			$cma=NULL;
			$titleStrg=NULL;
			foreach ($jsAlbumTitlesAry as $ctr=>$title){
				$titleStrg.="$cma'$title'";
				$cma=", ";
			}
			//- mediaTypeStrg - try doesnt work
			$jsMediaTypeAry=$albumAry['jsmediatypeary'];
			if (!is_array($jsMediaTypeAry)){$jsMediaTypeAry=array();}
			$cma=NULL;
			$mediaTypeStrg=NULL;
			foreach ($jsMediaTypeAry as $ctr=>$mediaType){
				$mediaTypeStrg.="$cma'$mediaType'";
				$cma=", ";	
			}
			//- videoIdStrg
			$jsVideoIdAry=$albumAry['jsvideoidary'];
		if (!is_array($jsVideoIdAry)){$jsVideoIdAry=array();}
			$cma=NULL;
			$videoIdStrg=NULL;
			foreach ($jsVideoIdAry as $ctr=>$videoId){
				$videoIdStrg.="$cma'$videoId'";
				$cma=", ";				
			}		
			$returnAry[]="albumsAry['$albumName']['picturesrcary'] = new Array($pictureSrcStrg);\n";
			$ajaxAry[]=$pictureSrcStrg;
			$returnAry[]="albumsAry['$albumName']['picturecaptionsary'] = new Array($captionStrg);\n";
			$ajaxAry[]=$captionStrg;
			$returnAry[]="albumsAry['$albumName']['picturetitlesary'] = new Array($titleStrg);\n";
			$ajaxAry[]=$titleStrg;
			$returnAry[]="albumsAry['$albumName']['mediatypeary'] = new Array($mediaTypeStrg);\n";
			$ajaxAry[]=$mediaTypeStrg;
			$returnAry[]="albumsAry['$albumName']['videoidary'] = new Array($videoIdStrg);\n";
			$ajaxAry[]=$videoIdStrg;
		}
		}
//- end js
		$ajaxAry[]='!!html!!';//container footer stuff is left
		$returnAry[]='</script>'."\n";
		if (array_key_exists('container',$base->paramsAry)){
			$cnt=count($ajaxAry);
			for ($ctr=0;$ctr<$cnt;$ctr++){$ajaxAry[$ctr].="\n";}
			$returnAry=$ajaxAry;
		}
		$base->DebugObj->printDebug("-rtn:insertDbTableInitJs",0); //xx (f)
		return $returnAry;
	}
//end of functions
}
?>
