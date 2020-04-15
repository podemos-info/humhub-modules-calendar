<?php

use yii\db\Migration;
use humhub\modules\calendar\models\CalendarExternalSource;
use humhub\modules\external_calendar\models\ExternalCalendar;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\external_calendar\models\ICalSync;

/**
 * Class m200413_094132_replace_calendar_fork_to_external_calendar
 */
class m200413_094132_replace_calendar_fork_to_external_calendar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        // ORIGINAL: calendar_external_source -> FINAL: external_calendar
        // name                               -> title
        // color                              -> color
        // ?                                  -> description [NULL]
        // url                                -> url
        // ?                                  -> time_zone [Europe/Madrid]
        // ?                                  -> version [2.0]
        // / (If google get from url ?)       -> cal_name
        // ?                                  -> cal_scale [GREGORIAN]
        // ?                                  -> sync_mode [0] 
        // ?                                  -> event_mode [1]
        // source_type                        -> ?
        // last_update                        -> ?
        // valid                              -> ?   
               
                $external_calendar_source = CalendarExternalSource::find()
                    ->joinWith("content")
                    ->where(['content.object_model' => "humhub\modules\calendar\models\CalendarExternalSource"])
                    ->all();
                foreach ($external_calendar_source as $row) {
                    if(empty($row->color)){ $row->color = "#a34e45 " }
                    $data = ["ExternalCalendar" => [
                        "color"      => $row->color,
                        "title"      => $row->name,
                        "url"        => $row->url,
                        "public"     => "0",
                        "sync_mode"  => "0",
                        "event_mode" => "1",
                    ]]; 
                    
                    $contentContainerModel = ContentContainer::findOne(['id' => $row->content->contentcontainer_id]);
                    $contentContainer = $contentContainerModel->getPolymorphicRelation();
                    if ($contentContainer !== null) {
			//activamos el modulo para el contentcontainer
                        $contentContainer->getModuleManager()->enable("calendar");
                        $contentContainer->getModuleManager()->enable("external_calendar");
                        $model = new ExternalCalendar($contentContainer);
                        $model->content->created_by = $row->content->created_by;
                        $model->content->updated_by = $row->content->created_by;
                        if ($model->load($data)) {
                            require_once(__DIR__ . "/../vendor/johngrogg/ics-parser/src/ICal/ICal.php");
                            require_once(__DIR__ . "/../vendor/johngrogg/ics-parser/src/ICal/Event.php");
                            require_once(__DIR__ . "/../vendor/simshaun/recurr/src/Recurr/Rule.php");
                            
                                $IcalSyncModel = new ICalSync(
                                    ['calendarModel' => $model, 'skipEvents' => true]
                                );
                                $IcalSyncModel->calendarModel = $model;
                                $IcalSyncModel->skipEvents = true;
                                $IcalSyncModel->syncICal();
                                
                                $calendarModel = $IcalSyncModel->calendarModel;
                                $calendarModel->sync();
                            echo(Yii::t('ExternalCalendarModule.results', 'Calendar successfully created!'));
                        } else {
                            echo "\n no se pudieron cargar los datos en el modelo \n";
                        }
                    }
                }
                $calendar_external_source = CalendarExternalSource::deleteAll();
                $this->dropTable('calendar_external_source');
                $this->dropColumn('calendar_entry', 'external_uid');
       
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $external_calendar = ExternalCalendar::deleteAll();
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
        $this->addColumn('calendar_external_source', 'valid', $this->boolean()->defaultValue(1)); 
        $this->update('content', ['stream_channel' =>  new \yii\db\Expression('NULL')], ['object_model' => \humhub\modules\calendar\models\CalendarExternalSource::class]);
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200413_094132_replace_calendar_fork_to_external_calendar cannot be reverted.\n";

        return false;
    }
    */
}
