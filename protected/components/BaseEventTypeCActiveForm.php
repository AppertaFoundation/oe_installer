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

class BaseEventTypeCActiveForm extends CActiveForm
{

	// This property will be used as default widget options for the field widget options.
	public $widgetOptions = array();

	public function dropDownList($model, $field, $data, $htmlOptions=array(), $hidden=false, $widgetOptions=array())
	{
		$this->widget('application.widgets.DropDownList', array(
			'element' => $model,
			'field' => $field,
			'data' => $data,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions),
			'hidden' => $hidden
		));
	}

	public function dropDownListRow($model, $fields, $datas, $htmlOptions=array())
	{
		$this->widget('application.widgets.DropDownListRow', array(
			'element' => $model,
			'fields' => $fields,
			'datas' => $datas,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function dropDownListNoPost($id, $options, $selected_value, $htmlOptions=array())
	{
		$this->widget('application.widgets.DropDownListNoPost', array(
			'id' => $id,
			'options' => $options,
			'selected_value' => $selected_value,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function radioButtons($element, $field, $table=null, $selected_item=null, $maxwidth=false, $hidden=false, $no_element=false, $label_above=false, $htmlOptions=array(), $widgetOptions=array())
	{
		$data = $element->getFormOptions($table);
		$this->widget('application.widgets.RadioButtonList', array(
			'element' => $element,
			'name' => get_class($element)."[$field]",
			'field' => $field,
			'data' => $data,
			'selected_item' => $selected_item,
			'maxwidth' => $maxwidth,
			'hidden' => $hidden,
			'no_element' => $no_element,
			'label_above' => $label_above,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function radioBoolean($element, $field, $htmlOptions=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.RadioButtonList', array(
			'element' => $element,
			'name' => get_class($element)."[$field]",
			'field' => $field,
			'data' => array(
				1 => 'Yes',
				0 => 'No'
			),
			'selected_item' => $element->$field,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function datePicker($element, $field, $options=array(), $htmlOptions=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.DatePicker', array(
			'element' => $element,
			'name' => get_class($element)."[$field]",
			'field' => $field,
			'options' => $options,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function textArea($element, $field, $options=array(), $hidden=false, $widgetOptions=array())
	{
		if (!isset($options['rows'])) {
			throw new SystemException('textArea requires the rows option to be specified');
		}
		if (!isset($options['cols'])) {
			throw new SystemException('textArea requires the cols option to be specified');
		}

		$this->widget('application.widgets.TextArea', array_merge(array(
			'element' => $element,
			'field' => $field,
			'hidden' => $hidden,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		), $options));
	}

	public function textField($element, $field, $htmlOptions=array(), $links=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.TextField', array(
			'element' => $element,
			'name' => get_class($element)."[$field]",
			'field' => $field,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions),
			'links' => $links
		));
	}

	public function passwordField($element, $field, $htmlOptions=array(), $widgetOptions=array())
	{
		$widgetOptions['password'] = 1;

		$this->widget('application.widgets.TextField', array(
			'element' => $element,
			'name' => get_class($element)."[$field]",
			'field' => $field,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function passwordConfirmField($element, $label, $name, $htmlOptions=array(), $widgetOptions=array())
	{
		$widgetOptions = array_merge(array(
			'label' => $label,
			'password' => 1
		), $widgetOptions);

		$this->widget('application.widgets.TextField', array(
			'element' => $element,
			'name' => $name,
			'field' => null,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function checkBox($element, $field, $options=false, $htmlOptions=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.CheckBox', array(
			'element' => $element,
			'field' => $field,
			'options' => $options,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function checkBoxArray($element,$labeltext,$fields, $options=false, $widgetOptions=array())
	{
		$this->widget('application.widgets.CheckBoxArray', array(
			'element' => $element,
			'fields' => $fields,
			'labeltext' => $labeltext,
			'options' => $options,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function multiSelectList($element, $field, $relation, $relation_id_field, $options, $default_options, $htmlOptions=array(), $hidden=false, $widgetOptions=array())
	{
		$this->widget('application.widgets.MultiSelectList', array(
			'element' => $element,
			'field' => $field,
			'relation' => $relation,
			'relation_id_field' => $relation_id_field,
			'options' => $options,
			'default_options' => $default_options,
			'htmlOptions' => $htmlOptions,
			'hidden' => $hidden,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function dropDownTextSelection($element, $text_field, $options, $htmlOptions=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.DropDownTextSelection', array(
			'element' => $element,
			'field' => $text_field,
			'options' => $options,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function multiDropDownTextSelection($element, $text_field, $options, $htmlOptions, $widgetOptions=array())
	{
		$this->widget('application.widgets.MultiDropDownTextSelection', array(
			'element' => $element,
			'field' => $text_field,
			'options' => $options,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function hiddenInput($element, $field, $value=false, $htmlOptions=array(), $widgetOptions=array())
	{
		$this->widget('application.widgets.HiddenField', array(
			'element' => $element,
			'field' => $field,
			'value' => $value,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function slider($element, $field, $options, $widgetOptions=array())
	{
		$this->widget('application.widgets.Slider', array(
			'element' => $element,
			'field' => $field,
			'min' => $options['min'],
			'max' => $options['max'],
			'step' => $options['step'],
			'force_dp' => @$options['force_dp'],
			'prefix_positive' => @$options['prefix_positive'],
			'remap_values' => @$options['remap'],
			'null' => @$options['null'],
			'append' => @$options['append'],
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function sliderTable($element, $field, $data, $widgetOptions=array())
	{
		$this->widget('application.widgets.SliderTable', array(
			'element' => $element,
			'field' => $field,
			'data' => $data,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}

	public function hiddenField($element, $field, $htmlOptions=array(), $widgetOptions=array()) {
		$this->widget('application.widgets.HiddenField', array(
			'element' => $element,
			'field' => $field,
			'htmlOptions' => $htmlOptions,
			'widgetOptions' => array_merge($this->widgetOptions, $widgetOptions)
		));
	}
}