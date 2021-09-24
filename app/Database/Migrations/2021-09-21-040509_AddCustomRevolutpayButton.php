<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomRevolutpayButton extends Migration
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
            'is_enabled' => [
                'type' => 'int',
                'null' => true
            ],
            'container_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'css_prop' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'payment_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'powerby_logo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'buttoncolor' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'textcolor' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'outlinecolor' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->createTable('custom_revolutpay_button');
    }

    public function down()
    {
        $this->forge->dropTable('custom_revolutpay_button');
    }
}
