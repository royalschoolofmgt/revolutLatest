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
 * Class CustomWebhooks
 *
 * Represents a helper class to get updates from BigCommerce
 */
class customwebhooks_helper
{
    /**
     * createCustomWebhooks - to create webhooks in Bigcommerce store used for order status change
     *
     * @param text| $acess_token
     * @param text| $store_hash
     * @param text| $email_id
     * @param text| $validation_id
     */
    public static function createCustomWebhooks($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('curl');
        $url = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v3/hooks';

        $headers = [
            'headers' => [
                'X-Auth-Token' => $acess_token,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        $webhooks = [
            [
                'id'          => 1,
                'scope'       => 'store/order/statusUpdated',
                'destination' => getenv('app.baseURL') . 'webHooks/index/' . $email_id . '/' . base64_encode(json_encode($validation_id, true)),
            ],
        ];

        foreach ($webhooks as $k => $v) {
            $request = [
                'scope'       => $v['scope'],
                'destination' => $v['destination'],
                'is_active'   => true,
            ];
            $request      = json_encode($request, true);
            $api_response = \curl_helper::APICall($url, 'POST', $request, $headers, 'BigCommerce', 'WebhooksCreation', $email_id, $validation_id, 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['data']['id'])) {
                    $data = [
                        'email_id'            => $email_id,
                        'webhook_bc_id'       => $res['data']['id'],
                        'scope'               => $res['data']['scope'],
                        'destination'         => $res['data']['destination'],
                        'api_response'        => addslashes(json_encode($res)),
                        'token_validation_id' => $validation_id,
                    ];
                    $webHooks = new \App\Models\WebhooksModel();
                    $webHooks->insertData($data);
                }
            }
        }
    }
}
