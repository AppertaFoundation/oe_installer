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
<?php $this->beginContent('//patient/event_container', array()); ?>

	<h2 class="event-title"><?php echo $this->event_type->name ?></h2>

	<?php
	$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id'=>'clinical-create',
			'enableAjaxValidation'=>false,
	));

		// Event actions
		$this->event_actions[] = EventAction::button('Save and Schedule later','#', null, array('name'=> 'scheduleLater', 'id' => 'et_save', 'class' => 'button small', 'form'=>'clinical-create'));
		$this->event_actions[] = EventAction::button('Save and Schedule now', '#', array('level'=>'secondary'),  array('name'=> 'scheduleNow','id' => 'et_save_and_schedule', 'class' => 'button small', 'form' => 'clinical-create'));

		?>
		<input type="hidden" name="schedule_now" id="schedule_now" value="0" />
		<?php
		$this->displayErrors($errors);
		$this->renderDefaultElements($this->action->id, $form);
		$this->renderOptionalElements($this->action->id, $form);
		$this->displayErrors($errors, true);
		?>
		<div class="cleartall"></div>
	<?php $this->endWidget()?>

<?php $this->endContent() ;?>

