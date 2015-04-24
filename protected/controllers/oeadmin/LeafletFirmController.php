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
class LeafletFirmController extends BaseAdminController
{
	public function actionList()
	{
		$admin = new AdminListAutocomplete(OphTrConsent_Leaflet_Firm::model(), $this);
		$admin->setListFields(array(
			'id',
			'leaflet.name'
		));
		$admin->setCustomDeleteURL('/oeadmin/LeafletFirm/delete');
		$admin->setCustomSaveURL('/oeadmin/LeafletFirm/add');
		$admin->setModelDisplayName('Leaflet-Firm Assignment');
		$admin->setFilterFields(
			array(
				array(
					'label' => 'Firm',
					'dropDownName' => 'firm_id',
					'defaultValue' => Yii::app()->session['selected_firm_id'],
					'listModel' => Firm::model(),
					'listIdField' => 'id',
					'listDisplayField' => 'name'
				)
			)
		);
		// we set default search options
		if ($this->request->getParam('search') == '') {
			$admin->getSearch()->initSearch(array(
					'filterid' =>
						array(
							'firm_id' => Yii::app()->session['selected_firm_id']
						)
				)
			);
		}
		$admin->setAutocompleteField(
			array(
				'fieldName' => 'leaflet_id',
				'jsonURL' => '/oeadmin/LeafletFirm/search',
				'placeholder' => 'search for leaflets'
			)
		);
		//$admin->searchAll();
		$admin->listModel();
	}

	public function actionDelete($itemId)
	{
		/*
 		* We make sure to not allow deleting directly with the URL, user must come from the commondrugs list page
 		*/
		if (!Yii::app()->request->isAjaxRequest) {
			$this->render("errorpage", array("errorMessage" => "notajaxcall"));
		} else {
			if ($leafletSubspecialy = OphTrConsent_Leaflet_Firm::model()->findByPk($itemId)) {
				$leafletSubspecialy->delete();
				echo "success";
			} else {
				$this->render("errorpage", array("errormessage" => "recordmissing"));
			}
		}
	}

	public function actionAdd()
	{
		$firmId = $this->request->getParam("firm_id");
		$leafletId = $this->request->getParam("leaflet_id");
		if (!Yii::app()->request->isAjaxRequest) {
			$this->render("errorpage", array("errormessage" => "notajaxcall"));
		} else {
			if (!is_numeric($firmId) || !is_numeric($leafletId)) {
				echo "error";
			} else {
				$newLFF = new OphTrConsent_Leaflet_Firm();
				$newLFF->firm_id = $firmId;
				$newLFF->leaflet_id = $leafletId;
				if ($newLFF->save()) {
					echo "success";
				} else {
					echo "error";
				}
			}
		}
	}

	public function actionSearch()
	{
		if (Yii::app()->request->isAjaxRequest) {
			$criteria = new CDbCriteria();
			if (isset($_GET['term']) && strlen($term = $_GET['term']) > 0) {
				$criteria->addCondition(array('LOWER(name) LIKE :term'),
					'OR');
				$params[':term'] = '%' . strtolower(strtr($term, array('%' => '\%'))) . '%';
			}
			$criteria->order = 'name';
			$criteria->select = 'id, name';
			$criteria->params = $params;
			$results = OphTrConsent_Leaflet::model()->active()->findAll($criteria);
			$return = array();
			foreach ($results as $resultRow) {
				$return[] = array(
					'label' => $resultRow->name,
					'value' => $resultRow->name,
					'id' => $resultRow->id,
				);
			}
			echo CJSON::encode($return);
		}
	}
}