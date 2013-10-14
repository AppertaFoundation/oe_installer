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
?>

<section class="box patient-info associated-data js-toggle-container">

	<header class="box-header">
		<h3 class="box-title">
			<span class="icon-patient-clinician-hd_flag"></span>
			Medication
		</h3>
		<a href="#" class="toggle-trigger toggle-hide js-toggle">
			<span class="icon-showhide">
				Show/hide this section
			</span>
		</a>
	</header>

	<div class="js-toggle-body">
		<table class="plain patient-data">
			<thead>
			<tr>
				<th width="85px">Medication</th>
				<th>Route</th>
				<th>Option</th>
				<th>Frequency</th>
				<th>Start date</th>
				<th>Edit</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->patient->medications as $medication) {?>
				<tr>
					<td><?php echo $medication->drug->name?></td>
					<td><?php echo $medication->route->name?></td>
					<td><?php echo $medication->option ? $medication->option->name : '-'?></td>
					<td><?php echo $medication->frequency->name?></td>
					<td><?php echo $medication->NHSDate('start_date')?></td>
					<td>
						<a href="#" class="small editMedication" rel="<?php echo $medication->id?>"><strong>Edit</strong></a>&nbsp;&nbsp;
						<a href="#" class="small removeMedication" rel="<?php echo $medication->id?>"><strong>Remove</strong></a>
					</td>
				</tr>
			<?php }?>
			</tbody>
		</table>

		<div class="box-actions">
			<button  id="btn-add_medication" class="secondary small">
				Add Medication
			</button>
		</div>

		<div id="add_medication" style="display: none;">
			<?php
			$form = $this->beginWidget('FormLayout', array(
					'id'=>'add-medication',
					'enableAjaxValidation'=>false,
					'htmlOptions' => array('class'=>'sliding form add-data'),
					'action'=>array('patient/addMedication'),
					'layoutColumns'=>array(
						'label' => 3,
						'field' => 9
					),
				))?>
			<fieldset class="field-row">

				<legend><strong>Add medication</strong></legend>

				<input type="hidden" name="edit_medication_id" id="edit_medication_id" value="" />
				<input type="hidden" name="patient_id" value="<?php echo $this->patient->id?>" />

				<div class="patientMedication field-row row">
					<div class="<?php echo $form->columns('label');?>">
						<label for="">Medication:</label>
					</div>
					<div class="large-7 column end">
						<div class="field-row">
							<?php echo CHtml::dropDownList('drug_id','',Drug::model()->listBySubspecialty($firm->getSubspecialtyID()),array('empty'=>'- Select -'))?>
						</div>

						<div class="patientMedication field-row">
							<div class="label"></div>
							<?php
							$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
									'name' => 'drug_id',
									'id' => 'autocomplete_drug_id',
									'source' => "js:function(request, response) {
													$.getJSON('".$this->createUrl('DrugList')."', {
														term : request.term,
													}, response);
												}",
									'options' => array(
										'select' => "js:function(event, ui) {
														$('#selectedMedicationName').text(ui.item.value);
														$('#selectedMedicationID').val(ui.item.id);
														$(this).val('');
														return false;
													}",
									),
									'htmlOptions' => array(
										'placeholder' => 'or search formulary',
									),
								))?>
						</div>
					</div>
				</div>

				<div class="patientMedication">
					<div class="label"></div>
					<div class="data">
						<span id="selectedMedicationName" style="font-weight: bold;"></span>
						<input type="hidden" name="selectedMedicationID" id="selectedMedicationID" value="" />
					</div>
				</div>

				<div class="field-row row">
					<div class="large-3 column">
						<label for="">Option:</label>
					</div>
					<div class="large-7 column end">
						<?php echo CHtml::dropDownList('route_id','',CHtml::listData(DrugRoute::model()->findAll(),'id','name'),array('empty'=>'- Select -'))?>
					</div>
				</div>

				<div class="patientMedication routeOption" style="display: none;">
					<div class="label">
						Option:
					</div>
					<div class="data">
					</div>
				</div>

				<div class="field-row row">
					<div class="large-3 column">
						<label for="">Frequency:</label>
					</div>
					<div class="large-7 column end">
						<?php echo CHtml::dropDownList('frequency_id','',CHtml::listData(DrugFrequency::model()->findAll(array('order'=>'display_order')),'id','name'),array('empty'=>'- Select -'))?>
					</div>
				</div>

				<div class="field-row row">
					<div class="large-3 column">
						<label for="">Date from:</label>
					</div>
					<div class="large-3 column end">
						<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
								'name'=>'start_date',
								'id'=>'start_date',
								'options'=>array(
									'showAnim'=>'fold',
									'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
								),
								'value' => date('j M Y'),
								'htmlOptions'=>array('style'=>'width: 90px;')
							))?>
					</div>
				</div>

				<div class="medication_form_errors"></div>

				<div align="right">
					<img src="<?php echo Yii::app()->createUrl('/img/ajax-loader.gif')?>" class="add_medication_loader" style="display: none;" />
					<div class="buttons">
						<button type="submit" class="secondary small btn_save_medication">
							Save
						</button>
						<button class="warning small btn_cancel_medication">
							Cancel
						</button>
					</div>
				</div>
			</fieldset>
			<?php $this->endWidget()?>


		</div>
	</div>
</section>

<div id="confirm_remove_medication_dialog" title="Confirm remove medication" style="display: none;">
	<div>
		<div id="delete_medication">
			<div class="alertBox" style="margin-top: 10px; margin-bottom: 15px;">
				<strong>WARNING: This will remove the medication from the patient record.</strong>
			</div>
			<p>
				<strong>Are you sure you want to proceed?</strong>
			</p>
			<div class="buttonwrapper" style="margin-top: 15px; margin-bottom: 5px;">
				<input type="hidden" id="medication_id" value="" />
				<button type="submit" class="classy red venti btn_remove_medication"><span class="button-span button-span-red">Remove medication</span></button>
				<button type="submit" class="classy green venti btn_cancel_remove_medication"><span class="button-span button-span-green">Cancel</span></button>
				<img class="loader" src="<?php echo Yii::app()->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$('#btn-add_medication').click(function() {
		$('div.patientMedication #route_id').val('');
		$('div.patientMedication #drug_id').val('');
		$('div.patientMedication #frequency_id').val('');
		$('div.patientMedication #start_date').val('');
		$('div.routeOption .date').html('');
		$('div.routeOption').hide();

		$('#add_medication').slideToggle('fast');
		$('#btn-add_medication').attr('disabled',true);
		$('#btn-add_medication').removeClass('green').addClass('disabled');
		$('#btn-add_medication span').removeClass('button-span-green').addClass('button-span-disabled');
	});
	$('button.btn_cancel_medication').click(function() {
		$('#add_medication').slideToggle('fast');
		$('#btn-add_medication').attr('disabled',false);
		$('#btn-add_medication').removeClass('disabled').addClass('green');
		$('#btn-add_medication span').removeClass('button-span-disabled').addClass('button-span-green');
		return false;
	});
	$('#drug_id').change(function() {
		if ($(this).val() != '') {
			selectMedication($(this).val(),$(this).children('option:selected').text());
			$('#drug_id').val('');
		}
	});

	function selectMedication(id, name)
	{
		$('#selectedMedicationName').text(name);
		$('#selectedMedicationID').val(id);

		$.ajax({
			'type': 'GET',
			'dataType': 'json',
			'url': baseUrl+'/patient/DrugDefaults?drug_id='+id,
			'success': function(data) {
				if (data['route_id']) {
					$('#route_id').val(data['route_id']);
					$('#route_id').change();
				}
				if (data['frequency_id']) {
					$('#frequency_id').val(data['frequency_id']);
				}
			}
		});
	}

	handleButton($('button.btn_save_medication'),function(e) {
		e.preventDefault();

		$.ajax({
			'type': 'POST',
			'data': $('#add-medication').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'url': baseUrl+'/patient/validateAddMedication',
			'success': function(data) {
				$('div.medication_form_errors').html('');

				if (data.length == 0) {
					$('#add-medication').submit();
					return;
				}

				enableButtons();

				for (var i in data) {
					$('div.medication_form_errors').append('<div class="errorMessage">'+data[i]+'</div>');
				}
			}
		});
	});
	$('a.editMedication').click(function(e) {
		var medication_id = $(this).attr('rel');

		$('#edit_medication_id').val(medication_id);

		$.ajax({
			'type': 'GET',
			'dataType': 'json',
			'url': baseUrl+'/patient/getMedication?medication_id='+medication_id,
			'success': function(data) {
				$('div.patientMedication #route_id').val(data['route_id']);
				$('#selectedMedicationID').val(data['drug_id']);
				$('#selectedMedicationName').text(data['drug_name']);
				$('div.patientMedication #frequency_id').val(data['frequency_id']);
				$('div.patientMedication #start_date').val(data['start_date']);
				$('div.routeOption .data').html(data['route_options']);
				$('div.routeOption').show();
				$('div.patientMedication #option_id').val(data['option_id']);
			}
		});

		$('#add_medication').slideToggle('fast');
		$('#btn-add_medication').attr('disabled',true);
		$('#btn-add_medication').removeClass('green').addClass('disabled');
		$('#btn-add_medication span').removeClass('button-span-green').addClass('button-span-disabled');

		e.preventDefault();
	});
	$('#route_id').change(function() {
		var route_id = $(this).val();

		if (route_id == '') {
			$('div.routeOption').hide();
			$('div.routeOption .data').html('');
		} else {
			$.ajax({
				'type': 'GET',
				'url': baseUrl+'/patient/getDrugRouteOptions?route_id='+route_id,
				'success': function(html) {
					$('div.routeOption .data').html(html);
					if (html.length >0) {
						$('div.routeOption').show();
					} else {
						$('div.routeOption').hide();
					}
				}
			});
		}
	});
</script>
