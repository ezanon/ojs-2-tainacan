<?php

require_once './banco.php';

class issue {
    
    public $banco;
    public $id;
    public $volume, $numero, $ano, $data_publicacao;
    public $articles_ids;
    public $files;
    
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
                . "select volume, number, year, date_published "
                . "from issues "
                . "where issue_id={$id} "
                . "limit 1";
        $res = $this->banco->consultar($q);
        $this->volume = $res[0]['volume'];
        $this->numero = $res[0]['number'];
        $this->ano = $res[0]['year'];
        $this->data_publicacao = $res[0]['date_published'];
        // obtém ids dos artigos da revista
        $this->get_articles_ids();
        return true;
    }
    
    /**
     * Obtém ids dos artigos desta publicação
     * @return boolean
     */
    private function get_articles_ids(){
        $q = ""
                . "select article_id "
                . "from published_articles "
                . "where issue_id={$this->id} "
                . "order by seq";
        $res = $this->banco->consultar($q);
        $ids = array();
        foreach ($res as $row){
            $ids[] = $row['article_id'];
        }
        $this->articles_ids = $ids;
        return true;
    }
    
    /**
     * Obtém lista de arquivos do artigo
     * @return boolean
     */
    public function get_files(){
        $q = ""
                . "select file_name "
                . "from article_files "
                . "where article_id={$this->id} "
                . "order by file_id";
        $res = $this->banco->consultar($q);
        $names = array();
        foreach ($res as $row){
            $names[] = $row['file_name'];
        }
        $this->files = $names;
        return true;
                
    }
    
}