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
<div class="eyedraw-image column small">
	<?php $this->widget('application.modules.eyedraw.OEEyeDrawWidget', array(
			'idSuffix' => $side.'_'.$element->elementType->id.'_'.$element->id,
			'side' => ($side == 'right') ? 'R' : 'L',
			'mode' => 'view',
			'width' => 200,
			'height' => 200,
			'model' => $element,
			'attribute' => $side.'_eyedraw',
	))?>
</div>
<div class="eyedraw-value column small">
	<?php if ($element->{$side.'_description'}) {?>
		<div class="data-value">
			<?php echo $element->{$side.'_description'}?>
		</div>
	<?php }?>
	<div class="data-value">
		<div class="shaffer_grade">
			<div class="label">Shaffer Grade:</div>
			<div class="data">
				<div class="data gonioCross">
					<div class="gonioSup">
						<?php echo $element->{$side.'_gonio_sup'}->name; ?>
					</div>
					<div class="gonioTem">
						<?php echo $element->{$side.'_gonio_tem'}->name; ?>
					</div>
					<div class="gonioNas">
						<?php echo $element->{$side.'_gonio_nas'}->name; ?>
					</div>
					<div class="gonioInf">
						<?php echo $element->{$side.'_gonio_inf'}->name; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="data-value">
		<div class="label"><?php echo $element->getAttributeLabel($side.'_van_herick_id') ?>:</div>
		<div class="data">
			<?php if ($van_herick = $element->{$side.'_van_herick'}) {
				echo $van_herick->name;
			} else {
				echo 'NR';
			} ?>
		</div>
	</div>
</div>
