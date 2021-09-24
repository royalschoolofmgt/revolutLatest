<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderDetails extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'token_validation_id' => [
                'type' => 'int',
                'null' => true
            ],
            'invoice_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'order_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'bg_customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'reponse_params' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'total_inc_tax' => [
                'type' => 'FLOAT',
                'null' => false
            ],
            'total_ex_tax' => [
                'type' => 'FLOAT',
                'null' => false
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => false
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->addKey('invoice_id');
        $this->forge->addKey('order_id');
        $this->forge->createTable('order_details');
    }

    public function down()
    {
        $this->forge->dropTable('order_details');
    }
}
