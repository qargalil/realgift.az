<?php
    namespace App\Models;

    use PDO;

    abstract class Model{

        //table name
        private $table;

        //PDO mysql Connection
        private $db;

        //extra query
        private $extra='';

        //Fields that could be selected. This one could be set in child class
        protected $selectable='*';

        //Fields that could be inserted or updated.This one must be set in child class
        protected $insertable=false;

        public $lastid;

        function __construct(){
            global $db;
            $this->db = $db;
            $class = explode("\\",get_class($this));
            $class = $class[count($class)-1];
            if($class[strlen($class)-1]==y){
                $class[strlen($class)-1] = "i";
                $class.="e";
            }
            $this->table = strtolower($class).'s';
            if ($this->selectable!='*'){
                $this->setSelectables($this->selectable);
            }
        }

        public function setSelectables($array){
            $s = '';
            $x = 0;
            foreach ($array as $value) {
                if($x==0){
                    $s.=" $value ";
                    $x++;
                }
                else
                    $s.=",$value ";

            }
            $this->selectable = $s;
            return $this;
        }

        public function custom($query){
            return $this->db->exec($query);
        }

        public function delete($id){
            if(!is_array($id)) {
                return $this->db->exec("DELETE FROM {$this->table} WHERE id = '$id'");
            }else{
                $this->where($id);
                return $this->db->exec("DELETE FROM {$this->table}".$this->extra);
            }
        }

        //Select all first argument is start second is limit but they are not important
        public function getAll(){
            switch(func_num_args()){
                case 1:
                    $this->extra.=" LIMIT ".func_get_arg(0);
                    break;
                case 2:
                    $this->extra.= " LIMIT ".func_get_arg(0).",".func_get_arg(1);
                    break;
            }
            $query = "SELECT {$this->selectable} FROM {$this->table}{$this->extra}";

            $data = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            $this->extra = '';

            return $data;
        }

        //You know Orderby first is Field second second is order
        public function orderBy($field, $order="ASC"){
            if($field=='rand')$this->extra.= " ORDER BY RAND()";
            else
            $this->extra.= " ORDER BY $field $order";
            return $this;
        }

        //add where attribute
        public function where($where,$if="AND"){
            $x = 0;
            foreach ($where as $key => $value) {
                $key = addslashes(trim($key));
                $value = addslashes(trim($value));
                if($x==0){
                    $this->extra.= " WHERE $key='$value'";
                    $x++;
                }
                else{
                    $plus = $if;
                    if (count($value)>1){
                        $plus = $value[1];
                        $value = $value[0];
                    }
                    $this->extra.= " $plus $key='$value'";
                }
            }
            return $this;
        }

        //get one item by id
        public function getOne($id){
            if(!is_array($id)) {
                return $this->db->query("SELECT {$this->selectable} FROM {$this->table} WHERE id = '$id'")->fetch(PDO::FETCH_ASSOC);
            }else{
                $this->where($id);
                return $this->db->query("SELECT {$this->selectable} FROM {$this->table}".$this->extra)->fetch(PDO::FETCH_ASSOC);
            }

        }

        //update the table
        public function update($data){
            $data = $this->filterArray($data);
            $x=0;
            $set ='';
            foreach ($data as $key => $value) {
                $key = addslashes($key);
                $value = addslashes($value);
                if($x==0){
                    $set.= " $key='$value '";
                    $x++;
                }
                else{
                    $set.= " ,$key='$value'";
                }
            }

            $query = "UPDATE {$this->table} SET".$set.$this->extra;
            if(!preg_match("/WHERE /",$query))die("WHERE is WHERE");

            return $this->db->exec($query);
        }

        //insert a row to table
        public function post($data){

            $data = $this->filterArray($data);

            $values = '';
            $keys = '';
            $x = 0;
            foreach ($data as $key => $value) {
                if($x==0){
                    $x++;
                    $keys.=addslashes($key);
                    $values.="'".trim(addslashes($value))."'";
                }
                else{
                    $keys.=",".addslashes($key);
                    $values.=",'".trim(addslashes($value))."'";
                }
            }

            $num_rows = $this->db->exec("INSERT INTO $this->table($keys) VALUES($values)");
            $this->lastid = $this->db->lastInsertId();
            return $num_rows;
        }

        public function getLast(){
            return $this->getOne($this->lastid);
        }

        //filter the data which is going to be inserted to table or updated
        function filterArray($data){
            if(!$this->insertable) die('You forgot to fill insertables');
            foreach ($data as $key => $value) {
                $is =false;
                foreach ($this->insertable as $val) {
                    if($key==$val) {
                        $is = true;
                        break;
                    }
                }
                if($is)continue;
                    unset($data[$key]);

            }

            return $data;
        }

    }
