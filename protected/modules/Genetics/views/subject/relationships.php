<?php
$assetPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.' . $this->getModule()->name . '.assets'));
Yii::app()->clientScript->registerScriptFile($assetPath.'/js/OpenEyes.Genetics.Relationships.js');
Yii::app()->clientScript->registerScriptFile($assetPath.'/js/relationships.js');
?>
<div class="row field-row">
    <div class="large-2 column">
        <label for="genetics_patient_lookup">Relationships:</label>
    </div>
    <div class="large-5 column end">
        <input type="text" name="genetics_patient_lookup" id="genetics_patient_lookup" placeholder="Search for a related patient">
        <div style="display:none" class="no-result-patients warning alert-box">
          <div class="small-12 column text-center">
            No results found.
          </div>
        </div>
        <ul id="relationships_list">
            <?php if($model->relationships):?>
                <?php foreach ($model->relationships as $relationship):?>
                    <li>
                        <span class="genetics_relationship_remove">
                            <i class="fa fa-minus-circle" title="Remove Relationship"></i>
                        </span>
                        <input type="hidden" name="GeneticsPatient[relationships][<?=$relationship->related_to_id?>][related_to_id]" value="<?=$relationship->related_to_id?>">
                        <input type="hidden" name="GeneticsPatient[relationships][<?=$relationship->related_to_id?>][relationship_id]" value="<?=$relationship->relationship_id?>">
                        <?= $relationship->relation->patient->fullName ?> is a  <?=$relationship->relationship->relationship?> to the subject.
                    </li>
                <?php endforeach?>
            <?php endif;?>
        </ul>
    </div>
</div>