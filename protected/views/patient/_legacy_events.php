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

$legacyletters = EventType::model()->find('class_name=?', array('OphLeEpatientletter'));

if ($legacyletters && !$legacyletters->disabled && is_array($legacyepisodes)) foreach ($legacyepisodes as $i => $episode) {?>
	<div class="episode open clearfix" style="display:block;">
		<div class="episode_nav legacy">
			<div class="start_date small">
				<?php echo $episode->NHSDate('start_date') ?>
				<span class="aBtn">
					<a class="sprite showhide2 legacy" href="#">
						<span class="<?php if ((!$this->event || $this->event->eventType->class_name != 'OphLeEpatientletter') && !@Yii::app()->session['episode_hide_status']['legacy']) { ?>show<?php } else { ?>hide<?php } ?>"></span>
					</a>
				</span>
			</div>
			<h4 class="legacy" style="margin-left: 8px;">Legacy events</h4>


			<ul class="events"<?php if ((!$this->event || $this->event->eventType->class_name != 'OphLeEpatientletter') && !@Yii::app()->session['episode_hide_status']['legacy']) { ?> style="display: none;"<?php } ?>>
				<?php
				foreach ($episode->events as $event) {
					$highlight = false;

					if (isset($this->event) && $this->event->id == $event->id) {
						$highlight = TRUE;
					}

					$event_path = Yii::app()->createUrl($event->eventType->class_name . '/Default/view') . '/';
					?>
					<li id="eventLi<?php echo $event->id ?>">
						<div class="quicklook" style="display: none; ">
							<span class="event"><?php echo $event->eventType->name ?></span>
							<span class="info"><?php echo str_replace("\n", "<br/>", $event->info) ?></span>
							<?php if ($event->hasIssue()) { ?>
								<span class="issue"><?php echo $event->getIssueText() ?></span>
							<?php } ?>
						</div>
						<?php if ($highlight) { ?>
							<div class="viewing">
							<?php } else { ?>
								<a style="color:#999;" href="<?php echo $event_path . $event->id ?>" rel="<?php echo $event->id ?>" class="show-event-details">
								<?php } ?>
								<span class="type<?php if ($event->hasIssue()) { ?> statusflag<?php } ?>">
									<?php $assetpath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.' . $event->eventType->class_name . '.assets')) . '/'; ?>
									<img src="<?php echo Yii::app()->createUrl($assetpath . 'img/small.png') ?>" alt="op" width="19" height="19" />
								</span>
								<span class="date"> <?php echo $event->NHSDateAsHTML('created_date'); ?></span>
								<?php if (!$highlight) { ?>
								</a>
							<?php } else { ?>
							</div>
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php }?>
