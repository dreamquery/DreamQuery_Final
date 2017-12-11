<?php

class iris extends paymentHandler {

  public $gateway_name;
  public $gateway_url;
  public $gateway;

  // Payment server url..
  public function paymentServer() {
    return ($this->settings->gatewayMode == 'live' ? $this->modules[$this->gateway]['live'] : $this->modules[$this->gateway]['sandbox']);
  }

  // Validate gateway payment..
  public function validateResponse($params, $order) {
    // Log incoming vars..
    $this->logGateWayParams($_POST, $order->id);
    $merchantid = (isset($_POST['MERCHANT_ID']) ? $_POST['MERCHANT_ID'] : '');
    $timestamp  = (isset($_POST['TIMESTAMP']) ? $_POST['TIMESTAMP'] : '');
    $result     = (isset($_POST['RESULT']) ? $_POST['RESULT'] : '');
    $orderid    = (isset($_POST['ORDER_ID']) ? $_POST['ORDER_ID'] : '');
    $message    = (isset($_POST['MESSAGE']) ? $_POST['MESSAGE'] : '');
    $authcode   = (isset($_POST['AUTHCODE']) ? $_POST['AUTHCODE'] : '');
    $pasref     = (isset($_POST['PASREF']) ? $_POST['PASREF'] : '');
    $realexmd5  = (isset($_POST['MD5HASH']) ? $_POST['MD5HASH'] : '');
    $tmp        = "$timestamp.$merchantid.$orderid.$result.$message.$pasref.$authcode";
    $this->writeLog($order->id, 'Creating callback MD5 Hash Digest from the following string:' . mc_defineNewline() . $tmp);
    $hashTemp = md5($tmp);
    $key      = $params['secret-key'];
    $keyTemp  = "$hashTemp.$key";
    $this->writeLog($order->id, 'Append key and create MD5 Hash Digest from the following string:' . mc_defineNewline() . $keyTemp);
    $received = md5($keyTemp);
    // Validate..
    $this->writeLog($order->id, 'MD5 hash check to validate. Must match:' . mc_defineNewline() . 'Callback: ' . strtoupper($realexmd5) . mc_defineNewline() . 'Calculated: ' . strtoupper($received));
    // Check result..
    $this->writeLog($order->id, 'Checking Result: ' . $this->gatewayCodes($result));
    return (strtoupper($received) == strtoupper($realexmd5) && $result == '00' ? 'ok' : 'err');
  }

  // Convert gateway post options to global vars handled by callback..
  // Add other options if required by handler..
  public function gatewayPostFields() {
    $order   = $this->getOrderInfo('none', (isset($_POST['ORDER_ID']) ? (int) $_POST['ORDER_ID'] : '0'));
    $gateway = array(
      'trans-id' => (isset($_POST['AUTHCODE']) ? $_POST['AUTHCODE'] : ''),
      'amount' => (isset($order->id) ? $order->grandTotal : '0-0'),
      'refund-amount' => '',
      'currency' => $this->settings->baseCurrency,
      'code-id' => (isset($order->id) ? $order->buyCode . '-' . $order->id : '0-0'),
      'pay-status' => (isset($order->id) ? 'OK' : ''),
      'pending-reason' => '',
      'inv-status' => '',
      'fraud-status' => ''
    );
    return $gateway;
  }

  // Assigns fields array..
  public function gatewayFields($ssl, $buyCode, $id, $itemName) {
    $url      = ($ssl == 'yes' ? str_replace('http://', 'https://', $this->settings->ifolder) . '/' : $this->settings->ifolder . '/');
    $order    = $this->getOrderInfo($buyCode, $id);
    $params   = $this->paymentParams($this->gateway);
    $ts       = strftime("%Y%m%d%H%M%S");
    $country  = mc_getShippingCountry($order->bill_9, true);
    $country2 = mc_getShippingCountry($order->shipSetCountry, true);
    $fields   = array(
      'MERCHANT_ID' => $params['merchant-id'],
      'ORDER_ID' => $id,
      'ACCOUNT' => ($params['sub-account'] ? $params['sub-account'] : 'internet'),
      'CURRENCY' => $this->settings->baseCurrency,
      'AMOUNT' => str_replace('.', '', $order->grandTotal),
      'TIMESTAMP' => $ts,
      'AUTO_SETTLE_FLAG' => ($this->settings->pendingAsComplete == 'yes' ? '0' : '1'),
      'COMMENT1' => $this->stripInvalidChars(str_replace('{store}', $this->settings->website, $itemName) . ' (Maian Cart)'),
      'VAR_REF' => $this->stripInvalidChars($order->bill_1),
      'CUSTOM' => $buyCode . '-' . $id . '-mswcart',
      'BILLING_CODE' => preg_replace('/[^0-9]/', '', $order->bill_7) . '|' . preg_replace('/[^0-9]/', '', $order->bill_3),
      'BILLING_CO' => $country->cISO_2,
      'SHIPPING_CODE' => preg_replace('/[^0-9]/', '', $order->ship_7) . '|' . preg_replace('/[^0-9]/', '', $order->ship_3),
      'SHIPPING_CO' => $country2->cISO_2,
      'MERCHANT_RESPONSE_URL' => $url . 'checkout/iris.php'
    );
    // Calculate hash..
    // Concatenation MUST be quoted or else Global Iris will fail..
    $merchant = $params['merchant-id'];
    $key      = $params['secret-key'];
    $amt      = str_replace('.', '', $order->grandTotal);
    $cur      = $this->settings->baseCurrency;
    $tmp      = "$ts.$merchant.$id.$amt.$cur";
    $this->writeLog($id, 'Create MD5 Hash Digest from the following string:' . mc_defineNewline() . $tmp);
    $hashTemp = md5($tmp);
    $keyTemp  = "$hashTemp.$key";
    $this->writeLog($id, 'Append key and create MD5 Hash Digest from the following string:' . mc_defineNewline() . $keyTemp);
    $fields['MD5HASH'] = md5($keyTemp);
    return array(
      'form',
      $fields
    );
  }

  // Mail templates assigned to this method..
  public function mailTemplates() {
    $t = array(
      'completed' => 'order-completed.txt',
      'completed-wm' => 'order-completed-webmaster.txt',
      'completed-dl' => 'order-completed-dl.txt',
      'completed-wm-dl' => 'order-completed-dl-webmaster.txt',
      'pending' => 'order-pending.txt',
      'pending-wm' => 'order-pending-webmaster.txt',
      'refunded' => 'order-refunded.txt',
      'cancelled' => 'order-cancelled.txt',
      'completed-wish' => 'order-completed-wish.txt',
      'completed-wish-dl' => 'order-completed-wish-dl.txt',
      'completed-wish-recipient' => 'order-completed-wish-recipient.txt',
      'completed-wish-recipient-dl' => 'order-completed-wish-recipient-dl.txt'
    );
    return $t;
  }

  // Set preferred status..
  public function setOrderStatus($code) {
    $d = array(
      'completed' => 'shipping',
      'download' => 'completed',
      'virtual' => 'completed',
      'free' => 'completed',
      'pending' => 'pending',
      'cancelled' => 'cancelled',
      'refunded' => 'refund'
    );
    $s = ($this->modules[$this->gateway]['statuses'] ? unserialize($this->modules[$this->gateway]['statuses']) : '');
    return (isset($s[$code]) ? $s[$code] : $d[$code]);
  }

  // Error codes..
  public function gatewayCodes($result) {
    switch(substr($result, 0, 1)) {
      case 2:
        $result = '2xx';
        break;
      case 3:
        $result = '3xx';
        break;
      case 5:
        $result = '5xx';
        break;
    }
    $codes = array(
      '00' => 'Successful',
      '101' => 'Declined by Bank',
      '102' => 'Referral by Bank (treat as decline in automated system such as internet)',
      '103' => 'Card reported lost or stolen',
      '2xx' => 'Error with bank systems',
      '3xx' => 'Error with Realex Payments systems',
      '5xx' => 'Incorrect XML message formation or content',
      '666' => 'Client deactivated.'
    );
    return (isset($codes[$result]) ? $codes[$result] : 'N/A');
  }

}

?>