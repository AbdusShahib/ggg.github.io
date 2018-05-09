<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Log;

use OSC\OM\Registry;

class View extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        if (isset($_GET['lID']) && is_numeric($_GET['lID'])) {
            $Qlog = $this->page->app->db->prepare('select l.*, unix_timestamp(l.date_added) as date_added, c.customers_firstname, c.customers_lastname from :table_oscom_app_paypal_log l left join :table_customers c on (l.customers_id = c.customers_id) where id = :id');
            $Qlog->bindInt(':id', $_GET['lID']);
            $Qlog->execute();

            if ($Qlog->fetch() !== false) {
                $this->page->data['log_request'] = [];

                $req = explode("\n", $Qlog->value('request'));

                foreach ($req as $r) {
                    $p = explode(':', $r, 2);

                    $this->page->data['log_request'][$p[0]] = $p[1];
                }

                $this->page->data['log_response'] = [];

                $res = explode("\n", $Qlog->value('response'));

                foreach ($res as $r) {
                    $p = explode(':', $r, 2);

                    $this->page->data['log_response'][$p[0]] = $p[1];
                }

                $this->page->setFile('log_view.php');
            }
        }
    }
}
