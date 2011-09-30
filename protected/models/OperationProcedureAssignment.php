<?php
/*
_____________________________________________________________________________
(C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
(C) OpenEyes Foundation, 2011
This file is part of OpenEyes.
OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
_____________________________________________________________________________
http://www.openeyes.org.uk   info@openeyes.org.uk
--
*/

/**
 * This is the model class for table "operation_procedure_assignment".
 *
 * The followings are the available columns in table 'operation_procedure_assignment':
 * @property string $operation_id
 * @property string $proc_id
 * @property integer $display_order
 */
class OperationProcedureAssignment extends BaseActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return OperationProcedureAssignment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'operation_procedure_assignment';
	}

	public function primaryKey()
	{
		return array('operation_id', 'proc_id');
	}

	public function defaultScope()
	{
		return array('order'=>'display_order ASC');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('operation_id, proc_id', 'required'),
			array('display_order', 'numerical', 'integerOnly'=>true),
			array('operation_id, proc_id', 'length', 'max'=>10),
			array('operation_id, proc_id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('operation_id, proc_id, display_order', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'operation' => array(self::BELONGS_TO, 'ElementOperation', 'operation_id'),
			'procedure' => array(self::BELONGS_TO, 'Procedure', 'proc_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'operation_id' => 'Operation',
			'proc_id' => 'Procedure',
			'display_order' => 'Display Order',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('operation_id',$this->operation_id,true);
		$criteria->compare('proc_id',$this->proc_id,true);
		$criteria->compare('display_order',$this->display_order);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}