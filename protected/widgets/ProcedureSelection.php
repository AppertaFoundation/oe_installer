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

class ProcedureSelection extends BaseCWidget {
	public $subsections;
	public $procedures;
	public $newRecord;
	public $selected_procedures;
	public $form;
	public $durations = false;
	public $class;
	public $total_duration = 0;
	public $last;

	public function run() {
		$firm = Firm::model()->findByPK(Yii::app()->session['selected_firm_id']);

		$subspecialty = $firm->serviceSubspecialtyAssignment->subspecialty;
		$this->subsections = SubspecialtySubsection::model()->getList($subspecialty->id);
		$this->procedures = array();
		
		if (empty($_POST)) {
			if (!$this->selected_procedures) {
				$this->selected_procedures = $this->element->procedures;
				if ($this->durations) {
					$this->total_duration = $this->element->total_duration;
				}
			}
		} else {
			$this->selected_procedures = array();

			if (isset($_POST['Procedures']) && is_array($_POST['Procedures'])) {
				foreach ($_POST['Procedures'] as $proc_id) {
					$proc = Procedure::model()->findByPk($proc_id);
					$this->selected_procedures[] = $proc;
					if ($this->durations) {
						$this->total_duration += $proc->default_duration;
					}
				}
			}
		}
		
		if (empty($this->subsections)) {
			foreach (Procedure::model()->getListBySubspecialty($subspecialty->id) as $proc_id => $name) {
				if (empty($_POST)) {
					$found = false;
					if ($this->selected_procedures) {
						foreach ($this->selected_procedures as $procedure) {
							if ($procedure->id == $proc_id) {
								$found = true; break;
							}
						}
					}
					if (!$found) {
						$this->procedures[$proc_id] = $name;
					}
				} else {
					if (!@$_POST['Procedures'] || !in_array($proc_id,$_POST['Procedures'])) {
						$this->procedures[$proc_id] = $name;
					}
				}
			}
		}

		$this->class = get_class($this->element);
		parent::run();
	}
}
?>
