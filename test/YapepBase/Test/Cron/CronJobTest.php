<?php

namespace YapepBase\Test\Cron;

/**
 * Test class for CronJob.
 * Generated by PHPUnit on 2012-02-01 at 13:16:41.
 */
class CronJobTest extends \PHPUnit_Framework_TestCase {
	protected function getPidFilePath() {
		$pidpath = getenv('YAPEPBASE_TEST_PIDPATH');
		if (empty ($pidpath)) {
			$pidpath = \dirname(__DIR__) . '/Temp/Cron';
		}
		return $pidpath;
	}

	public function tearDown() {
		unlink($this->getPidFilePath() . '/YapepBase_Test_Mock_Cron_CronJobMock.pid');
	}

	public function testLock() {
		$cronjob = new \YapepBase\Test\Mock\Cron\CronJobMock();
		$cronjob->setPidPath($this->getPidFilePath());
		$cronjob2 = new \YapepBase\Test\Mock\Cron\CronJobMock();
		$cronjob2->setPidPath($this->getPidFilePath());
		$this->assertEquals($this->getPidFilePath(), $cronjob->getPidPath());
		$cronjob->startFakeRun();
		$cronjob2->run();
		$cronjob->stopFakeRun();
		$this->assertEquals(false, $cronjob2->hasWorked);
		$cronjob2->run();
		$this->assertEquals(true, $cronjob2->hasWorked);
		$cronjob->setPidFile('test');
		$this->assertEquals('test', $cronjob->getPidFile());
	}
}