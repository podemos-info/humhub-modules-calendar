<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\calendar\assets;

use yii\web\AssetBundle;

class RecurrenceFormAssets extends AssetBundle
{
    public $publishOptions = [
        'forceCopy' => true
    ];
    
    public $sourcePath = '@calendar/resources';

    public $js = [
        'js/humhub.calendar.recurrence.Form.min.js',
    ];
}
