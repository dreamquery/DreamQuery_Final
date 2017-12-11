<?php

class beanstream extends paymentHandler {

  public $gateway_name;
  public $gateway_url;
  public $gateway;

  // Response and transaction type globals for Beanstream..See api docs..
  public $responseType = 'R';
  public $trnType = 'P';

  // Payment server url..
  public function paymentServer() {
    return ($this->settings->gatewayMode == 'live' ? $this->modules[$this->gateway]['live'] : $this->modules[$this->gateway]['sandbox']);
  }

  // Validate gateway payment..
  public function validateResponse($params, $order) {
    // Log incoming vars..
    $this->logGateWayParams($_POST, $order->id);
    // Hash comparison..
    $hash = 'merchant_id=' . $params['merchant-id'] . '&responseType=' . $this->responseType . '&trnType=' . $this->trnType . '&trnOrderNumber=' . $_POST['trnOrderNumber'] . '&trnAmount=' . $_POST['trnAmount'] . $params['hash-value'];
    $this->writeLog($order->id, 'Create SHA1 Hash Digest from the following string:' . mc_defineNewline() . $hash);
    $hash  = sha1($hash);
    $hash2 = (isset($_POST['ref2']) ? $_POST['ref2'] : '');
    $this->writeLog($order->id, 'Hash check comparison to validate. Must match:' . mc_defineNewline() . 'Received: ' . strtoupper($hash2) . mc_defineNewline() . 'Calculated: ' . strtoupper($hash));
    return (strtoupper($hash) == strtoupper($hash2) ? 'ok' : 'err');
  }

  // Convert gateway post options to global vars handled by callback..
  // Add other options if required by handler..
  public function gatewayPostFields() {
    $gateway = array(
      'trans-id' => (isset($_POST['trnId']) ? $_POST['trnId'] : ''),
      'amount' => (isset($_POST['trnAmount']) ? @number_format($_POST['trnAmount'], 2, '.', '') : ''),
      'refund-amount' => '',
      'currency' => $this->settings->baseCurrency,
      'code-id' => (isset($_POST['ref1']) ? $_POST['ref1'] : ''),
      'pay-status' => (isset($_POST['trnApproved']) ? $_POST['trnApproved'] : '0'),
      'message' => (isset($_POST['avsId']) ? $this->responseMessage($_POST['avsId']) : ''),
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
    $country  = mc_getShippingCountry($order->bill_9, true);
    $country2 = mc_getShippingCountry($order->shipSetCountry, true);
    $fields   = array(
      'merchant_id' => $params['merchant-id'],
      'responseType' => $this->responseType,
      'trnType' => $this->trnType,
      'trnOrderNumber' => $id,
      'trnAmount' => $order->grandTotal,
      'hashValue' => sha1('merchant_id=' . $params['merchant-id'] . '&responseType=' . $this->responseType . '&trnType=' . $this->trnType . '&trnOrderNumber=' . $id . '&trnAmount=' . $order->grandTotal . $params['hash-value']),
      'ordName' => $this->stripInvalidChars($order->bill_1),
      'ordAddress1' => $this->stripInvalidChars($order->bill_3),
      'ordAddress2' => $this->stripInvalidChars($order->bill_4),
      'ordCity' => $this->stripInvalidChars($order->bill_5),
      'ordProvince' => (in_array($order->bill_9, array(
        184,
        31
      )) ? $this->stripInvalidChars($order->bill_6) : '--'),
      'ordCountry' => $country->cISO_2,
      'ordPostalCode' => $this->stripInvalidChars($order->bill_7),
      'ordPhoneNumber' => $this->stripInvalidChars($order->bill_8),
      'ordEmailAddress' => $this->stripInvalidChars($order->bill_2),
      'errorPage' => $url . 'checkout/beanstream.php',
      'approvedPage' => $url . 'checkout/redirects/beanstream-app.php',
      'declinedPage' => $url . 'checkout/redirects/beanstream-dec.php',
      'shipEmailAddress' => $this->stripInvalidChars($order->ship_2),
      'shipPhoneNumber' => $this->stripInvalidChars($order->ship_8),
      'shipAddress1' => $this->stripInvalidChars($order->ship_3),
      'shipAddress2' => $this->stripInvalidChars($order->ship_4),
      'shipCity' => $this->stripInvalidChars($order->ship_5),
      'shipProvince' => (in_array($order->shipSetCountry, array(
        184,
        31
      )) ? $this->stripInvalidChars($order->ship_6) : '--'),
      'shipPostalCode' => $this->stripInvalidChars($order->ship_7),
      'shipCountry' => $country2->cISO_2,
      'trnLanguage' => $params['language'],
      'ref1' => $buyCode . '-' . $id . '-mswcart',
      'ref2' => sha1('merchant_id=' . $params['merchant-id'] . '&responseType=' . $this->responseType . '&trnType=' . $this->trnType . '&trnOrderNumber=' . $id . '&trnAmount=' . $order->grandTotal . $params['hash-value'])
    );
    return array(
      'redirect',
      beanstream::paymentServer() . '?' . http_build_query($fields, '', '&')
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

  // Response messages..
  public function responseMessage($code) {
    $a = array(
      '0' => 'Address Verification not performed for this transaction.',
      '5' => 'Invalid AVS Response.',
      '9' => 'Address Verification Data contains edit error.',
      'A' => 'Street address matches, Postal/ZIP does not match.',
      'B' => 'Street address matches, Postal/ZIP not verified.',
      'C' => 'Street address and Postal/ZIP not verified.',
      'D' => 'Street address and Postal/ZIP match.',
      'E' => 'Transaction ineligible.',
      'G' => 'Non AVS participant. Information not verified.',
      'I' => 'Address information not verified for international transaction.',
      'M' => 'Street address and Postal/ZIP match.',
      'N' => 'Street address and Postal/ZIP do not match.',
      'P' => 'Postal/ZIP matches. Street address not verified.',
      'R' => 'System unavailable or timeout.',
      'S' => 'AVS not supported at this time.',
      'U' => 'Address information is unavailable.',
      'W' => 'Postal/ZIP matches, street address does not match.',
      'X' => 'Street address and Postal/ZIP match.',
      'Y' => 'Street address and Postal/ZIP match.',
      'Z' => 'Postal/ZIP matches, street address does not match.'
    );
    return (isset($a[$code]) ? $a[$code] : 'Unknown');
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

}

?>