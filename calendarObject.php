<?php
class CalendarObject {
	var $statusMsg;
	var $callNo = 0;
	var $calendarProfileAry = array();
	var $calendarAry_js = array();
	var $calendarAry_ajax = array(0=>"!!calendar!!\n");
	var $dataAry = array();
//====================================
	function CalendarObject() {
		$this->incCalls();
		$this->statusMsg='calendar Object is fired up and ready for work!';
	}
//---------------
	function calendarStatus($base){
		echo $this->statusMsg;	
	}
//------------------------------------
	function initCalendar($base){
		$this->getCalendarProfileAry(&$base);
		$this->setupCalendarJs(&$base);
	}
//------------------------------------
	function retrieveCalendarAjax($paramFeed,$base){
		$calendarName=$paramFeed['param_1'];
		$workAry = $this->calendarAry_ajax;
		$workDataAry=$this->dataAry;
		$jsonXml=$base->XmlObj->array2Json($workDataAry,&$base);
		$workAry[]="loadeventsjson|$calendarName|dataary|$jsonXml\n";
		//$base->DebugObj->printDebug($workAry,1,'workaryxxxf');
		return $workAry;
	}
//====================================
	function setupCalendarJs($base){
		$this->calendarAry_js[]="//----------------------- setup for calendars;\n";
		$this->calendarAry_js[]="var calendarAry = new Array();\n";	
		$this->calendarAry_js[]="var CalendarObj = new CalendarObject();\n";
		foreach ($this->calendarProfileAry as $calendarName=>$calendarAry){
			$this->calendarAry_js[]="calendarAry['$calendarName'] = new Array();\n";
			$this->calendarAry_js[]="calendarAry['$calendarName']['etc'] = new Array();\n";
			$this->calendarAry_js[]="calendarAry['$calendarName']['desc'] = new Array();\n";
			$this->calendarAry_js[]="calendarAry['$calendarName']['data'] = new Array();\n";
		}
		//$base->DebugObj->printDebug($this->calendarAry_js,1,'xxx: 5');
		//$this->calendarAry_js[]="CalendarObj.doAlert('yo man');\n";
	}
//====================================
	function getCalendarJs($base){
		return $this->calendarAry_js;	
	}
//====================================
	function getCalendarProfileAry($base){
		//$base->DebugObj->printDebug($base->paramsAry,1,'xxx');
		$job=$base->paramsAry['job'];
		$jobProfileId=$base->jobProfileAry['jobprofileid'];
		$query="select * from calendarprofileview where jobprofileid=$jobProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array('delimit1'=>'calendarname');
		$this->calendarProfileAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
	}
//====================================
	function getCalendar($name,$base){
		if (array_key_exists($name,$this->calendarProfileAry)){
			$returnAry=$this->calendarProfileAry[$name];	
		}
		else {$returnAry=array();}
		return $returnAry;
	}
//====================================
	function insertCalendarHtml($name,$base){
		$calendarAry=$this->calendarProfileAry[$name];
		$dbTableName=$calendarAry['calendardbtablename'];
		//- today
		$todayAry=getdate();
//- get server date
    	$today_hour_server=$todayAry['hours'];
    	$today_day_server=$todayAry['mday'];
    	$today_month_server=$todayAry['mon'];
    	$today_year_server=$todayAry['year'];
//- convert to pacific standard time
    	$today_hour=$today_hour_server-8;
    	$today_month=$today_month_server;
    	$today_year=$today_year_server;
    	if ($today_hour<0){$day_adj=-1;}
    	else {$day_adj=0;}
    	$today_day=$today_day_server+$day_adj;
    	$year_adj=0;
    	if ($today_day<0){
    		if ($today_month<0){
        		$today_month=12;
        		$year_adj=-1;
      		}
      		$tst=$today_month;
      		if ($tst==1 || $tst==3 || $tst==5 || $tst==7 || $tst==8 || $tst==10 || $tst==12){$today_day=31;}
      		if ($tst==2){$today_day=28;}
      		if ($tst==4 || $tst==6 || $tst==9 || $tst==11){$today_day=30;}
    	}
    	$todayCheck=$today_month.'/'.$today_day.'/'.$today_year;
//- class
		$calendarClass=$calendarAry['calendarclass'];
		if ($calendarClass == NULL){$classInsert=NULL;}
		else {$classInsert="class=\"$calendarClass\"";}
//- selected class
		$calendarSelectedClass=$calendarAry['calendarselectedclass'];
		if ($calendarSelectedClass == NULL){$selectedClassInsert=NULL;}
		else {$selectedClassInsert="class=\"$calendarSelectedClass\"";}
//- start date
		$startDate=$base->paramsAry['startdate'];
		if ($startDate==NULL || $startDate=='NULL'){
			$startDate='startmonth';
		}
		$passAry['thedate']=$startDate;
		$startDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$startDate=$startDateAry['date_v1'];
		$monthChange=$base->paramsAry['monthchange'];
//echo "monthchange: $monthChange<br>";//xxxd
		if ($monthChange != NULL){
					$startDateWorkAry=explode('/',$startDate);	
					$startMonth=$startDateWorkAry[0];
					$startDay=$startDateWorkAry[1];
					$startYear=$startDateWorkAry[2];
					if ($monthChange=='previous'){
						$startMonth--;
						if ($startMonth<1){
							$startMonth=12;
							$startYear--;
						}
					}
					else {
						$startMonth++;
						if ($startMonth>12){
							$startMonth=1;
							$startYear++;
						}
					}
					$startDate=$startMonth.'/'.$startDay.'/'.$startYear;
//echo "startdate: $startDate<br>";//xxxd
					$passAry['thedate']=$startDate;
					$startDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
					$base->paramsAry['startdate']=$startDate;
//echo "write startdate: $startDate to paramsAry";//xxxd
		} // end if monthchange != null
//- calendarevents
		$calendarEvents_raw=$calendarAry['calendarevents'];
		$calendarEvents=$base->UtlObj->returnFormattedString($calendarEvents_raw,&$base);
//- date stuff
		$endDate=$base->paramsAry['enddate'];
		if ($endDate==NULL){$endDate='endmonth';}
		$passAry['thedate']=$endDate;
		$passAry['startdate']=$startDate;
		$endDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$displayMonthNo=$startDateAry['mon'];
		$displayYearNo=$startDateAry['year'];
		$displayMonth=$startDateAry['month'];
		$dayOfWeek=$startDateAry['wday'];
		$calendarCaption="$displayMonth $displayYearNo";
		//$base->DebugObj->printDebug($startDateAry,1,'xxxstartdate');
		//- get data
		$startDate=$startDateAry['date_v1'];
		$endDate=$endDateAry['date_v1'];
		$passAry = array(
			'startdate'=>$startDate,
			'enddate'=>$endDate,
			'dbtablename'=>$dbTableName,
			'calendarname'=>$name
		);
		$this->dataAry=$this->getCalendarData($passAry,&$base);
//$base->DebugObj->printDebug($this->dataAry,1,'dtaxxxd');
		$displayCalendarAry=array();
		$weekDay=$startDateAry['wday'];
		$colNo=$weekDay;
		$rowNo=1;
		$calendarAry_html=array();
		$calendarAry_html[]="<table $classInsert border=1>\n";
		$calendarAry_html[]="<caption>$calendarCaption</caption>\n";
		$calendarAry_html[]="<th>sun</th><th>mon</th><th>tue</th><th>wed</th><th>thu</th><th>fri</th><th>sat</th>\n<tr>";
		for ($colLp=0;$colLp<$colNo;$colLp++){
			$calendarAry_html[]="<td>&nbsp</td>";
		}
		$weekOfMonth=1;
		$mondayHasntPassed=true;
		$mondayCnt=0;
		for ($dayLp=1; $dayLp<=31; $dayLp++){
			$thisDay=$displayMonthNo.'/'.$dayLp.'/'.$displayYearNo;
			//echo "this: $thisDay, today: $todayCheck<br>";//xxxf
			$displayStrg=NULL;
			$displayStrg2=NULL;
			$aHoliday=false;
			$itIsToday=false;
			if ($dayOfWeek==1){$mondayCnt++;}
			if ($thisDay == $todayCheck){$aHoliday=true;$itIsToday=true;$displayStrg='today';}
			if ($dayLp==25 && $displayMonthNo==12){$aHoliday=true;$displayStrg2='christmas';}
			if ($dayLp==11 && $displayMonthNo==11){$aHoliday=true;$displayStrg2='veterans day';}
			if ($dayLp==1 && $displayMonthNo==1){$aHoliday=true;$displayStrg2='new year';}
			if ($dayLp==14 && $displayMonthNo==2){$aHoliday=true;$displayStrg2='valentines day';}
			if ($dayLp==4 && $displayMonthNo==7){$aHoliday=true;$displayStrg2='independence day';}			
			if ($dayLp==31 && $displayMonthNo==10){$aHoliday=true;$displayStrg2='halloween';}			
			if ($dayLp==20 && $displayMonthNo==3){$aHoliday=true;$displayStrg2='spring equinox';}			
			if ($dayLp==20 && $displayMonthNo==6){$aHoliday=true;$displayStrg2='summer solstice';}			
			if ($dayLp==22 && $displayMonthNo==9){$aHoliday=true;$displayStrg2='fall equinox';}			
			if ($dayLp==21 && $displayMonthNo==12){$aHoliday=true;$displayStrg2='winter solstice';}			
			if ($dayOfWeek==4 && $weekOfMonth==5 && $displayMonthNo==11){$aHoliday=true;$displayStrg2='thanksgiving';}
			if ($dayOfWeek==1 && $mondayHasntPassed && $displayMonthNo==9){
				$aHoliday=true;
				$displayStrg2='labor day';
				$mondayHasntPassed=false;
			}
			if ($dayOfWeek==1 && $mondayCnt==3 && $displayMonthNo==1){$aHoliday=true;$displayStrg2='mlk day';}
			if ($dayOfWeek==1 && $mondayCnt==3 && $displayMonthNo==2){$aHoliday=true;$displayStrg2='presidents day';}
			if ($dayOfWeek==1 && $mondayCnt==4 && $displayMonthNo==5){$aHoliday=true;$displayStrg2='memorial day';}
			$bannerStrg=trim($displayStrg.' '.$displayStrg2);
			$colNo++;
			if ($colNo>7){
				$colNo=1;$rowNo++;
				$calendarAry_html[]="</tr>\n<tr>";
			}
			if (!array_key_exists($rowNo,$displayCalendarAry)){
				$displayCalendarAry[$rowNo]=array();
			}
			if (!array_key_exists($colNo,$displayCalendarAry[$rowNo])){
				$displayCalendarAry[$rowNo][$colNo]=array();
			}
			$gotIt=false;
			//echo "$dayLp, $displayMonthNo, $displayYearNo: ";//xxxa
			if (array_key_exists($displayYearNo,$this->dataAry)){
			if (array_key_exists($displayMonthNo,$this->dataAry[$displayYearNo])){
			if (array_key_exists($dayLp,$this->dataAry[$displayYearNo][$displayMonthNo])){
				$gotIt=true;
				$dayDetailAry=$this->dataAry[$displayYearNo][$displayMonthNo][$dayLp];
				$displayCalendarAry[$rowNo][$colNo]=$dayDetailAry;
			}		
			}
			}
			$preToday=NULL;$postToday=NULL;
			if ($aHoliday){
				$preToday="<span class=\"holiday\">";
				$postToday="&nbsp;$bannerStrg</span>";
			}
			//xxxf22
			echo "xxxf22";
			//echo "itistoday: $itIsToday<br>";exit();//xxxf
			if ($itIsToday){$preToday="<span class=\"today\">";}
			if ($gotIt){
				$titleString=NULL;
				$messageAry=array();
				foreach($dayDetailAry as $ctr=>$theAry){
					$title=$theAry['title'];
					$message=$theAry['message'];
					//print_r($theAry);//xxxa
					if ($aHoliday && $titleString==NULL){$theBR=NULL;}
					else {$theBR="";}
					//above is a test xxx
					$titleString.="$theBR<div $selectedClassInsert $calendarEvents>$title</div>";
				} // end foreach
				$calendarAry_html[]="<td $selectedClassInsert>$preToday$dayLp$postToday$titleString</td>";
				//$base->DebugObj->printDebug($dayDetailAry,1,'xxxday');//xxx
			} // end gotit
			else {
				$calendarAry_html[]="<td $classInsert>$preToday$dayLp$postToday</td>";
			} // end else not gotit
			$dayOfWeek++;
			if ($dayOfWeek>6){$dayOfWeek=0;$weekOfMonth++;}
		} // end for loop
		$calendarAry_html[]="</td></tr>\n</table>";
		//$base->DebugObj->printDebug($calendarAry_html,1,'xxxf');//xxxf
		//exit();//xxxf
		return $calendarAry_html;
	}
//====================================
	function getCalendarData($passAry,$base){
		$startDate=$passAry['startdate'];
		$endDate=$passAry['enddate'];
		$dbTableName=$passAry['dbtablename'];
		$calendarName=$passAry['calendarname'];
		$query="select * from $dbTableName where startdate >= '$startDate' and startdate <= '$endDate'";
//echo "query: $query<br.";//xxxd
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$tempDataAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
//$base->DebugObj->printDebug($tempDataAry,1,'dta');//xxxd
		$calendarAry=array();
		$jsString="var $calendarName = new array();\n";
		$jsValueString=NULL;
		$jsValueString2=NULL;
		$jsValueString3=NULL;
		foreach ($tempDataAry as $ctr=>$dayAry){
			$startDate=$dayAry['startdate'];
			$endDate=$dayAry['enddate'];
			if ($endDate == NULL){$endDate=$startDate;}
			$passAry['thedate']=$startDate;
			$startDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
			//$startDateAry=$base->UtlObj->getDateInfo($startDate,&$base);
			$passAry['thedate']=$endDate;
			$endDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
			$startDateYearNo=$startDateAry['year'];
			$startDateMonthNo=$startDateAry['mon'];
			$startDateDayNo=$startDateAry['mday'];
			$endDateYearNo=$endDateAry['year'];
			$endDateMonthNo=$endDateAry['mon'];
			$endDateDayNo=$endDateAry['mday'];
			if ($endDateYearNo>$startDateYearNo){
				$endDateYearNo=$startDateYearNo+1;
				$endDateMonthNo=1;	
			}
			for ($yearCtr=$startDateYearNo; $yearCtr<=$endDateYearNo; $yearCtr++){
				for ($monthCtr=$startDateMonthNo; $monthCtr<=$endDateMonthNo; $monthCtr++){
					for ($dayCtr=$startDateDayNo; $dayCtr<=$endDateDayNo; $dayCtr++){
						if (!array_key_exists($yearCtr,$calendarAry)){
							$calendarAry[$yearCtr]=array();
							$jsString.="$calendarName[$yearCtr]=new array();\n";
						}
						if (!array_key_exists($monthCtr,$calendarAry[$yearCtr])){
							$calendarAry[$yearCtr][$monthCtr]=array();
							$jsString.="$calendarName[$yearCtr][$monthCtr]=new array();\n";
						}
						if (!array_key_exists($dayCtr,$calendarAry[$yearCtr][$monthCtr])){
							$calendarAry[$yearCtr][$monthCtr][$dayCtr]=array();
							$jsString.="$calendarName[$yearCtr][$monthCtr][$dayCtr]=new array();\n";
						}
						$calendarAry[$yearCtr][$monthCtr][$dayCtr][]=$dayAry;
						$jsValueString.="'$dayAry[xxx]'";
					}
				}
			}
			//$base->DebugObj->printDebug($calendarAry,1,'xxxstartdateary');
		}
		return $calendarAry;
	}
	//====================================
	function insertCalendarHtmlV2($calendarName,$base){
		$calendarAry=$this->calendarProfileAry[$calendarName];
		$dbTableName=$calendarAry['calendardbtablename'];
		$formName=$calendarAry['calendarformname'];
		$calendarEntryDateName=$calendarAry['calendarentrydatename'];
		$calendarEntryClassName=$calendarAry['calendarentryclassname'];
		$calendarEntryTitleName=$calendarAry['calendarentrytitlename'];
		$calendarEntryKeyName=$calendarAry['calendarentrykeyname'];
		$calendarEventTypeName=$calendarAry['calendareventtypename'];
		$calendarEntryDbTableName=$calendarAry['calendarentrydbtablename'];
		$calendarEntryStartTimeName=$calendarAry['calendarentrystarttime'];
		$calendarMenuId=$calendarAry['calendarmenuid'];
		$calendarMenuFormName=$calendarAry['calendarmenuformname'];
		$calendarMenuContainerId=$calendarAry['calendarmenucontainerid'];
		//- today determines what month is first displayed in calendar function
		$todayDate=$base->paramsAry['today'];
		if ($todayDate != NULL){
			$passAry=array('thedate'=>$todayDate);
			$todayAry=$base->UtlObj->getDateInfo($passAry,&$base);
		}
		else {
			$todayAry=getdate();
		}
//- get server date
    	$today_hour_server=$todayAry['hours'];
    	$today_day_server=$todayAry['mday'];
    	$today_month_server=$todayAry['mon'];
    	$today_year_server=$todayAry['year'];
//- convert to pacific standard time
    	$today_hour=$today_hour_server-8;
    	//echo "todayhourserver: $today_hour_server, todayhour: $today_hour<br>";//xxx
    	$today_month=$today_month_server;
    	$today_year=$today_year_server;
    	if ($today_hour<0){$day_adj=-1;}
    	else {$day_adj=0;}
    	$today_day=$today_day_server+$day_adj;
    	$year_adj=0;
    	if ($today_day<0){
    		if ($today_month<0){
        		$today_month=12;
        		$year_adj=-1;
      		}
      		$tstScan='_'.$today_month.'_';
      		//- bug with leap year below - fix later
       		if (strpos('_1_3_5_7_8_10_12_',$tstScan,0)>0){$today_day=31;}
      		else if ($today_month==2){$today_day=28;}
      		else {$today_day=30;} 
    	}
    	$thisDate=$today_month.'/'.$today_day.'/'.$today_year;
    	$this->calendarAry_ajax[]="calendarname|$calendarName\n";
    	$this->calendarAry_js[]="calendarAry['$calendarName']['etc']['curmonthno']=$today_month;\n";
    	$this->calendarAry_ajax[]="loadetc|curmonthno|$today_month\n";
    	$this->calendarAry_js[]="calendarAry['$calendarName']['etc']['curyearno']=$today_year;\n";
    	$this->calendarAry_ajax[]="loadetc|curyearno|$today_year\n";
    	$this->calendarAry_js[]="calendarAry['$calendarName']['etc']['formname']=$formName;\n";
    	/*
    	$this->calendarAry_ajax[]="loadetc|formname|$formName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentrydatename|$calendarEntryDateName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentryclassname|$calendarEntryClassName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentrytitlename|$calendarEntryTitleName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentrykeyname|$calendarEntryKeyName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentrydbtablename|$calendarEntryDbTableName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarentrystarttimename|$calendarEntryStartTimeName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarmenuid|$calendarMenuId\n";
    	$this->calendarAry_ajax[]="loadetc|calendarmenuformname|$calendarMenuFormName\n";
    	$this->calendarAry_ajax[]="loadetc|calendarmenucontainerid|$calendarMenuContainerId\n";
    	*/
    	foreach ($calendarAry as $theName=>$theValue_raw){
    		//xxxf - the below should be flagged not literal check
    		if ($theName!='calendarevents'){
	    		$theValue=$base->UtlObj->returnFormattedString($theValue_raw,&$base);
    		}
    		else {
    			$theValue=$theValue_raw;
    		}
    		$this->calendarAry_ajax[]="loadetc|$theName|$theValue\n";
    	}
 //- this date
		$passAry['thedate']=$thisDate;
		$thisDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$thisDate=$thisDateAry['date_v1'];
    	//echo "thisdate: $thisDate<br>";//xxx
//- start date for getting data
		$startDate=$base->paramsAry['startdate'];
		if ($startDate==NULL || $startDate=='NULL'){
			$startDate='startyear';
		}
		$passAry['thedate']=$startDate;
		$startDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$startDate=$startDateAry['date_v1'];
		$startYear=$startDateAry['year'];
		//echo "startdate: $startDate<br>";//xxx
//- end date for getting data
		$endDate=$base->paramsAry['enddate'];
		if ($endDate==NULL){$endDate='endyear';}
		$passAry['thedate']=$endDate;
		$endDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$endDate=$endDateAry['date_v1'];
		$endYear=$endDateAry['year'];
		//echo "enddate: $endDate<br>";//xxx
//- month to display
//- first day
		$displayMonthNo=$thisDateAry['mon'];
		$displayYearNo=$thisDateAry['year'];
		$displayMonth=$thisDateAry['month'];
		$firstDayDate=$displayMonthNo.'/1/'.$displayYearNo;
		$passAry['thedate']=$firstDayDate;
		$firstDayAry=$base->UtlObj->getDateInfo($passAry,&$base);
		$firstDayWeekDayNo=$firstDayAry['wday'];
//- last day
   		$tstScan='_'.$displayMonthNo.'_';
   		//- bug with leap year below - fix later
   		if (strpos('x_1_3_5_7_8_10_12_',$tstScan,0)>0){$lastDayNo=31;}
   		else if ($displayMonthNo==2){$lastDayNo=28;}
   		else {$lastDayNo=30;}
   		$lastDate=$displayMonthNo.'/'.$lastDayNo.'/'.$displayYearNo;
   		$passAry=array('thedate'=>$lastDate);
   		$lastDayAry=$base->UtlObj->getDateInfo($passAry,&$base); 
   		$lastDayWeekDayNo=$lastDayAry['wday'];
		//echo "dmno: $displayMonthNo, dyno: $displayYearNo, dm: $displayMonth, fddow: $firstDayWeekDayNo<br>";//xxx
//- class
		$calendarClass=$calendarAry['calendarclass'];
		if ($calendarClass == NULL){$classInsert=NULL;}
		else {$classInsert="class=\"$calendarClass\"";}
//- id
		$calendarId=$calendarAry['calendarid'];
		if ($calendarId == NULL){$idInsert=NULL;}
		else {$idInsert="id=\"$calendarId\"";}
		$this->calendarAry_js[]="calendarAry['$calendarName']['etc']['id']='$calendarId';\n";
		$this->calendarAry_ajax[]="loadetc|id|$calendarId\n";
//- today class
		$todayClass=$calendarAry['todayclass'];
		if ($todayClass != null){$todayClassInsert="class=\"$todayClass\"";}
		else {$todayClassInsert=null;}
//- week class
		$weekClass=$calendarAry['weekclass'];
		if ($weekClass == NULL){$weekClassInsert=NULL;}
		else {$weekClassInsert="class=\"$weekClass\"";}
//- weekend class
		$weekendClass=$calendarAry['weekendclass'];
		if ($weekendClass == NULL){$weekendClassInsert=NULL;}
		else {$weekendClassInsert="class=\"$weekendClass\"";}
//- selected class
		$calendarSelectedClass=$calendarAry['calendarselectedclass'];
		if ($calendarSelectedClass == NULL){$selectedClassInsert=NULL;}
		else {$selectedClassInsert="class=\"$calendarSelectedClass\"";}
		//echo "calclass: $classInsert, weclass: $weekendClassInsert, wclass: $weekClassInsert<br>";//xxx
//- calendarevents
		$calendarEvents_raw=$calendarAry['calendarevents'];
		//$calendarEvents=$base->UtlObj->returnFormattedString($calendarEvents_raw,&$base);
		$calendarCaption="$displayMonth $displayYearNo";
		//$base->DebugObj->printDebug($startDateAry,1,'xxxstartdate');
//- get data	
		$startDate=$startDateAry['date_v1'];
		$endDate=$endDateAry['date_v1'];
		//xxxf - override to see what happens
		$startDate='01/01/2000';//xxxf
		$endDate='12/31/2020';//xxxf
		$passAry = array(
			'startdate'=>$startDate,
			'startyear'=>$startYear,
			'enddate'=>$endDate,
			'endyear'=>$endYear,
			'dbtablename'=>$dbTableName,
			'calendarname'=>$calendarName
		);
		//$base->DebugObj->printDebug($passAry,1,'xxxf');
		$this->getCalendarDataV2($passAry,&$base);
		//$base->DebugObj->printDebug($this->dataAry[2007],1,'xxxddataary');exit();//xxxf
		$displayCalendarAry=array();
		$colNo=$firstDayWeekDayNo;
		$rowNo=1;
		$calendarAry_html=array();
		$calendarAry_html[]="<table $classInsert $idInsert>\n";
		$calendarAry_html[]="<caption>$calendarCaption</caption>\n";
		$calendarAry_html[]="<th>sun</th><th>mon</th><th>tue</th><th>wed</th><th>thu</th><th>fri</th><th>sat</th>\n<tr>";
//- fill in first blanks of month
		for ($colLp=0;$colLp<$colNo;$colLp++){
			$calendarAry_html[]="<td>&nbsp</td>";
		}
		$weekOfMonth=1;
		$dayOfWeek=$firstDayWeekDayNo;
		$mondayHasntPassed=true;
		$mondayCnt=0;
		$rowCtr=0;
		//- the below is in the get data section
		//$this->calendarAry_js[]="calendarAry['$calendarName']=new Array();\n";
		for ($dayLp=1; $dayLp<=$lastDayNo; $dayLp++){
			$theLen=strlen($dayLp);
			if ($theLen<2){$useDayLp='0'.$dayLp;}
			else {$useDayLp=$dayLp;}
			$thisDay=$displayMonthNo.'/'.$useDayLp.'/'.$displayYearNo;
			$dayLpDisplay="<span>$dayLp</span>";
			$todayCheckAry=$base->UtlObj->getDateInfo('today',&$base);
			$todayCheck=$todayCheckAry['date_v1'];
			$displayStrg=NULL;
			$displayStrg2=NULL;
			$aHoliday=false;
			$itIsToday=false;
			if ($thisDay == $todayCheck){$aHoliday=true;$itIsToday=true;$displayStrg='today';}
			//echo "thisday: $thisDay, todaycheck: $todayCheck, itistoday: $itIsToday<br>";exit();
			$bannerStrg=trim($displayStrg.' '.$displayStrg2);
			$colNo++;
			if ($colNo>7){
				$colNo=1;$rowNo++;
				$calendarAry_html[]="</tr>\n<tr>";
				$rowCtr++;
			}
			if (!array_key_exists($rowNo,$displayCalendarAry)){
				$displayCalendarAry[$rowNo]=array();
			}
			if (!array_key_exists($colNo,$displayCalendarAry[$rowNo])){
				$displayCalendarAry[$rowNo][$colNo]=array();
			}
//- dayofweek and td classes
			if ($dayOfWeek==0 || $dayOfWeek==6){$tdClassInsert=$weekendClassInsert;}
			else {$tdClassInsert=$weekClassInsert;}
			if ($tdClassInsert==NULL){$tdClassInsert=$classInsert;}
			$gotIt=false;
			if (array_key_exists($displayYearNo,$this->dataAry)){
				if (array_key_exists($displayMonthNo,$this->dataAry[$displayYearNo])){
					if (array_key_exists($dayLp,$this->dataAry[$displayYearNo][$displayMonthNo])){
						$gotIt=true;
						$dayDetailAry=$this->dataAry[$displayYearNo][$displayMonthNo][$dayLp];
						$displayCalendarAry[$rowNo][$colNo]=$dayDetailAry;
					}		
				}
			}
			$preToday=NULL;$postToday=NULL;$todayMsg=null;
			$doInsert=null;
			if ($itIsToday){
				$preToday="<div $todayClassInsert>";$postToday="</div>";$todayMsg='today';
			}
			if ($gotIt){
				$titleString=NULL;
				$titleSubString=NULL;
				$messageAry=array();
				//$base->DebugObj->printDebug($dayDetailAry,1,'xxx');
				$displayIt=false;
				foreach($dayDetailAry as $ctr=>$theAry){
					$title=$theAry[$calendarEntryTitleName];
					$theEventClass=$theAry[$calendarEntryClassName];
					//xxxf - we are not getting the class here!!!
					//$base->DebugObj->printDebug($theAry,1,'xxxf');
					//echo "theeventclass: $theEventClass, theeventclassname: $calendarEntryClassName<br>";//xxxf
					//xxxf
					$theEventDateType=$theAry[$calendarEventTypeName];
					if ($theEventClass==NULL){$theEventClassInsert=NULL;}
					else {$theEventClassInsert="class=\"$theEventClass\"";}
					$passAry=array('eventno'=>$ctr,'theday'=>$dayLp,'themonth'=>$displayMonthNo,'theyear'=>$displayYearNo);
					$calendarEvents=$base->UtlObj->returnFormattedStringDataFed($calendarEvents_raw,$passAry,$base);
					//echo "$calendarEvents<br>";
					if ($theEventDateType=='holiday'){
						$titleString.="<span $theEventClassInsert $calendarEvents>$title</span>";
						$displayIt=true;
					}
					else {
						$titleSubString.="<div $theEventClassInsert $calendarEvents>$title</div>";
					}
				} // end foreach
				//xxxf: new
				$titleString.=$titleSubString;
				$tableCellDisplay="<td $tdClassInsert>$doInsert$preToday$dayLpDisplay$todayMsg$postToday$titleString</td>\n";
				$calendarAry_html[]=$tableCellDisplay;
				//$base->DebugObj->printDebug($dayDetailAry,1,'xxxday');//xxx
			} // end gotit
			else {
				//xxxf: new
				$calendarAry_html[]="<td $tdEventCall $tdClassInsert>$doInsert$preToday$dayLpDisplay$todayMsg$postToday</td>\n";
			} // end else not gotit
			$dayOfWeek++;
			if ($dayOfWeek>6){$dayOfWeek=0;$weekOfMonth++;}
		} // end for loop
		$colStart=$lastDayWeekDayNo+1;
		for ($colLp=$colStart;$colLp<7;$colLp++){
			$calendarAry_html[]="<td class=\"hidden\">&nbsp;</td>";
		}
		if ($rowCtr<5){
			$rowCtr++;
			for ($rowLp=$rowCtr;$rowLp<6;$rowLp++){
				$calendarAry_html[]="</tr><tr>";
				for ($colLp=0;$colLp<7;$colLp++){	
					$calendarAry_html[]="<td class=\"hidden\">&nbsp;</td>";
				}
			}
		}
		$calendarAry_html[]="</tr>\n</table>";
		$cnt=count($this->calendarAry_ajax);
		//echo "end of getcalendarhtmlv2 for $calendarName cnt in calendarary_ajax: $cnt<br>";
		return $calendarAry_html;
	}
//====================================
	function getCalendarDataV2($passAry,$base){
		$startDate=$passAry['startdate'];
		$endDate=$passAry['enddate'];
		$startYear=$passAry['startyear'];
		$endYear=$passAry['endyear'];
		$dbTableName=$passAry['dbtablename'];
		$calendarName=$passAry['calendarname'];
		$calendarEntryTitleName=$this->calendarProfileAry[$calendarName]['calendarentrytitlename'];
		$holidayClass=$this->calendarProfileAry[$calendarName]['holidayclass'];
		//xxxf - the below has not been defined yet
		$calendarEntryMessageName=$this->calendarProfileAry[$calendarName]['calendarentrymessagename'];
		$calendarEventTypeName=$this->calendarProfileAry[$calendarName]['calendareventtypename'];
		$calendarEntryClassName=$this->calendarProfileAry[$calendarName]['calendarentryclassname'];
//- put in all rotating dates
		$companyProfileId=$base->jobProfileAry['companyprofileid'];
		$query="select * from calendareventprofileview where calendarname='$calendarName' and companyprofileid=$companyProfileId";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$eventAry=$base->UtlObj->tableToHashAryV3($result,$passAry);
		//echo "query: $query<br>";//xxxf
		//$base->DebugObj->printDebug($eventAry,1,'eventary xxxf');exit();
		$this->dataAry=array();
		//$base->DebugObj->printDebug($eventAry,1,'xxxd');exit();
		foreach ($eventAry as $eventName=>$thisEventAry){
			$eventName=$thisEventAry['calendareventname'];
			$eventLabel=$thisEventAry['calendareventlabel'];
			$eventDesc=$thisEventAry['calendareventdesc'];
			$monthNo=$thisEventAry['calendareventmonthno'];
			$monthDayNo=$thisEventAry['calendareventmonthdayno'];
			$weekNo=$thisEventAry['calendareventweekno'];	
			$weekDayNo=$thisEventAry['calendareventweekdayno'];
			$noDays=$thisEventAry['calendareventnodays'];
			$eventType=$thisEventAry['calendareventtype'];//xxxf - dont think that this exists
			$eventClass=$thisEventAry['calendareventclass'];
			$dateType=$thisEventAry['calendareventdatetype'];
			$eventFrequency=$thisEventAry['calendareventfrequency'];
			//echo "name: $eventName, type: $eventType, monthno: $monthNo, dayno: $monthDayNo<br>";//xxxf
			$dayEventAry=array($calendarEntryTitleName=>$eventLabel,$calendarEntryMessageName=>$eventDesc,$calendarEntryClassName=>$eventClass,$calendarEventTypeName=>$dateType);
//- update js - there are problems in the js where it doesnt match the ajax
			$this->calendarAry_js[]="calendarAry['$calendarName']['desc']['$eventLabel']='$eventDesc';\n";
			for ($yearCtr=$startYear;$yearCtr<=$endYear;$yearCtr++){
				if (!array_key_exists($yearCtr,$this->dataAry)){
					$this->dataAry[$yearCtr]=array();
					$this->calendarAry_js[]="calendarAry['$calendarName']['data'][$yearCtr]=new Array();\n";
				}
				if ($eventFrequency == 'yearly_month_week_day'){
				//xxxf22
					$monthFirstDayDate=$monthNo.'/01/'.$yearCtr;
					$passAry=array('thedate'=>$monthFirstDayDate);
					$firstDayAry=$base->UtlObj->getDateInfo($passAry,&$base);
					$firstDayWeekDay=$firstDayAry['wday'];
					$dayAdj=$weekDayNo-$firstDayWeekDay+1;
					$monthDayNo=($weekDayNo-1)*7+$dayAdj;
				}
				if (!array_key_exists($monthNo,$this->dataAry[$yearCtr])){
					$this->dataAry[$yearCtr][$monthNo]=array();
					$this->calendarAry_js[]="calendarAry['$calendarName']['data'][$yearCtr][$monthNo]=new Array();\n";
				}
				if (!array_key_exists($monthDayNo,$this->dataAry[$yearCtr][$monthNo])){
					$this->dataAry[$yearCtr][$monthNo][$monthDayNo]= array();	
					$this->calendarAry_js[]="calendarAry['$calendarName']['data'][$yearCtr][$monthNo][$monthDayNo]=new Array();\n";
				}	
				$this->dataAry[$yearCtr][$monthNo][$monthDayNo][]=$dayEventAry;	
//- js stuff 
				$this->calendarAry_js[]="var dayEventsAry=calendarAry['$calendarName']['data'][$yearCtr][$monthNo][$monthDayNo];\n";
				$this->calendarAry_js[]="var dayEventAry = new Array();\n";
				$this->calendarAry_js[]="dayEventAry[$calenderEntryTitleName]='$eventLabel';\n";
				$dmy="title:$eventLabel";
				$this->calendarAry_js[]="dayEventAry['desc']='$eventDesc';\n";//xxxf22 - need to make generic
				$dmy.="~desc:$eventDesc";
				$this->calendarAry_js[]="dayEventAry[$calendarEntryClassName]='$eventClass';\n";
				$dmy.="~class:$eventClass";
				$this->calendarAry_js[]="dayEventAry[$calendarEventTypeName]='$dateType';\n";//xxxf22 - need to make generic
				$dmy.="~datetype:$dateType";
				//xxxf - dont need this $this->calendarAry_ajax[]="event|$yearCtr|$monthNo|$monthDayNo|$dmy\n";
				$this->calendarAry_js[]="dayEventsAry[dayEventsAry.length]=dayEventAry;\n";
				$this->calendarAry_js[]="calendarAry['$calendarName']['data'][$yearCtr][$monthNo][$monthDayNo]=dayEventsAry;\n";
			}
		}
//- put in all user table date fields
		$query="select * from $dbTableName where startdate >= '$startDate' and startdate <= '$endDate'";
		$result=$base->DbObj->queryTable($query,'read',&$base);
		$passAry=array();
		$data2Ary=$base->UtlObj->tableToHashAryV3($result,$passAry);
		$jsString="var $calendarName = new array();\n";
		$jsValueString=NULL;
		$jsValueString2=NULL;
		$jsValueString3=NULL;
		foreach ($data2Ary as $ctr=>$dayAry){
			$startDate=$dayAry['startdate'];
			$endDate=$dayAry['enddate'];
			if ($endDate == NULL){$endDate=$startDate;}
			$passAry['thedate']=$startDate;
			$startDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
			//$startDateAry=$base->UtlObj->getDateInfo($startDate,&$base);
			$passAry['thedate']=$endDate;
			$endDateAry=$base->UtlObj->getDateInfo($passAry,&$base);
			$startDateYearNo=$startDateAry['year'];
			$startDateMonthNo=$startDateAry['mon'];
			$startDateDayNo=$startDateAry['mday'];
			$endDateYearNo=$endDateAry['year'];
			$endDateMonthNo=$endDateAry['mon'];
			$endDateDayNo=$endDateAry['mday'];
			if ($endDateYearNo>$startDateYearNo){
				$endDateYearNo=$startDateYearNo+1;
				$endDateMonthNo=1;	
			}
			for ($yearCtr=$startDateYearNo; $yearCtr<=$endDateYearNo; $yearCtr++){
				for ($monthCtr=$startDateMonthNo; $monthCtr<=$endDateMonthNo; $monthCtr++){
					for ($dayCtr=$startDateDayNo; $dayCtr<=$endDateDayNo; $dayCtr++){
						if (!array_key_exists($yearCtr,$this->dataAry)){
							$this->dataAry[$yearCtr]=array();
						}
						if (!array_key_exists($monthCtr,$this->dataAry[$yearCtr])){
							$this->dataAry[$yearCtr][$monthCtr]=array();
						}
						if (!array_key_exists($dayCtr,$this->dataAry[$yearCtr][$monthCtr])){
							$this->dataAry[$yearCtr][$monthCtr][$dayCtr]=array();
						}
						$this->dataAry[$yearCtr][$monthCtr][$dayCtr][]=$dayAry;
					}
				}
			}
		}
		//$base->DebugObj->printDebug($this->calendarAry_ajax,1,'xxxf');
		//exit();//xxxf
	}
//=====================================
	function incCalls(){
		$this->callNo++;
	}
}
?>
