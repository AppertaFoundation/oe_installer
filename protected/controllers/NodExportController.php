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
class NodExportController extends BaseController
{
    /**
     * @var string the default layout for the views
     */
    public $layout = '//layouts/main';

    protected $exportPath;
    protected $zipName;

    protected $institutionCode;

    private $startDate = '';
    private $endDate = '';

    private $allEpisodeIds;

    public function accessRules()
    {
        return array(
            array('allow',
                'roles' => array('NOD Export'),
            ),
        );
    }

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function init()
    {
        $this->institutionCode = Yii::app()->params['institution_code'];
        $date = date('YmdHi');
        $this->exportPath = realpath(dirname(__FILE__) . '/..') . '/runtime/nod-export/' . $this->institutionCode . '/' . $date;
        $this->zipName = $this->institutionCode . '_' . $date . '_NOD_Export.zip';

        if (!file_exists($this->exportPath)) {
            mkdir($this->exportPath, 0777, true);
        }
        
        $startDate = Yii::app()->request->getParam("date_from", '');
        $endDate =  Yii::app()->request->getParam("date_to", '');
        
        $startDateTime = null;
        $endDateTime = null;
        
        if($startDate){
            $startDateTime = new DateTime($startDate);
        }
        
        if($endDate){
            $endDateTime = new DateTime($endDate);
        }

        // if start date is greater than end date we exchange the two dates
        if(($startDateTime instanceof DateTime && $endDateTime instanceof DateTime) && $endDateTime < $startDateTime){
            $tempDate = $endDateTime;
            $endDateTime = $startDateTime;
            $startDateTime = $tempDate;
            $tempDate = null;
        }
        
        if($startDate){
            $this->startDate = $startDateTime->format('Y-m-d');
        }
        
        if($endDate){
            $this->endDate = $endDateTime->format('Y-m-d');
        }

        parent::init();
    }

    private function getAllEpisodeId()
    {
        $this->saveIds('tmp_episode_ids', $this->getEpisodeDiabeticDiagnosis());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeDrug());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeBiometry());
        $this->saveIds('tmp_episode_ids', $this->getEpisodePostOpComplication());
        $this->saveIds('tmp_episode_ids', $this->getEpisodePreOpAssessment());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeIOP());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeVisualAcuity());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeRefraction());
        $this->saveIds('tmp_operation_ids', $this->getEpisodeOperationCoPathology());
        $this->saveIds('tmp_operation_ids', $this->getEpisodeOperationAnaesthesia());
        $this->saveIds('tmp_operation_ids', $this->getEpisodeOperationIndication());
        $this->saveIds('tmp_operation_ids', $this->getEpisodeOperationComplication());
        $this->saveIds('tmp_episode_ids', $this->getEpisodeOperation());
        $this->saveIds('tmp_treatment_ids', $this->getEpisodeTreatmentCataract());
        $this->getEpisodeTreatment();
    }

    /**
     * Save CSV file and returns episodeIDs if $episodeIdField isset
     *
     * @param string $dataQuery SQL query
     * @param string $filename
     * @param string $episodeIdField
     * @return null|array
     */
    private function saveCSVfile($dataQuery, $filename, $dataFormatter = null, $IdField = null)
    {
        $resultIds = array();
        $offset = 0;
        $chunk = 200000;

        if (!isset($dataQuery['query'])) {
            throw new Exception('Query not found: array key "query" not exist');
        }

        while (true) {
            $runQuery = $dataQuery['query']. " LIMIT ".$chunk." OFFSET ".$offset.";";
            $dataCmd = Yii::app()->db->createCommand($runQuery);

            $data = $dataCmd->queryAll();

            if($offset == 0 && (!count($data)>0)){
                file_put_contents($this->exportPath . '/' . $filename . '.csv', $dataQuery['header'], FILE_APPEND);
            }

            if(count($data) > 0)
            {
                $csv = $this->array2Csv($data, ($offset == 0 && isset($dataQuery['header'])) ? $dataQuery['header'] : null, $dataFormatter);

                file_put_contents($this->exportPath . '/' . $filename . '.csv', $csv, FILE_APPEND);

                if($IdField) {
                    foreach ($data as $d) {
                        $resultIds[] = $d[$IdField];
                    }
                }

                $offset+=$chunk;
                unset($data);
            }else {
                break;
            }
        }
        return $resultIds;
    }

    private function getIdArray($data, $IdField)
    {
        $objectIds = array();
        if ($IdField && $data) {
            foreach ($data as $row) {
                $objectIds[] = $row[$IdField];
            }
        }
        return $objectIds;
    }

    public function actionIndex()
    {
        // TODO: need to create views!!!
        $this->render('//nodexport/index');
    }

    public function actionTest()
    {

        $this->createAllTempTables();
        $this->getAllEpisodeId();
        $this->clearAllTempTables();
    }

    private function createAllTempTables()
    {
        // DROP all tables if exsist before createing them
        $this->clearAllTempTables();
        
        $createTempQuery = <<<EOL
			DROP TEMPORARY TABLE IF EXISTS tmp_episode_ids;
			
			CREATE TEMPORARY TABLE tmp_episode_ids(
				id  int(10) UNSIGNED NOT NULL UNIQUE,
				KEY `tmp_episode_ids_id` (`id`)
			);
			
			DROP TEMPORARY TABLE IF EXISTS tmp_operation_ids;
			
			CREATE TEMPORARY TABLE tmp_operation_ids(
				id  int(10) UNSIGNED NOT NULL UNIQUE,
				KEY `tmp_operation_ids_id` (`id`)
			);
			
			DROP TABLE IF EXISTS tmp_treatment_ids;
			
			CREATE TABLE tmp_treatment_ids(
				id  int(10) UNSIGNED NOT NULL UNIQUE,
				KEY `tmp_treatment_ids_id` (`id`)
			);
			

			DROP TEMPORARY TABLE IF EXISTS tmp_pathology_type;
			CREATE TEMPORARY TABLE tmp_pathology_type (
				`nodcode` INT(10) UNSIGNED NOT NULL,
				`term` VARCHAR(100)
			);
			INSERT INTO tmp_pathology_type (`nodcode`, `term`)
			VALUES
				(0, 'None'),
				(1, 'Age related macular degeneration'),
				(2, 'Amblyopia'),
				(4, 'Diabetic retinopathy'),
				(5, 'Glaucoma'),
				(7, 'Degenerative progressive high myopia'),
				(8, 'Ocular Hypertension'),
				(11, 'Stickler Syndrome'),
				(12, 'Uveitis'),
				(13, 'Pseudoexfoliation'),
				(13, 'phacodonesis'),
				(18, 'macular hole'),
				(19, 'epiretinal membrane'),
				(20, 'retinal detachment ');
							
			DROP TEMPORARY TABLE IF EXISTS tmp_iol_positions;
			CREATE TEMPORARY TABLE tmp_iol_positions (
				`nodcode` INT(10) UNSIGNED NOT NULL,
				`term` VARCHAR(100)
			);

			INSERT INTO tmp_iol_positions (`nodcode`, `term`)
			VALUES
				(0, 'None'),
				(8, 'In the bag'),
				(9, 'Partly in the bag'),
				(6, 'In the sulcus'),
				(2, 'Anterior chamber'),
				(12, 'Sutured posterior chamber'),
				(5, 'Iris fixated'),
				(13, 'Other');
							
			DROP TEMPORARY TABLE IF EXISTS tmp_anesthesia_type;

			CREATE TEMPORARY TABLE tmp_anesthesia_type(
				`id` INT(10) UNSIGNED NOT NULL,
				`name` VARCHAR(50),
				`code` VARCHAR(50),
				`nod_code` VARCHAR(50),
				`nod_desc` VARCHAR(50),
				KEY `tmp_anesthesia_type_name` (`name`)
			);

			INSERT INTO tmp_anesthesia_type(`id`, `name`, `code`, `nod_code`, `nod_desc`)
			VALUE
			(1, 'Topical', 'Top', 4, 'Topical anaesthesia alone'),
			(2, 'LAC',     'LAC', 2, 'Local anaesthesia alone'),
			(3, 'LA',      'LA',  2, 'Local anaesthesia alone'),
			(4, 'LAS',     'LAS', 2, 'Local anaesthesia alone'),
			(5, 'GA',      'GA',  1, 'General anaesthesia alone');
			
					
		DROP TEMPORARY TABLE IF EXISTS tmp_complication_type;

		CREATE TEMPORARY TABLE tmp_complication_type (
			`code` INT(10) UNSIGNED NOT NULL,
			`name` VARCHAR(100)
		);

		INSERT INTO tmp_complication_type (`code`, `name`)
		VALUES
			(0, 'None'),
			(1, 'choroidal / suprachoroidal haemorrhage'),
			(2, 'corneal burn'),
			(3, 'corneal epithelial abrasion'),
			(4, 'corneal oedema'),
			(5, 'endothelial damage / Descemet\'s tear'),
			(6, 'epithelial abrasion'),
			(7, 'hyphaema'),
			(8, 'IOL into the vitreous'),
			(9, 'iris prolapse'),
			(10, 'iris trauma'),
			(11, 'lens exchange required / other IOL problems'),
			(12, 'nuclear / epinuclear fragment into vitreous'),
			(13, 'PC rupture - no vitreous loss'),
			(14, 'PC rupture - vitreous loss'),
			(15, 'phaco burn / wound problems'),
			(16, 'suprachoroidal haemorrhage'),
			(17, 'torn iris / damage from the phaco'),
			(18, 'vitreous loss'),
			(19, 'vitreous to the section at end of surgery'),
			(20, 'zonule dialysis'),
			(21, 'zonule rupture - vitreous loss'),
			(25, 'Not recorded'),
			(999, 'other');
                        
                        
                        CREATE TABLE tmp_biometry_formula (
                                `code` INT(10) UNSIGNED NOT NULL,
                                `desc` VARCHAR(100)
                        );
                        
                        INSERT INTO tmp_biometry_formula (`code`, `desc`)
                        VALUES
                        (1, 'Haigis'),
                        (2, 'Holladay'),
                        (3, 'Holladay II'),
                        (4, 'SRK/T'),
                        (5, 'SRK II'),
                        (6, 'Hoffer Q'),
                        (7, 'Average of SRK/T + Holladay + Hoffer Q'),
                        (9, 'Not recorded');
                        
                        CREATE TABLE tmp_episode_diagnosis (
                           `oe_subspecialty_name` VARCHAR(50),
                           `rco_condition_name` VARCHAR(50),
                           `oe_subspecialty_id` INT(10) UNSIGNED NOT NULL,
                           `rco_condition_id` INT(10) UNSIGNED NOT NULL
                        );
                
                        INSERT INTO tmp_episode_diagnosis (`oe_subspecialty_name`, `rco_condition_name`, `oe_subspecialty_id`, `rco_condition_id`)
                        VALUES
                        ('Adnexal', 'Lacrimal Orbital & Socket', 2, 12),
                        ('Cataract', 'Cataract', 4, 2),
                        ('Cornea', 'Corneal', 5, 4),
                        ('External', 'External', 6, 20),
                        ('Refractive', 'Refractive', 13, 17),
                        ('Accident & Emergency', 'Eye Casualty', 1, 7),
                        ('General Ophthalmology', 'General', 12, 10),
                        ('Glaucoma', 'Glaucoma', 7, 11),
                        ('Medical Retinal', 'Medical retina', 8, 13),
                        ('Uveitis', 'Medical retina', 15, 13),
                        ('Oncology', 'Ocular Oncology', 10, 21),
                        ('Neuro-ophthalmology', 'Neuroophthalmology', 9, 14),
                        ('Strabismus', 'Strabismus & Paediatric', 14, 18),
                        ('Paediatrics', 'Strabismus & Paediatric', 11, 18),
                        ('Vitreoretinal', 'Vitreoretinal', 16, 19);
                
                        
                        
                        
                    DROP TABLE IF EXISTS tmp_episode_drug_route;
                
                    CREATE TABLE tmp_episode_drug_route (
                        `oe_route_id` INT(10) UNSIGNED,
                        `oe_route_name` VARCHAR(50), 
                        `oe_option_id` INT(10) UNSIGNED DEFAULT NULL, 
                        `oe_option_name` VARCHAR(50), 
                        `nod_id` INT(10) UNSIGNED DEFAULT NULL, 
                        `nod_name` VARCHAR(50),
                        KEY `tmp_episode_drug_route_oe_route_id` (`oe_route_id`)
                    ); 
                
                    INSERT INTO `tmp_episode_drug_route` ( `oe_route_id`, `oe_route_name`, `oe_option_id`, `oe_option_name`, `nod_id`, `nod_name` )
                        VALUES
                         (1, 'Eye', 1, 'Left', 1, 'Left eye'), 
                         (1, 'Eye', 2, 'Right', 2, 'Right eye'), 
                         (1, 'Eye', 3, 'Both', 4, 'Both eyes'), 
                         (2, 'IM', NULL, "", 7, 'Intramuscular injection'), 
                         (3, 'Inhalation', NULL, "", 6, 'Inhaled'), 
                         (4, 'Intracameral', NULL, "", 5,'Intracameral'), 
                         (5, 'Intradermal', NULL, "", 99, 'Other'), 
                         (6, 'Intravitreal', NULL, "", 99, 'Other'), 
                         (7, 'IV', NULL, "", 9, 'Intravenously'), 
                         (8, 'Nose', NULL, "", 8, 'Intranasally'), 
                         (9, 'Ocular muscle', NULL, "", 7, 'Intramuscular injection'), 
                         (10, 'PO', NULL, "", 12, 'Orally'), 
                         (11, 'PR', NULL, "", 15, 'Per rectum'), 
                         (12, 'PV', NULL, "", 99, 'Other'), 
                         (13, 'Sub-Conj', NULL, "", 18, 'Subconjunctival'), 
                         (14, 'Sub-lingual', NULL, "", 19, 'Sub-lingual'), 
                         (15, 'Subcutaneous', NULL, "", 17, 'Subcutaneously'), 
                         (16, 'To Nose', NULL, "", 8, 'Intranasally'), 
                         (17, 'To skin', NULL, "", 24, 'Trans-cutaneous'), 
                         (18, 'Topical', NULL, "", 23, 'Topically'), 
                         (19, 'n/a', NULL, "", 99, 'Other'), 
                         (20, 'Other', NULL, "", 99, 'Other');

             
                
EOL;
        Yii::app()->db->createCommand($createTempQuery)->execute();

    }

    private function clearAllTempTables()
    {
        $cleanQuery = <<<EOL
                
                DROP TEMPORARY TABLE IF EXISTS tmp_complication_type;
                DROP TEMPORARY TABLE IF EXISTS tmp_anesthesia_type;
                DROP TEMPORARY TABLE IF EXISTS tmp_iol_positions;
                DROP TEMPORARY TABLE IF EXISTS tmp_pathology_type;
                DROP TABLE IF EXISTS tmp_biometry_formula;
                DROP TABLE IF EXISTS tmp_episode_diagnosis;
                DROP TABLE IF EXISTS tmp_episode_drug_route;
                DROP TEMPORARY TABLE IF EXISTS tmp_episode_ids;
                DROP TEMPORARY TABLE IF EXISTS tmp_operation_ids;
                DROP TABLE IF EXISTS tmp_treatment_ids;


EOL;

        Yii::app()->db->createCommand($cleanQuery)->execute();
    }

    /**
     * This table will contain the only person identifiable data (surgeon’s GMC number or national code
     * ) stored on the RCOphth NOD. This information will be used to match a surgeon to their
     * own data on the RCOphth NOD website and in the prospective projects enable thematching of a surgeons’
     * record if they move between centres. This was not done with the ‘legacy’ data already in
     *  NOD and therefore at present we do not have the ability to identify individual surgeons.
     */
    private function getSurgeons()
    {


        $query = <<<EOL
    SELECT id as Surgeonid, IFNULL(registration_code, '') as GMCnumber, IFNULL(title, '') as Title, IFNULL(first_name, '') as FirstName,
    user.`doctor_grade_id`  AS CurrentGradeId
FROM user 
WHERE is_surgeon = 1 AND active = 1 
EOL;

        $dataQuery = array(
            'query' => $query,
            'header' => array('Surgeonid', 'GMCnumber', 'Title', 'FirstName', 'CurrentGradeId'),
        );

        $this->saveCSVfile($dataQuery, 'Surgeon');

    }

    /**
     * The extraction of patient data is psuedoanonymised. All tables prefixed with “Patient” link back to the
     * “Patient” table via the ‘PatientId’ variable. Each patient on the RCOphth NOD will have one row in the “Patient” table.
     */
    private function getPatients()
    {

        $query = "SELECT id as PatientId, "
            . "(SELECT CASE WHEN gender='F' THEN 2 WHEN gender='M' THEN 1 ELSE 9 END) as GenderId, "
            . "IFNULL((SELECT `code` FROM ethnic_group WHERE ethnic_group.id = patient.ethnic_group_id), 'Z') AS EthnicityId, "
            . "IFNULL( DATE_ADD(dob, INTERVAL ROUND((RAND() * (3-1))+1) MONTH) , '') AS DateOfBirth, "
            . "IFNULL(date_of_death, '') as DateOfDeath, "
            . "'' as IMDScore, '' as IsPrivate "
            . "FROM patient "
            . "WHERE patient.id IN (SELECT patient_id FROM episode WHERE episode.id IN
                                    (SELECT id FROM ((SELECT id FROM tmp_episode_ids)
                                            UNION ALL
                                    (SELECT episode_id AS id FROM event WHERE event.id in (SELECT id FROM tmp_operation_ids)) 
                                            UNION ALL
                                    (SELECT episode_id AS id FROM event e 
                                            JOIN et_ophtroperationnote_procedurelist eop ON eop.event_id = e.id 
                                            JOIN ophtroperationnote_procedurelist_procedure_assignment oppa ON oppa.procedurelist_id = eop.id 
                                            WHERE oppa.id IN (SELECT id FROM tmp_treatment_ids))) a )) ";

        $dataQuery = array(
            'query' => $query,
            'header' => array('PatientId', 'GenderId', 'EthnicityId', 'DateOfBirth', 'DateOfDeath', 'IMDScore', 'IsPrivate'),
        );

        $this->saveCSVfile($dataQuery, 'Patient');
    }

    private function getPatientCviStatus()
    {

        $query = "SELECT
                                episode.`patient_id` AS PatientId,
                                cvi_status_date AS `Date`,
                                (SELECT CASE WHEN DAYNAME(DATE) IS NULL THEN 1 END) AS IsDateApprox, 
                                (SELECT CASE WHEN cvi_status_id=4 THEN 1 END) AS IsCVIBlind, 
                                (SELECT CASE WHEN cvi_status_id=3 THEN 1 END) AS IsCVIPartial
                        FROM episode
                        JOIN patient ON episode.`patient_id` = patient.id
                        JOIN patient_oph_info ON patient.id = patient_oph_info.`patient_id`
                        WHERE episode.`patient_id` IN (SELECT patient_id FROM episode WHERE episode.id IN 
								(SELECT id FROM ((SELECT id FROM tmp_episode_ids) 
									UNION ALL
								(SELECT episode_id AS id FROM event WHERE event.id in (SELECT id FROM tmp_operation_ids)) 
									UNION ALL
								(SELECT episode_id AS id FROM event e 
									JOIN et_ophtroperationnote_procedurelist eop ON eop.event_id = e.id 
									JOIN ophtroperationnote_procedurelist_procedure_assignment oppa ON oppa.procedurelist_id = eop.id 
									WHERE oppa.id IN (SELECT id FROM tmp_treatment_ids))) a ))";

        $dataQuery = array(
            'query' => $query,
            'header' => array('PatientId', 'Date', 'IsDateApprox', 'IsCVIBlind', 'IsCVIPartial'),
        );

        $this->saveCSVfile($dataQuery, 'PatientCVIStatus');
    }

    private function getEpisode()
    {

        $query = "SELECT patient_id as PatientId, id as EpisodeId, DATE(start_date) as Date FROM episode WHERE episode.id IN
								(SELECT id FROM ((SELECT id FROM tmp_episode_ids) 
									UNION ALL
								(SELECT episode_id AS id FROM event WHERE event.id in (SELECT id FROM tmp_operation_ids)) 
									UNION ALL
								(SELECT episode_id AS id FROM event e 
									JOIN et_ophtroperationnote_procedurelist eop ON eop.event_id = e.id 
									JOIN ophtroperationnote_procedurelist_procedure_assignment oppa ON oppa.procedurelist_id = eop.id 
									WHERE oppa.id IN (SELECT id FROM tmp_treatment_ids))) a ) "
        ;

        $dataQuery = array(
            'query' => $query,
            'header' => array('PatientId', 'EpisodeId', 'Date'),
        );

        $this->saveCSVfile($dataQuery, 'Episode');
    }

    private function getEpisodeDiagnosis()
    {

        $query = "SELECT
                        id AS EpisodeId,
                        (SELECT CASE WHEN eye_id = 1 THEN 'L' WHEN eye_id = 2 THEN 'R' WHEN eye_id = 3 THEN 'B' ELSE 'N' END ) AS Eye,
                        DATE(last_modified_date) AS `Date`,
                        (
                                SELECT (
                                        IFNULL(
                                                (SELECT last_modified_user_id FROM episode_version WHERE ep.id=id ORDER BY last_modified_date ASC LIMIT 1), 
                                                (SELECT last_modified_user_id FROM episode WHERE id = ep.id)
                                        )
                                )
                        ) AS SurgeonId,
                        (
                                SELECT rco_condition_id FROM tmp_episode_diagnosis WHERE oe_subspecialty_id = (
                                SELECT service_subspecialty_assignment.`subspecialty_id` FROM firm 
                                JOIN service_subspecialty_assignment ON firm.service_subspecialty_assignment_id = service_subspecialty_assignment.`id`
                                WHERE firm.id = ep.`firm_id`)
                                
                        ) AS ConditionId,
                        IFNULL(disorder_id, '') AS DiagnosisTermId
                FROM episode ep 
                WHERE 
                ep.`firm_id` IS NOT NULL AND
                ep.id IN
                    (SELECT id FROM ((SELECT id FROM tmp_episode_ids)
                            UNION ALL
                    (SELECT episode_id AS id FROM event WHERE event.id in (SELECT id FROM tmp_operation_ids))
                            UNION ALL
                    (SELECT episode_id AS id FROM event e 
                            JOIN et_ophtroperationnote_procedurelist eop ON eop.event_id = e.id 
                            JOIN ophtroperationnote_procedurelist_procedure_assignment oppa ON oppa.procedurelist_id = eop.id 
                            WHERE oppa.id IN (SELECT id FROM tmp_treatment_ids))) a )
                HAVING ConditionId IS NOT NULL
                "
        ;

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Eye', 'Date', 'SurgeonId', 'ConditionId', 'DiagnosisTermId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeDiagnosis', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodeDiabeticDiagnosis()
    {
        $dateWhere = $this->getDateWhere('s');

        $disorder = Disorder::model()->findByPk(Disorder::SNOMED_DIABETES);
        $disorder_ids = implode(",", $disorder->descendentIds());

        $query = <<<EOL
                    SELECT 	
                    e.id AS EpisodeId, 
                    1 AS IsDiabetic,

                    (
                            SELECT CASE 
                                    WHEN d.id IN (23045005,28032008,46635009,190368000,190369008,190371008,190372001,199229001,237618001,290002008,313435000,314771006,314893005,
                                                               314894004,401110002,420270002,420486006,420514000,420789003,420825003,420868002,420918009,421165007,421305000,421365002,421468001,
                                                               421893009,421920002,422228004,422297002,425159004,425442003,426907004,427571000,428896009,11530004) THEN 1
                                    WHEN d.id IN (9859006,44054006,81531005,190388001,190389009,190390000,190392008,199230006,237599002,237604008,237614004,237650006,
                                                               313436004,314772004,314902007,314903002,314904008,359642000,395204000,420279001,420414003,420436000,420715001,420756003,
                                                               421326000,421631007,421707005,421750000,421779007,421847006,421986006,422014003,422034002,422099009,422166005,423263001,
                                                               424989000,427027005,427134009,428007007,359638003) THEN 2
                                    WHEN d.id IN (237626009,237627000,11687002,46894009,71546005,75022004,420491007,420738003,420989005,421223006,421389009,421443003,
                                                               422155003,76751001,199223000,199225007,199227004) THEN 3
                                    WHEN d.id IN (237619009,359939009) THEN 4
                                    WHEN d.id IN (14052004,28453007) THEN 5
                                    WHEN d.id IN (2751001,4307007,4783006,5368009,5969009,8801005,33559001,42954008,49817004,51002006,57886004,59079001,70694009,
                                                              73211009,75524006,75682002,111552007,111554008,127012008,190329007,190330002,190331003,190406000,190407009,190410002,
                                                              190411003,190412005,190416008,190447002,199226008,199228009,199231005,237600004,237601000,237603002,237611007,237612000,
                                                              237616002,237617006,237620003,238981002,275918005,276560009,408540003,413183008,420422005,420683009,421256007,421895002,
                                                              422088007,422183001,422275004,426705001,426875007,427089005,441628001,91352004,399144008) THEN 9
                                    ELSE ""
                                    END 

                    ) AS DiabetesTypeId,
                    "" AS DiabetesRegimeId,
                    IFNULL((DATE_FORMAT(`date`, '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(`date`, '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))),"") AS AgeAtDiagnosis
            FROM secondary_diagnosis s
            JOIN disorder d ON d.id = s.disorder_id
            JOIN episode e ON e.patient_id = s.patient_id
            JOIN patient p ON e.patient_id = p.id
            WHERE d.id IN ( $disorder_ids ) $dateWhere
EOL;

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'IsDiabetic', 'DiabetesTypeId', 'DiabetesRegimeId', 'AgeAtDiagnosis'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeDiabeticDiagnosis', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodeDrug()
    {

        $dateWhere = $this->getDateWhere('m');
        $dateWhereScript = $this->getDateWhere('opi');

        $query = <<<EOL
                    (SELECT e.id AS EpisodeId ,
                    (SELECT CASE WHEN option_id = 1 THEN 'L' WHEN option_id = 2 THEN 'R' WHEN option_id = 3 THEN 'B'  ELSE 'N' END) AS Eye,
                    (SELECT CASE WHEN m.drug_id IS NOT NULL THEN (SELECT `name`  FROM drug WHERE id = m.drug_id)
                                    WHEN m.medication_drug_id IS NOT NULL THEN (SELECT `name` FROM medication_drug WHERE id = m.medication_drug_id)
                                    WHEN m.medication_drug_id IS NULL THEN '' END) AS DrugId,
                    IFNULL(
                    (
                          SELECT nod_id FROM tmp_episode_drug_route
                          WHERE 
                         (opi.route_id = tmp_episode_drug_route.oe_route_id AND tmp_episode_drug_route.`oe_option_id` = opi.route_option_id) OR
                         (opi.route_id = tmp_episode_drug_route.oe_route_id AND tmp_episode_drug_route.`oe_option_id` IS NULL) )
                          , ""
                    ) AS DrugRouteId,
                    (SELECT CASE WHEN m.start_date IS NULL THEN '' ELSE m.start_date END) AS StartDate,
                    (SELECT CASE WHEN m.end_date IS NULL THEN '' ELSE m.end_date END) AS StopDate,
                    (SELECT CASE WHEN opi.prescription_id IS NOT NULL THEN 1 ELSE 0 END ) AS IsAddedByPrescription,
                    (SELECT CASE WHEN opi.continue_by_gp IS NULL THEN 0 ELSE opi.continue_by_gp END) AS IsContinueIndefinitely,
                    (SELECT CASE WHEN DAYNAME(m.start_date) IS NULL THEN 1 ELSE 0 END) AS IsStartDateApprox
                  FROM episode e
                  INNER JOIN medication m ON e.patient_id = m.patient_id
                  left join ophdrprescription_item opi on m.prescription_item_id = opi.id
                  WHERE 1=1 $dateWhere
                  ) UNION (
                    select  episode.id AS EpisodeId ,
                    (SELECT CASE WHEN route_option_id = 1 THEN 'L' WHEN route_option_id = 2 THEN 'R' WHEN route_option_id = 3 THEN 'B'  ELSE 'N' END) AS Eye,
                    drug.`name` as DrugId,
                
                    IFNULL(
                    (
                          SELECT nod_id FROM tmp_episode_drug_route
                          WHERE 
                          (opi.route_id = tmp_episode_drug_route.oe_route_id AND tmp_episode_drug_route.`oe_option_id` = opi.route_option_id) OR
                          (opi.route_id = tmp_episode_drug_route.oe_route_id AND tmp_episode_drug_route.`oe_option_id` IS NULL) )
                          , ""
                      ) AS DrugRouteId,
                    DATE(`event`.event_date) as StartDate,
                    CASE WHEN LOCATE('day', drug_duration.name) > 0 THEN
                            DATE_FORMAT(DATE_ADD(event.event_date, INTERVAL SUBSTR(drug_duration.name, 1, LOCATE('day', drug_duration.name)-1) DAY), '%Y-%m-%d')
                         WHEN LOCATE('month', drug_duration.name) > 0 THEN
                            DATE_FORMAT(DATE_ADD(event.event_date, INTERVAL SUBSTR(drug_duration.name, 1, LOCATE('month', drug_duration.name)-1) MONTH), '%Y-%m-%d')
                         WHEN LOCATE('week', drug_duration.name) > 0 THEN
                            DATE_FORMAT(DATE_ADD(event.event_date, INTERVAL SUBSTR(drug_duration.name, 1, LOCATE('week', drug_duration.name)-1) WEEK), '%Y-%m-%d')
                         ELSE ''
                        END
                    AS StopDate,
                    1 AS IsAddedByPrescription,
                    continue_by_gp AS IsContinueIndefinitely,
                    0 AS IsStartDateApprox
                    from ophdrprescription_item as opi
                    join `et_ophdrprescription_details` on opi.prescription_id = `et_ophdrprescription_details`.id
                    join `event` on et_ophdrprescription_details.event_id = `event`.id
                    join episode on `event`.episode_id = episode.id
                    join drug on opi.drug_id = drug.id
                    join drug_duration on opi.duration_id = drug_duration.id
                    left join medication on medication.prescription_item_id = opi.id
                    where medication.id is null
                     $dateWhereScript
                  )
EOL;

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Eye', 'DrugId', 'DrugRouteId', 'StartDate', 'StopDate', 'IsAddedByPrescription', 'IsContinueIndefinitely', 'IsStartDateApprox'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeDrug', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodeBiometry()
    {

        $dateWhere = $this->getDateWhere('ev');

        $query = "(
                 SELECT
                    ev.`episode_id` AS EpisodeId,
                    'L' AS Eye,
                    axial_length_left AS AxialLength,
                    '' AS BiometryAScanId,
                    (SELECT CASE
                            WHEN ophinbiometry_imported_events.`device_model` = 'IOLmaster 500'  THEN 1
                            WHEN ophinbiometry_imported_events.`device_model` = 'Haag-Streit LensStar' THEN 2
                            WHEN ophinbiometry_imported_events.`device_model` = 'Other' THEN 9
                     END) AS BiometryKeratometerId,
                    ( SELECT `code` FROM tmp_biometry_formula WHERE tmp_biometry_formula.`desc` = ophinbiometry_calculation_formula.name COLLATE utf8_general_ci) AS BiometryFormulaId,
                    k1_left AS K1PreOperative,
                    k2_left AS K2PreOperative,
                    axis_k1_left AS AxisK1,
                    ms.k2_axis_left AS AxisK2,
                    ms.acd_left AS ACDepth,
                    ms.snr_left AS SNR
                FROM episode ep
                JOIN `event` ev ON ep.id =  ev.`episode_id`
                JOIN event_type et ON ev.`event_type_id` = et.`id`
                JOIN et_ophinbiometry_measurement ms ON ev.id = ms.event_id
                JOIN `event` AS opnote ON ep.id = opnote.`episode_id`
                        AND opnote.event_type_id = 4 AND opnote.created_date < ev.created_date
                LEFT JOIN ophinbiometry_imported_events ON ev.id = ophinbiometry_imported_events.`event_id`
                LEFT JOIN et_ophinbiometry_selection ON ev.id = et_ophinbiometry_selection.`event_id` AND et_ophinbiometry_selection.eye_id = 1 OR et_ophinbiometry_selection.eye_id = 3
                LEFT JOIN ophinbiometry_calculation_formula ON et_ophinbiometry_selection.`formula_id_left` = ophinbiometry_calculation_formula.id
                WHERE et.id = 37
                AND ms.deleted = 0
                AND ev.deleted = 0
                ".$dateWhere."
             )
        UNION
        (
                SELECT
                    ev.`episode_id` AS EpisodeId,
                    'R' AS Eye,
                    axial_length_right AS AxialLength,
                    '' AS BiometryAScanId,
                    (SELECT CASE
                            WHEN ophinbiometry_imported_events.`device_model` = 'IOLmaster 500'  THEN 1
                            WHEN ophinbiometry_imported_events.`device_model` = 'Haag-Streit LensStar' THEN 2
                            WHEN ophinbiometry_imported_events.`device_model` = 'Other' THEN 9
                     END) AS BiometryKeratometerId,
                    ( SELECT `code` FROM tmp_biometry_formula WHERE tmp_biometry_formula.`desc` = ophinbiometry_calculation_formula.name COLLATE utf8_general_ci) AS BiometryFormulaId,
                    k1_right AS K1PreOperative,
                    k2_right AS K2PreOperative,
                    axis_k1_right AS AxisK1,
                    ms.k2_axis_right AS AxisK2,
                    ms.acd_right AS ACDepth,
                    ms.snr_right AS SNR
            FROM episode ep
            JOIN `event` ev ON ep.id =  ev.`episode_id`
            JOIN event_type et ON ev.`event_type_id` = et.`id`
            JOIN et_ophinbiometry_measurement ms ON ev.id = ms.event_id
            JOIN `event` AS opnote ON ep.id = opnote.`episode_id`
                    AND opnote.event_type_id = 4 AND opnote.created_date < ev.created_date
            LEFT JOIN ophinbiometry_imported_events ON ev.id = ophinbiometry_imported_events.`event_id`
            LEFT JOIN et_ophinbiometry_selection ON ev.id = et_ophinbiometry_selection.`event_id` AND et_ophinbiometry_selection.eye_id = 2 OR et_ophinbiometry_selection.eye_id = 3
            LEFT JOIN ophinbiometry_calculation_formula ON et_ophinbiometry_selection.`formula_id_left` = ophinbiometry_calculation_formula.id
            WHERE et.id = 37
            AND ms.deleted = 0
            AND ev.deleted = 0
            ".$dateWhere."
         )";

        $dataQuery = array(
            'query' => $query,
            'header' => array(
                'EpisodeId',
                'Eye',
                'AxialLength',
                'BiometryAScanId',
                'BiometryKeratometerId',
                'BiometryFormulaId',
                'K1PreOperative',
                'K2PreOperative',
                'AxisK1',
                'AxisK2',
                'ACDepth',
                'SNR'
            ),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeBiometry', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');

    }

    private function getEpisodeIOP()
    {
        $dateWhere = $this->getDateWhere('etoi');

        $query = " (    
                        SELECT e.id AS EpisodeId,
                        'L' AS Eye,
                        '' AS `Type`,
                        9 AS GlaucomaMedicationStatusId,
                        (oipvr.value + 0.0) AS Value
                        FROM episode e
                        JOIN `event` ev ON ev.episode_id = e.id
                        JOIN event_type et ON et.id = ev.event_type_id
                        JOIN et_ophciexamination_intraocularpressure etoi ON etoi.event_id = ev.id
                        JOIN ophciexamination_intraocularpressure_value oipv ON oipv.element_id = etoi.id
                        JOIN ophciexamination_intraocularpressure_reading oipvr ON oipv.`reading_id` = oipvr.id
                        WHERE et.name = 'Examination' $dateWhere
                        AND (oipv.eye_id = 1 OR oipv.eye_id = 3)
                        GROUP BY e.id 
                    )
                    UNION
                    (
                        SELECT e.id AS EpisodeId,
                        'R' AS Eye,
                        '' AS `Type`,
                        9 AS GlaucomaMedicationStatusId,
                        (oipvr.value + 0.0) AS Value
                        FROM episode e
                        JOIN `event` ev ON ev.episode_id = e.id
                        JOIN event_type et ON et.id = ev.event_type_id
                        JOIN et_ophciexamination_intraocularpressure etoi ON etoi.event_id = ev.id
                        JOIN ophciexamination_intraocularpressure_value oipv ON oipv.element_id = etoi.id
                        JOIN ophciexamination_intraocularpressure_reading oipvr ON oipv.`reading_id` = oipvr.id
                        WHERE et.name = 'Examination' $dateWhere 
                        AND (oipv.eye_id = 2 OR oipv.eye_id = 3)
                        GROUP BY e.id
                    )
                        ";
        

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Eye', 'Type', 'GlaucomaMedicationStatusId', 'Value'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeIOP', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    protected function array2Csv(array $data, $header = null, $dataFormatter = null)
    {

        ob_start();
        $df = fopen("php://output", 'w');

        if (count($data) !== 0) {
            fputcsv($df, array_keys(reset($data)));
            foreach ($data as $row) {
                if (method_exists($this, $dataFormatter)) {
                    $row = $this->$dataFormatter($row);
                }
                fputcsv($df, $row);
            }
        } else if ($header) {
            fputcsv($df, $header);
        }

        fclose($df);
        return ob_get_clean();
    }

    private function getDateWhere($tablename)
    {
        if ($this->startDate != "") {
            $dateWhereStart = $tablename . ".last_modified_date >= '" . $this->startDate . "'";
        }
        if ($this->endDate != "") {
            $dateWhereEnd = $tablename . ".last_modified_date <= '" . $this->endDate . "'";
        }

        $dateWhere = "";
        if (isset($dateWhereStart) && isset($dateWhereEnd)) {
            $dateWhere = "AND " . $dateWhereStart . " AND " . $dateWhereEnd;
        } else if (isset($dateWhereStart)) {
            $dateWhere = "AND " . $dateWhereStart;
        } else if (isset($dateWhereEnd)) {
            $dateWhere = "AND " . $dateWhereEnd;
        }

        return $dateWhere;
    }

    private function getEpisodePostOpComplication()
    {

        $dateWhere = $this->getDateWhere('et_ophciexamination_postop_complications');

        $query = "(
                    SELECT
                    episode.id AS EpisodeId, 
                    ophciexamination_postop_et_complications.`operation_note_id` AS OperationId, 
                    'L' AS Eye,
                    ophciexamination_postop_complications.`code` AS ComplicationTypeId
                    FROM episode
                    JOIN `event` ON episode.id = `event`.`episode_id`
                    JOIN et_ophciexamination_postop_complications ON `event`.id = et_ophciexamination_postop_complications.`event_id`
                    JOIN ophciexamination_postop_et_complications ON et_ophciexamination_postop_complications.id = ophciexamination_postop_et_complications.`element_id`
                    JOIN ophciexamination_postop_complications ON ophciexamination_postop_et_complications.`complication_id` = ophciexamination_postop_complications.id 
                    WHERE 1=1 " . $dateWhere . "
                    AND ( ophciexamination_postop_et_complications.`eye_id` = 1 OR ophciexamination_postop_et_complications.`eye_id` = 3)
                )
                UNION
                (
                    SELECT
                    episode.id AS EpisodeId, 
                    ophciexamination_postop_et_complications.`operation_note_id` AS OperationId, 
                    (SELECT CASE WHEN ophciexamination_postop_et_complications.`eye_id` = 1 THEN 'L' WHEN ophciexamination_postop_et_complications.`eye_id` = 2 THEN 'R' END ) AS Eye,
                    ophciexamination_postop_complications.`code` AS ComplicationTypeId
                    FROM episode
                    JOIN `event` ON episode.id = `event`.`episode_id`
                    JOIN et_ophciexamination_postop_complications ON `event`.id = et_ophciexamination_postop_complications.`event_id`
                    JOIN ophciexamination_postop_et_complications ON et_ophciexamination_postop_complications.id = ophciexamination_postop_et_complications.`element_id`
                    JOIN ophciexamination_postop_complications ON ophciexamination_postop_et_complications.`complication_id` = ophciexamination_postop_complications.id 
                    WHERE 1=1 " . $dateWhere . "
                    AND ( ophciexamination_postop_et_complications.`eye_id` = 2 OR ophciexamination_postop_et_complications.`eye_id` = 3)

                )"
                ;

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'OperationId', 'Eye', 'ComplicationTypeId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodePostOpComplication', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodeOperationCoPathology()
    {

        $query = "(SELECT
                        op_event.id AS OperationId,
                        (SELECT
                                CASE
                                        WHEN (proc_list.eye_id = 3) THEN 'B'
                                        WHEN (proc_list.eye_id = 2) THEN 'R'
                                        WHEN (proc_list.eye_id = 1) THEN 'L'
                                    END
                            ) AS Eye,
                        IF(element_type.`name` = 'Trabeculectomy', 25,23)  AS CoPathologyId
                    FROM
                        `event` AS op_event
                            JOIN
                        `episode` ON op_event.episode_id = episode.id
                            JOIN
                        `event` AS previous_op_event ON previous_op_event.episode_id = episode.id
                            AND previous_op_event.event_type_id = (SELECT id FROM event_type WHERE `name` = 'Operation Note')
                            AND previous_op_event.created_date <= op_event.created_date
                            JOIN
                        `et_ophtroperationnote_procedurelist` AS proc_list ON proc_list.event_id = previous_op_event.id
                            JOIN
                        `ophtroperationnote_procedurelist_procedure_assignment` AS proc_list_asgn ON proc_list_asgn.procedurelist_id = proc_list.id
                            JOIN
                        proc ON proc_list_asgn.proc_id = proc.id
                            JOIN
                        ophtroperationnote_procedure_element ON ophtroperationnote_procedure_element.procedure_id = proc.id
                            JOIN
                        element_type ON ophtroperationnote_procedure_element.element_type_id = element_type.id
                    WHERE
                        element_type.`name` in ('Vitrectomy', 'Trabeculectomy')
                        AND op_event.event_type_id = (SELECT id FROM event_type WHERE `name` = 'Operation Note') " . $this->getDateWhere('op_event') . ")
                    UNION
                    (SELECT
                    op_event.id AS OperationId,
                    (SELECT
                            CASE
                                    WHEN (proc_list.eye_id = 3) THEN 'B'
                                    WHEN (proc_list.eye_id = 2) THEN 'R'
                                    WHEN (proc_list.eye_id = 1) THEN 'L'
                                END
                        ) AS Eye,
                    21 AS CoPathologyId
                    FROM
                        `event` AS op_event
                            JOIN
                        `episode` ON op_event.episode_id = episode.id
                            JOIN
                        `event` AS previous_op_event ON previous_op_event.episode_id = episode.id
                            AND previous_op_event.event_type_id = (SELECT id FROM event_type WHERE `name` = 'Operation Note')
                            AND previous_op_event.created_date <= op_event.created_date
                            JOIN `et_ophtroperationnote_procedurelist` AS proc_list ON proc_list.event_id = previous_op_event.id
                            JOIN `ophtroperationnote_procedurelist_procedure_assignment` AS proc_list_asgn ON proc_list_asgn.procedurelist_id = proc_list.id
                            JOIN proc ON proc_list_asgn.proc_id = proc.id
                            JOIN procedure_benefit ON procedure_benefit.proc_id = proc.id
                            JOIN benefit ON procedure_benefit.benefit_id = benefit.id
                    WHERE
                        benefit.`name` = 'to prevent retinal detachment'
                        AND op_event.event_type_id = (SELECT id FROM event_type WHERE `name` = 'Operation Note') " . $this->getDateWhere('op_event') . " )
                    UNION
                    (SELECT op_event.id AS OperationId,
						(SELECT CASE
							WHEN (left_cortical_id = 4 OR left_nuclear_id = 4) AND (right_cortical_id = 4 OR right_nuclear_id = 4) THEN 'B'
							WHEN (left_cortical_id = 4 OR left_nuclear_id = 4) THEN 'L'
							WHEN (right_cortical_id = 4 OR right_nuclear_id = 4) THEN 'R'
							END
						) AS Eye,
                    14 AS CoPathologyId
                    From et_ophciexamination_anteriorsegment
                    JOIN `event` AS exam_event on et_ophciexamination_anteriorsegment.event_id = exam_event.id
                    JOIN `episode` ON exam_event.episode_id = episode.id
                    JOIN `event` AS op_event
                    ON episode.id = op_event.episode_id
                    AND op_event.event_type_id = (select id from event_type where `name` = 'Operation Note')
                    AND op_event.created_date >= exam_event.created_date
                    WHERE 1=1 " . $this->getDateWhere('et_ophciexamination_anteriorsegment') . "
					HAVING Eye IS NOT NULL)
                    UNION
                    (SELECT
                        event.id AS OperationId,
                        (SELECT CASE
                            WHEN secondary_diagnosis.eye_id = 1 THEN 'L'
                            WHEN secondary_diagnosis.eye_id = 2 THEN 'R'
                            WHEN secondary_diagnosis.eye_id = 3 THEN 'B'
                            END
                        ) AS Eye,
                        tmp_pathology_type.nodcode as CoPathologyId
                    FROM `event`
                    JOIN `episode` ON `event`.episode_id = episode.id
                    JOIN secondary_diagnosis ON episode.`patient_id` = secondary_diagnosis.`patient_id`
                    JOIN `disorder` ON  secondary_diagnosis.`disorder_id` = `disorder`.id
                    JOIN tmp_pathology_type on LOWER(disorder.term) = LOWER(tmp_pathology_type.term)
                    WHERE event_type_id = (SELECT id from event_type where `name` = 'Operation Note') " . $this->getDateWhere('event') . ")";

        $dataQuery = array(
            'query' => $query,
            'header' => array('OperationId', 'Eye', 'CoPathologyId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeOperationCoPathology', null, 'OperationId');

        //return $this->getIdArray($data, 'OperationId');
    }

    private function getEpisodeTreatmentCataract()
    {

        $query = "
                    select pa.id AS TreatmentId,
					IFNULL((select
						IF(eye.`name` = 'First eye', 1, 0)
						from ophciexamination_cataractsurgicalmanagement_eye eye
						join et_ophciexamination_cataractsurgicalmanagement mng on eye.id = mng.eye_id
						join `event` as exam_event on mng.event_id = exam_event.id
						where exam_event.episode_id = episode.id
						and exam_event.event_date <= op_event.event_date
						order by exam_event.event_date desc
						limit 1
					), 1) as IsFirstEye,
					'' as PreparationDrugId,
					if(inc_site.`name` = 'Limbal', 5, IF(inc_site.`name` = 'Scleral', 8, 4)) as IncisionSiteId,
					cataract.length as IncisionLengthId,
					4 as IncisionPlanesId, #unkown
					cataract.meridian as IncisionMeridean,
					if(cataract.pupil_size = 'Small', 1, if(cataract.pupil_size = 'Medium', 2, if(cataract.pupil_size = 'Large', 3, ''))) as PupilSizeId,
					tmp_iol_positions.nodcode as IOLPositionId,
					ophtroperationnote_cataract_iol_type.`name` as IOLModelId,
					cataract.iol_power as IOLPower,
					cataract.predicted_refraction as PredictedPostOperativeRefraction,
					'' as WoundClosureId
					FROM ophtroperationnote_procedurelist_procedure_assignment pa
					JOIN et_ophtroperationnote_procedurelist ON pa.procedurelist_id = et_ophtroperationnote_procedurelist.id
					join `event` as op_event on et_ophtroperationnote_procedurelist.event_id = op_event.id
					join episode on op_event.episode_id = episode.id
					join et_ophtroperationnote_cataract as cataract on op_event.id = cataract.event_id
					join ophtroperationnote_cataract_incision_site as inc_site on cataract.incision_site_id = inc_site.id
					join ophtroperationnote_cataract_iol_position iol_pos on cataract.iol_position_id = iol_pos.id
					join tmp_iol_positions on iol_pos.`name` = tmp_iol_positions.term
					join ophtroperationnote_cataract_iol_type on cataract.iol_type_id = ophtroperationnote_cataract_iol_type.id
					WHERE 1=1 " . $this->getDateWhere('pa');

        $dataQuery = array(
            'query' => $query,
            'header' => array(
                'TreatmentId',
                'IsFirstEye',
                'PreparationDrugId',
                'IncisionSiteId',
                'IncisionLengthId',
                'IncisionPlanesId',
                'IncisionMeridean',
                'PupilSizeId',
                'IolPositionId',
                'IOLModelId',
                'IOLPower',
                'PredictedPostOperativeRefraction',
                'WoundClosureId',
            ),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeTreatmentCataract', null, 'TreatmentId');

        //TODO: need to select episodeIds here!
        //return $this->getIdArray($data, 'TreatmentId');

    }

    private function getEpisodeTreatment()
    {
        $query = "  (SELECT pa.id AS TreatmentId,
                                pl.`event_id` AS OperationId, 
                                'L' AS Eye,
                                proc.snomed_code AS TreatmentTypeId
                    FROM ophtroperationnote_procedurelist_procedure_assignment pa
                    JOIN et_ophtroperationnote_procedurelist pl ON pa.procedurelist_id = pl.id 
					JOIN proc ON pa.`proc_id` = proc.`id`
					WHERE pa.id in (SELECT id FROM tmp_treatment_ids) AND (pl.eye_id=1 OR pl.eye_id=3))
					UNION
					(SELECT pa.id AS TreatmentId,
                                pl.`event_id` AS OperationId,
                                'R' AS Eye,
                                proc.snomed_code AS TreatmentTypeId
                    FROM ophtroperationnote_procedurelist_procedure_assignment pa
                    JOIN et_ophtroperationnote_procedurelist pl ON pa.procedurelist_id = pl.id
					JOIN proc ON pa.`proc_id` = proc.`id`
					WHERE pa.id in (SELECT id FROM tmp_treatment_ids) AND (pl.eye_id=2 OR pl.eye_id=3))";

        $dataQuery = array(
            'query' => $query,
            'header' => array('TreatmentId', 'OperationId', 'Eye', 'TreatmentTypeId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeTreatment', null, 'TreatmentId');
        //TODO: need to select episodeIds here!
        //return $this->getIdArray($data, 'TreatmentId');

    }

    private function getEpisodeOperationAnaesthesia()
    {
        $query = "SELECT event_id AS OperationId,
                        (SELECT `nod_code` FROM tmp_anesthesia_type WHERE at.`name` = `name`) AS AnaesthesiaTypeId,
                        '' as AnaesthesiaNeedle,
                        '' as Sedation,
                        '' as SurgeonId,
                        '' as ComplicationId
                        FROM et_ophtroperationnote_anaesthetic a 
                        JOIN `anaesthetic_type` `at` ON a.`anaesthetic_type_id` = at.`id`
                        WHERE 1=1 ".$this->getDateWhere('a');

        $dataQuery = array(
            'query' => $query,
            'header' => array('OperationId', 'AnaesthesiaTypeId', 'AnaesthesiaNeedle', 'Sedation', 'SurgeonId', 'ComplicationId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeOperationAnaesthesia', null, 'OperationId');

        //return $this->getIdArray($data, 'OperationId');
    }

    private function getEpisodeOperationIndication()
    {
        $query = "(SELECT pl.`event_id` AS OperationId, 'L' AS Eye,
                            (
                                    SELECT IF(	pl.`booking_event_id`,
                                                    d.`disorder_id`, 
                                                    (
                                                            SELECT disorder_id
                                                            FROM episode
                                                            WHERE e.`episode_id` = episode.id
                                                    )
                                            ) 
                            ) AS IndicationId
                            FROM `event` e
                            JOIN event_type evt ON evt.id = e.event_type_id
                            JOIN et_ophtroperationnote_procedurelist pl ON e.id = pl.event_id
                            LEFT JOIN `et_ophtroperationbooking_diagnosis` d ON pl.booking_event_id = d.`event_id`
                            WHERE evt.name = 'Operation Note' " . $this->getDateWhere('e') ."
                            AND (pl.eye_id = 1 OR pl.eye_id = 3))
                    UNION
                            (
                                SELECT pl.`event_id` AS OperationId, 'R' AS Eye,
                                (
                                        SELECT IF(	pl.`booking_event_id`,
                                                        d.`disorder_id`, 
                                                        (
                                                                SELECT disorder_id
                                                                FROM episode
                                                                WHERE e.`episode_id` = episode.id
                                                        )
                                                ) 
                                ) AS IndicationId
                                FROM `event` e
                                JOIN event_type evt ON evt.id = e.event_type_id
                                JOIN et_ophtroperationnote_procedurelist pl ON e.id = pl.event_id
                                LEFT JOIN `et_ophtroperationbooking_diagnosis` d ON pl.booking_event_id = d.`event_id`
                                WHERE evt.name = 'Operation Note' " . $this->getDateWhere('e') ."
                                AND (pl.eye_id = 2 OR pl.eye_id = 3)
                            )
                            ";


        $dataQuery = array(
            'query' => $query,
            'header' => array('OperationId', 'Eye', 'IndicationId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeOperationIndication', null, 'OperationId');

        //return $this->getIdArray($data, 'OperationId');

    }

    private function getEpisodeOperationComplication()
    {

        $query = "SELECT
                        event.id AS OperationId, 
                        (SELECT CASE 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 1 THEN 'L' 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 2 THEN 'R' 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 3 THEN 'B' 
                            END
                        ) AS Eye,
                        IFNULL(
                            (SELECT `code`
                                    FROM tmp_complication_type 
                                    WHERE tmp_complication_type.`name` = ophtroperationnote_cataract_complications.name
                            ),
                            '') AS ComplicationTypeId
                    FROM ophtroperationnote_cataract_complication
                    INNER JOIN `et_ophtroperationnote_cataract` ON `ophtroperationnote_cataract_complication`.cataract_id = et_ophtroperationnote_cataract.id
                    INNER JOIN ophtroperationnote_cataract_complications ON ophtroperationnote_cataract_complication.`complication_id` = ophtroperationnote_cataract_complications.`id`
                    INNER JOIN `event` ON  et_ophtroperationnote_cataract.`event_id` = `event`.id
                    INNER JOIN et_ophtroperationnote_procedurelist ON event.id = et_ophtroperationnote_procedurelist.event_id 
					WHERE 1=1 " . $this->getDateWhere('ophtroperationnote_cataract_complication');

        $dataQuery = array(
            'query' => $query,
            'header' => array('OperationId', 'Eye', 'ComplicationTypeId'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeOperationComplication', null, 'OperationId');

        //return $this->getIdArray($data, 'OperationId');
    }

    private function getEpisodeOperation()
    {

        $query = "SELECT e.id AS OperationId, e.episode_id AS EpisodeId, 
                '' as Description, 
                '' as IsHypertensive,
                e.event_date AS ListedDate,
			s.surgeon_id AS SurgeonId, 
			user.`doctor_grade_id` AS SurgeonGradeId,
                        s.assistant_id as AssistantId,
                        (SELECT doctor_grade_id FROM user WHERE id = s.assistant_id) as AssistantGradeId,
                        s.supervising_surgeon_id as ConsultantId
					FROM `event` e
					JOIN event_type evt ON evt.id = e.event_type_id
					LEFT JOIN et_ophtroperationnote_surgeon s ON s.event_id = e.id
					INNER JOIN `user` ON s.`surgeon_id` = `user`.`id`
					WHERE e.id in (SELECT id FROM tmp_operation_ids)";


        $dataQuery = array(
            'query' => $query,
            'header' => array('OperationId', 'EpisodeId', 'Description', 'IsHypertensive', 'ListedDate', 'SurgeonId', 'SurgeonGradeId', 'AssistantId', 'AssistantGradeId','ConsultantId'),
        );
        return $this->saveCSVfile($dataQuery, 'EpisodeOperation', null, 'OperationId');

        //return $this->getIdArray($data, 'OperationId');
    }

    private function getEpisodeVisualAcuity()
    {

        $query = "(SELECT
                    e.episode_id AS EpisodeId, 
                    'L' AS Eye,
                    v.unit_id AS NotationRecordedId,

                    (   SELECT value 
                        FROM ophciexamination_visual_acuity_unit_value 
                        WHERE base_value = (
                                SELECT MAX(VALUE) 
                                FROM ophciexamination_visualacuity_reading r 
                                JOIN et_ophciexamination_visualacuity va ON va.id = r.element_id 
                                WHERE r.element_id = v.id) AND unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')
                    ) AS BestMeasure,
                    IFNULL(
                        (
                        SELECT value
                        FROM ophciexamination_visual_acuity_unit_value
                        WHERE base_value = (
                            SELECT MAX(r.value)
                            FROM ophciexamination_visualacuity_reading r
                            JOIN ophciexamination_visualacuity_method m ON r.`method_id` = m.`id`
                            WHERE r.element_id = v.id
                            AND m.name = 'Unaided'
                            AND side = 1
                        ) AND unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')),
                        ''
                    ) AS Unaided,
                    IFNULL(
                        (
                        SELECT value
                        FROM ophciexamination_visual_acuity_unit_value
                        WHERE base_value = (
                            SELECT MAX(r.value)
                            FROM ophciexamination_visualacuity_reading r
                            JOIN ophciexamination_visualacuity_method m ON r.`method_id` = m.`id`
                            WHERE r.element_id = v.id
                            AND m.name = 'Pinhole'
                            AND side = 1
                        ) AND unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')),
                        ''
                    ) AS Pinhole, 
                    '' AS BestCorrected
                    FROM `event` e
                    INNER JOIN et_ophciexamination_visualacuity v ON v.event_id = e.id
                    WHERE v.eye_id = 1 OR v.eye_id=3 " . $this->getDateWhere('v').")
                    UNION
                    (SELECT
                    e.episode_id AS EpisodeId,
                    'R' AS Eye,
                    v.unit_id AS NotationRecordedId,

                    (   SELECT value 
                        FROM ophciexamination_visual_acuity_unit_value 
                        WHERE base_value = (
                                SELECT MAX(VALUE) 
                                FROM ophciexamination_visualacuity_reading r 
                                JOIN et_ophciexamination_visualacuity va ON va.id = r.element_id 
                                WHERE r.element_id = v.id) 
                                AND unit_id = (
                                    SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')
                    ) AS BestMeasure,
                    IFNULL(
                      ( SELECT value
                        FROM ophciexamination_visual_acuity_unit_value
                        WHERE base_value = (
                            SELECT MAX(r.value)
                            FROM ophciexamination_visualacuity_reading r
                            JOIN ophciexamination_visualacuity_method m ON r.`method_id` = m.`id`
                            WHERE r.element_id = v.id
                            AND m.name = 'Unaided'
                            AND side = 0
                        ) AND unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter') ),
                        ''
                    ) AS Unaided,
                    IFNULL(
                        (
                        SELECT value
                        FROM ophciexamination_visual_acuity_unit_value
                        WHERE base_value = (
                            SELECT MAX(r.value)
                            FROM ophciexamination_visualacuity_reading r
                            JOIN ophciexamination_visualacuity_method m ON r.`method_id` = m.`id`
                            WHERE r.element_id = v.id
                            AND m.name = 'Pinhole'
                            AND side = 0
                        ) AND unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')),
                        ''
                    ) AS Pinhole,
                    '' AS BestCorrected
                    FROM `event` e
                    INNER JOIN et_ophciexamination_visualacuity v ON v.event_id = e.id
                    WHERE v.eye_id = 2 OR v.eye_id=3 " . $this->getDateWhere('v').")";

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Eye', 'NotationRecordedId', 'BestMeasure', 'Unaided', 'Pinhole', 'BestCorrected'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeVisualAcuity', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodeRefraction()
    {

        $query = "(SELECT 
                    e.episode_id AS EpisodeId, 
                    'L' AS Eye,
                    '' AS RefractionTypeId,
                    r.left_sphere AS Sphere,
                    r.left_cylinder AS Cylinder,
                    r.left_axis AS Axis, 
                    '' AS ReadingAdd

                    FROM `event` e
                    JOIN et_ophciexamination_refraction r ON r.event_id = e.id
                    WHERE r.eye_id = 1 OR r.eye_id = 3 " . $this->getDateWhere('r') . ")
                    UNION
                    (SELECT
                     e.episode_id AS EpisodeId,
                    'R' AS Eye,
                    '' AS RefractionTypeId,
                    r.right_sphere AS Sphere,
                    r.right_cylinder AS Cylinder,
                    r.right_axis AS Axis,
                    '' AS ReadingAdd
                    FROM `event` e
                    JOIN et_ophciexamination_refraction r ON r.event_id = e.id
                    WHERE r.eye_id = 2 OR r.eye_id = 3 " . $this->getDateWhere('r') . ")";

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Sphere', 'Cylinder', 'Axis', 'RefractionTypeId', 'ReadingAdd', 'Eye'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodeRefraction', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }

    private function getEpisodePreOpAssessment()
    {

        $query = "(
                    SELECT e.id AS EpisodeId,
                    'L' AS Eye,
                    (SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 1 THEN 1 ELSE 0 END) AS IsAbleToLieFlat,
                    (SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 4 THEN 1 ELSE 0 END) AS IsInabilityToCooperate
                    FROM episode e
                    LEFT JOIN `event` ev ON ev.episode_id = e.id
                    JOIN et_ophtroperationnote_procedurelist pl ON pl.event_id = ev.id
                    LEFT JOIN patient_risk_assignment pr ON pr.patient_id = e.patient_id
                    WHERE 1=1 ".$this->getDateWhere('pl')."
                    AND (pl.eye_id = 1 OR pl.eye_id = 3)
                    GROUP BY e.id
                )
                UNION 
                (
                    SELECT e.id AS EpisodeId,
                    'R' AS Eye,
                    (SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 1 THEN 1 ELSE 0 END) AS IsAbleToLieFlat,
                    (SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 4 THEN 1 ELSE 0 END) AS IsInabilityToCooperate
                    FROM episode e
                    LEFT JOIN `event` ev ON ev.episode_id = e.id
                    JOIN et_ophtroperationnote_procedurelist pl ON pl.event_id = ev.id
                    LEFT JOIN patient_risk_assignment pr ON pr.patient_id = e.patient_id
                    WHERE 1=1 ".$this->getDateWhere('pl')."
                    AND (pl.eye_id = 2 OR pl.eye_id = 3)
                    GROUP BY e.id
                )
                ";

        $dataQuery = array(
            'query' => $query,
            'header' => array('EpisodeId', 'Eye', 'IsAbleToLieFlat', 'IsInabilityToCooperate'),
        );

        return $this->saveCSVfile($dataQuery, 'EpisodePreOpAssessment', null, 'EpisodeId');

        //return $this->getIdArray($data, 'EpisodeId');
    }
    
    /**
     * Inserts Ids into the temp table
     * 
     * @param string $tableName
     * @param array $idArray
     */
    private function saveIds($tableName, $idArray)
    {
        foreach ($idArray as $id) {
            Yii::app()->db->createCommand("INSERT IGNORE INTO " . $tableName . " (id) VALUES (" . $id . ")")->execute();
        }
    }

    /**
     * Creates zip files from the CSV files
     */
    private function createZipFile()
    {

        $zip = new ZipArchive();

        if ($zip->open($this->exportPath . '/' . $this->zipName, ZIPARCHIVE::CREATE) !== true) {
            exit("cannot open <$name>\n");
        }

        foreach (glob($this->exportPath . "/*.csv") as $filename) {
            $zip->addFile($filename, basename($filename));
        }
        $zip->close();
    }
    
    /**
     * Generates CSV and zip files then sends to the browser 
     */
    public function actionGenerate()
    {

        $this->generateExport();

        $this->createZipFile();

        if (file_exists($this->exportPath . '/' . $this->zipName)) {
            Yii::app()->getRequest()->sendFile($this->zipName, file_get_contents($this->exportPath . '/' . $this->zipName));
        } else {
        }
    }

    /**
     * Generates the CSV files
     */
    public function generateExport()
    {

        $this->createAllTempTables();
        $this->getAllEpisodeId();
        $this->getEpisodeDiagnosis();
        $this->getEpisode();
        $this->getSurgeons();
        $this->getPatientCviStatus();
        $this->getPatients();
        $this->clearAllTempTables();

    }

    /**
     * @param array $row
     * @return array
     */
    protected function prescriptionItemFormat(array $row)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $row['StopDate']) === 0) {
            if (!in_array($row['StopDate'], array('Other', 'Until review'))) {
                $startDate = new DateTime($row['StartDate']);
                $endDate = $startDate->add(DateInterval::createFromDateString($row['StopDate']));
                $row['StopDate'] = $endDate->format('Y-m-d');
            } else {
                $row['StopDate'] = '';
            }
        }

        return $row;
    }
}