<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Bag_OrganizerTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Bag_Organizer
     */
    private $subject;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->testingFramework->createRecord('tx_seminars_organizers');

        $this->subject = new \Tx_Seminars_Bag_Organizer('is_dummy_record=1');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    public function testBagCanHaveAtLeastOneElement()
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
