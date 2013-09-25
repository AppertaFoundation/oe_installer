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
					<section class="box patient-info associated-data">
						<header class="box-header">
							<h3 class="box-title">
								<span class="icon-patient-clinician-hd_flag"></span>
								Other ophthalmic diagnoses
							</h3>
							<a href="#" class="toggle-trigger toggle-hide">
								<span class="icon-showhide">
									Show/hide this section
								</span>
							</a>
						</header>
						<table class="plain patient-data">
							<thead>
								<tr>
									<th>Date</th>
									<th>Diagnosis</th>
									<?php if (BaseController::checkUserLevel(4)) { ?><th>Edit</th><?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($this->patient->ophthalmicDiagnoses as $diagnosis) {?>
									<tr>
										<td><?php echo $diagnosis->dateText?></td>
										<td><?php echo $diagnosis->eye->adjective?> <?php echo $diagnosis->disorder->term?></td>
										<?php if (BaseController::checkUserLevel(4)) { ?>
										<td><a href="#" rel="<?php echo $diagnosis->id?>"><strong>Remove</strong></a></td>
										<?php } ?>
									</tr>
								<?php }?>
							</tbody>
						</table>


						<?php if (BaseController::checkUserLevel(4)) { ?>
							<div class="box-actions">
								<button id='btn-add_new_ophthalmic_diagnosis' class="secondary small">
									Add Ophthalmic Diagnosis
								</button>
							</div>

						<div id="add_new_ophthalmic_diagnosis" style="display: none;">
							<h5>Add ophthalmic diagnosis</h5>
							<?php
							$form = $this->beginWidget('CActiveForm', array(
									'id'=>'add-ophthalmic-diagnosis',
									'enableAjaxValidation'=>false,
									'htmlOptions' => array('class'=>'sliding'),
									'action'=>array('patient/adddiagnosis'),
								))?>

							<?php $form->widget('application.widgets.DiagnosisSelection',array(
									'field' => 'ophthalmic_disorder_id',
									'options' => CommonOphthalmicDisorder::getList(Firm::model()->findByPk($this->selectedFirmId)),
									'code' => 130,
									'default' => false,
									'layout' => 'patientSummary',
									'loader' => 'add_ophthalmic_diagnosis_loader',
								))?>

							<div id="add_ophthalmic_diagnosis_loader" style="display: none;">
								<img align="left" class="loader" src="<?php echo Yii::app()->createUrl('/img/ajax-loader.gif')?>" />
								<div>
									searching...
								</div>
							</div>

							<input type="hidden" name="patient_id" value="<?php echo $this->patient->id?>" />

							<fieldset class="diagnosis_eye row field-row">
								<legend class="large-3 column">
									Eye:
								</legend>
								<?php foreach (Eye::model()->findAll(array('order'=>'display_order')) as $i => $eye) {?>
									<input type="radio" name="diagnosis_eye" class="diagnosis_eye" value="<?php echo $eye->id?>"<?php if ($i==0) {?> checked="checked"<?php }?> /> <?php echo $eye->name?>
								<?php }?>
							</fieldset>

							<?php $this->renderPartial('_fuzzy_date')?>
							<div class="ophthalmic_diagnoses_form_errors"></div>

							<div align="right">
								<img src="<?php echo Yii::app()->createUrl('/img/ajax-loader.gif')?>" class="add_ophthalmic_diagnosis_loader" style="display: none;" />
								<div class="buttons">
									<button type="submit" class="secondary small btn_save_ophthalmic_diagnosis">
										Save
									</button>
									<button class="warning small btn_cancel_ophthalmic_diagnosis">
										Cancel
									</button>
								</div>
							</div>

							<?php $this->endWidget()?>

						<?php } ?>
							</form>
							</div>

					</section>
				<?php if (BaseController::checkUserLevel(4)) { ?>
				<div id="confirm_remove_diagnosis_dialog" title="Confirm remove diagnosis" style="display: none;">
					<div>
						<div id="delete_diagnosis">
							<div class="alertBox" style="margin-top: 10px; margin-bottom: 15px;">
								<strong>WARNING: This will remove the diagnosis from the patient record.</strong>
							</div>
							<p>
								<strong>Are you sure you want to proceed?</strong>
							</p>
							<div class="buttonwrapper" style="margin-top: 15px; margin-bottom: 5px;">
								<input type="hidden" id="diagnosis_id" value="" />
								<button type="submit" class="classy red venti btn_remove_diagnosis"><span class="button-span button-span-red">Remove diagnosis</span></button>
								<button type="submit" class="classy green venti btn_cancel_remove_diagnosis"><span class="button-span button-span-green">Cancel</span></button>
								<img class="loader" src="<?php echo Yii::app()->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
							</div>
						</div>
					</div>
				</div>
<script type="text/javascript">

	$('#btn-add_new_ophthalmic_diagnosis').click(function() {
		$('#add_new_ophthalmic_diagnosis').slideToggle('fast');
		$('#btn-add_new_ophthalmic_diagnosis').attr('disabled',true);
	});
	$('button.btn_cancel_ophthalmic_diagnosis').click(function() {
		$('#add_new_ophthalmic_diagnosis').slideToggle('fast');
		$('#btn-add_new_ophthalmic_diagnosis').attr('disabled',false);
		$('#btn-add_new_ophthalmic_diagnosis').removeClass('disabled').addClass('green');
		$('#btn-add_new_ophthalmic_diagnosis span').removeClass('button-span-disabled').addClass('button-span-green');
		return false;
	});
	$('button.btn_save_ophthalmic_diagnosis').click(function() {
		$.ajax({
			'type': 'POST',
			'dataType': 'json',
			'url': baseUrl+'/patient/validateadddiagnosis',
			'data': $('#add-ophthalmic-diagnosis').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(data) {
				$('div.ophthalmic_diagnoses_form_errors').html('');
				if (data.length == 0) {
					$('img.add_ophthalmic_diagnosis_loader').show();
					$('#add-ophthalmic-diagnosis').submit();
					return true;
				} else {
					for (var i in data) {
						$('div.ophthalmic_diagnoses_form_errors').append('<div class="errorMessage">'+data[i]+'</div>');
					}
				}
			}
		});
		return false;
	});

	</script>
<?php } ?>
