		<h2>Episodes &amp; Events</h2>
		<script type="text/javascript"> var header_text = ''; </script>
		<div class="fullWidth fullBox clearfix">
			<div id="episodesBanner whiteBox">
				<form><button type="submit" value="submit" class="btn_newEvent ir" id="addNewEvent"><img style="float:right; margin:0px 0px 0 0;" src="/img/_elements/btns/new-event.png" alt="add-new-event" width="155" height="35" /></button></form>
				<p><strong>&nbsp;<?php if (count($episodes) <1) {?>No Episodes for this patient<?php }?></strong></p>
			</div>
			<div id="episodes_sidebar">
				<?php foreach ($episodes as $i => $episode) {
					if (isset($_GET['episode']) && ctype_digit($_GET['episode'])) {
						if ($episode->id == $_GET['episode']) {
							$current_episode = $episode;
						}
					} else {
						if ($i == 0) $current_episode = $episode;
					}
					?>
					<div class="episode <?php echo empty($episode->end_date) ? 'closed' : 'open' ?> clearfix">
						<div class="episode_nav">
							<input type="hidden" name="episode-id" value="<?php echo $episode->id?>" />
							<div class="small"><?php echo date('d M Y',strtotime($episode->start_date))?><span style="float:right;"><a href="#" rel="<?php echo $episode->id?>" class="episode-details">Details</a><span></div>
							<h4><?php echo CHtml::encode($episode->firm->serviceSpecialtyAssignment->specialty->name)?></h4>
							<ul class="events">
								<?php foreach ($episode->events as $event) {?>
									<?php
									$event_elements = $this->service->getElements(
										null, null, null, 0, $event
									);
									$scheduled = false;
									foreach ($event_elements as $element) {
										if (get_class($element) == 'ElementOperation' && $element->status == ElementOperation::STATUS_SCHEDULED) {
											$scheduled = true;
										}
									}

									if (ctype_digit(@$_GET['event']) && $_GET['event'] == $event->id) {
										$highlight = true;
									} else {
										$highlight = false;
									}
									?>
									<li><a href="#" rel="<?php echo $event->id?>" class="show-event-details"><?php if ($highlight) echo '<div class="viewing">'?><span class="type"><img src="/img/_elements/icons/event_op_<?php if (!$scheduled) echo 'un'?>scheduled.png" alt="op" width="16" height="16" /></span><span class="date"> <?php echo date('d M Y',strtotime($event->datetime))?></span><?php if ($highlight) echo '</div>' ?></a></li>
							<?php
								}
							?>
							</ul>
						</div>
						<div class="episode_details hidden" id="episode-details-<?php echo $episode->id?>">
							<div class="row"><span class="label">Start date:</span><?php echo date('d M Y',strtotime($episode->start_date))?></div>
							<div class="row"><span class="label">End date:</span><?php echo ($episode->end_date ? date('d M Y',strtotime($episode->end_date)) : '-')?></div>
                                                        <?php $diagnosis = $episode->getPrincipalDiagnosis() ?>
							<div class="row"><span class="label">Principal eye:</span><?php echo !empty($diagnosis) ? $diagnosis->getEyeText() : 'No diagnosis' ?></div>
							<div class="row"><span class="label">Principal diagnosis:</span><?php echo !empty($diagnosis) ? $diagnosis->disorder->term : 'No diagnosis' ?></div>
							<div class="row"><span class="label">Specialty:</span><?php echo CHtml::encode($episode->firm->serviceSpecialtyAssignment->specialty->name)?></div>
							<div class="row"><span class="label">Consultant firm:</span><?php echo CHtml::encode($episode->firm->name)?></span></div>
							<img class="folderIcon"src="/img/_elements/icons/folder_open.png" alt="folder open" />
						</div>
					</div> <!-- .episode -->
				<?php }?>
			</div> <!-- #episodes_sidebar -->
			<div id="event_display">
				<div id="add-event-select-type" class="whiteBox addEvent clearfix" style="display: none;">
					<h3>Adding New Event</h3>
					<p><strong>Select event to add:</strong></p>
					<?php
						foreach ($eventTypes as $eventType) {
?>
					<p><a href="#" id="add-new-event-type<?php echo $eventType->id ?>"><img src="/img/_elements/icons/event_op_unscheduled.png" alt="operation" width="16" height="16" /> - <strong><?php echo $eventType->name ?></strong></a></p>
<?php
						}
?>
				</div>
				<input type="hidden" id="edit-eventid" name="edit-eventid" value="<?php if (ctype_digit(@$_GET['event'])) echo $_GET['event']?>" />
				<?php
				if (!isset($current_episode)) {?>
					<div class="alertBox fullWidthEvent">
						<h4>There are currently no episodes for this patient, please add a new event to open an episode.</h4>
					</div>
				<?php }?>
				<div class="display_actions"<?php if (!isset($current_episode)) {?> style="display: none;"<?php }?>>
					<div class="display_mode">View mode</div>
					<div class="action_options"<?php if (!ctype_digit(@$_GET['event'])){?> style="display: none;"<?php }?>><span class="aBtn_inactive">View</span><span class="aBtn edit-event"<?php if (!$editable){?> style="display: none;"<?php }?>><a class="edit-event" href="#">Edit</a></span></div>
				</div>
				<!-- EVENT CONTENT HERE -->
				<div id="event_content" class="eventBox fullWidthEvent">
					<?php
					if (ctype_digit(@$_GET['event'])) {?>
						<?php
						$this->renderPartial(
							"/clinical/".$this->getTemplateName('view', $event->event_type_id),
							array(
								'elements' => $elements,
								'eventId' => $_GET['event'],
								'editable' => $editable,
								'site' => $site
							), false, true
						);
					} else {
						if (isset($current_episode)) {
							$this->renderPartial('/clinical/episodeSummary',
								array('episode' => $current_episode)
							);
						}
					}
					?>
				</div>
				<!-- #event_content -->
				<div id="display_actions_footer" class="display_actions footer"<?php if (!isset($current_episode)) {?> style="display: none;"<?php }?>>
					<div class="action_options"<?php if (!ctype_digit(@$_GET['event'])){?> style="display: none;"<?php }?>><span class="aBtn_inactive">View</span><span class="aBtn edit-event"<?php if (!$editable){?> style="display: none;"<?php }?>><a class="edit-event" href="#">Edit</a></span></div>
				</div>
			</div><!-- #event_display -->
		</div> <!-- .fullWidth -->
		<script type="text/javascript">
			$('a.episode-details').unbind('click').click(function() {
				$.ajax({
					url: '/clinical/episodesummary/'+$(this).attr('rel'),
					success: function(data) {
						$('div.action_options').hide();
						$('#event_content').html(data);
					}
				});
				return false;
			});

			$('a.show-event-details').unbind('click').click(function() {
				var event_id = $(this).attr('rel');
				view_event(event_id);
				// Highlight event clicked - get child of element. If it's a div do nothing. If it's a span blank all other elements of this class and add a div to this span

				var content = $(".viewing").contents()
				$(".viewing").replaceWith(content);

				$(this).wrapInner('<div class="viewing" />');

				return false;
			});

			function view_event(event_id) {
				var header_text = '';

				$.ajax({
					url: '/clinical/'+event_id,
					success: function(data) {
						$('#edit-eventid').val(event_id);
						$('div.display_actions').show();
						$('#display_actions_footer').show();
						$('div.action_options').show();
						$('#event_content').html(data);

						$('.display_mode').html(header_text);

						if (data.match(/<!-- editable -->/)) {
							$('span.edit-event').show();
						} else {
							$('span.edit-event').hide();
						}
					}
				});
			}

			$(document).ready(function(){
				$('.display_mode').html(header_text);

				$btn_normal = $('img','#addNewEvent').attr("src");
				$btn_over = $btn_normal.replace(/.png$/ig,"_o.png");
				$btn_inactive = $btn_normal.replace(/.png$/ig,"_inactive.png");
				$collapsed = true;

				// rollover... if not open
				$('#addNewEvent').mouseover(function(){
					if($collapsed){ $('img','#addNewEvent').attr("src",$btn_over); }
				});

				$('#addNewEvent').mouseout(function(){
					if($collapsed){ $('img','#addNewEvent').attr("src",$btn_normal);	}
				});

				$('#addNewEvent').unbind('click').click(function(e) {
					e.preventDefault();
					$collapsed = false;

					$('#add-event-select-type').slideToggle(100,function() {
						if($(this).is(":visible")){
							$('img','#addNewEvent').attr("src",$btn_inactive);
						} else {
							$('img','#addNewEvent').attr("src",$btn_normal);
							$collapsed = true;
						}
						return false;
					});

					return false;
				});
			});

			$('a[id^="add-new-event-type"]').unbind('click').click(function() {
				eventTypeId = this.id.match(/\d*$/);
				$.ajax({
					url: '/clinical/create?event_type_id=' + eventTypeId,
					success: function(data) {
						$('div.display_actions').hide();
						$('#add-event-select-type').hide();
						$collapsed = true;
						$('img','#addNewEvent').attr("src",$btn_normal);
						$('#event_content').html(data);
					}
				});
				return false;
			});

			$('a.edit-event').unbind('click').click(function() {
				edit_event($('#edit-eventid').val());
				return false;
			});

			function edit_event(event_id) {
				$.ajax({
					url: '/clinical/update/'+event_id,
					success: function(data) {
						edit_mode();
						$('div.display_actions').show();
						$('div.action_options').show();
						$('#event_content').html(data);
					}
				});
			}

			function edit_mode() {
				$('div.action_options').html('<span class="aBtn"><a class="view-event" href="#">View</a></span><span class="aBtn_inactive edit-event">Edit</span>');
				$('a.view-event').unbind('click').click(function() {
					view_event($('#edit-eventid').val());
					view_mode();
					return false;
				});
			}

			function view_mode() {
				$('div.action_options').html('<span class="aBtn_inactive">View</span><span class="aBtn edit-event"><a class="edit-event" href="#">Edit</a></span>');
				$('a.edit-event').unbind('click').click(function() {
					edit_event($('#edit-eventid').val());
					edit_mode();
					return false;
				});
			}
		</script>
