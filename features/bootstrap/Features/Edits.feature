@edit
Feature: These tests set up Events, Edit and Delete them.

#  Scenario: Route 1A: Login and create a Anaesthetic Satisfaction Audit Regression: Site 2 Kings, Firm 3 Anderson Glaucoma
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    Then I expand the Glaucoma sidebar
#    And I add a New Event "Satisfaction"
#
#    Then I select an Anaesthetist "no"
#    And I select Satisfaction levels of Pain "2" Nausea "3"
#
#    And I tick the Vomited checkbox
#
#    Then I select Vital Signs of Respiratory Rate "3" Oxygen Saturation "3" Systolic Blood Pressure "4"
#    And I select Vital Signs of Body Temperature "5" and Heart Rate "2" Conscious Level AVPU "2"
#
#    Then I enter Comments "This test is for Site 2 Kings, Firm 3 Anderson Glaucoma"
#
#    And I select the Yes option for Ready to Discharge
#
#    Then I Save the Event
#
#  Scenario: Route 1B: Edit previously created ASA from Route1A
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I edit the Last Event
#
#    Then I select an Anaesthetist "non"
#    And I select Satisfaction levels of Pain "4" Nausea "1"
#
#    And I untick the Vomited checkbox
#
#    Then I select Vital Signs of Respiratory Rate "4" Oxygen Saturation "1" Systolic Blood Pressure "5"
#    And I select Vital Signs of Body Temperature "1" and Heart Rate "5" Conscious Level AVPU "5"
#
#    Then I enter Comments "Route 1 ASA Edit and Save Test"
#
#    And I select the No option for Ready to Discharge
#
#    Then I Save the Event
#
#  Scenario: Route 1C: Delete previously created/edited ASA from Route1A/1B
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I delete the Last Event
#
#  Scenario: Route 2A: Login and create a new Consent Form
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for hospital number "1009465"
#
#    Then I select the Latest Event
#
#    Then I expand the Glaucoma sidebar
#    And I add a New Event "Consent"
#    Then I select Unbooked Procedures
#    Then I select Add Consent Form
#    And I choose Type "1"
#
#    Then I choose Procedure eye of "Both"
#    And I choose an Anaesthetic type of LA
#    And I add a common procedure of "127"
#
#    Then I choose Permissions for images No
#
#    Then I save the Consent Form
#
#  Scenario: Route 2B: Edit previously created Consent from Route2A
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I edit the Last Event
#
#    And I choose Type "2"
#
#    Then I choose Procedure eye of "Right"
#    And I choose an Anaesthetic type of LAC
#
#    Then I choose Permissions for images No
#
#    Then I save the Consent Form
#
#  Scenario: Route 2C: Delete previously created/edited Consent From from Route2A/2B
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I delete the Last Event

#  Scenario: Route 3A: Login and create a Phasing Event
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "1"
#    Then I select a firm of "3"
#
#    Then I search for hospital number "1009465 "
#
#    Then I select the Latest Event
#
#    Then I expand the Glaucoma sidebar
#    And I add a New Event "Phasing"
#
#    Then I choose a right eye Intraocular Pressure Instrument  of "1"
#
#    And I choose right eye Dilation of Yes
#
#    Then I choose a right eye Intraocular Pressure Reading Time of "14:00"
#    Then I choose a right eye Intraocular Pressure Reading of "5"
#    And I add right eye comments of "Right eye comments here"
#
#    Then I choose a left eye Intraocular Pressure Instrument  of "5"
#
#    And I choose left eye Dilation of Yes
#
#    Then I choose a left eye Intraocular Pressure Reading Time of "14:42"
#    Then I choose a left eye Intraocular Pressure Reading of "7"
#    And I add left eye comments of "Left eye comments here"
#
#    Then I Save the Phasing Event
#
#  Scenario: Route 3B: Edit previously edited Phasing from Route 3A
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I edit the Last Event
#
#    Then I choose a right eye Intraocular Pressure Instrument  of "3"
#
#    And I choose right eye Dilation of No
#
#    Then I choose a right eye Intraocular Pressure Reading Time of "21:00"
#    Then I choose a right eye Intraocular Pressure Reading of "14"
#    And I add right eye comments of "Right eye comments here"
#
#    Then I choose a left eye Intraocular Pressure Instrument  of "4"
#
#    And I choose left eye Dilation of Yes
#
#    Then I choose a left eye Intraocular Pressure Reading Time of "04:42"
#    Then I choose a left eye Intraocular Pressure Reading of "12"
#    And I add left eye comments of "Left eye comments here"
#
#    Then I Save the Phasing Event
#
#  Scenario: Route 3C: Delete previously created/edited Phasing From from Route3A/3B
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I delete the Last Event

#  Scenario: Route 4A: Login and fill in a Correspondence
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "1"
#    Then I select a firm of "3"
#
#    Then I search for hospital number "1009465"
#
#    Then I select Create or View Episodes and Events
#
#    Then I expand the Glaucoma sidebar
#    And I add a New Event "Correspondence"
#
#    Then I select Site ID "1"
#    And I select Address Target "Gp1"
#    Then I choose a Macro of "site1"
#
#    And I select Clinic Date "7"
#
#    And I choose CC Target "Patient19434"
#
#    Given I add a New Enclosure of "Test Enclosure"
#
#    Then I Save the Correspondence Draft
#
#  Scenario: Route 4B: Edit previously edited Correspondence from Route 4A
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I edit the Last Event
#
#    Then I select Site ID "2"
#    And I select Address Target "Patient19434"
#
#    And I select Clinic Date "11"
#
#    And I choose CC Target "Gp1"
#
#    Given I add a New Enclosure of "Test Enclosure EDIT"
#
#    Then I Save the Correspondence Draft
#
#  Scenario: Route 4C: Delete previously created/edited Correspondence From from Route 4A/4B
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "admin" and "admin"
#    And I select Site "2"
#    Then I select a firm of "3"
#
#    Then I search for patient name last name "Coffin," and first name "Violet"
#
#    Then I select the Latest Event
#
#    And I delete the Last Event

  Scenario: Route 5A: Login and create a New Intravitreal Event

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "admin" and "admin"
    And I select Site "1"
    Then I select a firm of "1"

    Then I search for hospital number "1009465"

    Then I select the Latest Event

    Then I expand the Cataract sidebar
    And I add a New Event "Intravitreal"

    Then I select Add Right Side
    Then I choose Right Anaesthetic Type of Topical
    Then I choose Right Anaesthetic Type of LA

    Then I choose Right Anaesthetic Delivery of Retrobulbar

    And I choose Right Anaesthetic Agent "5"

    Then I choose Left Anaesthetic Type of Topical
    Then I choose Left Anaesthetic Type of LA

    Then I choose Left Anaesthetic Delivery of Retrobulbar

    And I choose Left Anaesthetic Agent "1"

    Then I choose Right Pre Injection Antiseptic "1"
    Then I choose Right Pre Injection Skin Cleanser "2"
    And I tick the Right Pre Injection IOP Lowering Drops checkbox
    Then I choose Right Pre Injection IOP Lowering Drops "1"
    Then I choose Right Drug "7"
    And I enter "2" number of Right injections
    Then I enter Right batch number "123"

    Then I choose Right Injection Given By "1"
    And I enter a Right Injection time of "09:30"

    Then I choose Left Pre Injection Antiseptic "1"
    Then I choose Left Pre Injection Skin Cleanser "2"
    And I tick the Left Pre Injection IOP Lowering Drops checkbox
    Then I choose Left Pre Injection IOP Lowering Drops "1"
    Then I choose Left Drug "7"
    And I enter "2" number of Left injections
    Then I enter Left batch number "123"

    Then I choose Left Injection Given By "1"
    And I enter a Left Injection time of "09:30"

    Then I choose A Right Lens Status of "1"
    And I choose Right Counting Fingers Checked Yes


    And I choose Right IOP Needs to be Checked No
    Then I choose Right Post Injection Drops "1"

    Then I choose A Left Lens Status of "1"
    And I choose Left Counting Fingers Checked Yes


    And I choose Left IOP Needs to be Checked No
    Then I choose Left Post Injection Drops "1"

    And I select Right Complications "5"
    And I select Left Complications "5"

    Then I Save the Intravitreal injection

  Scenario: Route 5B: Edit previously edited Intravitreal from Route 5A

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "admin" and "admin"
    And I select Site "2"
    Then I select a firm of "3"

    Then I search for patient name last name "Coffin," and first name "Violet"

    Then I select the Latest Event

    And I edit the Last Event

    Then I choose Right Pre Injection Antiseptic "1"
    Then I choose Right Pre Injection Skin Cleanser "1"

    Then I choose Right Drug "7"
    And I enter "2" number of Right injections
    Then I enter Right batch number "567"

    Then I choose Right Injection Given By "1"
    And I enter a Right Injection time of "09:30"

    Then I choose Left Pre Injection Antiseptic "1"
    Then I choose Left Pre Injection Skin Cleanser "2"

    Then I choose Left Drug "2"
    And I enter "1" number of Left injections
    Then I enter Left batch number "789"

    Then I choose Left Injection Given By "3"
    And I enter a Left Injection time of "09:30"

    Then I choose A Right Lens Status of "1"

    And I choose Right Counting Fingers Checked No
    And I choose Right IOP Needs to be Checked Yes

    Then I choose Right Post Injection Drops "1"

    Then I choose A Left Lens Status of "2"

    And I choose Left Counting Fingers Checked Yes
    And I choose Left IOP Needs to be Checked No

    Then I choose Left Post Injection Drops "2"

    And I select Right Complications "2"
    And I select Left Complications "2"

    Then I Save the Intravitreal injection

  Scenario: Route 5C: Delete previously created/edited Correspondence From from Route 5A/5B

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "admin" and "admin"
    And I select Site "2"
    Then I select a firm of "3"

    Then I search for patient name last name "Coffin," and first name "Violet"

    Then I select the Latest Event

    And I delete the Last Event



