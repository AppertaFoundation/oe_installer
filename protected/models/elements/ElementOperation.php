<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * This is the model class for table "element_operation".
 *
 * The followings are the available columns in table 'element_operation':
 * @property string $id
 * @property string $event_id
 * @property integer $eye
 * @property string $comments
 * @property integer $total_duration
 * @property integer $consultant_required
 * @property integer $anaesthetist_required
 * @property integer $anaesthetic_type_id
 * @property integer $overnight_stay
 * @property data $decision_date
 * @property integer $schedule_timeframe
 * @property boolean $urgent
 *
 * The followings are the available model relations:
 * @property Event $event
 * @property Procedure[] $procedures
 * @property Booking $booking
 * @property CancelledOperation $cancellation
 * @property CancelledBooking $cancelledBooking
 * @property Site $site
 * @property DateLetterSent $date_letter_sent
 * @property User $user
 * @property User $usermodified
 * @property AnaestheticType $anaesthetic_type
 * @property Eye $eye
 * @property Priority $priority
 * @property ElementOperationEROD $erod
 */
class ElementOperation extends BaseEventTypeElement
{
	const CONSULTANT_NOT_REQUIRED = 0;
	const CONSULTANT_REQUIRED = 1;

	const SCHEDULE_IMMEDIATELY = 0;
	const SCHEDULE_AFTER_1MO = 1;
	const SCHEDULE_AFTER_2MO = 2;
	const SCHEDULE_AFTER_3MO = 3;

	const STATUS_PENDING = 0;
	const STATUS_SCHEDULED = 1;
	const STATUS_NEEDS_RESCHEDULING = 2;
	const STATUS_RESCHEDULED = 3;
	const STATUS_CANCELLED = 4;

	const LETTER_INVITE = 0;
	const LETTER_REMINDER_1 = 1;
	const LETTER_REMINDER_2 = 2;
	const LETTER_GP = 3;
	const LETTER_REMOVAL = 4;
	
	// these reflect an actual status, relating to actions required rather than letters sent
	const STATUS_WHITE = 0; // no action required.	the default status.
	const STATUS_PURPLE = 1; // no invitation letter has been sent
	const STATUS_GREEN1 = 2; // it's two weeks since an invitation letter was sent with no further letters going out
	const STATUS_GREEN2 = 3; // it's two weeks since 1st reminder was sent with no further letters going out
	const STATUS_ORANGE = 4; // it's two weeks since 2nd reminder was sent with no further letters going out
	const STATUS_RED = 5; // it's one week since gp letter was sent and they're still on the list
	const STATUS_NOTWAITING = null;

	public $service;

	/**
	 * Returns the static model of the specified AR class.
	 * @return ElementOperation the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'element_operation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('eye_id', 'required', 'message' => 'Please select an eye option'),
			array('eye_id', 'matchDiagnosisEye'),
			array('decision_date, total_duration', 'required'),
			array('decision_date', 'OeDateValidator', 'message' => 'Please enter a valid decision date (e.g. '.Helper::NHS_DATE_EXAMPLE.')'),
			array('eye_id, total_duration, consultant_required, anaesthetist_required, anaesthetic_type_id, overnight_stay, schedule_timeframe, priority_id', 'numerical', 'integerOnly' => true),
			array('eye_id, event_id, comments, decision_date, site_id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, event_id, eye_id, comments, total_duration, decision_date, consultant_required, anaesthetist_required, anaesthetic_type_id, overnight_stay, schedule_timeframe, priority_id, site_id', 'safe', 'on' => 'search'),
			array('anaesthetic_type_id', 'checkAnaestheticType'),
			array('consultant_required', 'checkConsultantRequired')
		);
	}
	
	/**
	 * Define date fields which should be converted when saving to (or fetching from) the database
	 */
	/*
	public function behaviors() {
		return array(
			'OeDateFormat' => array(
				'class' => 'application.behaviors.OeDateFormat',
				'date_columns' => array('decision_date'),
			),
		);
	}
	*/
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'event' => array(self::BELONGS_TO, 'Event', 'event_id'),
			'procedures' => array(self::MANY_MANY, 'Procedure', 'operation_procedure_assignment(operation_id, proc_id)', 'order' => 'display_order ASC'),
			'booking' => array(self::HAS_ONE, 'Booking', 'element_operation_id'),
			'cancellation' => array(self::HAS_ONE, 'CancelledOperation', 'element_operation_id'),
			'cancelledBooking' => array(self::HAS_ONE, 'CancelledBooking', 'element_operation_id'),
			'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
			'date_letter_sent' => array(self::HAS_ONE, 'DateLetterSent', 'element_operation_id', 'order' => 'date_letter_sent.id DESC'),
			'user' => array(self::BELONGS_TO, 'User', 'created_user_id'),
			'usermodified' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
			'anaesthetic_type' => array(self::BELONGS_TO, 'AnaestheticType', 'anaesthetic_type_id'),
			'eye' => array(self::BELONGS_TO, 'Eye', 'eye_id'),
			'priority' => array(self::BELONGS_TO, 'Priority', 'priority_id'),
			'erod' => array(self::HAS_ONE, 'ElementOperationEROD', 'element_operation_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Event',
			'eye_id' => 'Eye(s)',
			'comments' => 'Add comments',
			'total_duration' => 'Total duration',
			'consultant_required' => 'Consultant required',
			'anaesthetist_required' => 'Anaesthetist required',
			'anaesthetic_type_id' => 'Anaesthetic type',
			'overnight_stay' => 'Post operative stay',
			'decision_date' => 'Decision date',
			'schedule_timeframe' => 'Schedule timeframe',
			'priority_id' => 'Priority',
			'site_id' => 'Site'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('event_id', $this->event_id, true);
		$criteria->compare('eye_id', $this->eye_id);
		$criteria->compare('comments', $this->comments, true);
		$criteria->compare('total_duration', $this->total_duration);
		$criteria->compare('consultant_required', $this->consultant_required);
		$criteria->compare('anaesthetist_required', $this->anaesthetist_required);
		$criteria->compare('anaesthetic_type_id', $this->anaesthetic_type_id);
		$criteria->compare('overnight_stay', $this->overnight_stay);
		$criteria->compare('decision_date', $this->decision_date);
		$criteria->compare('schedule_timeframe', $this->schedule_timeframe);
		$criteria->compare('priority_id', $this->priority_id);
		$criteria->compare('site_id', $this->site_id);
		
		return new CActiveDataProvider(get_class($this), array(
				'criteria' => $criteria,
			));
	}

	/**
	 * Set default values for forms on create
	 */
	public function setDefaultOptions() {
		$patient_id = (int) $_REQUEST['patient_id'];
		$firm = Yii::app()->getController()->firm;
		$episode = Episode::getCurrentEpisodeByFirm($patient_id, $firm);
		if($episode && $episode->diagnosis) {
			$this->eye_id = $episode->eye_id;
		}
		$this->consultant_required = self::CONSULTANT_NOT_REQUIRED;
		$this->anaesthetic_type_id = 1;
		$this->overnight_stay = 0;
		$this->decision_date = date('Y-m-d', time());
		$this->total_duration = 0;
		$this->schedule_timeframe = self::SCHEDULE_IMMEDIATELY;
		$this->status = self::STATUS_PENDING;
	}

	/**
	 * Return list of options for consultant
	 * @return array
	 */
	public function getConsultantOptions()
	{
		return array(
			self::CONSULTANT_REQUIRED => 'Yes',
			self::CONSULTANT_NOT_REQUIRED => 'No',
		);
	}

	/**
	 * Return list of priority options
	 * @return array
	 */
	public function getPriorityOptions() {
		return array(
			self::ROUTINE => 'Routine',
			self::URGENT => 'Urgent'
		);
	}

	public function getBooleanText($field)
	{
		switch ($this->$field) {
			case 1:
				$text = 'Yes';
				break;
			default:
				$text = 'No';
				break;
		}

		return $text;
	}

	/**
	 * Return list of options for schedule
	 * @return array
	 */
	public function getScheduleOptions()
	{
		return array(
			self::SCHEDULE_IMMEDIATELY => 'As soon as possible',
			1 => 'Within timeframe specified by patient'
		);
	}

	/**
	 * Return list of options for schedule timeframe
	 * @return array
	 */
	public function getScheduleDelayOptions()
	{
		return array(
			self::SCHEDULE_AFTER_1MO => 'After 1 Month',
			self::SCHEDULE_AFTER_2MO => 'After 2 Months',
			self::SCHEDULE_AFTER_3MO => 'After 3 Months',
		);
	}

	public function getScheduleText()
	{
		switch ($this->schedule_timeframe) {
			case self::SCHEDULE_IMMEDIATELY:
				$text = 'Immediately';
				break;
			case self::SCHEDULE_AFTER_1MO:
				$text = 'After 1 month';
				break;
			case self::SCHEDULE_AFTER_2MO:
				$text = 'After 2 months';
				break;
			case self::SCHEDULE_AFTER_3MO:
				$text = 'After 3 months';
				break;
			default:
				$text = 'Unknown';
				break;
		}

		return $text;
	}

	/**
	 * Return list of options for overnight stay
	 * @return array
	 */
	public function getOvernightOptions()
	{
		return array(
			1 => 'Yes',
			0 => 'No',
		);
	}

	public function matchDiagnosisEye()
	{
		if (isset($_POST['ElementDiagnosis']['eye_id']) &&
			isset($_POST['ElementOperation']['eye_id'])
		) {
			$diagnosis = $_POST['ElementDiagnosis']['eye_id'];
			$operation = $_POST['ElementOperation']['eye_id'];
			if ($diagnosis != 3 &&
				$diagnosis != $operation
			) {
				$this->addError('eye_id', 'Operation eye must match diagnosis eye!');
			}
		}
	}

	protected function beforeSave()
	{
		$anaesthetistRequired = array(
			'LAC','LAS','GA'
		);
		$this->anaesthetist_required = in_array($this->anaesthetic_type->name, $anaesthetistRequired);

		if (!empty($_POST['schedule_timeframe2'])) {
			$this->schedule_timeframe = $_POST['schedule_timeframe2'];
		} else {
			$this->schedule_timeframe = self::SCHEDULE_IMMEDIATELY;
		}
		return parent::beforeSave();
	}

	protected function afterSave()
	{

		$operationId = $this->id;
		$order = 1;

		if (!empty($_POST['Procedures'])) {
			// first wipe out any existing procedures so we start from scratch
			OperationProcedureAssignment::model()->deleteAll('operation_id = :id', array(':id' => $operationId));

			foreach ($_POST['Procedures'] as $id) {
				$procedure = new OperationProcedureAssignment;
				$procedure->operation_id = $operationId;
				$procedure->proc_id = $id;
				$procedure->display_order = $order;
				if (!$procedure->save()) {
					throw new Exception('Unable to save procedure');
				}

				$order++;
			}
		}
		return parent::afterSave();
	}

	protected function afterValidate()
	{
		if (!empty($_POST['action']) && empty($_POST['Procedures'])) {
			$this->addError('procedures', 'At least one procedure must be entered');
		}

		return parent::afterValidate();
	}

	public function getMinDate()
	{
		$date = strtotime($this->event->datetime);

		if ($this->schedule_timeframe != self::SCHEDULE_IMMEDIATELY) {
			$interval = str_replace('After ', '+', $this->getScheduleText());
			$date = strtotime($interval, $date);
		}

		return $date;
	}

	public function getSessions($emergency = false)
	{
		$minDate = $this->getMinDate();
		$thisMonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
		if ($minDate < $thisMonth) {
			$minDate = $thisMonth;
		}

		$monthStart = empty($_GET['date']) ? date('Y-m-01', $minDate) : $_GET['date'];

		if (!$emergency) {
			$firmId = empty($_GET['firm']) ? $this->event->episode->firm_id : $_GET['firm'];
		} else {
			$firmId = null;
		}

		$service = $this->getBookingService();
		$sessions = $service->findSessions($monthStart, $minDate, $firmId);

		$results = array();
		foreach ($sessions as $session) {
			$date = $session['date'];
			$weekday = date('N', strtotime($date));
			$text = $this->getWeekdayText($weekday);

			$sessionTime = explode(':', $session['session_duration']);
			$session['duration'] = ($sessionTime[0] * 60) + $sessionTime[1];
			$session['time_available'] = $session['duration'] - $session['bookings_duration'];
			unset($session['session_duration'], $session['date']);

			$results[$text][$date]['sessions'][] = $session;
		}

		foreach ($results as $weekday => $dates) {
			$timestamp = strtotime($monthStart);
			$firstWeekday = strtotime(date('Y-m-t', $timestamp - (60 * 60 * 24)));
			$dateList = array_keys($dates);
			while (date('N', strtotime($dateList[0])) != date('N', $firstWeekday)) {
				$firstWeekday -= 60 * 60 * 24;
			}

			for ($weekCounter = 1; $weekCounter < 8; $weekCounter++) {
				$addDays = ($weekCounter - 1) * 7;
				$selectedDay = date('Y-m-d', mktime(0, 0, 0, date('m', $firstWeekday), date('d', $firstWeekday) + $addDays, date('Y', $firstWeekday)));
				if (in_array($selectedDay, $dateList)) {
					foreach ($dates[$selectedDay] as $sessions) {
						$totalSessions = count($sessions);
						$status = $totalSessions;

						$open = $full = 0;

						foreach ($sessions as $session) {
							if ($session['time_available'] >= $this->total_duration) {
								$open++;
							} else {
								$full++;
							}
						}
						if ($full == $totalSessions) {
							$status = 'full';
						} elseif ($full > 0 && $open > 0) {
							$status = 'limited';
						} elseif ($open == $totalSessions) {
							$status = 'available';
						}
					}
				} else {
					$status = 'closed';
				}
				$results[$weekday][$selectedDay]['status'] = $status;
			}
		}

		foreach ($results as $weekday => &$dates) {
			$dateSort = array();
			foreach ($dates as $date => $info) {
				$dateSort[] = $date;
			}

			array_multisort($dateSort, SORT_ASC, $dates);
		}

		return $results;
	}

	public function getTheatres($date, $emergency = false)
	{
		if (empty($date)) {
			throw new Exception('Date is required.');
		}

		if (empty($emergency) || $emergency == 'EMG') {
			$firmId = null;
		} else {
			$firmId = $emergency;
		}

		$service = $this->getBookingService();
		$sessions = $service->findTheatres($date, $firmId);

		$results = array();
		$names = array();
		foreach ($sessions as $session) {
			$theatre = Theatre::model()->findByPk($session['id']);

			$name = $session['name'] . ' (' . $theatre->site->short_name . ')';
			$sessionTime = explode(':', $session['session_duration']);
			$session['duration'] = ($sessionTime[0] * 60) + $sessionTime[1];
			$session['time_available'] = $session['duration'] - $session['bookings_duration'];
			$session['id'] = $session['session_id'];
			unset($session['session_duration'], $session['date'], $session['name']);

			// Add status field to indicate if session is full or not
			if ($session['time_available'] <= 0) {
				$session['status'] = 'full';
			} else {
				$session['status'] = 'available';
			}

			$session['date'] = $date;

			// Add bookable field to indicate if session can be booked for this operation
			$bookable = true;
			if($this->anaesthetist_required && !$session['anaesthetist']) {
				$bookable = false;
				$session['bookable_reason'] = 'anaesthetist';
			}
			if($this->consultant_required && !$session['consultant']) {
				$bookable = false;
				$session['bookable_reason'] = 'consultant';
			}
			$paediatric = ($this->event->episode->patient->isChild());
			if($paediatric && !$session['paediatric']) {
				$bookable = false;
				$session['bookable_reason'] = 'paediatric';
			}
			if($this->anaesthetic_type->name == 'GA' && !$session['general_anaesthetic']) {
				$bookable = false;
				$session['bookable_reason'] = 'general_anaesthetic';
			}
			$session['bookable'] = $bookable;
			$results[$name][] = $session;
			if (!in_array($name, $names)) {
				$names[] = $name;
			}

		}

		if (count($results) > 1) {
			array_multisort($names, SORT_ASC, $results);
		}

		return $results;
	}

	public function getSession($sessionId)
	{
		if (empty($sessionId)) {
			throw new Exception('Session id is invalid.');
		}
		$service = $this->getBookingService();
		$results = $service->findSession($sessionId);

		$session = $results->read();
		if (!empty($session['name'])) {
			$name = $session['name'];
			$sessionTime = explode(':', $session['session_duration']);
			$session['duration'] = ($sessionTime[0] * 60) + $sessionTime[1];
			$session['time_available'] = $session['duration'] - $session['bookings_duration'];
			unset($session['session_duration'], $session['name']);

			if ($session['time_available'] <= 0) {
				$status = 'full';
			} else {
				$status = 'available';
			}
			$session['status'] = $status;
		} else {
			$session = false;
		}

		return $session;
	}

	public function getBookingService()
	{
		return new BookingService;
	}

	public function getWeekdayText($index)
	{
		switch ($index) {
			case 1:
				$text = 'Monday';
				break;
			case 2:
				$text = 'Tuesday';
				break;
			case 3:
				$text = 'Wednesday';
				break;
			case 4:
				$text = 'Thursday';
				break;
			case 5:
				$text = 'Friday';
				break;
			case 6:
				$text = 'Saturday';
				break;
			case 7:
				$text = 'Sunday';
				break;
		}

		return $text;
	}

	public function getWardOptions($siteId, $theatreId = null)
	{
		if (empty($siteId)) {
			throw new Exception('Site id is required.');
		}
		$results = array();
		// if we have a theatre id, see if it has an associated ward
		if (!empty($theatreId)) {
			$ward = Yii::app()->db->createCommand()
				->select('t.ward_id AS id, w.name')
				->from('theatre_ward_assignment t')
				->join('ward w', 't.ward_id = w.id')
				->where('t.theatre_id = :id', array(':id' => $theatreId))
				->queryRow();

			if (!empty($ward)) {
				$results[$ward['id']] = $ward['name'];
			}
		}

		if (empty($results)) {
			// otherwise select by site and patient age/gender
			$patient = $this->event->episode->patient;

			$genderRestrict = $ageRestrict = 0;
			$genderRestrict = ('M' == $patient->gender) ? Ward::RESTRICTION_MALE : Ward::RESTRICTION_FEMALE;
			$ageRestrict = ($patient->isChild()) ? Ward::RESTRICTION_CHILD : Ward::RESTRICTION_ADULT;

			$whereSql = 's.id = :id AND
				(w.restriction & :r1 > 0) AND (w.restriction & :r2 > 0)';
			$whereParams = array(
				':id' => $siteId,
				':r1' => $genderRestrict,
				':r2' => $ageRestrict
			);

			$wards = Yii::app()->db->createCommand()
				->select('w.id, w.name')
				->from('ward w')
				->join('site s', 's.id = w.site_id')
				->where($whereSql, $whereParams)
				->queryAll();

			$results = array();

			foreach ($wards as $ward) {
				$results[$ward['id']] = $ward['name'];
			}
		}

		return $results;
	}

	public function getService()
	{
		if (empty($this->service)) {
			$this->service = new LetterOutService($this->event->episode->firm);
		}

		return $this->service;
	}

	public function getPhrase($name)
	{
		return $this->getService()->getPhrase('LetterOut', $name);
	}

	public function getCancellationText()
	{
		$text = '';
		$cancellation = $this->cancellation;
		if (!empty($cancellation)) {
			$text = "Operation Cancelled: By " . $cancellation->user->first_name;
			$text .= ' ' . $cancellation->user->last_name . ' on ' . date('F j, Y', strtotime($cancellation->cancelled_date));
			$text .= ' [' . $cancellation->cancelledReason->text . ']';
		}

		return $text;
	}

	public function getStatusText()
	{
		switch ($this->status) {
			case self::STATUS_PENDING:
				$status = 'Requires scheduling';
				break;
			case self::STATUS_SCHEDULED:
				$status = 'Scheduled';
				break;
			case self::STATUS_NEEDS_RESCHEDULING:
				$status = 'Requires rescheduling';
				break;
			case self::STATUS_RESCHEDULED:
				$status = 'Rescheduled';
				break;
			case self::STATUS_CANCELLED:
				$status = 'Cancelled';
				break;
			default:
				$status = 'Unknown status';
				break;
		}

		return $status;
	}

	/**
	 * Returns the letter status for an operation.
	 *
	 * Checks to see if it's an operation to be scheduled or an operation to be rescheduled. If it's the former it bases its calculation
	 *	 on the operation creation date. If it's the latter it bases it on the most recent cancelled_booking creation date.
		 *
	 * return int
	 */
	public function getWaitingListStatus()
	{
		if (is_null($this->getLastLetter())) {
			return self::STATUS_PURPLE; // no invitation letter has been sent
		} elseif (
			is_null($this->date_letter_sent->date_invitation_letter_sent) and
			is_null($this->date_letter_sent->date_1st_reminder_letter_sent) and
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and
			is_null($this->date_letter_sent->date_gp_letter_sent)
		) {
			return self::STATUS_PURPLE; // no invitation letter has been sent
		}

		$now = new DateTime(); $now->setTime(0,0,0); // $two_weeks_ago = $now->modify('-14 days');
		$now = new DateTime(); $now->setTime(0,0,0); // $one_week_ago = $now->modify('-7 days');

		// if the last letter was the invitation and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_invitation_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_INVITE) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_GREEN1;
		}

		// if the last letter was the 1st reminder and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_1st_reminder_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_REMINDER_1) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_GREEN2;
		}

		// if the last letter was the 2nd reminder and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_2nd_reminder_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_REMINDER_2) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_ORANGE;
		}
		// if the last letter was the gp letter and it was sent over one week ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_gp_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_GP) and ($now->getTimestamp() - $date_sent->getTimestamp() > 604800) ) {
			return self::STATUS_RED;
		}
		return null;
	}

	public function getWaitingListLetterStatus()
	{
		echo var_export($this->date_letter_sent,true);
		Yii::app()->end();
	}

	public function getLastLetter()
	{
		if (!$this->date_letter_sent) {
			return null;
		}
		if (
			!is_null($this->date_letter_sent->date_invitation_letter_sent) and 
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			is_null($this->date_letter_sent->date_1st_reminder_letter_sent) and // but no 1st reminder
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and // no 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_INVITE;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and // but no 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_REMINDER_1;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			$this->date_letter_sent->date_2nd_reminder_letter_sent and // and a 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_REMINDER_2;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			$this->date_letter_sent->date_2nd_reminder_letter_sent and // and a 2nd reminder
			$this->date_letter_sent->date_gp_letter_sent // and a gp letter
		) {
			return self::LETTER_GP;
		}
		return null;
	}

	public function getNextLetter()
	{
		if (is_null($this->getLastLetter())) {
			return self::LETTER_INVITE;
		} else {
			$lastletter = $this->getLastLetter();
			if ($lastletter == self::LETTER_INVITE) {
				return self::LETTER_REMINDER_1;	
			} elseif ($lastletter == self::LETTER_REMINDER_1) {
				return self::LETTER_REMINDER_2;
			} elseif ($lastletter == self::LETTER_REMINDER_2) {
				return self::LETTER_GP;
			} elseif ($lastletter == self::LETTER_GP) {
				return self::LETTER_REMOVAL;
			}
		}
	}

	public function getDueLetter()
	{
		$lastletter = $this->getLastLetter();
		if (!$this->getWaitingListStatus()) { // if getwaitingliststatus returns null, we're white
			return $lastletter; // no new letter is due, so we should print the last one
		}
		if ($this->getWaitingListStatus() == self::STATUS_PURPLE) {
			return self::LETTER_INVITE;
		} elseif ($this->getWaitingListStatus() == self::STATUS_GREEN1) {
			return self::LETTER_REMINDER_1;
		} elseif ($this->getWaitingListStatus() == self::STATUS_GREEN2) {
			return self::LETTER_REMINDER_2;
		} elseif ($this->getWaitingListStatus() == self::STATUS_ORANGE) {
			return self::LETTER_GP;
		} elseif ($this->getWaitingListStatus() == self::STATUS_RED) {
			return null; // possibly this should return the gp letter, though it's already been sent?
		} else {
			return null; // possibly this should return $lastletter ?
		}
	}

	// This method is based on faulty logic and should not be called.
	public function getLetterStatus()
	{
		return $this->getDueLetter();

		if ($this->status == self::STATUS_NEEDS_RESCHEDULING && !empty($this->cancelledBooking)) {
			$criteria = new CDbCriteria;
			$criteria->addCondition('element_operation_id = :eoid');
			$criteria->params = array('eoid' => $this->id);
			$criteria->order = 'id DESC';
			$criteria->limit = 1;
			$cancelledBooking = CancelledBooking::model()->find($criteria);

			$datetime = strtotime($cancelledBooking->cancelled_date);
		} else {
			$datetime = strtotime($this->event->datetime);
		}

		$now = time();
		$week = 86400 * 7;

		if ($datetime >= ($now - 2 * $week)) {
			$letterStatus = self::LETTER_INVITE;
		} elseif (
			$datetime >= ($now - 4 * $week) &&
			$datetime < ($now - 2 * $week)
		) {
			$letterStatus = self::LETTER_REMINDER_1;
		} elseif (
			$datetime >= ($now - 6 * $week) &&
			$datetime < ($now - 4 * $week)
		) {
			$letterStatus = self::LETTER_REMINDER_2;
		} elseif (
			$datetime >= ($now - 8 * $week) &&
			$datetime < ($now - 6 * $week)
		) {
			$letterStatus = self::LETTER_GP;
		} elseif (
			$datetime < ($now - 8 * $week)
		) {
			$letterStatus = self::LETTER_REMOVAL;
		}

		return $letterStatus;
	}

	public static function getLetterOptions()
	{
		return array(
			'' => 'Any',
			self::LETTER_INVITE => 'Invitation',
			self::LETTER_REMINDER_1 => '1st Reminder',
			self::LETTER_REMINDER_2 => '2nd Reminder',
			self::LETTER_GP => 'Refer to GP'
		);
	}

	public function getName() {
		if(in_array($this->booking->session->theatre->code, array('CRZ','BRZ'))) { // Not Ozurdex
			return 'Ozurdex injection';
		}
	}
	
	public function showPreopWarning() {
		$show = true;
		
		// Not Ozurdex
		if (in_array($this->booking->session->theatre->code, array('CRZ','BRZ'))) {
			$show = false;
		}
		
		// Not External in Theatre 9 / CXL
		if(($this->booking->session->theatre->code == 'CR9' || $this->booking->session->theatre->code == 'CXL') 
				&& $this->booking->session->firm->serviceSubspecialtyAssignment->subspecialty->ref_spec == 'EX') {
			$show = false;
		}
		
		return $show;
	}
	
	public function showSeatingWarning() {
		return (!in_array($this->booking->session->theatre->code, array('CRZ','BRZ'))); // Not Ozurdex
	}
	
	public function showPrescriptionWarning() {
		return (!in_array($this->booking->session->theatre->code, array('CRZ','BRZ'))); // Not Ozurdex
	}
	
	/**
	 * Get the diagnosis for this operation. Used by the booking event type template to create the admission form.
	 *
	 * @return string
	 */
	public function getDisorder()
	{
		$eventId = $this->event_id;

		$elementDiagnosis = ElementDiagnosis::model()->find('event_id = ?', array($eventId));

		if (empty($elementDiagnosis)) {
			return null;
		} else {
			return $elementDiagnosis->disorder->term;
		}
	}

	public function getDisorderEyeText() {
		$eventId = $this->event_id;

		$elementDiagnosis = ElementDiagnosis::model()->find('event_id = ?', array($eventId));

		if (empty($elementDiagnosis)) {
			return null;
		} else {
			return $elementDiagnosis->eye->adjective;
		}
	}

	/**
	 * Get list of procedures (short format) as a string
	 * @return string
	 */
	public function getProceduresString() {
		$procedures = array();
		foreach($this->procedures as $procedure) {
			$procedures[] = $procedure->term;
		}
		return implode(', ',$procedures);
	}

	/**
	 * Contact number/details for changes
	 */
	public function getWaitingListContact() {
		$changeContact = '';
		$siteId = $this->site->id;
		$serviceId = $this->event->episode->firm->serviceSubspecialtyAssignment->service->id;
		$firmCode = $this->event->episode->firm->pas_code;
		if ($this->event->episode->patient->isChild()) {
			if ($siteId == 1) {
				// City Road
				$changeContact = 'a nurse on 020 7566 2595';
			} else {
				// St. George's
				$changeContact = 'Naeela Butt on 020 8725 0060';
			}
		} else {
			switch ($siteId) {
				case 1: // City Road
					switch ($serviceId) {
						case 2: // Adnexal
							$changeContact = 'Sarah Veerapatren on 020 7566 2206/2292';
							break;
						case 4: // Cataract
							switch($firmCode)  {
								case 'STEJ': // Julian Stevens
									$changeContact = 'Joyce Carmichael on 020 7566 2205/2704';
									break;
								default:
									$changeContact = 'Ian Johnson on 020 7566 2006';
							}
							break;
						case 5: // External Disease aka Corneal
							switch($firmCode)  {
								case 'STEJ': // Julian Stevens
									$changeContact = 'Joyce Carmichael on 020 7566 2205/2704';
									break;
								default:
									$changeContact = 'Ian Johnson on 020 7566 2006';
							}
							break;
						case 6: // Glaucoma
							$changeContact = 'Karen O\'Connor on 020 7566 2056';
							//$changeContact = 'Joanna Kuzmidrowicz on 020 7566 2056';
							break;
						case 11: // Vitreoretinal
							$changeContact = 'Joanna Kuzmidrowicz on 020 7566 2004';
							//$changeContact = 'Deidre Clarke on 020 7566 2004';
							break;
						default: // Medical Retinal, Paediatric, Strabismus
							$changeContact = 'Sherry Ramos on 0207 566 2258';
					}
					break;
				case 3: // Ealing
					$changeContact = 'Valerie Giddings on 020 8967 5648';
					break;
				case 4: // Northwick Park
					$changeContact = 'Saroj Mistry on 020 8869 3161';
					break;
				case 6: // Mile End
					if ($serviceId == 4) {
						// Cataract
						$changeContact = 'Linda Haslin on 020 7566 2712';
					} else {
						$changeContact = 'Eileen Harper on 020 7566 2020';
					}
					break;
				case 7: // Potters Bar
					$changeContact = 'Sue Harney on 020 7566 2339';
					break;
				case 9: // St Anns
					$changeContact = 'Veronica Brade on 020 7566 2843';
					break;
				default: // St George's
					$changeContact = 'Naeela Butt on 020 8725 0060';
			}
		}
		return $changeContact;
	}

	/**
	 * Contact number/details for health/refuse
	 */
	public function getAdmissionContact() {
		$siteId = $this->booking->ward->site_id;
		$subspecialty = $this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty;
		if ($this->booking && $this->booking->session && $this->booking->session->firm) {
			$firmId = $this->booking->session->firm->id;
		} else {
			$firmId = null;
		}
		$contact = array(
			'refuse' => $subspecialty->name . ' Admission Coordinator on ',
			'health' => '',
		);

		switch ($siteId) {
			case 1: // City Road
				switch ($subspecialty->id) {
					case 4: // Cataract
						$contact['refuse'] .= '020 7566 2006';
						break;
					case 6: // External Disease
						$contact['refuse'] .= '020 7566 2006';
						break;
					case 7: // Glaucoma
						$contact['refuse'] .= '020 7566 2056';
						break;
					case 8: // Medical Retinal
						if($this->booking->session->theatre->code == 'CRZ') {
							$contact['refuse'] = 'Admission Coordinator on 020 7566 2311';
						} else {
							$contact['refuse'] .= '020 7566 2258';
						}
						break;
					case 11: // Paediatrics
						$contact['refuse'] = 'Paediatrics and Strabismus Admission Coordinator on 020 7566 2258';
						$contact['health'] = '0207 566 2595 and ask to speak to a nurse';
						break;
					case 13: // Refractive Laser
						$contact['refuse'] = '020 7566 2205 and ask for Joyce Carmichael';
						$contact['health'] = '020 7253 3411 X4336 and ask Laser Nurse';
						break;
					case 14: // Strabismus
						$contact['refuse'] = 'Paediatrics and Strabismus Admission Coordinator on 020 7566 2258';
						$contact['health'] = '0207 566 2595 and ask to speak to a nurse';
						break;
					case 16: // Vitreo Retinal
						$contact['refuse'] .= '020 7566 2004';
						break;
					default:
						$contact['refuse'] .= '020 7566 2206/2292';
				}
				break;
			case 3: // Ealing
				if($this->booking->session->theatre->code == 'BRZ') {
					$contact['refuse'] = 'Admission Coordinator on 020 8967 5648';
				} else {
					$contact['refuse'] .= '020 8967 5766';
					//$contact['health'] = 'Sister Kelly on 020 8967 5766';
				}
				break;
			case 4: // Northwick Park
				$contact['refuse'] .= '0203 182 4027';
				//$contact['health'] = 'Sister Titmus on 020 8869 3162';
				break;
			case 5: // St George's
				$contact['refuse'] .= '020 8725 0060';
				$contact['health'] = '020 8725 0060';
				break;
			case 6: // Mile End
				if ($firmId == 233) {
					$contact['refuse'] .= '020 7566 2020';
				} else {
					switch ($subspecialty->id) {
						case 7:	// Glaucoma
							$contact['refuse'] .= '020 7566 2020';
							//$contact['health'] = 'Eileen Harper on 020 7566 2020';
							break;
						default:
							$contact['refuse'] .= '020 7566 2712';
							//$contact['health'] = 'Linda Haslin on 020 7566 2712';
					}
				}
				break;
			case 7: // Potters Bar
				$contact['refuse'] .= '01707 646422';
				//$contact['health'] = 'Potters Bar Admission Team on 01707 646422';
				break;
			case 8: // Queen Mary's
				$contact['refuse'] .= '020 8725 0060';
				break;
			case 9: // St Anns
				$contact['refuse'] .= '020 8211 8323';
				//$contact['health'] = 'St Ann\'s Team on 020 8211 8323';
				break;
		}

		# OE-2259 special case for Allan Bruce in External Theatre 9 or CXL
		if ($this->event->episode->firm_id == 19
			&& ($this->booking->session->theatre_id == 9 || $this->booking->session->theatre_id == 25)) {
			$contact['refuse'] = '020 7566 2205';
		}

		return $contact;
	}
	
	/**
	 * Returns an array of cancelled bookings
	 *
	 * @return array
	 */
	public function getCancelledBookings()
	{
		if ($this->status == self::STATUS_PENDING || $this->status == self::STATUS_SCHEDULED) {
			// Can't be any cancelled bookings, return empty array
			return array();
		}

		$cbs = CancelledBooking::model()->findAll(
			array(
				'order' => 'id DESC',
				'condition' => 'element_operation_id = :eoid',
				'params' => array(
					':eoid' => $this->id
				)
			)
		);

		return $cbs;
	}

	/**
	 * Used by the booking event type template to format the date.
	 *
	 * @param string date
	 * @return string
	 */
	public function convertDate($date)
	{
		return date('l jS F Y', strtotime($date));
	}

	/**
	 * Used by the booking event to display the admission time (session start time minus one hour)
	 *
	 * @param string $time
	 * @return string
	 */
	public function convertTime($time)
	{
		return date('G:i:s', strtotime('-1 hour', strtotime($time)));
	}

	/**
	 * Move the operation up or down within the session
	 *
	 * @param boolean $up
	 */
	public function move($up)
	{
		$booking = $this->booking;

		$criteria=new CDbCriteria;
		$criteria->addCondition('session_id = :sid');

		if ($up) {
			// Moving up the page means moving down the display_order
			$criteria->addCondition('display_order < :do');
			$criteria->order = 'display_order DESC';
		} else {
			$criteria->addCondition('display_order > :do');
			$criteria->order = 'display_order ASC';
		}

		$criteria->params = array(':sid' => $booking->session_id, ':do' => $booking->display_order);
		$criteria->limit = 1;

		$otherBooking = Booking::model()->find($criteria);

		if (empty($otherBooking)) {
			return false;
		}

		$otherDisplayOrder = $otherBooking->display_order;

		$otherBooking->display_order = $booking->display_order;
		$booking->display_order = $otherDisplayOrder;

		if (!$booking->save()) {
			throw new SystemException('Unable to save booking: '.print_r($booking->getErrors(),true));
		}

		if (!$otherBooking->save()) {
			throw new SystemException('Unable to save booking: '.print_r($otherBooking->getErrors(),true));
		}

		return true;
	}

	public function confirmLetterPrinted($confirmto = null, $confirmdate = null) {
		// admin users can set confirmto and confirm up to a specific point, steamrollering whatever else is in there
		if (!is_null($confirmto)) {
			if (!$dls = $this->date_letter_sent) {
				$dls = new DateLetterSent;
				$dls->element_operation_id = $this->id;
			}
			if ($confirmto == self::LETTER_GP) {
				$dls->date_invitation_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_1st_reminder_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_2nd_reminder_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_gp_letter_sent = Helper::convertNHS2MySQL($confirmdate);
			}
			if ($confirmto == self::LETTER_INVITE) {
				$dls->date_invitation_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_1st_reminder_letter_sent = null;
				$dls->date_2nd_reminder_letter_sent = null;
				$dls->date_gp_letter_sent = null;
			}
			if ($confirmto == self::LETTER_REMINDER_1) {
				$dls->date_invitation_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_1st_reminder_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_2nd_reminder_letter_sent = null;
				$dls->date_gp_letter_sent = null;
			}
			if ($confirmto == self::LETTER_REMINDER_2) {
				$dls->date_invitation_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_1st_reminder_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_2nd_reminder_letter_sent = Helper::convertNHS2MySQL($confirmdate);
				$dls->date_gp_letter_sent = null;
			}
			if ($confirmto == 'noletters') {
				$dls->date_invitation_letter_sent = null;
				$dls->date_1st_reminder_letter_sent = null;
				$dls->date_2nd_reminder_letter_sent = null;
				$dls->date_gp_letter_sent = null;
			}
			$dls->save();

			OELog::log("Letter print confirmed, datelettersent=$dls->id confirmdate='$confirmdate'");

		// Only confirm if letter is actually due
		} else if ($this->getDueLetter() !== $this->getLastLetter()) {
			if ($dls = $this->date_letter_sent) {
				if ($dls->date_invitation_letter_sent == null) {
					$dls->date_invitation_letter_sent = date('Y-m-d H:i:s');
				} else if ($dls->date_1st_reminder_letter_sent == null) {
					$dls->date_1st_reminder_letter_sent = date('Y-m-d H:i:s');
				} else if ($dls->date_2nd_reminder_letter_sent == null) {
					$dls->date_2nd_reminder_letter_sent = date('Y-m-d H:i:s');
				} else if ($dls->date_gp_letter_sent == null) {
					$dls->date_gp_letter_sent = date('Y-m-d H:i:s');
				} else if ($dls->date_scheduling_letter_sent == null) {
					$dls->date_scheduling_letter_sent = date('Y-m-d H:i:s');
				}
				if (!$dls->save()) {
					throw new SystemException("Unable to update date_letter_sent record {$dls->id}: ".print_r($dls->getErrors(),true));
				}

				OELog::log("Letter print confirmed, datelettersent=$dls->id");

			} else {
				$dls = new DateLetterSent;
				$dls->element_operation_id = $this->id;
				$dls->date_invitation_letter_sent = date('Y-m-d H:i:s');
				if (!$dls->save()) {
					throw new SystemException('Unable to save new date_letter_sent record: '.print_r($dls->getErrors(),true));
				}

				OELog::log("Letter print confirmed, datelettersent=$dls->id");
			}
		}
	}

	// Ensure that we haven't changed the anaesthetic type to something that requires an anaesthetist if the patient is already booked into
	// a session that doesn't have an anaesthetist
	public function checkAnaestheticType() {
		if ($booking = $this->booking) {
			$session = $booking->session;

			if (!$session->anaesthetist && !in_array($this->anaesthetic_type->name, array('Topical','LA'))) {
				$this->addError('anaesthetic_type_id', 'Unable to change anaesthetic type to '.$this->anaesthetic_type->name.' - this operation is booked into a session without an anaesthetist.<br/>You will need to first re-schedule the operation.');
			} else if (!$session->general_anaesthetic && $this->anaesthetic_type->name == 'GA') {
				$this->addError('anaesthetic_type_id', 'Unable to change anaesthetic type to '.$this->anaesthetic_type->name.' - this operation is booked into a session without general anaesthetic.<br/>You will need to first re-schedule the operation.');
			}
		}
	}

	public function checkConsultantRequired() {
		if ($booking = $this->booking) {
			$session = $booking->session;

			if ($this->consultant_required && !$session->consultant) {
				$this->addError('consultant_required', 'Unable to set consultant required - this operation is booked into a session without a consultant.<br/>You will need to first re-schedule the operation.');
			}
		}
	}
	
	// Cancel operation
	public function cancel($reason_id, $comment = null) {
		$cancel = new CancelledOperation();
		$cancel->element_operation_id = $this->id;
		$cancel->cancelled_date = date('Y-m-d H:i:s');
		$cancel->cancelled_reason_id = $reason_id;
		$cancel->cancellation_comment = $comment;
		$this->status = self::STATUS_CANCELLED;
		if(!$cancel->save()) {
			return array(
				'result' => false,
				'errors' => $cancel->getErrors()
			);
		}
		if(!$this->save()) {
			return array(
				'result' => false,
				'errors' => $this->getErrors()
			);
		}
		OELog::log("Operation cancelled: $this->id");

		$this->event->episode->episode_status_id = 5;

		if (!$this->event->episode->save()) {
			throw new Exception('Unable to change episode status for episode '.$this->event->episode->id);
		}

		// Update event datestamp
		$event = $this->event;
		$event->datetime = date("Y-m-d H:i:s");
		$event->save();
	
		// Does the operation have a booking?
		$booking = $this->booking;
		if($booking) {
			$session = $booking->session;
			$cancellation = new CancelledBooking;
			$cancellation->element_operation_id = $this->id;
			$cancellation->date = $session->date;
			$cancellation->start_time = $session->start_time;
			$cancellation->end_time = $session->end_time;
			$cancellation->theatre_id = $session->theatre_id;
			$cancellation->cancelled_date = date('Y-m-d H:i:s');
			$cancellation->cancelled_reason_id = $reason_id;
			$cancellation->cancellation_comment = $comment;
			if (!$cancellation->save()) {
				return array(
					'result' => false,
					'errors' => $cancellation->getErrors()
				);
			}
			OELog::log("Booking cancelled: $booking->id");
			
			// Urgent notification
			if(Yii::app()->params['urgent_booking_notify_hours'] && Yii::app()->params['urgent_booking_notify_email']) {
				if(strtotime($session->date) <= (strtotime(date('Y-m-d')) + (Yii::app()->params['urgent_booking_notify_hours'] * 3600))) {
					if (!is_array(Yii::app()->params['urgent_booking_notify_email'])) {
						$targets = array(Yii::app()->params['urgent_booking_notify_email']);
					} else {
						$targets = Yii::app()->params['urgent_booking_notify_email'];
					}
					foreach ($targets as $email) {
						mail(
							$email,
							"[OpenEyes] Urgent cancellation made","A cancellation was made with a TCI date within the next 24 hours.\n\nDisorder: "
								. $this->getDisorder() . "\n\nPlease see: http://" . @$_SERVER['SERVER_NAME']
								. Yii::app()->createUrl('transport')."\n\nIf you need any assistance you can reply to this email and one of the OpenEyes support personnel will respond.",
							"From: " . Yii::app()->params['urgent_booking_notify_email_from']."\r\n"
						);
					}
				}
			}
	
			// Remove booking
			if (!$booking->delete()) {
				throw new CException('Unable to remove cancelled booking: '.print_r($booking->getErrors(), true));
			}
		}

		return array('result'=>true);
	}

	public function getInfoText() {
		$text = '';
		foreach ($this->procedures as $procedure) {
			$text .= $procedure['term']."\n";
		}
		return $text;
	}

	public function calculateEROD($booking_session_id) {
		$where = '';

		if ($this->cancelledBookings) {
			OELog::log("We have cancelled bookings so we dont set EROD");
			return false;
		} else {
			OELog::log("No cancelled bookings so we set EROD");
		}
		$service_subspecialty_assignment_id = $this->event->episode->firm->service_subspecialty_assignment_id;

		if ($this->consultant_required) {
			$where .= " and session.consultant = 1";
		}

		if ($this->event->episode->patient->isChild()) {
			$where .= " and session.paediatric = 1";

			$service_subspecialty_assignment_id = $this->event->element_operation->booking->session->firm->serviceSubspecialtyAssignment->id;
		}

		if ($this->anaesthetist_required || $this->anaesthetic_type->code == 'GA') {
			$where .= " and session.anaesthetist = 1 and session.general_anaesthetic = 1";
		}

		$lead_time_date = date('Y-m-d',strtotime($this->decision_date) + (86400 * 7 * Yii::app()->params['erod_lead_time_weeks']));

		if ($rule = ErodRule::model()->find('subspecialty_id=?',array($this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty_id))) {
			$firm_ids = array();
			foreach ($rule->items as $item) {
				if ($item->item_type == 'firm') {
					$firm_ids[] = $item->item_id;
				}
			}

			$where .= " and firm.id in (".implode(',',$firm_ids).")";
		} else {
			$where .= " and firm.service_subspecialty_assignment_id = $service_subspecialty_assignment_id";
		}

		foreach ($erod = Yii::app()->db->createCommand()->select("session.id as session_id, date, start_time, end_time, firm.name as firm_name, firm.id as firm_id, subspecialty.name as subspecialty_name, consultant, paediatric, anaesthetist, general_anaesthetic")
			->from("session")
			->join("session_firm_assignment sfa","sfa.session_id = session.id")
			->join("firm","firm.id = sfa.firm_id")
			->join("booking","booking.session_id = session.id")
			->join("element_operation","booking.element_operation_id = element_operation.id")
			->join("service_subspecialty_assignment ssa","ssa.id = firm.service_subspecialty_assignment_id")
			->join("subspecialty","subspecialty.id = ssa.subspecialty_id")
			->join("theatre","session.theatre_id = theatre.id")
			->where("session.date > '$lead_time_date' and session.status = 0 $where")
			->group("session.id")
			->order("session.date, session.start_time")
			->queryAll() as $row) {
			// removed this from the theatre join: and theatre.id != 10")		~chrisr

			$session = Session::model()->findByPk($row['session_id']);
			// if the session has no firm, under the existing booking logic it is an emergency session
			if (!$session->firm) {
				continue;
			}
			$available_time = $session->available_time;

			if ($session->id == $booking_session_id) {
				// this is so that the available_time value saved below is accurate
				$available_time -= $this->total_duration;
			}

			if ($available_time >= $this->total_duration) {
				$erod = new ElementOperationEROD;
				$erod->element_operation_id = $this->id;
				$erod->session_id = $row['session_id'];
				$erod->session_date = $row['date'];
				$erod->session_start_time = $row['start_time'];
				$erod->session_end_time = $row['end_time'];
				$erod->firm_id = $row['firm_id'];
				$erod->consultant = $row['consultant'];
				$erod->paediatric = $row['paediatric'];
				$erod->anaesthetist = $row['anaesthetist'];
				$erod->general_anaesthetic = $row['general_anaesthetic'];
				$erod->session_duration = $session->duration;
				$erod->total_operations_time = $session->total_operations_time;
				$erod->available_time = $available_time;

				if (!$erod->save()) {
					throw new Exception('Unable to save EROD: '.print_r($erod->getErrors(),true));
				}

				break;
			}
		}
	}
}
