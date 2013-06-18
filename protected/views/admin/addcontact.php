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

?>
<div class="report curvybox white">
	<div class="admin">
		<h3 class="georgia">Add contact</h3>
		<?php echo $this->renderPartial('_form_errors',array('errors'=>$errors))?>
		<div>
			<?php
			$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
				'id'=>'adminform',
				'enableAjaxValidation'=>false,
				'htmlOptions' => array('class'=>'sliding'),
				'focus'=>'#contactname'
			))?>
			<?php echo $form->textField($contact,'title')?>
			<?php echo $form->textField($contact,'first_name')?>
			<?php echo $form->textField($contact,'last_name')?>
			<?php echo $form->textField($contact,'nick_name')?>
			<?php echo $form->textField($contact,'primary_phone')?>
			<?php echo $form->textField($contact,'qualifications')?>
			<?php echo $form->dropDownList($contact,'contact_label_id',CHtml::listData(ContactLabel::model()->findAll(array('order'=>'name')),'id','name'),array('empty'=>'- None -'))?>
			<div style="margin-left: 170px;">
				<?php echo EventAction::button('Add label','add_label',array('colour'=>'blue'))->toHtml()?>
			</div>
			<?php $this->endWidget()?>
		</div>
	</div>
</div>
<?php echo $this->renderPartial('_form_errors',array('errors'=>$errors))?>
<div>
	<?php echo EventAction::button('Save', 'save', array('colour' => 'green'))->toHtml()?>
	<?php echo EventAction::button('Cancel', 'cancel', array('colour' => 'red'))->toHtml()?>
	<img class="loader" src="<?php echo Yii::app()->createUrl('/img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('#Contact_title').focus();
	});
</script>
