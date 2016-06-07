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

?>
<div class="box admin">
    <h2>Examination Event Log(s)</h2>
    <?php
    $form = $this->beginWidget('BaseEventTypeCActiveForm', array(
        'id' => 'adminform',
        'focus' => '#contactname',
    )) ?>
    <table width="100%" cellspacing="0" cellpadding="0">

        <tr>
            <td>Event Id</td>
            <td>
                <?php if($event):?>
                <?php echo $event->id; ?>
                <?php echo CHtml::hiddenField('eventId', $event->id, array('id' => 'hiddenInput')); ?>
                <?php else: ?>
                Event Deleted
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                <?php echo $status; ?>
            </td>
        </tr>
        <tr>
            <td>Unique Code</td>
            <td><?php echo $unique_code; ?></td>
        </tr>
        <tr>
            <td>Patient Identifier</td>
            <td><?php echo $data['patient']['unique_identifier']; ?></td>
        </tr>
        <tr>
            <td>Date of birth</td>
            <td><?php echo date("d M Y", strtotime($data['patient']['dob'])); ?></td>
        </tr>
        <?php
        if($status === 'Duplicate Event'):
            $exams = array($previous, $data);
            ?>
        <tr>
            <th>&nbsp;</th>
            <th>Existing - <?=date("d M Y", strtotime($data['examination_date']));?></th>
            <th>New - <?=date("d M Y", strtotime($previous['examination_date']));?></th>
        </tr>
        <?php
        else:
            $exams = array($data);
        ?>
        <?php endif;?>
        <tr>
            <td>Eye Readings</td>
            <?php
            foreach($exams as $exam):
            ?>
            <td>
                <?php foreach ($exam['patient']['eyes'] as $eyes) {
                    echo $eyes['label']; ?>
                    <br/> Refraction ( Sphere-<?php echo $eyes['reading'][0]['refraction']['sphere']; ?>, Cylinder-<?php echo $eyes['reading'][0]['refraction']['cylinder']; ?>, Axis-<?php echo $eyes['reading'][0]['refraction']['axis']; ?> )
                    <br/>IOP ( <?php echo $eyes['reading'][0]['iop']['mm_hg']; ?> mmhg, <?php echo $eyes['reading'][0]['iop']['instrument']; ?>)
                    <br/>
                    <br/>
                <?php } ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td>
                OpTom Details
            </td>
            <?php foreach($exams as $exam):
            ?>
            <td>
                Name : <?php echo $exam['op_tom']['name']; ?>
                <br/>
                Address : <?php echo $exam['op_tom']['address']; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php if($status === 'Unfound Event'):?>
        <tr>
            <td><label for="patient-search">Assign To</label></td>
            <td>
                <input type="text" name="search" id="patient-search"><img class="loader" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif') ?>"
                alt="loading..." style="margin-right: 10px; display: none;"/>
            </td>
        </tr>
        <?php endif;?>
    </table>
    <?php echo $form->formActions($buttons); ?>

    <?php $this->endWidget() ?>
</div>

<script type="text/javascript">

    $(document).ready(function() {
        OpenEyes.UI.Search.setSingleSelect();
        OpenEyes.UI.Search.init($('#patient-search'));
    });
</script>
