<?php

namespace humhub\modules\calendar\models;

use Yii;
use DateTime;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\calendar\models\CalendarEntry;
use vcalendar;

/**
 * This is the model class for table "calendar_external_source"./
 * 
 * @package humhub.modules.calendar.models
 * The followings are the available columns in table 'calendar_external_source':
 * @property integer $id
 * @property string $source_type
 * @property string $name
 * @property string $url
 * @property string $color
 * @property string $last_update
 */
class CalendarExternalSource extends \humhub\modules\content\components\ContentActiveRecord
{
    const SOURCE_TYPE_ICAL = 1;
    public $autoAddToWall = false;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'calendar_external_source';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            [['last_update'], 'safe'],
            array(['name', 'url', 'color'], 'safe'),
            array(['source_type', 'name', 'url'], 'required'),
        );
    }

    public function afterDelete()
    {
        CalendarEntry::deleteAll(["external_source_id"=>$this->id]);
        parent::afterDelete();
    }

    public function getUrl()
    {
        return $this->content->container->createUrl('calendar/global');
    }

    public function getContentName()
    {
        return 'Calendar External Source';
    }
    
    public function getContentDescription()
    {
        return $this->title;
    }    

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'source_type' => Yii::t('CalendarModule.base', 'Source Type'),
            'name' => Yii::t('CalendarModule.base', 'Name'),
            'url' => Yii::t('CalendarModule.base', 'URL'),
            'color' => Yii::t('CalendarModule.base', 'Color'),
            'last_update' => Yii::t('CalendarModule.base', 'Last Update')
        );
    }

    public static function getSourceTypes()
    {
        return array(
            self::SOURCE_TYPE_ICAL => Yii::t('CalendarModule.base', 'iCal'),
        );
    }

    public function updateEvents()
    {
        if ($this->source_type==self::SOURCE_TYPE_ICAL)
            $this->iCalUpdateEvents();
        $this->last_update = Yii::$app->formatter->asDateTime(new DateTime('-1 minute'), 'php:c'); // avoid date sync with server delay
        $this->save();
    }

    private function iCalUpdateEvents() {
	require_once __DIR__."/../libs/iCalcreator.php";
	
	//load file into lines array
	$ical = file($this->url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($this->last_update)
		$last_update = strtotime($this->last_update);
	else
		$last_update = 0;
	
	//browse lines array to update only last modified events and retrieve all event uids 
	$last_element_num_line = -1;
	$step = 0;
	$uids = [];
	foreach ($ical as $num_line => $line) {
		if (substr_compare($line, "UID:", 0, 4) == 0) {
			$uids []= substr($line, 4);
		} elseif ($step<2 && substr_compare($line, "LAST-MODIFIED:", 0, 14) == 0) {
			$event_last_modified = strtotime(substr($line, 14));
			if ($last_update>=$event_last_modified)
				$step = $step==0 ? 3 : 2;
			else
				$step = 1;
			
		} elseif ($step<3 && substr_compare($line, "END:", 0, 4) == 0) {
			if ($line !== "END:VCALENDAR") $last_element_num_line = $num_line;
			if ($step>1) $step = 3;
		}
	}
	
	//only update entries if there is any recent change
	if ($last_element_num_line!==-1) {
		$tmp_file = tempnam(sys_get_temp_dir(), 'ical');
		$tmp_ical = implode("\n", array_slice($ical, 0, $last_element_num_line+1))."\nEND:VCALENDAR\n";
		file_put_contents($tmp_file, $tmp_ical);
		$this->iCalParseTmpFile($tmp_file);
	}
	//delete events not included in ical file
	CalendarEntry::deleteAll(['and', ["external_source_id"=>$this->id], ['not in', 'external_uid', $uids]]);
    }

    private function iCalParseTmpFile($path){
	$user = $container = $this->content->container;
	$public = false;
	if ($container instanceof Space) {
		$public = $container->getDefaultContentVisibility();
		$user = User::findOne(['id'=>$this->content->user_id]);
	}
	Yii::$app->user->setIdentity($user);

	$config = array( "unique_id" => "ical{$this->id}", "filename" => basename($path), "directory"=>dirname($path));
	$vcalendar = new vcalendar( $config ); 
	$vcalendar->parse();
        while( $event = $vcalendar->getComponent('vevent')) {
		$dtstart = $event->getProperty("dtstart");
		$dtend = $event->getProperty("dtend");
		$all_day = !isset($dtstart["hour"]);
		if ($all_day) {
			$start_date = date("Y-m-d H:i:s", mktime(0, 0, 0, $dtstart['month'], $dtstart['day'], $dtstart['year']));
			$end_date = date("Y-m-d H:i:s", mktime(23, 59, 59, $dtend['month'], $dtend['day']-1, $dtend['year']));
		} else {
			$start_date = date('Y-m-d H:i:s', gmmktime($dtstart['hour'], $dtstart['min'], $dtstart['sec'], $dtstart['month'], $dtstart['day'], $dtstart['year']));
			$end_date = date('Y-m-d H:i:s', gmmktime($dtend['hour'], $dtend['min'], $dtend['sec'], $dtend['month'], $dtend['day'], $dtend['year']));
		}
		$uid = $event->getProperty("uid"); 
		$entry = CalendarEntry::findOne(["external_source_id"=>$this->id, "external_uid"=>$uid]);
		if (!$entry) {
			$entry = new CalendarEntry([
				'participation_mode' => CalendarEntry::PARTICIPATION_MODE_NONE,
				'color' => $this->color,
				'external_source_id' => $this->id,
				'external_uid' => $uid
			]);
		}
		$entry->content->container = $container;
		$entry->title = $event->getProperty("summary");
		$entry->description = $event->getProperty("description");
		$entry->start_datetime = $start_date;
		$entry->end_datetime = $end_date;
		$entry->all_day = $all_day;
		$entry->is_public = $public;
		$entry->validate();
		$entry->save();
	}
   }
}
