<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Add247RevolutWebhooks extends Migration
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
            'webhook_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'scope' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'destination' => [
                'type' => 'TEXT',
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
        $this->forge->createTable('247_revolut_webhooks');
    }

    public function down()
    {
        $this->forge->dropTable('247_revolut_webhooks');
    }
}
