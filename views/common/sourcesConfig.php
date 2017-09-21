<?php

/**
 * View to list and manage content container external sources.
 *
 * @uses $external_sources an array of the external sources to show.
 * @uses $accesslevel the access level of the user currently logged in.
 */
use yii\helpers\Html;
use yii\helpers\Url;
use humhub\widgets\ModalButton;
use humhub\modules\calendar\widgets\ContainerConfigMenu;
use humhub\modules\calendar\widgets\GlobalConfigMenu;

?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('CalendarModule.config', '<strong>Calendar</strong> module configuration'); ?></div>

    <?php if($contentContainer === null) : ?>
        <?= 
            GlobalConfigMenu::widget() 
        ?>
    <?php else: ?>
        <?= 
            ContainerConfigMenu::widget()
        ?>
    <?php endif; ?>

    <div class="panel-body">
        <div class="clearfix">
            <h4>
                <?= Yii::t('CalendarModule.views_external_source_index', 'External sources Configuration'); ?>
                <? echo Html::a(Yii::t('CalendarModule.views_external_source_index', 'Add External Source'), $contentContainer->createUrl('/calendar/external-source/edit', ['external_source_id' => -1]), array('class' => 'btn btn-primary')); ?>
            </h4>
            <div class="help-block">
                <?= Yii::t('CalendarModule.views_external_source_index', 'Here you can manage your external sources.') ?>
            </div>

        </div>


        <div id="calendar-empty-txt" <?php if (empty(!$external_sources)) echo 'style="visibility:hidden; display:block"' ?>>   <?php echo Yii::t('CalendarModule.base', 'There have been no external sources added to this user or space yet.') ?> <i class="fa fa-frown-o"></i><br/><br/>
        </div>

        <div class="list-view">
            <?php foreach ($external_sources as $external_source) { ?>
                <div data-key="2" id="calendar-external-source_<?php echo $external_source->id ?>">
                    <div class="media" style="margin-top:5px;">
                        <div class="media-body">
                            <div class="input-group">
                                <span class="form-control">
                                    <?php echo Html::encode($external_source->name); ?>
                                </span>
                                <div class="input-group-addon">
                                    <?php
                                        echo \humhub\widgets\ModalConfirm::widget(array(
                                                'uniqueID' => 'modal_external-sourcedelete_' . $external_source->id,
                                                'linkOutput' => 'a',
                                                'title' => Yii::t('CalendarModule.base', '<strong>Confirm</strong> external source deleting'),
                                                'message' => Yii::t('CalendarModule.base', 'Do you really want to delete this external source? All related events will be lost!'),
                                                'buttonTrue' => Yii::t('CalendarModule.base', 'Delete'),
                                                'buttonFalse' => Yii::t('CalendarModule.base', 'Cancel'),
                                                'linkContent' => '<span class="deleteButton btn btn-xs btn-danger"><i class="fa fa-times"></i></span>',
                                                'linkHref' => $contentContainer->createUrl("/calendar/external-source/delete", array('external_source_id' => $external_source->id)),
                                                'confirmJS' => 'function() {
                                                                    $("#calendar-external-source_' . $external_source->id . '").remove();
                                                                    $("#calendar-widget-external-source_' . $external_source->id . '").remove();
                                                                }'
                                        ));
                                    ?>
                                    <?php
                                        echo Html::a('<i class="fa fa-pencil"></i>', $contentContainer->createUrl('/calendar/external-source/edit', ['external_source_id' => $external_source->id]), array('title' => Yii::t('CalendarModule.views_external_source_index', 'Edit External Source'), 'class' => 'btn-xs btn btn-primary')) . ' ';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
</div>
