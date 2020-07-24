<?php

namespace Gi\Traits;

use Gi\Collection;

trait QueryBuilder {

    private
        $coloum = [],
        $wcoloum = [],
        $owcoloum = [],
        $sql = false,
        $wsql = false,
        $obsql = false,
        $table = false,
        $start = 0,
        $length = 0;

    public function __set($coloum, $value){

    	$this->coloum[$coloum] = $value;
    }

    private function tableName(){

        return $this->table != false ? $this->table : get_called_class();
    }

    public function table($name){

        $this->table = $name;
        return $this;
    }

    public function arg(array $args = []){

        $args = implode("', '", self::escape($args));

        $this->table .= "('{$args}')";

        return $this;
    }

    public function select(){

        $coloum = func_get_args();
        $coloum = empty($coloum) ? '*' : implode(', ', $coloum);

        $table = $this->tableName();

        $this->sql = "select {$coloum} from {$table}";

        return $this;
    }

    public function start($start){

        $this->start = $start;
        return $this;
    }
    
    public function length($length){

        $this->length = $length;
        return $this;
    }

    public function count(){

        $coloum = func_get_args();
        $coloum = empty($coloum) ? '*' : implode(', ', $coloum);

        $table = $this->tableName();

        $this->sql = "select count({$coloum}) from {$table}";

        return $this;
    }

    public function orderBy(){

        $coloum = func_get_args();

        $this->obsql = ' order by ' . implode(', ', $coloum);

        return $this;
    }

    public function asc(){

        $this->obsql .= ' asc';
        return $this;
    }

    public function desc(){

        $this->obsql .= ' desc';
        return $this;
    }

    public function insert(array $data = []){

    	$data = empty($data) ? $this->coloum : $data;

    	$coloum = implode(',', $this->escape(array_keys($data)));

		$value = array_map(function($value){

            $value = $this->escape($value);
			return "'{$value}'";

		}, array_values($data));

		$value = implode(',', $value);

        $table = $this->tableName();

		$this->sql = "insert into {$table}({$coloum}) values({$value})";

		return $this;
    }

    public function update(array $data = []){

        $data = empty($data) ? $this->coloum : $data;

        $arr_data = [];
        foreach ($data as $coloum => $value) {
            
            $coloum = $this->escape($coloum);
            $value = $this->escape($value);

            $arr_data[] = "{$coloum} = '$value'";
        }

        $data = implode(', ', $arr_data);
        

        $table = $this->tableName();

        $this->sql = "update {$table} set {$data}";

        return $this;
    }

    public function orWhere($coloums, $condition = false, $value = false){

        return $this->where($coloums, $condition, $value, 'or');
    }

    public function where(
        $coloums,
        $condition = false,
        $value = false,
        $glue = 'and'
    ){

        if (is_callable($coloums)) {

            $this->wcoloum[] = "{$glue} (";
            
            $coloums($this);

            $this->wcoloum[] = ")";

        } else if (is_array($coloums)) {

            foreach ($coloums as $coloum => $value) {
                    

                if (is_array($value)) {

                    $coloum = $this->escape($value[0]);
                    $condition = $value[1];
                    $value = $this->escape($value[2]);

                    $this->wcoloum[] =
                        "{$glue} {$coloum} {$condition} '{$value}'";
                
                } else {
                    
                    $coloum = $this->escape($coloum);
                    $value = $this->escape($value);

                    $this->wcoloum[] = "{$glue} {$coloum} = '{$value}'";

                }

            }

        } else if ($condition !== false and $value === false) {
            
            $coloums = $this->escape($coloums);
            $condition = $this->escape($condition);

            $this->wcoloum[] = "{$glue} {$coloums} = '{$condition}'";

        } else if ($condition !== false and $value !== false) {
            
            
            $coloums = $this->escape($coloums);
            $value = $this->escape($value);

            $this->wcoloum[] = "{$glue} {$coloums} {$condition} '{$value}'";
        }


        $this->wsql = ' where';
        $pwstatement = null;
        foreach ($this->wcoloum as $i => $wstatement) {


            if (0 == $i or in_array($pwstatement, ['(', 'or (', 'and ('])) {

                $wstatement = str_replace(['and ', 'or '], null, $wstatement);
            }

            $this->wsql .= ' ' . $wstatement;
            
            $pwstatement = $wstatement;
        }

        return $this;
    }

    public function delete(){
        
        $table = $this->tableName();

        $this->sql = "delete from {$table}";

        return $this;
    }

    public function getSql(){

        if (0 != $this->length) {

            $this->sql = str_replace('select ',
                "select first {$this->length} skip {$this->start} ",
                $this->sql);

        }

    	return $this->sql . $this->wsql . $this->obsql;
    }

    public function run($sql = false){

        if (false == $sql) {

            return $this->query($this->getSql());
        }

    	return $this->query($sql);
    }

    public function raw($sql, $prepare_var = false, $callback = false){

        if (is_array($prepare_var)) {

            foreach ($prepare_var as $key => $val) {

                $key = $this->escape($key);
                $val = $this->escape($val);

                $sql = str_replace("{{$key}}", "{$val}", $sql);
            }
        }   

        $query = $this->query($sql);

        if (is_callable($prepare_var)) {

            $callback = $prepare_var;

        } else if(false == $callback) {

            $callback = function($item) {

                return $item;
            };
        }

        $data = [];
        while ($item = $this->assoc($query)){

               
            $data[] = $callback($item);
        }

        return new Collection($data);
    }

    public function returning($coloum){

        $this->sql .= ' returning ' . $this->escape($coloum);

        $result = $this->assoc($this->run());

        if ($result) {

            return $result[$coloum];
        }
        
        return false;
    }

    public function get($callback = false){

    	$query = $this->run();

        $data = [];

        if ($callback == false) {

            $callback = function($item) {

                return $item;
            };
        }
    	
    	while ($item = $this->assoc($query)){

               
            $data[] = $callback($item);
        }

    	return new Collection($data);
    }

    public function one(){

        $data = $this->assoc($this->run());

        if ($data) {
            return new Collection($data);
        }

        return new Collection;
    }

    public function all($callback = false){

    	return $this->select()->get($callback);
    }
}
