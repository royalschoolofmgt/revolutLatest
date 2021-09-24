<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderPaymentDetails extends Migration
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
            'order_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'cart_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'payment_type' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['MANUAL','AUTOMATIC'],
                'default' => 'AUTOMATIC',
            ],
            'total_amount' => [
                'type' => 'FLOAT',
                'null' => false
            ],
            'amount_paid' => [
                'type' => 'FLOAT',
                'null' => false
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => false
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['PENDING','CONFIRMED','FAILED','SUSPENDED','PROCESSING','AUTHORISED','CANCELLED','COMPLETED'],
                'default' => 'PENDING',
            ],
            'settlement_status' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
                'default' => 'PENDING',
            ],
            'params' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'api_request' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'api_response' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'settlement_response' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->addKey('order_id');
        $this->forge->createTable('order_payment_details');
    }

    public function down()
    {
        $this->forge->dropTable('order_payment_details');
    }
}
