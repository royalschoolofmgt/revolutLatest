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
 * Class settingsviews
 *
 * Represents a helper class to connect Revolut & BigCommerce
 * connector and launch configuration.
 */
class settingsviews_helper
{
    /**
     * xmltoArray - Static funtion to convert xml to array.
     *
     * @param string| $xmlObject
     * @param mixed $out
     *
     * @return array
     */
    public static function xmltoArray($xmlObject, $out = [])
    {
        helper('settingsviews');

        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? \settingsviews_helper::xmltoArray($node) : $node;
        }

        return $out;
    }

    /**
     * storeTokenData - Static funtion to create BigCommerce details.
     *
     * @param array| $response
     *
     * @return Validation id & Email
     */
    public static function storeTokenData($response)
    {
        $email       = '';
        $accessToken = '';
        $storeHash   = '';
        helper('settingsviews');
        $tokenValidation = new \App\Models\RevolutTokenValidationModel();
        if (isset($response['user']['email'])) {
            $email = $response['user']['email'];
        }
        if (isset($response['access_token'])) {
            $accessToken = $response['access_token'];
        }
        if (isset($response['context'])) {
            $storeHash = str_replace('stores/', '', $response['context']);
        }
        if (! empty($email) && ! empty($accessToken) && ! empty($storeHash)) {
            $condition = [
                'email_id'   => $email,
                'store_hash' => $storeHash,
            ];
            $db_resp = $tokenValidation->getData($condition);
            if ($db_resp['status']) {
                $result = $db_resp['data'];
                $data   = [
                    'acess_token' => $accessToken,
                    'store_hash'  => $storeHash,
                ];
                $condition = [
                    'email_id'   => $email,
                    'store_hash' => $storeHash,
                ];
                $tokenValidation->updateData($condition, $data);

                try {
                    \settingsviews_helper::createCustomPages($accessToken, $storeHash, $email, $result['validation_id']);
                } catch (\Exception $e) {
                    log_message('info', 'exception:' . $e->getMessage());
                }

                $responseRedirect          = [];
                $responseRedirect['id']    = $result['validation_id'];
                $responseRedirect['email'] = $email;

                return $responseRedirect;
            }
            $sellerdb = '247c' . strtotime(date('y-m-d h:m:s'));
            $data     = [
                'acess_token' => $accessToken,
                'store_hash'  => $storeHash,
                'email_id'    => $email,
                'sellerdb'    => $sellerdb,
            ];
            $insertID = $tokenValidation->insertData($data);

            try {
                \settingsviews_helper::createCustomPages($accessToken, $storeHash, $email, $insertID);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }

            $responseRedirect          = [];
            $responseRedirect['id']    = $insertID;
            $responseRedirect['email'] = $email;

            return $responseRedirect;
        }
    }

    /**
     * createCustomPages - Static funtion to create Custom Page and webhooks of Bigcommerce.
     *
     * @param text| $acess_token
     * @param test| $store_hash
     * @param test| $email_id
     * @param test| $validation_id
     */
    public static function createCustomPages($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($acess_token) && ! empty($store_hash) && ! empty($email_id) && ! empty($validation_id)) {
            helper('customorderpages');
            \customorderpages_helper::customOrderConfirmation($acess_token, $store_hash, $email_id, $validation_id);

            helper('customwebhooks');
            \customwebhooks_helper::createCustomWebhooks($acess_token, $store_hash, $email_id, $validation_id);
        }
    }

    /**
     * getAppClientDetails - Static funtion to get Loggedin client details.
     *
     * @return logged in client details
     */
    public static function getClientDetails()
    {
        helper('settingsviews');
        $session       = session();
        $email_id      = $session->get('email_id');
        $validation_id = $session->get('validation_id');
        $data          = [];
        if (! empty($email_id) && ! empty($validation_id)) {
            $condition = [
                'email_id'      => $email_id,
                'validation_id' => $validation_id,
            ];
            $tokenValidation = new \App\Models\RevolutTokenValidationModel();
            $db_resp         = $tokenValidation->getData($condition);
            if ($db_resp['status']) {
                $data = $db_resp['data'];
            }
        }

        return $data;
    }

    /**
     * unInstallCustomPages - uninstall all custom pages,webhooks,scripts from bigcommerce and revolut.
     *
     *@param array|clientDetails
     * @param mixed $clientDetails
     */
    public static function unInstallCustomPages($clientDetails)
    {
        helper('settingsviews');
        if (! empty($clientDetails)) {
            $email_id      = $clientDetails['email_id'];
            $validation_id = $clientDetails['validation_id'];
            $sellerdb      = $clientDetails['sellerdb'];
            $store_hash    = $clientDetails['store_hash'];
            $acess_token   = $clientDetails['acess_token'];

            try {
                \settingsviews_helper::deleteScripts($acess_token, $store_hash, $email_id, $validation_id);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }

            try {
                \settingsviews_helper::deleteCustomPages($acess_token, $store_hash, $email_id, $validation_id);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }

            try {
                \settingsviews_helper::deleteCustomWebhooks($acess_token, $store_hash, $email_id, $validation_id);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }

            try {
                \settingsviews_helper::deleteCustomRevolutWebhooks($acess_token, $store_hash, $email_id, $validation_id, $clientDetails);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }
            $data = [
                'is_enable'            => 0,
                'revolut_api_key'      => '',
                'is_test_live'         => '',
                'revolut_api_key_test' => '',
            ];

            $condition = [
                'email_id'      => $email_id,
                'validation_id' => $validation_id,
            ];
            $tokenValidation = new \App\Models\RevolutTokenValidationModel();
            $tokenValidation->updateData($condition, $data);
        }
    }

    /**
     * deleteScripts - to delete script file in bigcommerce.
     *
     *@param acess_token|text
     *@param store_hash|text
     *@param email_id|text
     *@param text|validation_id
     * @param mixed $acess_token
     * @param mixed $store_hash
     * @param mixed $email_id
     * @param mixed $validation_id
     */
    public static function deleteScripts($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('curl');
        $revolutScripts = new \App\Models\RevolutScriptsModel();
        $condition      = [
            'script_email_id'     => $email_id,
            'token_validation_id' => $validation_id,
        ];
        $db_resp = $revolutScripts->getAllData($condition);
        if ($db_resp['status']) {
            $result = $db_resp['data'];

            foreach ($result as $k => $v) {
                $headers = [
                    'headers' => [
                        'X-Auth-Client' => $acess_token,
                        'X-Auth-Token'  => $acess_token,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                ];
                $request = '';
                $url     = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v3/content/scripts/' . $v['script_code'];

                \curl_helper::APICall($url, 'DELETE', $request, $headers, 'BigCommerce', 'ScriptDeletion', $email_id, $validation_id, 'text/plain');

                $revolutScripts->where('script_id', $v['script_id'])->delete($v['script_id']);
            }
        }
    }

    /**
     * deleteCustomWebhooks - to delete webhooks in bigcommerce.
     *
     *@param acess_token|text
     *@param store_hash|text
     *@param email_id|text
     *@param text|validation_id
     * @param mixed $acess_token
     * @param mixed $store_hash
     * @param mixed $email_id
     * @param mixed $validation_id
     */
    public static function deleteCustomWebhooks($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('curl');
        $webhooks  = new \App\Models\WebhooksModel();
        $condition = [
            'email_id'            => $email_id,
            'token_validation_id' => $validation_id,
        ];
        $db_resp = $webhooks->getAllData($condition);
        if ($db_resp['status']) {
            $result = $db_resp['data'];

            foreach ($result as $k => $v) {
                $headers = [
                    'headers' => [
                        'X-Auth-Client' => $acess_token,
                        'X-Auth-Token'  => $acess_token,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                ];
                $request = '';
                $url     = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v3/hooks/' . $v['webhook_bc_id'];
                \curl_helper::APICall($url, 'DELETE', $request, $headers, 'BigCommerce', 'WebhooksDeletion', $email_id, $validation_id, 'text/plain');

                $webhooks->where('id', $v['id'])->delete($v['id']);
            }
        }
    }

    /**
     * deleteCustomRevolutWebhooks - to delete webhooks in revolut.
     *
     *@param acess_token|text
     *@param store_hash|text
     *@param email_id|text
     *@param text|validation_id
     * @param mixed $acess_token
     * @param mixed $store_hash
     * @param mixed $email_id
     * @param mixed $validation_id
     * @param mixed $clientDetails
     */
    public static function deleteCustomRevolutWebhooks($acess_token, $store_hash, $email_id, $validation_id, $clientDetails)
    {
        helper('curl');
        $revolutWebhooks = new \App\Models\RevolutWebhooksModel();
        $condition       = [
            'email_id'            => $email_id,
            'token_validation_id' => $validation_id,
        ];
        $db_resp = $revolutWebhooks->getAllData($condition);
        if ($db_resp['status']) {
            $result = $db_resp['data'];

            foreach ($result as $k => $v) {
                $paymentURL   = getenv('revolut.SANDBOX_API_URL');
                $api_key      = $clientDetails['revolut_api_key_test'];
                $is_test_live = $clientDetails['is_test_live'];
                if ($is_test_live === '1') {
                    $paymentURL = getenv('revolut.PROD_API_URL');
                    $api_key    = $clientDetails['revolut_api_key'];
                }
                $request = '';
                $url     = $paymentURL . '/api/1.0/webhooks/' . $v['webhook_id'];
                $headers = [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $api_key,
                        'Content-Type'  => 'application/json',
                    ],
                ];
                \curl_helper::APICall($url, 'DELETE', $request, $headers, 'Revolut', 'WebhooksDeletion', $email_id, $validation_id, 'text/plain');

                $revolutWebhooks->where('id', $v['id'])->delete($v['id']);
            }
        }
    }

    /**
     * deleteCustomPages - to delete custom pages in bigcommerce.
     *
     *@param acess_token|text
     *@param store_hash|text
     *@param email_id|text
     *@param text|validation_id
     * @param mixed $acess_token
     * @param mixed $store_hash
     * @param mixed $email_id
     * @param mixed $validation_id
     */
    public static function deleteCustomPages($acess_token, $store_hash, $email_id, $validation_id)
    {
        helper('curl');
        $customPages = new \App\Models\CustomPagesModel();
        $condition   = [
            'email_id'            => $email_id,
            'token_validation_id' => $validation_id,
        ];
        $db_resp = $customPages->getAllData($condition);
        if ($db_resp['status']) {
            $result = $db_resp['data'];

            foreach ($result as $k => $v) {
                $headers = [
                    'headers' => [
                        'X-Auth-Client' => $acess_token,
                        'X-Auth-Token'  => $acess_token,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                ];
                $request      = '';
                $url          = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v2/pages/' . $v['page_bc_id'];
                $api_response = \curl_helper::APICall($url, 'DELETE', $request, $headers, 'BigCommerce', 'CustomPageDeletion', $email_id, $validation_id, 'text/plain');
                if ($api_response['status']) {
                    $res = $api_response['data'];
                    if (empty($res)) {
                        $customPages->where('id', $v['id'])->delete($v['id']);
                    }
                }
            }
        }
    }

    /**
     * enablePayment - to enable payment script files in bigcommerce.
     */
    public static function enablePayment()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails)) {
            $email_id      = $clientDetails['email_id'];
            $validation_id = $clientDetails['validation_id'];
            $sellerdb      = $clientDetails['sellerdb'];
            $store_hash    = $clientDetails['store_hash'];
            $acess_token   = $clientDetails['acess_token'];
            $is_test_live  = $clientDetails['is_test_live'];

            try {
                $res = \settingsviews_helper::injectPaymentScripts($sellerdb, $acess_token, $store_hash, $email_id, $validation_id);
                if ($res === 1) {
                    $data = [
                        'is_enable' => 1,
                    ];
                    $condition = [
                        'email_id'      => $email_id,
                        'validation_id' => $validation_id,
                    ];
                    $tokenValidation = new \App\Models\RevolutTokenValidationModel();
                    $tokenValidation->updateData($condition, $data);
                }
                helper('custompaymentscript');
                \custompaymentscript_helper::createPaymentScript($sellerdb, $email_id, $validation_id, $is_test_live);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }
        }
    }

    /**
     * injectPaymentScripts - to inject required script files in bigcommerce.
     *
     *@param sellerdb|text
     *@param acess_token|text
     *@param store_hash|text
     *@param email_id|text
     *@param text|validation_id
     * @param mixed $sellerdb
     * @param mixed $acess_token
     * @param mixed $store_hash
     * @param mixed $email_id
     * @param mixed $validation_id
     *
     * return status of scripts
     */
    public static function injectPaymentScripts($sellerdb, $acess_token, $store_hash, $email_id, $validation_id)
    {
        $url     = [];
        $rStatus = 0;
        $url[]   = getenv('bigcommerceapp.JS_SDK');
        $url[]   = getenv('app.ASSETSPATH') . $sellerdb . '/custom_script.js';

        foreach ($url as $k => $v) {
            $location  = 'head';
            $cstom_url = getenv('app.ASSETSPATH') . $sellerdb . '/custom_script.js';
            if ($v === $cstom_url) {
                $location = 'footer';
            }
            $request = '{
			  "name": "RevolutApp",
			  "description": "Revolut files",
			  "html": "<script src=\"' . $v . '\"></script>",
			  "auto_uninstall": true,
			  "load_method": "default",
			  "location": "' . $location . '",
			  "visibility": "checkout",
			  "kind": "script_tag",
			  "consent_category": "essential"
			}';

            $url     = getenv('bigcommerceapp.STORE_URL') . $store_hash . '/v3/content/scripts';
            $headers = [
                'headers' => [
                    'X-Auth-Token' => $acess_token,
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];
            helper('curl');
            $api_response = \curl_helper::APICall($url, 'POST', $request, $headers, 'BigCommerce', 'ScriptInjection', $email_id, $validation_id, 'application/json');
            if ($api_response['status']) {
                $response = $api_response['data'];
                if (! empty($response)) {
                    //$response = json_decode($res,true);
                    if (isset($response['data']['uuid'])) {
                        $data = [
                            'script_email_id'     => $email_id,
                            'script_filename'     => basename($v),
                            'script_code'         => $response['data']['uuid'],
                            'status'              => 1,
                            'api_response'        => addslashes(json_encode($response, true)),
                            'token_validation_id' => $validation_id,
                        ];
                        $revolutScripts = new \App\Models\RevolutScriptsModel();
                        $revolutScripts->insertData($data);
                        $rStatus++;
                    }
                }
            }
        }
        if ($rStatus >= 2) {
            return 1;
        }

        return 0;
    }

    /**
     * disablePayment - to disable Payment script files in Bigcommerce store
     */
    public static function disablePayment()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails)) {
            $email_id      = $clientDetails['email_id'];
            $validation_id = $clientDetails['validation_id'];
            $sellerdb      = $clientDetails['sellerdb'];
            $store_hash    = $clientDetails['store_hash'];
            $acess_token   = $clientDetails['acess_token'];

            try {
                \settingsviews_helper::deleteScripts($acess_token, $store_hash, $email_id, $validation_id);
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }

            $data = [
                'is_enable' => 0,
            ];
            $condition = [
                'email_id'      => $email_id,
                'validation_id' => $validation_id,
            ];
            $tokenValidation = new \App\Models\RevolutTokenValidationModel();
            $tokenValidation->updateData($condition, $data);
        }
    }

    /**
     * unInstallRevolutWebhooks - to uninstall revolut webhooks in Revolut store
     *
     *@param array|clientDetails
     * @param mixed $clientDetails
     */
    public static function unInstallRevolutWebhooks($clientDetails)
    {
        $data           = [];
        $data['status'] = false;
        helper('settingsviews');
        if (! empty($clientDetails)) {
            $email_id      = $clientDetails['email_id'];
            $validation_id = $clientDetails['validation_id'];
            $sellerdb      = $clientDetails['sellerdb'];
            $store_hash    = $clientDetails['store_hash'];
            $acess_token   = $clientDetails['acess_token'];

            try {
                \settingsviews_helper::deleteCustomRevolutWebhooks($acess_token, $store_hash, $email_id, $validation_id, $clientDetails);
                $data['status'] = true;
            } catch (\Exception $e) {
                log_message('info', 'exception:' . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * verifySignedRequest - Static funtion to check the valid signature.
     *
     * @param text| $signedRequest
     *
     * @return BigCommerce Store URL id & Email
     */
    public static function verifySignedRequest($signedRequest)
    {
        $clientSecret                     = getenv('bigcommerceapp.APP_CLIENT_SECRET');
        [$encodedData, $encodedSignature] = explode('.', $signedRequest, 2);
        $signature                        = base64_decode($encodedSignature, true);
        $jsonStr                          = base64_decode($encodedData, true);
        $data                             = json_decode($jsonStr, true);
        $expectedSignature                = hash_hmac('sha256', $jsonStr, $clientSecret, $raw = false);
        if (! hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');

            return null;
        }

        return $data;
    }
}
