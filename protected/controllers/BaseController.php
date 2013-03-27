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
 * A base controller class that helps display the firm dropdown and the patient name.
 * It is extended by all non-admin controllers.
 */

class BaseController extends Controller
{
	public $selectedFirmId;
	public $firms;
	public $showForm = false;
	public $patientId;
	public $patientName;
	public $jsVars = array();

	/**
	 * Check to see if user's level is high enough
	 * @param integer $level
	 * @return boolean
	 */
	public static function checkUserLevel($level) {
		if($user = Yii::app()->user) {
			return ($user->access_level >= $level);
		} else {
			return false;
		}
	}
	
	/**
	 * Set default rules to block everyone apart from admin
	 * These should be overridden in child classes
	 * @return array
	 */
	public function filters() {
		return array('accessControl');
	}
	public function accessRules() {
		return array(
			array('allow',
				'roles'=>array('admin'),
			),
			// Deny everyone else (this is important to add when overriding as otherwise
			// any authenticated user may fall through and be allowed)
			array('deny'),
		);
	}
	
	public function filterAccessControl($filterChain) {
		$filter = new CAccessControlFilter;
		$filter->setRules($this->compileAccessRules());
		$filter->filter($filterChain);
	}
	
	protected function compileAccessRules() {
		// Always allow admin
		$admin_rule = array('allow', 'roles' => array('admin'));
		
		// Always deny unauthenticated users in case rules fall through
		// Maybe we should change this to deny everyone for safety
		$default_rule = array('deny', 'users' => array('?'));
		
		// Merge rules defined by controller
		return array_merge(array($admin_rule), $this->accessRules(), array($default_rule));
	}

	/**
	 * List of actions for which the style.css file should _not_ be included
	 * @return array:
	 */
	public function printActions() {
		return array();
	}
	
	protected function beforeAction($action) {
		
		// Register base style.css unless it's a print action
		if(!in_array($action->id,$this->printActions())) {
			Yii::app()->getClientScript()->registerCssFile(Yii::app()->createUrl('/css/style.css'));
		}
		
		$app = Yii::app();

		if (Yii::app()->params['ab_testing']) {
			if (Yii::app()->user->isGuest) {
				$identity=new UserIdentity('admin', 'admin');
				$identity->authenticate();
				Yii::app()->user->login($identity,0);
				$this->selectedFirmId = 1;
				$app->session['patient_id'] = 1;
				$app->session['patient_name'] = 'John Smith';
			}
		}

		if (isset($app->session['firms']) && count($app->session['firms'])) {
			$this->showForm = true;

			$this->firms = $app->session['firms'];
			$this->selectedFirmId = $app->session['selected_firm_id'];
		}

		if (isset($app->session['patient_name'])) {
			$this->patientName = $app->session['patient_name'];
		}

		return parent::beforeAction($action);
	}

	/**
	 * Resets the session patient information.
	 *
	 * This method is called when the patient id for the requested activity is not the
	 * same as the session patient id, e.g. the user has viewed a different patient in
	 * a different tab. As such the patient id has to be reset to prevent problems
	 * such an event being assigned to the wrong patient.
	 *
	 * This code is much like that in PatientController->actionView.
	 *
	 * @param int $patientId
	 */
	public function resetSessionPatient($patientId)
	{
		$patient = Patient::model()->findByPk($patientId);

		if (empty($patient)) {
			throw new Exception('Invalid patient id provided.');
		}

		$this->setSessionPatient($patient);

		if (isset(Yii::app()->session['patient_id'])) {
			$this->patientId = Yii::app()->session['patient_id'];
		}
		if (isset(Yii::app()->session['patient_name'])) {
			$this->patientName = Yii::app()->session['patient_name'];
		}
	}

	protected function setSessionPatient($patient)
	{
		$app = Yii::app();
		$app->session['patient_id'] = $patient->id;
		$app->session['patient_name'] = $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name;
	}

	public function checkPatientId()
	{
		$app = Yii::app();

		if (Yii::app()->params['ab_testing']) {
			if (Yii::app()->user->isGuest) {
				$identity=new UserIdentity('admin', 'admin');
				$identity->authenticate();
				Yii::app()->user->login($identity,0);
				$this->selectedFirmId = 1;
				$app->session['patient_id'] = 1;
				$app->session['patient_name'] = 'John Smith';
			}
			$app->session['patient_id'] = 1;
			$app->session['patient_name'] = 'John Smith';
		}

		if (isset($app->session['patient_id'])) {
			$this->patientId = $app->session['patient_id'];
			$this->patientName = $app->session['patient_name'];
		} else {
			throw new CHttpException(403, 'You are not authorised to perform this action.');
		}
	}

	public function storeData()
	{
		$app = Yii::app();

		if (!empty($app->session['firms'])) {
			$this->showForm = true;

			$this->firms = $app->session['firms'];
			$this->selectedFirmId = $app->session['selected_firm_id'];
		}

		if (isset($app->session['patient_name'])) {
			$this->patientName = $app->session['patient_name'];
		}
	}

	public function logActivity($message)
	{
		$addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

		Yii::log($message . ' from ' . $addr, "user", "userActivity");
	}

	protected function beforeRender($view) {
		$this->processJsVars();
		return parent::beforeRender($view);
	}

	public function processJsVars() {
		foreach ($this->jsVars as $key => $value) {
			$value = CJavaScript::encode($value);
			Yii::app()->getClientScript()->registerScript('scr_'.$key, "$key = $value;",CClientScript::POS_READY);
		}
	}
}
