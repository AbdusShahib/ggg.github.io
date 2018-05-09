<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions;

class Start extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $this->page->data['action'] = 'Start';
    }
}
