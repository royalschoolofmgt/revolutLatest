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
 * Class bigcommerceorder_helper
 *
 * Represents a helper class to create Order in BigCommerce once paymet is success
 */
class bigcommerceorder_helper
{
    /**
     * productOptions - to retrieve product options of a product from bigcommerce
     *
     * @param $email_id(email id of user logged in)
     * @param $productId(bigcommerce product id)
     * @param $variantId(bigcommerce variant id of a product)
     * @param $token_validation_id(validation id of user logged in)
     *
     * @return product data from api call
     */
    public static function productOptions($email_id, $productId, $variantId, $token_validation_id)
    {
        $data = [];
        helper('settingsviews');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        if (! empty($clientDetails)) {
            $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/catalog/products/' . $productId . '/variants';
            $headers = [
                'headers' => [
                    'X-Auth-Token' => $clientDetails['acess_token'],
                    'store_hash'   => $clientDetails['store_hash'],
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];

            $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'BigCommerce', 'ProductOptions', $email_id, $token_validation_id, 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['data']) && count($res['data']) > 0) {
                    foreach ($res['data'] as $k => $v) {
                        if ($v['id'] === $variantId) {
                            $data = $v;
                            break;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * deleteCart - to delete cart data in bigcommerce
     *
     * @param $email_id(email id of user logged in)
     * @param $cart_id(bigcommerce cart id)
     * @param $token_validation_id(validation id of user logged in)
     */
    public static function deleteCart($email_id, $cart_id, $token_validation_id)
    {
        helper('settingsviews');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        if (! empty($clientDetails)) {
            $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/carts/' . $cart_id;
            $request = '';
            $headers = [
                'headers' => [
                    'X-Auth-Token' => $clientDetails['acess_token'],
                    'store_hash'   => $clientDetails['store_hash'],
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];

            \curl_helper::APICall($url, 'DELETE', '', $headers, 'BigCommerce', 'ClearCart', $email_id, $token_validation_id, 'application/json');
        }
    }

    /**
     * createOrder - to create order in bigcommerce
     *
     * @param $email_id(email id of user logged in)
     * @param $request(bigcommerce order request format array)
     * @param $invoice_id(reference id of 247commerce)
     * @param $token_validation_id(validation id of user logged in)
     *
     * return bigcommerce order id
     */
    public static function createOrder($email_id, $request, $invoice_id, $token_validation_id)
    {
        $bigComemrceOrderId = '';

        helper('settingsviews');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        if (! empty($clientDetails)) {
            $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders';
            $request = json_encode($request);
            $headers = [
                'headers' => [
                    'X-Auth-Token' => $clientDetails['acess_token'],
                    'store_hash'   => $clientDetails['store_hash'],
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];

            $api_response = \curl_helper::APICall($url, 'POST', $request, $headers, 'BigCommerce', 'CreateOrder', $email_id, $token_validation_id, 'application/json');
            if ($api_response['status']) {
                $res = $api_response['data'];
                if (isset($res['id'])) {
                    $data = [
                        'email_id'            => $email_id,
                        'invoice_id'          => $invoice_id,
                        'order_id'            => $res['id'],
                        'bg_customer_id'      => $res['customer_id'],
                        'reponse_params'      => addslashes(json_encode($res)),
                        'total_inc_tax'       => $res['total_inc_tax'],
                        'total_ex_tax'        => $res['total_ex_tax'],
                        'currency'            => $res['currency_code'],
                        'token_validation_id' => $token_validation_id,
                    ];
                    $orderDetails = new \App\Models\OrderDetailsModel();
                    $orderDetails->insertData($data);

                    $bigComemrceOrderId = $res['id'];

                    try {
                        helper('bigcommerceorder');
                        \bigcommerceorder_helper::updateBCCustomerStoreCredit($email_id, $token_validation_id, $invoice_id);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return $bigComemrceOrderId;
    }

    /**
     * updateOrderStatus - to update order status in bigcommerce
     *
     * @param $bigComemrceOrderId(bigcommerce order id)
     * @param $email_id(email id of user logged in)
     * @param $token_validation_id(validation id of user logged in)
     */
    public static function updateOrderStatus($bigComemrceOrderId, $email_id, $token_validation_id)
    {
        helper('settingsviews');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        if (! empty($clientDetails)) {
            $url_u     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders/' . $bigComemrceOrderId;
            $request_u = ['status_id' => 11];
            $request_u = json_encode($request_u, true);
            $headers   = [
                'headers' => [
                    'X-Auth-Token' => $clientDetails['acess_token'],
                    'store_hash'   => $clientDetails['store_hash'],
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ];

            \curl_helper::APICall($url_u, 'PUT', $request_u, $headers, 'BigCommerce', 'UpdateOrder', $email_id, $token_validation_id, 'application/json');
        }
    }

    /**
     * updateBCCustomerStoreCredit - to update store credit of logged in customer in bigcommerce
     *
     * @param $email_id(email id of user logged in)
     * @param $token_validation_id(validation id of user logged in)
     * @param $invoice_id(reference id of 247commerce)
     */
    public static function updateBCCustomerStoreCredit($email_id, $token_validation_id, $invoice_id)
    {
        helper('settingsviews');
        helper('curl');
        $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $token_validation_id);
        $invalidCondition = empty($clientDetails);
        if ($invalidCondition) {
            return;
        }

        $orderPaymentDetails = new \App\Models\OrderPaymentDetailsModel();

        $condition = [
            'order_id' => $invoice_id,
        ];
        $db_resp = $orderPaymentDetails->getData($condition);

        $invalidCondition = $invalidCondition && ($db_resp['status'] == false);

        if ($invalidCondition) {
            return;
        }
        $result_order_payment = $db_resp['data'];
        $string               = base64_decode($result_order_payment['params'], true);
        $string               = preg_replace("/[\r\n]+/", ' ', $string);
        $json                 = utf8_encode($string);
        $cartData             = json_decode($json, true);

        $grandTotal        = $cartData['grand_total'];
        $StoreCreditAmount = 0;
        $invalidCondition = $invalidCondition && (! ($cartData['cart']['customer_id'] > 0));
        if ($invalidCondition) {
            return;
        }
        $invalidCondition = $invalidCondition && (!isset($cartData['isStoreCreditApplied'])  && !($cartData['isStoreCreditApplied'] === 'true'));
        if ($invalidCondition) {
            return;
        }
        $customerData = \bigcommerceorder_helper::getBCCustomerData($email_id, $token_validation_id, $cartData['cart']['customer_id']);
        $invalidCondition = $invalidCondition && empty($customerData) && !isset($customerData['store_credit_amounts'], $customerData['store_credit_amounts'][0]['amount']);
        if ($invalidCondition) {
            return;
        }

        $StoreCreditAmount = $customerData['store_credit_amounts'][0]['amount'];
        $invalidCondition = $invalidCondition && (! ($StoreCreditAmount > 0));
        if ($invalidCondition) {
            return;
        }
        $storeCreditLeft = 0;
        if ($grandTotal > $StoreCreditAmount) {
            $storeCreditLeft = 0;
        } else {
            $storeCreditLeft = ($StoreCreditAmount - $grandTotal);
        }
        $url_u     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/customers';
        $request_u = '[
					{
						"id": ' . $cartData['cart']['customer_id'] . ',
						"store_credit_amounts": [
								{
									"amount": ' . $storeCreditLeft . '
								}
							]
					}
					]';
        $headers = [
            'headers' => [
                'X-Auth-Token' => $clientDetails['acess_token'],
                'store_hash'   => $clientDetails['store_hash'],
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        \curl_helper::APICall($url_u, 'PUT', $request_u, $headers, 'BigCommerce', 'UpdateCustomerStoreCredit', $email_id, $token_validation_id, 'application/json');
    }

    /**
     * getBigCommerceOrder - to get bigcommerce order details from bigcommerce
     *
     * @param $order_id(order id of bigcommerce)
     * @param $invoiceId(reference id of 247commerce)
     *
     *@return order Data from BigCommerce api
     */
    public static function getBigCommerceOrder($order_id, $invoiceId = '')
    {
        $final_data           = [];
        $final_data['status'] = false;
        $final_data['data']   = [];
        $final_data['msg']    = '';
        $request 			  = '';
        $response_P			  = [];
        $data 				  = [];
        helper('settingsviews');
        helper('curl');

        $invalidCondition = empty($order_id);
        if ($invalidCondition) {
            return $final_data;
        }
        $orderDetails = new \App\Models\OrderDetailsModel();
        $condition    = [
            'order_id' => $order_id,
        ];
        if (! empty($invoiceId)) {
            $condition['invoice_id'] = $invoiceId;
        }
        $db_resp = $orderDetails->getData($condition);
        $invalidCondition = $invalidCondition && ($db_resp['status'] == false);
        if ($invalidCondition) {
            return $final_data;
        }
        $result        = $db_resp['data'];
        $clientDetails = \bigcommerceorder_helper::getClientDetails($result['email_id'], $result['token_validation_id']);

        $invalidCondition = $invalidCondition && empty($clientDetails);
        if ($invalidCondition) {
            return $final_data;
        }

        $email_id      = $clientDetails['email_id'];
        $validation_id = $clientDetails['validation_id'];
        $url           = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders/' . $order_id;

        $headers = [
            'headers' => [
                'X-Auth-Token' => $clientDetails['acess_token'],
                'store_hash'   => $clientDetails['store_hash'],
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'BigCommerce', 'getOrder', $email_id, $validation_id, 'application/json');
        $invalidCondition = $invalidCondition && ($api_response['status'] == false) && ($api_response['data']['id']);
        if ($invalidCondition) {
            return $final_data;
        }
        $final_data['status'] = true;
        $final_data['data']   = $api_response['data'];

        $url2                 = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/store';
        $api_response_store   = \curl_helper::APICall($url2, 'GET', '', $headers, 'BigCommerce', 'Store', $email_id, $validation_id, 'application/json');
        if ($api_response_store['status']) {
            $res_store = $api_response_store['data'];
            if (isset($res_store['secure_url'])) {
                $final_data['data']['storeData'] = $res_store;
            }
        }

        $url3                  = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v2/orders/' . $order_id . '/products';
        $api_response_products = \curl_helper::APICall($url3, 'GET', '', $headers, 'BigCommerce', 'OrderProducts', $email_id, $validation_id, 'application/json');

        $invalidCondition = ($api_response_products['status'] == false) && ! (count($api_response_products['data'] > 0));
        if ($invalidCondition) {
            return final_data;
        }
        $response_P = $api_response_products['data'];

        foreach ($response_P as $k => $v) {
            $invalidCondition = (! isset($v['product_id']));
            if ($invalidCondition) {
                continue;
            }

            $url4                        = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/catalog/products/' . $v['product_id'] . '/images';
            $api_response_product_images = \curl_helper::APICall($url4, 'GET', '', $headers, 'BigCommerce', 'ProductImages', $email_id, $validation_id, 'application/json');

            $invalidCondition = ($api_response_product_images['status'] == false) && (! (isset($api_response_product_images['data']['data'])));
            if ($invalidCondition) {
                continue;
            }
            $response_I = $api_response_product_images['data'];
            foreach ($response_I['data'] as $k1 => $v1) {
                if ($v['product_id'] === $v1['product_id']) {
                    $b64image                                = base64_encode(file_get_contents($v1['url_thumbnail']));
                    $type                                    = pathinfo($v1['url_thumbnail'], PATHINFO_EXTENSION);
                    $response_I['data'][$k1]['encodedImage'] = 'data:image/' . $type . ';base64,' . $b64image;
                }
            }
            $response_P[$k]['productImages'] = $response_I['data'];
        }
        $final_data['data']['productsData'] = $response_P;
        return $final_data;
    }

    /**
     * getBCCustomerData- to get Customer Data from BigCommerce API
     *
     * @param text| $email_id
     * @param text| $validation_id
     * @param text| $customer_id
     *
     * @return Customer Data from BigCommerce api
     */
    public static function getBCCustomerData($email_id, $validation_id, $customer_id)
    {
        helper('settingsviews');
        helper('curl');
        $data = [];
        if (! empty($customer_id) && ! empty($email_id)) {
            $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
            if (! empty($clientDetails)) {
                $request = '';
                $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/customers?id:in=' . $customer_id . '&include=storecredit';
                $headers = [
                    'headers' => [
                        'X-Auth-Token' => $clientDetails['acess_token'],
                        'store_hash'   => $clientDetails['store_hash'],
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ];
                $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'BigCommerce', 'CustomData', $email_id, $validation_id, 'application/json');
                if ($api_response['status']) {
                    $res = $api_response['data'];
                    if (isset($res['data'])) {
                        foreach ($res['data'] as $k => $v) {
                            if (isset($v['id']) && ($v['id'] === $customer_id)) {
                                $data = $v;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * getCartData - get Cart Data from BigCommerce API
     *
     * @param text| $email_id
     * @param text| $cartId
     * @param text| $validation_id
     *
     * @return cart Data from BigCommerce api
     */
    public static function getCartData($email_id, $cartId, $validation_id)
    {
        $data = [];
        if (! empty($cartId) && ! empty($email_id)) {
            $clientDetails = \bigcommerceorder_helper::getClientDetails($email_id, $validation_id);
            if (! empty($clientDetails)) {
                $request = '';
                $url     = getenv('bigcommerceapp.STORE_URL') . $clientDetails['store_hash'] . '/v3/checkouts/' . $cartId . '?include=cart.line_items.physical_items.options,cart.line_items.digital_items.options,customer,customer.customer_group,payments,promotions.banners,cart.line_items.physical_items.category_names,cart.line_items.digital_items.category_names';

                $headers = [
                    'headers' => [
                        'X-Auth-Token' => $clientDetails['acess_token'],
                        'store_hash'   => $clientDetails['store_hash'],
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ];
                helper('curl');
                $api_response = \curl_helper::APICall($url, 'GET', '', $headers, 'BigCommerce', 'GetCartData', $clientDetails['email_id'], $clientDetails['validation_id'], 'application/json');
                if ($api_response['status']) {
                    $res = $api_response['data'];
                    if (isset($res['data'])) {
                        $data = $res['data'];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * get_client_ip - to get client ip address
     *
     * @return client ip address
     */
    public static function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        $ip = explode(',', $ipaddress);
        if (isset($ip[0])) {
            $ipaddress = $ip[0];
        }

        return $ipaddress;
    }

    /**
     * getGeoData - to get client geo location details
     *
     * @return client geo details
     */
    public static function getGeoData()
    {
        $PublicIP = get_client_ip();
        $PublicIP = explode(',', $PublicIP);
        $json     = file_get_contents("http://ipinfo.io/{$PublicIP[0]}/geo");

        return json_decode($json, true);
    }

    /**
     * getAppClientDetails - Static funtion to get BigCommerce client details.
     *
     * @param text| $email_id
     * @param text| $validation_id
     *
     * @return BigCommerce Store URL id & Email
     */
    public static function getClientDetails($email_id, $validation_id)
    {
        $data = [];
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
     * getCustomerStoreAmount - Static funtion to calculate customer store credit with xart amount.
     *
     * @param text| $email_id
     * @param text| $validation_id
     * @param text| $customer_id
     * @param text| $total_amount
     *
     * @return totalAmount
     */
    public static function getCustomerStoreAmount($email_id, $validation_id, $customer_id, $total_amount)
    {
        $totalAmount = 0;
        helper('bigcommerceorder');
        $customerData = \bigcommerceorder_helper::getBCCustomerData($email_id, $validation_id, $customer_id);
        if (! empty($customerData) && isset($customerData['store_credit_amounts'], $customerData['store_credit_amounts'][0]['amount'])) {
            $StoreCreditAmount = $customerData['store_credit_amounts'][0]['amount'];
            if ($StoreCreditAmount > 0) {
                if ($total_amount > $StoreCreditAmount) {
                    $totalAmount = ($total_amount - $StoreCreditAmount);
                } else {
                    $totalAmount = 0;
                }
            } else {
                $totalAmount = $total_amount;
            }
        } else {
            $totalAmount = $total_amount;
        }
        return $totalAmount;
    }

    /**
     * getCustomerStoreAmount - Static funtion to format bigcommerce order creation
     *
     * @param array| $email_id
     * @param array| $validation_id
     * @param array| $cartData
     * @param array| $result_order_payment
     * @param array| $pay_type
     *
     * @return createOrder array
     */
    public static function formatCreateOrder($email_id, $validation_id, $cartData, $result_order_payment, $pay_type)
    {
        $resp = [];
        $resp['status'] = false;
        $resp['data'] = [];
        helper('bigcommerceorder');
        $items_total          = 0;
        $order_products       = [];

        $invalidCondition = (! isset($cartData['cart']['line_items'])) && (! count($cartData['cart']['line_items']) > 0);
        if ($invalidCondition) {
            return $resp;
        }
        foreach ($cartData['cart']['line_items'] as $liv) {
            $cart_products = $liv;
            foreach ($cart_products as $k => $v) {
                if ($v['variant_id'] > 0) {
                    $details        = [];
                    $productOptions = \bigcommerceorder_helper::productOptions($result_order_payment['email_id'], $v['product_id'], $v['variant_id'], $result_order_payment['token_validation_id']);

                    log_message('info', 'Product variant options: ' . json_encode($productOptions));

                    $temp_option_values = $productOptions['option_values'];
                    $option_values      = [];
                    if (! empty($temp_option_values) && isset($temp_option_values[0])) {
                        foreach ($temp_option_values as $tk => $tv) {
                            $option_values[] = [
                                'id'    => $tv['option_id'],
                                'value' => (string) ($tv['id']),
                            ];
                        }
                    } else {
                        if (isset($v['options']) && ! empty($v['options'])) {
                            foreach ($v['options'] as $tk => $tv) {
                                if (isset($tv['name_id'], $tv['value_id'])) {
                                    $option_values[] = [
                                        'id'    => $tv['name_id'],
                                        'value' => (string) ($tv['value_id']),
                                    ];
                                }
                            }
                        }
                    }
                    $items_total += $v['quantity'];
                    $details = [
                        'product_id'      => $v['product_id'],
                        'quantity'        => $v['quantity'],
                        'product_options' => $option_values,
                        'price_inc_tax'   => $v['sale_price'],
                        'price_ex_tax'    => $v['sale_price'],
                        'upc'             => @$productOptions['upc'],
                        'variant_id'      => $v['variant_id'],
                    ];
                    $order_products[] = $details;
                }
            }
        }
        $checkShipping = false;
        if (count($cartData['cart']['line_items']['physical_items']) > 0 || count($cartData['cart']['line_items']['custom_items']) > 0) {
            $checkShipping = true;
        } else {
            if (count($cartData['cart']['line_items']['digital_items']) > 0) {
                $checkShipping = false;
            }
        }
        $cart_billing_address = $cartData['billing_address'];
        $billing_address      = [
            'first_name' => $cart_billing_address['first_name'],
            'last_name'  => $cart_billing_address['last_name'],
            'phone'      => $cart_billing_address['phone'],
            'email'      => $cart_billing_address['email'],
            'street_1'   => $cart_billing_address['address1'],
            'street_2'   => $cart_billing_address['address2'],
            'city'       => $cart_billing_address['city'],
            'state'      => $cart_billing_address['state_or_province'],
            'zip'        => $cart_billing_address['postal_code'],
            'country'    => $cart_billing_address['country'],
            'company'    => $cart_billing_address['company'],
        ];
        if ($checkShipping) {
            $cart_shipping_address = $cartData['consignments'][0]['shipping_address'];
            $cart_shipping_options = $cartData['consignments'][0]['selected_shipping_option'];
            $shipping_address      = [
                'first_name'      => $cart_shipping_address['first_name'],
                'last_name'       => $cart_shipping_address['last_name'],
                'company'         => $cart_shipping_address['company'],
                'street_1'        => $cart_shipping_address['address1'],
                'street_2'        => $cart_shipping_address['address2'],
                'city'            => $cart_shipping_address['city'],
                'state'           => $cart_shipping_address['state_or_province'],
                'zip'             => $cart_shipping_address['postal_code'],
                'country'         => $cart_shipping_address['country'],
                'country_iso2'    => $cart_shipping_address['country_code'],
                'phone'           => $cart_shipping_address['phone'],
                'email'           => $cart_billing_address['email'],
                'shipping_method' => $cart_shipping_options['description'],
            ];
        }
        $grandTotal        = $cartData['grand_total'];
        $totalCartPrice    = 0;
        if (isset($cartData['cart']['customer_id']) && $cartData['cart']['customer_id'] > 0) {
            if (isset($cartData['isStoreCreditApplied']) && ($cartData['isStoreCreditApplied'] === true)) {
                $totalCartPrice     = \bigcommerceorder_helper::getCustomerStoreAmount($email_id, $validation_id, $cartData['cart']['customer_id'], $totalCartPrice);
            } else {
                $totalCartPrice = $grandTotal;
            }
        } else {
            $totalCartPrice = $grandTotal;
        }

        $createOrder                = [];
        $createOrder['customer_id'] = $cartData['cart']['customer_id'];
        $createOrder['products']    = $order_products;
        if ($checkShipping) {
            $createOrder['shipping_addresses'][] = $shipping_address;
        }
        $createOrder['billing_address'] = $billing_address;
        if (isset($cartData['coupons'][0]['discounted_amount'])) {
            $createOrder['discount_amount'] = $cartData['coupons'][0]['discounted_amount'];
        }
        $createOrder['customer_message']   = $cartData['customer_message'];
        $createOrder['customer_locale']    = 'en';
        $createOrder['total_ex_tax']       = $totalCartPrice;
        $createOrder['total_inc_tax']      = $totalCartPrice;
        $createOrder['geoip_country']      = $cart_shipping_address['country'];
        $createOrder['geoip_country_iso2'] = $cart_shipping_address['country_code'];
        $createOrder['status_id']          = 0;
        $createOrder['ip_address']         = \bigcommerceorder_helper::get_client_ip();
        if ($checkShipping) {
            $createOrder['order_is_digital'] = true;
        }
        $createOrder['shipping_cost_ex_tax']  = $cartData['shipping_cost_total_ex_tax'];
        $createOrder['shipping_cost_inc_tax'] = $cartData['shipping_cost_total_inc_tax'];

        $createOrder['tax_provider_id']       = 'BasicTaxProvider';
        $createOrder['payment_method']        = $pay_type;
        $createOrder['external_source']       = '247 REVOLUT';
        $createOrder['default_currency_code'] = $cartData['cart']['currency']['code'];

        $resp['status'] = true;
        $resp['data'] = $createOrder;

        return $resp;
    }
}
