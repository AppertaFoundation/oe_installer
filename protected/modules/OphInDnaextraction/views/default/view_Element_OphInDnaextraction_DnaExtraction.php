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
<section class="sub-element">
	<header class="sub-element-header">
		<h3 class="sub-element-title"><?php echo $element->elementType->name?></h3>
		&nbsp;&nbsp;
		<?php echo CHtml::link("edit",Yii::app()->createUrl('/'.$element->elementType->eventType->class_name.'/default/update/'.$element->event_id))?>&nbsp;&nbsp;
		<?php echo CHtml::link("delete",Yii::app()->createUrl('/'.$element->elementType->eventType->class_name.'/default/delete/'.$element->event_id))?>
	</header>
	<div class="sub-element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('box_id')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->box ? $element->box->value : 'None')?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('letter_id')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->letter ? $element->letter->value : 'None')?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('number_id')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->number ? $element->number->value : 'None')?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('extracted_date')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo $element->extracted_date ? $element->NHSDate('extracted_date') : 'None'?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('extracted_by')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->extracted_by)?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('comments')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->comments)?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('dna_concentration')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->dna_concentration)?></div>
			</div>
		</div>
	</div>
	<div class="element-data">
		<div class="row data-row">
			<div class="large-2 column">
				<div class="data-label"><?php echo $element->getAttributeLabel('volume')?>:</div>
			</div>
			<div class="large-10 column">
				<div class="data-value"><?php echo CHtml::encode($element->volume)?></div>
			</div>
		</div>
	</div>
</section>
