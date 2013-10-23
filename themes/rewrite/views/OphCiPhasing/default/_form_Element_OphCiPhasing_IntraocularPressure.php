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

$instruments = $element->getInstrumentOptions();
$key = 0;
?>
<section class="element">
	<div class="element-fields element-eyes row">
		<input type="hidden" name="intraocularpressure_readings_valid" value="1" />
		<div class="element-eye right-eye column" data-side="right">
			<a href="#" class="icon-remove-side">Remove side</a>
			<?php echo $form->dropDownList($element, 'right_instrument_id', $instruments, array(), false, array('label' => 2, 'field' => 10))?>
			<?php echo $form->radioBoolean($element, 'right_dilated', array(), array('label' => 2, 'field' => 10))?>
			<fieldset class="row field-row">
				<legend class="large-2 column">
					Readings:
				</legend>
				<div class="large-10 column">
					<table class="blank">
						<thead>
							<tr>
								<th>Time (HH:MM)</th>
								<th>mm Hg</th>
								<th><div class="hide-offscreen">Actions</div></th>
							</tr>
						</thead>
						<tbody class="readings-right">
							<?php
							$right_readings = (isset($_POST['intraocularpressure_readings_valid']) ? $element->convertReadings(@$_POST['intraocularpressure_reading'], 'right') : $element->right_readings);
								if ($right_readings) {
									foreach ($right_readings as $index => $reading) {
										$this->renderPartial('_form_Element_OphCiPhasing_IntraocularPressure_Reading', array(
											'key' => $key,
											'reading' => $reading,
											'side' => $reading->side,
											'no_remove' => ($index == 0)
										));
										$key++;
									}
								} else {
									$this->renderPartial('_form_Element_OphCiPhasing_IntraocularPressure_Reading', array(
										'key' => $key,
										'side' => 0,
										'no_remove' => true
									));
									$key++;
								}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"><button class="secondary small addReading">Add</button></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</fieldset>
			<?php echo $form->textArea($element, 'right_comments', array(), false, array('class' => 'autosize', 'placeholder' => 'Enter comments ...'), array('label' => 2, 'field' => 10))?>
		</div>
		<div class="element-eye left-eye column" data-side="left">
			<a href="#" class="icon-remove-side">Remove side</a>
			<?php echo $form->dropDownList($element, 'left_instrument_id', $instruments, array(), false, array('label' => 2, 'field' => 10))?>
			<?php echo $form->radioBoolean($element, 'left_dilated', array(), array('label' => 2, 'field' => 10))?>
			<fieldset class="row field-row">
				<legend class="large-2 column">
					Readings:
				</legend>
				<div class="large-10 column">
					<table class="blank">
						<thead>
							<tr>
								<th>Time (HH:MM)</th>
								<th>mm Hg</th>
								<th><div class="hide-offscreen">Actions</div></th>
							</tr>
						</thead>
						<tbody class="readings-left">
							<?php
							$left_readings = (isset($_POST['intraocularpressure_readings_valid']) ? $element->convertReadings(@$_POST['intraocularpressure_reading'], 'left') : $element->left_readings);
								if ($left_readings) {
									foreach ($left_readings as $index => $reading) {
										$this->renderPartial('_form_Element_OphCiPhasing_IntraocularPressure_Reading', array(
											'key' => $key,
											'reading' => $reading,
											'side' => $reading->side,
											'no_remove' => ($index == 0)
										));
										$key++;
									}
								} else {
									$this->renderPartial('_form_Element_OphCiPhasing_IntraocularPressure_Reading', array(
										'key' => $key,
										'side' => 1,
										'no_remove' => true
									));
									$key++;
								}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"><button class="secondary small addReading">Add</button></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</fieldset>
			<?php echo $form->textArea($element, 'left_comments', array(), false, array('class' => 'autosize', 'placeholder' => 'Enter comments ...'), array('label' => 2, 'field' => 10))?>
		</div>
	</div>
</section>
<script id="intraocularpressure_reading_template" type="text/html">
	<?php
	$this->renderPartial('_form_Element_OphCiPhasing_IntraocularPressure_Reading', array(
			'key' => '{{key}}',
			'side' => '{{side}}',
	));
	?>
</script>
