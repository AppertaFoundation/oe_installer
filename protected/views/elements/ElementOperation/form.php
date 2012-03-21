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

if ($this->nopost) {
	$selected_procedures = $model->procedures;
} else {
	$selected_procedures = array();

	if (isset($_POST['Procedures']) && is_array($_POST['Procedures'])) {
		foreach ($_POST['Procedures'] as $proc_id) {
			$selected_procedures[] = Procedure::model()->findByPk($proc_id);
		}
	}
}

if (!isset($_POST['ElementOperation']['decision_date'])) {
	if ($model->decision_date) {
		$_POST['ElementOperation']['decision_date'] = $model->decision_date;
	} else {
		$_POST['ElementOperation']['decision_date'] = date('j M Y',time());
	}
}

if (!$model->site_id) {
	$model->site_id = Yii::app()->request->cookies['site_id']->value;
}

?>
					<script type="text/javascript" src="/js/element_operation.js"></script>
					<h4>Operation details</h4>

					<?php echo $form->radioButtons($model, 'eye_id', 'eye');?>

					<?php $form->widget('application.widgets.ProcedureSelection',array(
						'model' => $model,
						'subsections' => $subsections,
						'procedures' => $procedures,
						'newRecord' => $newRecord,
						'selected_procedures' => $selected_procedures
					));
					?>

					<?php echo $form->radioBoolean($model, 'consultant_required')?>
					<?php echo $form->radioButtons($model, 'anaesthetic_type_id', 'anaesthetic_type');?>
					<?php echo $form->radioBoolean($model, 'overnight_stay')?>
					<?php echo $form->dropDownList($model, 'site_id', CHtml::listData(Site::model()->findAll(array('order' => 'short_name')), 'id', 'short_name'))?>
					<?php echo $form->radioButtons($model, 'priority_id', 'priority')?>
					<?php echo $form->datePicker($model, 'decision_date', array('maxDate' => 'today'), array('style'=>'width: 110px;'))?>
					<?php echo $form->textArea($model, 'comments', array('rows'=>4,'cols'=>50))?>
