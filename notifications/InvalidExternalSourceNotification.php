<?php

namespace humhub\modules\calendar\notifications;

use Yii;
use humhub\libs\Html;
use humhub\modules\notification\components\BaseNotification;

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
        return "You have at least one wrong external calendar. Please correct and <a style='color:blue' href='/u/".Html::encode($this->source->content->getUser()->username)."/calendar/external-source/edit?external_source_id='".Html::encode($this->source->id).">review it</a>";
    }
}