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

use CodeIgniter\Model;

class MainModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function insert_data($table, $data = [])
    {
        if (! empty($data)) {
            $this->db->table($table)->insert($data);
        }

        return $this->db->insertID();
    }

    public function update_data($table, $condition = [], $data = [])
    {
        if (! empty($condition)) {
            $this->db->table($table)->update($data, $condition);
        }

        return $this->db->affectedRows();
    }

    public function delete_data($table, $condition = [])
    {
        if (! empty($condition)) {
            return $this->db->table($this->table)->delete($condition);
        }
    }

    public function get_all_data($table)
    {
        $query = $this->db->query('select * from ' . $table);

        return $query->getResult();
    }
}
