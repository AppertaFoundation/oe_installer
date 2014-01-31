<?php
/**
	*	OpenEyes
	*
	*	(C)	Moorfields	Eye	Hospital	NHS	Foundation	Trust,	2008-2011
	*	(C)	OpenEyes	Foundation,	2011-2013
	*	This	file	is	part	of	OpenEyes.
	*	OpenEyes	is	free	software:	you	can	redistribute	it	and/or	modify	it	under	the	terms	of	the	GNU	General	Public	License	as	published	by	the	Free	Software	Foundation,	either	version	3	of	the	License,	or	(at	your	option)	any	later	version.
	*	OpenEyes	is	distributed	in	the	hope	that	it	will	be	useful,	but	WITHOUT	ANY	WARRANTY;	without	even	the	implied	warranty	of	MERCHANTABILITY	or	FITNESS	FOR	A	PARTICULAR	PURPOSE.	See	the	GNU	General	Public	License	for	more	details.
	*	You	should	have	received	a	copy	of	the	GNU	General	Public	License	along	with	OpenEyes	in	a	file	titled	COPYING.	If	not,	see	<http://www.gnu.org/licenses/>.
	*
	*	@package	OpenEyes
	*	@link	http://www.openeyes.org.uk
	*	@author	OpenEyes	<info@openeyes.org.uk>
	*	@copyright	Copyright	(c)	2008-2011,	Moorfields	Eye	Hospital	NHS	Foundation	Trust
	*	@copyright	Copyright	(c)	2011-2013,	OpenEyes	Foundation
	*	@license	http://www.gnu.org/licenses/gpl-3.0.html	The	GNU	General	Public	License	V3.0
	*/
class DrugTest extends CDbTestCase	{
	/**
	*	@var Drug
	*/
	protected	$model;
	public $fixtures = array(
		'drugs'	=> 'Drug',
	);

	/**
	* Sets up the fixture, for example, opens a network connection.
	* This method is called before a test is executed.
	*/
	protected	function setUp() {
		parent::setUp();
		$this->model	=	new	Drug;
	}

	/**
	* Tears down the fixture, for example, closes a network connection.
	* This method is called after a test is executed.
	*/
	protected	function tearDown() {
	}

	/**
	*	@covers	Drug::model
	*	@todo Implement testModel().
	*/
	public function testModel()	{
		$this->assertEquals('Drug',	get_class(Drug::model()),	'Class name should match model.');
	}

	/**
	*	@covers	DrugForm::tableName
	*	@todo Implement testTableName().
	*/
	public function testTableName()	{
		$this->assertEquals('drug',	$this->model->tableName());
	}

	/**
	*	@covers	DrugForm::rules
	*	@todo Implement testRules().
	*/
	public function testRules()	{
		$this->assertTrue($this->drugs('drug1')->validate());
		$this->assertEmpty($this->drugs('drug2')->errors);
	}

	/**
	*	@covers	Drug::defaultScope
	*	@todo Implement testDefaultScope().
	*/
	public function testDefaultScope() {
		$result	= $this->model->defaultScope();
		if ($this->model->default_scope) {
			$expected = array('condition' => 't.discontinued = 0');
			$this->assertEquals($expected, $result);
		} else {
			$expected = array();
			$this->assertEquals($expected, $result);
		}
	}

	/**
	*	@covers	Drug::scopes
	*	@todo Implement testScopes().
	*/
	public function testScopes() {
		$result	=	$this->model->scopes();
		$expected	=	array();
		$this->assertEquals($expected,	$result);
	}

	/**
	*	@covers	Drug::discontinued
	*	@todo Implement testDiscontinued().
	*/
	public function testDiscontinued()	{
		$result	= $this->model->discontinued();
		$this->assertEquals('(t.discontinued = 0) OR (t.discontinued = 1)', $result->getDbCriteria()->condition);
	}

	/**
	*	@covers	Drug::relations
	*	@todo Implement testRelations().
	*/
	public function testRelations()	{
		//	Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	*	@covers	Drug::attributeLabels
	*	@todo	Implement	testAttributeLabels().
	*/
	public function testAttributeLabels()	{
		$expected	=	array();

		$this->assertEquals($expected,	$this->model->attributeLabels());
	}

	/**
	* @covers Drug::getLabel
	* @todo Implement testGetLabel().
	*/
	public function testGetLabel() {
		$result = $this->drugs('drug1')->getLabel();

		if ($this->drugs('drug1')->preservative_free) {
			$expected = 'Abidec drops (No Preservative)';
			$this->assertEquals($expected, $result);
		} else {
			$expected = 'Abidec drops';
			$this->assertEquals($expected, $result);
		}
	}

	 /**
	 * @covers Drug::getTallmanLabel
	 * @todo Implement testGetTallmanLabel().
	 */
	 public function testGetTallmanLabel() {
		 $result = $this->drugs('drug1')->getTallmanLabel();

		 if ($this->drugs('drug1')->preservative_free) {
				$expected = 'ABIDEC drops (No Preservative)';
				$this->assertEquals($expected, $result);
		 } else {
				$expected = 'ABIDEC drops';
				$this->assertEquals($expected, $result);
		 }
	 }

	/**
	* @covers Drug::search
	* @todo	Implement testSearch().
	*/
	public function testSearch() {
		$this->model->setAttributes($this->drugs('drug1')->getAttributes());
		$results = $this->model->search();
		$data = $results->getData();

		$expectedKeys = array('drug1');
		$expectedResults = array();
		if (!empty($expectedKeys)) {
			foreach ($expectedKeys as $key) {
				$expectedResults[] = $this->drugs($key);
			}
		}
		$this->assertEquals(1, $results->getItemCount());
		$this->assertEquals($expectedResults, $data);
	}

	/**
	* @covers Drug::listBySubspecialty
	* @todo	Implement testListBySubspecialty().
	*/
	public function testListBySubspecialty() {
		$result = $this->model->listBySubspecialty('1');
		$expected = $this->drugs('drug1')->listBySubspecialty('1');

		$this->assertEquals($result, $expected);
	}
}
