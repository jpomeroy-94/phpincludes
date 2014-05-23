<?php
class TableObject {
	var $statusMsg;
	var $callNo = 0;
	var $tableJsAry=array();
//====================================================
	function TableObject() {
		$this->incCalls();
		$this->statusMsg='table Object is fired up and ready for work!';
		$this->tableJsAry[]="var TableObj = new TableObject();\n";
	}
//====================================================
	function getTableJs(&$base){
		return $this->tableJsAry;
	}
//====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//====================================================
	function getTableForJsV2($paramFeed,$base){
		//echo "xxxf0";
		$ajaxAry=array();
		$tableName=$paramFeed['param_1'];
		$ajaxAry[]="!!table!!\n";
		$ajaxAry[]="tablename|$tableName\n";
		//-get list of column names in dbtable
		$dbTableName=$base->tableProfileAry[$tableName]['tablename'];
		$ajaxAry[]="etc|dbtablename|$dbTableName\n";
		$ajaxAry[]="etc|pageno|1\n";
		$jsTableAry=$base->tableProfileAry['jstableary'][$dbTableName];//xxxf
		$jsTableSelectAry=$base->tableProfileAry['jstableselectary'][$dbTableName];//xxxf
		$columnCnt=count($jsTableAry[0]);
		$ajaxAry[]="etc|columncnt|$columnCnt\n";
		$pageSize=$base->tableProfileAry[$tableName]['pagesize'];
		$ajaxAry[]="etc|pagesize|$pageSize\n";
		$tableId = $base->tableProfileAry[$tableName]['tableid'];
		//echo "tablename: $tableName, tableid: $tableId<br>";//xxx
		$ajaxAry[]="etc|tableid|$tableId\n";
		$maxDataAry=count($jsTableAry);
		$ajaxAry[]="etc|maxdataary|$maxDataAry\n";
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
				$ajaxLine="displayary|$ajaxAttributes\n";
				$ajaxAry[]=$ajaxLine;
				$jsTableSelectString=$jsTableSelectAry[$rowNo];
				$jsTableSelectString_js=$base->UtlObj->returnFormattedData($jsTableSelectString,'varchar','js');
				$ajaxAry[]="selectary|$jsTableSelectString\n";
			} // end foreach rowno
		}
//-get list of column names in dbtable
		$nameString="";
		$delim="";
		$theDataAry=$base->tableProfileAry[$tableName]['jsalldataary'];
		//$base->DebugObj->printDebug($base->tableProfileAry,1,'xxx');
		if (count($theDataAry[0])>0){
			foreach ($theDataAry[0] as $name=>$theBody){
				$nameString.="$delim$name";
				$delim="~";
			}
		}
		$ajaxAry[]="datadef|$nameString\n";
//-get list of column names in table
		$workAry=$base->columnProfileAry['sortorderary_'.$tableName];
		$nameString="";
		$delim="";
		$theCnt=count($workAry);
		for ($lp=1;$lp<=$theCnt;$lp++){
			$tableColName=$workAry[$lp];
			$nameString.="$delim$tableColName";
			$delim="~";
		}
		$ajaxAry[]="tabledef|$nameString\n";
//-get all data from table
		foreach ($theDataAry as $rowNo=>$valueAry){
			$valueString=implode('~',$valueAry);
			$ajaxAry[]="dataary|$valueString\n";
		}
//-get list of column names in table
		//-get html code for empty version of table
		$ajaxAry[]='!!html!!';//footer part of container still needs to be done
		return $ajaxAry;
	}	
//====================================================
	function getTableForAjax($paramFeed,$base){
		//- have to manually do the below
		//$ajaxAry=$base->AjaxObj->getContainerForAjax($paramFeed,&$base);
		$ajaxAry=array();
		$tableName=$paramFeed['param_1'];
		$ajaxAry[]="!!table!!\n";
		$ajaxAry[]="tablename|$tableName\n";
		//-get list of column names in dbtable
		$dbTableName=$base->tableProfileAry[$tableName]['dbtablename'];
		$ajaxAry[]="etc|dbtablename|$dbTableName\n";
		$pageNoId=$base->tableProfileAry[$tableName]['pagenoid'];
		$ajaxAry[]="etc|pagenoid|$pageNoId\n";
		$ajaxAry[]="etc|pageno|1\n";
		$sessionName=$base->paramsAry['sessionname'];
		$ajaxAry[]="etc|sessionname|$sessionName\n";
		$jsTableAry=$base->tableProfileAry['jstableary'][$tableName];//xxxd
		$jsTableSelectAry=$base->tableProfileAry['jstableselectary'][$tableName];//xxxd
		// not needed $jsAllDataAry=$base->tableProfileAry['jsalltableary'];
		$columnCnt=count($jsTableAry[0]);
		$ajaxAry[]="etc|columncnt|$columnCnt\n";
		$pageSize=$base->tableProfileAry[$tableName]['pagesize'];
		$ajaxAry[]="etc|pagesize|$pageSize\n";
		$tableId = $base->tableProfileAry[$tableName]['tableid'];
		//echo "tablename: $tableName, tableid: $tableId<br>";//xxx
		$ajaxAry[]="etc|tableid|$tableId\n";
		$tableIconMenu=$base->tableProfileAry[$tableName]['tableiconmenu'];
		$ajaxAry[]="etc|tableiconmenu|$tableIconMenu\n";
		//- filters
		$theFiltersAry=$base->tableProfileAry[xxx];//xxxd		
		$maxDataAry=count($jsTableAry);
		$ajaxAry[]="etc|maxdataary|$maxDataAry\n";
		$cnt=count($jsTableAry);
		if ($cnt>0){
			foreach ($jsTableAry as $rowNo=>$valueAry){
				$attributes='';$ajaxAttributes='';
				$theComma=null;$ajaxDelim=null;
				foreach ($valueAry as $colName=>$colValue){
					$colValue_js=$base->UtlObj->returnFormattedData($colValue,'varchar','js');
					//xxxf
					$tst=strpos($colValue_js,'coming off!!!',0);
					if ($tst>0){echo "tableobj.php: colvalue_js: $colValue_js";exit();}
					$attributes .= $theComma.$colValue_js;
					$ajaxAttributes .= $ajaxDelim.$colValue;
					$theComma=',';$ajaxDelim="~";
				} // end foreach colname
				$ajaxLine="displayary|$ajaxAttributes\n";
				$ajaxAry[]=$ajaxLine;
				$jsTableSelectString=$jsTableSelectAry[$rowNo];
				$jsTableSelectString_js=$base->UtlObj->returnFormattedData($jsTableSelectString,'varchar','js');
				$ajaxAry[]="selectary|$jsTableSelectString\n";
			} // end foreach rowno
		}
//-get list of column names in dbtable
		$theDataAry=$base->tableProfileAry['jsalldataary'][$tableName];
		$theDataDefsAry=$base->tableProfileAry['jsdatadefs'][$tableName];
		$keyName=$base->tableProfileAry['etc'][$tableName]['keyname'];
		$dbTableName=$base->tableProfileAry['etc'][$tableName]['dbtablename'];
		if ($dbTableName == null || $theDataDefsAry == null){
			echo "TableObj.getTableForAjax, tablename: $tableName<br>\n";
			echo "dbtablename: $dbTablname<br>\nkeyName: $keyName<br>\ntheDataDefsAry:<br>\n";
			$base->DebugObj->printDebug($theDataDefsAry,1,'thedatadefs ary from jsdatadefs, tablename');
			exit();
		}
		$nameString="";
		$foreignTableNameString="";
		$foreignKeyNameString="";
		$delim="";
//xxxf- below defs is not necessarily the same order as the data
// so use data names unless there is no data
		$useAry=$theDataAry[0];
		if (count($useAry)>0){
			foreach ($useAry as $name=>$theBody){
				$foreignKeyName=$theDataDefsAry[$name]['dbcolumnforeignkeyname'];
				$foreignTableName=$theDataDefsAry[$name]['dbcolumnforeigntable'];
				$nameString.="$delim$name";
				$foreignKeyNameString.="$delim$foreignKeyName";
				$foreignTableNameString.="$delim$foreignTableName";
				$delim="~";
			}				
		}
		else {
			foreach ($theDataDefsAry as $name=>$theBody){
				$nameString.="$delim$name";
				$delim="~";
			}
		}
		//echo "datadef namestrg: $nameString<br>";//xxx
		$ajaxAry[]="etc|dbtablename|$dbTableName\n";
		$ajaxAry[]="etc|keyname|$keyName\n";
		$ajaxAry[]="etc|datadef|$nameString\n";
		$ajaxAry[]="etc|foreigntablenames|$foreignTableNameString\n";
		$ajaxAry[]="etc|foreignkeynames|$foreignKeyNameString\n";
		//- selects
		$theFilters=$base->tableProfileAry['thefilters'][$tableName];
		foreach ($theFilters as $theName=>$theValue){
			$ajaxAry[]="etc|$theName|$theValue\n";
		}
//-get list of column names in table
		$workAry=$base->columnProfileAry['sortorderary_'.$tableName];
		$nameString="";
		$delim="";
		$theCnt=count($workAry);
		for ($lp=1;$lp<=$theCnt;$lp++){
			$tableColName=$workAry[$lp];
			$nameString.="$delim$tableColName";
			$delim="~";
		}
		$ajaxAry[]="etc|tabledef|$nameString\n";
//-get all data from dbtable
		$theCnt=count($theDataAry);
		if ($theCnt>0){
			$keyIndexStrg=null;$ajaxCtr=0;$delim='';
			foreach ($theDataAry as $rowNo=>$valueAry){
				$valueString=implode('~',$valueAry);
				$ajaxAry[]="dataary|$valueString\n";
				$keyValue=$valueAry[$keyName];
				$keyIndexStrg.="$delim$keyValue:$ajaxCtr";
				$delim='~';
				$ajaxCtr++;
			}
			$ajaxAry[]="keyindex|$keyIndexStrg\n";
		}
//- go through whole tableprofile and put into etc
	foreach ($base->tableProfileAry[$tableName] as $etcName=>$etcValue){
		$ajaxAry[]="etc|$etcName|$etcValue\n";
	}
//-get list of column names in table
		//-get html code for empty version of table
		//$ajaxAry[]='!!html!!';//footer part of container still needs to be done
		return $ajaxAry;
	}	
//==================================================== 
	function insertTableHtml($paramFeed,$base){
		$base->DebugObj->printDebug("TagObj:insertTable($paramFeed,'base')",0);
		$tableName=$paramFeed['param_1'];
		$tableType=$base->tableProfileAry[$tableName]['tabletype'];
		$tableRepeatNo=$base->tableProfileAry[$tableName]['tablerepeatno'];
		if ($tableType == NULL){$tableType='datadriven';}
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
		//xxxf blows up below
		$workAry=$base->TagObj->breakOutTable($columnsAry,$columnsSortOrder,&$base);
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
					if ($columnClass!=NULL){
						$columnClassInsert="class=\"$columnClass\"";
						$columnTdClassInsert="class=\"$columnClass".'_td'."\"";
					}
					else {
						$columnClassInsert=NULL;
						$columnTdClassInsert=NULL;
					}
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
						if ($rowClassInsert == NULL){
							$useClassInsert=$columnClassInsert;
							$useTdClassInsert=$columnTdClassInsert;
						}
						else {
							$useClassInsert=$rowClassInsert;
							$useTdClassInsert=$rowClassInsert;//just use the same here for now
						}
						//xxxdproject: check for page no
					//if ($dataCtr<=$pageSize-1){$returnAry[]="<td $useClassInsert>$colDisplayDataSt</td>";}
					if ($dataCtr>=(($pageSize-1)*($pageNo-1)) && $dataCtr<=(($pageSize-1)*$pageNo)){$returnAry[]="<td $useTdClassInsert>$colDisplayDataSt</td>";}
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
		//break;
		$tableDontPrint_raw=$base->tableProfileAry[$tableName]['tabledontprint'];
		$tableDontPrint=$base->UtlObj->returnFormattedData($tableDontPrint_raw,'boolean','internal',&$base);
		if ($tableDontPrint){$doReturnAry = array();}
		else {$doReturnAry=$returnAry;}
		return $doReturnAry;
	}
//====================================================
	function incCalls(){$this->callNo++;}
//end of functions
}
?>
