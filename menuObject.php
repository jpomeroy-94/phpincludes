<?php
class menuObject {
	var $statusMsg;
	var $callNo = 0;
//====================================================
	function incCalls(){$this->callNo++;}
//====================================================
	function menuObject() {
		$this->incCalls();
		$this->statusMsg='tag Object is fired up and ready for work!';
	}
//====================================================
	function cloneMenu($base){
		//$base->debugObj->printDebug($base->paramsAry,1,'xxx');	
		// this is done by operationProfile001Obj->cloneRow also
		//- get old stuff
		$menuProfileId=$base->paramsAry['menuprofileid'];
		$newMenuName=$base->paramsAry['newmenuname'];
		if ($menuProfileId != null && $newMenuName != null){
		$jobProfileId=$base->paramsAry['jobprofileid'];
		$dbControlsAry=array('dbtablename'=>'menuprofile');
		$base->dbObj->getDbTableInfo(&$dbControlsAry,&$base);
		//foreach ($dbControlsAry as $one=>$two){echo "$one<br>";}
		$query="select * from menuprofile where menuprofileid=$menuProfileId";
		$passAry=array();
		$workAry=$base->dbObj->retrieveAryFromDb($query,$passAry,&$base);
		//$base->debugObj->printDebug($workAry,1,'xxx');
		$newMenuAry=$workAry[0];
		if (count($newMenuAry)>0){
			$newMenuAry['menuname']=$newMenuName;	
			$newMenuAry['jobprofileid']=$jobProfileId;
			unset ($newMenuAry['menuprofileid']);
			$dbControlsAry['writerowsary'][0]=$newMenuAry;
			//$base->debugObj->printDebug($dbControlsAry['writerowsary'],1,'xxxf');
			$base->dbObj->writeToDb($dbControlsAry,&$base);
			$query="select menuprofileid from menuprofileview where jobprofileid=$jobProfileId and menuname='$newMenuName'";
			$passAry=array();
			$workAry=$base->dbObj->retrieveAryFromDb($query,$passAry,&$base);
			$newMenuProfileId=$workAry[0]['menuprofileid'];
			if ($newMenuProfileId != null){
			$query="select * from menuelementprofileview where menuprofileid=$menuProfileId";
			$passAry=array();
			$workAry=$base->dbObj->retrieveAryFromDb($query,$passAry,&$base);
			foreach ($workAry as $ctr=>$menuElementAry){
				unset($workAry[$ctr]['menuelementprofileid']);
				$workAry[$ctr]['menuprofileid']=$newMenuProfileId;	
			}
			$dbControlsAry=array('dbtablename'=>'menuelementprofile');
			$dbControlsAry['writerowsary']=$workAry;
			//$base->debugObj->printDebug($dbControlsAry,1,'xxx');
			$base->dbObj->writeToDb($dbControlsAry,&$base);
			}
			else {echo "didnt create menuprofile row<br>";}
		}
		else {
			echo "nothing in menu: $menuName<br>";	
		}
		}
		else {
			echo "either menuname: $menuName or newmenuname: $newMenuName is invalid<br>";	
		}
	}
//====================================================
	function getMenuForAjax($paramAry,$base){
		//- can only run if in ajax so check if paramsary has 'container'
		$tst=$base->paramsAry['container'];
		$ajaxAry=array();
		if ($tst != null){
		$menuName_raw=$paramAry['param_1'];
		$menuNameAry=explode('_',$menuName_raw);
		$menuName=$menuNameAry[0];
		$menuType=$base->menuProfileAry[$menuName]['menutype'];
		//xxxf - rotate doesnt build the js directory!!!
		if ($menuType == 'rotate'){
			$menuAry=$this->buildMenuFromScratch($menuName,$menuType,&$base);
		}
		else {
			$menuAry=$base->menuProfileAry['jsmenusary'][$menuName];
		}
		//$ajaxAry=$base->ajaxObj->getContainerForAjax(&$base);
		$ajaxAry=array();
		$ajaxAry[]="\n!!menus!!\n";
		$ajaxAry[]='initmenu|'.$menuName."\n";
//- batch update
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
		$batchAry[]='menupictureclass';
		$batchAry[]='menupictureid';
		$batchAry[]='menuobjectclass';
		$batchAry[]='menuobjectid';
		$batchAry[]='menuparamclass';
		$batchAry[]='menuparamid';
		$batchAry[]='menuembedclass';
		$batchAry[]='menuembedid';
		$batchAry[]='menutextid';			
		$batchAry[]='videoheight';
		$batchAry[]='videowidth';
		$batchAry[]='menuimageid';
		$batchAry[]='menutitle';
		$batchAry[]='albumname';
		foreach ($batchAry as $ctr=>$menuFieldName){
			$menuFieldValue=$menuAry[$menuFieldName];
			//if ($menuFieldName=='menutitle'){
				//echo "menuname: $menuName, menutitle: menufieldvalue: $menuFieldValue<br>";
				//$base->debugObj->printDebug($menuAry,1,'xxxf');exit();
			//}
			//xxxf
			if ($menuFieldName == 'pageno'){$menuFieldValue=1;}
			$ajaxAry[]='setetchash|'.$menuFieldName.'|'.$menuFieldValue."\n";		
		}
		//$base->debugObj->printDebug($menuAry,1,'xxxd');//xxxd
//---
		$menuType=$menuAry['menutype'];
		$returnAryLine=NULL;
		$titleAryLine=NULL;
		$firstTime=true;
		if ($menuType == 'rotate' || $menuType =='albumfixed' ){
// - revolving menu
//$base->debugObj->printDebug($menuAry,1,'men');//xxx
			foreach ($menuAry['elements'] as $menuElementNo=>$menuElementAry){
				$returnAryLine_ajax=NULL;
				$titleAryLine_ajax=NULL;
				$textAryLine_ajax=NULL;
				$mediaAryLine_ajax=NULL;
				$tdAryLine_ajax=NULL;
				$firstTime=true;
				$maxPageSize=0;
				//????? xxxf $ajaxAry[]='inithash|etchash|menuelementno|0'."\n";
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
//- titles
					$titleSubElement_raw=$menuAry['titles'][$menuElementNo][$menuSubElementNo];
					$titleSubElement=preg_replace("/!+/","!",$titleSubElement_raw);
					$titleAryLine_ajax.="$ajaxInsert$titleSubElement";
//- text
					$textSubElement_raw=$menuAry['text'][$menuElementNo][$menuSubElementNo];//xxxnew
					$textSubElement=preg_replace("/!+/","!",$textSubElement_raw);
					$textAryLine_ajax.="$ajaxInsert$textSubElement";
//- media
					$mediaSubElement_raw=$menuAry['media'][$menuElementNo][$menuSubElementNo];//xxxnew
					$mediaSubElement=preg_replace("/!+/","!",$mediaSubElement_raw);
					$mediaAryLine_ajax.="$ajaxInsert$mediaSubElement";
//- elements
					$returnAryLine_ajax.="$ajaxInsert$menuSubElement";
					$maxPageSize++;
				} // end foreach menuelementary
				//echo "returnaryline_ajax: $returnAryLine_ajax<br>";//xxx
				$ajaxAry[]='initsetary|elements|'.$menuElementNo.'|'.$returnAryLine_ajax."\n";
				$ajaxAry[]='initsetary|titles|'.$menuElementNo.'|'.$titleAryLine_ajax."\n";
				$ajaxAry[]='initsetary|text|'.$menuElementNo.'|'.$textAryLine_ajax."\n";
				$ajaxAry[]='initsetary|media|'.$menuElementNo.'|'.$mediaAryLine_ajax."\n";
				//- below has issues - is it count of elements or albums
				$ajaxAry[]="initsethash|etchash|$menuElementNo|maxpagesize|$maxPageSize\n";
			} // end foreach menuary['elements']
		}
		else {
// - simple menu
//$base->debugObj->printDebug($menuAry,1,'menu');//xxx
//xxxf - not sure what to do with this - vertical menu does not get elements, titles, etc.
			foreach ($menuAry['elements'] as $menuElementNo=>$menuElement_raw){
				$menuElement=str_replace("'","\'",$menuElement_raw);
				//echo "tdeleemetn: $tdElement, menuelement: $menuElementNo<br>";//xxx
				if ($firstTime){$commaInsert=NULL;$firstTime=false;}
				else {$commaInsert=', ';}
				$returnAryLine.="$commaInsert'$menuElement'";
				$menuTitleElement=NULL;
				$titleAryLine.="$commaInsert'placeholder'";
			}
			$returnAryLine=str_replace("'",'',$returnAryLine);
			$returnAryLine=str_replace(",",'~',$returnAryLine);
			//- below is old way
			//$ajaxAry[]=$menuName.'|elementsary|'.$returnAryLine."\n";
			//- new way
			//xxxf - below fixed 12/1/2010 to match ajax.js
			$ajaxAry[]="initsetary|elements|0|".$returnAryLine."\n";
			//echo "returnaryline: $returnAryLine<br>";//xxx
			//$titleAryLine_forObj=str_replace("'",'',$titleAryLine);
			//$titleAryLine_forObj=str_replace(",",'~',$titleAryLine_forObj);
			//$returnAry[]="menuObj.setArrays('titles','','','$titleAryLine_forObj');\n";
			//- old way: $ajaxAry[]=$menuName.'|titles|'.$titleAryLine."\n";
			//-xxxf why does the below have the same as elements
			$ajaxAry[]='initsetary|titles|0|'.$returnAryLine."\n";
			//xxxf why isn't below finished?
			$eleCnt=count($menuAry['elementsother']);
		}
		}
		return $ajaxAry;
	}
//======================================================xxxf22
	function buildMenuFromScratch($menuName, $menuType, $base){
		$mpAry=$base->menuProfileAry[$menuName];
		$mepAry=$base->menuElementProfileAry[$menuName];
		$menuAry=array();
		$elementsAry=array();
		$titlesAry=array();
		$textAry=array();
		$mediaAry=array();
		foreach ($mpAry as $name=>$value){$menuAry[$name]=$value;}
		if ($menuType=='albumfixed'){$useMenuType='rotate';}
		else {$useMenuType=$menuType;}
		switch ($useMenuType){
			case 'rotate':
			// get all of menuprofile
			foreach ($mepAry as $meName=>$meAry){
				$meType=$meAry['menuelementtype'];
				if ($meType=='album'){
					$albName=$meAry['menuelementname'];
					$albAry=$base->albumProfileAry[$albName];
					$albSrcAry=array();
					$albTitleAry=array();
					$albTextAry=array();
					$albMediaAry=array();
					foreach ($albAry as $imgName=>$imgAry){
						$srcStrg=$imgAry['picturedirectory'].'/'.$imgAry['picturefilename'];
						$albSrcAry[]=$srcStrg;
						$titleStrg=$imgAry['picturetitle'];
						$albTitleAry[]=$titleStrg;
						$textStrg=$imgAry['picturetext'];
						$albTextAry[]=$textStrg;
						$mediaStrg=$imgAry['mediatype'];
						$albMediaAry[]=$mediaStrg;
					}
					$elementsAry[]=$albSrcAry;
					$titlesAry[]=$albTitleAry;
					$textAry[]=$albTextAry;
					$mediaAry[]=$albMediaAry;
				}
				else {
					$meLabel=$meAry['menuelementlabel'];
					$elementsAry[]=$meLabel;
				}
			}
			//$base->debugObj->printDebug($menuAry,1,'elementsaryxxxf');
			break;
		default:
			foreach ($mepAry as $meName=>$meAry){
				$meLabel=$meAry['menuelementlabel'];
				$elementsAry[]=$meLabel;		
			}
		}
		$menuAry['elements']=$elementsAry;
		$menuAry['titles']=$titlesAry;
		$menuAry['text']=$textAry;
		$menuAry['media']=$mediaAry;
		//$base->debugObj->printDebug($menuAry,1,'xxxf');exit();
		return $menuAry;
	}
//------------------------------------------------------
function insertMenu($paramFeed,$base){
		$base->debugObj->printDebug("insertMenu($paramFeed,'base')",0); //xx (h)
		$menuName=$paramFeed['param_1'];
		$menuAry=$base->menuProfileAry[$menuName];
		$menuElementsAry=$base->menuElementProfileAry[$menuName];
		$sortOrder=$base->menuProfileAry['sortorder'][$menuName];
		$menuType=$menuAry['menutype'];
		if ($menuType == ''){
			echo "error!!! name: $menuName, type: $menuType<br>";//xxxf
			//$base->debugObj->printDebug($base->menuProfileAry,1,'xxxf');
			//exit();//xxxf
		}
		//echo "$menuName, $menuType<br>";//xxxf
		$base->fileObj->writeLog('debug1',"insert a menuname: $menuName menutype: $menuType",&$base);
		switch ($menuType){
			case 'horizontal':
				$returnAry=$base->plugin002Obj->insertMenuHorizontal($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'vertical':
				$returnAry=$this->insertMenuVertical($sortOrder,$menuAry,$menuElementsAry,&$base);
				//$base->debugObj->printDebug($returnAry,1,'xxxf returnary');
			break;
			case 'horizontaldropdown':
				$returnAry=$base->plugin002Obj->insertMenuHorizontalDropDown($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'rotate':
				$returnAry=$this->insertMenuRotate($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'fixed':
				$returnAry=$base->plugin002Obj->insertMenuFixed($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'albumfixed':
				$returnAry=$base->plugin002Obj->insertMenuFixed($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			case 'album':
				$returnAry=$base->plugin002Obj->insertMenuAlbum($sortOrder,$menuAry,$menuElementsAry,&$base);
			break;
			default:
				//echo "menuname: $menuName, menutype: $menuType is invalid!!!<br>";
			break;
		}
		$base->fileObj->writeLog('debug1',"end of insert a menu",&$base);
		$base->debugObj->printDebug("-rtn:insertMenu",0); //xx (f)	
		return $returnAry;
	}
//---------------------------------------
	function insertMenuRotate($sortOrder,$menuAry,$menuElementsAry,&$base){
		//$base->debugObj->printDebug($menuAry,1,'mea');//xxx
		$menuName=$menuAry['menuname'];
		$menuClass=$menuAry['menuclass'];
//- title id
		$menuTitleId=$menuAry['menutitleid'];
		if ($menuTitleId==NULL){$menuTitleId=$menuName.'title';}
		$menuTitleIdInsert="id=\"$menuTitleId\"";
//- text id
		$menuTextId=$menuAry['menutextid'];//xxxnew
		if ($menuTextId==NULL){$menuTextIdInsert=NULL;}
		else {$menuTextIdInsert="id=\"$menuTextId\"";}
//- text class
		$menuTextClass=$menuAry['menutextclass'];
		if ($menuTextClass!=NULL){$menuTextClassInsert="class=\"$menuTextClass\"";}
		else {$menuTextClassInsert=NULL;}
//- title class
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitleClass!=NULL){$menuTitleClassInsert="class=\"$menuTitleClass\"";}
		else {$menuTitleClassInsert=NULL;}
//- menuchangetype
		$menuChangeType=$menuAry['menuchangetype'];
		if ($menuChangeType == null){$menuChangeType='button';}
//- menudisplaytype
		$menuDisplayType=$menuAry['menudisplaytype'];
//- previous event
		$menuPreviousEvent=$menuAry['menupreviousevent'];
		$menuPreviousEvent=$base->utlObj->returnFormattedString($menuPreviousEvent,&$base);
		//if ($menuPreviousEvent==null){$menuPreviousEvent="onclick=\"previousPictureV2('$menuName','$menuTextId');\"";}
//- next event
		$menuNextEvent=$menuAry['menunextevent'];
		$menuNextEvent=$base->utlObj->returnFormattedString($menuNextEvent,&$base);
//xxxf - what about this?
		//if ($menuNextEvent==null){$menuNextEvent="onclick=\"nextPictureV2('$menuName','$menuTextId')\"";}
//echo "name: $menuName, display: $menuDisplayType<br>";//xxxf
		switch ($menuDisplayType){
			case 'none':
				$doCaption=false;
				$doTitle=false;
				$doPaging=false;
				break;;
			case 'caption':
				$doCaption=true;
				$doTitle=false;
				$doPaging=true;
				break;;
			case 'title':
				$doCaption=false;
				$doTitle=true;
				$doPaging=true;
				break;;
			case 'titlecaptionelsewhere':
				$doCaption=false;
				$doTitle=true;
				$doPaging=true;
				break;;
			case 'titlecaption':
				$doCaption=true;
				$doTitle=true;
				$doPaging=true;
				break;;
			default:
				$doCaption=false;
				$doTitle=false;
				$doPaging=true;
		}
		//- menu class
		$menuClass=$menuAry['menuclass'];
		if ($menuClass==null){$menuClassInsert=null;$menuClassMainInsert=null;}
		else {$menuClassInsert="class=\"$menuClass\"";$menuClassMainInsert="class=\"$menuClass"."_main\"";}
		//- menu id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=null;}
		else {$menuIdInsert="id=\"$menuId\"";}
		//- image id
		$menuImageId=$menuAry['menuimageid'];
		$menuImageIdInsert="id=\"$menuImageId\"";
		$menuPagingClass=$menuAry['menupagingclass'];
		$menuType=$menuAry['menutype'];
		$returnAry=array();
//-beginning of table
		$returnAry[]="<!-- start rotating menu: $menuName -->\n";
		$returnAry[]="<table $menuClassMainInsert><tr><td>\n";
		$theKeys=array_keys($menuElementsAry);
		$menuElementId=$theKeys[0];
		$menuElementAry=$menuElementsAry[$menuElementId];
		$menuElementAlt=$menuElementAry['menuelementalt'];
		$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
		$menuElementClass=$menuElementAry['menuelementclass'];
		$menuElementNo=$menuElementAry['menuelementno'];
		if ($menuElementClass == null){$menuElementClass=$menuClass;}
		$menuElementId=$menuElementAry['menuelementid'];
		$workAry=array();
		$workAry['menuelementalt']=$menuElementAlt;
		$workAry['menuelementalertclass']=$menuElementAlertClass;
		$workAry['menuelementclass']=$menuElementClass;
		$workAry['menuelementid']=$menuElementId;
		$albumName=$menuElementAry['albumname'];
		//echo "albumname: $albumName<br>";//xxx
		if ($albumName != NULL){
			$albumPicturesAry=$base->albumProfileAry[$albumName];
			$theKeys=array_keys($albumPicturesAry);
			$albumPictureName=$theKeys[0];
			$albumPictureAry=$albumPicturesAry[$albumPictureName];
			$pictureFileName=$albumPictureAry['picturefilename'];
			$pictureFileNameAry=explode('.',$pictureFileName);
			$smallPictureFileName=$pictureFileNameAry[0].'.'.$pictureFileNameAry[1];
			$thumbnailPictureFileName=$pictureFileNameAry[0].'_TT.'.$pictureFileNameAry[1];
			$sourcePath=$albumPictureAry['picturedirectory']."/$smallPictureFileName";
			$pictureTitle=$albumPictureAry['picturetitle'];
			if ($pictureTitle == NULL){$pictureTitle=$albumPictureName;}
			$pictureText=$albumPictureAry['picturetext'];// xxxnew this and next
			$pictureText=str_replace(chr(0x0a),'',$pictureText);
			$pictureText=str_replace(chr(0x0d),'',$pictureText);
			if ($pictureText == NULL){$pictureText=$albumPictureName;}
//echo "title: $pictureTitle, text: $pictureText<br>";//xxxd
			$returnAry[]="<table $menuIdInsert $menuClassInsert><tr><td $menuTitleClassInsert >\n";
			if ($doTitle){
				$returnAry[]="<div $menuTitleClassInsert $menuTitleIdInsert>";
				$returnAry[]="$pictureTitle";
				$returnAry[]="</div>\n";
			}
			$returnAry[]="</td></tr>\n";
			//- the image
			$returnAry[]="<tr><td $menuClassInsert>\n";
			$returnAry[]="<img src=\"$sourcePath\" $menuImageIdInsert $menuClassInsert>\n";
			//- below needs to be turned on with a switch
			$returnAry[]="</td></tr><tr><td $menuTextClassInsert>\n";
			if ($doCaption){
				$returnAry[]="<div $menuTextIdInsert $menuTextClassInsert>$pictureText</div>\n";
			}
			$returnAry[]="</td></tr><tr><td>\n";
			if ($doPaging){
//- standard button
				$returnAry[]="<table class=\"standardbutton\">\n<tr><td class=\"prevstandardbutton\">\n<div class=\"prevstandardbutton\" $menuPreviousEvent>Previous</div>\n</td>\n";
				$returnAry[]="<td class=\"nextstandardbutton\">\n<div class=\"nextstandardbutton\" $menuNextEvent>Next</div>\n";
				$returnAry[]="</td></tr>\n</table>\n";
			}
			$returnAry[]="</td></tr></table>\n";
		} // end if albumname
		$returnAry[]="</td></tr></table>\n";
		$returnAry[]="<!-- end of rotating menu: $menuName ->\n";
		return $returnAry;
	}
//====================================================
	function getMenuForAjaxJson($paramFeed,$base){
		//- can only run if in ajax so check if paramsary has 'container'
		$tst=$base->paramsAry['container'];
		$ajaxAry=array();
		if ($tst != null){
		$menuName_raw=$paramFeed['param_1'];
		$menuNameAry=explode('_',$menuName_raw);
		$menuName=$menuNameAry[0];
		//echo "menuname: $menuName<br>";//xxxf
		$jsonAry=array();
		//$base->debugObj->printDebug($base->menuProfileAry['jsmenusary'],1,'menuary');exit();//xxxf
		$jsonAry['etchash']=$base->menuProfileAry[$menuName];
		$jsonAry['etchash']['pageno']=1;
		//$base->debugObj->printDebug($jsonAry,1,'xxxf jsonary');exit();
//---
		$menuType=$jsonAry['etchash']['menutype'];
		//xxxf22 need to put in conversions for %dblqt%, etc.
		$convertStrg='menunextevent_menupreviousevent_menuevent';
		$convertAry=explode('_',$convertStrg);
		foreach ($convertAry as $ctr=>$convName){
			$convValue_raw=$jsonAry['etchash'][$convName];
			//echo "$convName, $convValue_raw<br>";//xxxf
			$convValue=$base->utlObj->returnFormattedString($convValue_raw,&$base);
			$jsonAry['etchash'][$convName]=$convValue;
		}
		//$base->debugObj->printDebug($jsonAry,1,'xxxf');exit();
		if ($menuType == 'rotate' || $menuType == 'albumfixed'){
			$workAry=$this->buildMenuFromScratch($menuName,$menuType,&$base);
			$jsonAry['elementsary']=$workAry['elements'];
			$jsonAry['titlesary']=$workAry['titles'];
			$jsonAry['textary']=$workAry['text'];
			$jsonAry['mediaary']=$workAry['media'];
		}
		else {
			//need to add in videoid
			$jsonAry['elementsary']=$base->menuProfileAry['jsmenusary'][$menuName]['elements'];
			$jsonAry['titlesary']=$base->menuProfileAry['jsmenusary'][$menuName]['titles'];
			$jsonAry['textary']=$base->menuProfileAry['jsmenusary'][$menuName]['text'];
			$jsonAry['mediaary']=$base->menuProfileAry['jsmenusary'][$menuName]['media'];
			$jsonAry['videoary']=$base->menuProfileAry['jsmenusary'][$menuName]['video'];
		}
		//xxxf - pipe character is invalid for json why?
		foreach ($jsonAry['elementsary'] as $ctr=>$theElement){
			$theElement=str_replace('|','&#124',$theElement);
			$jsonAry['elementsary'][$ctr]=$theElement;
		}
		//xxxf -must set this up manually $ajaxAry=$base->ajaxObj->getContainerForAjaxInternal(&$base);
		$ajaxAry[]="\n!!menus!!\n";
		$jsonStrg=$base->xmlObj->array2Json($jsonAry,&$base);
		$ajaxAry[]="loadjson|$menuName|".$jsonStrg."\n";
		}
		return $ajaxAry;
	}
//--------------------------------------- xxxd
	function insertMenuVertical($sortOrder,$menuAry,$menuElementsAry,$base){
		$returnAry=array();
		$jsMenuAry=array();
		$jsMenuElementAry=array();
		$menuMaxElements=$menuAry['menumaxelements'];
		$menuName=$menuAry['menuname'];
		$menuType=$menuAry['menutype'];
		//echo "menuname: $menuName<br>";//xxx
	//- class
		$menuClass=$menuAry['menuclass'];
		if ($menuClass != NULL){$menuClassInsert="class=\"$menuClass\"";}
		else {$menuClassInsert='';}
	//- selectedclass
		$menuSelectedClass=$menuAry['menuselectedclass'];
		if ($menuSelectedClass != ''){$menuSelectedClassInsert="class=\"$menuSelectedClass\"";}
		else {$menuSelectedClassInsert=NULL;}
	//- nonselectedclass	//- setup nonselectedclass
		$menuNonSelectedClass=$menuAry['menunonselectedclass'];
		if ($menuNonSelectedClass != ''){$menuNonSelectedClassInsert="class=\"$menuNonSelectedClass\"";}
		else {$menuNonSelectedClassInsert=NULL;}
	//- paging class
		$menuPagingClass=$menuAry['pagingclass'];
		if ($menuPagingClass != NULL){$menuPagingClassInsert="class=\"$menuPagingClass\"";}
		else {$menuPagingClassInsert=$menuClassInsert;}
	//- id
		$menuId=$menuAry['menuid'];
		if ($menuId == NULL){$menuIdInsert=NULL;}
		$menuIdInsert="id=\"$menuId\"";
	//- bullet 
		$menuBulletPath=$menuAry['menubulletpath'];
		if ($menuBulletPath == NULL){$menuBulletInsert=NULL;}
		else {$menuBulletInsert="<td $menuClassInsert><img src=\"$menuBulletPath\" $menuClassInsert></td>";}
	//- title
		$menuTitle=$menuAry['menutitle'];
		$menuTitleClass=$menuAry['menutitleclass'];
		if ($menuTitle == NULL){$menuTitleInsert=NULL;}
		else {
			if ($menuTitleClass==NULL){$menuTitleClassInsert=NULL;}
			else {$menuTitleClassInsert="class=\"$menuTitleClass\"";}
			$menuTitleInsert="<caption $menuTitleClassInsert>$menuTitle</caption>";
		}
	//- delimiter
		$menuDelimiter=$menuAry['menudelimiter'];
	//- alt
		$menuAlt=$menuAry['menualt'];
		if ($menuAlt == NULL){$menuAltInsert=NULL;}
		else {$menuAltInsert="title=\"$menuAlt\"";}
	//- event
		$menuEvent_raw=$menuAry['menuevent'];
		$menuEvent=$base->utlObj->returnFormattedString($menuEvent_raw,&$base);
	//- next event (for button at bottom)
		$menuEventNext_raw=$menuAry['menunextevent'];
		$menuEventNext=$base->utlObj->returnFormattedString($menuEventNext_raw,&$base);
//- start building menu
	//- heading
		$returnAry[]="\n<!-- start verticalmenu: $menuName -->\n";
	//- setup <table ...
		$returnAry[]="<table $menuClassInsert $menuIdInsert $menuEvent>";
	//- setup title
		$returnAry[]=$menuTitleInsert;
	//- setup table cells holding menu items
		$allDone=false;
		$noElements=count($sortOrder);
		if ($menuMaxElements >0 && $menuMaxElements>$noElements){$menuMaxElements=0;}
		//$base->debugObj->printDebug($menuElementsAry,1,'mea3');//xxx
		//$base->debugObj->printDebug($sortOrder,1,'sortorder');//xxx
//- loop through menu rows
		$firstTime=true;
		for ($rowCtr=1;$rowCtr<=$noElements;$rowCtr++){
			$menuElementCtr=$sortOrder[$rowCtr];
			$menuElementAry=$menuElementsAry[$menuElementCtr];
			//$base->debugObj->printDebug($menuElementAry,1,'mea2');//xxx
			$menuElementName=$menuElementAry['menuelementname'];
			if ($rowCtr==1){
				$lastId=$menuElementAry['menuelementid'];
				//echo "lastId: $lastId, menuelementname: $menuElementName<br>";
				if ($lastId==NULL){$lastId=$menuElementName;}
			}
			//xxxf22 - need to change the below to use what is stored defaulting to this
			if ($menuMaxElements > 0 && $noElements > $menuMaxElements && $rowCtr==$menuMaxElements){
				if ($menuEventNext == null){
					$returnAry[]="<tr><td $menuClassInsert><a href=\"#\" $menuIdInsert $menuPagingClassInsert onclick=\"pageNextV2('$menuName');\">-more-</a></td></tr>";
				}
				else {
					$returnAry[]="<tr><td $menuClassInsert><a href=\"#\" $menuIdInsert $menuPagingClassInsert $menuEventNext>-more-</a></td></tr>";
				}
				$allDone=true;
			}
//- url
			$menuElementUrl_raw=$menuElementAry['menuelementurl'];
			$menuElementUrl=$base->utlObj->returnFormattedString($menuElementUrl_raw,&$base);
			$menuElementTarget=$menuElementAry['menuelementtarget'];
			if ($menuElementTarget != null){$menuElementTargetInsert="target=\"$menuElementTarget\"";}
			else {$menuElementTargetInsert=null;}
//- get class
			$menuElementClass=$menuElementAry['menuelementclass'];
			$menuElementClass_td=$menuElementClass.'_td';
			if ($menuElementClass != NULL){
				$menuElementClassInsert=" class=\"$menuElementClass\"";
				$menuElementClassTdInsert=" class=\"$menuElementClass_td\"";
			}
			else {$menuElementClass=$menuClass;$menuElementClassInsert=$menuClassInsert;$menuElementTdClassInsert=NULL;}
//- selected class - has a selected field
			$menuElementSelectedFieldName=$menuElementAry['menuelementselectedfieldname'];
			$menuElementSelectedFieldValue=$menuElementAry['menuelementselectedfieldvalue'];
			if ($menuElementSelectedFieldName != NULL){
				$testForSelectedClass=$base->paramsAry[$menuElementSelectedFieldName];
				if ($menuElementSelectedFieldName == 'always'){$doit=true;}
				elseif ($testForSelectedClass == $menuElementSelectedFieldValue && $menuSelectedClassInsert != NULL){
					$doit=true;
				}
				else {$doit=false;}
				if ($doit){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;	
				}
				else {
					$useMenuElementClassInsert=$menuClassInsert;
					$useMenuElementClass=$menuClass;
				}
			}
//- selected class - no selected field, so look at jobname
			else {
				$jobName=$base->jobProfileAry['jobname'];
				$menuElementUrlAry=explode('&',$menuElementUrl);
				$menuElementUrlTest=$menuElementUrlAry[0];
				if ($menuElementUrlTest == $jobName && $menuSelectedClassInsert != NULL){
					$useMenuElementClassInsert=$menuSelectedClassInsert;	
					$useMenuElementClass=$menuSelectedClass;
				}
				else {
					$useMenuElementClassInsert=$menuElementClassInsert;
					$useMenuElementClass=$menuElementClass;
				}
			}
//- get id
			$menuElementId=$menuElementAry['menuelementid'];
			if ($menuElementId==NULL){$menuElementId=$menuElementAry['menuelementname'];}
			$menuElementIdInsert="id=\"$menuElementId\"";
			$menuElementIdTdInsert="id=\"$menuElementId_td\"";
//- get label and modify and add events if needed
			$menuElementLabel_raw=$menuElementAry['menuelementlabel'];
			$menuElementLabel=$base->utlObj->returnFormattedString($menuElementLabel_raw,&$base);
			$menuElementEventAttributes_raw=$menuElementAry['menuelementeventattributes'];
			$menuElementEventAttributes=$base->utlObj->returnFormattedString($menuElementEventAttributes_raw,&$base);
			$useMenuElementLabel_div="<div $useMenuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementLabel_div="<div $menuElementClassInsert $menuElementIdInsert $menuAltInsert $menuElementEventAttributes>$menuElementLabel</div>";
			$menuElementType=$menuElementAry['menuelementtype'];
			if ($menuElementType == NULL){$menuElementType='url';}
//- change label positions with !!xxx!!
			if (strpos($menuElementLabel,'!!',0) !== false) {
				$doLabelInsert=true;
				$menuLineAry=$base->htmlObj->convertHtmlLine($menuElementLabel,&$base);
				//echo "menulineary: $menuLineAry<br>";//xxx
			} // end if strpos!!
			else {$doLabelInsert=false;}
			$menuElementAlt=$menuElementAry['menuelementalt'];
			$menuElementNo=$menuElementAry['menuelementno'];
			$menuElementAlertClass=$menuElementAry['menuelementalertclass'];
			$workAry=array();
			$workAry['menuelementalt']=$menuElementAlt;
			$workAry['menuelementalertclass']=$menuElementAlertClass;
			$workAry['menuelementclass']=$menuElementClass;
			$workAry['menuelementid']=$menuElementId;
			$jsMenuElementAry[$menuElementNo]=$workAry;			
			//echo "name: $menuElementName, type: $menuElementType<br>";//xxx
			//$base->debugObj->printDebug($menuElementAry,1,'mea');//xxx
			if (!$firstTime && $menuDelimiter != NULL){
				$returnAry[]="<tr><td class=\"menudelimiter\"><div class=\"menudelimiter\">$menuDelimiter</div></td></tr>\n";
			}
			//- xxxf problem needs to be fixed later
			if ($menuElementType == 'paragraph'){$menuElementType='para';}
			$base->fileObj->writeLog('jefftest',"menueletype: $menuElementType",&$base);//xxxf
			switch ($menuElementType){
//- element is url
			case 'url':
				//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
				$htmlElementAry=array();
				$htmlElementAry['label']=$menuElementLabel;
				$htmlElementAry['htmlelementclass']=$menuElementClass;
				$htmlElementAry['joblink']=$menuElementUrl;	
				$htmlElementAry['htmlelementeventattributes']=$menuElementEventAttributes;
				$htmlElementAry['htmlelementtarget']=$menuElementTarget;
				//echo "menuelementtarget: $menuElementTarget";//xxxf
				$workAry=$base->htmlObj->buildUrl($htmlElementAry,&$base);
				//$base->debugObj->printDebug($workAry,1,'xxxf',&$base);//xxxf
				$menuElementUrl_html=$base->utlObj->returnFormattedData($menuElementUrl,'url','html',&$base);
				if (!$allDone){
					$returnAry[]='<tr>';
					$returnAry[]="<td $useMenuElementClassInsert>";
					//$returnAry[]="<li>";
					$returnAry[]=$menuBulletInsert;
					$returnAry=array_merge($returnAry,$workAry);
					//$returnAry[]="</li>";
					$returnAry[]='</tr>';
				} // end if !alldone
				$jsMenuAry[]="$menuBulletInsert<a href=\"$menuElementUrl_html\" $menuElementTargetInsert $menuElementClassInsert $menuElementIdInsert $menuAltInsert>$menuElementLabel</a>";
				break;
			case 'para':
				$menuElementName=$menuElementAry['menuelementname'];
				$menuElementNameAry=explode('_',$menuElementName);
				$menuElementName=$menuElementNameAry[0];
				$paramFeed=array('param_1'=>$menuElementName);
				//$base->debugObj->printDebug($paramFeed,1,'xxxf');exit();//xxxf
				$subReturnAry=$base->plugin002Obj->insertParagraph($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]="</td></tr>";
				break;
			case 'table':
				$tableName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$tableName);
				$menuElementDisplayAry=$base->tagObj->insertTable($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'menu':
				$menuName=$menuElementAry['menuelementname'];
				$paramFeed=array('param_1'=>$menuName);
				$menuElementDisplayAry=$this->insertMenu($paramFeed,&$base);
				$returnAry[]="<tr><td $useMenuElementClassInsert>";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'map':
				$mapProfileId=$menuElementAry['mapprofileid'];
				$mapName=$base->mapProfileAry['main'][$mapProfileId]['mapname'];
				$menuElementDisplayAry=$base->htmlObj->buildMap($mapName,&$base);
				$returnAry[]='<tr>';
				$returnAry[]="<td $menuElementClassInsert>\n";
				$returnAry=array_merge($returnAry,$menuElementDisplayAry);
				$returnAry[]="</td></tr>";
				break;
			case 'form':
				$passAry=array();
				$passAry['param_1']=$menuElementName;
				$subReturnAry=$base->tagObj->insertForm($passAry,&$base);
				$returnAry[]='<tr><td>';
				$returnAry=array_merge($returnAry,$subReturnAry);
				$returnAry[]='</td></tr>';
				//$base->debugObj->printDebug($subReturnAry,1,'srtn');//xxx
				//exit();//xxx			
				break;	
			case 'repeatingform':
				$passAry=array();
				$query_raw=$menuElementAry['menuelementsql'];
				//-below let querytable do the formatting
				//$query=$base->utlObj->returnFormattedString($query_raw,&$base);
				//!!! - below needs to check if using db2
				$useOtherDb_raw=$base->formProfileAry[$menuElementName]['formuseotherdb'];
				$useOtherDb=$base->utlObj->returnFormattedData($useOtherDb_raw,'boolean','internal');
				if ($useOtherDb){$base->dbObj->setUseOtherDb(&$base);}
				$base->fileObj->writeLog('jefftest',"queryraw: $query_raw",&$base);//xxxf
				$result=$base->dbObj->queryTable($query_raw,'read',&$base,0);
				$workAry=$base->utlObj->tableToHashAryV3($result,$passAry);
				//echo "query: $query_raw";//xxxf
				//$base->debugObj->printDebug($workAry,1,'xxxf');
				$passAry['param_1']=$menuElementName;
				foreach ($workAry as $ctr=>$workRowAry){
					//echo "$ctr<br>";//xxx
					$base->paramsAry['ctr']=$ctr;
					$tst=$base->paramsAry['ctr'];//xxxf
					//echo "ctr in paramsary in vmenu: $tst<br>";//xxxf
					$workRowAry['ctr']=$ctr;
					$passAry['usethisdataary']=$workRowAry;
					$tabIndexBase=($ctr+1)*10;
					$passAry['tabindexbase']=$tabIndexBase;
					//$base->debugObj->printDebug($workRowAry,1,'xxxworkrowary');
					//echo "build a form<br>";//xxxd
					$subReturnAry=$base->tagObj->insertForm($passAry,&$base);
					unset ($passAry['usethisdata']);
					$returnAry[]="<tr><td>\n";
					$headingStr="<!-- Form Number: $ctr -->\n";
					//echo "$headingStr<br>";//xxx
					$returnAry[]=$headingStr;
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="</tr></td>\n";
				}
				//- need to write how many forms have been created here
				$base->formProfileAry[$menuElementName]['formcount']=$ctr;
				//$base->debugObj->printDebug($returnAry,1,'rtnary');//xxx
				//exit(0);//xxx
				break;
				case 'album':
					$albumProfileId=$menuElementAry['albumprofileid'];
					//echo "albumprofileid: $albumProfileId<br>";//xxx
					$passAry=$base->htmlObj->buildAlbumTable($albumProfileId,&$base);
					$albumTableDisplayAry=$passAry['returnary'];
					$albumName=$passAry['albumname'];
					if (!array_key_exists('jsary',$base->albumProfileAry)){$base->albumProfileAry['jsary']=array();}
					$base->albumProfileAry['jsary'][$albumName]=$passAry[$albumName];
					//$base->debugObj->printDebug($albumTableDisplayAry,1,'atdaxxxa');
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$returnAry=array_merge($returnAry,$albumTableDisplayAry);
					$returnAry[]="</td></tr>\n";
				break;
				case 'image':
					$imageName=$menuElementName;
					$returnAry[]="<tr><td $useMenuElementClassInsert>\n";
					$subReturnAry=$base->htmlObj->buildImg($imageName,&$base);
					$returnAry=array_merge($returnAry,$subReturnAry);
					$returnAry[]="<span $menuElementClassInsert>$menuElementLabel</span";
					$returnAry[]="</td></tr>\n";
				break;
			default:
//- element is text
				if (!$allDone){
					//$base->debugObj->printDebug($menuElementAry,1,'mea');//xxx
					//echo "url label: $menuElementLabel, class: $useMenuElementClass<br>";//xxx
					$returnAry[]='<tr>';
					if ($doLabelInsert){
						$returnAry[]="<td $menuElementIdInsert $useMenuElementClassInsert>\n";
						//$returnAry[]="<li>";
						$returnAry=array_merge($returnAry,$menuLineAry);
						//$returnAry[]="</li>";
						$returnAry[]="</td>\n";
					} // end dolabelfirst
					else {
						$returnAry[]="$menuBulletInsert<td $menuElementIdTdInsert $menuElementClassTdInsert>$useMenuElementLabel_div</td>\n";
					} // end else for dolabelfirst
					$returnAry[]='</tr>';
				} // end if !alldone
				$jsMenuAry[]="$menuElementLabel_div";
			} // end switch menuelementtype
			$firstTime=false;
		} // end for rowctr = 1 - 99
		//exit(0);//xxx
		$returnAry[]="</table>\n";
		$returnAry[]="<!-- end verticalmenu: $menuName -->\n";
		//$returnAry[]='</ul>';
		$base->menuProfileAry['jsmenusary'][$menuName]=array();
		$base->menuProfileAry['jsmenusary'][$menuName]['menuclass']=$menuClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menupagingclass']=$menuPagingClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menuselectedclass']=$menuSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menunonselectedclass']=$menuNonSelectedClass;
		$base->menuProfileAry['jsmenusary'][$menuName]['menutype']='verticle';
		$base->menuProfileAry['jsmenusary'][$menuName]['menuid']=$menuId;		
		if ($menuMaxElements == NULL){$menuMaxElements=0;}
		$base->menuProfileAry['jsmenusary'][$menuName]['maxpagesize']=$menuMaxElements;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastid']=$lastId;
		$base->menuProfileAry['jsmenusary'][$menuName]['lastmenuelementno']=0;
		$base->menuProfileAry['jsmenusary'][$menuName]['elements']=$jsMenuAry;
		$base->menuProfileAry['jsmenusary'][$menuName]['elementsother']=$jsMenuElementAry;
		$base->debugObj->printDebug("rtn: insertmenuvertical",0);//xx
		return $returnAry;				
	}
//====================================================
	function status(){
		$this->incCalls();
		echo "<br> $this->statusMsg ";
		echo "(No of calls: $this->callNo)\n";
	}
//====================================================

//end of functions
}
?>