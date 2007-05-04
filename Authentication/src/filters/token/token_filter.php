<?php
/**
 * File containing the ezcAuthenticationTokenFilter class.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 * @package Authentication
 * @version //autogen//
 */

/**
 * Filter to authenticate against a server generated token.
 *
 * Some uses for this filter:
 *  - CAPTCHA tests
 *  - security token devices (as used by banks)
 *
 * CAPTCHA example:
 * - on the initial request
 * <code>
 * // generate a token and save it in the session or in a file/database or in
 * // the html source code in a hidden form field.
 * // this is just an example to generate a token, the developers can use any
 * // function they want instead of this
 * $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
 * $token  = "";
 * for( $i = 1; $i <= 6 ; $i++ )
 * {
 *     $token .= $pattern{rand( 0, 36 )};
 * }
 * $encryptedToken = sha1( $token );
 * // save the $encryptedToken, for example in a hidden form field in the html source code
 * // also generate a distorted image which contains the symbols from $token and use it
 * </code>
 *
 * - on the follow-up request
 * <code>
 * // load the $token as it was generated on the previous request
 * $token = isset( $_POST['token'] ) ? $_POST['token'] : null;
 * // also load the value entered by the user in response to the CAPTCHA image
 * $captcha = isset( $_POST['captcha'] ) ? $_POST['captcha'] : null;
 * $credentials = new ezcAuthenticationIdCredentials( $captcha );
 * $authentication = new ezcAuthentication( $credentials );
 * $authentication->addFilter( new ezcAuthenticationTokenFilter( $token, 'sha1' ) );
 * if ( !authentication->run() )
 * {
 *     // CAPTCHA was incorrect, so inform the user to try again, eventually
 *     // by generating another token and CAPTCHA image
 * }
 * else
 * {
 *     // CAPTCHA was correct, so let the user send his spam or whatever
 * }
 * </code>
 *
 * @property string $token
 *           The token to check against.
 * @property callback $function
 *           The encryption function to use when comparing tokens.
 *
 * @package Authentication
 * @version //autogen//
 * @mainclass
 */
class ezcAuthenticationTokenFilter extends ezcAuthenticationFilter
{
    /**
     * Token is not the same as the provided one.
     */
    const STATUS_TOKEN_INCORRECT = 1;

    /**
     * Holds the properties of this class.
     *
     * @var array(string=>mixed)
     */
    private $properties = array();

    /**
     * Creates a new object of this class.
     *
     * @param string $token A string value generated by the server
     * @param callback $function The encryption function to use when comparing tokens
     * @param ezcAuthenticationTokenOptions The options for this class
     */
    public function __construct( $token, $function, ezcAuthenticationTokenOptions $options = null )
    {
        $this->token = $token;
        $this->function = $function;
        $this->options = ( $options === null ) ? new ezcAuthenticationTokenOptions() : $options;
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name does not exist
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
            case 'token':
                if ( is_string( $value ) || is_numeric( $value ) )
                {
                    $this->properties[$name] = $value;
                }
                else
                {
                    throw new ezcBaseValueException( $name, $value, 'string || int' );
                }
                break;

            case 'function':
                if ( is_callable( $value ) )
                {
                    $this->properties[$name] = $value;
                }
                else
                {
                    throw new ezcBaseValueException( $name, $value, 'callback' );
                }
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    /**
     * Returns the value of the property $name.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name does not exist
     * @param string $name
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'token':
            case 'function':
                return $this->properties[$name];

            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        switch ( $name )
        {
            case 'token':
            case 'function':
                return isset( $this->properties[$name] );

            default:
                return false;
        }
    }

    /**
     * Runs the filter and returns a status code when finished.
     *
     * @param ezcAuthenticationCredentials $credentials Authentication credentials
     * @return int
     */
    public function run( $credentials )
    {
        $password = call_user_func( $this->function, $credentials->id );
        if ( $this->token === $password )
        {
            return self::STATUS_OK;
        }
        return self::STATUS_TOKEN_INCORRECT;
    }
}
?>
