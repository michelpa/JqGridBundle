<?php
namespace EPS\JqGridBundle\Grid;

class GridTools
{
    protected function encode($input = array(), $funcs = array(), $level = 0)
    {

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $ret = self::encode($value, $funcs, 1);
                $input[$key] = $ret[0];
                $funcs = $ret[1];
            } else {
                if (strpos($value, 'function(') === 0 || strpos($value, 'var_') === 0) {
                    $func_key = "#" . uniqid() . "#";
                    $value = ltrim($value, 'var_');
                    $funcs[$func_key] = $value;
                    $input[$key] = $func_key;
                }
            }
        }
        if ($level == 1) {
            return array(
                $input, $funcs
            );
        } else {
            $input_json = json_encode($input);
            foreach ($funcs as $key => $value) {
                $input_json = str_replace('"' . $key . '"', $value, $input_json);
            }

            return $input_json;
        }
    }

}
