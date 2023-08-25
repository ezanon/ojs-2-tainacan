<?php

require_once './banco.php';

class article {
    
    public $banco;
    public $id;
    
    /**
     * Obtém volume, numero e ano
     * @param type $id
     * @return boolean
     */
    public function __construct($id = NULL) {
        $this->banco = banco::instanciar();
        // define id da revista
        $this->id = $id;
        return true;
    }
    
    public function get_xml($path) {
        global $url;
        $link = "$url"
                . "{$path}"
                . "/manager/importexport/plugin/NativeImportExportPlugin/exportArticle/"
                . "{$this->id}";
        $content = file_get_contents($link);
        file_put_contents("{$this->id}.xml", $content);
        return true;
    }
    
    public function get_files() {
        $q = "select file_name "
                . "from article_files "
                . "where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        foreach ($res as $file){
            $files[] = $file['file_name'];
        }
        $files_names = implode("||", $files);
        return $files_names;
    }
    
}