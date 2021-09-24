<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevolutTokenValidation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'validation_id' => [
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
            'is_test_live' => [
                'type' => 'int',
                'null' => false,
                'default' => '0'
            ],
            'revolut_api_key_test' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'revolut_api_key' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'sellerdb' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'acess_token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'store_hash' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'is_enable' => [
                'type' => 'int',
                'null' => false,
                'default' => '0'
            ],
            'payment_option' => [
                'type' => 'ENUM',
                'constraint' => ['CFO','CFS'],
                'default' => 'CFO',
            ],
            'updated_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('validation_id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('store_hash');
        $this->forge->createTable('revolut_token_validation');
    }

    public function down()
    {
        $this->forge->dropTable('revolut_token_validation');
    }
}
