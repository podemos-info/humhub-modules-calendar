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
 * Date: 23.07.2017
 * Time: 23:00
 */

namespace humhub\modules\calendar\controllers;


use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\calendar\models\CalendarEntryType;
use humhub\modules\calendar\permissions\ManageEntry;
use Yii;
use humhub\modules\calendar\models\DefaultSettings;
use humhub\modules\content\components\ContentContainerController;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use humhub\modules\calendar\controllers\ExternalSourceController;
use humhub\modules\calendar\models\CalendarExternalSource;
use humhub\modules\calendar\models\SnippetModuleSettings;

class ContainerConfigController extends ContentContainerController
{
    public $adminOnly = true;

    public function getAccessRules()
    {
        return [
          ['permission' => [ManageSpaces::class, ManageEntry::class]]
        ];
    }

    public function actionIndex()
    {
        $model = new DefaultSettings(['contentContainer' => $this->contentContainer]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        return $this->render('@calendar/views/common/defaultConfig', [
            'model' => $model
        ]);
    }

    public function actionResetConfig()
    {
        $model = new DefaultSettings(['contentContainer' => $this->contentContainer]);
        $model->reset();
        $this->view->saved();
        return $this->render('@calendar/views/common/defaultConfig', [
            'model' => $model
        ]);
    }

    public function actionTypes()
    {
        $typeDataProvider = new ActiveDataProvider([
            // TODO: replace with findByContainer with includeGlobal
            'query' => CalendarEntryType::find()->andWhere(['or',
                ['content_tag.contentcontainer_id' => $this->contentContainer->contentcontainer_id],
                'content_tag.contentcontainer_id IS NULL',
            ])
        ]);

        return $this->render('@calendar/views/common/typesConfig', [
            'typeDataProvider' => $typeDataProvider,
            'createUrl' => $this->contentContainer->createUrl('/calendar/container-config/edit-type'),
            'contentContainer' => $this->contentContainer
        ]);
    }

    public function actionEditType($id = null)
    {
        if($id) {
            $entryType = CalendarEntryType::find()->where(['id' => $id])->andWhere(['contentcontainer_id' => $this->contentContainer->contentcontainer_id])->one();
        } else {
            $entryType = new CalendarEntryType($this->contentContainer);
        }

        if(!$entryType) {
            throw new HttpException(404);
        }

        if($entryType->load(Yii::$app->request->post()) && $entryType->save()) {
            $this->view->saved();
            return $this->htmlRedirect($this->contentContainer->createUrl('/calendar/container-config/types'));
        }

        return $this->renderAjax('/common/editTypeModal', ['model' => $entryType]);
    }

    public function actionDeleteType($id)
    {
        $this->forcePostRequest();

        $entryType = CalendarEntryType::find()->where(['id' => $id])->andWhere(['contentcontainer_id' => $this->contentContainer->contentcontainer_id])->one();

        if(!$entryType) {
            throw new HttpException(404);
        }

        $entryType->delete();

        return $this->htmlRedirect($this->contentContainer->createUrl('/calendar/container-config/types'));
    }

    /**
     * Calendar Configuration Action for Admins
     */
    public function actionSources()
    {
        $external_sources = CalendarExternalSource::find()->contentContainer($this->contentContainer)->all();

        //$typeDataProvider = CalendarExternalSource::find()->contentContainer($this->contentContainer)->all();
        return $this->render('@calendar/views/common/sourcesConfig', array(
                    'contentContainer' => $this->contentContainer,
                    'external_sources' => $external_sources,
        ));
    }
    
    /**
     * Action that renders the view to add or edit an external source.<br />
     * The request has to provide the id of the external source to edit in the url parameter 'external_source_id'.
     * @see views/global/editExternalSource.php
     * @throws HttpException 404, if the logged in User misses the rights to access this view.
     */
    public function actionSourceEdit()
    {
        $external_source_id = (int) Yii::$app->request->get('external_source_id');
        $external_source = CalendarExternalSource::find()->contentContainer($this->contentContainer)->where(array('calendar_external_source.id' => $external_source_id))->one();

        if ($external_source == null) {
            $external_source = new CalendarExternalSource;
            $external_source->content->container = $this->contentContainer;
        }
            $external_source->valid = 1;
        if ($external_source->load(Yii::$app->request->post()) && $external_source->validate() && $external_source->save()) {
            $this->redirect($this->contentContainer->createUrl('/calendar/external-source/index'));
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
    public function actionSourceDelete()
    {
        $external_source_id = (int) Yii::$app->request->get('external_source_id');
        $external_source = CalendarExternalSource::find()->contentContainer($this->contentContainer)->where(array('calendar_external_source.id' => $external_source_id))->one();

        if ($external_source == null) {
            throw new HttpException(404, Yii::t('CalendarModule.base', 'Requested external source could not be found.'));
        }

        $external_source->delete();

        $this->redirect($this->contentContainer->createUrl('/calendar/external-source/index'));
    }

}
