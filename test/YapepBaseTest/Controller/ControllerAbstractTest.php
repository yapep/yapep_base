<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Controller;


use YapepBase\Config;
use YapepBase\Exception\ControllerException;
use YapepBase\Exception\RedirectException;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Application;

use YapepBase\Response\HttpResponse;
use YapepBaseTest\Mock\Controller\MockController;
use YapepBaseTest\Mock\I18n\TranslatorMock;
use YapepBaseTest\Mock\Request\RequestMock;
use YapepBaseTest\Mock\Response\OutputMock;
use YapepBaseTest\Mock\Response\ViewMock;

class ControllerAbstractTest extends \YapepBaseTest\TestAbstract {

	protected $originalDiContainer;

	protected $originalObLevel;

	protected function setUp() {
		parent::setUp();
		$this->originalObLevel = ob_get_level();
		$application               = Application::getInstance();
		$this->originalDiContainer = $application->getDiContainer();
		$diContainer = new SystemContainer();
		$diContainer->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_CONTROLLER,
			'\YapepBaseTest\Mock\Controller');
		$application->setDiContainer($diContainer);
		$application->setI18nTranslator(new TranslatorMock(
			function($string, $params) {
				return json_encode(array(
					'string'   => $string,
					'params'   => $params,
				));
			}
		));
	}

	protected function tearDown() {
		parent::tearDown();
		Config::getInstance()->clear();
		$application = Application::getInstance();
		$application->setDiContainer($this->originalDiContainer);
		$application->clearI18nTranslator();
		while (ob_get_level() > $this->originalObLevel) {
			ob_end_flush();
		}
	}


	public function testRun() {
		$response = null;
		$controller = $this->getController($response);

		$controller->setAction(function() {});
		$this->assertFalse($controller->ran);
		$controller->run('Test');
		$this->assertTrue($controller->ran);

		try {
			$controller->run('nonExistent');
			$this->fail('Running a non-existent action should result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_ACTION_NOT_FOUND, $e->getCode());
		}

		$controller->setAction(function() {
			return 3.14;
		});
		try {
			$controller->run('Test');
			$this->fail('Running an action with an invalid result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_INVALID_ACTION_RESULT, $e->getCode());
		}

		$controller->setAction(function() {
			return 'test string';
		});
		$controller->run('Test');
		$this->assertEquals('test string', $response->getRenderedBody());

		$controller->setAction(function() {
			$view = new ViewMock();
			$view->set('view test string');
			return $view;
		});
		$controller->run('Test');
		$this->assertEquals('view test string', $response->getRenderedBody());

		$controller->setAction(function(MockController $controller) {
			$controller->internalRedirect('Mock', 'RedirectTarget');
		});
		try {
			$controller->run('Test');
			$this->fail('No redirectException is thrown');
		} catch (RedirectException $exception) {
		}
		$this->assertEquals('redirect test', $response->getRenderedBody());

		// Test if the action is run with an invalid case with no strict checking
		$controller->setAction(function(MockController $controller) {
			return 'test';
		});
		$controller->run('test');
		$this->assertTrue($controller->ran,
			'The action should have been run with an invalid case in the action and no strict checking enabled');

		// Test if the action is run with an invalid case with strict checking enabled
		Config::getInstance()->set('system.performStrictControllerActionNameValidation', true);
		$controller->setAction(function(MockController $controller) {
			return 'test';
		});
		try {
			$controller->run('test');
			$this->fail('There should be a ControllerException thrown when running an action with an invalid case'
				. ' and strict checking is enabled');
		} catch (ControllerException $e) {
			$this->assertEquals(ControllerException::ERR_ACTION_NOT_FOUND, $e->getCode(),
				'The exception should have a code for action not found');
			$this->assertContains('Invalid case', $e->getMessage(), 'The error message should contain invalid case');
		}
		$this->assertFalse($controller->ran,
			'The action should not have been run with an invalid case in the action and strict checking enabled');

		// Test that strict checking doesn't affect running a correctly named action
		$controller->setAction(function(MockController $controller) {
			return 'test';
		});
		$controller->run('Test');
		$this->assertTrue($controller->ran,
			'The action should have been run with a valid case in the action and strict checking enabled');
	}

	public function getController(&$response = null) {
		$request      = new RequestMock('http://example.com/');
		$output       = new OutputMock();
		$response     = new HttpResponse($output);
		return new MockController($request, $response);
	}

	public function testSetToView() {
		$controller = $this->getController();
		$controller->setAction(function(MockController $instance) {
			$instance->setToView('test', 'test value');
		});
		$controller->run('Test');
		$this->assertEquals('test value', Application::getInstance()->getDiContainer()->getViewDo()->get('test'));
	}

	public function testTranslation() {
		$controller = $this->getController();
		$expectedResult = array(
			'string' => 'test',
			'params' => array('testParam' => 'testValue'),
		);
		$this->assertSame(
			$expectedResult,
			json_decode($controller->_('test', array('testParam' => 'testValue')), true),
			'The translator method does not return the expected result'
		);
	}
}
