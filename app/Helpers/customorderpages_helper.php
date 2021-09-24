<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Class customorderpages_helper
 *
 * Represents a helper class to create Success Order Custom Pages in BigCommerce
 */
class customorderpages_helper
{
    /**
     * create custom Pages order confirmation for Revolut in Bigcommerce store
     *
     * @param text| $acess_token
     * @param text| $store_hash
     * @param text| $email_id
     * @param text| $validation_id
     */
    public static function customOrderConfirmation($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('curl');

        $url = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v2/pages';

        $headers = [
            'headers' => [
                'X-Auth-Token' => $acess_token,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        /**
         *	custom webpage creation: /revolut-order-confirmation
         */
        $request = [
            'body' => "<head>
							<script type=\"text/javascript\">var app_base_url = '" . getenv('app.baseURL') . "';</script>
							<link rel=\"stylesheet\" href=\"" . getenv('app.ASSETSPATH') . '/css/247revolutloader.css">
							<link rel="stylesheet" href="' . getenv('app.ASSETSPATH') . '/css/order-confirmation.css">
							<script src="' . getenv('app.ASSETSPATH') . 'js/jquery-min.js"></script>
							<script src="' . getenv('app.ASSETSPATH') . 'js/247revolutloader.js"></script>
							<script src="' . getenv('app.ASSETSPATH') . "js/order-confirmation.js\"></script>
							</head>
							<body>
							</body>
							<script>
								var text = 'Please wait...';
								var current_effect = 'bounce';
								$('body').waitMe({
									effect: current_effect,
									text: text,
									bg: 'rgba(255,255,255,0.7)',
									color: '#000',
									maxSize: '',
									waitTime: -1,
									source: \"" . getenv('app.ASSETSPATH') . "images/img.svg\",
									textPos: 'vertical',
									fontSize: '',
									onClose: function(el) {}
								});
							</script>",
            'channel_id'         => 1,
            'has_mobile_version' => false,
            'is_customers_only'  => false,
            'is_homepage'        => false,
            'is_visible'         => false,
            'mobile_body'        => '',
            'name'               => 'Revolut Order Confirmation',
            'parent_id'          => 0,
            'search_keywords'    => '',
            'sort_order'         => 0,
            'type'               => 'raw',
            'url'                => '/revolut-order-confirmation',
        ];
        $request      = json_encode($request, true);
        $api_response = \curl_helper::APICall($url, 'POST', $request, $headers, 'BigCommerce', 'CustomPageCreation', $email_id, $validation_id, 'application/json');
        if ($api_response['status']) {
            $res = $api_response['data'];
            if (isset($res['id'])) {
                $data = [
                    'email_id'            => $email_id,
                    'page_bc_id'          => $res['id'],
                    'api_response'        => addslashes(json_encode($res)),
                    'token_validation_id' => $validation_id,
                ];
                $customPages = new \App\Models\CustomPagesModel();
                $customPages->insertData($data);
            }
        }
    }
}
