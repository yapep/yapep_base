<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Controller;


/**
 * @codeCoverageIgnore
 */
class RestMockController extends \YapepBase\Controller\RestController {
	function getXml() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::XML, 'UTF-8');
		return array('test1' => 'test');
	}
	function getJson() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::JSON, 'UTF-8');
		return array('test1' => 'test');
	}
	function getUnknown() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::HTML, 'UTF-8');
		return array('test1' => 'test');
	}
	function getString() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::PLAINTEXT, 'UTF-8');
		return 'test';
	}
	function getInvalid() {
		$this->response->setContentType(\YapepBase\Mime\MimeType::JSON, 'UTF-8');
		return new \stdClass();
	}
}