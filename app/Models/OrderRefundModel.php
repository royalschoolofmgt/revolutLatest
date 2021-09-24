<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

/**
 * Class OrderRefundModel
 *
 * Represents a refund model to store revolut refund details
 */
class OrderRefundModel extends MainModel
{
    protected $table = 'order_refund';

    protected $primaryKey = 'id';

    protected $allowedFields = ['email_id', 'token_validation_id', 'invoice_id', 'refund_status', 'refund_amount', 'api_request', 'api_response', 'order_comments'];

    /**
     * getData. used to retrieve single data from table based on conditional query
     *
     * @param $condition(array)
     */
    public function getData($condition)
    {
        $resp            = [];
        $resp['status']  = false;
        $resp['data']    = [];
        $tokenValidation = $this;
        if (! empty($condition)) {
            foreach ($condition as $k => $v) {
                $tokenValidation->where($k, $v);
            }
            $data = $tokenValidation->first();
            if (! empty($data)) {
                $resp['status'] = true;
                $resp['data']   = $data;
            }
        }

        return $resp;
    }

    /**
     * getAllData. used to retrieve total data from table based on conditional query
     *
     * @param $condition(array)
     */
    public function getAllData($condition)
    {
        $resp            = [];
        $resp['status']  = false;
        $resp['data']    = [];
        $tokenValidation = $this;
        if (! empty($condition)) {
            foreach ($condition as $k => $v) {
                $tokenValidation->where($k, $v);
            }
            $data = $tokenValidation->findAll();
            if (! empty($data)) {
                $resp['status'] = true;
                $resp['data']   = $data;
            }
        }

        return $resp;
    }

    /**
     * updateData. used to update data in table using condition
     *
     * @param $condition(array)
     * @param $data(array)
     */
    public function updateData($condition = [], $data = [])
    {
        return $this->update_data($this->table, $condition, $data);
    }

    /**
     * insertData. used to insert data in table
     *
     * @param $data(array)
     */
    public function insertData($data = [])
    {
        return $this->insert_data($this->table, $data);
    }
}
