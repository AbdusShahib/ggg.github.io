<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions;

use OSC\OM\Registry;

class Privacy extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $this->page->setFile('privacy.php');
        $this->page->data['action'] = 'Privacy';

        $OSCOM_PayPal->loadDefinitions('admin/privacy');
    }
}
