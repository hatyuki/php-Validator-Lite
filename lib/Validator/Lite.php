<?php
require_once 'Validator/Lite/Constraint.php';
require_once 'Validator/Lite/Messages/Ja.php';

class ValidatorLite
{
    public    $rules;
    public    $error;
    protected $query;
    protected $error_ary;
    protected $msg;

    function __construct ($query, $constraint=array( ))
    {
        if ( empty($query) ) {
            trigger_error('Usage: ${class}->new($query)', E_USER_ERROR);
        }

        $this->query     = $query;
        $this->rules     = array( );
        $this->error     = array( );
        $this->error_ary = array( );

        if ( !is_array($constraint) ) {
            $constraint = array($constraint);
        }

        array_push($constraint, '+Default');
        $this->load_constraints($constraint);
    }


    function check ($rule_ary)
    {
        $query = $this->query;

        foreach ($rule_ary as $key => $rules) {
            $value = null;

            if ( isset($query[$key]) ) {
                $value = $query[$key];
            }

            foreach ($rules as $rule) {
                if ( $this->ref($rule) ) {
                    list($rule_name, $args) = each($rule);
                    if ( !is_array($args) ) {
                        $args = array($args);
                    }
                }
                else {
                    $rule_name = $rule;
                    $args      = array( );
                }

                $is_ok = $this->go($value, $rule_name, $args);

                if ($is_ok === false) {
                    $this->set_error($key, $rule_name);
                }
            }
        }

        return $this;
    }


    function is_error  ($key) { return @$this->error[$key]  ? true : false; }
    function is_valid  ( )    { return !$this->has_error( ) ? true : false; }
    function has_error ( )    { return  $this->error        ? true : false; }


    function set_error ($param, $rule_name)
    {
        $this->error[$param][$rule_name] = true;

        array_push($this->error_ary, array($param, $rule_name));
    }


    function load_constraints ($constraint)
    {
        if ( !is_array($constraint) ) {
            $constraint = array($constraint);
        }

        foreach ($constraint as $c) {
            if ( preg_match('/^\+(.+)$/', $c, $m) ) {
                $c = 'ValidatorLiteConstraint'.$m[1];
            }

            if ( class_exists($c) ) {
                $obj = new $c($this);

                if ( method_exists($obj, 'register_rule') ) {
                    $obj->register_rule( );
                }
                else {
                    trigger_error("colud not register rules: $c", E_USER_WARNING);
                }
            }
            else {
                trigger_error("validator class not found: $c", E_USER_ERROR);
            }
        }
    }


    function load_function_message ($lang='Ja')
    {
        $pkg = "ValidatorLiteMessage$lang";
        $obj = new $pkg( );

        $this->msg['function'] = $obj->messages;
    }


    function set_param_message ($args)
    {
        $this->msg['param'] = $args;
    }


    function set_message_data ($msg)
    {
        foreach ( array('message', 'param', 'function') as $key ) {
            if ( !isset($msg[$key]) ) {
                trigger_error("missing key $key", E_USER_ERROR);
            } 
        }

        $this->msg = $msg;
    }


    function set_message ($args)
    {
        $this->msg['message'] = array_merge($this->msg['message'], $args);
    }


    function get_error_messages ( )
    {
        if (!$this->msg) {
            trigger_error("message doesn't loaded yet", E_USER_ERROR);
        }

        $dup_check = array( );
        $messages  = array( );

        foreach ($this->error_ary as $err) {
            $param = $err[0];
            $func  = $err[1];

            if ( isset($dup_check["$param.$func"]) ) {
                continue;
            }

            array_push($messages, $this->get_error_message($param, $func));
            $dup_check["$param.$func"] = true;
        }

        return $messages;
    }


    function get_error_message ($param, $function)
    {
        $function = strtolower($function);

        $msg = $this->msg;

        if ( !$msg ) {
            trigger_error('please load messages file first', E_USER_ERROR);
        }

        $err_message  = @$msg['message']["$param.$function"];
        $err_param    = @$msg['param'][$param];
        $err_function = @$msg['function'][$function];

        $gen_msg = array($this, 'gen_msg');

        if ($err_message) {
            return call_user_func_array($gen_msg, array($err_function, $err_param));
        }
        else if ($err_function && $err_param) {
            return call_user_func_array($gen_msg, array($err_function, $err_param));
        }
        else {
            trigger_error("$param.$function is not defined in message file", E_USER_WARNING);

            if ( isset($msg['default_tmpl']) ) {
                $args[ ] = $err_function ? $err_function : $msg['default_tmpl'];
                $args[ ] = $err_function ? $err_function : $param;

                return call_user_func_array($gen_msg, $args);
            }
            else {
                return '';
            }
        }
    }


    private function go ($value, $rule_name, $args)
    {
        if ( !isset($value) ) {
            return $rule_name === 'NOT_NULL'
                 ? false
                 : true;
        }
        else {
            if ( !isset($this->rules[$rule_name]) ) {
                trigger_error("unknown rule $rule_name", E_USER_ERROR);
            }

            $func = $this->rules[$rule_name];

            array_unshift($args, $value);

            return call_user_func_array($func, $args) ? true : false;
        }
    }


    private function ref (&$val)
    {
        switch ($val) {
        case is_array($val):
            reset($val);
            $type = 'ARRAY';

            foreach ($val as $k => $v) {
                if ( !is_integer($k) ) {
                    $type= 'HASH';
                }
            }

            reset($val);
            break;

        case is_object($val):
            $type = 'OBJECT';
            break;

        default:
            $type = '';
            break;
        }

        return $type;
    }


    private function gen_msg ($tmpl, $args)
    {
        return preg_replace('/\[_(\d+)\]/', $args, $tmpl);
    }
}
