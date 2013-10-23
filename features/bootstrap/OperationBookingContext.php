<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Mink\Driver\Selenium2Driver;
use \SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class OperationBookingContext extends PageObjectContext
{
    public function __construct(array $parameters)
    {

    }

    /**
     * @Then /^I select Diagnosis Eyes of "([^"]*)"$/
     */
    public function iSelectDiagnosisEyesOf($eye)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->diagnosisEyes($eye);
    }


    /**
     * @Given /^I select a Diagnosis of "([^"]*)"$/
     */
    public function iSelectADiagnosisOf($diagnosis)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->diagnosis($diagnosis);
    }

    /**
     * @Then /^I select Operation Eyes of "([^"]*)"$/
     */
    public function iSelectOperationEyesOf($opEyes)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->operationEyes($opEyes);
    }


    /**
     * @Given /^I select a Procedure of "([^"]*)"$/
     */
    public function iSelectAProcedureOf($procedure)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->procedure($procedure);
    }

    /**
     * @Then /^I select Yes to Consultant required$/
     */
    public function iSelectYesToConsultantRequired()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->consultantYes();
    }

    /**
     * @Then /^I select No to Consultant required$/
     */
    public function iSelectNoToConsultantRequired()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->consultantNo();
    }

    /**
     * @Given /^I select a Anaesthetic type "([^"]*)"$/
     */
    public function iSelectAAnaestheticType($type)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->selectAnaesthetic($type);
    }

    /**
     * @Then /^I select Yes to a Post Operative Stay$/
     */
    public function iSelectYesToAPostOperativeStay()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->postOpStayYes();
    }

    /**
     * @Then /^I select No to a Post Operative Stay$/
     */
    public function iSelectNoToAPostOperativeStay()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->postOpStayNo();
    }

    /**
     * @Given /^I select a Operation Site of "([^"]*)"$/
     */
    public function iSelectAOperationSiteOf($site)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->operationSiteID($site);
    }

    /**
     * @Then /^I select a Priority of Routine$/
     */
    public function iSelectAPriorityOfRoutine()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->priorityRoutine();
    }

    /**
     * @Then /^I select a Priority of Urgent$/
     */
    public function iSelectAPriorityOfUrgent()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->priorityUrgent();
    }

    /**
     * @Given /^I select a decision date of "([^"]*)"$/
     */
    public function iSelectADecisionDateOf($date)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->decisionDate($date);
        $operationBooking->getSession()->wait(3000);
}

    /**
     * @Then /^I add comments of "([^"]*)"$/
     */
    public function iAddCommentsOf($comments)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->operationComments($comments);
    }

    /**
     * @Then /^I select Save and Schedule later$/
     */
    public function iSelectSaveAndScheduleLater()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->scheduleLater();
    }

    /**
     * @Then /^I select Save and Schedule now$/
     */
    public function iSelectSaveAndScheduleNow()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->getSession()->wait(3000);
        $operationBooking->scheduleNow();

    }

    /**
     * @Given /^I select an Available theatre slot date$/
     */
    public function iSelectAnAvailableTheatreSlotDate()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->getSession()->wait(3000);
        $operationBooking->availableSlot();
    }

    /**
     * @Then /^I select an Available theatre slot date three weeks in the future$/
     */
    public function iSelectAnAvailableTheatreSlotDateWeeksInTheFuture()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->getSession()->wait(3000);
    }

    /**
     * @Given /^I select an Available session time$/
     */
    public function iSelectAnAvailableSessionTime()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->getSession()->wait(3000);
        $operationBooking->availableSessionTime();
    }

    /**
     * @Then /^I add Session comments of "([^"]*)"$/
     */
    public function iAddSessionCommentsOf($sessionComments)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->sessionComments($sessionComments);
    }

    /**
     * @Given /^I add Operation comments of "([^"]*)"$/
     */
    public function iAddOperationCommentsOf($opComments)
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->sessionOperationComments($opComments);
    }

    /**
     * @Then /^I confirm the operation slot$/
     */
    public function iConfirmTheOperationSlot()
    {
        /**
         * @var OperationBooking $operationBooking
         */
        $operationBooking = $this->getPage('OperationBooking');
        $operationBooking->getSession()->wait(3000);
        $operationBooking->confirmSlot();
    }
}
