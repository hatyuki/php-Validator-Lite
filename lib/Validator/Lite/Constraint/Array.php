<?php
require_once 'Validator/Lite/Constraint.php';


class ValidatorLiteConstraintArray extends ValidatorLiteConstraint
{
    function register_rule ( )
    {
        $this->rule( array(
            'ARRAY[INT]'  => array($this, 'v_array_int'),
            'ARRAY[UINT]' => array($this, 'v_array_uint'),
        ) );
    }

    function v_array_int ($value)
    {
        if ( !is_array($value) ) {
            return false;
        }

        foreach ($value as $v) {
            if ( !preg_match('/^[+\-]?[0-9]+$/', $v) ) {
                return false;
            }
        }

        return true;
    }

    function v_array_uint ($value)
    {
        if ( !is_array($value) ) {
            return false;
        }

        foreach ($value as $v) {
            if ( !preg_match('/^[0-9]+$/', $v) ) {
                return false;
            }
        }

        return true;
    }
}
