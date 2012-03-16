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
 * This is the model class for table "element_diagnosis".
 *
 * The followings are the available columns in table 'element_diagnosis':
 * @property string $id
 * @property string $event_id
 * @property string $disorder_id
 * @property integer $eye
 *
 * The followings are the available model relations:
 * @property Disorder $disorder
 * @property Event $event
 */
class ElementDiagnosis extends BaseElement
{
	const EYE_LEFT = 0;
	const EYE_RIGHT = 1;
	const EYE_BOTH = 2;

	/**
	 * Returns the static model of the specified AR class.
	 * @return ElementDiagnosis the static model class
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
		return 'element_diagnosis';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('eye', 'required', 'message' => 'Please select an eye option'),
			array('disorder_id', 'required', 'message' => 'Please enter a valid disorder'),
			array('eye', 'numerical', 'integerOnly'=>true),
			array('disorder_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, disorder_id, eye', 'safe', 'on'=>'search'),
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
			'disorder' => array(self::BELONGS_TO, 'Disorder', 'disorder_id'),
			'event' => array(self::BELONGS_TO, 'Event', 'event_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Event',
			'disorder_id' => 'Diagnosis',
			'eye' => 'Eye(s)',
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
		$criteria->compare('event_id',$this->event_id,true);
		$criteria->compare('disorder_id',$this->disorder_id,true);
		$criteria->compare('eye',$this->eye);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Return list of options for eye
	 * @return array
	 */
	public function getEyeOptions()
	{
		return array(
			self::EYE_RIGHT => 'Right',
			self::EYE_BOTH => 'Both',
			self::EYE_LEFT => 'Left'
		);
	}

	public function getEyeText() {
		switch ($this->eye) {
			case self::EYE_LEFT:
				$text = 'Left';
				break;
			case self::EYE_RIGHT:
				$text = 'Right';
				break;
			case self::EYE_BOTH:
				$text = 'Both';
				break;
			default:
				$text = 'Unknown';
				break;
		}

		return $text;
	}

	/**
	 * As the disoder is provided as a string we need to convert it into a disorder id
	 *
	 * @return boolean
	 */
	public function beforeValidate()
	{
		if (!empty($this->disorder_id) && preg_match('/[^\d]/', $this->disorder_id)) {
			$disorder = Disorder::model()->find('term = ? AND systemic = 0', array($this->disorder_id));

			if (empty($disorder)) {
				return false;
			}

			$this->disorder_id = $disorder->id;
		}

		return parent::beforeValidate();
	}

	/**
	 * Returns the disorder if there is one. If not, it returns the most recent disorder for this episode.
	 *
	 * @return object
	 */
	public function getNewestDiagnosis($patient)
	{
		if (!empty($model->disorder)) {
			return $model->disorder;
		} else {
			$firmId = Yii::app()->session['selected_firm_id'];

			if (empty($firmId)) {
				return null;
			}

			$firm = Firm::model()->findByPk($firmId);

			$patientId = $patient->id;

			if (empty($patientId)) {
				return null;
			}

			$sql = '
				SELECT
					ed.*
				FROM
					element_diagnosis ed,
					event ev,
					episode ep,
					firm f,
					service_subspecialty_assignment ssa
				WHERE
					ed.event_id = ev.id
				AND
					ev.episode_id = ep.id
				AND
					ep.firm_id = f.id
				AND
					ep.end_date IS NULL
				AND
					f.service_subspecialty_assignment_id = ssa.id
				AND
					ssa.subspecialty_id = :subspecialty_id
				AND
					ep.patient_id = :patient_id
				ORDER BY
					ed.id
				DESC
				LIMIT 1
			';

			$diagnosis = ElementDiagnosis::model()->findBySql($sql, array(
				'subspecialty_id' => $firm->serviceSubspecialtyAssignment->subspecialty_id,
				'patient_id' => $patientId
			));

			return $diagnosis;
		}
	}
}
