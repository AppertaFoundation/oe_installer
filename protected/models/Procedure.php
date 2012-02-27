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

/**
 * This is the model class for table "proc".
 *
 * The followings are the available columns in table 'proc':
 * @property string $id
 * @property string $term
 * @property string $short_format
 * @property integer $default_duration
 *
 * The followings are the available model relations:
 * @property ElementOperation[] $elementOperations
 * @property Specialty $specialty
 * @property SpecialtySubsection $serviceSubsection
 * @property OpcsCode[] $opcsCodes
 */
class Procedure extends BaseActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Procedure the static model class
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
		return 'proc';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('term, short_format, default_duration', 'required'),
			array('default_duration', 'numerical', 'integerOnly'=>true),
			array('term, short_format', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, term, short_format, default_duration', 'safe', 'on'=>'search'),
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
			'operations' => array(self::MANY_MANY, 'ElementOperation', 'operation_procedure_assignment(proc_id, operation_id)'),
			'specialties' => array(self::MANY_MANY, 'Specialty', 'proc_specialty_assignment(proc_id, specialty_id)'),
			'specialtySubsections' => array(self::MANY_MANY, 'SpecialtySubsection', 'proc_specialty_subsection_assignment(proc_id, specialty_subsection_id)'),
			'opcsCodes' => array(self::MANY_MANY, 'OpcsCode', 'procedure_opcs_assignment(proc_id, opcs_code_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'term' => 'Term',
			'short_format' => 'Short Format',
			'default_duration' => 'Default Duration',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('term',$this->term,true);
		$criteria->compare('short_format',$this->short_format,true);
		$criteria->compare('default_duration',$this->default_duration);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Get a list of procedures
	 * Store extra data for the session
	 *
	 * @param string  $term          term to search by
	 *
	 * @return array
	 */
	public static function getList($term)
	{
		$search = "%{$term}%";

		$select = 'term, short_format, id, default_duration';

		$procedures = Yii::app()->db->createCommand()
			->select($select)
			->from('proc')
			->where('term LIKE :term', array(':term'=>$search))
			->order('term')
			->queryAll();

		$data = array();
		$session = Yii::app()->session['Procedures'];

		foreach ($procedures as $procedure) {
			$data[] = $procedure['term'];
			$id = $procedure['id'];
			$session[$id] = array(
				'term' => $procedure['term'],
				'short_format' => $procedure['short_format'],
				'duration' => $procedure['default_duration'],
			);
		}

		Yii::app()->session['Procedures'] = $session;

		return $data;
	}

	public function getListBySpecialty($specialtyId)
	{
		$procedures = Yii::app()->db->createCommand()
			->select('proc.id, proc.term')
			->from('proc')
			->join('proc_specialty_assignment psa', 'psa.proc_id = proc.id')
			->where('psa.specialty_id = :id',
				array(':id'=>$specialtyId))
			->order('proc.term ASC')
			->queryAll();

		$data = array();

		foreach ($procedures as $procedure) {
			$data[$procedure['id']] = $procedure['term'];
		}

		return $data;
	}
}
