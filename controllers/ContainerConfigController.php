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
use humhub\modules\calendar\permissions\ManageEntry;
use humhub\modules\calendar\controllers\ExternalSourceController;
use humhub\modules\calendar\models\CalendarExternalSource;
use humhub\modules\calendar\models\SnippetModuleSettings;

class ContainerConfigController extends AbstractConfigController
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
          ['permission' => [ManageSpaces::class, ManageEntry::class]]
        ];
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
