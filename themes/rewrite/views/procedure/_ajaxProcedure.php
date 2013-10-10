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
<tr class="procedureItem">
	<td>	<span class="middle<?php if (!$durations) {?> noDuration<?php }?>">
		<?php
		echo CHtml::hiddenField('Procedures_'.$identifier.'[]', $data['id']);
		echo "<span>".$data['term'].'</span>';
		if ($short_version) {
			echo ' - <span>'.$data['short_format']."</span>";
		}
		?>
	</span></td>
	<td>	<?php if ($durations) {?>
			<span id='procedureItemDuration' class="right">
			<?php echo $data['duration']?> mins
		</span>
		<?php }?></td>
	<td><a href="#" class="small removeProcedure"><strong>(remove)</strong></a></td>
</tr>


