<?php
/**
 * File containing the ezcAuthenticationTypekeyOptions class.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 * @package Authentication
 * @version //autogen//
 */

/**
 * Class containing the options for the TypeKey authentication filter.
 *
 * Example of use:
 * <code>
 * // create an options object
 * $options = new ezcAuthenticationTypekeyOptions();
 * $options->validity = 60;
 *
 * // use the options object when creating a new TypeKey filter
 * $filter = new ezcAuthenticationTypekeyFilter( $options );
 *
 * // alternatively, you can set the options to an existing filter
 * $filter = new ezcAuthenticationTypekeyFilter();
 * $filter->setOptions( $options );
 * </code>
 *
 * @property int $validity
 *           The maximum timespan that can exist between the timestamp
 *           sent by the application server at log-in and the timestamp sent
 *           by the TypeKey server. A value of 0 means the validity value
 *           is not taken into consideration when validating the response
 *           sent by the TypeKey server. Do not use a value too small, as
 *           the servers might not be synchronized.
 *
 * @package Authentication
 * @version //autogen//
 */
class ezcAuthenticationTypekeyOptions extends ezcAuthenticationFilterOptions
{
    /**
     * Constructs an object with the specified values.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if $options contains a property not defined
     * @throws ezcBaseValueException
     *         if $options contains a property with a value not allowed
     * @param array(string=>mixed) $options
     */
    public function __construct( array $options = array() )
    {
        $this->validity = 0; // seconds

        parent::__construct( $options );
    }

    /**
     * Sets the option $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'validity':
                if ( !is_numeric( $value ) || ( $value < 0 ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'int >= 0' );
                }
                $this->properties[$name] = (int) $value;
                break;

            default:
                parent::__set( $name, $value );
        }
    }
}
?>
