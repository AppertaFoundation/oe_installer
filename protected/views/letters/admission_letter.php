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

<div class="accessible">

<?php echo $this->renderPartial('/letters/letter_start', array(
		'to' => $patient->salutationname,
		'patient' => $patient,
		)); ?>

<?php
$booking = $operation->booking;
if($consultant = $firm->getConsultant()) {
			$consultantName = $consultant->contact->title . ' ' . $consultant->contact->first_name . ' ' . $consultant->contact->last_name;
		} else {
			$consultantName = 'CONSULTANT';
		}
		$subspecialty = $firm->serviceSubspecialtyAssignment->subspecialty;
		?>
<?php if ($patient->isChild()) {
		// Start Child ?>

<p>
	<?php if ($operation->status == ElementOperation::STATUS_RESCHEDULED) {
			// Rescheduled ?>
	I am writing to inform you that the date for your child's eye operation
	has been changed
	<?php if(isset($cancelledBookings[0])) { 
		echo ' from ' . date('jS F Y', strtotime($cancelledBookings[0]->date));
} ?>
	. The details are now:
	<?php } else {
			// Scheduled ?>
	I am writing to confirm the date for your child's eye operation. The
	details are:
	<?php } ?>
</p>

<table class="borders">
	<tr>
		<th>Date of admission:</th>
		<td><?php echo date('jS F Y', strtotime($booking->session->date)) ?></td>
		<th>Time to arrive:</th>
		<td><?php echo date('g:ia',strtotime($booking->admission_time)) ?></td>
	</tr>
	<tr>
		<th>Ward:</th>
		<td><?php if ($site->id == 5) {
			// St George's ?>St Georges Jungle Ward<?php } else {
			// City Road ?>Richard Desmond's Children's Eye Centre (RDCEC)<?php }	?>
		</td>
		<th>Location:</th>
		<td><?php echo CHtml::encode($site->name); ?></td>
	</tr>
	<tr>
		<th>Consultant:</th>
		<td><?php echo $consultantName ?></td>
		<th>Speciality:</th>
		<td><?php echo $subspecialty->name ?></td>
	</tr>
</table>
<br/>

<p>To help ensure this admission proceeds smoothly, please follow these
	instructions:</p>

<ul>
	<?php if ($site ->id != 5) {
			// City Road ?>
	<li><strong>Please contact the Children's Ward as soon as possible on
			0207 566 2595 to discuss pre-operative instructions</strong></li>
	<?php } ?>
	<li>Bring this letter with you on date of admission</li>
	<?php if ($site->id == 5) {
			// St Georges ?>
	<li>Please go directly to the Jungle Ward on level 5 of the
		Lanesborough wing at the time of your child's admission</li>
	<?php } else { ?>
	<li>Please go directly to the Main Reception in the RDCEC at the time
		of your child's admission</li>
	<?php } ?>
</ul>

<p>
	If there has been any change in your child's general health, such as a
	cough or cold, any infectious disease, or any other condition which
	might affect their fitness for operation, please telephone
	<?php if ($site->id == 5) {
			// St Georges ?>
	020 8725 0060
	<?php } else { ?>
	0207 566 2595 and ask to speak to a nurse
	<?php } ?>
	for advice.
</p>

<p>If you do not speak English, please arrange for an English speaking
	adult to stay with you until you reach the ward and have been seen by a
	doctor and anaesthetist.</p>

<p>
	It is very important that you let us know immediately if you are unable
	to keep this admission date. Please let us know by return of post, or
	if necessary, telephone
	<?php if ($site->id == 5) {
			// St Georges ?>
	the Admissions Department 020 8725 0060
	<?php } else { ?>
	the Paediatrics and Strabismus Admission Coordinator on 020 7566 2258.
	<?php } ?>
</p>

<?php
} // End Child
else {
			// Start Adult ?>

<p>
	<?php if ($operation->status == ElementOperation::STATUS_RESCHEDULED) { 
			// Adult Rescheduled ?>
	I am writing to inform you that the date for your
	<?php if($operation->name) { 
		echo $operation->name;
	} else { ?>
	eye operation
	<?php } ?>
	has been changed
	<?php if(isset($cancelledBookings[0])) { 
		echo ' from ' . date('jS F Y', strtotime($cancelledBookings[0]->date));
} ?>
	, the new details are:
	<?php } else {
			// Adult Scheduled ?>
	I am pleased to confirm the date of your
	<?php if($operation->name) { 
		echo $operation->name;
	} else { ?>
	operation with
	<?php echo $consultantName; 
} ?>
	, the details are:
	<?php } ?>
</p>

<table class="borders">
	<tr>
		<th>Date of admission:</th>
		<td><?php echo date('jS F Y', strtotime($booking->session->date)) ?></td>
	</tr>
	<tr>
		<th>Time to arrive:</th>
		<td><?php echo date('g:ia',strtotime($booking->admission_time)) ?></td>
	</tr>
	<tr>
		<th>Ward:</th>
		<td><?php if ($subspecialty->id == 13) {
			// Refractive laser ?>Refractive waiting room - Cumberledge Wing 4th
			Floor<?php } else { ?><?php echo CHtml::encode($booking->ward->name); ?>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<th>Location:</th>
		<td><?php echo CHtml::encode($site->name); ?></td>
	</tr>
	<tr>
		<th>Consultant:</th>
		<td><?php echo $consultantName ?></td>
	</tr>
	<tr>
		<th>Speciality:</th>
		<td><?php echo $subspecialty->name ?></td>
	</tr>
</table>
<p></p>

<p>
	If this is not convenient or you no longer wish to proceed with surgery, please contact the <?php echo $refuseContact?> as soon as possible.
</p>

<?php if(!$operation->overnight_stay) { ?>
<p>
	<em>This is a daycase and you will be discharged from hospital on the
		same day.</em>
</p>
<?php } ?>

<?php if($subspecialty->id != 13 && $operation->showPreopWarning()) { // Not refractive laser ?>
<p>
	<strong>All admissions require a Pre-Operative Assessment which you
		must attend. Non-attendance will cause a delay or possible <em>cancellation</em>
		to your surgery.
	</strong>
</p>
<?php } ?>

<p>If you are unwell the day before admission, please contact us to
	ensure that it is still safe and appropriate to do the procedure. If
	you do not speak English, please arrange for an English speaking adult
	to stay with you until you reach the ward and have been seen by a
	Doctor.</p>

<?php if($subspecialty->id != 13 && $operation->showPrescriptionWarning()) { // Not refractive laser ?>
<p>
	<em>You may be given a prescription after your treatment. This can be
		collected from our pharmacy on the ward, however unless you have an
		exemption certificate the standard prescription charge will apply.
		Please ensure you, or the person collecting you, have the correct money
		to cover the prescription cost.</em>
</p>
<?php } ?>

<p>To help ensure your admission proceeds smoothly, please follow these
	instructions:</p>

<ul>
	<li>Bring this letter with you on date of admission</li>
	<li>Please go directly to <?php if ($subspecialty->id == 13) {
		// Refractive laser ?> Refractive waiting room - Cumberledge Wing 4th
		Floor<?php } else { ?> <?php echo CHtml::encode($booking->ward->name) ?>
		ward<?php } ?>
	</li>
	<li>You must not drive yourself to or from hospital</li>
	<?php if($operation->showSeatingWarning()) { ?>
	<li>We request that only 1 person accompany you to ensure that there is adequate
		seating available for patients</li>
	<?php } ?>
	<?php if($subspecialty->id != 13 && $operation->showPrescriptionWarning()) { ?>
	<li><em>Check whether you have to pay or are exempt from prescription
			charges. If you are exempt, you will need to provide proof that you
			are exempt every time you collect a prescription. The prescription
			charge is £7.40 per item.</em></li>
	<?php } ?>
</ul>

<?php } // End Adult ?>

<?php echo $this->renderPartial('/letters/letter_end'); ?>

</div>
