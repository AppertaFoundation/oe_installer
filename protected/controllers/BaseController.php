<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * A base controller class that helps display the firm dropdown and the patient name.
 * It is extended by all non-admin controllers.
 */

class BaseController extends Controller
{
	public $renderPatientPanel = false;
	public $selectedFirmId;
	public $selectedSiteId;
	public $firms;
	public $jsVars = array();
	protected $css = array();

	/**
	 * Check to see if user's level is high enough
	 * @param integer $level
	 * @return boolean
	 */
	public static function checkUserLevel($level)
	{
		if ($user = Yii::app()->user) {
			return ($user->access_level >= $level);
		} else {
			return false;
		}
	}

	/**
	 * Check if current user can create event of the given type
	 *
	 * @param EventType $event_type
	 * @return boolean
	 */
	public function canCreateEventType($event_type)
	{
		$firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
		if (!$firm->service_subspecialty_assignment_id) {
			// firm is a support services firm, which are restricted to only certain event types
			if (!$event_type->support_services) {
				return false;
			}
		}

		if (self::checkUserLevel(5)) {
			return true;
		}
		if (self::checkUserLevel(4) && $event_type->class_name != 'OphDrPrescription') {
			return true;
		}
		return false;
	}

	/**
	 * Set default rules to block everyone apart from admin
	 * These should be overridden in child classes
	 * @return array
	 */
	public function filters()
	{
		return array('accessControl');
	}
	public function accessRules()
	{
		return array(
			array('allow',
				'roles'=>array('admin'),
			),
			// Deny everyone else (this is important to add when overriding as otherwise
			// any authenticated user may fall through and be allowed)
			array('deny'),
		);
	}

	public function filterAccessControl($filterChain)
	{
		$filter = new CAccessControlFilter;
		$filter->setRules($this->compileAccessRules());
		$filter->filter($filterChain);
	}

	protected function compileAccessRules()
	{
		// Always allow admin
		$admin_rule = array('allow', 'roles' => array('admin'));

		// Always deny unauthenticated users in case rules fall through
		// Maybe we should change this to deny everyone for safety
		$default_rule = array('deny', 'users' => array('?'));

		// Merge rules defined by controller
		return array_merge(array($admin_rule), $this->accessRules(), array($default_rule));
	}

	/**
	 * (Pre)register a CSS file with a priority to allow ordering
	 * @param string $name
	 * @param string $path
	 * @param integer $priority
	 */
	public function registerCssFile($name, $path, $priority = 100)
	{
		$this->css[$name] = array(
				'path' => $path,
				'priority' => $priority,
		);
	}

	/**
	 * Registers all CSS file that were preregistered by priority
	 */
	protected function registerCssFiles()
	{
		$css_array = array();
		foreach ($this->css as $css_item) {
			$css_array[$css_item['path']] = $css_item['priority'];
		}
		arsort($css_array);
		$clientscript = Yii::app()->clientScript;
		foreach ($css_array as $path => $priority) {
			$clientscript->registerCssFile($path);
		}
	}

	/**
	 * List of actions for which the style.css file should _not_ be included
	 * @return array:
	 */
	public function printActions()
	{
		return array();
	}

	protected function beforeAction($action)
	{

		$app = Yii::app();

		// Register base style.css unless it's a print action
		if (!in_array($action->id,$this->printActions())) {
			$this->registerCssFile('style.css', Yii::app()->createUrl('/css/style.css'), 200);
		}


		if ($app->params['ab_testing']) {
			if ($app->user->isGuest) {
				$identity=new UserIdentity('admin', '');
				$identity->authenticate('force');
				$app->user->login($identity,0);
				$this->selectedFirmId = 1;
				$app->session['patient_id'] = 1;
				$app->session['patient_name'] = 'John Smith';
			}
		}

		if (isset($app->session['firms']) && count($app->session['firms'])) {
			$this->firms = $app->session['firms'];
			$this->selectedFirmId = $app->session['selected_firm_id'];
		}

		if (isset($app->session['selected_site_id'])) {
			$this->selectedSiteId = $app->session['selected_site_id'];
		}

		$this->registerCssFiles();
		$this->adjustScriptMapping();

		return parent::beforeAction($action);
	}

	/**
	 * Adjust the the client script mapping (for javascript and css files assets).
	 *
	 * If a Yii widget is being used in an Ajax request, all dependant scripts and
	 * stylesheets will be outputted in the response. This method ensures the core
	 * scripts and stylesheets are not outputted in an Ajax response.
	 */
	private function adjustScriptMapping() {
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			$scriptMap = Yii::app()->clientScript->scriptMap;
			$scriptMap['jquery.js'] = false;
			$scriptMap['jquery.min.js'] = false;
			$scriptMap['jquery-ui.js'] = false;
			$scriptMap['jquery-ui.min.js'] = false;
			$scriptMap['module.js'] = false;
			$scriptMap['style.css'] = false;
			$scriptMap['jquery-ui.css'] = false;
			Yii::app()->clientScript->scriptMap = $scriptMap;
		}
	}

	protected function setSessionPatient($patient)
	{
		$app = Yii::app();
		$app->session['patient_id'] = $patient->id;
		$app->session['patient_name'] = $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name;
	}

	public function storeData()
	{
		$app = Yii::app();

		if (!empty($app->session['firms'])) {
			$this->firms = $app->session['firms'];
			$this->selectedFirmId = $app->session['selected_firm_id'];
		}
	}

	public function logActivity($message)
	{
		$addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

		Yii::log($message . ' from ' . $addr, "user", "userActivity");
	}

	protected function beforeRender($view)
	{
		$this->processJsVars();
		return parent::beforeRender($view);
	}

	public function processJsVars()
	{
		$this->jsVars['YII_CSRF_TOKEN'] = Yii::app()->request->csrfToken;

		foreach ($this->jsVars as $key => $value) {
			$value = CJavaScript::encode($value);
			Yii::app()->getClientScript()->registerScript('scr_'.$key, "$key = $value;",CClientScript::POS_HEAD);
		}
	}

	/**
	 * Whether the current user is allowed to call print actions
	 * @return boolean
	 */
	public function canPrint()
	{
		return BaseController::checkUserLevel(3);
	}

	/*
	 * Convenience function for authorisation checks
	 *
	 * @param string $operation
	 * @param mixed $param, ...
	 * @return boolean
	 */
	public function checkAccess($operation)
	{
		$params = func_get_args();
		array_shift($params);

		return Yii::app()->user->checkAccess($operation, $params);
	}
}
