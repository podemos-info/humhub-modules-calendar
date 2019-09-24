<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 17.07.2017
 * Time: 21:02
 */

namespace humhub\modules\calendar\widgets;

use humhub\modules\content\helpers\ContentContainerHelper;
use Yii;
use humhub\modules\calendar\interfaces\CalendarService;
use humhub\widgets\SettingsTabs;

class ContainerConfigMenu extends SettingsTabs
{
    public $contentContainer;

    public function getFirstVisibleItem()
    {
        foreach ($this->items as $item) {
            if(!isset($item['visible']) || $item['visible'] === true) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        \humhub\modules\calendar\assets\Assets::register($this->getView());
        $this->contentContainer = ContentContainerHelper::getCurrent();

        if(!$this->contentContainer && !Yii::$app->user->isGuest) {
            $this->contentContainer = Yii::$app->user->identity;
        }

        if($this->contentContainer) {
            $this->initItems();
        }

        parent::init();
    }

    public function initItems()
    {
        /* @var $calendarService CalendarService */
        $calendarService =  Yii::$app->getModule('calendar')->get(CalendarService::class);

        $this->items = [
            [
                'label' => Yii::t('CalendarModule.widgets_GlobalConfigMenu', 'Defaults'),
                'url' => $this->contentContainer->createUrl('/calendar/container-config/index'),
                'active' => $this->isCurrentRoute('calendar', 'container-config', 'index'),
                'visible' => Yii::$app->user->isAdmin()
            ],
            [
                'label' => Yii::t('CalendarModule.widgets_GlobalConfigMenu', 'Event Types'),
                'url' => $this->contentContainer->createUrl('/calendar/container-config/types'),
                'active' => $this->isCurrentRoute('calendar', 'container-config', 'types'),
                'visible' => Yii::$app->user->isAdmin()
            ],
            [
                'label' => Yii::t('CalendarModule.views_external_source_index', 'External Sources'),
                'url' => $contentContainer->createUrl('/calendar/container-config/sources'),
                'active' => $this->isCurrentRoute('calendar', 'container-config', 'sources')
            ]
        ];

        if(!empty($calendarService->getCalendarItemTypes($this->contentContainer))) {
            $this->items[] = [
                'label' => Yii::t('CalendarModule.widgets_GlobalConfigMenu', 'Other Calendars'),
                'url' => $this->contentContainer->createUrl('/calendar/container-config/calendars'),
                'active' => $this->isCurrentRoute('calendar', 'container-config', 'calendars'),
                'visible' => Yii::$app->user->isAdmin()
            ];
        }
    }

}
