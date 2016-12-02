<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


/**
 * Class FW_Option_Type_Exception
 *
 * @since 2.6.11
 */
class FW_Option_Type_Exception extends Exception {

}

/**
 * Class FW_Option_Type_Exception_Not_Found
 *
 * @since 2.6.11
 */
class FW_Option_Type_Exception_Not_Found extends FW_Option_Type_Exception {

}

/**
 * Class FW_Option_Type_Exception_Invalid_Class
 *
 * @since 2.6.11
 */
class FW_Option_Type_Exception_Invalid_Class extends FW_Option_Type_Exception {

}

/**
 * Class FW_Option_Type_Exception_Already_Registered
 *
 * @since 2.6.11
 */
class FW_Option_Type_Exception_Already_Registered extends FW_Option_Type_Exception {

}