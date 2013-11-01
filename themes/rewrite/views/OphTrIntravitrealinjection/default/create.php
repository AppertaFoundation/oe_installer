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

	<?php
		$this->breadcrumbs=array($this->module->id);
		$this->event_actions[] = EventAction::button('Save', 'save', array('level' => 'secondary'), array('class'=>'button small', 'form'=>'clinical-create'));
	?>

	<h2 class="event-title"><?php echo $this->event_type->name ?></h2>

	<?php $this->renderPartial('//base/_messages'); ?>

	<?php $form = $this->beginWidget('BaseEventTypeCActiveForm', array(
		'id'=>'clinical-create',
		'enableAjaxValidation'=>false,
		'layoutColumns' => array(
			'label' => 4,
			'field' => 8
		)
	));
	?>
		<?php $this->displayErrors($errors)?>
		<?php if ($this->side_to_inject !== null) {
			$cls_lkup = array(
				0 => 'none',
				Eye::LEFT => 'left',
				Eye::RIGHT => 'right',
				Eye::BOTH => 'both'
			);
			if ($this->side_to_inject == 0) {
				$msg = 'No injection should be performed today';
			}
			elseif ($this->side_to_inject == Eye::BOTH) {
				$msg = "Both eyes to be injected";
			}
			else {
				$msg = "Only " . strtolower(Eye::model()->findByPk($this->side_to_inject)->name) . " eye to be injected";
			}
			?>
			<div class="alert-box alert with-icon injection-warning <?php echo $cls_lkup[$this->side_to_inject] ?>">
				<?php echo $msg ?>
			</div>

			<?php
		}
		?>
		<?php $this->renderDefaultElements($this->action->id, $form)?>
		<?php $this->renderOptionalElements($this->action->id, $form)?>
		<?php $this->displayErrors($errors, true)?>

	<?php $this->endWidget()?>
<?php $this->endContent() ;?>
