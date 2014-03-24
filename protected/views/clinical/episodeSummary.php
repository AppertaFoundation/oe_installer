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

if (!empty($episode)) {

	if ($episode->diagnosis) {
		$eye = $episode->eye ? $episode->eye->name : 'None';
		$diagnosis = $episode->diagnosis ? $episode->diagnosis->term : 'none';
	} else {
		$eye = 'No diagnosis';
		$diagnosis = 'No diagnosis';
	}

	$episode->audit('episode summary','view');
	?>

	<div class="element-data">
		<h2>Summary</h2>
		<h3><?php echo $episode->support_services ? 'Support services' : $episode->firm->getSubspecialtyText()?></h3>
	</div>

	<?php $this->renderPartial('//base/_messages'); ?>

	<section class="element element-data">
		<h3 class="data-title">Principal diagnosis:</h3>
		<div class="data-value highlight">
			<?php echo $episode->diagnosis ? $episode->diagnosis->term : 'None'?>
		</div>
	</section>

	<section class="element element-data">
		<h3 class="data-title">Principal eye:</h3>
		<div class="data-value highlight">
			<?php echo $episode->eye ? $episode->eye->name : 'None'?>
		</div>
	</section>

	<div class="element element-data event-types">
		<?php
		$summaryItems = array();
		if ($episode->subspecialty) {
			$summaryItems = EpisodeSummaryItem::model()->enabled($episode->subspecialty->id)->findAll();
		}
		if (!$summaryItems) {
			$summaryItems = EpisodeSummaryItem::model()->enabled()->findAll();
		}

		foreach ($summaryItems as $summaryItem) {
			echo '<h3 id="' . $summaryItem->getClassName() . '" class="data-title">' . $summaryItem->name . ':</h3>' . "\n";
			Yii::import("{$summaryItem->event_type->class_name}.widgets.{$summaryItem->getClassName()}");
			$this->widget(
					$summaryItem->getClassName(),
					array(
							'episode' => $episode,
							'event_type' => $summaryItem->event_type,
					)
			);
		}
		?>
	</div>

	<section class="element element-data">
		<div class="row">
			<div class="large-6 column">
				<h3 class="data-title">Start Date:</h3>
				<div class="data-value">
					<?php echo $episode->NHSDate('start_date')?>
				</div>
			</div>
			<div class="large-6 column">
				<h3 class="data-title">End date:</h3>
				<div class="data-value"><?php echo !empty($episode->end_date) ? $episode->NHSDate('end_date') : '(still open)'?></div>
			</div>
		</div>
	</section>

	<section class="element element-data">
		<div class="row">
			<div class="large-6 column">
				<h3 class="data-title">Subspecialty:</h3>
				<div class="data-value">
					<?php echo $episode->support_services ? 'Support services' : $episode->firm->getSubspecialtyText()?>
				</div>
			</div>
			<div class="large-6 column">
				<h3 class="data-title">Consultant firm:</h3>
				<div class="data-value"><?php echo $episode->firm ? $episode->firm->name : 'None'?></div>
			</div>
		</div>
	</section>

	<div class="metadata">
		<span class="info">
			<?php echo $episode->support_services ? 'Support services' : $episode->firm->getSubspecialtyText()?>: created by <span class="user"><?php echo $episode->user->fullName?></span>
			on <?php echo $episode->NHSDate('created_date')?> at <?php echo substr($episode->created_date,11,5)?>
		</span>
	</div>

	<section class="element element-data">
		<h3 class="data-title">Episode Status:</h3>
		<div class="data-value highlight">
			<?php echo $episode->status->name?>
		</div>
	</section>

	<div class="metadata">
		<span class="info">
			Status last changed by <span class="user"><?php echo $episode->usermodified->fullName?></span>
			on <?php echo $episode->NHSDate('last_modified_date')?> at <?php echo substr($episode->last_modified_date,11,5)?>
		</span>
	</div>

<?php } else { // hide the episode border ?>
	<script type="text/javascript">
		$('div#episodes_details').hide();
	</script>
<?php }?>

<script type="text/javascript">
	$('#closelink').click(function() {
		$('#dialog-confirm').dialog({
			resizable: false,
			height: 140,
			modal: false,
			buttons: {
				"Close episode": function() {
					$.ajax({
						url: $('#closelink').attr('href'),
						type: 'GET',
						success: function(data) {
							$('#episodes_details').show();
							$('#episodes_details').html(data);
						}
					});
					$(this).dialog('close');
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			},
			open: function() {
				$(this).parents('.ui-dialog-buttonpane button:eq(1)').focus();
			}
		});
		return false;
	});
</script>

<?php if (empty($episode->end_date)) {?>
	<div style="text-align:right; position:relative; ">
		<!--button id="close-episode" type="submit" value="submit" class="wBtn_close-episode ir">Close Episode</button-->

		<div id="close-episode-popup" class="popup red" style="display: none;">
			<p style="text-align:left;">You are closing this episode. This can not be undone. Once an episode is closed it can not be re-opened.</p>
			<p><strong>Are you sure?</strong></p>
			<div class="action_options">
				<span class="aBtn"><a id="yes-close-episode" href="#"><strong>Yes, I am</strong></a></span>
				<span class="aBtn"><a id="no-close-episode" href="#"><strong>No, cancel this.</strong></a></span>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$('#close-episode').unbind('click').click(function(e) {
			e.preventDefault();
			$('#close-episode-popup').slideToggle(100);
			return false;
		});

		$('#no-close-episode').unbind('click').click(function(e) {
			e.preventDefault();
			$('#close-episode-popup').slideToggle(100);
			return false;
		});

		$('#yes-close-episode').unbind('click').click(function(e) {
			e.preventDefault();
			$('#close-episode-popup').slideToggle(100);
			$.ajax({
				url: '<?php echo Yii::app()->createUrl('clinical/closeepisode/'.$episode->id)?>',
				success: function(data) {
					$('#event-content').html(data);
					return false;
				}
			});

			return false;
		});
	</script>
<?php }?>
