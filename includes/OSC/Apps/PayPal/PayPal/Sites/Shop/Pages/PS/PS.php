<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Shop\Pages\PS;

use OSC\OM\HTML;
use OSC\OM\Mail;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\Apps\PayPal\PayPal\Module\Payment\PS as PaymentModulePS;

class PS extends \OSC\OM\PagesAbstract
{
    protected $file = null;
    protected $use_site_template = false;
    protected $pm;
    protected $lang;

    protected function init()
    {
        global $currencies;

        $this->lang = Registry::get('Language');

        $this->pm = new PaymentModulePS();

        if (!defined('OSCOM_APP_PAYPAL_PS_STATUS') || !in_array(OSCOM_APP_PAYPAL_PS_STATUS, [
            '1',
            '0'
        ])) {
            return false;
        }

        $this->lang->loadDefinitions('Shop/checkout_process');

        $result = false;

        $seller_accounts = [
            $this->pm->app->getCredentials('PS', 'email')
        ];

        if (tep_not_null($this->pm->app->getCredentials('PS', 'email_primary'))) {
            $seller_accounts[] = $this->pm->app->getCredentials('PS', 'email_primary');
        }

        if (isset($_POST['receiver_email']) && in_array($_POST['receiver_email'], $seller_accounts)) {
            $parameters = 'cmd=_notify-validate&';

            foreach ($_POST as $key => $value) {
                if ($key != 'cmd') {
                    $parameters .= $key . '=' . urlencode(stripslashes($value)) . '&';
                }
            }

            $parameters = substr($parameters, 0, -1);

            $result = $this->pm->app->makeApiCall($this->pm->form_action_url, $parameters);
        }

        $log_params = $_POST;
        $log_params['cmd'] = '_notify-validate';

        foreach ($_GET as $key => $value) {
            $log_params['GET ' . $key] = $value;
        }

        $this->pm->app->log('PS', '_notify-validate', ($result == 'VERIFIED') ? 1 : -1, $log_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox', true);

        if ($result == 'VERIFIED') {
            $this->pm->verifyTransaction($_POST, true);

            $order_id = (int)$_POST['invoice'];
            $customer_id = (int)$_POST['custom'];

            $Qorder = $this->pm->app->db->get('orders', 'orders_status', [
                'orders_id' => $order_id,
                'customers_id' => $customer_id
            ]);

            if ($Qorder->fetch() !== false) {
                if ($Qorder->value('orders_status') == OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID) {
                    $new_order_status = DEFAULT_ORDERS_STATUS_ID;

                    if (OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID > 0) {
                        $new_order_status = OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID;
                    }

                    $this->pm->app->db->save('orders', [
                        'orders_status' => $new_order_status,
                        'last_modified' => 'now()'
                    ], [
                        'orders_id' => $order_id
                    ]);

                    $sql_data_array = [
                        'orders_id' => $order_id,
                        'orders_status_id' => (int)$new_order_status,
                        'date_added' => 'now()',
                        'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                        'comments' => ''
                    ];

                    $this->pm->app->db->save('orders_status_history', $sql_data_array);

                    include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/classes/order.php');
                    $order = new \order($order_id);

                    if (DOWNLOAD_ENABLED == 'true') {
                        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
                            $Qdownloads = $this->pm->app->db->prepare('select opd.orders_products_filename from :table_orders o, :table_orders_products op, :table_orders_products_download opd where o.orders_id = :orders_id and o.customers_id = :customers_id and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != ""');
                            $Qdownloads->bindInt(':orders_id', $order_id);
                            $Qdownloads->bindInt(':customers_id', $customer_id);
                            $Qdownloads->execute();

                            if ($Qdownloads->fetch() !== false) {
                                if ($order->content_type == 'physical') {
                                    $order->content_type = 'mixed';

                                    break;
                                } else {
                                    $order->content_type = 'virtual';
                                }
                            } else {
                                if ($order->content_type == 'virtual') {
                                    $order->content_type = 'mixed';

                                    break;
                                } else {
                                    $order->content_type = 'physical';
                                }
                            }
                        }
                    } else {
                        $order->content_type = 'physical';
                    }

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

                                $Qstock = $this->pm->app->db->prepare($stock_query_sql);
                                $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));

                                if (is_array($products_attributes)) {
                                    $Qstock->bindInt(':options_id', $products_attributes[0]['option_id']);
                                    $Qstock->bindInt(':options_values_id', $products_attributes[0]['value_id']);
                                }

                                $Qstock->execute();
                            } else {
                                $Qstock = $this->pm->app->db->get('products', 'products_quantity', [
                                    'products_id' => tep_get_prid($order->products[$i]['id'])
                                ]);
                            }

                            if ($Qstock->fetch() !== false) {
// do not decrement quantities if products_attributes_filename exists
                                if ((DOWNLOAD_ENABLED != 'true') || !empty($Qstock->value('products_attributes_filename'))) {
                                    $stock_left = $Qstock->valueInt('products_quantity') - $order->products[$i]['qty'];
                                } else {
                                    $stock_left = $Qstock->valueInt('products_quantity');
                                }

                                if ($stock_left != $Qstock->valueInt('products_quantity')) {
                                    $this->pm->app->db->save('products', [
                                        'products_quantity' => $stock_left
                                    ], [
                                        'products_id' => tep_get_prid($order->products[$i]['id'])
                                    ]);
                                }

                                if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false')) {
                                    $this->pm->app->db->save('products', [
                                        'products_status' => '0'
                                    ], [
                                        'products_id' => tep_get_prid($order->products[$i]['id'])
                                    ]);
                                }
                            }
                        }

// Update products_ordered (for bestsellers list)
                        $Qupdate = $this->pm->app->db->prepare('update :table_products set products_ordered = products_ordered + :products_ordered where products_id = :products_id');
                        $Qupdate->bindInt(':products_ordered', $order->products[$i]['qty']);
                        $Qupdate->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
                        $Qupdate->execute();

                        $products_ordered_attributes = '';

                        if (isset($order->products[$i]['attributes'])) {
                            for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                                $products_ordered_attributes .= "\n\t" . $order->products[$i]['attributes'][$j]['option'] . ' ' . $order->products[$i]['attributes'][$j]['value'];
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

                    for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
                        $email_order .= strip_tags($order->totals[$i]['title']) . ' ' . strip_tags($order->totals[$i]['text']) . "\n";
                    }

                    if ($order->content_type != 'virtual') {
                        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                                        EMAIL_SEPARATOR . "\n" .
                                        tep_address_format($order->delivery['format_id'], $order->delivery, false, '', "\n") . "\n";
                    }

                    $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                                    EMAIL_SEPARATOR . "\n" .
                                    tep_address_format($order->billing['format_id'], $order->billing, false, '', "\n") . "\n\n";

                    $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                                    EMAIL_SEPARATOR . "\n" .
                                    $this->pm->title . "\n\n";

                    if (isset($this->pm->email_footer)) {
                        $email_order .= $this->pm->email_footer . "\n\n";
                    }

                    $orderEmail = new Mail($order->customer['email_address'], $order->customer['name'], STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, EMAIL_TEXT_SUBJECT);
                    $orderEmail->setBody($email_order);
                    $orderEmail->send();

// send emails to other people
                    if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
                        $extraEmail = new Mail(SEND_EXTRA_ORDER_EMAILS_TO, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, EMAIL_TEXT_SUBJECT);
                        $extraEmail->setBody($email_order);
                        $extraEmail->send();
                    }

                    $this->pm->app->db->delete('customers_basket', [
                        'customers_id' => $customer_id
                    ]);

                    $this->pm->app->db->delete('customers_basket_attributes', [
                        'customers_id' => $customer_id
                    ]);
                }
            }
        }

        Registry::get('Session')->kill();
    }
}
