<?php

/**
 * View to list and manage content container external sources.
 *
 * @uses $external_sources an array of the external sources to show.
 * @uses $accesslevel the access level of the user currently logged in.
 */
use yii\helpers\Html;
use yii\helpers\Url;
humhub\modules\calendar\ExternalSourcesAssets::register($this);

?>

<div id="calendar-empty-txt" <?php if (empty($external_sources)) echo 'style="visibility:visible; display:block"' ?>><?php echo Yii::t('CalendarModule.base', 'There have been no external sources added to this user or space yet.') ?> <i class="fa fa-frown-o"></i><br/><br/></div>

<div class="calendar-external-sources">
    <?php foreach ($external_sources as $external_source) { ?>
        <div id="calendar-external-source_<?php echo $external_source->id ?>"
             class="panel panel-default panel-calendar-external-source" data-id="<?php echo $external_source->id ?>">
            <div class="panel-heading">
                <div class="heading">
                    <?php echo Html::encode($external_source->name); ?>
                    <div class="calendar-edit-controls calendar-editable">
                        <?php
                            echo \humhub\widgets\ModalConfirm::widget(array(
                                    'uniqueID' => 'modal_external-sourcedelete_' . $external_source->id,
                                    'linkOutput' => 'a',
                                    'class' => 'deleteButton btn btn-xs btn-danger" title="' . Yii::t('CalendarModule.base', 'Delete external source'),
                                    'title' => Yii::t('CalendarModule.base', '<strong>Confirm</strong> external source deleting'),
                                    'message' => Yii::t('CalendarModule.base', 'Do you really want to delete this external source? All related events will be lost!'),
                                    'buttonTrue' => Yii::t('CalendarModule.base', 'Delete'),
                                    'buttonFalse' => Yii::t('CalendarModule.base', 'Cancel'),
                                    'linkContent' => '<i class="fa fa-trash-o"></i>',
                                    'linkHref' => $contentContainer->createUrl("/calendar/external-source/delete", array('external_source_id' => $external_source->id)),
                                    'confirmJS' => 'function() {
								$("#calendar-external-source_' . $external_source->id . '").remove();
								$("#calendar-widget-external-source_' . $external_source->id . '").remove();
							}'
                            ));
                            echo Html::a('<i class="fa fa-pencil"></i>', $contentContainer->createUrl('/calendar/external-source/edit', ['external_source_id' => $external_source->id]), array('title' => Yii::t('CalendarModule.views_external_source_index', 'Edit External Source'), 'class' => 'btn btn-xs btn-primary')) . ' ';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<div class="calendar-add-external-source calendar-editable"><?php echo Html::a(Yii::t('CalendarModule.views_external_source_index', 'Add External Source'), $contentContainer->createUrl('/calendar/external-source/edit', ['external_source_id' => -1]), array('class' => 'btn btn-primary')); ?></div>
