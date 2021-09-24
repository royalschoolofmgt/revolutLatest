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
 * Class Webhooks
 *
 * Represents a Revolut webhooks related function to BigCommerce
 * update of order changes.
 */
class WebHooks extends BaseController
{
    /**
     * Index -  Bigcommerce Webhooks will be called from bigcommerce once order status changed
     *
     *@param bc_email_id|text
     *@param key|text
     * @param mixed $bc_email_id
     * @param mixed $key
     */
    public function index($bc_email_id, $key)
    {
        helper('settingsviews');
        helper('bigcommerceorder');
        $data = file_get_contents('php://input');
        log_message('info', 'webhooksData:' . $data);
        if (! empty($data)) {
            $check_errors = json_decode($data);
            if (isset($check_errors->errors)) {
                return;
            } else {
                if (json_last_error() === 0) {
                } else {
                    return;
                }
            }
        } else {
            return;
        }
        $data       = json_decode($data, true);
        $order_data = $data['data'];
        if (isset($order_data['id'], $order_data['status'], $order_data['status']['new_status_id'])) {
            $order_id      = $order_data['id'];
            $email_id      = $bc_email_id;
            $validation_id = json_decode(base64_decode($key, true), true);
            $result        = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
            if (! empty($result)) {
                $acess_token = $result['acess_token'];
                $store_hash  = $result['store_hash'];

                $orderDetails = new \App\Models\OrderDetailsModel();
                $condition    = [
                    'order_id'=> $order_id,
                    'email_id'=> $email_id,
                    'token_validation_id'=> $validation_id,
                ];
                $details = $orderDetails->getData($condition);
                if ($details['status']) {
                    $result_order_det = $details['data'];
                    if ($order_data['status']['new_status_id'] === 2) {
                        $this->proceedSettle($result_order_det['invoice_id']);
                    }
                }
            }
        }
    }

    /**
     * proceedSettle - validate revolut order to process capture
     *
     *@param invoice_id|text
     * @param mixed $invoice_id
     */
    public function proceedSettle($invoice_id)
    {
        if (! empty($invoice_id)) {
            $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
            $result_refund       = $orderPaymentDetails->getSingleOrderDetails($invoice_id);
            if (isset($result_refund[0]) && ($result_refund[0]['type'] === 'MANUAL') && ($result_refund[0]['settlement_status'] !== 'COMPLETED')) {
                $payment_details = json_decode(str_replace('\\', '', $result_refund[0]['api_response']), true);

                if (isset($payment_details['id'])) {
                    $status = $this->captureFunds($payment_details);
                }
            }
        }
    }

    /**
     * captureFunds - capture api call in revolut and return the status
     *
     *@param array|request
     * @param mixed $request
     */
    public function captureFunds($request)
    {
        $status = false;
        helper('bigcommerceorder');
        helper('curl');
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $condition           = [
            'order_id' => $request['merchant_order_ext_ref'],
        ];
        $db_resp = $orderPaymentDetails->getData($condition);
        $invalidCondition = ($db_resp['status'] == false);
        if ($invalidCondition) {
            return $status;
        }
        $orderDetails = $db_resp['data'];

        $clientDetails = \bigcommerceorder_helper::getClientDetails($orderDetails['email_id'], $orderDetails['token_validation_id']);
        $invalidCondition = $invalidCondition && empty($clientDetails);
        if ($invalidCondition) {
            return $status;
        }
        $payment_option = $clientDetails['payment_option'];

        $paymentURL   = getenv('revolut.SANDBOX_API_URL');
        $api_key      = $clientDetails['revolut_api_key_test'];
        $is_test_live = $clientDetails['is_test_live'];
        if ($is_test_live === '1') {
            $paymentURL = getenv('revolut.PROD_API_URL');
            $api_key    = $clientDetails['revolut_api_key'];
        }
        // paymet Request

        $capture = '{"amount": "' . ($orderDetails['total_amount'] * 100) . '"}';
        $url     = $paymentURL . '/api/1.0/orders/' . $request['id'] . '/capture';
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
        ];

        $api_response = \curl_helper::APICall($url, 'POST', $capture, $headers, 'Revolut', 'SettleOrder', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
        if ($api_response['status']) {
            $resp = $api_response['data'];
            if (isset($resp['state']) && ($resp['state'] === 'COMPLETED')) {
                $status = true;
                $data   = [
                    'status'              => 'COMPLETED',
                    'settlement_status'   => $resp['state'],
                    'settlement_response' => addslashes(json_encode($resp, true)),
                    'amount_paid'         => $resp['order_amount']['value'],
                ];
            } else {
                $data = [
                    'settlement_status'   => 'Pending',
                    'settlement_response' => addslashes(json_encode($resp, true)),
                ];
            }
            $condition = [
                'order_id'      => $request['merchant_order_ext_ref'],
                'email_id'      => $clientDetails['email_id'],
                'token_validation_id' => $clientDetails['validation_id'],
            ];
            $orderPaymentDetails->updateData($condition, $data);
        }
        return $status;
    }

    /**
     * revolutWebhook - method will be called by revolut once order status changes
     *
     *@param text|bc_email_id
     *@param text|key
     * @param mixed $bc_email_id
     * @param mixed $key
     */
    public function revolutWebhook($bc_email_id, $key)
    {
        helper('settingsviews');
        helper('bigcommerceorder');
        helper('curl');
        $data = file_get_contents('php://input');
        log_message('info', 'webhooksData:' . $data);

        $email_id      = $bc_email_id;
        $validation_id = json_decode(base64_decode($key, true), true);

        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
        $invalidCondition = empty($clientDetails);
        if ($invalidCondition) {
            return;
        }
        $apiData = json_decode($data, true);
        $invalidCondition = ! (isset($apiData['event'], $apiData['order_id'], $apiData['merchant_order_ext_ref']));
        if ($invalidCondition) {
            return;
        }

        $paymentURL   = getenv('revolut.SANDBOX_API_URL');
        $api_key      = $clientDetails['revolut_api_key_test'];
        $is_test_live = $clientDetails['is_test_live'];
        if ($is_test_live === '1') {
            $paymentURL = getenv('revolut.PROD_API_URL');
            $api_key    = $clientDetails['revolut_api_key'];
        }

        $event      = $apiData['event'];
        $revOrderId = $apiData['order_id'];
        $invoice_id = $apiData['merchant_order_ext_ref'];

        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $orderDetails        = $orderPaymentDetails->getSingleOrderDetails($invoice_id);

        $invalidCondition = $invalidCondition && ! count($orderDetails) > 0;
        if ($invalidCondition) {
            return;
        }
        $orderDetails = $orderDetails[0];

        $url     = $paymentURL . '/api/1.0/orders/' . $revOrderId;
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
        ];
        $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'Revolut', 'RetrieveOrder', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
        if ($api_response['status']) {
            $resp = $api_response['data'];
            if ($orderDetails['type'] === 'AUTOMATIC' && ($orderDetails['status'] === 'PROCESSING' || $orderDetails['status'] === 'AUTHORISED')) {
                if ($resp['state'] === 'COMPLETED') {
                    $amount = 0;
                    if (isset($resp['order_amount']['value'])) {
                        $amount = $resp['order_amount']['value'];
                    }
                    $data = [
                        'amount_paid'  => $amount,
                        'status'       => $resp['state'],
                        'api_response' => addslashes($body),
                    ];

                    $condition = [
                        'id' => $orderDetails['id'],
                    ];
                    $orderPaymentDetails->updateData($condition, $data);
                }
            } elseif ($orderDetails['type'] === 'MANUAL' && ($orderDetails['status'] === 'PROCESSING')) {
                if ($resp['state'] === 'AUTHORISED') {
                    $amount = 0;
                    if (isset($resp['order_amount']['value'])) {
                        $amount = $resp['order_amount']['value'];
                    }
                    $data = [
                        'amount_paid'  => $amount,
                        'status'       => $resp['state'],
                        'api_response' => addslashes($body),
                    ];
                    $builderupdate->update($data);
                    $condition = [
                        'id' => $orderDetails['id'],
                    ];
                    $orderPaymentDetails->updateData($condition, $data);
                }
            }
        }
    }
}

//status ids
/*"1"=>Pending
"2"=>Shipped
"3"=>Partially Shipped
"4" selected="true"=>Refunded
"5"=>Cancelled
"6"=>Declined
"7"=>Awaiting Payment
"8"=>Awaiting Pickup
"9"=>Awaiting Shipment
"10"=>Completed
"11"=>Awaiting Fulfillment
"12"=>Manual Verification Required
"13"=>Disputed
"14"=>Partially Refunded*/
