<?php

namespace Gi;

class Validation {

    private static $rules = [];
    private static $data = [];
    private static $errors = [];
    
    public function rules(array $rules)
    {

        static::$rules = $rules;
        return $this;
    }

    public function data(array $data)
    {

        static::$data = $data;
        return $this;
    }

    public function validate(){

        foreach(static::$rules as $field => $rules_str) {

            $rules = explode('|', $rules_str);
            
            if (isset(static::$data[$field])) {


                foreach ($rules as $rule_str){

                        
                    $param = [
                        $field
                    ];

                    $rule = explode(':', $rule_str);
                    
                    if (isset($rule[1])) {
                        $param = array_merge($param, explode(',', $rule[1]));
                    }

                    $method = $rule[0] . 'Check';
                    if (method_exists($this, $method)) {
                        call_user_func_array([$this, $method], $param);
                    }
                }

            } else {

                if (in_array('required', $rules)) {

                    static::$errors[$field] = 'Tidak boleh kosong';
                }

                static::$data[$field] = null;
            }
        }

        $result = [
            'data' => static::$data,
            'errors' => static::$errors
        ];

        return new Collection($result);
    }

    private function stringCheck($field)
    {
        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (is_numeric($data)) {

                    static::$data[$field][$key] =
                        (string) static::$data[$field][$key];
                }
            }

        } else {

            if (is_numeric(static::$data[$field])) {

                static::$data[$field] = (string) static::$data[$field];
            }
        }
    }

    private function numericCheck($field)
    {
        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (!is_numeric($data)) {

                    static::$errors["{$field}[$key]"] = "Harus berupa angka";
                }
            }

        } else {

            if (!is_numeric(static::$data[$field])) {

                static::$errors[$field] = "Harus berupa angka";
            }
        }
    }

    private function lengthCheck($field, $length)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (strlen((string)$data) != $length) {

                    static::$errors["{$field}[$key]"] =
                        "Harus $length karakter";
                }
            }

        } else {


            if (strlen((string)static::$data[$field]) != $length) {

                static::$errors[$field] = "Harus $length karakter";
            }
        }
    }

    private function moneyCheck($field)
    {

        // i cant use regex, honestly i found this code from https://stackoverflow.com/questions/17205377/javascript-regular-expression-for-currency-format
        $regex = '/^(?!0\.00)\d{1,3}(\.\d{3})*(,\d\d)?$/';

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (!preg_match($regex, $data)) {

                    static::$errors["{$field}[$key]"] = "Harus berupa uang";
                }
            }

        } else {

            if (!preg_match($regex, static::$data[$field])) {

                static::$errors[$field] = "Harus berupa uang";
            }
        }
    }



    private function daterangeCheck($field)
    {

        // the same as moneyCheck, https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
        $regex = '/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4} - (0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/';

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (!preg_match($regex, $data)) {

                    static::$errors["{$field}[$key]"] = "Tidak valid";
                }
            }

        } else {

            if (!preg_match($regex, static::$data[$field])) {

                static::$errors[$field] = "Tidak valid";
            }
        }
    }

    private function maxCheck($field, $max)
    {


        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {


                if (in_array('numeric', explode('|', static::$rules[$field]))) {
                    
                    if ($data > $max) {

                        static::$errors["{$field}[$key]"] =
                            "Tidak boleh lebih dari $max";
                    }
                
                } else {

                    if (strlen($data) > $max) {

                        static::$errors["{$field}[$key]"] =
                            "Tidak boleh lebih dari $max karakter";
                    }
                }
            }

        } else {

            if (in_array('numeric', explode('|', static::$rules[$field]))) {
                
                if (static::$data[$field] > $max) {

                    static::$errors[$field] = "Tidak boleh lebih dari $max";
                }
            
            } else {

                if (strlen(static::$data[$field]) > $max) {

                    static::$errors[$field] = 
                        "Tidak boleh lebih dari $max karakter";
                }
            }
        }
    }


    private function minCheck($field, $min)
    {


        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (in_array('numeric', explode('|', static::$rules[$field]))) {
                    
                    if ($data < $min) {

                        static::$errors["{$field}[$key]"] =
                            "Tidak boleh kurang dari $min";
                    }
                
                } else {

                    if (strlen($data) < $min) {

                        static::$errors["{$field}[$key]"] =
                            "Tidak boleh kurang dari $min karakter";
                    }
                }

            }

        } else {
    
            if (in_array('numeric', explode('|', static::$rules[$field]))) {
                
                if (static::$data[$field] < $min) {

                    static::$errors[$field] = "Tidak boleh kurang dari $min";
                }
            
            } else {

                if (strlen(static::$data[$field]) < $min) {

                    static::$errors[$field] =
                        "Tidak boleh kurang dari $min karakter";
                }
            }
        }

    }

    private function requiredCheck($field)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                
                if ($data == '') {
                    static::$errors["{$field}[$key]"] = "Tidak boleh kosong";
                }
            }

        } else {


            if (static::$data[$field] == '') {
                static::$errors[$field] = "Tidak boleh kosong";
            }
        }
    }

    private function inCheck()
    {

        $param = func_get_args();
        $field = array_shift($param);


        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                

                if (!in_array($data, $param)) {

                    static::$errors["{$field}[$key]"] = "Tidak valid";
                }

            }

        } else {

            if (!in_array(static::$data[$field], $param)) {

                static::$errors[$field] = "Tidak valid";
            }
        }
    }

    private function confirmationCheck($field, $field_to_confirm)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                
                if ($data != static::$data[$field_to_confirm][$key]) {

                    static::$errors["{$field}[$key]"] = "Tidak valid";
                }

            }

        } else {

            if (static::$data[$field] != static::$data[$field_to_confirm]) {

                static::$errors[$field] = "Tidak valid";
            }
        }
    }

    private function uniqCheck($field, $column, $table)
    {


        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                
                $check = (new Database)
                    ->table($table)
                    ->select($column)
                    ->where($column, $data)
                    ->one();

                if ($check) {

                    static::$errors["{$field}[$key]"] = "Sudah digunakan";
                }
            }

        } else {
            
            $check = (new Database)
                ->table($table)
                ->select($column)
                ->where($column, static::$data[$field])
                ->one();
            if ($check) {

                static::$errors[$field] = "Sudah digunakan";
            }
        }

    }
}
