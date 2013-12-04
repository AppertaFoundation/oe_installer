<?php

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

use Behat\YiiExtension\Context\YiiAwareContextInterface;
use Behat\Mink\Driver\Selenium2Driver;
use \SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class FeatureContext extends PageObjectContext implements YiiAwareContextInterface, \Behat\MinkExtension\Context\MinkAwareInterface
{
	private $yii;
	private $screenshots;

	protected $environment = array(
		'master' => 'http://admin:openeyesdevel@master.test.openeyes.org.uk',
		'develop' => 'http://admin:openeyesdevel@develop.test.openeyes.org.uk'
	);

	public function setYiiWebApplication(\CWebApplication $yii)
	{
		$this->yii = $yii;
	}

	public function __construct(array $parameters)
	{
		$this->useContext('LoginContext', new LoginContext($parameters));
		$this->useContext('HomepageContext', new HomepageContext($parameters));
		$this->useContext('WaitingListContext', new WaitingListContext($parameters));
		$this->useContext('AddingNewEventContext', new AddingNewEventContext($parameters));
		$this->useContext('PatientViewContext', new PatientViewContext($parameters));
		$this->useContext('OperationBookingContext', new OperationBookingContext($parameters));
		$this->useContext('AnaestheticAuditContext', new AnaestheticAuditContext($parameters));
		$this->useContext('ExaminationContext', new ExaminationContext($parameters));
		$this->useContext('LaserContext', new LaserContext($parameters));
		$this->useContext('PrescriptionContext', new PrescriptionContext($parameters));
		$this->useContext('PhasingContext', new PhasingContext($parameters));
		$this->useContext('CorrespondenceContext', new CorrespondenceContext($parameters));
		$this->useContext('IntravitrealContext', new IntravitrealContext($parameters));
		$this->useContext('TherapyApplication', new TherapyApplicationContext($parameters));
		$this->useContext('ConsentForm', new ConsentFormContext($parameters));
		$this->screenshots = array();
	}

	/**
	 * @Given /^I am on the OpenEyes "([^"]*)" homepage$/
	 */
	public function iAmOnTheOpeneyesHomepage($environment)
	{
		/**
		 * @var Login $loginPage
		 */
		if (isset($this->environment[$environment])) {
			$this->getPage('HomePage')->open();;

		} else {
			throw new \Exception("Environment $environment doesn't exist");
		}
	}

	/**
	 * @And /^I Select Add a New Episode and Confirm$/
	 */
	public function addNewEpisode()
	{
		/**
		 * @var AddingNewEvent $addNewEvent
		 */
		$addNewEvent = $this->getPage('AddingNewEvent');
		$addNewEvent->addNewEpisode();
	}

	public function setMink(\Behat\Mink\Mink $mink)
	{
		$this->mink = $mink;
	}

	public function setMinkParameters(array $parameters)
	{
		$this->minkParameters = $parameters;
	}

	/**
	 * Take screenshot when step fails.
	 * Works only with Selenium2Driver.
	 * based on https://gist.github.com/t3node/5430470
	 * and https://gist.github.com/michalochman/3175175
	 * implementing the MinkAwareInterface and placing its contexts in $this->mink
	 *
	 * @AfterStep
	 */
	public function takeScreenshotAfterFailedStep($event)
	{
		try{
            $this->stackScreenshots($event);

		    if (4 === $event->getResult()) {
		    	$this->saveScreenshots();
		    }
        }catch(Exception $e){
        }
	}

	private function stackScreenshots($event)
	{
		try {
			$driver = $this->mink->getSession()->getDriver();

			if ($driver instanceof Behat\Mink\Driver\Selenium2Driver) {
				$step = $event->getStep();
				$path = array(
					'date' => date("Ymd-Hi"),
					'feature' => substr($step->getParent()->getFeature()->getTitle(), 0, 255),
					'scenario' => substr($step->getParent()->getTitle(), 0, 255),
					'step' => substr($step->getType() . ' ' . $step->getText(), 0, 255)
				);
				$path = preg_replace('/[^\-\.\w]/', '_', $path);
				$filename = '/tmp/behat/' . implode('/', $path) . '.jpg';

				if (count($this->screenshots) >= 5) {
					$this->screenshots = array_slice($this->screenshots, 1);
				}
				$imgContent = $driver->getScreenshot();
				$this->screenshots[] = array('filename' => $filename, 'screenshotContent' => $imgContent);
			}
		} catch (Exception $e) {
			echo "Feature Context Exception " . get_class($e) . " \n\nFile: " . $e->getFile() . " \n\nMessage: " . $e->getMessage() .
				" \n\nLine: " . $e->getLine() . " \n\nCode: " . $e->getCode() . " \n\nTrace: " . $e->getTraceAsString();
		}
	}

	private function saveScreenshots()
	{
		foreach ($this->screenshots as $screenshot) {
			try{
				if (!@is_dir(dirname($screenshot['filename']))) {
					@mkdir(dirname($screenshot['filename']), 0775, TRUE);
				}
				file_put_contents($screenshot['filename'], $screenshot['screenshotContent']);
			}
			catch(Exception $e){}
		}
		$this->screenshots = array();
	}

	//public function __destruct(){
	//	$this->mink->getSession()->restart();
	//}

	/**
	 * If tests are using Selenium driver, set implicit wait
	 * so that next step is not executed until the xpath for the
	 * requested element becomes valid
	 *
	 * BeforeScenario
	 *
	 * public function setImplicitWait()
	 * {
	 * if ($this->isSelenium2Driver()) {
	 * $webDriver = $this->getSession()->getDriver()->getWebDriverSession();
	 * $webDriver->timeouts()->implicit_wait(array('ms' => 20000));
	 * }
	 * }*/

	/**
	 * clear up screenshot before new scenario is run
	 * @BeforeScenario
	 */

	public function clearScreenshots(){
		$this->screenshots = array();

		/*$driver = $this->mink->getSession()->getDriver();
		if ($driver instanceof Behat\Mink\Driver\Selenium2Driver) {
			$webDriver = $driver->getWebDriverSession();
			$webDriver->timeouts()->implicit_wait(array('ms' => 20000));
		}*/
	}
}
