<?php

use humhub\modules\calendar\models\CalendarExternalSource;
use yii\helpers\Html;
use humhub\compat\CActiveForm;

$this->registerJsFile('@web-static/js/colorpicker/js/bootstrap-colorpicker-modified.js', ['position' => \humhub\components\View::POS_END]);
$this->registerCssFile('@web-static/js/colorpicker/css/bootstrap-colorpicker.min.css');
?>

<div class="panel panel-default">
    <?php if ($external_source->isNewRecord) : ?>
        <div class="panel-heading"><strong><?php echo Yii::t('CalendarModule.base', 'Create')?></strong> <?php echo Yii::t('CalendarModule.base', 'new external source')?></div>
    <?php else: ?>
        <div class="panel-heading"><strong><?php echo Yii::t('CalendarModule.base', 'Edit')?></strong> <?php echo Yii::t('CalendarModule.base', 'external source')?></div>
    <?php endif; ?>
    <div class="panel-body">

        <?php
        $form = CActiveForm::begin();
        $form->errorSummary($external_source);
        ?>

        <div class="form-group">
            <?php echo $form->labelEx($external_source, 'source_type'); ?>
            <?php echo $form->dropdownList($external_source, 'source_type', CalendarExternalSource::getSourceTypes(), array('class' => 'form-control', 'rows' => '5')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($external_source, 'name'); ?>
            <?php echo $form->textField($external_source, 'name', array('class' => 'form-control')); ?>
            <?php echo $form->error($external_source, 'name'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($external_source, 'url'); ?>
            <?php echo $form->textField($external_source, 'url', array('class' => 'form-control')); ?>
            <?php echo $form->error($external_source, 'url'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($external_source, 'color'); ?>
            <div class="input-group external-source-color-chooser">
               <?= Html::activeTextInput($external_source, 'color', ['class' => 'form-control', 'id' => 'external-source-color-picker']); ?><span class="input-group-addon"><i></i></span>
            </div>
            <?php echo $form->error($external_source, 'color'); ?>
        </div>

        <?php echo Html::submitButton(Yii::t('CalendarModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php CActiveForm::end(); ?>
    </div>
    <div class="feedback"></div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('.external-source-color-chooser').colorpicker({
            format: 'hex',
            color: '<?php echo $external_source->color?>',
            horizontal: true,
            component: '.input-group-addon',
            input: '#external-source-color-picker',
        });
    });
    $("form button").click(function(e) {
        e.preventDefault();
        if(confirm("Tendras que esperar hasta 5 minutos para ver importados los eventos")){
            $("form").submit();
        }
    });
</script>
