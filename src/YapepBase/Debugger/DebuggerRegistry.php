<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Debugger
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Debugger;


use YapepBase\Application;
use YapepBase\Debugger\Item\IDebugItem;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Event\IEventHandler;
use YapepBase\Event\Event;
use YapepBase\Session\SessionRegistry;

/**
 * Debugger class, that allows registering any number of DebuggerRenderers to render debugging data.
 *
 * The renderers are triggered by the Event::TYPE_APPFINISH event.
 *
 * @package    YapepBase
 * @subpackage Debugger
 */
class DebuggerRegistry implements IDebugger, IEventHandler {

	/**
	 * The HTTP Url of the stored log files.
	 *
	 * @var string
	 */
	protected $urlToLogFiles;

	/**
	 * This will be replaced with the errorId in the given HTTP Url for log files.
	 *
	 * @var string
	 */
	protected $urlParamName;

	/**
	 * The exact time of the debug console initialized(UNIX timestamp with microseconds).
	 *
	 * @var float
	 */
	protected $startTime;

	/**
	 * Stores whether the render() method has already been called.
	 *
	 * @var bool
	 */
	protected $isRendered = false;

	/**
	 * The event handler registry instance.
	 *
	 * @var EventHandlerRegistry
	 */
	protected $eventHandlerRegistry;

	/**
	 * The session registry for the request.
	 *
	 * @var SessionRegistry
	 */
	protected $sessionRegistry;

	/**
	 * The registered renderers.
	 *
	 * @var array
	 */
	protected $renderers = array();

	/**
	 * The added debug items.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Constructor.
	 *
	 * @param SessionRegistry $sessionRegistry The session registry to use in the request.
	 */
	public function __construct(EventHandlerRegistry $eventHandlerRegistry, SessionRegistry $sessionRegistry) {
		$this->eventHandlerRegistry = $eventHandlerRegistry;
		$this->sessionRegistry = $sessionRegistry;

		$this->startTime = isset($_SERVER['REQUEST_TIME_FLOAT'])
			? (float)$_SERVER['REQUEST_TIME_FLOAT']
			: microtime(true);
	}

	/**
	 * Returns the URL to the stored error log files (if there are any).
	 *
	 * @return string|null
	 */
	public function getUrlToLogFiles() {
		return $this->urlToLogFiles;
	}

	/**
	 * Sets the URL to the stored error log files.
	 *
	 * @param string $urlToLogFiles   The URL to the stored error log files (if there are any)
	 *
	 * @return DebuggerRegistry
	 */
	public function setUrlToLogFiles($urlToLogFiles) {
		$this->urlToLogFiles = $urlToLogFiles;
		return $this;
	}

	/**
	 * Returns the name of the parameter what should be replaced with the errorId.
	 *
	 * @return string|null
	 */
	public function getUrlParamName() {
		return $this->urlParamName;
	}

	/**
	 * Sets the URL parameter name
	 *
	 * @param string $urlParamName    The name of tha parameter what should be replaced with the errorId.
	 *
	 * @return DebuggerRegistry
	 */
	public function setUrlParamName($urlParamName) {
		$this->urlParamName = $urlParamName;
		return $this;
	}

	/**
	 * Returns the time when the request was started as a float timestamp (microtime).
	 *
	 * @return float
	 */
	public function getStartTime() {
		return $this->startTime;
	}

	/**
	 * Adds a new debug item to the debugger.
	 *
	 * @param \YapepBase\Debugger\Item\IDebugItem $item   The debug item.
	 *
	 * @return void
	 */
	public function addItem(IDebugItem $item) {
		$type = $item->getType();
		if (isset($this->items[$type])) {
			$this->items[$type][] = $item;
		} else {
			$this->items[$type] = array($item);
		}
	}

	/**
	 * Returns the full url of the error log file.
	 *
	 * @param string $errorId   The id of the error.
	 *
	 * @return string
	 */
	protected function getErrorLogUrl($errorId) {
		if (empty($this->urlToLogFiles) || empty($this->urlParamName)) {
			return '';
		}

		return str_replace($this->urlParamName, $errorId, $this->urlToLogFiles);
	}

	/**
	 * Adds a new renderer to the registry.
	 *
	 * @param IDebuggerRenderer $renderer   The renderer to add.
	 *
	 * @return void
	 */
	public function addRenderer(IDebuggerRenderer $renderer) {
		$this->renderers[] = $renderer;
	}

	/**
	 * Registers the event handler.
	 *
	 * @return void
	 */
	public function registerEventHandler() {
		// Make sure we are only registered once for the event.
		$this->eventHandlerRegistry->removeEventHandler(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND, $this);
		// Register the event handler.
		$this->eventHandlerRegistry->registerEventHandler(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND, $this);
	}

	/**
	 * Returns true if the registry has renderers.
	 *
	 * @return bool
	 */
	public function hasRenderers() {
		return !empty($this->renderers);
	}

	/**
	 * Handles an event
	 *
	 * @param \YapepBase\Event\Event $event   The dispatched event.
	 *
	 * @return void
	 */
	public function handleEvent(Event $event) {
		switch ($event->getType()) {
			case Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND:
				$this->render();
				break;
		}
	}

	/**
	 * Displays the interface of the Debugger (if it has one).
	 *
	 * @return void
	 */
	protected function render() {
		// We only render if we have renderers and the render() method has not been called yet.
		if ($this->isRendered || empty($this->renderers)) {
			return;
		}
		$this->isRendered = true;

		$endTime = microtime(true);
		$runTime = $endTime - $this->startTime;
		$currentMemory = memory_get_usage(true);
		$peakMemory = memory_get_peak_usage(true);

		/** @var \YapepBase\Debugger\IDebuggerRenderer $renderer */
		foreach ($this->renderers as $renderer) {
			$renderer->render(
				$this->startTime,
				$runTime,
				$currentMemory,
				$peakMemory,
				$this->items,
				$_SERVER,
				$_POST,
				$_GET,
				$_COOKIE,
				$this->sessionRegistry->getAllData()
			);
		}
	}

	/**
	 * Handles the shut down event.
	 *
	 * This method should called in case of shutdown(for example fatal error).
	 *
	 * @return mixed
	 */
	public function handleShutdown() {
		$this->render();
	}
}
