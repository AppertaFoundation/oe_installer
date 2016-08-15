<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
use OEModule\OphCiExamination\models;

class OphCiExamination_Episode_IOP1 extends \EpisodeSummaryWidget
{
    public function run()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('episode_id', $this->episode->id);
        $criteria->compare('event_type_id', $this->event_type->id);
        $criteria->order = 'event_date';

        $iop = null;
        foreach (Event::model()->findAll($criteria) as $event) {
            if (($iop = models\Element_OphCiExamination_IntraocularPressure::model()->find('event_id=?', array($event->id)))) {
                break;
            }
        }

        $this->render('OphCiExamination_Episode_IOP', array('iop' => $iop));
    }
}
