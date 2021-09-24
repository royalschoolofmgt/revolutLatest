<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevolutScripts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'script_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'script_email_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'token_validation_id' => [
                'type' => 'int',
                'null' => true
            ],
            'script_filename' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'script_code' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],
            'status' => [
                'type' => 'int',
                'null' => true,
                'default' => '0',
            ],
            'api_response' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
        'created_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('script_id');
        $this->forge->addKey('email_id');
        $this->forge->addKey('token_validation_id');
        $this->forge->createTable('revolut_scripts');
    }

    public function down()
    {
        $this->forge->dropTable('revolut_scripts');
    }
}
