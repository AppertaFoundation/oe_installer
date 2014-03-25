@rbac @regression
Feature: Open Eyes Login RBAC user levels

  Scenario: Route 0: Level 0 RBAC access: User with no login rights

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "level0" and "password"
    And I confirm that an Invalid Login error message is displayed

  Scenario: Route 1: Level 1 RBAC access: Login access and only able to view patient demographics

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "level1" and "password"
    And I select Site "1"
    Then I select a firm of "1"
    Then I search for hospital number "1009465"

    Then a check is made to confirm that Patient details information is displayed
    Then a check is made to confirm the user has correct level one access

  Scenario: Route 2: Level 2 RBAC access: Login access and view only, no printing

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "level2" and "password"
    And I select Site "1"
    Then I select a firm of "1"
    Then I search for hospital number "1009465"

    Then a check is made to confirm that Patient details information is displayed

    Then a check is made to confirm the user has correct level two access

    Then I select the Latest Event

    Then additional checks are made for correct level two access

    And a check to see printing has been disabled

  Scenario: Route 3: Level 3 RBAC access: Login access,view only rights and Printing allowed??

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "level3" and "password"
    And I select Site "1"
    Then I select a firm of "1"
    Then I search for hospital number "1009465"

    Then a check is made to confirm that Patient details information is displayed

    Then a check is made to confirm the user has correct level two access

    Then I select the Latest Event

    Then additional checks are made for correct level two access

#    FIXME OE-4153
    #And a check to see printing has been enabled

  Scenario: Route 4(Prep): Create an ASA event to ensure Route 4 latest event is not Prescription

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "admin" and "admin"
    And I select Site "1"
    Then I select a firm of "3"

    Then I search for patient name last name "Coffin," and first name "Violet"

    Then I select the Latest Event

    Then I expand the Glaucoma sidebar
    And I add a New Event "Satisfaction"

    Then I select an Anaesthetist "no"
    And I select Satisfaction levels of Pain "2" Nausea "3"

    And I tick the Vomited checkbox

    Then I select Vital Signs of Respiratory Rate "3" Oxygen Saturation "3" Systolic Blood Pressure "4"
    And I select Vital Signs of Body Temperature "5" and Heart Rate "2" Conscious Level AVPU "2"

    Then I enter Comments "This test is for Site 2 Kings, Firm 3 Anderson Glaucoma"

    And I select the Yes option for Ready to Discharge

    Then I Save the Event and confirm it has been created successfully

  Scenario: Route 4: Level 4 RBAC access: Login access, edit rights, Prescription event blocked

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "level4" and "password"
    And I select Site "1"
    Then I select a firm of "3"
    Then I search for hospital number "1009465"

    Then a check is made to confirm that Patient details information is displayed

    Then I select the Latest Event

    And I edit the Last Event
    #level 4 access check using previously created ASA event form Route 4a

    Then I expand the Glaucoma sidebar

    Then a check is made to confirm the user has correct level four access
    #level 4 Prescription event disabled

#  Scenario: Route 5: Level 5 RBAC access: Login access, edit rights, Prescription event allowed
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "level4" and "password"
#    And I select Site "1"
#    Then I select a firm of "3"
#    Then I search for hospital number "1009465"
#
#    Then a check is made to confirm that Patient details information is displayed
#
#    Then I select the Latest Event
#
#    And I edit the Last Event
#    #level 4 access check
#
#    Then I expand the Glaucoma sidebar
#
#    And I add a New Event "Prescription"
#
#    Then I select a Common Drug "75"
#    And I select a Standard Set of "10"
#
#    Then I enter a Dose of "2" drops
#    And I enter a route of "1"
#
#    And I enter a frequency of "4"
#    Then I enter a duration of "1"
#    Then I enter a eyes option "1"
#
#    And I add Prescription comments of "TEST COMMENTS"
#
#    Then I Save the Prescription Draft and confirm it has been created successfully

#  Scenario: Route 6: Level 6 RBAC access: TBC
#
#    Given I am on the OpenEyes "master" homepage
#    And I enter login credentials "level6" and "password"
#    And I select Site "1"
#    Then I select a firm of "3"
#    Then I search for hospital number "1009465"
#
#    Then a check is made to confirm that Patient details information is displayed
#
#    Then I select the Latest Event


