<?php

require_once './banco.php';

class issue {
    
    public $banco;
    public $id;
    public $volume, $numero, $ano;
    public $articles_ids;
    
    /**
     * Obtém volume, numero e ano
     * @param type $id
     * @return boolean
     */
    public function __construct($id = NULL) {
        $this->banco = banco::instanciar();
        // define id da revista
        $this->id = $id;
        // obtém volume, numero e ano desta edição
        $q = ""
                . "select volume, number, year "
                . "from issues "
                . "where issue_id={$id} "
                . "limit 1";
        $res = $this->banco->consultar($q);
        $this->volume = $res[0]['volume'];
        $this->numero = $res[0]['numero'];
        $this->ano = $res[0]['ano'];
        return true;
    }
    
    /**
     * Obtém ids dos artigos desta publicação
     * @return boolean
     */
    public function get_issues_ids(){
        $q = ""
                . "select article_id "
                . "from published_articles "
                . "where issue_id={$this->id} "
                . "order by seq";
        $res = $this->banco->consultar($q);
        $ids = array();
        foreach ($res as $row){
            $ids[] = $row['issue_id'];
        }
        $this->articles_ids = $ids[];
        return true;
    }
    
}