<?php

require_once './banco.php';

class journal {
    
    public $banco;
    public $id;
    public $path;
    public $name;
    public $issues_ids;
    
    
    /**
     * Instancia obtendo valores de id, path (o alias) e name (nome da revista)
     * @param type $id
     * @return boolean
     */
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
        // obtém ids dos fascículos da revista
        $this->get_issues_ids();
        return true;
    }

    /**
     * Retorna os ids dos volume e numeros cadastrado da revista instanciada
     * @return boolean
     */
    public function get_issues_ids(){
        $q = ""
                . "select issue_id "
                . "from issues "
                . "where journal_id={$this->id} "
                . "order by volume, number";
        $res = $this->banco->consultar($q);
        $ids = array();
        foreach ($res as $row){
            $ids[] = $row['issue_id'];
        }
        $this->issues_ids = $ids[];
        return true;
    }
    
}