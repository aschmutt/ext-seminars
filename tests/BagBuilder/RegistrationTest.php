<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_seminars_BagBuilder_Registration class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BagBuilder_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BagBuilder_Registration
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_BagBuilder_Registration();
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBagBuilderBuildsARegistrationBag() {
		$this->assertTrue(
			$this->fixture->build() instanceof tx_seminars_Bag_Registration
		);
	}

	public function testBuildReturnsBagWhichIsSortedAscendingByCrDate() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Title 2', 'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + ONE_DAY))
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Title 1', 'crdate' => $GLOBALS['SIM_EXEC_TIME'])
		);

		$registrationBag = $this->fixture->build();
		$this->assertEquals(
			2,
			$registrationBag->count()
		);

		$this->assertEquals(
			'Title 1',
			$registrationBag->current()->getTitle()
		);
		$this->assertEquals(
			'Title 2',
			$registrationBag->next()->getTitle()
		);

		$registrationBag->__destruct();
	}

	public function testBuildWithoutLimitReturnsBagWithAllRegistrations() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'seminar' => $eventUid1)
		);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			2,
			$registrationBag->count()
		);

		$registrationBag->__destruct();
	}


	/////////////////////////////
	// Tests for limitToEvent()
	/////////////////////////////

	public function testLimitToEventWithNegativeEventUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(-1);
	}

	public function testLimitToEventWithZeroEventUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(0);
	}

	public function testLimitToEventWithValidEventUidFindsRegistrationOfEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			'Attendance 1',
			$registrationBag->current()->getTitle()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToEventWithValidEventUidIgnoresRegistrationOfOtherEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'seminar' => $eventUid2)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	////////////////////////////
	// Tests for limitToPaid()
	////////////////////////////

	public function testLimitToPaidFindsPaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isPaid()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToPaidIgnoresUnpaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'datepaid' => 0)
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	//////////////////////////////
	// Tests for limitToUnpaid()
	//////////////////////////////

	public function testLimitToUnpaidFindsUnpaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => 0)
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isPaid()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToUnpaidIgnoresPaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	////////////////////////////////////////
	// Tests for removePaymentLimitation()
	////////////////////////////////////////

	public function testRemovePaymentLimitationRemovesPaidLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => 0)
		);
		$this->fixture->limitToPaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isPaid()
		);

		$registrationBag->__destruct();
	}

	public function testRemovePaymentLimitationRemovesUnpaidLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToUnpaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isPaid()
		);

		$registrationBag->__destruct();
	}


	///////////////////////////////
	// Tests for limitToOnQueue()
	///////////////////////////////

	public function testLimitToOnQueueFindsRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToOnQueueIgnoresRegularRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	///////////////////////////////
	// Tests for limitToRegular()
	///////////////////////////////

	public function testLimitToRegularFindsRegularRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToRegularIgnoresRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	//////////////////////////////////////
	// Tests for removeQueueLimitation()
	//////////////////////////////////////

	public function testRemoveQueueLimitationRemovesOnQueueLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);

		$registrationBag->__destruct();
	}

	public function testRemoveQueueLimitationRemovesRegularLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);

		$registrationBag->__destruct();
	}


	///////////////////////////////////
	// Tests for limitToSeatsAtMost()
	///////////////////////////////////

	public function testLimitToSeatsAtMostWithNegativeVacanciesThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $seats must be >= 0.'
		);

		$this->fixture->limitToSeatsAtMost(-1);
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithEqualSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			2,
			$registrationBag->current()->getSeats()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithLessSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 1)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			1,
			$registrationBag->current()->getSeats()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToSeatsAtMostIgnoresRegistrationWithMoreSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}

	public function testLimitToSeatsAtMostWithZeroSeatsFindsAllRegistrations() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$this->fixture->limitToSeatsAtMost(0);
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->isEmpty()
		);

		$registrationBag->__destruct();
	}


	////////////////////////////////
	// Tests for limitToAttendee()
	////////////////////////////////

	public function testLimitToAttendeeWithNegativeFeUserUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $frontEndUserUid must be >= 0.'
		);

		$this->fixture->limitToAttendee(-1);
	}

	public function testLimitToAttendeeWithPositiveFeUserUidFindsRegistrationsWithAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'user' => $feUserUid)
		);

		$this->fixture->limitToAttendee($feUserUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$registrationUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToAttendeeWithPositiveFeUserUidIgnoresRegistrationsWithoutAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord('tx_seminars_seminars');

		$this->fixture->limitToAttendee($feUserUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToAttendeeWithZeroFeUserUidFindsRegistrationsWithOtherAttendee() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'user' => $feUserUid2)
		);

		$this->fixture->limitToAttendee($feUserUid);
		$this->fixture->limitToAttendee(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$registrationUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////
	// Tests for setOrderByEventColumn()
	//////////////////////////////////////

	public function testSetOrderByEventColumnCanSortAscendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			'tx_seminars_seminars.title ASC'
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$bag->current()->getUid(),
			$registrationUid1
		);
		$this->assertEquals(
			$bag->next()->getUid(),
			$registrationUid2
		);

		$bag->__destruct();
	}

	public function testSetOrderByEventColumnCanSortDescendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			'tx_seminars_seminars.title DESC'
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$bag->current()->getUid(),
			$registrationUid2
		);
		$this->assertEquals(
			$bag->next()->getUid(),
			$registrationUid1
		);

		$bag->__destruct();
	}


	//////////////////////////////////////////
	// Tests concerning limitToExistingUsers
	//////////////////////////////////////////

	public function testLimitToExistingUsersFindsRegistrationWithExistingUser() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('user' => $this->testingFramework->createFrontEndUser())
		);
		$this->fixture->limitToExistingUsers();
		$bag = $this->fixture->build();

		$this->assertFalse(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToExistingUsersDoesNotFindRegistrationWithDeletedUser() {
		$feUserUid = $this->testingFramework->createFrontEndUser();

		$this->testingFramework->changeRecord(
			'fe_users', $feUserUid, array('deleted' => 1)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('user' => $feUserUid)
		);
		$this->fixture->limitToExistingUsers();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}
}
?>