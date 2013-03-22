<div class="whiteBox forClinicians" id="patient_allergies">
	<div class="patient_actions">
		<span class="aBtn"><a class="sprite showhide" href="#"><span
				class="hide"></span> </a> </span>
	</div>
	<div class="icon_patientIssue"></div>
	<h4>Allergies</h4>
	<div class="data_row">
		<table class="subtleWhite">
			<thead>
				<tr>
					<th width="80%">Allergies</th>
					<?php if(BaseController::checkUserLevel(3)) { ?><th>Edit</th><?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach($this->patient->allergies as $allergy) { ?>
				<tr data-allergy-id="<?php echo $allergy->id ?>">
					<td><?php echo $allergy->name ?></td>
					<?php if(BaseController::checkUserLevel(3)) { ?>
					<td><a href="#" class="small removeAllergy"><strong>Remove</strong>
					<?php } ?>
					</a></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php if(BaseController::checkUserLevel(3)) { ?>
	<div class="data_row" id="add_new_allergy">
		Add Allergy:
		<?php echo CHtml::dropDownList('allergy_id', null, CHtml::listData($this->allergyList(), 'id', 'name'), array('empty' => '-- Select --'));?>
		<button id="btn_add_allergy" class="classy green mini" type="button"><span class="button-span button-span-green">Add</span></button>
	</div>
	<?php } ?>
</div>
<?php if(BaseController::checkUserLevel(3)) { ?>
<div id="confirm_remove_allergy_dialog" title="Confirm remove allergy" style="display: none;">
	<div>
		<div id="delete_allergy">
			<div class="alertBox" style="margin-top: 10px; margin-bottom: 15px;">
				<strong>WARNING: This will remove the allergy from the patient record.</strong>
			</div>
			<p>
				<strong>Are you sure you want to proceed?</strong>
			</p>
			<div class="buttonwrapper" style="margin-top: 15px; margin-bottom: 5px;">
				<input type="hidden" id="allergy_id" value="" />
				<button type="submit" class="classy red venti btn_remove_allergy"><span class="button-span button-span-red">Remove allergy</span></button>
				<button type="submit" class="classy green venti btn_cancel_remove_allergy"><span class="button-span button-span-green">Cancel</span></button>
				<img class="loader" src="<?php echo Yii::app()->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
			</div>
		</div>
	</div>
</div>
<!-- #patient_allergies -->
<script type="text/javascript">

	var patient_id = <?php echo $this->patient->id; ?>;

	// Disable current allergies in dropdown
	$('#patient_allergies tr').each(function(index) {
		var allergy_id = $(this).attr('data-allergy-id');
		var option = $('#allergy_id option[value="' + allergy_id + '"]');
		if(option) {
			option.attr('disabled','disabled');
		}
	});
	
	// Add allergy
	$('body').delegate('#btn_add_allergy','click', function() {
		var allergy_id = $('#allergy_id').val();
		if(allergy_id) {
			var option = $('#allergy_id option:selected').first();
			$.post("<?php echo Yii::app()->createUrl('patient/AddAllergy')?>", { patient_id: patient_id, allergy_id: allergy_id }, function(data) {
				var new_row = $('<tr data-allergy-id="'+allergy_id+'"></tr>');
				new_row.append($('<td>'+option.text()+'</td>'), $('<td><a href="#" class="small removeAllergy"><strong>Remove</strong></a></td>'));
				$('#patient_allergies tbody').append(new_row);
				option.attr('disabled','disabled');
			});
		}
		$('#allergy_id').val('');
		return false;
	});
	
	// Remove allergy
	$('#patient_allergies').delegate('a.removeAllergy', 'click', function() {
		$('#allergy_id').val($(this).closest('tr').attr('data-allergy-id'));

		$('#confirm_remove_allergy_dialog').dialog({
			resizable: false,
			modal: true,
			width: 560
		});

		return false;
	});

	$('button.btn_remove_allergy').click(function() {
		$("#confirm_remove_allergy_dialog").dialog("close");

		$.post("<?php echo Yii::app()->createUrl('patient/RemoveAllergy')?>", { patient_id: <?php echo $this->patient->id?>, allergy_id: $('#allergy_id').val() }, function(data) {
			$('tr[data-allergy-id="'+$('#allergy_id').val()+'"]').remove();
			$('#allergy_id option[value="' + $('#allergy_id').val() + '"]').removeAttr('disabled');
			$('#allergy_id').val('');
		});

		return false;
	});

	$('button.btn_cancel_remove_allergy').click(function() {
		$('#allergy_id').val('');
		$("#confirm_remove_allergy_dialog").dialog("close");
		return false;
	});
</script>
<?php } ?>
