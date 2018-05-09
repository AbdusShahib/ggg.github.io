<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  namespace OSC\Apps\PayPal\PayPal\Module\Payment;

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  use OSC\Apps\PayPal\PayPal\PayPal as PayPalApp;

  class EC implements \OSC\OM\Modules\PaymentInterface {
    public $code, $title, $description, $enabled, $app;

    function __construct() {
      global $PHP_SELF, $order;

      if (!Registry::exists('PayPal')) {
        Registry::set('PayPal', new PayPalApp());
      }

      $this->app = Registry::get('PayPal');
      $this->app->loadDefinitions('modules/EC/EC');

      $this->signature = 'paypal|paypal_express|' . $this->app->getVersion() . '|2.4';
      $this->api_version = $this->app->getApiVersion();

      $this->code = 'EC';
      $this->title = $this->app->getDef('module_ec_title');
      $this->public_title = $this->app->getDef('module_ec_public_title');
      $this->description = '<div align="center">' . HTML::button($this->app->getDef('module_ec_legacy_admin_app_button'), null, $this->app->link('Configure&module=EC'), null, 'btn-primary') . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_EC_SORT_ORDER') ? OSCOM_APP_PAYPAL_EC_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_EC_STATUS') && in_array(OSCOM_APP_PAYPAL_EC_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_EC_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code . '; Sandbox)';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->app->getDef('module_ec_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          if ( !$this->app->hasCredentials('EC') ) {
            $this->description .= '<div class="secWarning">' . $this->app->getDef('module_ec_error_credentials') . '</div>';

            $this->enabled = false;
          }
        } else { // Payflow
          if ( !$this->app->hasCredentials('EC', 'payflow') ) {
            $this->description .= '<div class="secWarning">' . $this->app->getDef('module_ec_error_credentials_payflow') . '</div>';

            $this->enabled = false;
          }
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( basename($PHP_SELF) == 'shopping_cart.php' ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1') ) {
          header('X-UA-Compatible: IE=edge', true);
        }
      }

// When changing the shipping address due to no shipping rates being available, head straight to the checkout confirmation page
      if ( (basename($PHP_SELF) == 'checkout_payment.php') && isset($_SESSION['appPayPalEcRightTurn']) ) {
        unset($_SESSION['appPayPalEcRightTurn']);

        if ( isset($_SESSION['payment']) && ($_SESSION['payment'] == $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code) ) {
          OSCOM::redirect('checkout_confirmation.php');
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_EC_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $this->app->db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => OSCOM_APP_PAYPAL_EC_ZONE, 'zone_country_id' => $order->delivery['country']['id']], 'zone_id');

        while ($Qcheck->fetch()) {
          if (($Qcheck->valueInt('zone_id') < 1) || ($Qcheck->valueInt('zone_id') == $order->delivery['zone_id'])) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function checkout_initialization_method() {
      global $oscTemplate;

      $string = '';

      if (OSCOM_APP_PAYPAL_GATEWAY == '1') {
        if (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0') {
          if (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '1') {
            if (OSCOM_APP_PAYPAL_EC_STATUS == '1') {
              $image_button = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image';
            } else {
              $image_button = 'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image';
            }

            $params = array('locale=' . $this->app->getDef('module_ec_button_locale'));

            if ($this->app->hasCredentials('EC')) {
              $response_array = $this->app->getApiResult('EC', 'GetPalDetails');

              if (isset($response_array['PAL'])) {
                $params[] = 'pal=' . $response_array['PAL'];
                $params[] = 'ordertotal=' . $this->app->formatCurrencyRaw($_SESSION['cart']->show_total());
              }
            }

            if (!empty($params)) {
              $image_button .= '&' . implode('&', $params);
            }
          } else {
            $image_button = $this->app->getDef('module_ec_button_url');
          }

          $button_title = HTML::outputProtected($this->app->getDef('module_ec_button_title'));

          if (OSCOM_APP_PAYPAL_EC_STATUS == '0') {
            $button_title .= ' (' . $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code . '; Sandbox)';
          }

          $string .= '<a href="' . OSCOM::link('index.php', 'order&callback&paypal&ec') . '"><img src="' . $image_button . '" border="0" alt="" title="' . $button_title . '" /></a>';
        } else {
          $oscTemplate->addBlock('<script src="https://www.paypalobjects.com/api/checkout.js" async></script>', 'footer_scripts');

          $merchant_id = (OSCOM_APP_PAYPAL_EC_STATUS === '1') ? OSCOM_APP_PAYPAL_LIVE_MERCHANT_ID : OSCOM_APP_PAYPAL_SANDBOX_MERCHANT_ID;
          if (empty($merchant_id)) $merchant_id = ' ';

          $server = (OSCOM_APP_PAYPAL_EC_STATUS === '1') ? 'production' : 'sandbox';

          $ppecset_url = OSCOM::link('index.php', 'order&callback&paypal&ec&format=json');

          switch (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_COLOR) {
            case '3':
              $button_color = 'silver';
              break;

            case '2':
              $button_color = 'blue';
              break;

            case '1':
            default:
              $button_color = 'gold';
              break;
          }

          switch (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SIZE) {
            case '3':
              $button_size = 'medium';
              break;

            case '1':
              $button_size = 'tiny';
              break;

            case '2':
            default:
              $button_size = 'small';
              break;
          }

          switch (OSCOM_APP_PAYPAL_EC_INCONTEXT_BUTTON_SHAPE) {
            case '2':
              $button_shape = 'rect';
              break;

            case '1':
            default:
              $button_shape = 'pill';
              break;
          }

          $string .= <<<EOD
<span id="ppECButton"></span>
<script>
window.paypalCheckoutReady = function () {
  paypal.checkout.setup('${merchant_id}', {
    environment: '{$server}',
    buttons: [
      {
        container: 'ppECButton',
        color: '${button_color}',
        size: '${button_size}',
        shape: '${button_shape}',
        click: function (event) {
          event.preventDefault();

          paypal.checkout.initXO();

          var action = $.getJSON('${ppecset_url}');

          action.done(function (data) {
            paypal.checkout.startFlow(data.token);
          });

          action.fail(function () {
            paypal.checkout.closeFlow();
          });
        }
      }
    ]
  });
};
</script>
EOD;
        }
      } else {
        $image_button = $this->app->getDef('module_ec_button_url');

        $button_title = HTML::outputProtected($this->app->getDef('module_ec_button_title'));

        if (OSCOM_APP_PAYPAL_EC_STATUS == '0') {
          $button_title .= ' (' . $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code . '; Sandbox)';
        }

        $string .= '<a href="' . OSCOM::link('index.php', 'order&callback&paypal&ec') . '"><img src="' . $image_button . '" border="0" alt="" title="' . $button_title . '" /></a>';
      }

      return $string;
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $messageStack, $order;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec');
      }

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        if ( !in_array($_SESSION['appPayPalEcResult']['ACK'], array('Success', 'SuccessWithWarning')) ) {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($_SESSION['appPayPalEcResult']['L_LONGMESSAGE0']));
        } elseif ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php');
        }
      } else { // Payflow
        if ($_SESSION['appPayPalEcResult']['RESULT'] != '0') {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($_SESSION['appPayPalEcResult']['OSCOM_ERROR_MESSAGE']));
        } elseif ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php');
        }
      }

      $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
    }

    function confirmation() {
      if (!isset($_SESSION['comments'])) {
        $_SESSION['comments'] = null;
      }

      $confirmation = false;

      if (empty($_SESSION['comments'])) {
        $confirmation = array('fields' => array(array('title' => $this->app->getDef('module_ec_field_comments'),
                                                      'field' => HTML::textareaField('ppecomments', '60', '5'))));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->before_process_paypal();
      } else {
        $this->before_process_payflow();
      }
    }

    function before_process_paypal() {
      global $order, $response_array;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec');
      }

      if ( in_array($_SESSION['appPayPalEcResult']['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php');
        }
      } else {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($_SESSION['appPayPalEcResult']['L_LONGMESSAGE0']));
      }

      if (empty($_SESSION['comments'])) {
        if (isset($_POST['ppecomments']) && tep_not_null($_POST['ppecomments'])) {
          $_SESSION['comments'] = HTML::sanitize($_POST['ppecomments']);

          $order->info['comments'] = $_SESSION['comments'];
        }
      }

      $params = array('TOKEN' => $_SESSION['appPayPalEcResult']['TOKEN'],
                      'PAYERID' => $_SESSION['appPayPalEcResult']['PAYERID'],
                      'PAYMENTREQUEST_0_AMT' => $this->app->formatCurrencyRaw($order->info['total']),
                      'PAYMENTREQUEST_0_CURRENCYCODE' => $order->info['currency']);

      if (is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0)) {
        $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
        $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
        $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $response_array = $this->app->getApiResult('EC', 'DoExpressCheckoutPayment', $params);

      if ( !in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( $response_array['L_ERRORCODE0'] == '10486' ) {
          if ( OSCOM_APP_PAYPAL_EC_STATUS == '1' ) {
            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          } else {
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          }

          $paypal_url .= '&token=' . $_SESSION['appPayPalEcResult']['TOKEN'];

          HTTP::redirect($paypal_url);
        }

        OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']));
      }
    }

    function before_process_payflow() {
      global $order, $response_array;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec');
      }

      if ( $_SESSION['appPayPalEcResult']['RESULT'] == '0' ) {
        if ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php');
        }
      } else {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($_SESSION['appPayPalEcResult']['OSCOM_ERROR_MESSAGE']));
      }

      if ( empty($_SESSION['comments']) ) {
        if ( isset($_POST['ppecomments']) && tep_not_null($_POST['ppecomments']) ) {
          $_SESSION['comments'] = HTML::sanitize($_POST['ppecomments']);

          $order->info['comments'] = $_SESSION['comments'];
        }
      }

      $params = array('EMAIL' => $order->customer['email_address'],
                      'TOKEN' => $_SESSION['appPayPalEcResult']['TOKEN'],
                      'PAYERID' => $_SESSION['appPayPalEcResult']['PAYERID'],
                      'AMT' => $this->app->formatCurrencyRaw($order->info['total']),
                      'CURRENCY' => $order->info['currency']);

      if ( is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0) ) {
        $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $response_array = $this->app->getApiResult('EC', 'PayflowDoExpressCheckoutPayment', $params);

      if ( $response_array['RESULT'] != '0' ) {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($response_array['OSCOM_ERROR_MESSAGE']));
      }
    }

    function after_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->after_process_paypal();
      } else {
        $this->after_process_payflow();
      }
    }

    function after_process_paypal() {
      global $response_array, $insert_id;

      $pp_result = 'Transaction ID: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_TRANSACTIONID']) . "\n" .
                   'Payer Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['PAYERSTATUS']) . "\n" .
                   'Address Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['ADDRESSSTATUS']) . "\n" .
                   'Payment Status: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PAYMENTSTATUS']) . "\n" .
                   'Payment Type: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PAYMENTTYPE']) . "\n" .
                   'Pending Reason: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PENDINGREASON']);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $pp_result);

      $this->app->db->save('orders_status_history', $sql_data_array);

      unset($_SESSION['appPayPalEcResult']);
      unset($_SESSION['appPayPalEcSecret']);
    }

    function after_process_payflow() {
      global $response_array, $insert_id;

      $pp_result = 'Transaction ID: ' . HTML::outputProtected($response_array['PNREF']) . "\n" .
                   'Gateway: Payflow' . "\n" .
                   'PayPal ID: ' . HTML::outputProtected($response_array['PPREF']) . "\n" .
                   'Payer Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['PAYERSTATUS']) . "\n" .
                   'Address Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['ADDRESSSTATUS']) . "\n" .
                   'Payment Status: ' . HTML::outputProtected($response_array['PENDINGREASON']) . "\n" .
                   'Payment Type: ' . HTML::outputProtected($response_array['PAYMENTTYPE']) . "\n" .
                   'Response: ' . HTML::outputProtected($response_array['RESPMSG']) . "\n";

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $pp_result);

      $this->app->db->save('orders_status_history', $sql_data_array);

      unset($_SESSION['appPayPalEcResult']);
      unset($_SESSION['appPayPalEcSecret']);

// Manually call PayflowInquiry to retrieve more details about the transaction and to allow admin post-transaction actions
      $response = $this->app->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $response_array['PNREF']));

      if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
        $result = 'Transaction ID: ' . HTML::outputProtected($response['ORIGPNREF']) . "\n" .
                  'Gateway: Payflow' . "\n";

        $pending_reason = $response['TRANSSTATE'];
        $payment_status = null;

        switch ( $response['TRANSSTATE'] ) {
          case '3':
            $pending_reason = 'authorization';
            $payment_status = 'Pending';
            break;

          case '4':
            $pending_reason = 'other';
            $payment_status = 'In-Progress';
            break;

          case '6':
            $pending_reason = 'scheduled';
            $payment_status = 'Pending';
            break;

          case '8':
          case '9':
            $pending_reason = 'None';
            $payment_status = 'Completed';
            break;
        }

        if ( isset($payment_status) ) {
          $result .= 'Payment Status: ' . HTML::outputProtected($payment_status) . "\n";
        }

        $result .= 'Pending Reason: ' . HTML::outputProtected($pending_reason) . "\n";

        switch ( $response['AVSADDR'] ) {
          case 'Y':
            $result .= 'AVS Address: Match' . "\n";
            break;

          case 'N':
            $result .= 'AVS Address: No Match' . "\n";
            break;
        }

        switch ( $response['AVSZIP'] ) {
          case 'Y':
            $result .= 'AVS ZIP: Match' . "\n";
            break;

          case 'N':
            $result .= 'AVS ZIP: No Match' . "\n";
            break;
        }

        switch ( $response['IAVS'] ) {
          case 'Y':
            $result .= 'IAVS: International' . "\n";
            break;

          case 'N':
            $result .= 'IAVS: USA' . "\n";
            break;
        }

        switch ( $response['CVV2MATCH'] ) {
          case 'Y':
            $result .= 'CVV2: Match' . "\n";
            break;

          case 'N':
            $result .= 'CVV2: No Match' . "\n";
            break;
        }

        $sql_data_array = array('orders_id' => $insert_id,
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        $this->app->db->save('orders_status_history', $sql_data_array);
      }
    }

    function get_error() {
      return false;
    }

    function check() {
      return defined('OSCOM_APP_PAYPAL_EC_STATUS') && (trim(OSCOM_APP_PAYPAL_EC_STATUS) != '');
    }

    function install() {
      $this->app->redirect('Configure&Install&module=EC');
    }

    function remove() {
      $this->app->redirect('Configure&Uninstall&module=EC');
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_EC_SORT_ORDER');
    }

    function getProductType($id, $attributes) {
      foreach ( $attributes as $a ) {
        $Qcheck = $this->app->db->prepare('select pad.products_attributes_id from :table_products_attributes pa, :table_products_attributes_download pad where pa.products_id = :products_id and pa.options_values_id = :options_values_id and pa.products_attributes_id = pad.products_attributes_id limit 1');
        $Qcheck->bindInt(':products_id', $id);
        $Qcheck->bindInt(':options_values_id', $a['value_id']);
        $Qcheck->execute();

        if ($Qcheck->fetch() !== false) {
          return 'Digital';
        }
      }

      return 'Physical';
    }
  }
?>
