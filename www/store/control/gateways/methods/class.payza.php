<?php

class payza extends paymentHandler {

  public $gateway_name;
  public $gateway_url;
  public $gateway;

  // Payment server url..
  public function paymentServer() {
    return ($this->settings->gatewayMode == 'live' ? $this->modules[$this->gateway]['live'] : $this->modules[$this->gateway]['sandbox']);
  }

  public function validateResponse($params, $order) {
    // Log incoming vars..
    $this->logGateWayParams($_POST, $order->id);
    return (($_POST['ap_merchant'] == $params['email']) && ($_POST['ap_securitycode'] == $params['ipncode']) ? 'ok' : 'err');
  }

  // Convert gateway post options to global vars handled by callback..
  // Add other options if required by handler..
  public function gatewayPostFields() {
    // Url decode incoming data..
    $_POST   = array_map('urldecode', $_POST);
    $gateway = array(
      'trans-id' => (isset($_POST['ap_referencenumber']) ? $_POST['ap_referencenumber'] : ''),
      'amount' => (isset($_POST['ap_amount']) ? number_format($_POST['ap_amount'], 2, '.', '') : ''),
      'refund-amount' => (isset($_POST['ap_amount']) ? number_format($_POST['ap_amount'], 2, '.', '') : ''),
      'currency' => (isset($_POST['ap_currency']) ? $_POST['ap_currency'] : ''),
      'code-id' => (isset($_POST['apc_1']) ? $_POST['apc_1'] : ''),
      'pay-status' => (isset($_POST['ap_status']) ? $_POST['ap_status'] : '')
    );
    return $gateway;
  }

  // Assigns fields array..
  public function gatewayFields($ssl, $buyCode, $id, $itemName) {
    $url    = ($ssl == 'yes' ? str_replace('http://', 'https://', $this->settings->ifolder) . '/' : $this->settings->ifolder . '/');
    $order  = $this->getOrderInfo($buyCode, $id);
    $params = $this->paymentParams($this->gateway);
    $fields = array(
      'ap_merchant' => $params['email'],
      'ap_itemname' => $this->stripInvalidChars(str_replace('{store}', $this->settings->website, $itemName)),
      'ap_quantity' => '1',
      'ap_notificationtype' => 'New',
      'ap_transactionstate' => 'Completed',
      'ap_cancelurl' => $url . 'index.php?p=cancel&o=' . $id . '-' . $buyCode,
      'ap_returnurl' => $url . 'index.php?gw=' . $id . '-' . $buyCode,
      'ap_amount' => $order->grandTotal,
      'ap_purchasetype' => 'item',
      'ap_test' => ($this->settings->gatewayMode == 'live' ? '1' : '0'),
      'ap_currency' => $this->settings->baseCurrency,
      'apc_1' => $buyCode . '-' . $id
    );
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

}

?>