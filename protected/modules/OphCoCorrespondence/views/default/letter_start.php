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
<div class="banner clearfix">
	<div class="letter-seal">
		<img src="<?php echo Yii::app()->assetManager->createUrl('img/_print/letterhead_seal.jpg')?>" alt="letterhead_seal" width="80" />
	</div>
	<div class="correspondence-letter-logo">
		<img src="<?php echo Yii::app()->assetManager->createUrl('img/_print/letterhead_Moorfields_NHS.jpg')?>" alt="letterhead_Moorfields_NHS" width="350" />
	</div>
</div>
<?php if ($element->site) {?>
	<div class="from-address">
		<?php
		echo $element->site->getLetterAddress(array(
			'include_name' => true,
			'delimiter' => '<br />',
			'include_telephone' => true,
			'include_fax' => true,
		))?>
		<?php if ($element->direct_line || $element->fax) {?>
			<br/>
		<?php }?>
		<?php if ($element->direct_line) {?>
			<br /><?php echo $element->getAttributeLabel('direct_line')?>: <?php echo $element->direct_line?>
		<?php }?>
		<?php if ($element->fax) {?>
			<br/><?php echo $element->getAttributeLabel('fax')?>: <?php echo $element->fax?>
		<?php }?>
		<div class="date"><?php echo date(Helper::NHS_DATE_FORMAT,strtotime($date))?><?php if ($clinicDate) {?> (clinic date <?php echo date(Helper::NHS_DATE_FORMAT,strtotime($clinicDate))?>)<?php }?></div>
	</div>
<?php }?>
<div class="to-address">
	<div class="to-address-header">
		To:
	</div>
	<div class="to-address-address">
		<?php echo str_replace("\n","<br/>",CHtml::encode($toAddress))?>
	</div>
</div>
<br/><br/>
