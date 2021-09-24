<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApiLog extends Migration
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
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'api_url' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'api_header' => [
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
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->addKey('type');
        $this->forge->addKey('action');
        $this->forge->createTable('api_log');
    }

    public function down()
    {
        $this->forge->dropTable('api_log');
    }
}
