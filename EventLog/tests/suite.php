<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package EventLog
 * @subpackage Tests
 */

require_once( "log_delayed_init_test.php");
require_once( "log_test.php");
require_once( "log_message_test.php");
require_once( "mapper/filterset_test.php");
require_once( "context_test.php");
require_once( "writers/writer_file_test.php");
require_once( "writers/writer_unix_file_test.php");
require_once( "writers/writer_syslog_test.php");
require_once( "writers/writer_stack_test.php");

/**
 * @package EventLog
 * @subpackage Tests
 */
class ezcEventLogSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("EventLog");

        $this->addTest( ezcLogDelayedInitTest::suite() );
        $this->addTest( ezcLogFilterSetTest::suite() );
        $this->addTest( ezcLogContextTest::suite() );
        $this->addTest( ezcLogFileWriterTest::suite() );
        $this->addTest( ezcLogUnixFileWriterTest::suite() );
        $this->addTest( ezcLogSyslogWriterTest::suite() );
        $this->addTest( ezcLogStackWriterTest::suite() );
        $this->addTest( ezcLogMessageTest::suite() );
        $this->addTest( ezcLogTest::suite() );
    }

    public static function suite()
    {
        return new ezcEventLogSuite();
    }
}


?>
