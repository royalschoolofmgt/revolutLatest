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
 * Class Home
 *
 * Represents a Revolut Dashboard
 */
class Home extends BaseController
{
    /**
     * Index - default home page once app installed in Revolut
     * and valid segments.
     *
     * @return Load BigCommerce store page
     */
    public function index()
    {
        helper('settingsviews');

        $clientDetails = \settingsviews_helper::getClientDetails();
        $data          = [];
        if (! empty($clientDetails)) {
            $data['clientDetails'] = $clientDetails;

            $orderDetails = new \App\Models\OrderDetailsModel();

            $data['orderDetails'] = $orderDetails->getOrderDetails($clientDetails['email_id'], $clientDetails['validation_id']);

            return view('index', $data);
        }
    }

    /**
     * orderDetails - page to display order details of bigcommerce and revolut
     *
     *@param offset|text
     *@param limit|text
     *@param searchVal|text
     * @param mixed $offset
     * @param mixed $limit
     * @param mixed $searchVal
     *
     * @return Load Order details page
     */
    public function orderDetails($offset = 1, $limit = 15, $searchVal = '')
    {
        helper('settingsviews');
        $clientDetails       = \settingsviews_helper::getClientDetails();
        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();
        $data                = [];
        if (! empty($clientDetails)) {
            $data['clientDetails'] = $clientDetails;
            $data['offset']        = $offset;
            $data['limit']         = $limit;
            $data['searchVal']     = $searchVal;
            $data['count']         = $orderPaymentDetails->getOrderPaymentDetailsCount($clientDetails['email_id'], $clientDetails['validation_id'], $searchVal);
            $data['orderDetails']  = $orderPaymentDetails->getOrderPaymentDetails($clientDetails['email_id'], $clientDetails['validation_id'], $searchVal, $offset, $limit);

            return view('orderDetails', $data);
        }

        return redirect()->to('/');
    }

    /**
     * getOrderDetails - method to get order details of bigcommerce in custom creation page
     *
     *@param authKey|text
     * @param mixed $authKey
     *
     * @return Order details from bigcommerce
     */
    public function getOrderDetails($authKey)
    {
        $final_data           = [];
        $final_data['status'] = false;
        $final_data['data']   = [];
        $final_data['msg']    = '';
        if (! empty($authKey)) {
            $invoiceId = json_decode(base64_decode($authKey, true), true);
            helper('settingsviews');

            $orderDetails = new \App\Models\OrderDetailsModel();
            $condition    = [
                'invoice_id' => $invoiceId,
            ];
            $db_resp = $orderDetails->getData($condition);
            if ($db_resp['status']) {
                $result_order_payment = $db_resp['data'];
                $order_id             = $result_order_payment['order_id'];
                helper('bigcommerceorder');
                $orderDetails = \bigcommerceorder_helper::getBigCommerceOrder($order_id, $invoiceId);
                if ($orderDetails['status']) {
                    $final_data['status'] = true;
                    $final_data['data']   = $orderDetails['data'];
                    $final_data['msg']    = $orderDetails['msg'];
                }
            }
        }
        echo json_encode($final_data, true);

        exit;
    }
}
