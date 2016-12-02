<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Exception extends Exception {

}

class FW_Option_Type_Exception_Not_Found extends FW_Option_Type_Exception {

}

class FW_Option_Type_Exception_Invalid_Class extends FW_Option_Type_Exception {

}

class FW_Option_Type_Exception_Already_Registered extends FW_Option_Type_Exception {

}