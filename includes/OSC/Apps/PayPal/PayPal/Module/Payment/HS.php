<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  namespace OSC\Apps\PayPal\PayPal\Module\Payment;

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  use OSC\Apps\PayPal\PayPal\PayPal as PayPalApp;

  class HS implements \OSC\OM\Modules\PaymentInterface {
    public $code, $title, $description, $enabled, $app;

    function __construct() {
      global $order;

      if (!Registry::exists('PayPal')) {
        Registry::set('PayPal', new PayPalApp());
      }

      $this->app = Registry::get('PayPal');
      $this->app->loadDefinitions('modules/HS/HS');

      $this->signature = 'paypal|paypal_pro_hs|' . $this->app->getVersion() . '|2.4';
      $this->api_version = $this->app->getApiVersion();

      $this->code = 'HS';
      $this->title = $this->app->getDef('module_hs_title');
      $this->public_title = $this->app->getDef('module_hs_public_title');
      $this->description = '<div align="center">' . HTML::button($this->app->getDef('module_hs_legacy_admin_app_button'), null, $this->app->link('Configure&module=HS'), null, 'btn-primary') . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_HS_SORT_ORDER') ? OSCOM_APP_PAYPAL_HS_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_HS_STATUS') && in_array(OSCOM_APP_PAYPAL_HS_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_HS_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_HS_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code . '; Sandbox)';
        }

        if ( OSCOM_APP_PAYPAL_HS_STATUS == '1' ) {
          $this->api_url = 'https://api-3t.paypal.com/nvp';
        } else {
          $this->api_url = 'https://api-3t.sandbox.paypal.com/nvp';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->app->getDef('module_hs_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && !$this->app->hasCredentials('HS') ) { // PayPal
          $this->description .= '<div class="secWarning">' . $this->app->getDef('module_hs_error_credentials') . '</div>';

          $this->enabled = false;
        } elseif ( OSCOM_APP_PAYPAL_GATEWAY == '0' ) { // Payflow
          $this->description .= '<div class="secWarning">' . $this->app->getDef('module_hs_error_payflow') . '</div>';

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_HS_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $this->app->db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => OSCOM_APP_PAYPAL_HS_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');

        while ($Qcheck->fetch()) {
          if (($Qcheck->valueInt('zone_id') < 1) || ($Qcheck->valueInt('zone_id') == $order->billing['zone_id'])) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      if (isset($_SESSION['cart_PayPal_Pro_HS_ID'])) {
        $order_id = substr($_SESSION['cart_PayPal_Pro_HS_ID'], strpos($_SESSION['cart_PayPal_Pro_HS_ID'], '-')+1);

        $Qcheck = $this->app->db->get('orders_status_history', 'orders_id', ['orders_id' => $order_id], null, 1);

        if ($Qcheck->fetch() === false) {
          $this->app->db->delete('orders', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_total', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products_attributes', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products_download', ['orders_id' => $order_id]);

          unset($_SESSION['cart_PayPal_Pro_HS_ID']);
        }
      }

      return array('id' => $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      if (empty($_SESSION['cart']->cartID)) {
        $_SESSION['cartID'] = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
      }
    }

    function confirmation() {
      global $order, $order_total_modules;

      $_SESSION['pphs_result'] = array();

      if (isset($_SESSION['cartID'])) {
        $insert_order = false;

        if (isset($_SESSION['cart_PayPal_Pro_HS_ID'])) {
          $order_id = substr($_SESSION['cart_PayPal_Pro_HS_ID'], strpos($_SESSION['cart_PayPal_Pro_HS_ID'], '-')+1);

          $Qorder = $this->app->db->get('orders', 'currency', ['orders_id' => $order_id]);

          if ( ($Qorder->value('currency') != $order->info['currency']) || ($_SESSION['cartID'] != substr($_SESSION['cart_PayPal_Pro_HS_ID'], 0, strlen($_SESSION['cartID']))) ) {
            $Qcheck = $this->app->db->get('orders_status_history', 'orders_id', ['orders_id' => $order_id], null, 1);

            if ($Qcheck->fetch() === false) {
              $this->app->db->delete('orders', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_total', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products_attributes', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products_download', ['orders_id' => $order_id]);
            }

            $insert_order = true;
          }
        } else {
          $insert_order = true;
        }

        if ($insert_order == true) {
          $order_totals = array();
          if (is_array($order_total_modules->modules)) {
            foreach ($order_total_modules->modules as $value) {
              $class = substr($value, 0, strrpos($value, '.'));
              if ($GLOBALS[$class]->enabled) {
                for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                  if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                    $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                            'title' => $GLOBALS[$class]->output[$i]['title'],
                                            'text' => $GLOBALS[$class]->output[$i]['text'],
                                            'value' => $GLOBALS[$class]->output[$i]['value'],
                                            'sort_order' => $GLOBALS[$class]->sort_order);
                  }
                }
              }
            }
          }

          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                  'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                  'customers_company' => $order->customer['company'],
                                  'customers_street_address' => $order->customer['street_address'],
                                  'customers_suburb' => $order->customer['suburb'],
                                  'customers_city' => $order->customer['city'],
                                  'customers_postcode' => $order->customer['postcode'],
                                  'customers_state' => $order->customer['state'],
                                  'customers_country' => $order->customer['country']['title'],
                                  'customers_telephone' => $order->customer['telephone'],
                                  'customers_email_address' => $order->customer['email_address'],
                                  'customers_address_format_id' => $order->customer['format_id'],
                                  'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                  'delivery_company' => $order->delivery['company'],
                                  'delivery_street_address' => $order->delivery['street_address'],
                                  'delivery_suburb' => $order->delivery['suburb'],
                                  'delivery_city' => $order->delivery['city'],
                                  'delivery_postcode' => $order->delivery['postcode'],
                                  'delivery_state' => $order->delivery['state'],
                                  'delivery_country' => $order->delivery['country']['title'],
                                  'delivery_address_format_id' => $order->delivery['format_id'],
                                  'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                  'billing_company' => $order->billing['company'],
                                  'billing_street_address' => $order->billing['street_address'],
                                  'billing_suburb' => $order->billing['suburb'],
                                  'billing_city' => $order->billing['city'],
                                  'billing_postcode' => $order->billing['postcode'],
                                  'billing_state' => $order->billing['state'],
                                  'billing_country' => $order->billing['country']['title'],
                                  'billing_address_format_id' => $order->billing['format_id'],
                                  'payment_method' => $order->info['payment_method'],
                                  'cc_type' => $order->info['cc_type'],
                                  'cc_owner' => $order->info['cc_owner'],
                                  'cc_number' => $order->info['cc_number'],
                                  'cc_expires' => $order->info['cc_expires'],
                                  'date_purchased' => 'now()',
                                  'orders_status' => $order->info['order_status'],
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);

          $this->app->db->save('orders', $sql_data_array);

          $insert_id = $this->app->db->lastInsertId();

          for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'title' => $order_totals[$i]['title'],
                                    'text' => $order_totals[$i]['text'],
                                    'value' => $order_totals[$i]['value'],
                                    'class' => $order_totals[$i]['code'],
                                    'sort_order' => $order_totals[$i]['sort_order']);

            $this->app->db->save('orders_total', $sql_data_array);
          }

          for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'products_id' => tep_get_prid($order->products[$i]['id']),
                                    'products_model' => $order->products[$i]['model'],
                                    'products_name' => $order->products[$i]['name'],
                                    'products_price' => $order->products[$i]['price'],
                                    'final_price' => $order->products[$i]['final_price'],
                                    'products_tax' => $order->products[$i]['tax'],
                                    'products_quantity' => $order->products[$i]['qty']);

            $this->app->db->save('orders_products', $sql_data_array);

            $order_products_id = $this->app->db->lastInsertId();

            $attributes_exist = '0';
            if (isset($order->products[$i]['attributes'])) {
              $attributes_exist = '1';
              for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                if (DOWNLOAD_ENABLED == 'true') {
                  $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                       from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                       left join :table_products_attributes_download pad on pa.products_attributes_id = pad.products_attributes_id
                                       where pa.products_id = :products_id
                                       and pa.options_id = :options_id
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = :options_values_id
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = :language_id
                                       and popt.language_id = poval.language_id';
                } else {
                  $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                       from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                       where pa.products_id = :products_id
                                       and pa.options_id = :options_id
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = :options_values_id
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = :language_id
                                       and popt.language_id = poval.language_id';
                }

                $Qattributes = $this->app->db->prepare($attributes_query);
                $Qattributes->bindInt(':products_id', $order->products[$i]['id']);
                $Qattributes->bindInt(':options_id', $order->products[$i]['attributes'][$j]['option_id']);
                $Qattributes->bindInt(':options_values_id', $order->products[$i]['attributes'][$j]['value_id']);
                $Qattributes->bindInt(':language_id', $this->app->lang->getId());
                $Qattributes->execute();

                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'products_options' => $Qattributes->value('products_options_name'),
                                        'products_options_values' => $Qattributes->value('products_options_values_name'),
                                        'options_values_price' => $Qattributes->value('options_values_price'),
                                        'price_prefix' => $Qattributes->value('price_prefix'));

                $this->app->db->save('orders_products_attributes', $sql_data_array);

                if ((DOWNLOAD_ENABLED == 'true') && $Qattributes->hasValue('products_attributes_filename') && !empty($Qattributes->value('products_attributes_filename'))) {
                  $sql_data_array = array('orders_id' => $insert_id,
                                          'orders_products_id' => $order_products_id,
                                          'orders_products_filename' => $Qattributes->value('products_attributes_filename'),
                                          'download_maxdays' => $Qattributes->value('products_attributes_maxdays'),
                                          'download_count' => $Qattributes->value('products_attributes_maxcount'));

                  $this->app->db->save('orders_products_download', $sql_data_array);
                }
              }
            }
          }

          $_SESSION['cart_PayPal_Pro_HS_ID'] = $_SESSION['cartID'] . '-' . $insert_id;
        }

        $order_id = substr($_SESSION['cart_PayPal_Pro_HS_ID'], strpos($_SESSION['cart_PayPal_Pro_HS_ID'], '-')+1);

        $params = array('buyer_email' => $order->customer['email_address'],
                        'cancel_return' => OSCOM::link('checkout_payment.php'),
                        'currency_code' => $_SESSION['currency'],
                        'invoice' => $order_id,
                        'custom' => $_SESSION['customer_id'],
                        'paymentaction' => OSCOM_APP_PAYPAL_HS_TRANSACTION_METHOD == '1' ? 'sale' : 'authorization',
                        'return' => OSCOM::link('checkout_process.php'),
                        'notify_url' => OSCOM::link('index.php', 'order&ipn&paypal&hs', false, false),
                        'shipping' => $this->app->formatCurrencyRaw($order->info['shipping_cost']),
                        'tax' => $this->app->formatCurrencyRaw($order->info['tax']),
                        'subtotal' => $this->app->formatCurrencyRaw($order->info['total'] - $order->info['shipping_cost'] - $order->info['tax']),
                        'billing_first_name' => $order->billing['firstname'],
                        'billing_last_name' => $order->billing['lastname'],
                        'billing_address1' => $order->billing['street_address'],
                        'billing_city' => $order->billing['city'],
                        'billing_state' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'billing_zip' => $order->billing['postcode'],
                        'billing_country' => $order->billing['country']['iso_code_2'],
                        'night_phone_b' => $order->customer['telephone'],
                        'template' => 'templateD',
                        'item_name' => STORE_NAME,
                        'showBillingAddress' => 'false',
                        'showShippingAddress' => 'false',
                        'showHostedThankyouPage' => 'false');

        if ( is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0) ) {
          $params['address_override'] = 'true';
          $params['first_name'] = $order->delivery['firstname'];
          $params['last_name'] = $order->delivery['lastname'];
          $params['address1'] = $order->delivery['street_address'];
          $params['city'] = $order->delivery['city'];
          $params['state'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['zip'] = $order->delivery['postcode'];
          $params['country'] = $order->delivery['country']['iso_code_2'];
        }

        $return_link_title = $this->app->getDef('module_hs_button_return_to_store', [
          'storename' => STORE_NAME
        ]);

        if ( strlen($return_link_title) <= 60 ) {
          $params['cbt'] = $return_link_title;
        }

        $_SESSION['pphs_result'] = $this->app->getApiResult('APP', 'BMCreateButton', $params, (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');
      }

      $_SESSION['pphs_key'] = Hash::getRandomString(16);

      $iframe_url = OSCOM::link('index.php', 'order&paypal&checkout&hs&key=' . $_SESSION['pphs_key']);
      $form_url = OSCOM::link('checkout_payment.php', 'payment_error=paypal_pro_hs');

      $output = <<<EOD
<iframe src="{$iframe_url}" width="570px" height="540px" frameBorder="0" scrolling="no"></iframe>
<script>
$(function() {
  $('form[name="checkout_confirmation"] input[type="submit"], form[name="checkout_confirmation"] input[type="image"], form[name="checkout_confirmation"] button[type="submit"]').hide();
  $('form[name="checkout_confirmation"]').attr('action', '{$form_url}');
});
</script>
EOD;

      $confirmation = array('title' => $output);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $order, $order_totals, $currencies;

      $result = false;

      if ( isset($_GET['tx']) && !empty($_GET['tx']) ) { // direct payment (eg, credit card)
        $result = $this->app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $_GET['tx']), (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');
      } elseif ( isset($_POST['txn_id']) && !empty($_POST['txn_id']) ) { // paypal payment
        $result = $this->app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $_POST['txn_id']), (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');
      }

      if ( !in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($result['L_LONGMESSAGE0']));
      }

      $order_id = substr($_SESSION['cart_PayPal_Pro_HS_ID'], strpos($_SESSION['cart_PayPal_Pro_HS_ID'], '-')+1);

      $seller_accounts = array($this->app->getCredentials('HS', 'email'));

      if ( tep_not_null($this->app->getCredentials('HS', 'email_primary')) ) {
        $seller_accounts[] = $this->app->getCredentials('HS', 'email_primary');
      }

      if ( !isset($result['RECEIVERBUSINESS']) || !in_array($result['RECEIVERBUSINESS'], $seller_accounts) || ($result['INVNUM'] != $order_id) || ($result['CUSTOM'] != $_SESSION['customer_id']) ) {
        OSCOM::redirect('shopping_cart.php');
      }

      $_SESSION['pphs_result'] = $result;

      $Qorder = $this->app->db->get('orders', 'orders_status', ['orders_id' => $order_id, 'customers_id' => $_SESSION['customer_id']]);

      if (($Qorder->fetch() === false) || ($order_id != $_SESSION['pphs_result']['INVNUM']) || ($_SESSION['customer_id'] != $_SESSION['pphs_result']['CUSTOM'])) {
        OSCOM::redirect('shopping_cart.php');
      }

      $this->verifyTransaction();

      $new_order_status = DEFAULT_ORDERS_STATUS_ID;

      if ( $Qorder->valueInt('orders_status') != OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID ) {
        $new_order_status = $Qorder->valueInt('orders_status');
      }

      if ( (OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID > 0) && ($Qorder->valueInt('orders_status') == OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID) ) {
        $new_order_status = OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID;
      }

      $this->app->db->save('orders', ['orders_status' => $new_order_status, 'last_modified' => 'now()'], ['orders_id' => $order_id]);

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (int)$new_order_status,
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      $this->app->db->save('orders_status_history', $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_sql = 'select p.products_quantity, pad.products_attributes_filename
                                from :table_products p
                                left join :table_products_attributes pa
                                on p.products_id = pa.products_id
                                left join :table_products_attributes_download pad
                                on pa.products_attributes_id = pad.products_attributes_id
                                where p.products_id = :products_id';

// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
            if (is_array($products_attributes)) {
              $stock_query_sql .= ' and pa.options_id = :options_id and pa.options_values_id = :options_values_id';
            }

            $Qstock = $this->app->db->prepare($stock_query_sql);
            $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));

            if (is_array($products_attributes)) {
              $Qstock->bindInt(':options_id', $products_attributes[0]['option_id']);
              $Qstock->bindInt(':options_values_id', $products_attributes[0]['value_id']);
            }

            $Qstock->execute();
          } else {
            $Qstock = $this->app->db->prepare('select products_quantity from :table_products where products_id = :products_id');
            $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
            $Qstock->execute();
          }

          if ($Qstock->fetch() !== false) {
// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || !empty($Qstock->value('products_attributes_filename'))) {
              $stock_left = $Qstock->valueInt('products_quantity') - $order->products[$i]['qty'];
            } else {
              $stock_left = $Qstock->valueInt('products_quantity');
            }

            if ($stock_left != $Qstock->valueInt('products_quantity')) {
              $this->app->db->save('products', ['products_quantity' => $stock_left], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
            }

            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
             $this->app->db->save('products', ['products_status' => '0'], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
            }
          }
        }

// Update products_ordered (for bestsellers list)
        $Qupdate = $this->app->db->prepare('update :table_products set products_ordered = products_ordered + :products_ordered where products_id = :products_id');
        $Qupdate->bindInt(':products_ordered', $order->products[$i]['qty']);
        $Qupdate->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
        $Qupdate->execute();

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                   from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                   left join :table_products_attributes_download pad on pa.products_attributes_id = pad.products_attributes_id
                                   where pa.products_id = :products_id
                                   and pa.options_id = :options_id
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = :options_values_id
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = :language_id
                                   and popt.language_id = poval.language_id';
            } else {
              $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                   from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                   where pa.products_id = :products_id
                                   and pa.options_id = :options_id
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = :options_values_id
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = :language_id
                                   and popt.language_id = poval.language_id';
            }

            $Qattributes = $this->app->db->prepare($attributes_query);
            $Qattributes->bindInt(':products_id', $order->products[$i]['id']);
            $Qattributes->bindInt(':options_id', $order->products[$i]['attributes'][$j]['option_id']);
            $Qattributes->bindInt(':options_values_id', $order->products[$i]['attributes'][$j]['value_id']);
            $Qattributes->bindInt(':language_id', $this->app->lang->getId());
            $Qattributes->execute();

            $products_ordered_attributes .= "\n\t" . $Qattributes->value('products_options_name') . ' ' . $Qattributes->value('products_options_values_name');
          }
        }

//------insert customer choosen option eof ----
        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . OSCOM::link('account_history_info.php', 'order_id=' . $order_id, false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= HTML::outputProtected($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n" .
                      EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $this->public_title . "\n\n";

      $orderEmail = new Mail($order->customer['email_address'], $order->customer['firstname'] . ' ' . $order->customer['lastname'], STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, EMAIL_TEXT_SUBJECT);
      $orderEmail->setBody($email_order);
      $orderEmail->send();

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        $extraEmail = new Mail(SEND_EXTRA_ORDER_EMAILS_TO, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, EMAIL_TEXT_SUBJECT);
        $extraEmail->setBody($email_order);
        $extraEmail->send();
      }

// load the after_process function from the payment modules
      $this->after_process();
    }

    function after_process() {
      $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
      unset($_SESSION['comments']);

      unset($_SESSION['cart_PayPal_Pro_HS_ID']);
      unset($_SESSION['pphs_result']);
      unset($_SESSION['pphs_key']);

      OSCOM::redirect('checkout_success.php');
    }

    function get_error() {
      $error = array('title' => $this->app->getDef('module_hs_error_general_title'),
                     'error' => $this->app->getDef('module_hs_error_general'));

      if ( isset($_SESSION['pphs_error_msg']) ) {
        $error['error'] = $_SESSION['pphs_error_msg'];

        unset($_SESSION['pphs_error_msg']);
      }

      return $error;
    }

    function check() {
      return defined('OSCOM_APP_PAYPAL_HS_STATUS') && (trim(OSCOM_APP_PAYPAL_HS_STATUS) != '');
    }

    function install() {
      $this->app->redirect('Configure&Install&module=HS');
    }

    function remove() {
      $this->app->redirect('Configure&Uninstall&module=HS');
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_HS_SORT_ORDER');
    }

    function verifyTransaction($is_ipn = false) {
      global $currencies;

      $tx_order_id = $_SESSION['pphs_result']['INVNUM'];
      $tx_customer_id = $_SESSION['pphs_result']['CUSTOM'];
      $tx_transaction_id = $_SESSION['pphs_result']['TRANSACTIONID'];
      $tx_payment_status = $_SESSION['pphs_result']['PAYMENTSTATUS'];
      $tx_payment_type = $_SESSION['pphs_result']['PAYMENTTYPE'];
      $tx_payer_status = $_SESSION['pphs_result']['PAYERSTATUS'];
      $tx_address_status = $_SESSION['pphs_result']['ADDRESSSTATUS'];
      $tx_amount = $_SESSION['pphs_result']['AMT'];
      $tx_currency = $_SESSION['pphs_result']['CURRENCYCODE'];
      $tx_pending_reason = (isset($_SESSION['pphs_result']['PENDINGREASON'])) ? $_SESSION['pphs_result']['PENDINGREASON'] : null;

      if ( is_numeric($tx_order_id) && ($tx_order_id > 0) && is_numeric($tx_customer_id) && ($tx_customer_id > 0) ) {
        $Qorder = $this->app->db->get('orders', ['orders_id', 'orders_status', 'currency', 'currency_value'], ['orders_id' => $tx_order_id, 'customers_id' => $tx_customer_id]);

        if ($Qorder->fetch() !== false) {
          $new_order_status = DEFAULT_ORDERS_STATUS_ID;

          if ( $Qorder->valueInt('orders_status') != OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID ) {
            $new_order_status = $Qorder->valueInt('orders_status');
          }

          $Qtotal = $this->app->db->get('orders_total', 'value', ['orders_id' => $Qorder->valueInt('orders_id'), 'class' => 'ot_total'], null, 1);

          $comment_status = 'Transaction ID: ' . HTML::outputProtected($tx_transaction_id) . "\n" .
                            'Payer Status: ' . HTML::outputProtected($tx_payer_status) . "\n" .
                            'Address Status: ' . HTML::outputProtected($tx_address_status) . "\n" .
                            'Payment Status: ' . HTML::outputProtected($tx_payment_status) . "\n" .
                            'Payment Type: ' . HTML::outputProtected($tx_payment_type) . "\n" .
                            'Pending Reason: ' . HTML::outputProtected($tx_pending_reason);

          if ( $tx_amount != $this->app->formatCurrencyRaw($Qtotal->value('value'), $Qorder->value('currency'), $Qorder->value('currency_value')) ) {
            $comment_status .= "\n" . 'OSCOM Error Total Mismatch: PayPal transaction value (' . HTML::outputProtected($tx_amount) . ') does not match order value (' . $this->app->formatCurrencyRaw($Qtotal->value('value'), $Qorder->value('currency'), $Qorder->value('currency_value')) . ')';
          } elseif ( $tx_payment_status == 'Completed' ) {
            $new_order_status = (OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID > 0 ? OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID : $new_order_status);
          }

          $this->app->db->save('orders', ['orders_status' => $new_order_status, 'last_modified' => 'now()'], ['orders_id' => $Qorder->valueInt('orders_id')]);

          if ( $is_ipn === true ) {
            $comment_status .= "\n" . 'Source: IPN';
          }

          $sql_data_array = array('orders_id' => $Qorder->valueInt('orders_id'),
                                  'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $comment_status);

          $this->app->db->save('orders_status_history', $sql_data_array);
        }
      }
    }
  }
?>
