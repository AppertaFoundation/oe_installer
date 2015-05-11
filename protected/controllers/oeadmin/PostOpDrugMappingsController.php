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
class PostOpDrugMappingsController extends BaseAdminController
{

	public function actionList()
	{   // Use Model OphTrOperationnote_PostopSiteSubspecialtyDrug
		//site_id, subspeciality_id, drug_id, display_order, default

	$test =	OphTrOperationnote_PostopSiteSubspecialtyDrug::model()->findAll();

	//	var_dump($test);die;

		$admin = new AdminListAutocomplete(OphTrOperationnote_PostopSiteSubspecialtyDrug::model(), $this);

		$admin->setListFields(array(
			'id',
			//'agents.name',
			'sites.name',
			//'subspecialties',
			//'drug_id',
			//'postopdrugs.name',
			//'postopdrugs.name',
			'postopdrugs.name',
		));

		$admin->setCustomDeleteURL('/oeadmin/PostOpDrugMappings/delete');
		$admin->setCustomSaveURL('/oeadmin/PostOpDrugMappings/add');
		$admin->setModelDisplayName('Operation Note Anaesthetic Agent Defaults');
		$admin->setFilterFields(
			array(
				array(
					'label' => 'Site',
					'dropDownName' => 'site_id',
					'defaultValue' => Yii::app()->session['selected_site_id'],
					'listModel' => Site::model(),
					'listIdField' => 'id',
					'listDisplayField' => 'short_name'
				),
				array(
					'label' => 'Subspecialty',
					'dropDownName' => 'subspecialty_id',
					'defaultValue' => Firm::model()->findByPk(Yii::app()->session['selected_firm_id'])->serviceSubspecialtyAssignment->subspecialty_id,
					'listModel' => Subspecialty::model(),
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
							'subspecialty_id' => Firm::model()->findByPk(Yii::app()->session['selected_firm_id'])->serviceSubspecialtyAssignment->subspecialty_id,
							'site_id' => Yii::app()->session['selected_site_id']
						)
				)
			);
		}

		$admin->setAutocompleteField(
			array(
				'fieldName' => 'drug_id',
				'jsonURL' => '/oeadmin/PostOpDrugMappings/search',
				'placeholder' => 'search for adding per op drug'
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
			if ($leafletSubspecialy = OphTrOperationnote_PostopSiteSubspecialtyDrug::model()->findByPk($itemId)) {
				$leafletSubspecialy->delete();
				echo "success";
			} else {
				$this->render("errorpage", array("errormessage" => "recordmissing"));
			}
		}

	}

	public function actionAdd()
	{

		$subspecialtyId = $this->request->getParam("subspecialty_id");
		$siteId = $this->request->getParam("site_id");
		$drugId = $this->request->getParam("drug_id");

		if (!Yii::app()->request->isAjaxRequest) {
			$this->render("errorpage", array("errormessage" => "notajaxcall"));
		} else {
			if (!is_numeric($subspecialtyId) || !is_numeric($siteId) || !is_numeric($drugId)) {
				echo "error";
			} else {

				$newONPSS = new OphTrOperationnote_PostopSiteSubspecialtyDrug();

				$newONPSS->subspecialty_id = $subspecialtyId;
				$newONPSS->site_id = $siteId;
				$newONPSS->drug_id = $drugId;

				if ($newONPSS->save()) {
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

			$results = Ophtroperationnote_PostopDrug::model()->active()->findAll($criteria);

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