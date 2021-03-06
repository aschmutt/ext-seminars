<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BagBuilder_AbstractTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_BagBuilder_Testing
     */
    private $subject;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /** @var int PID of a dummy system folder */
    private $dummySysFolderPid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Tests_Unit_Fixtures_BagBuilder_Testing();
        $this->subject->setTestMode();

        $this->dummySysFolderPid = $this->testingFramework->createSystemFolder();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    public function testBuilderThrowsExceptionForEmptyTableName()
    {
        $this->setExpectedException(
            \RuntimeException::class,
            'The attribute $this->tableName must not be empty.'
        );

        new \Tx_Seminars_Tests_Unit_Fixtures_BagBuilder_BrokenTesting();
    }

    public function testBuilderBuildsAnObject()
    {
        $bag = $this->subject->build();

        self::assertInternalType(
            'object',
            $bag
        );
    }

    public function testBuilderBuildsABag()
    {
        $bag = $this->subject->build();

        self::assertInstanceOf(\Tx_Seminars_Bag_Abstract::class, $bag);
    }

    public function testBuilderBuildsBagSortedAscendingByUid()
    {
        $eventUid1 = $this->testingFramework->createRecord('tx_seminars_test');
        $eventUid2 = $this->testingFramework->createRecord('tx_seminars_test');

        $testBag = $this->subject->build();
        self::assertEquals(
            2,
            $testBag->count()
        );

        self::assertEquals(
            $eventUid1,
            $testBag->current()->getUid()
        );
        self::assertEquals(
            $eventUid2,
            $testBag->next()->getUid()
        );
    }

    public function testBuilderWithAdditionalTableNameDoesNotProduceSqlError()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');

        $this->subject->build();
    }

    ///////////////////////////////////
    // Tests concerning source pages.
    ///////////////////////////////////

    public function testBuilderInitiallyHasNoSourcePages()
    {
        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasSourcePagesWithOnePage()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid);

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasSourcePagesWithTwoPages()
    {
        $this->subject->setSourcePages(
            $this->dummySysFolderPid . ',' . ($this->dummySysFolderPid + 1)
        );

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasNoSourcePagesWithEvilSql()
    {
        $this->subject->setSourcePages(
            '; DROP TABLE tx_seminars_test;'
        );

        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePages()
    {
        $this->subject->setSourcePages('');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesWithEmptyAfterNonEmptySourcePages()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid);
        $this->subject->setSourcePages('');
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndZeroRecursion()
    {
        $this->subject->setSourcePages(
            ''
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndNonZeroRecursion()
    {
        $this->subject->setSourcePages(
            '',
            1
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromOnePage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsFromTwoPages()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages(
            $this->dummySysFolderPid . ',' . ($this->dummySysFolderPid + 1)
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderIgnoresRecordsOnSubpageWithoutRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testBuilderSelectsRecordsOnSubpageWithRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsOnTwoSubpagesWithRecursion()
    {
        $subPagePid1 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid1]
        );

        $subPagePid2 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid2]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderSelectsRecordsOnSubpageFromTwoParentsWithRecursion()
    {
        $subPagePid1 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid1]
        );

        $parentPid2 = $this->testingFramework->createSystemFolder();
        $subPagePid2 = $this->testingFramework->createSystemFolder($parentPid2);
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid2]
        );

        $this->subject->setSourcePages(
            $this->dummySysFolderPid . ',' . $parentPid2,
            1
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    public function testBuilderIgnoresRecordsOnSubpageWithTooShallowRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $subSubPagePid = $this->testingFramework->createSystemFolder(
            $subPagePid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subSubPagePid]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning hidden/deleted/timed etc. records.
    ////////////////////////////////////////////////////////

    public function testBuilderIgnoresHiddenRecordsByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['hidden' => 1]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testBuilderFindsHiddenRecordsInBackEndMode()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['hidden' => 1]
        );

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testBuilderIgnoresTimedRecordsByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testBuilderFindsTimedRecordsInBackEndMode()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]
        );

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testBuilderIgnoresDeletedRecordsByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['deleted' => 1]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testBuilderIgnoresDeletedRecordsInBackEndMode()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['deleted' => 1]
        );

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testWhereClauseInitiallyIsNotEmpty()
    {
        self::assertNotEquals(
            '',
            $this->subject->getWhereClause()
        );
    }

    public function testWhereClauseCanSelectPids()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid);

        // We're using assertContains here because the PID in the WHERE clause
        // may be prefixed with the table name.
        self::assertContains(
            'pid IN (' . $this->dummySysFolderPid . ')',
            $this->subject->getWhereClause()
        );
    }

    /////////////////////////////////
    // Test concerning limitToTitle
    /////////////////////////////////

    public function testLimitToTitleFindsRecordWithThatTitle()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo']
        );
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testLimitToTitleIgnoresRecordWithOtherTitle()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'bar']
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ///////////////////////////////////////////////////
    // Test concerning the combination of limitations
    ///////////////////////////////////////////////////

    public function testLimitToTitleAndPagesFindsRecordThatMatchesBoth()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo', 'pid' => $this->dummySysFolderPid]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyTheTitle()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo']
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyThePage()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'bar', 'pid' => $this->dummySysFolderPid]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testLimitToTitleStillExcludesHiddenRecords()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo', 'hidden' => 1]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testLimitToTitleStillExcludesDeletedRecords()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo', 'deleted' => 1]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning addAdditionalTableName()
    //////////////////////////////////////////////

    public function testAddAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->addAdditionalTableName('');
    }

    public function testAddAdditionalTableNameWithTableNameAddsAdditionalTableName()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');

        self::assertContains(
            'tx_seminars_seminars',
            $this->subject->getAdditionalTableNames()
        );
    }

    /////////////////////////////////////////////////
    // Tests concerning removeAdditionalTableName()
    /////////////////////////////////////////////////

    public function testRemoveAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->removeAdditionalTableName('');
    }

    public function testRemoveAdditionalTableNameWithNotSetTableNameThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The given additional table name does not exist in the list ' .
            'of additional table names.'
        );

        $this->subject->removeAdditionalTableName('tx_seminars_seminars');
    }

    public function testRemoveAdditionalTableNameWithSetTableNameRemovesAdditionalTableName()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');
        $this->subject->removeAdditionalTableName('tx_seminars_seminars');

        self::assertNotContains(
            'tx_seminars_seminars',
            $this->subject->getAdditionalTableNames()
        );
    }

    //////////////////////////////////
    // Tests concerning setOrderBy()
    //////////////////////////////////

    public function testSetOrderByWithOrderBySetsOrderBy()
    {
        $this->subject->setOrderBy('field ASC');

        self::assertEquals(
            'field ASC',
            $this->subject->getOrderBy()
        );
    }

    public function testSetOrderByWithEmptyStringRemovesOrderBy()
    {
        $this->subject->setOrderBy('');

        self::assertEquals(
            '',
            $this->subject->getOrderBy()
        );
    }

    public function testSetOrderByWithOrderByActuallySortsTheBag()
    {
        $this->subject->setOrderBy('uid DESC');
        $eventUid1 = $this->testingFramework->createRecord('tx_seminars_test');
        $eventUid2 = $this->testingFramework->createRecord('tx_seminars_test');

        $testBag = $this->subject->build();
        self::assertEquals(
            2,
            $testBag->count()
        );

        self::assertEquals(
            $eventUid2,
            $testBag->current()->getUid()
        );
        self::assertEquals(
            $eventUid1,
            $testBag->next()->getUid()
        );
    }

    ////////////////////////////////
    // Tests concerning setLimit()
    ////////////////////////////////

    public function testSetLimitWithNonEmptyLimitSetsLimit()
    {
        $this->subject->setLimit('0, 30');

        self::assertEquals(
            '0, 30',
            $this->subject->getLimit()
        );
    }

    public function testSetLimitWithEmptyStringRemovesLimit()
    {
        $this->subject->setLimit('');

        self::assertEquals(
            '',
            $this->subject->getLimit()
        );
    }

    public function testSetLimitWithLimitActuallyLimitsTheBag()
    {
        $this->testingFramework->createRecord('tx_seminars_test');
        $this->testingFramework->createRecord('tx_seminars_test');
        $this->subject->setLimit('0, 1');
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    ///////////////////////////////////
    // Tests concerning setTestMode()
    ///////////////////////////////////

    public function testSetTestModeAddsTheTableNameBeforeIsDummy()
    {
        self::assertContains(
            'tx_seminars_test.is_dummy_record = 1',
            $this->subject->getWhereClause()
        );
    }
}
