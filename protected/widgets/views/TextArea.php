					<?php if (!$nowrapper) {?>
						<div id="div_<?php echo get_class($element)?>_<?php echo $field?>" class="eventDetail"<?php if ($hidden) {?> style="display: none;"<?php }?>>
							<div class="label"><?php if ($label) {?><?php echo CHtml::encode($element->getAttributeLabel($field))?>:<?php }?></div>
							<div class="data">
								<?php }?>
								<textarea rows="<?php echo $rows?>" cols="<?php echo $cols?>" name="<?php echo get_class($element)?>[<?php echo $field?>]" id="<?php echo get_class($element)?>_<?php echo $field?>"><?php echo strip_tags($element->$field)?></textarea>
								<?php if (!$nowrapper) {?>
									<?php if ($button) {?>
										<button type="submit" class="classy <?php echo $button['colour']?> <?php echo $button['size']?>" id="<?php echo get_class($element)?>_<?php echo $button['id']?>" name="<?php echo get_class($element)?>_<?php echo $button['id']?>"><span class="button-span button-span-<?php echo $button['colour']?>"><?php echo $button['label']?></span></button>
									<?php }?>
								</div>
							</div>
						<?php }?>
