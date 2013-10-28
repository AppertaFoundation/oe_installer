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
<?php $this->beginContent('//patient/event_container'); ?>

	<h2 class="event-title"><?php echo $this->event_type->name ?></h2>

	<div id="event_<?php echo $this->module->name?>">
		<?php
			$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
				'id'=>'correspondence-create',
				'enableAjaxValidation'=>false,
				'layoutColumns' => array(
					'label'=> 2,
					'field'=>10
				)
			));

			// Event actions
			$this->event_actions[] = EventAction::button(
				'Save draft',
				'savedraft',
				array('level' => 'secondary'),
				array('id' => 'et_save_draft', 'class'=>'button small', 'form' => 'correspondence-create')
			);
			$this->event_actions[] = EventAction::button(
				'Save and print',
				'saveprint',
				array('level' => 'secondary'),
				array('id' => 'et_save_print', 'class'=>'button small', 'form' => 'correspondence-create')
			);
		?>

			<?php if (!$this->patient->practice || !$this->patient->practice->contact->address) { ?>
				<div id="no-practice-address" class="alert-box alert with-icon">
					Patient has no GP practice address, please correct in PAS before updating GP letter.
				</div>
			<?php } ?>

			<?php $this->displayErrors($errors)?>
			<?php $this->renderDefaultElements($this->action->id, $form); ?>
			<?php $this->renderOptionalElements($this->action->id, $form); ?>
			<?php $this->displayErrors($errors, true)?>

		<?php $this->endWidget(); ?>
	</div>

<?php $this->endContent() ;?>
