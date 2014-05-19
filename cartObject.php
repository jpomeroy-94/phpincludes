<?php
//require_once('/home/catalog/public_html/includes/plurals.php');

/*
$pos = strpos($_SERVER['PHP_SELF'], "/checkOut/");

if ($_SERVER['HTTPS'] == 'on' && ($pos === false)) {
	//include('ssloff.php');
	}
else if ($_SERVER['HTTPS'] != 'on' && (!is_bool($pos) && $pos == 0)) {
	//include('sslon.php');
	}
*/
session_start();
// shopping cart object
// put this in your session and smoke it!
class cartObject {
	var $cart_items;
	var $accountinfo;
	var $shippinginfo;
	var $processed;
	var $itemtotal;
	var $shippingtotal;
	var $ordertotal;
	var $whenProcessed;
	var $ordernumber;
	var $process_error;
	var $processExtra;
	var $cardtype;
	var $cardnum;
	var $expires;
	var $nameoncard;
	var $havecardinfo;
	function cartObject() {
		$this->cart_items = array();
	/*
		$this->accountinfo = new accountinfo();
		$this->shippinginfo = new shippinginfo();
	*/
		$this->processed = false;
		$this->process_error = '';
		$this->cardtype = '';
		$this->cardnum = '';
		$this->expires = '';
		$this->nameoncard = '';
		$this->havecardinfo = false;
		}
	//===============================================================
	function set_account($account_id) {
		$this->accountinfo->account_id = $account_id;
		}
	//===============================================================
	function set_shipping($request_array) {
		$this->shippinginfo->loadShippinginfoFromRequest($request_array);
		}
	//===============================================================
	function add_item($documents_id, $quantity) {
		$this->cart_items[$documents_id] += $quantity;
		}
	//===============================================================
	function set_item($documents_id, $quantity) {
		if ($quantity < 1) {
			$this->drop_item($documents_id);
			}
		else {
			$this->cart_items[$documents_id] = $quantity;
			}
		}
	//===============================================================
	function getItemNumbersList() {
		$retVal = '-100';
		if(count($this->cart_items) > 0) {
			$keys = array_keys($this->cart_items);
			$keyCount = count($keys);
			$retVal = '-100';
			for($i=0;$i<$keyCount;$i++) {
				$retVal .= ',' . $keys[$i];
				}
			}
		else {
			$retVal = '-100';
			}
		return $retVal;
		}
	//===============================================================
	function getItemTextKeyList() {
		$retVal = '\'doo dah\'';
		if(count($this->cart_items) > 0) {
			$keys = array_keys($this->cart_items);
			$keyCount = count($keys);
			$retVal = '\'doo dah\'';
			for($i=0;$i<$keyCount;$i++) {
				$retVal .= ',\'' . $keys[$i] . '\'';
				}
			}
		else {
			$retVal = '\'doo dah\'';
			}
		return $retVal;
		}
	//===============================================================
	function count_products() {
		$howMany = 0;
		$tmpArray = $this->cart_items;
		$howMany = count($tmpArray);
		unset($tmpArray);
		return $howMany;
		}
	//===============================================================
	function count_items() {
		$howMany = 0;
		$tmpArray = $this->cart_items;
		if (count($tmpArray) > 0) {
			foreach($tmpArray as $Key => $Value) {
				if ($Key != '') {
					$howMany += $tmpArray[$Key];
					}
				}
			}
		unset($tmpArray);
		$howMany=2;
		return $howMany;
		}
	//===============================================================
	function drop_item($documents_id) {
		$tmpArray = $this->cart_items;
		unset($this->cart_items);
		foreach($tmpArray as $Key => $Value) {
			if ($Key != $documents_id) {
				$this->cart_items[$Key] = $Value;
				}
			}
		unset($tmpArray);		
		}
	//===============================================================
	function setCardInfo($cardtype, $cardnum, $expires, $nameoncard) {
		if (isset($cardtype) && isset($cardnum) && isset($expires) && isset($nameoncard)) {
			$this->cardtype = $cardtype;
			$this->cardnum = $cardnum;
			$this->expires = $expires;
			$this->nameoncard = $nameoncard;
			$this->havecardinfo = true;
			}
		}
	//===============================================================
	function getCardInfo() {
		$pA = array();
		$pA['cardtype'] = $this->cardtype;
		$pA['cardnum'] = $this->cardnum;
		$pA['expires'] = $this->expires;
		$pA['nameoncard'] = $this->nameoncard;
		$pA['havecardinfo'] = $this->havecardinfo;
		return $pA;
		}
	}
?>
