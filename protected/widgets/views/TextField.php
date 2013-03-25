<?php if (@$htmlOptions['nowrapper']) {?>
	<?php echo CHtml::textField($name, $value, $htmlOptions)?>
<?php }else{?>
	<div id="div_<?php echo get_class($element)?>_<?php echo $field?>" class="eventDetail">
		<div class="label"><?php echo empty($htmlOptions['label']) ? CHtml::encode($element->getAttributeLabel($field)) : $htmlOptions['label']?>:</div>
		<div class="data">
			<?php if (@$htmlOptions['password']) {?>
				<?php echo CHtml::passwordField($name, $value, $htmlOptions)?>
			<?php }else{?>
				<?php echo CHtml::textField($name, $value, $htmlOptions)?>
			<?php }?>
		</div>
	</div>
<?php }?>
