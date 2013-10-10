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

<section class="element <?php echo $element->elementType->class_name?>"
	data-element-type-id="<?php echo $element->elementType->id ?>"
	data-element-type-class="<?php echo $element->elementType->class_name ?>"
	data-element-type-name="<?php echo $element->elementType->name ?>"
	data-element-display-order="<?php echo $element->elementType->display_order ?>">
	<header class="element-header">
		<h3 class="element-title">
			<?php echo $element->elementType->name ?>
		</h3>
	</header>
	<fieldset class="element-fields">
	<?php echo $form->radioButtons($element, 'anaesthetic_type_id', 'anaesthetic_type');?>
	<?php echo $form->radioButtons($element, 'anaesthetist_id', 'anaesthetist', false, false, $element->hidden)?>
	<?php if ($element->getSetting('fife')) {?>
		<?php echo $form->dropDownList($element, 'anaesthetic_witness_id', CHtml::listData($element->surgeons, 'id', 'FullName'), array('empty'=>'- Please select -'), $element->witness_hidden)?>
	<?php }?>
	<?php echo $form->radioButtons($element, 'anaesthetic_delivery_id', 'anaesthetic_delivery',false,4, $element->hidden)?>
	<?php echo $form->multiSelectList($element, 'AnaestheticAgent', 'anaesthetic_agents', 'anaesthetic_agent_id', $element->anaesthetic_agent_list, $element->anaesthetic_agent_defaults, array('empty' => '- Anaesthetic agents -', 'label' => 'Agents'), $element->hidden)?>
	<?php echo $form->multiSelectList($element, 'OphTrOperationnote_AnaestheticComplications', 'anaesthetic_complications', 'anaesthetic_complication_id', CHtml::listData(OphTrOperationnote_AnaestheticComplications::model()->findAll(), 'id', 'name'), array(), array('empty' => '- Complications -', 'label' => 'Complications'), $element->hidden)?>
	<?php echo $form->textArea($element, 'anaesthetic_comment', array('rows' => 4, 'cols' => 60), $element->hidden)?>
	</fieldset>
</section>
