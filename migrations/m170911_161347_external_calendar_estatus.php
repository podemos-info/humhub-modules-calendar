<?php

use yii\db\Migration;

class m170911_161347_external_calendar_estatus extends Migration
{

    public function safeDown()
    {
        echo "m170911_161347_external_calendar_extatus cannot be reverted.\n";

        return false;
    }

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $schema = Yii::$app->db->getTableSchema("calendar_external_source", true);
        if (is_null($schema->getColumn("valid"))){
            $this->addColumn('calendar_external_source', 'valid', $this->boolean()->defaultValue(1)); 
        }
    }
    /*
    public function down()
    {
        echo "m170911_161347_external_calendar_extatus cannot be reverted.\n";

        return false;
    }
    */
}
