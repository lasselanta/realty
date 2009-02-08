<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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
 * Unit tests for the tx_realty_pi1_OffererView class in the 'realty'
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_realty_pi1_OffererView_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_realty_pi1_OffererView
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_realty');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_realty_pi1_OffererView(
			array('templateFile' => 'EXT:realty/pi1/tx_realty_pi1.tpl.htm'),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setConfigurationValue(
			'displayedContactInformation', 'company'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	/////////////////////////////
	// Testing the offerer view
	/////////////////////////////

	public function testRenderReturnsEmptyResultForShowUidOfDeletedRecord() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('deleted', 1);

		$this->assertEquals(
			'',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsNonEmptyResultForShowUidOfExistingRecord() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('employer', 'foo');

		$this->assertNotEquals(
			'',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsNoUnreplacedMarkersWhileTheResultIsNonEmpty() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('employer', 'foo');

		$result = $this->fixture->render(array('showUid' => $realtyObject->getUid()));

		$this->assertNotEquals(
			'',
			$result
		);
		$this->assertNotContains(
			'###',
			$result
		);
	}

	public function testRenderReturnsTheRealtyObjectsEmployerForValidRealtyObject() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('employer', 'foo');

		$this->assertContains(
			'foo',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsEmptyResultForValidRealtyObjectWithoutData() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('title', '');

		$this->assertEquals(
			'',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}


	//////////////////////////////////////////////
	// Testing the displayed offerer information
	//////////////////////////////////////////////

	public function testRenderReturnsContactInformationIfEnabledAndInformationIsSetInTheRealtyObject() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('contact_phone', '12345');

		$this->fixture->setConfigurationValue('displayedContactInformation', 'telephone');

		$this->assertContains(
			$this->fixture->translate('label_offerer'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsPhoneNumberIfContactDataIsEnabledAndInformationIsSetInTheRealtyObject() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('contact_phone', '12345');

		$this->fixture->setConfigurationValue('displayedContactInformation', 'telephone');

		$this->assertContains(
			'12345',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsCompanyIfContactDataIsEnabledAndInformationIsSetInTheRealtyObject() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('employer', 'test company');

		$this->assertContains(
			'test company',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsOwnersPhoneNumberIfContactDataIsEnabledAndContactDataMayBeTakenFromOwner() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('telephone' => '123123')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'telephone');

		$this->assertContains(
			'123123',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsOwnersCompanyIfContactDataIsEnabledAndContactDataMayBeTakenFromOwner() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('company' => 'any company')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->assertContains(
			'any company',
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderNotReturnsContactInformationIfOptionIsDisabled() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('contact_phone', '12345');

		$this->fixture->setConfigurationValue('displayedContactInformation', '');

		$this->assertNotContains(
			$this->fixture->translate('label_offerer'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderNotReturnsContactInformationForEnabledOptionAndDeletedOwner() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('company' => 'any company', 'deleted' => 1)
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->assertNotContains(
			$this->fixture->translate('label_offerer'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderNotReturnsContactInformationForEnabledOptionAndOwnerWithoutData() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->assertNotContains(
			$this->fixture->translate('label_offerer'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsLabelForLinkToTheObjectsByOwnerListForEnabledOptionAndOwnerSet() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');
		$this->fixture->setConfigurationValue(
			'objectsByOwnerPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			$this->fixture->translate('label_this_owners_objects'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsLabelOffererIfTheLinkToTheObjectsByOwnerListIsEnabled() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');
		$this->fixture->setConfigurationValue(
			'objectsByOwnerPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			$this->fixture->translate('label_offerer'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsLinkToTheObjectsByOwnerListForEnabledOptionAndOwnerSet() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);
		$objectsByOwnerPid = $this->testingFramework->createFrontEndPage();

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');
		$this->fixture->setConfigurationValue('objectsByOwnerPID', $objectsByOwnerPid);

		$this->assertContains(
			'?id=' . $objectsByOwnerPid,
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsOwnerUidInLinkToTheObjectsByOwnerListForEnabledOptionAndOwnerSet() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');
		$this->fixture->setConfigurationValue(
			'objectsByOwnerPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			'tx_realty_pi1[owner]=' . $ownerUid,
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderNotReturnsLinkToTheObjectsByOwnerListForEnabledOptionAndNoOwnerSet() {
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');
		$this->fixture->setConfigurationValue(
			'objectsByOwnerPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertNotContains(
			$this->fixture->translate('label_this_owners_objects'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderNotReturnsLinkToTheObjectsByOwnerListForDisabledContactInformationAndOwnerAndPidSet() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', '');
		$this->fixture->setConfigurationValue(
			'objectsByOwnerPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertNotContains(
			$this->fixture->translate('label_this_owners_objects'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}

	public function testRenderReturnsNoLinkToTheObjectsByOwnerListForNoObjectsByOwnerPidSetAndOwnerSet() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);
		$realtyObject = tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
			->getNewGhost();
		$realtyObject->setProperty('owner', $ownerUid);
		$realtyObject->setProperty(
			'contact_data_source', REALTY_CONTACT_FROM_OWNER_ACCOUNT
		);

		$this->fixture->setConfigurationValue('displayedContactInformation', 'offerer_label');

		$this->assertNotContains(
			$this->fixture->translate('label_this_owners_objects'),
			$this->fixture->render(array('showUid' => $realtyObject->getUid()))
		);
	}
}
?>