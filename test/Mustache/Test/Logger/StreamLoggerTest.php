<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group unit
 */
class Mustache_Test_Logger_StreamLoggerTest extends Yoast\PHPUnitPolyfills\TestCases\TestCase
{
    /**
     * @dataProvider acceptsStreamData
     */
    public function testAcceptsStream($name, $stream)
    {
        $logger = new Mustache_Logger_StreamLogger($stream);
        $logger->log(Mustache_Logger::CRITICAL, 'message');

        $this->assertEquals("CRITICAL: message\n", file_get_contents($name));
    }

    public function acceptsStreamData()
    {
        $one = tempnam(sys_get_temp_dir(), 'mustache-test');
        $two = tempnam(sys_get_temp_dir(), 'mustache-test');

        return [
            [$one, $one],
            [$two, fopen($two, 'a')],
        ];
    }

    public function testPrematurelyClosedStreamThrowsException()
    {
        $stream = tmpfile();
        $logger = new Mustache_Logger_StreamLogger($stream);
        fclose($stream);

        $this->expectException(Mustache_Exception_LogicException::class);
        $logger->log(Mustache_Logger::CRITICAL, 'message');
    }

    /**
     * @dataProvider getLevels
     */
    public function testLoggingThresholds($logLevel, $level, $shouldLog)
    {
        $stream = tmpfile();
        $logger = new Mustache_Logger_StreamLogger($stream, $logLevel);
        $logger->log($level, 'logged');

        rewind($stream);
        $result = fread($stream, 1024);

        if ($shouldLog) {
            $this->assertStringContainsString('logged', $result);
        } else {
            $this->assertEmpty($result);
        }
    }

    public function getLevels()
    {
        // $logLevel, $level, $shouldLog
        return [
            // identities
            [Mustache_Logger::EMERGENCY, Mustache_Logger::EMERGENCY, true],
            [Mustache_Logger::ALERT,     Mustache_Logger::ALERT,     true],
            [Mustache_Logger::CRITICAL,  Mustache_Logger::CRITICAL,  true],
            [Mustache_Logger::ERROR,     Mustache_Logger::ERROR,     true],
            [Mustache_Logger::WARNING,   Mustache_Logger::WARNING,   true],
            [Mustache_Logger::NOTICE,    Mustache_Logger::NOTICE,    true],
            [Mustache_Logger::INFO,      Mustache_Logger::INFO,      true],
            [Mustache_Logger::DEBUG,     Mustache_Logger::DEBUG,     true],

            // one above
            [Mustache_Logger::ALERT,     Mustache_Logger::EMERGENCY, true],
            [Mustache_Logger::CRITICAL,  Mustache_Logger::ALERT,     true],
            [Mustache_Logger::ERROR,     Mustache_Logger::CRITICAL,  true],
            [Mustache_Logger::WARNING,   Mustache_Logger::ERROR,     true],
            [Mustache_Logger::NOTICE,    Mustache_Logger::WARNING,   true],
            [Mustache_Logger::INFO,      Mustache_Logger::NOTICE,    true],
            [Mustache_Logger::DEBUG,     Mustache_Logger::INFO,      true],

            // one below
            [Mustache_Logger::EMERGENCY, Mustache_Logger::ALERT,     false],
            [Mustache_Logger::ALERT,     Mustache_Logger::CRITICAL,  false],
            [Mustache_Logger::CRITICAL,  Mustache_Logger::ERROR,     false],
            [Mustache_Logger::ERROR,     Mustache_Logger::WARNING,   false],
            [Mustache_Logger::WARNING,   Mustache_Logger::NOTICE,    false],
            [Mustache_Logger::NOTICE,    Mustache_Logger::INFO,      false],
            [Mustache_Logger::INFO,      Mustache_Logger::DEBUG,     false],
        ];
    }

    /**
     * @dataProvider getLogMessages
     */
    public function testLogging($level, $message, $context, $expected)
    {
        $stream = tmpfile();
        $logger = new Mustache_Logger_StreamLogger($stream, Mustache_Logger::DEBUG);
        $logger->log($level, $message, $context);

        rewind($stream);
        $result = fread($stream, 1024);

        $this->assertEquals($expected, $result);
    }

    public function getLogMessages()
    {
        // $level, $message, $context, $expected
        return [
            [Mustache_Logger::DEBUG,     'debug message',     [],  "DEBUG: debug message\n"],
            [Mustache_Logger::INFO,      'info message',      [],  "INFO: info message\n"],
            [Mustache_Logger::NOTICE,    'notice message',    [],  "NOTICE: notice message\n"],
            [Mustache_Logger::WARNING,   'warning message',   [],  "WARNING: warning message\n"],
            [Mustache_Logger::ERROR,     'error message',     [],  "ERROR: error message\n"],
            [Mustache_Logger::CRITICAL,  'critical message',  [],  "CRITICAL: critical message\n"],
            [Mustache_Logger::ALERT,     'alert message',     [],  "ALERT: alert message\n"],
            [Mustache_Logger::EMERGENCY, 'emergency message', [],  "EMERGENCY: emergency message\n"],

            // with context
            [
                Mustache_Logger::ERROR,
                'error message',
                ['name' => 'foo', 'number' => 42],
                "ERROR: error message\n",
            ],

            // with interpolation
            [
                Mustache_Logger::ERROR,
                'error {name}-{number}',
                ['name' => 'foo', 'number' => 42],
                "ERROR: error foo-42\n",
            ],

            // with iterpolation false positive
            [
                Mustache_Logger::ERROR,
                'error {nothing}',
                ['name' => 'foo', 'number' => 42],
                "ERROR: error {nothing}\n",
            ],

            // with interpolation injection
            [
                Mustache_Logger::ERROR,
                '{foo}',
                ['foo' => '{bar}', 'bar' => 'FAIL'],
                "ERROR: {bar}\n",
            ],
        ];
    }

    public function testChangeLoggingLevels()
    {
        $stream = tmpfile();
        $logger = new Mustache_Logger_StreamLogger($stream);

        $logger->setLevel(Mustache_Logger::ERROR);
        $this->assertEquals(Mustache_Logger::ERROR, $logger->getLevel());

        $logger->log(Mustache_Logger::WARNING, 'ignore this');

        $logger->setLevel(Mustache_Logger::INFO);
        $this->assertEquals(Mustache_Logger::INFO, $logger->getLevel());

        $logger->log(Mustache_Logger::WARNING, 'log this');

        $logger->setLevel(Mustache_Logger::CRITICAL);
        $this->assertEquals(Mustache_Logger::CRITICAL, $logger->getLevel());

        $logger->log(Mustache_Logger::ERROR, 'ignore this');

        rewind($stream);
        $result = fread($stream, 1024);

        $this->assertEquals("WARNING: log this\n", $result);
    }

    public function testThrowsInvalidArgumentExceptionWhenSettingUnknownLevels()
    {
        $logger = new Mustache_Logger_StreamLogger(tmpfile());
        $this->expectException(Mustache_Exception_InvalidArgumentException::class);
        $logger->setLevel('bacon');
    }

    public function testThrowsInvalidArgumentExceptionWhenLoggingUnknownLevels()
    {
        $logger = new Mustache_Logger_StreamLogger(tmpfile());
        $this->expectException(Mustache_Exception_InvalidArgumentException::class);
        $logger->log('bacon', 'CODE BACON ERROR!');
    }
}
