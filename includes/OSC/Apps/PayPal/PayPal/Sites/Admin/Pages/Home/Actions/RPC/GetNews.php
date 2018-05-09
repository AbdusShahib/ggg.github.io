<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\HTTP;

class GetNews extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $result = [
            'rpcStatus' => -1
        ];

        $response = @json_decode(HTTP::getResponse([
            'url' => 'http://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerBanner&forumid=105&onlyjson=true'
        ]), true);

        if (is_array($response) && isset($response['title'])) {
            $result = $response;

            $result['rpcStatus'] = 1;
        }

        echo json_encode($result);
    }
}
