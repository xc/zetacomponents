<?php
/**
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Mail
 * @subpackage Tests
 */

/**
 * @package Mail
 * @subpackage Tests
 */
class ezcMailTransportPop3Test extends ezcTestCase
{
    public function testInvalidServer()
    {
        try
        {
            $pop3 = new ezcMailPop3Transport( "no.such.server.example.com" );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidUsername()
    {
        try
        {
            $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
            $pop3->authenticate( "no_such_user", "ezcomponents" );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidPassword()
    {
        try
        {
            $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
            $pop3->authenticate( "ezcomponents", "no_such_password" );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidCallListMessages()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->disconnect();
        try
        {
            $pop3->listMessages();
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidCallTop()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->disconnect();
        try
        {
            $pop3->top( 1, 1 );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidCallStatus()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->disconnect();
        try
        {
            $pop3->status( $a, $b );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidCallDelete()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->disconnect();
        try
        {
            $pop3->delete( 1000 );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testLoginAuthenticated()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $pop3->authenticate( "ezcomponents", "ezcomponents" );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testInvalidCallListUniqueMessages()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->disconnect();
        try
        {
            $pop3->listUniqueIdentifiers();
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testFetchMail()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $set = $pop3->fetchAll();
        $parser = new ezcMailParser();
        $mail = $parser->parseMail( $set );
        $this->assertEquals( 4, count( $mail ) );
    }

    public function testListMessages()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $list = $pop3->listMessages();
        $this->assertEquals( array( 1 => '1723', 2 => '1694', 3 => '1537', 4 => '64070' ), $list );
    }

    public function testFetchByMessageNr1()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $message = $pop3->fetchByMessageNr( -1 );
            $this->assertEquals( 'Expected exception was not thrown' );
        }
        catch ( ezcMailNoSuchMessageException $e )
        {
            $this->assertEquals( 'The message with ID <-1> could not be found.', $e->getMessage() );
        }
    }

    public function testFetchByMessageNr2()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $message = $pop3->fetchByMessageNr( 0 );
            $this->assertEquals( 'Expected exception was not thrown' );
        }
        catch ( ezcMailNoSuchMessageException $e )
        {
            $this->assertEquals( 'The message with ID <0> could not be found.', $e->getMessage() );
        }
    }

    public function testFetchByMessageNr3()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $message = $pop3->fetchByMessageNr( 1 );
        $parser = new ezcMailParser();
        $mail = $parser->parseMail( $message );
        $this->assertEquals( 1, count( $mail ) );
        $this->assertEquals( array( 0 => '1' ), $this->getAttribute( $message, 'messages' ) );
        $this->assertEquals( 'ezcMailPop3Set', get_class( $message ) );
    }
    
    public function testfetchFromOffset1()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $set = $pop3->fetchFromOffset( -1, 10 );
            $this->assertEquals( 'Expected exception was not thrown' );
        }
        catch ( ezcMailOffsetOutOfRangeException $e )
        {
            $this->assertEquals( 'The offset <-1> is outside of the message subset <-1, 10>.', $e->getMessage());
        }
    }

    public function testfetchFromOffset2()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $set = $pop3->fetchFromOffset( 10, 1 );
            $this->assertEquals( 'Expected exception was not thrown' );
        }
        catch ( ezcMailOffsetOutOfRangeException $e )
        {
            $this->assertEquals( 'The offset <10> is outside of the message subset <10, 1>.', $e->getMessage() );
        }
    }

    public function testfetchFromOffset3()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $set = $pop3->fetchFromOffset( 0, -1 );
            $this->assertEquals( 'Expected exception was not thrown' );
        }
        catch ( ezcMailInvalidLimitException $e )
        {
            $this->assertEquals( 'The message count <-1> is not allowed for the message subset <0, -1>.', $e->getMessage() );
        }
    }

    public function testfetchFromOffset4()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $set = $pop3->fetchFromOffset( 1, 4 );
        $parser = new ezcMailParser();
        $mail = $parser->parseMail( $set );
        $this->assertEquals( 4, count( $mail ) );
        $this->assertEquals( "pine: Mail with attachment", $mail[1]->subject );
    }

    public function testfetchFromOffset5()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $set = $pop3->fetchFromOffset( 1, 0 );
        $parser = new ezcMailParser();
        $mail = $parser->parseMail( $set );
        $this->assertEquals( 4, count( $mail ) );
        $this->assertEquals( "pine: Mail with attachment", $mail[1]->subject );
    }

    public function testStatus()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $pop3->status( $num, $size );
        $this->assertEquals( 4, $num );
        $this->assertEquals( 69024, $size );
    }

    public function testTop()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $list = $pop3->top( 1, 1 );
        // we do a simple test here.. Any non-single line reply here is 99.9% certainly a good reply
        $this->assertEquals( true, count( explode( "\n", $list ) ) > 1 );
    }

    public function testInvalidTop()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $pop3->top( 1000, 1 );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testDelete()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        try
        {
            $pop3->delete( 1000 );
            $this->fail( "Didn't get exception when expected" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }
    
    public function testListUniqueIdentifiersSingle()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $this->assertEquals( array( 1 => "1143007546.176" ), $pop3->listUniqueIdentifiers( 1 ) );
    }

    public function testListUniqueIdentifiersMultiple()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $this->assertEquals(
            array(
                1 => '1143007546.176',
                2 => '1143007546.177',
                3 => '1143007546.178',
                4 => '1143007546.179',
            ),
            $pop3->listUniqueIdentifiers()
        );
    }

    public function testApop()
    {
        try
        {
            $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
            $pop3->authenticate( "ezcomponents", "ezcomponents", ezcMailPop3Transport::AUTH_APOP );
            $this->fail( "Did not get excepted exception" );
        }
        catch ( ezcMailTransportException $e )
        {
        }
    }

    public function testDisconnect()
    {
        $pop3 = new ezcMailPop3Transport( "dolly.ez.no" );
        $pop3->authenticate( "ezcomponents", "ezcomponents" );
        $pop3->disconnect();
        $pop3->disconnect();
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcMailTransportPop3Test" );
    }
}
?>
