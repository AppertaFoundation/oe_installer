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

class ProcedureController extends BaseController
{
	public $layout='column2';

	public function accessRules() {
		return array(
			// Level 2 or above can do anything
			array('allow',
				'expression' => 'BaseController::checkUserLevel(2)',
			),
			// Deny anything else (default rule allows authenticated users)
			array('deny'),
		);
	}
	
	protected function beforeAction($action)
	{
		// Sample code to be used when RBAC is fully implemented.
//		if (!Yii::app()->user->checkAccess('admin')) {
//			throw new CHttpException(403, 'You are not authorised to perform this action.');
//		}

		return parent::beforeAction($action);
	}

	/**
	 * Lists all disorders for a given search term.
	 */
	public function actionAutocomplete()
	{
		echo CJavaScript::jsonEncode(Procedure::getList($_GET['term']));
	}

	public function actionDetails()
	{
		$list = Yii::app()->session['Procedures'];
		$found = false;

		if (!isset($_GET['short_version'])) {
			$_GET['short_version'] = true;
		}

		if (!empty($_GET['name'])) {
			if (!empty($list)) {
				foreach ($list as $id => $procedure) {
					if ($procedure['term'] == $_GET['name']) {
						$data = $procedure;
						$data['id'] = $id;

						$found = true;

						$this->renderPartial('_ajaxProcedure', array('data' => $data, 'durations' => @$_GET['durations'], 'short_version' => $_GET['short_version']), false, false);
						break;
					}
				}
			}

			// if not in the session, check in the db
			if (!$found) {
				$procedure = Yii::app()->db->createCommand()
					->select('*')
					->from('proc')
					->where('term=:term', array(':term'=>$_GET['name']))
					->queryRow();
				if (!empty($procedure)) {
					$data = array(
						'term' => $procedure['term'],
						'short_format' => $procedure['short_format'],
						'duration' => $procedure['default_duration'],
					);
					$list[$procedure['id']] = $data;

					$data['id'] = $procedure['id'];

					Yii::app()->session['Procedures'] = $list;

					$this->renderPartial('_ajaxProcedure', array('data' => $data, 'durations' => @$_GET['durations'], 'short_version' => $_GET['short_version']), false, false);
				}
			}
		}
	}

	public function actionList()
	{
		if (!empty($_POST['subsection'])) {
			$criteria = new CDbCriteria;
			$criteria->select = 't.id, term, short_format';
			$criteria->join = 'LEFT JOIN proc_subspecialty_subsection_assignment pssa ON t.id = pssa.proc_id';
			$criteria->compare('pssa.subspecialty_subsection_id', $_POST['subsection']);
			$criteria->order = 'term asc';

			if (!empty($_POST['existing'])) {
				$criteria->addNotInCondition("CONCAT_WS(' - ', term, short_format)", $_POST['existing']);
			}

			$procedures = Procedure::model()->findAll($criteria);

			$this->renderPartial('_procedureOptions', array('procedures' => $procedures), false, false);
		}
	}
}
