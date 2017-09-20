<?php

namespace humhub\modules\calendar\controllers;

use Yii;
use yii\helpers\Json;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\calendar\models\CalendarEntry;
use yii\web\HttpException;
use humhub\modules\content\components\ActiveQueryContent;
use humhub\modules\calendar\models\SnippetModuleSettings;
use humhub\modules\calendar\models\CalendarExternalSource;

/**
 * ExternalSourcesController allow to add external events to a user or space calendar
 *
 * @package humhub.modules_core.calendar.controllers
 * @author luke
 */
class ExternalSourceController extends ContentContainerController
{
    public function beforeAction($action)
    {
        if (!SnippetModuleSettings::instance()->showGlobalCalendarItems()) {
            throw new HttpException('500', 'Calendar module is not enabled for your user!');
        } else if ($this->contentContainer instanceof User && $this->contentContainer->id != Yii::$app->user->id) {
            throw new HttpException('500', 'Your user is not allowed to access here!');
        } else if ($this->contentContainer instanceof Space && !$this->contentContainer->isAdmin(Yii::$app->user->id)) {
            throw new HttpException(404, Yii::t('CalendarModule.base', 'You miss the rights to view or modify external sources!'));
        }

        return parent::beforeAction($action);
    }
    
    /**
     * Action that renders the view to add or edit an external source.<br />
     * The request has to provide the id of the external source to edit in the url parameter 'external_source_id'.
     * @see views/global/editExternalSource.php
     * @throws HttpException 404, if the logged in User misses the rights to access this view.
     */
    public function actionEdit()
    {
        $external_source_id = (int) Yii::$app->request->get('external_source_id');
        $external_source = CalendarExternalSource::find()->contentContainer($this->contentContainer)->where(array('calendar_external_source.id' => $external_source_id))->one();

        if ($external_source == null) {
            $external_source = new CalendarExternalSource;
            $external_source->content->container = $this->contentContainer;
        }
            $external_source->valid = 1;
        if ($external_source->load(Yii::$app->request->post()) && $external_source->validate() && $external_source->save()) {
            $this->redirect($this->contentContainer->createUrl('/calendar/container-config/sources'));
        }
        return $this->render('edit', array(
                    'external_source' => $external_source,
        ));
    }

    /**
     * Action that deletes a given external source.<br />
     * The request has to provide the id of the external source to delete in the url parameter 'external_source_id'. 
     * @throws HttpException 404, if the logged in User misses the rights to access this view.
     */
    public function actionDelete()
    {
        $external_source_id = (int) Yii::$app->request->get('external_source_id');
        $external_source = CalendarExternalSource::find()->contentContainer($this->contentContainer)->where(array('calendar_external_source.id' => $external_source_id))->one();

        if ($external_source == null) {
            throw new HttpException(404, Yii::t('CalendarModule.base', 'Requested external source could not be found.'));
        }

        $external_source->delete();

        $this->redirect($this->contentContainer->createUrl('/calendar/container-config/sources'));
    }

}
