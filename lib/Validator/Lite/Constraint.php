<?php
require_once 'Validator/Lite/Constraint/Default.php';
require_once 'Validator/Lite/Constraint/Array.php';


abstract class ValidatorLiteConstraint
{
    protected $validator;

    function __construct ($validator)
    {
        $this->validator = $validator;
    }

    protected function rule ($rules)
    {
        foreach ($rules as $name => $func) {
            $this->validator->rules[$name] = $func;
        }
    }

    protected function alias ($alias)
    {
        foreach ($alias as $from => $to) {
            $this->validator->rules[$to] = $this->validator->rules[$from];
        }
    }

    protected function delsp ($x)
    {
        return preg_replace('/\s/', '', $x);
    }
}
