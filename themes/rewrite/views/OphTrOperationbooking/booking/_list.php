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

if (!$reschedule) {
	echo CHtml::form(Yii::app()->createUrl('/OphTrOperationbooking/booking/schedule/'.$operation->event->id.'?firm_id='.$_GET['firm_id'].'&date='.$_GET['date'].'&day='.$_GET['day'].'&session_id='.$_GET['session_id']), 'post', array('id' => 'bookingForm'));
} else {
	echo CHtml::form(Yii::app()->createUrl('/OphTrOperationbooking/booking/reschedule/'.$operation->event->id.'?firm_id='.$_GET['firm_id'].'&date='.$_GET['date'].'&day='.$_GET['day'].'&session_id='.$_GET['session_id']), 'post', array('id' => 'bookingForm'));
}
?>
	<h4>Other operations in this session: <?php echo abs($session->availableMinutes) . " min {$session->minuteStatus}"; ?></h4>
	<div class="theatre-sessions">
	<table id="appointment_list">
		<thead>
			<tr>
				<th>Operation list overview</th>
				<th>Date: <?php echo Helper::convertDate2NHS($session['date']); ?></th>
				<th>Anaesthetic type</th>
				<th>Session time: <?php echo substr($session['start_time'], 0, 5) . ' - '
				. substr($session['end_time'], 0, 5); ?></th>
				<th>Admission time</th>
				<th>Comments</th>
			</tr>
		</thead>
		<tbody>

<?php
	$counter = 1;
	foreach ($bookings as $booking) {?>
		<tr>
			<td><?php echo $counter?>. <?php echo $booking->operation->event->episode->patient->getDisplayName()?></td>
			<td><?php echo $booking->operation->getProceduresCommaSeparated()?></td>
			<td><?php echo $booking->operation->anaesthetic_type->name?></td>
			<td><?php echo "{$booking->operation->total_duration} minutes"; ?></td>
			<td><?php echo $booking->admission_time?></td>
			<td><?php echo $booking->operation->comments?></td>
		</tr>
<?php
		$counter++;
	} ?>
	</tbody>
		<tfoot>
			<tr>
				<th colspan="6">
					<?php echo ($counter - 1) . ' booking';
					if (($counter - 1) != 1) {
						echo 's';
					}
					if ($bookable) {
						echo ' currently scheduled';
					} else {
						echo ' were scheduled';
					}
					?>
				</th>
			</tr>
		</tfoot>
</table>
</div>

<a id="book"></a>

<?php if ($bookable) {?>

	<div class="eventDetail clearfix">
		<div class="row field-row">
			<div class="large-2 column">
				<label for="Booking_admission_time"><strong>Admission Time:</strong></label>
			</div>
			<div class="large-2 column end">
				<input type="text" id="Booking_admission_time" name="Booking[admission_time]" value="<?php echo $_POST['Booking']['admission_time']?>" size="6" />
				<span id="Booking_admission_time_error"></span>
			</div>
		</div>

		<div class="row field-row" style="position:relative;">
			<div class="large-2 column">
				<label for="Session_comments"><strong>Session Comments:</strong></label>
				<!-- <img src="<?php echo Yii::app()->createUrl('img/_elements/icons/alerts/comment.png')?>" alt="comment" width="17" height="17" style="position:absolute; bottom:10px; left:10px;" /> -->
			</div>
			<div class="large-5 column end">
				<div class="sessionComments">
					<textarea id="Session_comments" name="Session[comments]" rows="2"><?php echo htmlspecialchars($_POST['Session']['comments'])?></textarea>
				</div>
			</div>
		</div>

		<?php
		if ($reschedule) {
			echo CHtml::hiddenField('booking_id', $operation->booking->id);
		}
		echo CHtml::hiddenField('Booking[element_id]', $operation->id);
		echo CHtml::hiddenField('Booking[session_id]', $session['id']);
		?>

		<?php if ($reschedule) { ?>
			<h3>Reason for Reschedule</h3>
			<div class="row field-row">
				<div class="large-2 column">
					<?php echo CHtml::label('<strong>Reschedule Reason:</strong> ', 'cancellation_reason'); ?>
				</div>
				<div class="large-5 column end">
					<?php if (date('Y-m-d') == date('Y-m-d', strtotime($operation->booking->session->date))) {
						$listIndex = 3;
					} else {
						$listIndex = 2;
					} ?>
					<?php echo CHtml::dropDownList('cancellation_reason', '',
						OphTrOperationbooking_Operation_Cancellation_Reason::getReasonsByListNumber($listIndex),
						array('empty' => 'Select a reason')
					); ?>
				</div>
			</div>
			<div class="row field-row">
				<div class="large-2 column">
					<?php echo CHtml::label('<strong>Reschedule Comments:</strong> ', 'cancellation_comment'); ?>
				</div>
				<div class="large-5 column end">
					<textarea name="cancellation_comment" rows=3 cols=50></textarea>
				</div>
			</div>
		<?php }?>

		<div class="row field-row">
			<div class="large-2 column">
				<?php echo CHtml::label('<strong>Operation Comments:</strong>', 'operation_comments'); ?>
			</div>
			<div class="large-5 column end">
				<textarea id="operation_comments" name="Operation[comments]" rows=3 cols=50><?php echo htmlspecialchars($_POST['Operation']['comments'])?></textarea>
			</div>
		</div>

		<div class="field-row">
			<span id="dateSelected">Date/Time currently selected: <span class="highlighted"><?php echo Helper::convertDate2NHS($session['date']); ?>, <?php echo substr($session['start_time'], 0, 5) . ' - ' . substr($session['end_time'], 0, 5); ?></span></span>
		</div>

		<div class="field-row">
			<button type="submit" class="secondary" id="confirm_slot">Confirm slot</button>
			<button type="button" class="warning" id="cancel_scheduling"><?php echo 'Cancel '.($reschedule ? 're-' : '').'scheduling';?></button>
		</div>

		<?php
		echo CHtml::endForm();
		?>
	</div>
<?php }?>
