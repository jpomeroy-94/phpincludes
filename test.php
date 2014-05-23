var MenuObject = Class.create({
//==========================================================
	runOperation: function(jobParamsAry){
		utilObj.writeLog('debug1id','!!MenuObj.runOperation!!');
		var jobName=jobParamsAry[0];
		var operationName=jobParamsAry[1];
		//alert ('run operation(rmo): '+jobName+', '+operationName);//xxxd
		var formName='';
		var dbTableName='';
		var sendDataAry=new Array();
		var noJobParams=jobParamsAry.length;
		var nameFlg=true;
		var sendDataAry=new Array();
		for (var lp=0; lp lt noJobParams; lp++){
			if (nameFlg==true){var theName=jobParamsAry[lp];nameFlg=false;}
			else {
				var theValue=jobParamsAry[lp];
				if (theValue == 'uservalue'){
					theValue=UserObj.getEtcValue(theName);
				}
				sendDataAry[sendDataAry.length]=theName+'|'+theValue;
				nameFlg=true;
			}
		}
		postAjaxSimple(formName,jobName,operationName,dbTableName,sendDataAry);
	},
	menuTest: function (){
		var sendDataAry = new Array();
	}
	});
