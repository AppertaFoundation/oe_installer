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
$uri = preg_replace('/^\//','',preg_replace('/\/$/','',$_SERVER['REQUEST_URI']));
?>
<?php
if (!Yii::app()->user->isGuest) {
	if (!empty(Yii::app()->session['user'])) {
		$user = Yii::app()->session['user'];
	} else {
		$user = User::model()->findByPk(Yii::app()->user->id);
		Yii::app()->session['user'] = $user;
	} ?>
	<div id="user_panel">
		<div class="clearfix">
			<div id="user_id">
				Hi <strong><?php echo $user->first_name . ' ' . $user->last_name; ?></strong>&nbsp;<!--a href="#" class="small">(not you?)</a-->
			</div>
			<ul id="user_nav">
				<?php foreach (Yii::app()->params['menu_bar_items'] as $item) {?>
					<li>
						<?php if ($uri == $item['uri']) {?><span class="selected"><?php echo $item['title']?></span><?php }else{?><?php echo CHtml::link($item['title'],'/'.Yii::app()->baseUrl.$item['uri'])?><?php }?>
					</li>
				<?php }?>
			</ul>
		</div>
		<?php
		if ($this->showForm) {?>
			<div id="user_firm">
				<?php
				$form=$this->beginWidget('CActiveForm', array(
					'id'=>'base-form',
					'enableAjaxValidation'=>false,
					'action' => Yii::app()->createUrl('site')
				));
				?>
				<label>Selected firm: </label>
				<?php
				echo CHtml::dropDownList('selected_firm_id', $this->selectedFirmId, $this->firms);
				$this->endWidget();
				?>
			</div>
		<?php }?>
	</div> <!-- #user_panel -->
<?php
}?>
