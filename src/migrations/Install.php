<?php
namespace heroic\craftcacheinvalidator\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->db->tableExists('{{%invalidator_paths}}')) {
            $this->createTable('{{%invalidator_paths}}', [
                'id' => $this->primaryKey(),
                'path' => $this->string()->notNull(),
                'timestamp' => $this->integer()->notNull(),
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%invalidator_paths}}');
    }
}