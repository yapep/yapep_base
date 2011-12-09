<?php

namespace YapepBase\Syslog;

/**
 * Test class for SyslogConnection.
 * Generated by PHPUnit on 2011-12-06 at 11:33:00.
 */
class SyslogConnectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var SyslogConnection
     */
    protected $object;
    
    protected $logpath;
    protected $sock;
    protected $dgram;
    
    protected function initSyslogServer($logpath, $dgram = false) {
        if (\file_exists($logpath)) {
            \unlink($logpath);
        }
        if ($dgram) {
            $this->sock = \socket_create(AF_UNIX, SOCK_DGRAM, 0);
        } else {
            $this->sock = \socket_create(AF_UNIX, SOCK_STREAM, 0);
        }
        \socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1);
        \socket_bind($this->sock, $logpath);
        if (!$dgram) {
            \socket_listen($this->sock);
        }
        $this->logpath = $logpath;
        $this->dgram = $dgram;
    }
    
    protected function getSyslogMessage() {
        if (!$this->dgram) {
            $client = \socket_accept($this->sock);
            if ($client !== false) {
                $msg = \socket_read($client, 1024);
                \socket_close($client);
                return $msg;
            }
        } else {
            if (($msg = \socket_read($this->sock, 1024))) {
                return $msg;
            }
        }
        return false;
    }
    
    protected function closeSyslogServer() {
        \socket_close($this->sock);        
        if (\file_exists($this->logpath)) {
            \unlink($this->logpath);
        }
    }

    protected function setUp() {
        $this->object = new SyslogConnection;
    }

    public function testPath() {
        $this->object->setPath('/test');
        $this->assertEquals('/test', $this->object->getPath());
    }
    
    public function testIdent() {
        $this->object->setIdent('identtest');
        $this->assertEquals('identtest', $this->object->getIdent());
    }
    
    public function testOptions() {
        $this->object->setOptions(SyslogConnection::LOG_PID);
        $this->assertEquals(SyslogConnection::LOG_PID, $this->object->getOptions());
    }
    
    public function testFacility() {
        $this->object->setFacility(SyslogConnection::LOG_AUTH);
        $this->assertEquals(SyslogConnection::LOG_AUTH, $this->object->getFacility());
        
        try {
            $this->object->setFacility(192);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }
        
        try {
            $this->object->setFacility(-1);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }

        try {
            $this->object->setFacility(7);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }
    }

    public function testLogging() {
        if (!\function_exists('pcntl_fork')) {
            $this->markTestSkipped('Skipping syslog test, pcntl_fork is not available');
            return;
        }
        
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';
        $this->object->setFacility(SyslogConnection::LOG_USER);
        $this->object->setPath($logpath);
        $this->object->setIdent('test');

        $this->initSyslogServer($logpath);
        
        $pid = \pcntl_fork();
        if ($pid < 0) {
            $this->markTestSkipped('Failed to fork, skipping test.');
            return;
        } elseif ($pid == 0) {
            $this->object->log(SyslogConnection::LOG_NOTICE, 'test', 'test', mktime(15, 45, 19, 12, 6, 2011));
            $this->closeSyslogServer();
            exit;
        } else {
            $this->assertEquals('<13>Dec  6 15:45:19 test: test', $this->getSyslogMessage());
            \pcntl_waitpid($pid, $status);
            $this->closeSyslogServer();
        }
    }
    
    public function testHandleError() {
        try {
            $this->object->setPath('/nonexistent');
            $this->object->open();
            $this->fail('Connecting to a non-existent socket should result in a SyslogException');
        } catch (\YapepBase\Syslog\SyslogException $e) { }
    }
    
    public function testDgramSockets() {
        if (!\function_exists('pcntl_fork')) {
            $this->markTestSkipped('Skipping syslog test, pcntl_fork is not available');
            return;
        }
        
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';
        $this->object->setFacility(SyslogConnection::LOG_USER);
        $this->object->setPath($logpath);
        $this->object->setIdent('test');

        $this->initSyslogServer($logpath, true);
        
        $pid = \pcntl_fork();
        if ($pid < 0) {
            $this->markTestSkipped('Failed to fork, skipping test.');
            return;
        } elseif ($pid == 0) {
            $this->object->log(SyslogConnection::LOG_NOTICE, 'test', 'test', mktime(15, 45, 19, 12, 6, 2011));
            $this->closeSyslogServer();
            exit;
        } else {
            $this->assertEquals('<13>Dec  6 15:45:19 test: test', $this->getSyslogMessage());
            \pcntl_waitpid($pid, $status);
            $this->closeSyslogServer();
        }        
    }
}
