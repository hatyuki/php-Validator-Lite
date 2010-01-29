<?php

set_include_path( dirname(__FILE__).'/lib/' );
require_once 'Validator/Lite.php';

$args = array(
    'name' => 'name123です',
);

$rule = array(
    'name' => array('NOT_NULL', array('LENGTH' => array(1,5))),
);

$obj = new ValidatorLite($args);
$obj->load_function_message( );


$obj->check($rule);
$obj->set_param_message( array(
    'name' => 'name',
) );


var_dump( $obj->get_error_messages( ) );
