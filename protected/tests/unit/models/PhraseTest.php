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

class PhraseTest extends CDbTestCase
{
	public $fixtures = array(
		'sections' => 'Section',
		'sectionTypes' => 'SectionType',
		'services' => 'Service',
		'specialties' => 'Specialty',
		'serviceSpecialtyAssignment' => 'ServiceSpecialtyAssignment',
		'firms' => 'Firm',
		'eventTypes' => 'EventType',
		'elementTypes' => 'ElementType',
		'possibleElementTypes' => 'PossibleElementType',
		'siteElementTypes' => 'SiteElementType',
		'phrases'	=> 'Phrase',
		'phraseNames'	=> 'PhraseName'
	);


	public function testGet_InvalidParameters_ReturnsFalse()
	{
		$fakeId = 9999;
		$result = Phrase::model()->findByPk($fakeId);
		$this->assertNull($result);
	}

	public function testGet_ValidParameters_ReturnsCorrectData()
	{
		$fakeId = 9999;

		$expected = $this->phrases('phrase1');
		$result = Phrase::model()->findByPk($expected['id']);

		$this->assertEquals(get_class($result), 'Phrase');
		$this->assertEquals($expected, $result);
	}

	public function testCreate()
	{
		$phrase = new Phrase;
		$phrase->setAttributes(array(
			'phrase' => 'Testing phrase',
			'section_id' => $this->sections['section1']['id'],
			'display_order' => 1,
			'phrase_name_id' => $this->phraseNames['phraseName1']['id'],
		));
		$this->assertTrue($phrase->save(true));
	}

	public function testUpdate()
	{
		$expected = 'Testing again';
		$phrase = Phrase::model()->findByPk($this->phrases['phrase1']['id']);
		$phrase->phrase = $expected;
		$phrase->save();
		$phrase = Phrase::model()->findByPk($this->phrases['phrase1']['id']);
		$this->assertEquals($expected, $phrase->phrase);
	}

	public function testDelete()
	{
		$phrase = Phrase::model()->findByPk($this->phrases['phrase1']['id']);
		$phrase->delete();
		$result = Phrase::model()->findByPk($this->phrases['phrase1']['id']);
		$this->assertNull($result);
	}

	public function testRelevantSectionTypesReturnsValidSectionTypeNames()
	{
		$relevantSectionTypes = Phrase::model()->relevantSectionTypes();
		$this->assertTrue(is_array($relevantSectionTypes));
		foreach ($relevantSectionTypes as $relevantSectionType) {
			$sectionType = SectionType::model()->findByAttributes(array('name' => $relevantSectionType));
			$this->assertInstanceOf('SectionType', $sectionType);
		}
	}
	public function testGetOverrideableNames()
	{
		// No names should be overrideable for Phrase - only for PhraseByFirm or PhraseBySpecialty
		$this->assertFalse(Phrase::model()->getOverrideableNames());
	}
}
