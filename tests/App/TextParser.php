<?php
/**
 * TextParser test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class TextParser extends \Tests\Base
{
	/**
	 * Test record cache.
	 */
	private static $record;
	/**
	 * Test record instance.
	 */
	private static $testInstanceRecord;
	/**
	 * Test clean instance.
	 */
	private static $testInstanceClean;
	/**
	 * Test clean instance with module.
	 */
	private static $testInstanceCleanModule;

	/**
	 * Creating account module record for tests.
	 */
	public static function createLeadRecord()
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Leads');
		$recordModel->set('description', 'autogenerated test lead for \App\TextParser tests');
		$recordModel->save();
		return static::$record = $recordModel;
	}

	/**
	 * Testing instances creation.
	 */
	public function testInstancesCreation()
	{
		static::$testInstanceClean = \App\TextParser::getInstance();
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceClean, 'Expected clean instance without module of \App\TextParser');

		static::$testInstanceCleanModule = \App\TextParser::getInstance('Leads');
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceCleanModule, 'Expected clean instance with module Leads of \App\TextParser');

		$this->assertInstanceOf('\App\TextParser', \App\TextParser::getInstanceById(static::createLeadRecord()->getId(), 'Leads'), 'Expected instance from lead id and module string of \App\TextParser');

		static::$testInstanceRecord = \App\TextParser::getInstanceByModel(static::createLeadRecord());
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceRecord, 'Expected instance from record model of \App\TextParser');
	}

	/**
	 * Testing basic field placeholder replacement.
	 */
	public function testBasicFieldPlaceholderReplacement()
	{
		\App\User::setCurrentUserId(1);
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$testInstanceClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: By default employee last name should be empty');
		$this->assertSame('+  +', static::$testInstanceRecord
			->setContent($text)
			->parse()
			->getContent(), 'Record instance: By default employee last name should be empty');
	}

	/**
	 * Testing basic translate function.
	 */
	public function testTranslate()
	{
		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$testInstanceClean->withoutTranslations(true);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$testInstanceClean->withoutTranslations(false);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$testInstanceRecord->withoutTranslations(true);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$testInstanceRecord->withoutTranslations(false);
	}

	/**
	 * Testing basic source record related functions.
	 */
	public function testBasicSrcRecord()
	{
		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+', static::$testInstanceClean->setContent('+$(sourceRecord : description)$+')->setSourceRecord(static::createLeadRecord()->getId())->parse()->getContent(),
			'Clean instance: Translations should be equal');

		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+',
			static::$testInstanceRecord->setContent('+$(sourceRecord : description)$+')->setSourceRecord(static::createLeadRecord()->getId())->parse()->getContent(),
			'Record instance: Translations should be equal');
	}
}
