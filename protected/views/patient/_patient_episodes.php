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
<div class="halfColumnRight">
<div class="blueBox">
	<h5>All Episodes<span style="float:right;">&nbsp; open <?php echo $episodes_open?> &nbsp;|&nbsp;<span style="font-weight:normal;">closed <?php echo $episodes_closed?></span></span></h5>
	<div id="yw0" class="grid-view">
		<?php if (empty($episodes)) {?>
			<div class="summary">No episodes</div>
		<?php } else {?>
			<table class="items">
				<thead>
					<tr><th id="yw0_c0">Start  Date</th><th id="yw0_c1">End  Date</th><th id="yw0_c2">Firm</th><th id="yw0_c3">Subspecialty</th><th id="yw0_c4">Eye</th><th id="yw0_c5">Diagnosis</th></tr>
				</thead>
				<tbody>
					<?php foreach ($ordered_episodes as $specialty_episodes) {?>
						<tr>
						<td colspan="6" class="all-episode specialty small"><?php echo $specialty_episodes['specialty']->name ?></td>
						</tr>
						<?php foreach ($specialty_episodes['episodes'] as $i => $episode) {?>
							<tr id="<?php echo $episode->id?>" class="clickable all-episode <?php if ($i %2 == 0) {?>even<?php } else {?>odd<?php }?><?php if ($episode->end_date !== null) {?> closed<?php }?>">
								<td><?php echo $episode->NHSDate('start_date'); ?></td>
								<td><?php echo $episode->NHSDate('end_date'); ?></td>
								<td><?php echo CHtml::encode($episode->firm->name)?></td>
								<td><?php echo CHtml::encode($episode->firm->serviceSubspecialtyAssignment->subspecialty->name)?></td>
								<td><?php echo ($episode->diagnosis) ? $episode->eye->name : 'No diagnosis' ?></td>
								<td><?php echo ($episode->diagnosis) ? $episode->diagnosis->term : 'No diagnosis' ?></td>
							</tr>
						<?php }?>
					<?php }?>
				</tbody>
			</table>
			<div class="table_endRow"></div>
		<?php }?>
	</div>
</div>
<?php 
$editable = false;
if ($episode = $this->patient->getEpisodeForCurrentSubspecialty()) {
	$latest = $episode->getLatestEvent();
	$subspecialty = $episode->getSubspecialty();
	$editable = true;
}
elseif ($latest = $this->patient->getLatestEvent()) {
	$editable = $latest->episode->editable;
	$subspecialty = $latest->episode->getSubspecialty();
}	

$msg = null;

if ($latest) {
	$msg = "Latest Event ";
	if ($subspecialty) {
		// might not be a subspecialty for legacy
		$msg .= "in " . $subspecialty->name;
	}
	$msg .= ": <strong>" . $latest->eventType->name . "</strong> <span class='small'>(" . $latest->NHSDate('created_date') . ")</span>";
}
else if (BaseController::checkUserLevel(4)) {
	$msg = "Create episode / add event";
}

if ($msg) {
	echo '<p>' . CHtml::link('<span class="aPush">'. $msg . '</span>',Yii::app()->createUrl('patient/episodes/'.$this->patient->id)) . '</p>';
}

try {
	echo $this->renderPartial('custom/info');
} catch (Exception $e) {
	// This is our default layout
	$codes = $this->patient->getSpecialtyCodes();
	// specialist diagnoses
	foreach ($codes as $code) {
		try {
			echo $this->renderPartial('_' . $code . '_diagnoses');
		} catch (Exception $e) {}
	}
	$this->renderPartial('_systemic_diagnoses');
	$this->renderPartial('_previous_operations');
	$this->renderPartial('_medications',array('firm'=>$firm));
	// specialist extra data
	foreach ($codes as $code) {
		try {
			echo $this->renderPartial('_' . $code . '_info');
		} catch (Exception $e) {}
	}
	$this->renderPartial('_allergies');
	$this->renderPartial('_family_history');
}
?>
</div>
