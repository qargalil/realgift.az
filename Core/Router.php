<?php

    namespace Core;
    
    class Router
    {
        private $isHTTPS=false;
        private $ok=1;

        function Router($https){
            if($https) $this->isHTTPS=true;
        }

        public function post($curl,$action){
            if($this->check($curl) && $_POST && $this->ok){
                $this->start($action,$_POST);
            }
        }
        
        public function get($curl,$action){
            //if($_SERVER['REQUEST_SCHEME']=='http'){
            //    $this->route('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            //}
           // else{
                if($this->check($curl) && $this->ok){
                    $this->start($action);
                }
            //}

        }
        
        public function getjson($curl,$action){
            $postdata = trim(file_get_contents("php://input"));
            if($this->ok && $postdata && $this->check($curl)){
                $postdata = json_decode($postdata,true);
                $this->start($action,$postdata);
            }
        }
        
        public function defAction($action){
            if($this->ok){
                global $url;
                if($url){
                    $this->route('/');
                }
                else{
                    $this->start($action);
                }

            }
        }
        
        public function route($url){
            header("location: $url");
        }
        
        private function start($action,$data=false){
            
            $parts = explode("@",$action);
            $controller = "App\Controllers\\".$parts[0];
            $method = $parts[1];
            if(class_exists($controller)){
                $obj = new $controller();
                $obj->$method($data);
                $this->ok = 0;
            }
        }
        
        private function check($curl){
            global $url;
            $curl = preg_replace("/\//",'\/',$curl);
            return preg_match("/^$curl$/",$url);
        }
        
    }
    
    
    