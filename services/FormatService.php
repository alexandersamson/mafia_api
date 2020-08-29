<?php


class FormatService
{
    /**
     * @param string $string
     * @param array $options maxLength, breakChars, defaultValue, validateUserName
     */
    public function formatNameString(string $string, array $options = []){
        if(isset($options["maxLength"])){
            if(strlen($string) > $options["maxLength"]){
                $string = substr($string, 0, $options["maxLength"]);
                if(isset($options["breakChars"])){
                    $string .= $options["breakChars"];
                }
            }
        }
        if(isset($options["defaultValue"])) {
            if ($string == null) {
                $string = $options["defaultValue"];
            }
        }
        if(isset($options["validateUserName"])){
            preg_match('/^(?=[a-zA-Z0-9_]{2,20}$)(?!.*[_]{2})[^_].*[^_]$/', $string, $matches);
            if($matches == null){
                return null;
            }
        }
        return $string;
    }
}

