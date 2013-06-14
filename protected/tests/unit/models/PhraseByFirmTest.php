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

class PhraseByFirmTest extends CDbTestCase
{
	public $fixtures = array(
		'sections' => 'Section',
		'sectionTypes' => 'SectionType',
		'services' => 'Service',
		'specialties' => 'Specialty',
		'serviceSubspecialtyAssignment' => 'ServiceSubspecialtyAssignment',
		'firms' => 'Firm',
		'eventTypes' => 'EventType',
		'elementTypes' => 'ElementType',
		//'possibleElementTypes' => 'PossibleElementType',
		//'siteElementTypes' => 'SiteElementType',
		'phrasesByFirm'	=> 'PhraseByFirm',
		'phraseNames'	=> 'PhraseName'
	);


	public function testGet_InvalidParameters_ReturnsFalse()
	{
		$fakeId = 9999;
		$result = PhraseByFirm::model()->findByPk($fakeId);
		$this->assertNull($result);
	}

	public function testGet_ValidParameters_ReturnsCorrectData()
	{
		$fakeId = 9999;

		$expected = $this->phrasesByFirm('phraseByFirm1');
		$result = PhraseByFirm::model()->findByPk($expected['id']);

		$this->assertEquals(get_class($result), 'PhraseByFirm');
		$this->assertEquals($expected, $result);
	}

	public function testCreate()
	{
		$phrase = new PhraseByFirm;
		$phrase->setAttributes(array(
			'phrase' => 'Testing phrase',
			'section_id' => $this->sections['section1']['id'],
			'firm_id' => $this->firms['firm1']['id'],
			'display_order' => 1,
			'phrase_name_id' => $this->phraseNames['phraseName1']['id'],
		));
		$this->assertTrue($phrase->save(true));
	}

	public function testUpdate()
	{
		$expected = 'Testing again';
		$phrase = PhraseByFirm::model()->findByPk($this->phrasesByFirm['phraseByFirm1']['id']);
		$phrase->phrase = $expected;
		$phrase->save();
		$phrase = PhraseByFirm::model()->findByPk($this->phrasesByFirm['phraseByFirm1']['id']);
		$this->assertEquals($expected, $phrase->phrase);
	}

	public function testDelete()
	{
		$phrase = PhraseByFirm::model()->findByPk($this->phrasesByFirm['phraseByFirm1']['id']);
		$phrase->delete();
		$result = PhraseByFirm::model()->findByPk($this->phrasesByFirm['phraseByFirm1']['id']);
		$this->assertNull($result);
	}

	public function testRelevantSectionTypesReturnsValidSectionTypeNames()
	{
		$relevantSectionTypes = PhraseByFirm::model()->relevantSectionTypes();
		$this->assertTrue(is_array($relevantSectionTypes));
		foreach ($relevantSectionTypes as $relevantSectionType) {
			$sectionType = SectionType::model()->findByAttributes(array('name' => $relevantSectionType));
			$this->assertInstanceOf('SectionType', $sectionType);
		}
	}
	public function testGetOverrideableNames()
	{
		$overrideable = PhraseByFirm::model()->getOverrideableNames($this->sections['section1']['id'], $this->firms['firm1']['id']);
		$this->assertTrue(is_array($overrideable));
		foreach ($overrideable as $o) {
			$this->assertEquals('unnatural cataract', $o->name);
		}	
	}
}
