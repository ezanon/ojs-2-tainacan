<?php

require_once './banco.php';

class journal {
    
    public $banco;
    public $id;
    public $path;
    public $name;
    
    public function __construct($id = NULL) {
        $this->banco = banco::instanciar();
        // define id da revista
        $this->id = $id;
        // obtém alias (path)
        $q = ""
                . "select path "
                . "from journals "
                . "where journal_id={$id} "
                . "limit 1";
        $res = $this->banco->consultar($q);
        $this->path = $res[0]['path'];
        // obtém nome da revista
        $q = ""
                . "select setting_value "
                . "from journal_settings "
                . "where journal_id={$id} "
                . "and setting_name = 'searchDescription' "
                . "limit 1";
        $res = $this->banco->consultar($q);
        $this->name = $res[0]['setting_value'];
        return true;
    }
    
}