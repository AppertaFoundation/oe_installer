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

<h3 class="subsection"><?php echo $element->elementType->name ?></h3>

<div class="cols2 colsX clearfix">
	<div class="right" style="margin-top: 30px;">
		<?php
		$this->widget('application.modules.eyedraw.OEEyeDrawWidget', array(
			'side'=>$element->eye->getShortName(),
			'mode'=>'view',
			'width'=>200,
			'height'=>200,
			'model'=>$element,
			'attribute'=>'eyedraw',
		));
		?>
	</div>
	<div class="left">
		<table class="subtleWhite normalText">
      <tbody>
        <tr>
          <td width="30%"><?php echo CHtml::encode($element->getAttributeLabel('drainage_type_id'))?>:</td>
          <td><span class="big"><?php echo $element->drainage_type->name?></span></td>
        </tr>
        <tr>
          <td><?php echo CHtml::encode($element->getAttributeLabel('drain_haem'))?>:</td>
          <td><span class="big"><?php echo $element->drain_haem ? 'Yes' : 'No'?></span></td>
        </tr>
        <tr>
          <td><?php echo CHtml::encode($element->getAttributeLabel('deep_suture'))?>:</td>
          <td><span class="big"><?php echo $element->deep_suture ? 'Yes' : 'No'?></span></td>
        </tr>
				<tr>
					<td><?php echo CHtml::encode($element->getAttributeLabel('report'))?>:</td>
					<td><span class="big"><?php echo CHtml::encode($element->report)?></span></td>
				</tr>
				<tr>
					<td><?php echo CHtml::encode($element->getAttributeLabel('comments'))?>:</td>
					<td><span class="big"><?php echo CHtml::encode($element->comments)?></span></td>
				</tr>
      </tbody>
    </table>
	</div>
</div>
