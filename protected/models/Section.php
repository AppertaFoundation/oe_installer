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
 * This is the model class for table "section".
 *
 * The followings are the available columns in table 'section':
 * @property string $id
 * @property string $name
 * @property string $section_type_id
 *
 * The followings are the available model relations:
 * @property Phrase[] $phrases
 */
class Section extends BaseActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Section the static model class
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
		return 'section';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			// 'phrase' => array(self::HAS_MANY, 'Phrase', 'section_id'),
			'section_type' => array(self::BELONGS_TO, 'SectionType', 'section_type_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'section_type_id' => 'Section type',
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
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Given the plaintext name of a section type, returns all sections of that type by looking up the section type id then filtering sections based on it
	 */
	public function getAllByType($type, $name = null) 
	{
		$type_obj = SectionType::model()->findByAttributes(array('name' => $type)); 

		if (!$name) {
			return Section::model()->findAllByAttributes(array('section_type_id' => $type_obj->id)); 
		} else {
			return Section::model()->findAllByAttributes(array('section_type_id' => $type_obj->id, 'name' => $name)); 
		}
	}

	/**
	 * Given the plaintext name of a section type, returns all sections of that type by looking up the section type id then filtering sections based on it
	 */
	public function getByType($type, $name = null) 
	{
		$type_obj = SectionType::model()->findByAttributes(array('name' => $type)); 

		if (!$name) {
			return Section::model()->findByAttributes(array('section_type_id' => $type_obj->id)); 
		} else {
			return Section::model()->findByAttributes(array('section_type_id' => $type_obj->id, 'name' => $name)); 
		}
	}
}
