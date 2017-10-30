<?php

namespace humhub\modules\calendar;

use Yii;
use yii\helpers\Url;
use humhub\modules\calendar\widgets\UpcomingEvents;
use humhub\modules\calendar\models\CalendarExternalSource;
use humhub\modules\calendar\models\SnippetModuleSettings;

/**
 * Description of CalendarEvents
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    public static function onTopMenuInit($event)
    {
        if (SnippetModuleSettings::instantiate()->showGlobalCalendarItems()) {
            $event->sender->addItem([
                'label' => Yii::t('CalendarModule.base', 'Calendar'),
                'url' => Url::to(['/calendar/global/index']),
                'icon' => '<i class="fa fa-calendar"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'calendar' && Yii::$app->controller->id == 'global'),
                'sortOrder' => 300,
            ]);
        }
    }

    public static function onSpaceMenuInit($event)
    {
        $space = $event->sender->space;
        if ($space->isModuleEnabled('calendar')) {
            $event->sender->addItem([
                'label' => Yii::t('CalendarModule.base', 'Calendar'),
                'group' => 'modules',
                'url' => $space->createUrl('/calendar/view/index'),
                'icon' => '<i class="fa fa-calendar"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'calendar'),
            
            ]);
        }
    }

    public static function onProfileMenuInit($event)
    {
        $user = $event->sender->user;
        if ($user->isModuleEnabled('calendar')) {
            $event->sender->addItem([
                'label' => Yii::t('CalendarModule.base', 'Calendar'),
                'url' => $user->createUrl('/calendar/view/index'),
                'icon' => '<i class="fa fa-calendar"></i>',
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'calendar'),
            ]);
        }
    }

    public static function onSpaceSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $space = $event->sender->space;
        $settings = SnippetModuleSettings::instantiate();

        if ($space->isModuleEnabled('calendar')) {
            $event->sender->addWidget(UpcomingEvents::className(), ['contentContainer' => $space], ['sortOrder' => $settings->upcomingEventsSnippetSortOrder]);
        }
    }

    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $settings = SnippetModuleSettings::instantiate();

        if ($settings->showUpcomingEventsSnippet()) {
            $event->sender->addWidget(UpcomingEvents::className(), [], ['sortOrder' => $settings->upcomingEventsSnippetSortOrder]);
        }
    }

    public static function onProfileSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $user = $event->sender->user;
        if ($user != null) {
            $settings = SnippetModuleSettings::instantiate();

            if ($settings->showUpcomingEventsSnippet()) {
                $event->sender->addWidget(UpcomingEvents::className(), ['contentContainer' => $user], ['sortOrder' => $settings->upcomingEventsSnippetSortOrder]);
            }
        }
    }
    public static function onHourlyCron($event)
    {
        $controller = $event->sender;
        $controller->stdout("Sync external events... ");
        $sources = CalendarExternalSource::find()->orderBy('last_update')->where(array('valid' => 1))->limit(20);
        foreach ($sources->each() as $source) {
            $source->updateEvents();
        }
        $controller->stdout('done.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }
}
