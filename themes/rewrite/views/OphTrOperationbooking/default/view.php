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

	<h2 class="event-title"><?php  echo $this->event_type->name ?> (<?php echo Element_OphTrOperationbooking_Operation::model()->find('event_id=?',array($this->event->id))->status->name?>)</h2>

	<?php $this->renderPartial('//base/_messages'); ?>

	<?php if (!$operation->has_gp) {?>
		<div class="alertBox alert with-icon">
			Patient has no GP practice address, please correct in PAS before printing GP letter.
		</div>
	<?php } ?>
	<?php if (!$operation->has_address) { ?>
		<div class="alertBox alert with-icon">
			Patient has no address, please correct in PAS before printing letter.
		</div>
	<?php } ?>

	<?php if ($operation->event->hasIssue()) {?>
		<div class="alert-box issue with-icon">
			<?php echo $operation->event->getIssueText()?>
		</div>
	<?php }?>

	<div class="view booking highlight-fields">
		<?php
		$this->renderDefaultElements($this->action->id);
		$this->renderOptionalElements($this->action->id);
		?>
		<div class="cleartall"></div>
	</div>

<?php $this->endContent() ;?>