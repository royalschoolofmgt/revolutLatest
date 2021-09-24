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
 * Class SettleOrder
 *
 * Represents a Revolut Settlements
 */
class SettleOrder extends BaseController
{
    /**
     * index - page to show capture details
     *
     *@param authKey|text
     * @param mixed $authKey
     *
     * @return capture details page
     */
    public function index($authKey)
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        $data          = [];
        if (! empty($clientDetails) && ! empty($authKey)) {
            $data['clientDetails'] = $clientDetails;
            $invoice_id            = json_decode(base64_decode($authKey, true));
            if (! empty($invoice_id)) {
                $orderPaymentDetails  = new \App\Models\OrderPaymentDetailsModel();
                $result               = $orderPaymentDetails->getSingleOrderDetails($invoice_id);
                $data['orderDetails'] = [];
                if (count($result) > 0) {
                    $data['orderDetails'] = $result[0];
                }

                $orderRefund = new \App\Models\OrderRefundModel();
                $condition   = [
                    'invoice_id'          => $invoice_id,
                    'refund_status'       => 'COMPLETED',
                    'email_id'            => $clientDetails['email_id'],
                    'token_validation_id' => $clientDetails['validation_id'],
                ];

                $ref_result         = $orderRefund->getAllData($condition);
                $data['ref_result'] = [];
                if (count($ref_result) > 0) {
                    $data['ref_result'] = $ref_result;
                }

                return view('settleOrder', $data);
            }

            return redirect()->to('/');
        }

        return redirect()->to('/');
    }

    /**
     * proceedSettle - method to validate capture details of revolut
     *
     *@param invoice_id|text
     *
     * @return home page with success or failure message
     */
    public function proceedSettle()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        $invalidCondition = empty($clientDetails) && (! $this->request->getMethod() === 'post');
        if ($invalidCondition) {
            return redirect()->to('/');
        }
        $invoice_id = $this->request->getVar('invoice_id');
        $invalidCondition = $invalidCondition && empty($invoice_id);
        if ($invalidCondition) {
            return redirect()->to('/');
        }
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $result_refund       = $orderPaymentDetails->getSingleOrderDetails($invoice_id);
        if (isset($result_refund[0]) && ($result_refund[0]['type'] === 'MANUAL') && ($result_refund[0]['settlement_status'] !== 'COMPLETED')) {
            $payment_details = json_decode(str_replace('\\', '', $result_refund[0]['api_response']), true);
            if (isset($payment_details['id'])) {
                $status = $this->captureFunds($payment_details, $clientDetails['is_test_live']);
                if ($status) {
                    return redirect()->to('/settleOrder/index/' . base64_encode(json_encode($payment_details['merchant_order_ext_ref'])) . '?error=0');
                }
                return redirect()->to('/settleOrder/index/' . base64_encode(json_encode($payment_details['merchant_order_ext_ref'])) . '?error=1');
            }
            return redirect()->to('/');
        }
        return redirect()->to('/');
    }

    /**
     * captureFunds - method to process capture in revolut
     *
     *@param array|request
     *@param is_test_live|text
     * @param mixed $request
     * @param mixed $is_test_live
     *
     * @return update order payment details and status of refund
     */
    public function captureFunds($request, $is_test_live)
    {
        $status = false;
        helper('curl');
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $condition           = [
            'order_id' => $request['merchant_order_ext_ref'],
        ];
        $db_resp_opd = $orderPaymentDetails->getData($condition);
        $invalidCondition = ($db_resp_opd['status'] == false);
        if ($invalidCondition) {
            return $status;
        }

        $orderDetails = $db_resp_opd['data'];

        $condition = [
                'email_id'      => $orderDetails['email_id'],
                'validation_id' => $orderDetails['token_validation_id'],
            ];
        $tokenValidation = new \App\Models\RevolutTokenValidationModel();
        $db_resp         = $tokenValidation->getData($condition);

        $invalidCondition = $invalidCondition && ($db_resp['status'] == false);
        if ($invalidCondition) {
            return $status;
        }

        $clientDetails  = $db_resp['data'];
        $payment_option = $clientDetails['payment_option'];
        $paymentURL = getenv('revolut.SANDBOX_API_URL');
        $api_key    = $clientDetails['revolut_api_key_test'];
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
                $statusResponse = $this->updateOrderStatus($request['merchant_order_ext_ref']);
            } else {
                $data = [
                    'settlement_status'   => 'Pending',
                    'settlement_response' => addslashes(json_encode($resp, true)),
                ];
            }
            $condition = [
                'order_id'            => $request['merchant_order_ext_ref'],
                'email_id'            => $clientDetails['email_id'],
                'token_validation_id' => $clientDetails['validation_id'],
            ];
            $orderPaymentDetails->updateData($condition, $data);
        }

        return $status;
    }

    /**
     * updateOrderStatus - to update order status and staff notes in bigcommerce
     *
     *@param invoice_id|text
     * @param mixed $invoice_id
     */
    public static function updateOrderStatus($invoice_id)
    {
        helper('settingsviews');
        helper('curl');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails) && ! empty($invoice_id)) {
            $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
            $result              = $orderPaymentDetails->getSingleOrderDetails($invoice_id);
            if (count($result) > 0) {
                $result         = $result[0];
                $url_u          = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders/' . $result['order_id'];
                $staff_comments = 'Payment Number : ' . $invoice_id . ',Status : Settled,Settlement Date : ' . date('Y-m-d h:i A');
                $request_u      = ['status_id' => 2, 'staff_notes' => $staff_comments];
                $request_u      = json_encode($request_u, true);

                $headers = [
                    'headers' => [
                        'X-Auth-Token' => $clientDetails['acess_token'],
                        'store_hash'   => $clientDetails['store_hash'],
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ];

                \curl_helper::APICall($url_u, 'PUT', $request_u, $headers, 'BigCommerce', 'UpdateOrder', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
            }
        }
    }
}
