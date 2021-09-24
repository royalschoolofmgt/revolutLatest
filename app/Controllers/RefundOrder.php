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
 * Class RefundOrder
 *
 * Represents a Revolut Refunds
 */
class RefundOrder extends BaseController
{
    /**
     * index - page to show refund details
     *
     *@param authKey|text
     * @param mixed $authKey
     *
     * @return refund details page
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
                if ($ref_result['status']) {
                    $data['ref_result'] = $ref_result['data'];
                }

                return view('refundOrder', $data);
            }

            return redirect()->to('/');
        }

        return redirect()->to('/');
    }

    /**
     * proceedRefund - page to validate refund details of revolut
     *
     *@param invoice_id|text
     *@param refund_amount|text
     *
     * @return refund page with success or failure message
     */
    public function proceedRefund()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        $invalidCondition = empty($clientDetails) && (! $this->request->getMethod() === 'post');
        if ($invalidCondition) {
            return redirect()->to('/');
        }
        $invoice_id = $this->request->getVar('invoice_id');
        $refund_amount = $this->request->getVar('refund_amount');
        $invalidCondition = $invalidCondition && empty($invoice_id) && ! ($refund_amount > 0);
        if ($invalidCondition) {
            return redirect()->to('/');
        }
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $result_refund       = $orderPaymentDetails->getSingleOrderDetails($invoice_id);
        if (isset($result_refund[0]) && ($result_refund[0]['status'] === 'COMPLETED')) {
            $payment_details = json_decode(str_replace('\\', '', $result_refund[0]['api_response']), true);
            if (isset($payment_details['id'])) {
                $status = $this->refunds($payment_details, $refund_amount, $clientDetails['is_test_live']);
                if ($status) {
                    return redirect()->to('/refundOrder/index/' . base64_encode(json_encode($payment_details['merchant_order_ext_ref'])) . '?error=0');
                }
                return redirect()->to('/refundOrder/index/' . base64_encode(json_encode($payment_details['merchant_order_ext_ref'])) . '?error=1');
            }

            return redirect()->to('/');
        }

        return redirect()->to('/');
    }

    /**
     * refunds - method to process refund in revolut
     *
     *@param array|request
     *@param amount|text
     *@param is_test_live|text
     * @param mixed $request
     * @param mixed $amount
     * @param mixed $is_test_live
     *
     * @return update order payment details and status of refund
     */
    public function refunds($request, $amount, $is_test_live)
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
        $refund = '{"amount": "' . (sprintf('%.2f', $amount) * 100) . '", "currency": "' . $request['order_amount']['currency'] . '","merchant_order_ext_ref": "' . $request['merchant_order_ext_ref'] . '"}';

        $url     = $paymentURL . '/api/1.0/orders/' . $request['id'] . '/refund';
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
        ];
        $data = [
            'email_id'            => $clientDetails['email_id'],
            'invoice_id'          => $request['merchant_order_ext_ref'],
            'refund_status'       => 'PENDING',
            'refund_amount'       => $amount,
            'api_request'         => addslashes($refund),
            'token_validation_id' => $clientDetails['validation_id'],
        ];
        $orderRefund  = new \App\Models\OrderRefundModel();
        $r_id         = $orderRefund->insertData($data);
        $api_response = \curl_helper::APICall($url, 'POST', $refund, $headers, 'Revolut', 'RefundOrder', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
        if ($api_response['status']) {
            $resp   = $api_response['data'];
            $status = true;
            if (isset($resp['state']) && ($resp['state'] === 'COMPLETED')) {
                $udata = [
                    'refund_status' => $resp['state'],
                    'api_response'  => addslashes(json_encode($resp, true)),
                ];

                $r_data = [
                    'settlement_status' => 'REFUND',
                ];

                $condition = [
                    'order_id' => $request['merchant_order_ext_ref'],
                ];
                $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
                $orderPaymentDetails->updateData($condition, $r_data);
            } else {
                $udata = [
                    'refund_status' => 'Failed',
                    'api_response'  => addslashes(json_encode($resp, true)),
                ];
            }
            $condition = [
                'id' => $r_id,
            ];
            $orderRefund->updateData($condition, $udata);
            if (isset($resp['state']) && ($resp['state'] === 'COMPLETED')) {
                $statusResponse = $this->updateOrderStatus($clientDetails['email_id'], $r_id, $request['merchant_order_ext_ref'], $clientDetails['validation_id']);
            }
        }

        return $status;
    }

    /**
     * updateOrderStatus - to update order status and staff notes in bigcommerce
     *
     *@param email_id|text
     *@param rder_refund_id|text
     *@param invoice_id|text
     *@param text|token_validation_id
     * @param mixed $email_id
     * @param mixed $rder_refund_id
     * @param mixed $invoice_id
     * @param mixed $token_validation_id
     */
    public function updateOrderStatus($email_id, $rder_refund_id, $invoice_id, $token_validation_id)
    {
        helper('settingsviews');
        helper('bigcommerceorder');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        if (! empty($clientDetails)) {
            $orderDetails = new \App\Models\OrderDetailsModel();

            $order_details = [];
            $condition     = [
                'invoice_id'=> $invoice_id,
            ];
            $ord_result = $orderDetails->getData($condition);
            if ($ord_result['status']) {
                $order_details = $ord_result['data'];
            }
            $staff_comments       = '';
            $order_refund_details = [];

            $orderRefund = new \App\Models\OrderRefundModel();
            $condition   = [
                'invoice_id'          => $invoice_id,
                'refund_status'       => 'COMPLETED',
                'email_id'            => $clientDetails['email_id'],
                'token_validation_id' => $clientDetails['validation_id'],
            ];

            $ref_result         = $orderRefund->getAllData($condition);
            $data['ref_result'] = [];
            if ($ref_result['status']) {
                $result_or = $ref_result['data'];

                foreach ($result_or as $rk => $rv) {
                    $staff_comments .= 'Payment Number : ' . $invoice_id . ',Status : Refunded,Refunded Date : ' . $rv['created_at'] . ',Refunded Amount : ' . @$order_details['currency'] . ' ' . $rv['refund_amount'] . '     ';
                }
            }

            if (isset($order_details['order_id']) && ! empty($order_details['order_id']) && ! empty($staff_comments)) {
                $url_u = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders/' . $order_details['order_id'];

                $request_u = ['status_id' => 4, 'staff_notes' => $staff_comments];
                $request_u = json_encode($request_u, true);

                $headers = [
                    'headers' => [
                        'X-Auth-Token' => $clientDetails['acess_token'],
                        'store_hash'   => $clientDetails['store_hash'],
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ];
                helper('curl_helper');
                \curl_helper::APICall($url_u, 'PUT', $request_u, $headers, 'BigCommerce', 'UpdateOrder', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
            }
        }
    }
}
