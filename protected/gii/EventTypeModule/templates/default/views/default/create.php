<?php
?>

<h3 class="withEventIcon" style="background:transparent url(/img/_elements/icons/event/medium/treatment_operation_note.png) center left no-repeat;"><?php echo $this->event_type->name ?></h3>

<div>
	<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id'=>'clinical-create',
			'enableAjaxValidation'=>false,
			'htmlOptions' => array('class'=>'sliding'),
			'focus'=>'#procedure_id'
		));
	?>

		<?php $this->renderDefaultElements($this->action->id, $form); ?>
		<?php $this->renderOptionalElements($this->action->id, $form); ?>

		<?php $this->displayErrors($errors)?>

		<div class="cleartall"></div>
		<div class="form_button">
			<img class="loader" style="display: none;" src="/img/ajax-loader.gif" alt="loading..." />&nbsp;
			<button type="submit" class="classy green venti" id="save" name="save"><span class="button-span button-span-green">Save</span></button>
			<button type="submit" class="classy red venti" id="cancel" name="cancel"><span class="button-span button-span-red">Cancel</span></button>
		</div>
	<?php $this->endWidget(); ?>
</div>

<?php $this->footer() ?>
