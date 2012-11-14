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

class AdminSequenceController extends BaseController
{
	public $layout='column2';

	protected function beforeAction($action) {
		// Sample code to be used when RBAC is fully implemented.
		if (!Yii::app()->user->checkAccess('admin')) {
			throw new CHttpException(403, 'You are not authorised to perform this action.');
		}

		return parent::beforeAction($action);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new Sequence;

		if(!$model->firmAssignment) {
			$model->firmAssignment = new SequenceFirmAssignment();
		}

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sequence'])) {
			$model->attributes = Helper::convertNHS2MySQL($_POST['Sequence']);
			$model->firmAssignment->attributes = $_POST['SequenceFirmAssignment'];
			$modelValid = $model->validate();
			$firmValid = $model->firmAssignment->validate();
			if ($modelValid && $firmValid) {
				if ($model->save()) {
					if(!empty($firmAssociation->firm_id)) {
						$model->firmAssignment->sequence_id = $model->id;
						if ($model->firmAssignment->save()) {
							$this->redirect(array('view','id' => $model->id));
						}
					} else {
						$this->redirect(array('view','id' => $model->id));
					}
				}
			}
		}

		$this->render('create',array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		
		if(!$model->firmAssignment) {
			$model->firmAssignment = new SequenceFirmAssignment();
			$model->firmAssignment->sequence_id = $model->id;
		}

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sequence'])) {
			// TODO: Add validation to check collisions etc.
			$model->attributes = $_POST['Sequence'];
			if (!empty($_POST['SequenceFirmAssignment']['firm_id'])) {
				$model->firmAssignment->attributes = $_POST['SequenceFirmAssignment'];
				$firmValid = $model->firmAssignment->save();
			} else {
				if($model->firmAssignment->id) {
					$model->firmAssignment->delete();
				}
				$firmValid = true;
			}
			if ($firmValid && $model->save()) {
				$this->redirect(array('view','id' => $model->id));
			}
		}

		$this->render('update',array(
			'model' => $model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		
		// We only allow deletion via POST request
		if(Yii::app()->request->isPostRequest) {

			// Make really sure this thing has no bookings associated with it before we delete
			$sequence = $this->loadModel($id);
			if($sequence->getBookingCount() > 0) {
				throw new CHttpException(400, 'This sequence has bookings associated with it and cannot be deleted.');
			}

			// Delete any sessions that are involved with this sequence first
			// TODO: This might not be a good idea...
			Session::model()->deleteAllByAttributes(array('sequence_id' => $sequence->id));

			// Also delete any firm association(s)
			SequenceFirmAssignment::model()->deleteAllByAttributes(array('sequence_id' => $sequence->id));

			$sequence->delete();

			// If AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
		} else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$dataProvider=new CActiveDataProvider('Sequence', array(
			'criteria' => array('with' => array('firmAssignment'))
		));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model = new Sequence('search');
		$model->unsetAttributes();
		if(isset($_GET['Sequence'])) {
			$model->attributes = $_GET['Sequence'];
		}
		if(isset($_GET['Firm'])) {
			$model->firm_id = $_GET['Firm']['id'];
		}
		if(isset($_GET['Site'])) {
			$model->site_id = $_GET['Site']['id'];
		}
		if(isset($_GET['Sequence']['repeat']) && $_GET['Sequence']['repeat'] != '') {
			if ($_GET['Sequence']['repeat'] <= Sequence::FREQUENCY_4WEEKS) {
				$model->repeat_interval = $_GET['Sequence']['repeat'];
			} elseif ($_GET['Sequence']['repeat'] > Sequence::FREQUENCY_MONTHLY) {
				$model->week_selection = $_GET['Sequence']['repeat'] - Sequence::FREQUENCY_MONTHLY;
			}
		}
		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Sequence::model()->findByPk((int)$id);
		if($model===null) {
			throw new CHttpException(404,'The requested page does not exist.');
		}
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='sequence-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
