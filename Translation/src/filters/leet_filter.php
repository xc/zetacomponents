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
 * @package Translation
 */

/**
 * Implements the Leet translation filter.
 *
 * The leet filter mangles translations to old skool 1337 73x7.
 *
 * @package Translation
 * @version //autogentag//
 */
class ezcTranslationLeetFilter implements ezcTranslationFilter
{
    /**
     * @param ezcTranslationLeetFilter Instance
     */
    static private $instance = null;

    /**
     * Private constructor to prevent non-singleton use
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of the class ezcTranslationFilterLeet
     *
     * @return ezcTranslationFilterLeet Instance of ezcTranslationFilterLeet
     */
    public static function getInstance()
    { 
        if ( is_null( self::$instance ) ) 
        { 
            self::$instance = new ezcTranslationLeetFilter(); 
        } 
        return self::$instance; 
    }

    /**
     * This "leetify" the $text.
     *
     * @param string $text
     * @return string
     */
    static private function leetify( $text )
    {
        $searchMap = array( '/to/i', '/for/i', '/ate/i', '/your/i', '/you/i', '/l/i', '/e/i', '/o/i', '/a/i', '/t/i' );
        $replaceMap = array( '2', '4', '8', 'ur', 'u', '1', '3', '0', '4', '7' );

        $textBlocks = preg_split( '/(%[^ ]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
        $newTextBlocks = array();
        foreach ( $textBlocks as $text )
        {
            if ( strlen( $text ) && $text[0] == '%' )
            {
                $newTextBlocks[] = (string) $text;
                continue;
            }
            $text = preg_replace( $searchMap, $replaceMap, $text );

            $newTextBlocks[] = (string) $text;
        }
        $text = implode( '', $newTextBlocks );
        return $text;
    }

    /**
     * Filters a context
     *
     * Applies the "1337" filter on the given context. This filter leetifies
     * text old skool. It is, of course, just an example.
     *
     * @param array[ezcTranslationData] $context
     * @return void
     */
    public function runFilter( array $context )
    {
        foreach ( $context as $element )
        {
            $element->translation = self::leetify( $element->translation );
        }
    }
}
?>
