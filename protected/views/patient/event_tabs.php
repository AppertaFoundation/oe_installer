<?php
/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
?>
<ul class="inline-list tabs event-actions">
	<?php foreach ($this->event_tabs as $tab) { ?>
	<li<?php if (@$tab['active']) { ?> class="selected"<?php } ?>>
		<?php if (@$tab['href']) { ?>
			<a href="<?php echo $tab['href'] ?>"><?php echo $tab['label'] ?></a>
		<?php } else { //FIXME: don't select?>
			<a href="#"><?php echo $tab['label'] ?></a>
		<?php } ?>
	</li>
	<?php } ?>
</ul>

<?php //this needs adding to SASS and doing properly when we decide on a solution OEM-295
if (isset($this->event->eventType->name) && ($this->event->eventType->name === 'Examination')) {
    ?>
<div class="button-bar left">
	<span width="22px" height="24px" style="font-size:21px; color:#152250; vertical-align: middle; display:inline-block;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RIGHT</span>
</div>
<?php } ?>