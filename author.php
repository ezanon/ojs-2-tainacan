<?php

require_once './banco.php';

class author {
    
    public $banco;
    public $id;
    public $nome = ''; 
    public $afiliacao = '';
    public $email = '';
    
    /**
     * @param type $id
     * @return boolean
     */
    public function __construct($id) {
        $this->banco = banco::instanciar();

        $this->id = $id;
        
        $this->get_name();
        $this->get_email();
        $this->get_afiliacao();
        
        return true;
    }
    
    public function get_dados(){
        $autor = "{$this->nome} [{$this->email} :: {$this->afiliacao}]";
        return $autor;
    }
    
    private function get_name(){
        $q = "select first_name, middle_name, last_name from journals where author_id={$this->id}";
        $res = $this->banco->consultar($q);
        $this->nome = implode(' ', $res[0]);
        $this->nome = trim(preg_replace('/\s\s+/',' ', $this->nome)); // retira espaços em excesso
        return true;
    }
    
    private function get_email(){
        $q = "select email from journals where author_id={$this->id}";
        $res = $this->banco->consultar($q);
        $this->email = implode(' ', $res[0]['email']);
        if (
                ($this->email=='padrao@usp.br') or
                ($this->email=='thalita.almeida@usp.br')
            ){
            $this->email = '';
        }
        $this->email = substr_replace(':', '', $this->email);
        return true;
    }
    
    private function get_afiliacao(){
        $q = "select setting_value from author_settings where author_id={$this->id} and setting_name='affiliation'";
        $res = $this->banco->consultar($q);
        $this->afiliacao = $res[0]['setting_value'];
        $this->afiliacao = trim(preg_replace('/\s\s+/',' ', $this->afiliacao)); // retira espaços em excesso
        return true;
    }
    
}