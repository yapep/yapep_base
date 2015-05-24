<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage DependencyInjection
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\DependencyInjection;


use YapepBase\Config;
use YapepBase\DependencyInjection\SystemContainer;

use YapepBaseTest\Mock\Debugger\DebuggerMock;
use YapepBaseTest\Mock\File\ResourceHandlerMock;
use YapepBaseTest\Mock\Request\RequestMock;
use YapepBaseTest\Mock\Response\OutputMock;
use YapepBaseTest\Mock\Response\ResponseMock;
use YapepBaseTest\Mock\Storage\StorageMock;


class SystemContainerTest extends \YapepBaseTest\TestAbstract {

	protected $originalObLevel;

	protected function setUp() {
		parent::setUp();
		// TODO Make this setting global. For this, there should be a base class for all tests [szeber]
		Config::getInstance()->set('system.project.name', 'test');
		$this->originalObLevel = ob_get_level();
	}

	protected function tearDown() {
		parent::tearDown();
		while (ob_get_level() > $this->originalObLevel) {
			ob_end_flush();
		}
	}

	public function testConstructor() {
		$sc = new SystemContainer();
		$this->assertInstanceOf('\YapepBase\ErrorHandler\ErrorHandlerRegistry', $sc->getErrorHandlerRegistry());
		$this->assertInstanceOf('\YapepBase\Log\Message\ErrorMessage', $sc->getErrorLogMessage());
		$this->assertInstanceOf('\YapepBase\Event\EventHandlerRegistry', $sc->getEventHandlerRegistry());
		$this->assertInstanceOf('\YapepBase\Session\SessionRegistry', $sc->getSessionRegistry());
	}

	public function testGetMemcache() {
		if (!class_exists('\Memcache')) {
			$this->markTestSkipped('No memcache support');
		}
		$sc = new SystemContainer();
		$this->assertInstanceOf('\Memcache', $sc->getMemcache());
	}

	public function testGetMemcached() {
		if (!class_exists('\Memcached')) {
			$this->markTestSkipped('No memcached support');
		}
		$sc = new SystemContainer();
		$this->assertInstanceOf('\Memcached', $sc->getMemcached());
	}

	public function testGetController() {
		$sc = new SystemContainer();
		$sc->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_CONTROLLER, array());
		try {
			$request = new RequestMock('', '');
			$response = new ResponseMock();
			$sc->getController('Mock', $request, $response);
			$this->fail('Getting a controller with an empty search array should result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_CONTROLLER_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_CONTROLLER, '\YapepBaseTest\Mock\Controller');
		$this->assertInstanceOf('\YapepBase\Controller\ControllerAbstract', $sc->getController('Mock', $request, $response));
	}

	public function testGetTemplate() {
		$sc = new SystemContainer();
		$sc->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_TEMPLATE, array());
		try {
			$sc->getTemplate('Mock');
			$this->fail('Getting a template with an empty search array should result in a ViewException');
		} catch (\YapepBase\Exception\ViewException $e) {
			$this->assertEquals(\YapepBase\Exception\ViewException::ERR_TEMPLATE_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_TEMPLATE, '\YapepBaseTest\Mock\View');
		$this->assertInstanceOf('\YapepBase\View\TemplateAbstract', $sc->getTemplate('Mock'));
	}

	public function testGetBo() {
		$sc = new SystemContainer();
		try {
			$sc->getBo('Mock');
			$this->fail('Getting a BO with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_BO, '\YapepBaseTest\Mock\BusinessObject');
		$this->assertInstanceOf('\YapepBase\BusinessObject\BoAbstract', $sc->getBo('Mock'));
	}

	public function testGetDao() {
		$sc = new SystemContainer();
		try {
			$sc->getDao('Mock');
			$this->fail('Getting a Validator with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_VALIDATOR, '\YapepBaseTest\Mock\Validator');
		$this->assertInstanceOf('\YapepBase\Validator\ValidatorAbstract', $sc->getValidator('Mock'));
	}

	/**
	 * Tests the getValidator() method.
	 *
	 * @return void
	 */
	public function testGetValidator() {
		$sc = new SystemContainer();
		try {
			$sc->getValidator('Mock');
			$this->fail('Getting a DAO with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_DAO, '\YapepBaseTest\Mock\Dao');
		$this->assertInstanceOf('\YapepBase\Dao\DaoAbstract', $sc->getDao('Mock'));
	}

	public function testMiddlewareStorage() {
		$sc = new SystemContainer();
		try {
			$sc->getMiddlewareStorage();
			$this->fail('Getting a middleware storage without setting one first should result in a DiExceptiom');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_INSTANCE_NOT_SET, $e->getCode());
		}

		$storage = new StorageMock(true, true);
		$sc->setMiddlewareStorage($storage);

		$this->assertSame($storage, $sc->getMiddlewareStorage(),
			'The retrieved middleware storage is not the one that has been set.');
	}

	public function testDefaultErrorController() {
		$sc = new SystemContainer();
		$controller = $sc->getDefaultErrorController(
			new \YapepBase\Request\HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(),
				array(), array()),
			new \YapepBase\Response\HttpResponse(new OutputMock()));

		$this->assertInstanceOf('\YapepBase\Controller\DefaultErrorController', $controller,
			'The retrieved error controller is of invalid type');
	}

	public function testLoggerRegistry() {
		$sc = new SystemContainer();
		$this->assertInstanceOf('\YapepBase\Log\LoggerRegistry', $sc->getLoggerRegistry(),
			'The retrieved logger registry is of the wrong type');
	}

	public function testDebugger() {
		$sc = new SystemContainer();
		$this->assertFalse($sc->getDebugger(), 'The getDebugger method should return FALSE if no debugger is set');

		$debugger = new DebuggerMock();
		$sc->setDebugger($debugger);

		$this->assertSame($debugger, $sc->getDebugger(), 'The retrieved debugger is not the same instance');
	}

	/**
	 * Tests the getFileResourceHandler() method.
	 *
	 * @return void
	 */
	public function testGetFileResourceHandler() {
		$systemContainer = new SystemContainer();
		$systemContainer[SystemContainer::KEY_FILE_RESOURCE_HANDLER] = '\\YapepBaseTest\\Mock\\File\\ResourceHandlerMock';

		$path = 'test';
		$accessType = 2;
		/** @var ResourceHandlerMock $resourceHandler  */
		$resourceHandler = $systemContainer->getFileResourceHandler($path, $accessType);

		$this->assertInstanceOf('\YapepBaseTest\Mock\\File\ResourceHandlerMock', $resourceHandler);
		$this->assertEquals($path, $resourceHandler->path);
		$this->assertEquals($accessType, $resourceHandler->accessType);
	}
}
