<?php

/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class OphTrOperationnote_API extends BaseAPI
{
    /**
     * Return the list of procedures as a string for use in correspondence for the given patient and episode.
     * if the $snomed_terms is true, return the snomed_term, otherwise the standard text term.
     *
     * @param Patient $patient
     *
     * @return string
     * deprecated since 2.0
    public function getLetterProcedures($patient)
    {
        $return = '';

        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if ($plist = $this->getElementForLatestEventInEpisode($episode,
                'Element_OphTrOperationnote_ProcedureList')
            ) {
                foreach ($plist->procedures as $i => $procedure) {
                    if ($i) {
                        $return .= ', ';
                    }
                    $return .= $plist->eye->adjective . ' ' . $procedure->term;
                }
            }
        }

        return $return;
    }
    */
    public function getLetterProcedures($patient , $use_context = true)
    {
        $return = '';

        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if($plist =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_ProcedureList', $patient, $use_context)){
                foreach ($plist->procedures as $i => $procedure) {
                    if ($i) {
                        $return .= ', ';
                    }
                    $return .= $plist->eye->adjective . ' ' . $procedure->term;
                }
            }
        }

        return $return;
    }

    /*
     * OE-6554: I can't find where this function should be used
     * deprecated since 2.0

    public function getLetterProceduresBookingEventID($patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if ($plist = $this->getElementForLatestEventInEpisode($episode,
                'Element_OphTrOperationnote_ProcedureList')
            ) {
                return $plist->booking_event_id;
            }
        }
    }
    */
    public function getLetterProceduresBookingEventID($patient, $use_context = true)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if($plist =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_ProcedureList', $patient, $use_context)){
                return $plist->booking_event_id;
            }
        }
    }

    /*
     * deprecated since 2.0
    public function getLastEye($patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if ($plist = $this->getElementForLatestEventInEpisode($episode, 'Element_OphTrOperationnote_ProcedureList')) {
                return $plist->eye_id;
            }
        }
    }
    */
    public function getLastEye( $patient , $use_context = true )
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if($plist =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_ProcedureList', $patient, $use_context)){
                return $plist->eye_id;
            }
        }
    }

    /*
     * deprecated since 2.0

    public function getLetterProceduresSNOMED($patient)
    {
        $return = '';

        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if ($plist = $this->getElementForLatestEventInEpisode($episode,
                'Element_OphTrOperationnote_ProcedureList')
            ) {
                foreach ($plist->procedures as $i => $procedure) {
                    if ($i) {
                        $return .= ', ';
                    }
                    $return .= $plist->eye->adjective . ' ' . $procedure->snomed_term;
                }
            }
        }

        return $return;
    }
    */
    public function getLetterProceduresSNOMED($patient , $use_context = true)
    {
        $return = '';

        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if($plist =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_ProcedureList', $patient, $use_context)){
                foreach ($plist->procedures as $i => $procedure) {
                    if ($i) {
                        $return .= ', ';
                    }
                    $return .= $plist->eye->adjective . ' ' . $procedure->snomed_term;
                }
            }
        }

        return $return;
    }

    public function getOpnoteWithCataractElementInCurrentEpisode($patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            $event_type = EventType::model()->find('class_name=?', array('OphTrOperationnote'));

            $criteria = new CDbCriteria();
            $criteria->compare('episode_id', $episode->id);
            $criteria->compare('event_type_id', $event_type->id);

            return Element_OphTrOperationnote_Cataract::model()
                ->with('event')
                ->find($criteria);
        }
    }

    public function getPatientUniqueCode($patient)
    {
        $patient_latest_event = $patient->getLatestOperationNoteEventUniqueCode();
        $event_unique_code = '';
        if (!empty($patient_latest_event)) {
            $salt = isset(Yii::app()->params['portal']['credentials']['client_id']) ? Yii::app()->params['portal']['credentials']['client_id'] : '';
            $check_digit1 = new CheckDigitGenerator(
                Yii::app()->params['institution_code'] . $patient_latest_event,
                $salt
            );
            $check_digit2 = new CheckDigitGenerator(
                $patient_latest_event . $patient->dob,
                $salt
            );
            $event_unique_code = Yii::app()->params['institution_code'] . $check_digit1->generateCheckDigit()
                . '-' . $patient_latest_event . '-' . $check_digit2->generateCheckDigit();
        }

        return $event_unique_code;
    }

    /**
     * Get the last operation date
     *
     * @param Patient $patient
     *
     * @return false|string
     * deprecated since 2.0

     public function getLastOperationDate(\Patient $patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            $event_type = EventType::model()->find('class_name=?', array('OphTrOperationnote'));
            $event = $this->getMostRecentEventInEpisode($episode->id, $event_type->id);
            if (isset($event->event_date)) {
                return Helper::convertDate2NHS($event->event_date);
            }
        }

        return '';
    }
    */
    public function getLastOperationDate(\Patient $patient , $use_context = true)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {

            $operationNote = new OphTrOperationnote_API();
            $event = $operationNote->getLatestEvent($patient , $use_context);
            if (isset($event->event_date)) {
                return Helper::convertDate2NHS($event->event_date);
            }
        }

        return '';
    }

    /**
     * Get the last operation date
     *
     * @param Patient $patient
     *
     * @return false|string
     * deprecated since 2.0

    public function getLastOperationDateUnformatted(\Patient $patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            $event_type = EventType::model()->find('class_name=?', array('OphTrOperationnote'));
            $event = $this->getMostRecentEventInEpisode($episode->id, $event_type->id);
            if (isset($event->event_date)) {
                return $event->event_date;
            }
        }

        return '';
    }
    */
    public function getLastOperationDateUnformatted(\Patient $patient , $use_context = true)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            $operationNote = new OphTrOperationnote_API();
            $event = $operationNote->getLatestEvent($patient, $use_context);
            if (isset($event->event_date)) {
                return $event->event_date;
            }
        }

        return '';
    }



    /**
     * Get the last operation's surgeon name
     *
     * @param Patient $patient
     *
     * @return string
     * deprecated since 2.0

    public function getLastOperationSurgeonName(\Patient $patient)
    {
        $surgeon_name = '';
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            $surgeon_element = $this->getElementForLatestEventInEpisode($episode, 'Element_OphTrOperationnote_Surgeon');
            if ($surgeon_element) {
                $surgeon_name = ($surgeon = User::model()->findByPk($surgeon_element->surgeon_id)) ? $surgeon->getFullNameAndTitle() : '';
            }
        }
        
        return $surgeon_name;
    }
     */
    public function getLastOperationSurgeonName(\Patient $patient, $use_context = true)
    {
        $surgeon_name = '';
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            if($surgeon_element =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_Surgeon', $patient, $use_context)){
                $surgeon_name = ($surgeon = User::model()->findByPk($surgeon_element->surgeon_id)) ? $surgeon->getFullNameAndTitle() : '';
            }
        }

        return $surgeon_name;
    }


    /**
     * Get the last operation's location
     *
     * @param Patient $patient
     *
     * @return string
     * deprecated since 2.0

    public function getLastOperationLocation(\Patient $patient)
    {
        $site = '';
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            $site_element = $this->getElementForLatestEventInEpisode($episode, 'Element_OphTrOperationnote_SiteTheatre');
            if($site_element){
                $site = $site_element->site->name;
            }
        }

        return $site;
    }
     */
    public function getLastOperationLocation(\Patient $patient, $use_context = true )
    {
        $site = '';
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            if($site_element =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_SiteTheatre', $patient, $use_context)){
                $site = $site_element->site->name;
            }
        }

        return $site;
    }

    /**
     * Cataract Element from the latest operation note
     *
     * @param Patient $patient
     *
     * @return Element_OphTrOperationnote_Cataract | bool
     * deprecated since 2.0

    public function getLatestCataractElementForEpisode(\Patient $patient)
    {
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            $cataract_element = $this->getElementForLatestEventInEpisode($episode, 'Element_OphTrOperationnote_Cataract');
            if ($cataract_element) {
                return $cataract_element;
            }
        }

        return false;
    }
     */

    public function getLatestCataractElementForEpisode(\Patient $patient, $use_context = true)
    {
        $episode = $patient->getEpisodeForCurrentSubspecialty();
        if ($episode) {
            if($cataract_element =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_Cataract', $patient, $use_context)){
                return $cataract_element;
            }
        }

        return false;
    }

    /**
     * Get the last operation Incision Meridian
     * @param Patient $patient
     * @return string
     */
    public function getLastOperationIncisionMeridian(\Patient $patient)
    {
        $meridian = '';
        $cataract_element = $this->getLatestCataractElementForEpisode($patient);
        if ($cataract_element) {
            $meridian = $cataract_element->meridian . ' degrees';
        }

        return $meridian;
    }

    /**
     * Get the last operation Predicted Refraction
     * @param Patient $patient
     * @return string
     */
    public function getLastOperationPredictedRefraction(\Patient $patient)
    {
        $predicted_refraction = '';
        if ($cataract_element = $this->getLatestCataractElementForEpisode($patient)) {
            $predicted_refraction = $cataract_element->predicted_refraction ?: '';
        }

        return $predicted_refraction;
    }

    /**
     * Get the last operation Details
     * @param Patient $patient
     * @return string
     */
    public function getLastOperationDetails(\Patient $patient)
    {
        $details = '';
        if ($cataract_element = $this->getLatestCataractElementForEpisode($patient)) {
            $details = $cataract_element->report2 ?: '';
        }

        return $details;
    }

    /**
     * Get the last operation Post-op instructions
     * @param Patient $patient
     * @return string
     * deprecated since 2.0

    public function getLastOperationPostOpInstructions(\Patient $patient)
    {
        if ($episode = $patient->getEpisodeForCurrentSubspecialty()) {
            if($latest =  $this->getElementForLatestEventInEpisode($episode, 'Element_OphTrOperationnote_Comments')) {
                return $latest->postop_instructions;
            }
        }

        return '';
    }
    */

    /**
     * Get the last operation Post-op instructions, shortcode::[poi]
     * @param Patient $patient
     * @return string
     */

    public function getLastOperationPostOpInstructions(\Patient $patient, $use_context = true )
    {
        if($latest =  $this->getElementFromLatestEvent('Element_OphTrOperationnote_Comments', $patient, $use_context)){
            return $latest->postop_instructions;
        }
        return '';
    }

}
