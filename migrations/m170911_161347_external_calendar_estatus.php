<?php

use yii\db\Migration;

class m170911_161347_external_calendar_extatus extends Migration
{

    public function safeDown()
    {
        echo "m170911_161347_external_calendar_extatus cannot be reverted.\n";

        return false;
    }

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('calendar_external_source', 'valid', $this->boolean()->defaultValue(1)); 
    }
    /*
    public function down()
    {
        echo "m170911_161347_external_calendar_extatus cannot be reverted.\n";

        return false;
    }
    */
}
