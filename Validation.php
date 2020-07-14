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

    public function validate()
    {

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

                    static::$errors[] = [
                        'field' => $field,
                        'message' => 'Tidak boleh kosong'
                    ];
                }

                static::$data[$field] = null;
            }
        }

        $result =  [
            'status' => 'success',
            'data' => static::$data
        ];


        if (!empty(static::$errors)) {

            $result['status'] = 'error';
            $result['errors'] = static::$errors;
        }

        return $result;
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

                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => "Harus berupa angka"
                    ];        
                }
            }

        } else {

            if (!is_numeric(static::$data[$field])) {

                static::$errors[] = [
                    'field' => $field,
                    'message' => "Harus berupa angka"
                ];        
            }
        }
    }

    private function lengthCheck($field, $length)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {

                if (strlen((string)$data) != $length) {

                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => "Harus $length karakter"
                    ];
                }
            }

        } else {


            if (strlen((string)static::$data[$field]) != $length) {

                static::$errors[] = [
                    'field' => $field,
                    'message' => "Harus $length karakter"
                ];
            }
        }
    }


    private function maxCheck($field, $max)
    {


        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {


                if (in_array('numeric', explode('|', static::$rules[$field]))) {
                    
                    if ($data > $max) {

                        static::$errors[] = [
                            'field' => "{$field}[$key]",
                            'message' => "Tidak boleh lebih dari $max"
                        ];
                    }
                
                } else {

                    if (strlen($data) > $max) {

                        static::$errors[] = [
                            'field' => "{$field}[$key]",
                            'message' => "Tidak boleh lebih dari $max karakter"
                        ];
                    }
                }
            }

        } else {

            if (in_array('numeric', explode('|', static::$rules[$field]))) {
                
                if (static::$data[$field] > $max) {

                    static::$errors[] = [
                        'field' => $field,
                        'message' => "Tidak boleh lebih dari $max"
                    ];
                }
            
            } else {

                if (strlen(static::$data[$field]) > $max) {

                    static::$errors[] = [
                        'field' => $field,
                        'message' => "Tidak boleh lebih dari $max karakter"
                    ];
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

                        static::$errors[] = [
                            'field' => "{$field}[$key]",
                            'message' => "Tidak boleh kurang dari $min"
                        ];
                    }
                
                } else {

                    if (strlen($data) < $min) {

                        static::$errors[] = [
                            'field' => "{$field}[$key]",
                            'message' => "Tidak boleh kurang dari $min karakter"
                        ];
                    }
                }

            }

        } else {
    
            if (in_array('numeric', explode('|', static::$rules[$field]))) {
                
                if (static::$data[$field] < $min) {

                    static::$errors[] = [
                        'field' => $field,
                        'message' => "Tidak boleh kurang dari $min"
                    ];
                }
            
            } else {

                if (strlen(static::$data[$field]) < $min) {

                    static::$errors[] = [
                        'field' => $field,
                        'message' => "Tidak boleh kurang dari $min karakter"
                    ];
                }
            }
        }

    }

    private function requiredCheck($field)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                
                if ($data == '') {
                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => 'Tidak boleh kosong'
                    ];
                }
            }

        } else {


            if (static::$data[$field] == '') {
                static::$errors[] = [
                    'field' => $field,
                    'message' => 'Tidak boleh kosong'
                ];
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

                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => 'Tidak valid'
                    ];
                }

            }

        } else {

            if (!in_array(static::$data[$field], $param)) {

                static::$errors[] = [
                    'field' => $field,
                    'message' => 'Tidak valid'
                ];
            }
        }
    }

    private function confirmationCheck($field, $field_to_confirm)
    {

        if (is_array(static::$data[$field])) {

            foreach (static::$data[$field] as $key => $data) {
                
                if ($data != static::$data[$field_to_confirm][$key]) {

                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => 'Tidak valid'
                    ];
                }

            }

        } else {

            if (static::$data[$field] != static::$data[$field_to_confirm]) {

                static::$errors[] = [
                    'field' => $field,
                    'message' => 'Tidak valid'
                ];
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

                    static::$errors[] = [
                        'field' => "{$field}[$key]",
                        'message' => 'Sudah digunakan'
                    ];
                }
            }

        } else {
            
            $check = (new Database)
                ->table($table)
                ->select($column)
                ->where($column, static::$data[$field])
                ->one();
            if ($check) {

                static::$errors[] = [
                    'field' => $field,
                    'message' => 'Sudah digunakan'
                ];
            }
        }

    }
}
