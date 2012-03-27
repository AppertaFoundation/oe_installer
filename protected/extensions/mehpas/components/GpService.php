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

class GpService {
	
	public $gp;
	public $pas_gp;
	
	/**
	 * Create a new instance of the service
	 *
	 * @param model $gp Instance of the gp model
	 * @param model $pas_gp Instance of the PAS gp model
	 */
	public function __construct($gp = null, $pas_gp = null) {
		if (empty($gp)) {
			$this->gp = new Gp();
		} else {
			$this->gp = $gp;
		}
		if (empty($pas_gp)) {
			$this->pas_gp = new PAS_Gp();
		} else {
			$this->pas_gp = $pas_gp;
		}
	}
	
	/**
	 * Populate the GP for a given patient. $patient_id can also be an array of patient_ids
	 * (used by the PopulateGps method above to populate multiple patient GPs at once)
	 * @param unknown_type $patient_id
	 * @param unknown_type $verbose
	 * @deprecated
	 */
	public function GetPatientGp($patient_id, $verbose=false) {
		if (!is_array($patient_id)) {
			$patient_id = array($patient_id);
		}

		$errors = array();
		foreach (Yii::app()->db_pas->createCommand("select distinct rm_patient_no as patient_id, max(date_from) as latestGP from silver.patient_gps where rm_patient_no in (".implode(',',$patient_id).") group by rm_patient_no order by rm_patient_no")->queryAll() as $latestGP) {
			$gp = Yii::app()->db_pas->createCommand("select * from silver.patient_gps where rm_patient_no = '{$latestGP['PATIENT_ID']}' and date_from = '{$latestGP['LATESTGP']}'")->queryRow();

			// Exclude bad GP data
			if (self::is_bad_gp($gp['GP_ID'])) {
				$errors[] = "Rejected bad GP record: {$gp['GP_ID']}";
			} else {
				if ($pasGp = Yii::app()->db_pas->createCommand("select * from silver.ENV040_PROFDETS where obj_prof = '{$gp['GP_ID']}' order by date_fr desc")->queryRow()) {
					if ($gp = Gp::model()->noPas()->find('obj_prof = ?', array($pasGp['OBJ_PROF']))) {
						// Update existing GP
						if ($contact = Contact::model()->findByPk($gp->contact_id)) {
							if (!$this->populateContact($contact, $pasGp)) {
								$errors[] = "Failed to populate contact for GP $gp->id: ".print_r($this->errors,true);
							}

							if ($address = $contact->address) {
								if (!$this->populateAddress($address, $pasGp)) {
									$errors[] = "Failed to populate address for GP $gp->id: ".print_r($this->errors,true);
								}

								if (!$this->populateGp($gp, $pasGp)) {
									$errors[] = "Failed to populate GP $gp->id: ".print_r($this->errors,true);
								}
							} else {
								$errors[] = "No address for gp contact " . $contact->id;
								if ($verbose) echo "x";
							}
						} else {
							$errors[] = "Unable to update existing gp contact " . $pasGp['OBJ_PROF'];
							if ($verbose) echo "x";
						}
					} else {
						$address = new Address;

						if (!$this->populateAddress($address, $pasGp)) {
							$errors[] = "Unable to save new GP address: ".print_r($this->errors,true);
						}

						$contact = new Contact;

						$contact->consultant = 0;
						$contact->address_id = $address->id;

						if (!$this->populateContact($contact, $pasGp)) {
							$errors[] = "Unable to save new GP contact: ".print_r($this->errors,true);
						}

						$gp = new Gp;

						$gp->contact_id = $contact->id;

						if (!$this->populateGp($gp, $pasGp)) {
							$errors[] = "Unable to save new GP: ".print_r($this->errors,true);
						}
					}

					// Update patient
					if ($patient = Patient::Model()->noPas()->findByPk($latestGP['PATIENT_ID'])) {
						$patient->gp_id = $gp->id;
						if (!$patient->save()) {
							$errors[] = "Unable to save patient {$latestGP['PATIENT_ID']}: ".print_r($patient->getErrors(),true);
						}
						if ($verbose) echo ".";
					} else {
						$errors[] = "Unable to find patient {$latestGP['PATIENT_ID']}";
						if ($verbose) echo "x";
					}
				} else {
					$errors[] = "Unable to find GP for patient id={$latestGP['PATIENT_ID']}, GP_ID={$gp['GP_ID']}";
					if ($verbose) echo "x";
				}
			}
		}

		return $errors;
	}

	/**
	 * @deprecated
	 */
	protected function populateContact($contact, $pasGp)
	{
		$contact->title = $pasGp['TITLE'];
		$contact->first_name = $pasGp['FN1'] . ' ' . $pasGp['FN2'];
		$contact->last_name = $pasGp['SN'];
		$contact->primary_phone = $pasGp['TEL_1'];

		if (!$contact->save()) {
			$this->errors = $contact->getErrors();
			return false;
		}

		return true;
	}

	/**
	 * @deprecated
	 */
	protected function populateAddress($address, $pasGp, $contact_id = null)
	{
		$address->address1 = trim($pasGp['ADD_NAM'] . ' ' . $pasGp['ADD_NUM'] . ' ' . $pasGp['ADD_ST']);
		$address->address2 =  $pasGp['ADD_DIS'];
		$address->city = $pasGp['ADD_TWN'];
		$address->county = $pasGp['ADD_CTY'];
		$address->postcode = $pasGp['PC'];
		$address->country_id = 1;
		if($contact_id) {
			$address->parent_id = $contact_id;
		}

		if (!$address->save()) {
			$this->errors = $address->getErrors();
			return false;
		}

		return true;
	}

	/**
	 * @deprecated
	 */
	protected function populateGp($gp, $pasGp)
	{
		$gp->obj_prof = $pasGp['OBJ_PROF'];
		$gp->nat_id = $pasGp['NAT_ID'];

		if (!$gp->save()) {
			$this->errors = $gp->getErrors();
			return false;
		}

		return true;
	}
	
	
}
