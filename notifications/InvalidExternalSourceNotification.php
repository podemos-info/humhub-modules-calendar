<?php

namespace humhub\modules\calendar\notifications;

use Yii;
use humhub\libs\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Space;

/**
 * Notifies a user about something happend
 */
class InvalidExternalSourceNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = "calendar";

    /**
     * @inheritdoc
     */
    public function html()
    {
        $container = $this->source->content->getContainer();
        if ($container instanceof Space) {
            $containerURL = "/s/".$container->url."/calendar/external-source/edit?external_source_id=".$this->source->id;
        } else {
            $containerURL = "/u/".$container->url."/calendar/external-source/edit?external_source_id=".$this->source->id;
        }

        return Yii::t(
            'CalendarModule.views_notifications_invalidExternalSource', 
            'You have one wrong external calendar. Please review and <a style="color:blue" href="{url}">correct it</a>.', ['{url}' => $containerURL]
            );
    }
}