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

class BaseEventTypeController extends BaseController
{
	public $model;
	public $firm;
	public $patient;
	public $site;
	public $editable = true;
	public $editing;
	public $event;
	public $event_type;
	private $title;
	public $assetPath;
	public $episode;
	public $moduleNameCssClass = '';
	public $moduleStateCssClass = '';
	public $event_tabs = array();
	public $event_actions = array();
	public $print_css = true;
	public $successUri = 'default/view/';
	public $eventIssueCreate = false;
	public $extraViewProperties = array();
	public $jsVars = array();
	public $layout = '//layouts/events_and_episodes';
	public $current_episode;
	private $episodes = array();
	public $renderPatientPanel = true;


	public function getTitle()
	{
		if(isset($this->title)){
			return $this->title;
		}
		if(isset($this->event_type)){
			return $this->event_type->name;
		}
		return '';
	}

	public function setTitle($title)
	{
		$this->title=$title;
	}

	/**
	 * Checks to see if current user can create an event type
	 * @param EventType $event_type
	 */
	public function checkEventAccess($event_type)
	{
		$firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
		if (!$firm->service_subspecialty_assignment_id) {
			if (!$event_type->support_services) {
				return false;
			}
		}

		if (BaseController::checkUserLevel(5)) {
			return true;
		}
		if (BaseController::checkUserLevel(4) && $event_type->class_name != 'OphDrPrescription') {
			return true;
		}
		return false;
	}

	public function accessRules()
	{
		return array(
			// Level 2 can't change anything
			array('allow',
				'actions' => array('view'),
				'expression' => 'BaseController::checkUserLevel(2)',
			),
			array('allow',
				'actions' => $this->printActions(),
				'expression' => 'BaseController::checkUserLevel(3)',
			),
			// Level 4 or above can do anything
			array('allow',
				'expression' => 'BaseController::checkUserLevel(4)',
			),
			array('deny'),
		);
	}

	/**
	 * Whether the current user is allowed to call print actions
	 * @return boolean
	 */
	public function canPrint()
	{
		return BaseController::checkUserLevel(3);
	}

	public function renderEventMetadata($view='//patient/event_metadata')
	{
		$this->renderPartial($view);
	}

	public function actionIndex()
	{
		$this->render('index');
	}

	public function printActions()
	{
		return array('print');
	}

	protected function beforeAction($action)
	{
		// Set the module CSS class name.
		$this->moduleNameCssClass = strtolower(Yii::app()->getController()->module->id);

		// Set asset path
		if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets'))) {
			$this->assetPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets'), false, -1, YII_DEBUG);
		}

		// Automatic file inclusion unless it's an ajax call
		if ($this->assetPath && !Yii::app()->getRequest()->getIsAjaxRequest()) {

			if (in_array($action->id,$this->printActions())) {
				// Register print css
				if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets.css').'/print.css')) {
					$this->registerCssFile('module-print.css', $this->assetPath.'/css/print.css');
				}

			} else {
				// Register js
				if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets.js').'/module.js')) {
					Yii::app()->clientScript->registerScriptFile($this->assetPath.'/js/module.js');
				}

				if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets.js').'/'.get_class($this).'.js')) {
					Yii::app()->clientScript->registerScriptFile($this->assetPath.'/js/'.get_class($this).'.js');
				}

				// Register css
				if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets.css').'/module.css')) {
					$this->registerCssFile('module.css',$this->assetPath.'/css/module.css',10);
				}

				if (file_exists(Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets.css').'/css/'.get_class($this).'.css')) {
					$this->registerCssFile(get_class($this).'.css',$this->assetPath.'/css/'.get_class($this).'.css',10);
				}
			}
		}

		parent::storeData();

		$this->firm = Firm::model()->findByPk($this->selectedFirmId);

		if (!isset($this->firm)) {
			// No firm selected, reject
			throw new CHttpException(403, 'You are not authorised to view this page without selecting a firm.');
		}

		return parent::beforeAction($action);
	}

	/**
	 * Get all the elements for an event, the current module or an event_type
	 * @param string $action
	 * @param int $event_type_id
	 * @param Event $event
	 * @return BaseEventTypeElement[]
	 */
	public function getDefaultElements($action, $event_type_id = null, $event = null)
	{
		if (!$event && isset($this->event)) {
			$event = $this->event;
		}

		if (isset($event->event_type_id)) {
			$event_type = EventType::model()->find('id = ?',array($event->event_type_id));
		} elseif ($event_type_id) {
			$event_type = EventType::model()->find('id = ?',array($event_type_id));
		} else {
			$event_type = EventType::model()->find('class_name = ?',array($this->getModule()->name));
		}

		$criteria = new CDbCriteria;
		$criteria->compare('event_type_id',$event_type->id);
		$criteria->order = 'display_order asc';

		$elements = array();

		if (empty($_POST)) {
			if (isset($event->event_type_id)) {
				foreach (ElementType::model()->findAll($criteria) as $element_type) {
					$element_class = $element_type->class_name;

					foreach ($element_class::model()->findAll(array('condition'=>'event_id=?','params'=>array($event->id),'order'=>'id asc')) as $element) {
						$elements[] = $element;
					}
				}
			} else {
				$criteria->compare('`default`',1);

				foreach (ElementType::model()->findAll($criteria) as $element_type) {
					$element_class = $element_type->class_name;
					$elements[] = new $element_class;
				}
			}
		} else {
			foreach ($_POST as $key => $value) {
				if (preg_match('/^Element|^OEElement/',$key)) {
					if ($element_type = ElementType::model()->find('class_name=?',array($key))) {
						$element_class = $element_type->class_name;

						$keys = array_keys($value);

						if (is_array($value[$keys[0]])) {
							if ($action != 'update' || !$element_type->default) {
								for ($i=0; $i<count($value[$keys[0]]); $i++) {
									$element = new $element_class;
									$element->event_id = $event ? $event->id : null;

									foreach ($keys as $_key) {
										if ($_key != '_element_id') {
											$element[$_key] = $value[$_key][$i];
										}
									}

									$elements[] = $element;
								}
							}
						} else {
							if (isset($event->event_type_id) && ($element = $element_class::model()->find('event_id = ?',array($event->id)))) {
								$elements[] = $element;
							} else {
								if ($action != 'update' || !$element_type->default) {
									$elements[] = new $element_class;
								}
							}
						}
					}
				}
			}
		}

		return $elements;
	}

	/**
	 * Get the optional elements for the current module's event type
	 * This will be overriden by the module
	 *
	 * @return array
	 */
	public function getOptionalElements($action)
	{
		switch ($action) {
			case 'create':
			case 'view':
			case 'print':
				return array();
			case 'update':
				$event_type = EventType::model()->findByPk($this->event->event_type_id);

				$criteria = new CDbCriteria;
				$criteria->compare('event_type_id',$event_type->id);
				$criteria->compare('`default`',1);
				$criteria->order = 'display_order asc';

				$elements = array();
				$element_classes = array();

				foreach (ElementType::model()->findAll($criteria) as $element_type) {
					$element_class = $element_type->class_name;
					if (!$element_class::model()->find('event_id = ?',array($this->event->id))) {
						$elements[] = new $element_class;
					}
				}

				return $elements;
		}
	}

	/**
	 * function to redirect to the patient episodes when the controller determines the action cannot be carried out
	 *
	 */
	protected function redirectToPatientEpisodes()
	{
		$this->redirect(array("/patient/episodes/".$this->patient->id));
		Yii::app()->end();
	}

	/**
	 * carries out the base create action
	 *
	 * @return bool|string
	 * @throws CHttpException
	 * @throws Exception
	 */
	public function actionCreate()
	{
		$this->moduleStateCssClass = 'edit';
		$this->event_type = EventType::model()->find('class_name=?', array($this->getModule()->name));
		if (!$this->patient = Patient::model()->findByPk($_REQUEST['patient_id'])) {
			throw new CHttpException(403, 'Invalid patient_id.');
		}

		if (is_array(Yii::app()->params['modules_disabled']) && in_array($this->event_type->class_name,Yii::app()->params['modules_disabled'])) {
			// disabled module
			$this->redirectToPatientEpisodes();
		}

		$session = Yii::app()->session;
		/** @var $firm Firm */
		$firm = Firm::model()->findByPk($session['selected_firm_id']);
		$this->episode = $this->getEpisode($firm, $this->patient->id);

		if (!$this->event_type->support_services && !$firm->serviceSubspecialtyAssignment) {
			// Can't create a non-support service event for a support-service firm
			$this->redirectToPatientEpisodes();
		}

		if (!$episode = $this->patient->getEpisodeForCurrentSubspecialty()) {
			// there's no episode for this subspecialty
			$this->redirectToPatientEpisodes();
		}

		// firm changing sanity
		if (!empty($_POST) && !empty($_POST['firm_id']) && $_POST['firm_id'] != $this->firm->id) {
			// The firm id in the firm is not the same as the session firm id, e.g. they've changed
			// firms in a different tab. Set the session firm id to the provided firm id.

			$firms = $session['firms'];
			$firmId = intval($_POST['firm_id']);

			if ($firms[$firmId]) {
				$session['selected_firm_id'] = $firmId;
				$this->selectedFirmId = $firmId;
				$this->firm = Firm::model()->findByPk($this->selectedFirmId);
			} else {
				// They've supplied a firm id in the post to which they are not entitled??
				throw new Exception('Invalid firm id on attempting to create event.');
			}
		}
		$elements = $this->getDefaultElements('create', $this->event_type->id);

		if (empty($_POST) && !count($elements)) {
			throw new CHttpException(403, 'Gadzooks!	I got me no elements!');
		}

		if (!empty($_POST) && isset($_POST['cancel'])) {
			$this->redirect(array('/patient/view/'.$this->patient->id));
			return;
		} elseif (!empty($_POST) && !count($elements)) {
			$errors['Event'][] = 'No elements selected';
		} elseif (!empty($_POST)) {

			$elements = array();
			$element_names = array();

			foreach (ElementType::model()->findAll('event_type_id=?',array($this->event_type->id)) as $element_type) {
				if (isset($_POST[$element_type->class_name])) {
					$elements[] = new $element_type->class_name;
					$element_names[$element_type->class_name] = $element_type->name;
				}
			}

			$elementList = array();

			// validation
			$errors = $this->validatePOSTElements($elements);


			// creation
			if (empty($errors)) {
				// The user has submitted the form to create the event
				$eventId = $this->createElements(
					$elements, $_POST, $this->firm, $this->patient->id, Yii::app()->user->id, $this->event_type->id
				);

				if ($eventId) {
					$this->logActivity('created event.');

					$event = Event::model()->findByPk($eventId);

					if ($this->eventIssueCreate) {
						$event->addIssue($this->eventIssueCreate);
					}

					$audit_data = array('event' => $event->getAuditAttributes());

					foreach ($elements as $element) {
						$audit_data[get_class($element)] = $element->getAuditAttributes();
					}

					$event->audit('event','create',serialize($audit_data));

					Yii::app()->user->setFlash('success', "{$this->event_type->name} created.");
					$this->redirect(array($this->successUri.$eventId));
					return $eventId;
				}
			}
		}

		$this->editable = false;
		$this->event_tabs = array(
				array(
						'label' => 'Create',
						'active' => true,
				),
		);

		$cancel_url = ($this->episode) ? '/patient/episode/'.$this->episode->id : '/patient/episodes/'.$this->patient->id;
		$this->event_actions = array(
				EventAction::link('Cancel',
						Yii::app()->createUrl($cancel_url),
						array('level' => 'cancel')
				)
		);

		$this->processJsVars();

		$this->render('create', array(
			'elements' => $this->getDefaultElements('create'),
			'eventId' => null,
			'errors' => @$errors
		));
	}

	public function actionView($id)
	{
		$this->moduleStateCssClass = 'view';

		if (!$this->event = Event::model()->findByPk($id)) {
			throw new CHttpException(403, 'Invalid event id.');
		}
		$this->patient = $this->event->episode->patient;
		$this->event_type = EventType::model()->findByPk($this->event->event_type_id);
		$this->episode = $this->event->episode;

		$elements = $this->getDefaultElements('view');

		// Decide whether to display the 'edit' button in the template
		if ($this->editable) {
			if (!BaseController::checkUserLevel(4) || (!$this->event->episode->firm && !$this->event->episode->support_services)) {
				$this->editable = false;
			} else {
				if ($this->firm->getSubspecialtyID() != $this->event->episode->getSubspecialtyID()) {
					$this->editable = false;
				}
			}
		}
		// Allow elements to override the editable status
		if ($this->editable) {
			foreach ($elements as $element) {
				if (!$element->isEditable()) {
					$this->editable = false;
					break;
				}
			}
		}

		$this->logActivity('viewed event');

		$this->event->audit('event','view',false);

		$this->event_tabs = array(
			array(
				'label' => 'View',
				'active' => true,
			)
		);
		if ($this->editable) {
			$this->event_tabs[] = array(
				'label' => 'Edit',
				'href' => Yii::app()->createUrl($this->event->eventType->class_name.'/default/update/'.$this->event->id),
			);
		}
		if ($this->canDelete()) {
			$this->event_actions = array(
				EventAction::link('Delete',
					Yii::app()->createUrl($this->event->eventType->class_name.'/default/delete/'.$this->event->id),
					array('level' => 'delete')
				)
			);
		}

		$this->processJsVars();

		$viewData = array_merge(array(
			'elements' => $elements,
			'eventId' => $id,
		), $this->extraViewProperties);

		$this->render('view', $viewData);
	}

	public function actionUpdate($id)
	{
		$this->moduleStateCssClass = 'edit';
		if (!$this->event = Event::model()->findByPk($id)) {
			throw new CHttpException(403, 'Invalid event id.');
		}

		$this->patient = $this->event->episode->patient;

		// Check the user's firm is of the correct subspecialty to have the
		// rights to update this event
		if ($this->firm->getSubspecialtyID() != $this->event->episode->getSubspecialtyID()) {
			//The firm you are using is not associated with the subspecialty of the episode
			$this->redirectToPatientEpisodes();
		}

		$this->event_type = EventType::model()->findByPk($this->event->event_type_id);
		$this->episode = $this->event->episode;

		// firm changing sanity
		if (!empty($_POST) && !empty($_POST['firm_id']) && $_POST['firm_id'] != $this->firm->id) {
			// The firm id in the firm is not the same as the session firm id, e.g. they've changed
			// firms in a different tab. Set the session firm id to the provided firm id.

			$session = Yii::app()->session;

			$firms = $session['firms'];
			$firmId = intval($_POST['firm_id']);

			if ($firms[$firmId]) {
				$session['selected_firm_id'] = $firmId;
				$this->selectedFirmId = $firmId;
				$this->firm = Firm::model()->findByPk($this->selectedFirmId);
			} else {
				// They've supplied a firm id in the post to which they are not entitled??
				throw new Exception('Invalid firm id on attempting to update event.');
			}
		}

		if (empty($_POST) && !count($this->getDefaultElements($this->action->id))) {
			throw new CHttpException(403, 'Gadzooks!	I got me no elements!');
		}

		if (!empty($_POST) && isset($_POST['cancel'])) {
			// Cancel button pressed, so just bounce to view
			$this->redirect(array('default/view/'.$this->event->id));
			return;
		} elseif (!empty($_POST) && !count($this->getDefaultElements($this->action->id))) {
			$errors['Event'][] = 'No elements selected';
		} elseif (!empty($_POST)) {

			$elements = array();
			$to_delete = array();
			foreach (ElementType::model()->findAll('event_type_id=?',array($this->event_type->id)) as $element_type) {
				$class_name = $element_type->class_name;
				if (isset($_POST[$class_name])) {
					$keys = array_keys($_POST[$class_name]);
					if (is_array($_POST[$class_name][$keys[0]])) {
						if (!isset($_POST[$class_name]['_element_id'])) {
							throw new Exception("Array'd elements must include _element_id");
						}

						foreach ($class_name::model()->findAll(array('condition'=>'event_id=?','params'=>array($this->event->id),'order'=>'id asc')) as $element) {
							if (in_array($element->id,$_POST[$class_name]['_element_id'])) {
								$elements[] = $element;
							} else {
								$to_delete[] = $element;
							}
						}
						foreach ($_POST[$class_name]['_element_id'] as $element_id) {
							if (!$element_id) {
								$elements[] = new $class_name;
							}
						}
					} else {
						if ($element = $class_name::model()->find('event_id=?',array($this->event->id))) {
							// Add existing element to array
							$elements[] = $element;
						} else {
							// Add new element to array
							$elements[] = new $class_name;
						}
					}
				} elseif ($element = $class_name::model()->find('event_id=?',array($this->event->id))) {
					// Existing element is not posted, so we need to delete it
					$to_delete[] = $element;
				}
			}

			// validation
			$errors = $this->validatePOSTElements($elements);

			// creation
			if (empty($errors)) {

				// Need to pass through _all_ elements to updateElements (those not in _POST will be deleted)
				$all_elements = array_merge($elements, $to_delete);
				$success = $this->updateElements($all_elements, $_POST, $this->event);

				if ($success) {
					$info_text = '';
					foreach ($elements as $element) {
						if ($element->infotext) {
							$info_text .= $element->infotext;
						}
					}

					$this->logActivity('updated event');

					$audit_data = array('event' => $this->event->getAuditAttributes());

					foreach ($elements as $element) {
						$audit_data[get_class($element)] = $element->getAuditAttributes();
					}

					$this->event->audit('event','update',serialize($audit_data));

					$this->event->user = Yii::app()->user->id;
					$this->event->info = $info_text;

					if (!$this->event->save()) {
						throw new SystemException('Unable to update event: '.print_r($this->event->getErrors(),true));
					}

					OELog::log("Updated event {$this->event->id}");

					$this->redirect(array('default/view/'.$this->event->id));
					return;
				}
			}
		}

		$this->editing = true;
		$this->event_tabs = array(
				array(
						'label' => 'View',
						'href' => Yii::app()->createUrl($this->event->eventType->class_name.'/default/view/'.$this->event->id),
				),
				array(
						'label' => 'Edit',
						'active' => true,
				),
		);

		$this->event_actions = array(
				EventAction::link('Cancel',
						Yii::app()->createUrl($this->event->eventType->class_name.'/default/view/'.$this->event->id),
						array('level' => 'cancel')
				)
		);

		$this->processJsVars();

		$this->render($this->action->id, array(
			'elements' => $this->getDefaultElements($this->action->id),
			'errors' => @$errors
		));
	}

	/**
	 * Stub method:
	 *
	 * Use this for any many to many relations defined on your elements. This is called prior to validation
	 * so should set values without actually touching the database. To do that, the createElements and updateElements
	 * methods should be extended to handle the POST values.
	 */
	protected function setPOSTManyToMany($element)
	{
		// placeholder function
	}

	/**
	 * Uses the POST values to define elements and their field values without hitting the db, and then performs validation
	 *
	 * @param array() - elements
	 */
	protected function validatePOSTElements($elements)
	{
		$generic = array();

		$errors = array();
		foreach ($elements as $element) {
			$elementClassName = get_class($element);

			if ($element->required || isset($_POST[$elementClassName])) {
				if (isset($_POST[$elementClassName])) {
					$keys = array_keys($_POST[$elementClassName]);

					if (is_array($_POST[$elementClassName][$keys[0]])) {
						if (!isset($generic[$elementClassName])) {
							$generic[$elementClassName] = $_POST[$elementClassName];
						}

						$element = new $elementClassName;

						foreach ($keys as $key) {
							if ($key != '_element_id') {
								$element->{$key} = array_shift($generic[$elementClassName][$key]);
							}
						}

						$this->setPOSTManyToMany($element);

						if (!$element->validate()) {
							$proc_name = $element->procedure->term;
							$elementName = $element->getElementType()->name;
							foreach ($element->getErrors() as $errormsgs) {
								foreach ($errormsgs as $error) {
									$errors[$proc_name][] = $error;
								}
							}
						}
					}
					else
					{
						$element->attributes = Helper::convertNHS2MySQL($_POST[$elementClassName]);
						$this->setPOSTManyToMany($element);
						if (!$element->validate()) {
							$elementName = $element->getElementType()->name;
							foreach ($element->getErrors() as $errormsgs) {
								foreach ($errormsgs as $error) {
									$errors[$elementName][] = $error;
								}
							}
						}
					}
				}
			}
		}

		return $errors;
	}

	public function renderDefaultElements($action, $form=false, $data=false)
	{
		foreach ($this->getDefaultElements($action) as $element) {
			if ($action == 'create' && empty($_POST)) {
				$element->setDefaultOptions();
			}

			$view = ($element->{$action.'_view'}) ? $element->{$action.'_view'} : $element->getDefaultView();
			$this->renderPartial(
				$action . '_' . $view,
				array('element' => $element, 'data' => $data, 'form' => $form),
				false, false
			);
		}
	}

	public function renderOptionalElements($action, $form=false,$data=false)
	{
		foreach ($this->getOptionalElements($action) as $element) {
			if ($action == 'create' && empty($_POST)) {
				$element->setDefaultOptions();
			}

			$view = ($element->{$action.'_view'}) ? $element->{$action.'_view'} : $element->getDefaultView();
			$this->renderPartial(
				$action . '_' . $view,
				array('element' => $element, 'data' => $data, 'form' => $form),
				false, false
			);
		}
	}

	public function getEpisodes()
	{
		if (empty($this->episodes)) {
			$this->episodes = array(
				'ordered_episodes'=>$this->patient->getOrderedEpisodes(),
				'legacyepisodes'=>$this->patient->legacyepisodes,
				'supportserviceepisodes'=>$this->patient->supportserviceepisodes,
			);
		}
		return $this->episodes;
	}

	public function createElements($elements, $data, $firm, $patientId, $userId, $eventTypeId)
	{
		$valid = true;
		$elementsToProcess = array();

		// Go through the array of elements to see which the user is attempting to
		// create, which are required and whether they pass validation.
		foreach ($elements as $element) {
			$elementClassName = get_class($element);

			if ($element->required || isset($data[$elementClassName])) {
				if (isset($data[$elementClassName])) {
					$keys = array_keys($data[$elementClassName]);

					if (is_array($data[$elementClassName][$keys[0]])) {
						for ($i=0; $i<count($data[$elementClassName][$keys[0]]); $i++) {
							$element = new $elementClassName;

							foreach ($keys as $key) {
								if ($key != '_element_id') {
									$element->{$key} = $data[$elementClassName][$key][$i];
								}
							}

							$this->setPOSTManyToMany($element);

							if (!$element->validate()) {
								$valid = false;
							} else {
								$elementsToProcess[] = $element;
							}
						}
					} else {
						$element->attributes = Helper::convertNHS2MySQL($data[$elementClassName]);

						$this->setPOSTManyToMany($element);

						if (!$element->validate()) {
							$valid = false;
						} else {
							$elementsToProcess[] = $element;
						}
					}
				}
			}
		}

		if (!$valid) {
			return false;
		}

		/**
		 * Create the event. First check to see if there is currently an episode for this
		 * subspecialty for this patient. If so, add the new event to it. If not, create an
		 * episode and add it to that.
		 */
		$episode = $this->getOrCreateEpisode($firm, $patientId);
		$event = $this->createEvent($episode, $userId, $eventTypeId, $elementsToProcess);

		// Create elements for the event
		foreach ($elementsToProcess as $element) {
			$element->event_id = $event->id;

			// No need to validate as it has already been validated and the event id was just generated.
			if (!$element->save(false)) {
				throw new Exception('Unable to save element ' . get_class($element) . '.');
			}
		}

		$this->afterCreateElements($event);

		return $event->id;
	}

	/**
	 * Update elements based on arrays passed over from $_POST data
	 *
	 * @param BaseEventTypeElement[] $elements
	 * @param array $data $_POST data to update
	 * @param Event $event the associated event
	 *
	 * @throws SystemException
	 * @return bool true if all elements succeeded, false otherwise
	 */
	public function updateElements($elements, $data, $event)
	{
		$success = true;
		$toDelete = array();
		$toSave = array();

		foreach ($elements as $element) {
			$elementClassName = get_class($element);
			$needsValidation = false;

			if (isset($data[$elementClassName])) {
				$keys = array_keys($data[$elementClassName]);

				if (is_array($data[$elementClassName][$keys[0]])) {
					if (!$element->id || in_array($element->id,$data[$elementClassName]['_element_id'])) {

						$properties = array();

						foreach ($data[$elementClassName] as $key => $values) {
							if ($key != '_element_id') {
								$properties[$key] = array_shift($data[$elementClassName][$key]);
							}
						}

						$element->attributes = Helper::convertNHS2MySQL($properties);

						$toSave[] = $element;
						$needsValidation = true;
					} else {
						$toDelete[] = $element;
					}
				} else {
					$element->attributes = Helper::convertNHS2MySQL($data[$elementClassName]);
					$toSave[] = $element;
					$needsValidation = true;
				}
			} elseif ($element->required) {
				// The form has failed to provide an array of data for a required element.
				// This isn't supposed to happen - a required element should at least have the
				// $data[$elementClassName] present, even if there's nothing in it.
				$success = false;
			} elseif ($element->event_id) {
				// This element already exists, isn't required and has had its data deleted.
				// Therefore it needs to be deleted.
				$toDelete[] = $element;
			}

			if ($needsValidation) {
				$this->setPOSTManyToMany($element);
				if (!$element->validate()) {
					$success = false;
				}
			}
		}

		if (!$success) {
			// An element failed validation or a required element didn't have an
			// array of data provided for it.
			return false;
		}

		foreach ($toSave as $element) {
			if (!isset($element->event_id)) {
				$element->event_id = $event->id;
			}

			if (!$element->save()) {
				OELog::log("Unable to save element: $element->id ($elementClassName): ".print_r($element->getErrors(),true));
				throw new SystemException('Unable to save element: '.print_r($element->getErrors(),true));
			}
		}

		foreach ($toDelete as $element) {
			$element->delete();
		}

		$this->afterUpdateElements($event);

		return true;
	}

	/**
	 * Called after event (and elements) has been updated
	 * @param Event $event
	 */
	protected function afterUpdateElements($event)
	{
	}

	/**
	 * Called after event (and elements) have been created
	 * @param Event $event
	 */
	protected function afterCreateElements($event)
	{
	}

	/**
	 * @param Firm $firm
	 * @param integer $patientId
	 * @return Episode
	 */
	public function getEpisode($firm, $patientId)
	{
		return Episode::model()->getCurrentEpisodeByFirm($patientId, $firm);
	}

	public function getOrCreateEpisode($firm, $patientId)
	{
		if (!$episode = $this->getEpisode($firm, $patientId)) {
			$episode = Patient::model()->findByPk($patientId)->addEpisode($firm);
		}

		return $episode;
	}

	public function createEvent($episode, $userId, $eventTypeId, $elementsToProcess)
	{
		$info_text = '';

		foreach ($elementsToProcess as $element) {
			if ($element->infotext) {
				$info_text .= $element->infotext;
			}
		}

		$event = new Event();
		$event->episode_id = $episode->id;
		$event->event_type_id = $eventTypeId;
		$event->info = $info_text;

		if (!$event->save()) {
			OELog::log("Failed to creat new event for episode_id=$episode->id, event_type_id=$eventTypeId");
			throw new Exception('Unable to save event.');
		}

		OELog::log("Created new event for episode_id=$episode->id, event_type_id=$eventTypeId");

		return $event;
	}

	public function displayErrors($errors, $bottom=false)
	{
		$this->renderPartial('//elements/form_errors',array(
			'errors'=>$errors,
			'bottom'=>$bottom
		));
	}

	/**
	 * Print action
	 * @param integer $id event id
	 */
	public function actionPrint($id)
	{
		$this->printInit($id);
		$elements = $this->getDefaultElements('print');
		$pdf = (isset($_GET['pdf']) && $_GET['pdf']);
		$this->printLog($id, $pdf);
		if ($pdf) {
			$this->printPDF($id, $elements);
		} else {
			$this->printHTML($id, $elements);
		}
	}

	/**
	 * Initialise print action
	 * @param integer $id event id
	 * @throws CHttpException
	 */
	protected function printInit($id)
	{
		if (!$this->event = Event::model()->findByPk($id)) {
			throw new CHttpException(403, 'Invalid event id.');
		}
		$this->patient = $this->event->episode->patient;
		$this->event_type = $this->event->eventType;
		$this->site = Site::model()->findByPk(Yii::app()->session['selected_site_id']);
	}

	/**
	 * Render HTML
	 * @param integer $id event id
	 * @param array $elements
	 */
	protected function printHTML($id, $elements, $template='print')
	{
		$this->layout = '//layouts/print';

		$this->render($template, array(
			'elements' => $elements,
			'eventId' => $id,
		));
	}

	/**
	 * Render PDF
	 * @param integer $id event id
	 * @param array $elements
	 */
	protected function printPDF($id, $elements, $template='print', $params=array())
	{
		// Remove any existing css
		Yii::app()->getClientScript()->reset();

		$this->layout = '//layouts/pdf';
		$pdf_print = new OEPDFPrint('Openeyes', 'PDF', 'PDF');
		$oeletter = new OELetter();
		$oeletter->setBarcode('E:'.$id);
		$body = $this->render($template, array_merge($params,array(
			'elements' => $elements,
			'eventId' => $id,
		)), true);
		$oeletter->addBody($body);
		$pdf_print->addLetter($oeletter);
		$pdf_print->output();
	}

	/**
	 * Log print action
	 * @param integer $id event id
	 * @param boolean $pdf
	 */
	protected function printLog($id, $pdf)
	{
		$this->logActivity("printed event (pdf=$pdf)");
		$this->event->audit('event','print',false);
	}

	public function canDelete()
	{
		if($this->event){
			return($this->event->canDelete());
		}
		return false;
	}

	public function actionDelete($id)
	{
		if (!$this->event = Event::model()->findByPk($id)) {
			throw new CHttpException(403, 'Invalid event id.');
		}

		// Only the event creator can delete the event, and only 24 hours after its initial creation
		if (!$this->canDelete()) {
			$this->redirect(array('default/view/'.$this->event->id));
			return false;
		}

		if (!empty($_POST)) {
			$this->event->softDelete();

			$this->event->audit('event','delete',false);

			if (Event::model()->count('episode_id=?',array($this->event->episode_id)) == 0) {
				$this->event->episode->deleted = 1;
				if (!$this->event->episode->save()) {
					throw new Exception("Unable to save episode: ".print_r($this->event->episode->getErrors(),true));
				}

				$this->event->episode->audit('episode','delete',false);

				header('Location: '.Yii::app()->createUrl('/patient/episodes/'.$this->event->episode->patient->id));
				return true;
			}

			Yii::app()->user->setFlash('success', "An event was deleted, please ensure the episode status is still correct.");

			header('Location: '.Yii::app()->createUrl('/patient/episode/'.$this->event->episode_id));
			return true;
		}

		$this->patient = $this->event->episode->patient;

		$this->event_type = EventType::model()->findByPk($this->event->event_type_id);

		$this->title = "Delete ".$this->event_type->name;
		$this->event_tabs = array(
				array(
						'label' => 'View',
						'active' => true,
				)
		);
		if ($this->editable) {
			$this->event_tabs[] = array(
					'label' => 'Edit',
					'href' => Yii::app()->createUrl($this->event->eventType->class_name.'/default/update/'.$this->event->id),
			);
		}

		$this->processJsVars();

		$episodes = $this->getEpisodes();
		$viewData = array_merge(array(
			'eventId' => $id,
		), $episodes);

		$this->render('delete', $viewData);

		return false;
	}

	public function processJsVars()
	{
		if ($this->patient) {
			$this->jsVars['OE_patient_id'] = $this->patient->id;
		}
		if ($this->event) {
			$this->jsVars['OE_event_id'] = $this->event->id;
			$this->jsVars['OE_print_url'] = Yii::app()->createUrl($this->getModule()->name."/default/print/".$this->event->id);
		}
		$this->jsVars['OE_asset_path'] = $this->assetPath;
		$firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
		$subspecialty_id = $firm->serviceSubspecialtyAssignment ? $firm->serviceSubspecialtyAssignment->subspecialty_id : null;
		$this->jsVars['OE_subspecialty_id'] = $subspecialty_id;

		parent::processJsVars();
	}
}
