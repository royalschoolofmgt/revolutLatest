<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Controllers;

/**
 * Class Revolutpay
 *
 * Represents a Revolut Payment Authentication and redirection
 */
class RevolutPay extends BaseController
{
    /**
     * authentication - method to create order order in revolut
     *
     *@param authKey|text
     *@param cartId|text
     *
     * @return revolut details in json format
     */
    public function authentication()
    {
        $res                    = [];
        $res['status']          = false;
        $res['rev_order_id']    = '';
        $res['rev_public_id']   = '';
        $res['rev_customer_id'] = '';
        $res['invoiceId']       = '';

        helper('settingsviews');
        helper('bigcommerceorder');
        helper('curl');
        $invalidCondition = empty($this->request->getPost('authKey')) && empty($this->request->getPost('cartId'));
        if ($invalidCondition) {
            echo json_encode($res, true);

            exit;
        }
        log_message('info', 'BigCommerce fields-authKey:' . $this->request->getPost('authKey'));
        log_message('info', 'BigCommerce fields-cartId:' . $this->request->getPost('cartId'));

        $tokenData     = json_decode(base64_decode($this->request->getPost('authKey'), true), true);
        $email_id      = $tokenData['email_id'];
        $validation_id = $tokenData['key'];
        $isStoreCreditApplied     = $this->request->getPost('isStoreCreditApplied');

        if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
            $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
            $invalidCondition = empty($clientDetails);
            if ($invalidCondition) {
                echo json_encode($res, true);

                exit;
            }

            $is_test_live   = $clientDetails['is_test_live'];
            $payment_option = $clientDetails['payment_option'];
            $cartAPIRes     = \bigcommerceorder_helper::getCartData($email_id, $this->request->getPost('cartId'), $validation_id);

            if (! is_array($cartAPIRes) || (is_array($cartAPIRes) && count($cartAPIRes) === 0)) {
                echo json_encode($res, true);

                exit;
            }
            $cartData           = $cartAPIRes;
            $invoiceId          = 'REVOLUT-' . $validation_id . '-' . uniqid() . '-' . time();
            $currency           = $cartData['cart']['currency']['code'];
            $cartbillingAddress = $cartData['billing_address'];
            $checkShipping      = false;
            if (count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0) {
                $checkShipping = true;
            } else {
                if (count($cartData['cart']['line_items']['digital_items']) > 0) {
                    $checkShipping = false;
                }
            }
            if ($checkShipping) {
                $cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
            } else {
                $cart_shipping_address = $cartData['billing_address'];
            }
            $totalAmount = $cartData['grand_total'];
            if ($isStoreCreditApplied === 'true' && isset($cartData['cart']['customer_id']) && $cartData['cart']['customer_id'] > 0) {
                $cartData['isStoreCreditApplied'] = true;
                $totalAmount     = \bigcommerceorder_helper::getCustomerStoreAmount($email_id, $validation_id, $cartData['cart']['customer_id'], $totalAmount);
            }

            if ($totalAmount > 0) {
            } else {
                echo 'Amount should be grater than zero';
                echo json_encode($res, true);

                exit;
            }

            $transaction_type = 'MANUAL';
            if ($payment_option === 'CFO') {
                $transaction_type = 'AUTOMATIC';
            }

            $tokenData = ['email_id' => $email_id, 'key' => $validation_id, 'invoice_id' => $invoiceId];
            /**
             *	first check record exist in DB for same cart id, email id, validation id; if exist update the row else insert a new record. This way, if we reload checkout page multiple times it will be having same cart id always and only one record will be there in order_payment_details table.
            */

            $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
            $condition           = [
                'email_id'            => $email_id,
                'token_validation_id' => $validation_id,
                'cart_id', $cartData['id'],
                'status', 'PENDING',
            ];
            $db_resp = $orderPaymentDetails->getData($condition);
            if ($db_resp['status']) {
                $data = [
                    'type'         => $transaction_type,
                    'order_id'     => $invoiceId,
                    'total_amount' => $totalAmount,
                    'amount_paid'  => '0.00',
                    'currency'     => $currency,
                    'status'       => 'PENDING',
                    'params'       => base64_encode(json_encode($cartData)),
                ];
                $orderPaymentDetails->updateData($condition, $data);
            } else {
                $data = [
                    'email_id'            => $email_id,
                    'type'                => $transaction_type,
                    'order_id'            => $invoiceId,
                    'cart_id'             => $cartData['id'],
                    'total_amount'        => $totalAmount,
                    'amount_paid'         => '0.00',
                    'currency'            => $currency,
                    'status'              => 'PENDING',
                    'params'              => base64_encode(json_encode($cartData)),
                    'token_validation_id' => $validation_id,
                ];
                $orderPaymentDetails->insertData($data);
            }

            // revolut create order api to get public id whihc is required for hosted integration
            $paymentURL = getenv('revolut.SANDBOX_API_URL');
            $api_key    = $clientDetails['revolut_api_key_test'];
            if ($is_test_live === '1') {
                $paymentURL = getenv('revolut.PROD_API_URL');
                $api_key    = $clientDetails['revolut_api_key'];
            }
            $request = [
                'amount'                 => $totalAmount * 100,
                'capture_mode'           => $transaction_type,
                'merchant_order_ext_ref' => $invoiceId,
                'currency'               => $currency,
            ];

            $jsonRequest = json_encode($request);
            $url         = $paymentURL . '/api/1.0/orders';
            $headers     = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
            ];

            $api_response = \curl_helper::APICall($url, 'POST', $jsonRequest, $headers, 'Revolut', 'CreateOrderPaymentRequest', $email_id, $validation_id, 'application/json');

            if ($api_response['status']) {
                $resp = $api_response['data'];
                if (isset($resp['id'])) {
                    $res['status']        = true;
                    $res['rev_order_id']  = $resp['id'];
                    $res['rev_public_id'] = $resp['public_id'];
                    $res['invoiceId']     = $invoiceId;
                    if (isset($resp['customer_id'])) {
                        $res['rev_customer_id'] = $resp['customer_id'];
                    }
                }
            } else {
                $res['msg'] = $api_response['msg'];
            }
        }
        echo json_encode($res, true);
        exit;
    }

    /**
     * zauthentication - method to create zero amount order in 247commerce
     *
     *@param authKey|text
     *@param cartId|text
     *
     * @return payment details
     */
    public function zauthentication()
    {
        $res           = [];
        $res['status'] = false;
        $res['url']    = '';

        helper('settingsviews');
        helper('bigcommerceorder');

        $invalidCondition = empty($this->request->getPost('authKey')) && empty($this->request->getPost('cartId'));
        if ($invalidCondition) {
            return;
        }
        log_message('info', 'BigCommerce fields-authKey:' . $this->request->getPost('authKey'));
        log_message('info', 'BigCommerce fields-cartId:' . $this->request->getPost('cartId'));
        log_message('info', 'BigCommerce fields-cartId:' . $this->request->getPost('isStoreCreditApplied'));

        $tokenData     = json_decode(base64_decode($this->request->getPost('authKey'), true), true);
        $email_id      = $tokenData['email_id'];
        $validation_id = $tokenData['key'];
        $isStoreCreditApplied     = $this->request->getPost('isStoreCreditApplied');

        if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
            $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
            $invalidCondition = $invalidCondition && empty($clientDetails);
            if ($invalidCondition) {
                echo json_encode($res, true);

                exit;
            }

            $payment_option = $clientDetails['payment_option'];
            $cartAPIRes     = \bigcommerceorder_helper::getCartData($email_id, $this->request->getPost('cartId'), $validation_id);

            if (! is_array($cartAPIRes) || (is_array($cartAPIRes) && count($cartAPIRes) === 0)) {
                echo json_encode($res, true);

                exit;
            }
            $cartData           = $cartAPIRes;
            $invoiceId          = 'REVOLUT-' . $validation_id . '-' . uniqid() . '-' . time();
            $currency           = $cartData['cart']['currency']['code'];
            $cartbillingAddress = $cartData['billing_address'];
            $checkShipping      = false;
            if (count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0) {
                $checkShipping = true;
            } else {
                if (count($cartData['cart']['line_items']['digital_items']) > 0) {
                    $checkShipping = false;
                }
            }
            if ($checkShipping) {
                $cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
            } else {
                $cart_shipping_address = $cartData['billing_address'];
            }
            $totalAmount = $cartData['grand_total'];
            if ($isStoreCreditApplied === 'true' && isset($cartData['cart']['customer_id']) && $cartData['cart']['customer_id'] > 0) {
                $cartData['isStoreCreditApplied'] = true;
                $totalAmount     = \bigcommerceorder_helper::getCustomerStoreAmount($email_id, $validation_id, $cartData['cart']['customer_id'], $totalAmount);
            }
            if ($totalAmount === 0) {
                $transaction_type = 'MANUAL';
                if ($payment_option === 'CFO') {
                    $transaction_type = 'AUTOMATIC';
                }
                $tokenData = ['email_id' => $email_id, 'key' => $validation_id, 'invoice_id' => $invoiceId];

                $data = [
                    'email_id'            => $email_id,
                    'type'                => $transaction_type,
                    'order_id'            => $invoiceId,
                    'cart_id'             => $cartData['id'],
                    'total_amount'        => $totalAmount,
                    'amount_paid'         => '0.00',
                    'currency'            => $currency,
                    'status'              => 'PENDING',
                    'params'              => base64_encode(json_encode($cartData)),
                    'token_validation_id' => $validation_id,
                ];

                $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
                $orderPaymentDetails->insertData($data);

                $res['status'] = true;
                $url           = getenv('app.baseURL') . 'revolutPay/payment/' . $invoiceId;
                $res['url']    = $url;
            }
        }
        echo json_encode($res, true);

        exit;
    }

    /**
     * payment - method to create order in bigcommerce and revolut
     *
     *@param invoiceId|text
     *@param revOrderId|text
     * @param mixed $invoiceId
     * @param mixed $revOrderId
     *
     * @redirect to create order
     */
    public function payment($invoiceId, $revOrderId = '')
    {
        $data = [];
        if (! empty($invoiceId)) {
            log_message('info', 'Create Order with Revolut Payment Inv:' . $invoiceId);

            $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
            $condition           = [
                'order_id' => $invoiceId,
            ];
            $db_resp = $orderPaymentDetails->getData($condition);
            if ($db_resp['status']) {
                $result_order_payment = $db_resp['data'];
                $tokenData            = ['email_id' => $result_order_payment['email_id'], 'key' => $result_order_payment['token_validation_id'], 'invoice_id' => $invoiceId];
                if ($result_order_payment['total_amount'] > 0) {
                    $this->createOrder(base64_encode(json_encode($tokenData, true)), 'Revolut', $revOrderId);
                } else {
                    $this->createOrder(base64_encode(json_encode($tokenData, true)), 'Manual', '');
                }
            }
        }
    }

    /**
     * createOrder - method to create order in bigcommerce based on revolut payment status
     *
     *@param authKey|text
     *@param payType|text
     *@param revOrderId|text
     * @param mixed $authKey
     * @param mixed $payType
     * @param mixed $revOrderId
     *
     * @redirect to  redirectbigcommerce
     */
    public function createOrder($authKey, $payType, $revOrderId)
    {
        helper('settingsviews');
        helper('bigcommerceorder');
        helper('curl');

        $tokenData     = json_decode(base64_decode($authKey, true), true);
        $email_id      = $tokenData['email_id'];
        $invoice_id    = $tokenData['invoice_id'];
        $validation_id = $tokenData['key'];

        $result = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
        $invalidCondition = empty($result);
        if ($invalidCondition) {
            return;
        }

        $is_test_live = $result['is_test_live'];
        // revolut get order api to get see payment status details
        if ($revOrderId !== '') {
            $paymentURL = getenv('revolut.SANDBOX_API_URL');
            $api_key    = $result['revolut_api_key_test'];
            if ($is_test_live === '1') {
                $paymentURL = getenv('revolut.PROD_API_URL');
                $api_key    = $result['revolut_api_key'];
            }
            $url     = $paymentURL . '/api/1.0/orders/' . $revOrderId;
            $headers = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
            ];

            $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'Revolut', 'RetrieveOrderPaymentRequest', $email_id, $validation_id, 'application/json');
            if ($api_response['status']) {
                $resp = $api_response['data'];
                if (isset($resp['state'])) {
                    $amount = 0;
                    if (isset($resp['order_amount']['value'])) {
                        $amount = $resp['order_amount']['value'];
                    }
                    $data = [
                        'amount_paid'  => $amount,
                        'status'       => $resp['state'],
                        'api_response' => addslashes(json_encode($resp, true)),
                    ];
                }
            }
        } else {
            $data = [
                'status'       => 'CONFIRMED',
                'api_response' => 'manual zero amount order',
            ];
        }

        $condition = [
            'order_id'            => $invoice_id,
            'email_id'            => $email_id,
            'token_validation_id' => $validation_id,
        ];
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $orderPaymentDetails->updateData($condition, $data);

        $db_resp = $orderPaymentDetails->getData($condition);

        if ($db_resp['status']) {
            $result_order_payment = $db_resp['data'];
            $string               = base64_decode($result_order_payment['params'], true);
            $string               = preg_replace("/[\r\n]+/", ' ', $string);
            $json                 = utf8_encode($string);
            $cartData             = json_decode($json, true);

            $create_order_status = \bigcommerceorder_helper::formatCreateOrder($email_id, $validation_id, $cartData, $result_order_payment, $payType);
            if ($create_order_status['status']) {
                $createOrder = $create_order_status['data'];
                log_message('info', 'Before create order API call');
                $bigComemrceOrderId = \bigcommerceorder_helper::createOrder($result_order_payment['email_id'], $createOrder, $invoice_id, $result_order_payment['token_validation_id']);

                log_message('info', 'Create order API response: ' . $bigComemrceOrderId);
                if ($bigComemrceOrderId !== '') {
                    log_message('info', 'Before update order API call');
                    //update order status for trigger status update mail from bigcommerce
                    \bigcommerceorder_helper::updateOrderStatus($bigComemrceOrderId, $result_order_payment['email_id'], $result_order_payment['token_validation_id']);
                }
                log_message('info', 'Before delete cart API call');
                \bigcommerceorder_helper::deleteCart($result_order_payment['email_id'], $result_order_payment['cart_id'], $result_order_payment['token_validation_id']);
                log_message('info', 'After delete cart API call');
                $this->redirectBigcommerce($email_id, $invoice_id, $validation_id);
            }
        }
    }

    /**
     * redirectBigcommerce - method to check order details and redirect to bigcommerce custom page
     *
     *@param email_id|text
     *@param invoice_id|text
     *@param text|validation_id
     * @param mixed $email_id
     * @param mixed $invoice_id
     * @param mixed $validation_id
     *
     * @return redirect to bigcommerce
     */
    public function redirectBigcommerce($email_id, $invoice_id, $validation_id)
    {
        helper('bigcommerceorder');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
        if (! empty($clientDetails)) {
            $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/store';
            $headers = [
                'headers' => [
                    'X-Auth-Token' => $clientDetails['acess_token'],
                    'store_hash'   => $clientDetails['store_hash'],
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];
            $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'BigCommerce', 'StoreDetails', $email_id, $validation_id, 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['secure_url'])) {
                    $orderDetails = new \App\Models\OrderDetailsModel();
                    $condition    = [
                        'email_id'            => $email_id,
                        'token_validation_id' => $validation_id,
                        'invoice_id'          => $invoice_id,
                    ];
                    $db_resp = $orderDetails->getData($condition);
                    if ($db_resp['status']) {
                        $invoice_result = $db_resp['data'];
                        $order_id       = $invoice_result['order_id'];
                        $invoice_id     = $invoice_result['invoice_id'];
                        $bg_customer_id = $invoice_result['bg_customer_id'];

                        log_message('info', 'Redirecting to revolut-order-confirmation.');

                        $invoice_id = base64_encode(json_encode($invoice_id, true));
                        $url        = $res['secure_url'] . '/revolut-order-confirmation?authKey=' . $invoice_id;
                        echo '<script>window.parent.location.href="' . $url . '";</script>';
                    } else {
                        $url = $res['secure_url'] . '/checkout?revolutinv=' . base64_encode(json_encode($invoice_id));
                        echo '<script>window.parent.location.href="' . $url . '";</script>';
                    }
                }
            }
        }
    }

    /**
     * getPaymentStatus - method to show payment status of order details
     *
     *@param authKey|text
     *
     * @return json response of order
     */
    public function getPaymentStatus()
    {
        $final_data           = [];
        $final_data['status'] = false;
        $final_data['data']   = [];
        $final_data['msg']    = '';
        if (! empty($this->request->getPost('authKey'))) {
            $invoiceId = json_decode(base64_decode($this->request->getPost('authKey'), true), true);
            if ($invoiceId !== '') {
                $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
                $condition           = [
                    'order_id' => $invoiceId,
                ];
                $db_resp = $orderPaymentDetails->getData($condition);
                if ($db_resp['status']) {
                    $result_order_payment = $db_resp['data'];
                    if ($result_order_payment['status'] !== 'CONFIRMED') {
                        $final_data['status'] = true;
                        $final_data['msg']    = 'There was an issue with your payment';
                    }
                }
            }
        }
        echo json_encode($final_data, true);

        exit;
    }
}
