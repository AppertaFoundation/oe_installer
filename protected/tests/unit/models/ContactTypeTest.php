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

class ContactTypeTest extends CDbTestCase
{
	public $model;

	public $fixtures = array(
		'contactTypes' => 'ContactType'
	);

	public function dataProvider_Search()
	{
		return array(
			array(array('name' => 'GP', 'id' => 1), 1, array('contacttype1')),
			array(array('letter_template_only'), 8, array('contacttype1', 'contacttype2', 'contacttype3', 'contacttype4', 'contacttype5', 'contacttype6', 'contacttype7', 'contacttype8')),
			array(array('name' => 'foobar'), 0, array()),
		);
	}

	public function setUp()
	{
		parent::setUp();
		$this->model = new ContactType;
	}

	public function testModel()
	{
		$this->assertEquals('ContactType', get_class(ContactType::model()), 'Class name should match model.');
	}

	/**
	 * @covers ContactType::rules
	 * @todo   Implement testRules().
	 */
	public function testRules() {

		$this->assertTrue($this->contacttypes('contacttype1')->validate());
		$this->assertEmpty($this->contacttypes('contacttype1')->errors);
	}

	/**
	 * @covers ContactType::relations
	 * @todo   Implement testRelations().
	 */
	public function testRelations() {
		// Remove the following lines when you implement this test.
		//$this->markTestIncomplete(
		//	'This test has not been implemented yet.'
		//);
	}

	public function testAttributeLabels()
	{
		$expected = array(
			'id' => 'ID',
			'name' => 'Name',
			'letter_template_only' => 'Letter Template Only',
		);

		$this->assertEquals($expected, $this->model->attributeLabels(), 'Attribute labels should match.');
	}

	/**
	 * @dataProvider dataProvider_Search
	 */
	public function testSearch_WithValidTerms_ReturnsExpectedResults($searchTerms, $numResults, $expectedKeys)
	{
		$type = new ContactType;
		$type->setAttributes($searchTerms);
		$results = $type->search();
		$data = $results->getData();

		$expectedResults = array();
		if (!empty($expectedKeys)) {
			foreach ($expectedKeys as $key) {
				$expectedResults[] = $this->contactTypes($key);
			}
		}

		$this->assertEquals($numResults, $results->getItemCount(), 'Number of results should match.');
		$this->assertEquals($expectedResults, $data, 'Actual results should match.');
	}

	public function testNoContactTypes()
	{
		$this->assertEquals(8, count(ContactType::model()->findAll()));
	}
}