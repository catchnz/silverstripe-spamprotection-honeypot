<?php namespace StudioBonito\SilverStripe\SpamProtection\Honeypot\Tests;

use Mockery as m;
use StudioBonito\SilverStripe\SpamProtection\Honeypot\FormField\HoneypotField;

class HoneypotFieldTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @covers StudioBonito\SilverStripe\SpamProtection\Honeypot\FormField\HoneypotField::validate
     */
    public function testInvalidWithCaptcha()
    {
        $form = $this->getForm();
        $validator = $this->getValidator();

        $honeypotField = new HoneypotField('Captcha');
        $honeypotField->setForm($form);
        $honeypotField->setValue('foobar');

        $valid = $honeypotField->validate($validator);

        $this->assertFalse($valid);
    }

    /**
     * @covers StudioBonito\SilverStripe\SpamProtection\Honeypot\FormField\HoneypotField::validate
     */
    public function testInvalidTimeoutWithEmptyCaptcha()
    {
        $form = $this->getForm();
        $validator = $this->getValidator();

        $honeypotField = new HoneypotField('Captcha');
        $honeypotField->setForm($form);
        $honeypotField->setValue(null);

        $valid = $honeypotField->validate($validator);

        $this->assertFalse($valid);
    }

    /**
     * @covers StudioBonito\SilverStripe\SpamProtection\Honeypot\FormField\HoneypotField::validate
     */
    public function testValidWithEmptyCaptcha()
    {
        $form = $this->getForm();
        $validator = $this->getValidator();

        $honeypotField = new HoneypotField('Captcha');
        $honeypotField->setForm($form);
        $honeypotField->setValue(null);
        
        // we have to sleep for > 5s or the timeout will fail
        // or some reason the timestamp on travis is about 10s
        // ahead of time so we add another 10s to what would have 
        // otherwise been a reasonable value
        sleep(16);

        $valid = $honeypotField->validate($validator);

        $this->assertTrue($valid);
    }

    /**
     * @return m\MockInterface
     */
    protected function getForm()
    {
        $request = m::mock('Request');

        $request->shouldReceive('postVar')
            ->andReturn(time() + 10);

        $controller = m::mock('Controller');

        $controller->shouldReceive('getRequest')
            ->andReturn($request);

        $form = m::mock('Form');

        $form->shouldReceive('getController')
            ->andReturn($controller);

        return $form;
    }

    /**
     * @return m\MockInterface
     */
    protected function getValidator()
    {
        $validator = m::mock('RequiredFields')
            ->shouldReceive('validationError')
            ->getMock();

        return $validator;
    }
}
