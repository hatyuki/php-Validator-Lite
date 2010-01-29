<?php
require_once 'Validator/Lite/Constraint.php';


class ValidatorLiteConstraintDefault extends ValidatorLiteConstraint
{
    function register_rule ( )
    {
        $this->rule( array(
            'NOT_NULL' => array($this, 'v_not_null'),
            'INT'      => array($this, 'v_int'),
            'UINT'     => array($this, 'v_uint'),
            'ASCII'    => array($this, 'v_ascii'),
            'LENGTH'   => array($this, 'v_length'),
            'REGEX'    => array($this, 'v_regex'),
        ) );
            
        $this->alias( array(
            'NOT_NULL' => 'NOT_BLANK',
            'REGEX'    => 'REGEXP',
        ) );
    }

    function v_not_null ($v)
    {
        if ( !isset($v) ) {
            return false;
        }
        else if ( is_null($v) ) {
            return false;
        }
        else if ( is_array($v) ) {
            if ( empty($v) ) {
                return false;
            }
        }
        else if (strcmp($v, '') === 0) {
            return false;
        }
        else if ( !is_numeric($v) && empty($v) ) {
            return false;
        }

        return true;
    }

    function v_int ($v)
    {
        if ( !is_numeric($v) ) {
            return false;
        }
        if ( preg_match('/^[+\-]?[0-9]+$/', $v) ) {
            return true;
        }

        return false;
    }

    function v_uint ($v)
    {
        if ( !is_numeric($v) ) {
            return false;
        }

        if ( preg_match('/^[0-9]+$/', $v) ) {
            return true;
        }

        return false;
    }

    function v_ascii ($v)
    {
        if ( preg_match('/^[0x21-0x7E]+$/', $v) ) {
            return true;
        }

        return false;
    }

    function v_length ($v, $min, $max)
    {
        if ( !isset($min) || !is_int($min) ) {
            trigger_error('missing $min');
        }

        $length = strlen($v);

        return ($min <= $length && $length <= $max)
             ? true
             : false;
    }

    function v_regex ($v, $regex)
    {
        return preg_match($regex, $v)
             ? true
             : false;
    }
}
