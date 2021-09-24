<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderRefund extends Migration
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
            'refund_status' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'refund_amount' => [
                'type' => 'FLOAT',
                'null' => false
            ],
            'api_request' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'api_response' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'order_comments' => [
                'type' => 'TEXT',
                'null' => true
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('order_refund');
    }

    public function down()
    {
        $this->forge->dropTable('order_refund');
    }
}
