<?php

/*
 * This file is part of ProgPilot, a static analyzer for security
 *
 * @copyright 2017 Eric Therond. All rights reserved
 * @license MIT See LICENSE at the root of the project for more info
 */


namespace progpilot\Dataflow;

use progpilot\Objects\MyFunction;
use progpilot\Objects\MyOp;

class Functions
{
    private $functions;

    public function get_function($funcname, $class_name = null)
    {
        if (isset($this->functions[$funcname])) {
            $list_funcs = $this->functions[$funcname];
            foreach ($list_funcs as $myfunc) {
                if (!$myfunc->is_type(MyFunction::TYPE_FUNC_METHOD) && is_null($class_name)) {
                    return $myfunc;
                }

                if ($myfunc->is_type(MyFunction::TYPE_FUNC_METHOD)) {
                    $myclass = $myfunc->get_myclass();
                    if ($class_name === $myclass->get_name()) {
                        return $myfunc;
                    }
                }
            }
        }

        return null;
    }

    public function get_functions_byname($name)
    {
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }

        return null;
    }

    public function get_functions()
    {
        return $this->functions;
    }

    public function add_function($funcname, $func)
    {
        // we can have many functions/methods with the same name
        $continue = true;
        if (isset($this->functions[$funcname])) {
            $continue = false;
            if (!in_array($func, $this->functions[$funcname], true)) {
                $continue = true;
            }
        }

        if ($continue) {
            $this->functions[$funcname][] = $func;
        }
    }

    public function del_function($funcname)
    {
        $save = $this->functions[$funcname];
        $this->functions[$funcname] = [];
        //unset($this->functions[$funcname]);
        return $save;
    }
}
