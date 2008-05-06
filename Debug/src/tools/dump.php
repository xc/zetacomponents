<?php
/**
 * File containing the ezcDebugVariableDumpTool class.
 *
 * @package Debug
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Tool class to dump variables in a way similar to Xdebug.
 *
 * This tool class allows to dump variables similar to the way it is done by
 * Xdebug (@link http://xdebug.org). The class is used in the {@link
 * ezcDebugPhpStacktraceIterator} to unify the variable dumps with those
 * generated by {@link ezcDebugXdebugStacktraceIterator}.
 * 
 * @package Debug
 * @version //autogen//
 */
class ezcDebugVariableDumpTool
{
    /**
     * Maximum ammount of content to dump for a variable.
     */
    const MAX_DATA = 512;

    /**
     * Maximum number of children to dump for array and object structures. 
     */
    const MAX_CHILDREN = 128;

    /**
     * Maximum recursive depth to iterate for array and object structures. 
     */
    const MAX_DEPTH = 3;

    /**
     * Returns the string representation of an variable.
     *
     * Returns the dump of the given variable, respecting the $maxData and
     * $maxChildren paramaters when arrays or objects are dumped.
     * 
     * @param mixed $arg 
     * @param int $maxData 
     * @param int $maxChildren 
     * @return string
     */
    public static function dumpVariable( $arg, $maxData = self::MAX_DATA, $maxChildren = self::MAX_CHILDREN, $maxDepth = self::MAX_DEPTH )
    {
        switch ( gettype( $arg ) )
        {
            case 'boolean':
                return self::cutString( ( $arg ? 'TRUE' : 'FALSE' ), $maxData );
            case 'integer':
            case 'double':
                return self::cutString( (string) $arg, $maxData );
            case 'string':
                return sprintf(
                    "'%s'",
                    self::cutString( (string) $arg, $maxData )
                );
            case 'array':
                return self::dumpArray( $arg, $maxData, $maxChildren, $maxDepth );
            case 'object':
                return self::dumpObject( $arg, $maxData, $maxChildren, $maxDepth );
            case 'resource':
                return self::dumpResource( $arg, $maxData );
            case 'NULL':
                return 'NULL';
            default:
                return 'unknown type';
        }
    }

    /**
     * Returns the string representation of an array.
     *
     * Returns the dump of the given array, respecting the $maxData and
     * $maxChildren paramaters.
     * 
     * @param array $arg 
     * @param int $maxData 
     * @param int $maxChildren 
     * @param int $maxDepth
     * @return string
     */
    private static function dumpArray( array $arg, $maxData, $maxChildren, $maxDepth )
    {
        $arrayContent = '';

        if ( $maxDepth != 0 )
        {
            $max = min( count( $arg ), $maxChildren );
        
            $results = array();
            reset( $arg );
            for ( $i = 0; $i < $max; ++$i )
            {
                $results[] =
                    self::dumpVariable( key( $arg ), $maxData, $maxChildren, $maxDepth - 1 )
                    . ' => '
                    . self::dumpVariable( current( $arg ), $maxData, $maxChildren, $maxDepth - 1 );
                next( $arg );
            }

            if ( $max < count( $arg ) )
            {
                $results[] = '...';
            }

            $arrayContent = implode( ', ', $results );
        }
        else
        {
            $arrayContent = '...';
        }

        
        return sprintf(
            'array (%s)', $arrayContent
        );
    }

    /**
     * Returns the string representation of an object.
     *
     * Returns the dump of the given object, respecting the $maxData and
     * $maxChildren paramaters.
     * 
     * @param object $arg 
     * @param int $maxData 
     * @param int $maxChildren 
     * @return string
     */
    private static function dumpObject( $arg, $maxData, $maxChildren, $maxDepth )
    {
        $refObj   = new ReflectionObject( $arg );

        $objectContent = '';
        if ( $maxDepth != 0 )
        {
            $refProps = $refObj->getProperties();

            $max = min(
                count( $refProps ),
                $maxChildren
            );
            $results = array();

            reset( $refProps );
            for( $i = 0; $i < $max; $i++ )
            {
                $refProp = current( $refProps );
                $results[] = sprintf(
                    '%s $%s = %s',
                    self::getPropertyVisibility( $refProp ),
                    $refProp->getName(),
                    self::getPropertyValue( $refProp, $arg, $maxDepth - 1 )
                );
                next( $refProps );
            }
            $objectContent = implode( '; ', $results );
        }
        else
        {
            $objectContent = '...';
        }

        
        return sprintf(
            'class %s { %s }',
            $refObj->getName(),
            $objectContent
        );
    }

    /**
     * Returns the string representation of a resource.
     *
     * Returns the dump of the given resource, respecting the $maxData
     * paramater.
     * 
     * @param resource $res 
     * @param int $maxData 
     * @return string
     */
    private static function dumpResource( $res, $maxData )
    {
        // @TODO: Ugly, but necessary.
        // 'resource(5) of type (stream)'
        preg_match( '(^Resource id #(?P<id>\d+)$)', (string) $res, $matches );
        return sprintf(
            'resource(%s) of type (%s)',
            $matches['id'],
            get_resource_type( $res )
        );
    }

    /**
     * Returns the $value cut to $length and padded with '...'.
     *
     * @param string $value 
     * @param int $length 
     * @return string
     */
    private static function cutString( $value, $length )
    {
        if ( strlen( $value ) > ( $length - 3 ) )
        {
            return substr( $value, 0, ( $length - 3 ) ) . '...';
        }
        return $value;
    }

    /**
     * Returns private, protected or public.
     *
     * Returns the visibility of the given relfected property $refProp as a
     * readable string.
     * 
     * @param ReflectionProperty $refProp 
     * @return string
     */
    private static function getPropertyVisibility( ReflectionProperty $refProp )
    {
        $info = '%s %s = %s';
        if ( $refProp->isPrivate() )
        {
            return 'private';
        }
        if ( $refProp->isProtected() )
        {
            return 'protected';
        }
        return 'public';
    }

    /**
     * Returns the dumped property value.
     *
     * Returns the dumped value for the given reflected property ($refProp) on
     * the given $obj. Makes use of the ugly array casting hack to determine
     * values of private and protected properties.
     * 
     * @param ReflectionProperty $refProp 
     * @param object $obj 
     * @return string
     */
    private static function getPropertyValue( ReflectionProperty $refProp, $obj )
    {
        $value = null;
        // @TODO: If we switch to a PHP version where Reflection can access
        // protected/private property values, we should change this to the
        // correct way.
        if ( !$refProp->isPublic() )
        {
            $objArr    = (array) $obj;
            $className = ( $refProp->isProtected() ? '*' : $refProp->getDeclaringClass()->getName() );
            $propName  = $refProp->getName();
            $value     = $objArr["\0{$className}\0{$propName}"];
        }
        else
        {
            $value = $refProp->getValue( $obj );
        }
        return self::dumpVariable( $value );
    }

}

?>
