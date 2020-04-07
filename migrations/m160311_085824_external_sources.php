<?php

use yii\db\Migration;

class m160311_085824_external_sources extends Migration
{
    public function up()
    {
        if (Yii::$app->db->getTableSchema("calendar_external_source", true) === null) {
            $this->createTable('calendar_external_source', array(
                'id' => 'pk',
                'source_type' => 'tinyint(4) NOT NULL',
                'name' => 'string NOT NULL',
                'url' => 'string NOT NULL',
                'color' => 'string NOT NULL',
                'last_update' => 'datetime NULL',
            ));
            $this->addColumn('calendar_entry', 'external_source_id', $this->integer()); 
            $this->addColumn('calendar_entry', 'external_uid', $this->string()); 
            $this->createIndex('unique_external_entry', 'calendar_entry', 'external_source_id,external_uid', true);
        }
    }

    public function down()
    {
        $this->dropTable('calendar_external_source');
        $this->dropColumn('calendar_entry', 'external_source_id');
        $this->dropColumn('calendar_entry', 'external_uid');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
