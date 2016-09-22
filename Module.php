<?php

namespace humhub\modules\calendar;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\calendar\models\CalendarEntry;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\calendar\models\CalendarExternalSource;

class Module extends \humhub\modules\content\components\ContentContainerModule
{

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            Space::className(),
            User::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        return $container->createUrl('/calendar/external-source/index');
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        foreach (CalendarEntry::find()->all() as $entry) {
            $entry->delete();
        }
        foreach (CalendarExternalSource::find()->all() as $entry) {
            $entry->delete();
        }

        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        parent::disableContentContainer($container);
        foreach (CalendarEntry::find()->contentContainer($container)->all() as $entry) {
            $entry->delete();
        }
        foreach (CalendarExternalSource::find()->contentContainer($container)->all() as $entry) {
            $entry->delete();
        }
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        if ($container instanceof Space) {
            return Yii::t('CalendarModule.base', 'Adds an event calendar to this space.');
        } elseif ($container instanceof User) {
            return Yii::t('CalendarModule.base', 'Adds an calendar for private or public events to your profile and mainmenu.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new permissions\CreateEntry(),
            ];
        }
        return [];
    }

}
