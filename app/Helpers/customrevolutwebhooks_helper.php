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
 * Class customrevolutwebhooks_helper
 *
 * Represents a helper class to get updates from BigCommerce
 */
class customrevolutwebhooks_helper
{
    /**
     * create custom revolut webhooks in Bigcommerce store
     *
     * @param text| $email_id
     * @param text| $validation_id
     */
    public static function createCustomWebhooks($email_id, $validation_id)
    {
        $tokenValidation = new \App\Models\RevolutTokenValidationModel();

        $condition = [
            'email_id'      => $email_id,
            'validation_id' => $validation_id,
        ];
        $db_resp = $tokenValidation->getData($condition);
        if ($db_resp['status']) {
            $result = $db_resp['data'];

            $is_test_live = $result['is_test_live'];
            $paymentURL   = getenv('revolut.SANDBOX_API_URL');
            $api_key      = $result['revolut_api_key_test'];
            if ($is_test_live === '1') {
                $paymentURL = getenv('revolut.PROD_API_URL');
                $api_key    = $result['revolut_api_key'];
            }
            $request = [
                'url'    => getenv('app.baseURL') . 'webHooks/revolutWebhook/' . $email_id . '/' . base64_encode(json_encode($validation_id, true)),
                'events' => ['ORDER_COMPLETED', 'ORDER_AUTHORISED'],
            ];
            $request = json_encode($request, true);

            $headers = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
            ];

            $url = $paymentURL . '/api/1.0/webhooks';
            helper('curl');
            $api_response = \curl_helper::APICall($url, 'POST', $request, $headers, 'Revolut', 'WebhooksCreation', $email_id, $validation_id, 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['id'])) {
                    $data = [
                        'email_id'            => $email_id,
                        'webhook_id'          => $res['id'],
                        'scope'               => addslashes(json_encode($res['events'])),
                        'destination'         => $res['url'],
                        'api_response'        => addslashes(json_encode($res)),
                        'token_validation_id' => $validation_id,
                    ];
                    $revolutWebhooks = new \App\Models\RevolutWebhooksModel();
                    $revolutWebhooks->insertData($data);
                }
            }
        }
    }

    /**
     * checkRevolutValidKey - validating api key using Revolut API
     *
     * @param $is_test_live(live or sandbox)
     * @param $api_key(api key of revolut)
     *
     * @return status array
     */
    public static function checkRevolutValidKey($is_test_live, $api_key)
    {
        $resp           = [];
        $resp['status'] = false;
        $resp['msg']    = '';
        if (! empty($api_key)) {
            $paymentURL = getenv('revolut.SANDBOX_API_URL');
            if ($is_test_live === '1') {
                $paymentURL = getenv('revolut.PROD_API_URL');
            }

            $headers = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
            ];
            $url = $paymentURL . '/api/1.0/webhooks';
            helper('curl');
            $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'Revolut', 'Validation', 'info@revolut.com', '0', 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['code'])) {
                    $resp['status'] = false;
                    $resp['msg']    = $res['msg'];
                } else {
                    $resp['status'] = true;
                }
            }
        }

        return $resp;
    }
}
