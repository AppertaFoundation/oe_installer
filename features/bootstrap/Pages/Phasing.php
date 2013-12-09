<?php

class Phasing extends OpenEyesPage
{
    protected $path = "/site/OphCiPhasing/Default/create?patient_id={parentId}";

    protected $elements = array(
        'phasingLogo' => array('xpath' => "//*[@id='event-content']//*[contains(text(),'Phasing')]"),

        'phasingInstrumentRight' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_right_instrument_id']"),
        'phasingDilationRightYes' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_right_dilated_1']"),
        'phasingDilationRightNo' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_right_dilated_0']"),
        'phasingReadingTimeRight' => array('xpath' => "//*[@id='intraocularpressure_reading_0_measurement_timestamp']"),
        'phasingPressureRight' => array('xpath' => "//input[@id='intraocularpressure_reading_0_value']"),
        'phasingCommentsRight' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_right_comments']"),

        'phasingInstrumentLeft' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_left_instrument_id']"),
        'phasingDilationLeftYes' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_left_dilated_1']"),
        'phasingDilationLeftNo' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_left_dilated_0']"),
        'phasingReadingTimeLeft' => array('xpath' => "//*[@id='intraocularpressure_reading_1_measurement_timestamp']"),
        'phasingPressureLeft' => array('xpath' => "//input[@id='intraocularpressure_reading_1_value']"),
        'phasingCommentsLeft' => array('xpath' => "//*[@id='Element_OphCiPhasing_IntraocularPressure_left_comments']"),
        'savePhasingEvent' => array('xpath' => "//*[@id='et_save']")
    );

    protected function doesPhasingLogoExist()
    {
        return (bool) $this->find('xpath', $this->getElement('phasingLogo')->getXpath());
    }

    public function confirmPhasingLogoExist ()
    {
        if ($this->doesPhasingLogoExist()){
            print "Phasing Text & Logo IS Present";
        }
        elseif (print "Logo MISSING!");
    }

    public function rightInstrument ($rightEye)
    {
        $this->getElement('phasingInstrumentRight')->selectOption($rightEye);
    }

    public function rightDilationYes ()
    {
        $this->getElement('phasingDilationRightYes')->click();
    }

    public function rightDilationNo ()
    {
        $this->getElement('phasingDilationRightNo')->click();
    }

    public function rightPressureTime ($time)
    {
        $this->getElement('phasingReadingTimeRight')->setValue($time);
    }

    public function rightPressure ($rightEye)
    {
        $this->getElement('phasingPressureRight')->setValue($rightEye);
    }

    public function rightComments ($comments)
    {
        $this->getElement('phasingCommentsRight')->setValue($comments);
    }

    public function leftInstrument ($leftEye)
    {
        $this->getElement('phasingInstrumentLeft')->selectOption($leftEye);
    }

    public function leftDilationYes ()
    {
        $this->getElement('phasingDilationLeftYes')->click();
    }

    public function leftDilationNo ()
    {
        $this->getElement('phasingDilationLeftNo')->click();
    }

    public function leftPressureTime ($time)
    {
        $this->getElement('phasingReadingTimeLeft')->setValue($time);
    }

    public function leftPressure ($leftEye)
    {
        $this->getElement('phasingPressureLeft')->setValue($leftEye);
    }

    public function leftComments ($comments)
    {
        $this->getElement('phasingCommentsLeft')->setValue($comments);

    }

    public function savePhasingEvent ()
    {
        $this->getElement('savePhasingEvent')->click();
        $this->getSession()->wait(3000);
    }

}