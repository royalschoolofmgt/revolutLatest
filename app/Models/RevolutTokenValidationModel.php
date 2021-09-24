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
 * Class RevolutTokenValidationModel
 *
 * Represents a token model to store bigcommerce acess token details and revolut api key details
 */
class RevolutTokenValidationModel extends MainModel
{
    protected $table = 'revolut_token_validation';

    protected $primaryKey = 'validation_id';

    protected $allowedFields = ['email_id', 'is_test_live', 'revolut_api_key_test', 'revolut_api_key', 'sellerdb', 'acess_token', 'store_hash', 'is_enable', 'payment_option'];

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
