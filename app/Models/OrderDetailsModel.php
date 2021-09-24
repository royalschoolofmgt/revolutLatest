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
 * Class OrderDetailsModel
 *
 * Represents a order model to store bigcommerce order details
 */
class OrderDetailsModel extends MainModel
{
    protected $table = 'order_details';

    protected $primaryKey = 'id';

    protected $allowedFields = ['email_id', 'token_validation_id', 'invoice_id', 'order_id', 'bg_customer_id', 'reponse_params', 'total_inc_tax', 'total_ex_tax', 'currency'];

    /**
     * getOrderDetails used to retrieve data from two tables based on client
     *
     * @param $email_id
     * @param $token_validation_id
     */
    public function getOrderDetails($email_id, $token_validation_id)
    {
        $builder = $this->db->table('order_payment_details opd');
        $builder->select('opd.api_response,opd.id,opd.settlement_status,opd.type,opd.amount_paid,opd.email_id as email,opd.order_id as invoice_id,od.order_id,opd.status,opd.currency,opd.total_amount,opd.created_at');
        $builder->join('order_details od', 'opd.order_id = od.invoice_id', 'left');
        $builder->where('opd.email_id', $email_id);
        $builder->where('opd.token_validation_id', $token_validation_id);
        $builder->orderBy('opd.id', 'DESC');
        $builder->limit(10);
        $query = $builder->get();

        return $query->getResultArray();
    }

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
