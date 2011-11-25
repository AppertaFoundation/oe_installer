<?php
/*
_____________________________________________________________________________
(C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
(C) OpenEyes Foundation, 2011
This file is part of OpenEyes.
OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
_____________________________________________________________________________
http://www.openeyes.org.uk	 info@openeyes.org.uk
--
*/

class TheatreController extends BaseController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/main';
 
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'users'=>array('@')
			),
			// non-logged in can't view anything
			array('deny',
				'users'=>array('?')
			),
		);
	}

	public function actionIndex()
	{
		$firm = Firm::model()->findByPk($this->selectedFirmId);

		if (empty($firm)) {
			// No firm selected, reject
			throw new CHttpException(403, 'You are not authorised to view this page without selecting a firm.');
		}

		$this->render('index', array('firm' => $firm));
	}

	public function actionPrintList()
	{
		$pdf = new TheatrePDF;

		$_POST = $_GET;

		$previousSequenceId = false;

		foreach ($this->getTheatres() as $name => $dates) {
			foreach ($dates as $date => $sessions) {
				foreach ($sessions as $session) {
					if ($session['sequenceId'] != $previousSequenceId) {
						$pdf->add_page(array(
							'theatre_no' => $name,
							'session' => substr($session['startTime'], 0, 5).' - '.substr($session['endTime'], 0, 5),
							'surgical_firm' => empty($session['firm_name']) ? 'Emergency list' : $session['firm_name'],
							'anaesthetist' => '', // todo: wtf
							'date' => date('d M Y', strtotime($date))
						));
					}

					if (!empty($session['patientId'])) {
						$procedures = !empty($session['procedures']) ? '['.$session['eye'].'] '.$session['procedures'] : 'No procedures';

						if ($session['operationComments']) {
							$procedures .= "\n".$session['operationComments'];
						}

						$pdf->add_row($session['patientHosNum'], $session['patientName'], $session['patientAge'], $session['ward'], $session['anaesthetic'], $procedures, $session['admissionTime']);
					}

					$previousSequenceId = $session['sequenceId'];
				}
			}
		}

		$pdf->build();
	}

	public function actionSearch()
	{
		$this->renderPartial('_list', array('theatres'=>$this->getTheatres()), false, true);
	}

	public function getTheatres()
	{
		$operation = new ElementOperation;
		$theatres = array();
		if (!empty($_POST)) {
			$siteId = !empty($_POST['site-id']) ? $_POST['site-id'] : null;
			$specialtyId = !empty($_POST['specialty-id']) ? $_POST['specialty-id'] : null;
			$firmId = !empty($_POST['firm-id']) ? $_POST['firm-id'] : null;
			$theatreId = !empty($_POST['theatre-id']) ? $_POST['theatre-id'] : null;
			$wardId = !empty($_POST['ward-id']) ? $_POST['ward-id'] : null;
			$emergencyList = !empty($_POST['emergency_list']) ? $_POST['emergency_list'] : null;

			if (!empty($_POST['date-start']) && !empty($_POST['date-end'])) {
				$_POST['date-filter'] = 'custom';
			}
			$filter = !empty($_POST['date-filter']) ? $_POST['date-filter'] : null;

			$service = new BookingService;

			if (
				empty($siteId) &&
				empty($specialtyId) &&
				empty($firmId) &&
				empty($theatreId) &&
				empty($wardId) &&
				empty($filter) &&
				empty($emergencyList)
			) {
				// No search options selected, e.g. the page has just loaded, so set to the session firm
				$firmId = Yii::app()->session['selected_firm_id'];
			}

			if (empty($_POST['date-start']) || empty($_POST['date-end'])) {
				$startDate = $service->getNextSessionDate($firmId);
				$endDate = $startDate;
			} else {
				$startDate = $_POST['date-start'];
																$endDate = $_POST['date-end'];
			}

			$data = $service->findTheatresAndSessions(
				$startDate,
				$endDate,
				$siteId,
				$theatreId,
				$specialtyId,
				$firmId,
				$wardId,
				$emergencyList
			);

			foreach ($data as $values) {
				$sessionTime = explode(':', $values['session_duration']);
				$sessionDuration = ($sessionTime[0] * 60) + $sessionTime[1];

				$operation->eye = $values['eye'];
				$operation->anaesthetic_type = $values['anaesthetic_type'];
				$age = floor((time() - strtotime($values['dob'])) / 60 / 60 / 24 / 365);

				$procedures = Yii::app()->db->createCommand()
					->select("GROUP_CONCAT(p.short_format SEPARATOR ', ') AS List")
					->from('proc p')
					->join('operation_procedure_assignment opa', 'opa.proc_id = p.id')
					->where('opa.operation_id = :id',
						array(':id'=>$values['operation_id']))
					->group('opa.operation_id')
					->order('opa.display_order ASC')
					->queryRow();

				$theatres[$values['name']][$values['date']][] = array(
					'operationId' => $values['operation_id'],
					'episodeId' => $values['episodeId'],
					'eventId' => $values['eventId'],
					'firm_name' => @$values['firm_name'],
					'specialty_name' => @$values['specialty_name'],
					'startTime' => $values['start_time'],
					'endTime' => $values['end_time'],
					'sequenceId' => $values['sequence_id'],
					'sessionId' => $values['session_id'],
					'sessionDuration' => $sessionDuration,
					'operationDuration' => $values['operation_duration'],
					'operationComments' => $values['comments'],
					'consultantRequired' => $values['consultant_required'],
					'overnightStay' => $values['overnight_stay'],
					'admissionTime' => $values['admission_time'],
					'timeAvailable' => $sessionDuration,
					'eye' => substr($operation->getEyeText(), 0, 1),
					'anaesthetic' => $operation->getAnaestheticAbbreviation(),
					'procedures' => $procedures['List'],
					'patientHosNum' => $values['hos_num'],
					'patientId' => $values['patientId'],
					'patientName' => $values['first_name'] . ' ' . $values['last_name'],
					'patientAge' => $age,
					'patientGender' => $values['gender'],
					'ward' => $values['ward'],
					'displayOrder' => $values['display_order'],
					'comments' => $values['session_comments'],
					'operationDuration' => $values['operation_duration'],
					'confirmed' => $values['confirmed']
				);

				if (empty($theatreTotals[$values['name']][$values['date']][$values['session_id']])) {
					$theatreTotals[$values['name']][$values['date']][$values['session_id']] = $values['operation_duration'];
				} else {
					$theatreTotals[$values['name']][$values['date']][$values['session_id']] += $values['operation_duration'];
				}
			}

			foreach ($theatres as $name => &$dates) {
				foreach ($dates as $date => &$sessions) {
					foreach ($sessions as &$session) {
						$totalBookings = $theatreTotals[$name][$date][$session['sessionId']];
						$session['timeAvailable'] = $session['sessionDuration'] - $totalBookings;
					}
				}
			}
		}

		return $theatres;
	}

	/**
	 * Generates a firm list based on a specialty id provided via POST
	 * echoes form option tags for display
	 */
	public function actionFilterFirms()
	{
		echo CHtml::tag('option', array('value'=>''),
			CHtml::encode('All firms'), true);
		if (!empty($_POST['specialty_id'])) {
			$firms = $this->getFilteredFirms($_POST['specialty_id']);

			foreach ($firms as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id),
					CHtml::encode($name), true);
			}
		}
	}

	/**
	 * Generates a theatre list based on a site id provided via POST
	 * echoes form option tags for display
	 */
	public function actionFilterTheatres()
	{
		echo CHtml::tag('option', array('value'=>''),
			CHtml::encode('All theatres'), true);
		if (!empty($_POST['site_id'])) {
			$theatres = $this->getFilteredTheatres($_POST['site_id']);

			foreach ($theatres as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id),
					CHtml::encode($name), true);
			}
		}
	}

	/**
	 * Generates a theatre list based on a site id provided via POST
	 * echoes form option tags for display
	 */
	public function actionFilterWards()
	{
		echo CHtml::tag('option', array('value'=>''),
			CHtml::encode('All wards'), true);
		if (!empty($_POST['site_id'])) {
			$wards = $this->getFilteredWards($_POST['site_id']);

			foreach ($wards as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id),
					CHtml::encode($name), true);
			}
		}
	}

	/*public function actionUpdateSessionComments()
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			if (!empty($_POST['id']) && !empty($_POST['comments'])) {
				$session = Session::model()->findByPk($_POST['id']);

				if (!empty($session)) {
					$session->comments = $_POST['comments'];
					$session->save();
				}
			}
			return true;
		}
	}*/

	public function actionSaveSessions() {
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			$display_order = 1;

			foreach ($_POST as $key => $value) {
				if (preg_match('/^operation_([0-9]+)$/',$key,$m)) {
					$booking = Booking::model()->findByAttributes(array('element_operation_id' => $m[1]));

					if (!empty($booking)) {
						$booking->admission_time = $value;
						$booking->display_order = $display_order++;
						$booking->save();
					}
				}

				if (preg_match('/^comments_([0-9]+)$/',$key,$m)) {
					$session = Session::model()->findByPk($m[1]);

					if (!empty($session)) {
						$session->comments = $value;
						$session->save();
					}
				}
			}

			return true;
		}
	}

	/*public function actionUpdateAdmitTime()
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			if (!empty($_POST['id']) && !empty($_POST['admission_time'])) {
				$booking = Booking::model()->findByAttributes(array('element_operation_id' => $_POST['id']));

				if (!empty($booking)) {
					$booking->admission_time = $_POST['admission_time'];
					$booking->save();
				}
			}
			return true;
		}
	}*/

	public function actionMoveOperation()
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			if (!empty($_POST['id'])) {
				$operation = ElementOperation::model()->findByPk($_POST['id']);

				if ($operation->move($_POST['up'])) {
					echo CJavaScript::jsonEncode(1);
				} else {
					return CJavaScript::jsonEncode(1);;
				}

				return true;
			}
		}

		return false;
	}

        public function actionConfirmOperation()
        {
                if (Yii::app()->getRequest()->getIsAjaxRequest()) {
                        if (!empty($_POST['id'])) {
                                $operation = ElementOperation::model()->findByPk($_POST['id']);

				$operation->booking->confirmed = 1;
				$operation->booking->save();

                                echo CJavaScript::jsonEncode(1);

                                return true;
                        }
                }

                return false;
        }

	/**
	 * Helper method to fetch firms by specialty ID
	 *
	 * @param integer $specialtyId
	 *
	 * @return array
	 */
	protected function getFilteredFirms($specialtyId)
	{
		$data = Yii::app()->db->createCommand()
			->select('f.id, f.name')
			->from('firm f')
			->join('service_specialty_assignment ssa', 'f.service_specialty_assignment_id = ssa.id')
			->join('specialty s', 'ssa.service_id = s.id')
			->where('ssa.specialty_id=:id',
				array(':id'=>$specialtyId))
			->queryAll();

		$firms = array();
		foreach ($data as $values) {
			$firms[$values['id']] = $values['name'];
		}

		natcasesort($firms);

		return $firms;
	}

	/**
	 * Helper method to fetch theatres by site ID
	 *
	 * @param integer $siteId
	 *
	 * @return array
	 */
	protected function getFilteredTheatres($siteId)
	{
		$data = Yii::app()->db->createCommand()
			->select('t.id, t.name')
			->from('theatre t')
			->where('t.site_id = :id',
				array(':id'=>$siteId))
			->queryAll();

		$theatres = array();
		foreach ($data as $values) {
			$theatres[$values['id']] = $values['name'];
		}

		return $theatres;
	}

	/**
	 * Helper method to fetch theatres by site ID
	 *
	 * @param integer $siteId
	 *
	 * @return array
	 */
	protected function getFilteredWards($siteId)
	{
		$data = Yii::app()->db->createCommand()
			->select('w.id, w.name')
			->from('ward w')
			->where('w.site_id = :id',
				array(':id'=>$siteId))
			->order('w.name ASC')
			->queryAll();

		$wards = array();
		foreach ($data as $values) {
			$wards[$values['id']] = $values['name'];
		}

		return $wards;
	}
}
