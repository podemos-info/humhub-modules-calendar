<?php

namespace humhub\modules\calendar\controllers;

use humhub\modules\calendar\models\forms\CalendarEntryForm;
use humhub\modules\calendar\permissions\ManageEntry;
use humhub\modules\calendar\widgets\FullCalendar;
use humhub\modules\calendar\widgets\WallEntry;
use humhub\widgets\ModalClose;
use Yii;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\calendar\permissions\CreateEntry;
use humhub\modules\calendar\models\CalendarEntry;
use humhub\modules\calendar\models\CalendarEntryParticipant;

/**
 * EntryController used to display, edit or delete calendar entries
 *
 * @package humhub.modules_core.calendar.controllers
 * @author luke
 */
class EntryController extends ContentContainerController
{

    /**
     * @inheritdoc
     */
    public $hideSidebar = true;

    public function actionView($id, $cal = null)
    {
        $entry = $this->getCalendarEntry($id);

        if (!$entry) {
            throw new HttpException('404');
        }

        if ($cal) {
            $wallEntry = Yii::createObject(['class' => WallEntry::class, 'contentObject' => $entry]);
            return $this->renderAjax('modal', [
                'content' => $this->renderAjax('view', ['entry' => $entry]),
                'entry' => $entry,
                'editUrl' => $wallEntry->getEditUrl(),
                'canManageEntries' => $entry->content->canEdit() || $this->canManageEntries(),
                'contentContainer' => $this->contentContainer,
            ]);
        }

        return $this->render('view', ['entry' => $entry]);
    }

    public function actionRespond($id, $type)
    {
        $calendarEntry = $this->getCalendarEntry($id);

        if ($calendarEntry == null) {
            throw new HttpException('404');
        }

        if ($calendarEntry->canRespond()) {
            $calendarEntryParticipant = $calendarEntry->findParticipant(Yii::$app->user->getIdentity());

            if ($calendarEntryParticipant == null) {
                $calendarEntryParticipant = new CalendarEntryParticipant([
                    'user_id' => Yii::$app->user->id,
                    'calendar_entry_id' => $calendarEntry->id]);
            }

            $calendarEntryParticipant->participation_state = (int) $type;
            $calendarEntryParticipant->save();
        }

        return $this->asJson(['success' => true]);
    }

    public function actionEdit($id = null, $start = null, $end = null, $cal = null)
    {
        if (empty($id)) {
            $calendarEntryForm = new CalendarEntryForm();
            $calendarEntryForm->createNew($this->contentContainer, $start, $end);
        } else if($this->canCreateEntries()) {
            $calendarEntryForm = new CalendarEntryForm(['entry' => $this->getCalendarEntry($id)]);
        } else {
            throw new HttpException(403);
        }

        if (!$calendarEntryForm->entry) {
            throw new HttpException(404);
        } else if(!$calendarEntryForm->entry->content->canEdit()) {
            throw new HttpException(403);
        }


        if ($calendarEntryForm->load(Yii::$app->request->post()) && $calendarEntryForm->save()) {
            return ModalClose::widget(['saved' => true]);
        }

        return $this->renderAjax('edit', [
            'calendarEntryForm' => $calendarEntryForm,
            'contentContainer' => $this->contentContainer,
            'createFromGlobalCalendar' => false
        ]);
    }

    public function actionEditAjax()
    {
        $this->forcePostRequest();

        $entry = $this->getCalendarEntry(Yii::$app->request->post('id'));

        if (!$entry) {
            throw new HttpException('404', Yii::t('CalendarModule.base', "Event not found!"));
        }

        if (!($this->canManageEntries() || $entry->content->canEdit())) {
            throw new HttpException('403', Yii::t('CalendarModule.base', "You don't have permission to edit this event!"));
        }

        $entryForm = new CalendarEntryForm(['entry' => $entry]);

        if ($entryForm->updateTime(Yii::$app->request->post('start'), Yii::$app->request->post('end'))) {
            return $this->asJson(['success' => true]);
        }

        throw new HttpException(400, "Could not save! " . print_r($entry->getErrors()));
    }

    public function actionUserList()
    {
        $calendarEntry = $this->getCalendarEntry(Yii::$app->request->get('id'));

        if ($calendarEntry == null) {
            throw new HttpException('404', Yii::t('CalendarModule.base', "Event not found!"));
        }
        $state = Yii::$app->request->get('state');

        $query = User::find();
        $query->leftJoin('calendar_entry_participant', 'user.id=calendar_entry_participant.user_id AND calendar_entry_participant.calendar_entry_id=:calendar_entry_id AND calendar_entry_participant.participation_state=:state', [
            ':calendar_entry_id' => $calendarEntry->id,
            ':state' => $state
        ]);
        $query->where('calendar_entry_participant.id IS NOT NULL');

        $title = "";
        if ($state == CalendarEntryParticipant::PARTICIPATION_STATE_ACCEPTED) {
            $title = Yii::t('CalendarModule.base', 'Attending users');
        } elseif ($state == CalendarEntryParticipant::PARTICIPATION_STATE_DECLINED) {
            $title = Yii::t('CalendarModule.base', 'Declining users');
        } elseif ($state == CalendarEntryParticipant::PARTICIPATION_STATE_MAYBE) {
            $title = Yii::t('CalendarModule.base', 'Maybe attending users');
        }
        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $calendarEntry = $this->getCalendarEntry(Yii::$app->request->get('id'));

        if ($calendarEntry == null) {
            throw new HttpException('404', Yii::t('CalendarModule.base', "Event not found!"));
        }

        if (!($this->canManageEntries() ||  $calendarEntry->content->canEdit())) {
            throw new HttpException('403', Yii::t('CalendarModule.base', "You don't have permission to delete this event!"));
        }

        $calendarEntry->delete();

        if (Yii::$app->request->isAjax) {
            $this->asJson(['success' => true]);
        } else {
            return $this->redirect($this->contentContainer->createUrl('/calendar/view/index'));
        }
    }

    /**
     * Returns a readable calendar entry by given id
     *
     * @param int $id
     * @return CalendarEntry
     */
    protected function getCalendarEntry($id)
    {
        return CalendarEntry::find()->contentContainer($this->contentContainer)->readable()->where(['calendar_entry.id' => $id])->one();
    }

    /**
     * Checks the CreatEntry permission for the given user on the given contentContainer.
     * @return bool
     */
    private function canCreateEntries()
    {
        return $this->contentContainer->permissionManager->can(new CreateEntry);
    }

    /**
     * Checks the ManageEntry permission for the given user on the given contentContainer.
     *
     * Todo: After 1.2.1 use $entry->content->canEdit();
     *
     * @return bool
     */
    private function canManageEntries()
    {
        return $this->contentContainer->permissionManager->can(new ManageEntry);
    }
}
