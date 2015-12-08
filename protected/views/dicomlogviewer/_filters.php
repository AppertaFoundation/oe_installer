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
<div class="row">&nbsp;</div>
<div class="search-filters theatre-diaries">

	<div class="row">
		<input type="hidden" id="page" name="page" value="1" />
		<div class="large-12 column">
			<div class="panel">
				<div class="row">
					<div class="large-12 column">

						<table class="grid">
							<thead>
								<tr>
									<th>Station ID:</th>
									<th>Location: (like)</th>
									<th>Patient Number:</th>
									<th>Status:</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php echo CHtml::textField('site_id',@$_POST['station_id'],array('class'=>'small fixed-width')); ?></td>
									<td><?php echo CHtml::textField('firm_id',@$_POST['firm_id'],array('class'=>'small fixed-width')); ?></td>
									<td><?php echo CHtml::textField('user',@$_POST['user'],array('class'=>'small fixed-width')); ?></td>
									<td><?php echo CHtml::dropDownList('status', 'status',array('All' => 'All', 'success' => 'Success', 'failed' =>'Failed'));?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="large-12 column">

						<table class="grid">
							<thead>
							<tr>
								<th>Type:</th>
								<th>Study Instance ID: (like)</th>
								<th>File name (like):</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>
									<?php
									echo CHtml::dropDownList('type', 'type',array('All' => 'All', 'Biometry' => 'Biometry'));
									?>
								</td>
								<td>
									<?php //echo CHtml::textField('firm_id',@$_POST['firm_id'],array('class'=>'small fixed-width')); ?>
									<?php
									$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
										'id'=>'firm_id',
										'name'=>'firm_id',
										'value'=>'',
										'sourceUrl'=>array('audit/users'),
										'options'=>array(
											'minLength'=>'3',
										),
										'htmlOptions'=>array(
											'placeholder' => 'Type to search for Study Instance ID...'
										),
									));
									?>
								</td>
								<td>
									<?php
									$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
										'id'=>'user',
										'name'=>'user',
										'value'=>'',
										'sourceUrl'=>array('audit/users'),
										'options'=>array(
											'minLength'=>'3',
										),
										'htmlOptions'=>array(
											'placeholder' => 'Type to search for File name...'
										),
									));
									?>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row">
					<div class="large-10 column">
						<div class="search-filters-extra audit-filters clearfix">
							<fieldset class="inline highlight">
								<label class="inline" for="hos_num">Import Date:</label>
								<?php
								echo CHtml::radioButton('btn', true, array(
									'value'=>'1',
									'id'=>'import_date',
									'name' => 'date',
									'uncheckValue'=>null
								));?>
								<label class="inline" for="hos_num">Study Date:</label>
								<?php
								echo CHtml::radioButton('btn', false, array(
									'value'=>'2',
									'id'=>'stude_date',
									'name' => 'date',
									'uncheckValue'=>null
								));
								?>
								<label class="inline" for="date_from">From:</label>
								<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
									'name' => 'date_from',
									'id' => 'date_from',
									'options' => array(
										'showAnim'=>'fold',
										'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
									),
									'value' => @$_POST['date_from'],
									'htmlOptions' => array(
										'class' => 'small fixed-width'
									)
								))?>
								<label class="inline" for="date_to">To:</label>
								<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
									'name' => 'date_to',
									'id' => 'date_to',
									'options' => array(
										'showAnim'=>'fold',
										'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
									),
									'value' => @$_POST['date_to'],
									'htmlOptions' => array(
										'class' => 'small fixed-width'
									)
								))?>

<!--								<?php /*echo CHtml::textField('hos_num',@$_POST['hos_num'],array('autocomplete'=>Yii::app()->params['html_autocomplete'],'class'=>'small fixed-width'))*/?>
								<?php /*echo CHtml::link('View all',array('audit/'),array('class'=>'inline'))*/?>
								--><?php /*echo CHtml::link("Auto update on",'#',array('class'=>'inline','id'=>'auto_update_toggle'))*/?>
							</fieldset>
						</div>
					</div>
					<div class="large-2 column text-right">
						<img class="loader hidden" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif');?>" alt="loading..." style="margin-right:10px" />
						<button type="submit" class="secondary long">Search</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
