<?php

use yii\db\Migration;

class m170914_100055_fix_calendar_external_source_stream_channer extends Migration
{
    public function safeUp()
    {
        $this->update('content', ['stream_channel' =>  new \yii\db\Expression('NULL')], ['object_model' => \humhub\modules\calendar\models\CalendarExternalSource::class]);
    }

    public function safeDown()
    {
        echo "m170914_100055_fix_calendar_external_source_stream_channer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170914_100055_fix_calendar_external_source_stream_channer cannot be reverted.\n";

        return false;
    }
    */
}
