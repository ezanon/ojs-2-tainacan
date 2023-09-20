<?php

require_once './banco.php';
require_once './author.php';

class article {
    
    public $banco;
    public $id;
    public $journalpath;
    public $journalid;
    public $issueid;
    public $locales;
    public $filesfolder;
    public $title, $authors, $abstract, $keywords, $pages, $doi, $file, $files;
    
    /**
     * 
     * @param type $id (id do artigo)
     * @param type $jid (id da revista ou journal)
     * @return boolean
     */
    public function __construct($id, $jid) {
        $this->banco = banco::instanciar();
        // define id da revista
        $this->id = $id;
        // obtém o id da revista
        $this->journalid = $jid;
        // obtém o path da revista a que pertence este artigo
        $this->get_path();
        // obtém locales do artigo
        $this->get_locales();
        // monta pasta onde estão os arquivos deste artigo
        global $folder;
        //$folder = './files/journals/[JOURNALID]/articles/[ARTICLEID]/public/';
        $this->filesfolder = str_replace('[JOURNALID]', $this->journalid, $folder);
        $this->filesfolder = str_replace('[ARTICLEID]', $this->id, $this->filesfolder);
        // obtém dados
        $this->get_data();
        return true;
    }
    
    private function get_data(){
        $line = $this->id;
        foreach ($this->locales as $locale) {
            $this->title[$locale] = $this->get_titulo($locale);
            $this->abstract[$locale] = $this->get_resumo($locale);
            $this->keywords[$locale] = $this->get_palavraschaves($locale);
        }
        $this->authors = $this->get_autores();
        $this->pages = $this->get_pages();
        $this->doi = $this->get_doi();
        $this->get_files();
        return true;
    }
    
    private function get_path(){
        $q = "select path from journals where journal_id={$this->journalid}";
        $res = $this->banco->consultar($q);
        $this->journalpath = $res[0]['path'];
        return true;
    }
    
    /**
     * Obtém idiomas do artigo
     * @return boolean
     */
    private function get_locales(){
        $q = "select locale 
                from ojs_ppegeo.article_settings 
                where article_id={$this->id} and locale != ''
                group by locale ";
        $res = $this->banco->consultar($q);
        foreach ($res as $r){
            $this->locales[] = $r['locale'];
        }
        return true;
    }
    
    /**
     * Obtém lista de arquivos do artigo
     * @return type
     */
    private function get_files() {
        // modelo: https://ppegeo.igc.usp.br/index.php/GeoCT/article/download/13983/13581
        // https://ppegeo.igc.usp.br/index.php/[alias]/article/download/[13983]/[13581]
        // https://dev2.igc.usp.br/ojs-2-tainacan/wow/
        $q = "select file_name from article_files where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        foreach ($res as $file){
            $files[] = $this->filesfolder . $file['file_name'];
        }
        
        $this->file = $files[0]; // arquivo principal
        unset($files[0]);
        $this->files = implode("||", $files); // anexos
        
        return true;
    }
    
    private function get_titulo($locale = 'pt_BR'){
        $q = "select setting_value as titulo from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='cleanTitle' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $titulo = @!is_null($res[0]['titulo']) ? $res[0]['titulo'] : '';
        return utf8_encode($titulo);
    }
    
    /**
     * Retorna os autores dos artigos junto de seu email e afiliação
     * @return string
     */
    private function get_autores(){
        // obtém ids dos autores
        $q = "select author_id as id from authors where submission_id=$this->id order by author_id";
        $res = $this->banco->consultar($q);
        // loop nos autores pegando seus dados
        $autores = array();
        foreach ($res as $r){
            $autor = new author($r['id']);
            $autores[] = $autor->get_dados();
        }
        $authors = implode('||', $autores);
        $authors = "'" . $authors . "'";
        return utf8_encode($authors);
    }
    
    private function get_resumo($locale = 'pt_BR'){
        $q = "select setting_value as resumo from article_settings 
                where article_id={$this->id} 
                and setting_name='abstract' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $resumo = @!is_null($res[0]['resumo']) ? $res[0]['resumo'] : '';
        return utf8_encode($resumo);
    }
    
    private function get_palavraschaves($locale = 'pt_BR'){
        $q = "select setting_value as keywords from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='subject' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $palavraschaves = @!is_null($res[0]['keywords']) ? $res[0]['keywords'] : '';
        return utf8_encode($palavraschaves);
    }
    
    private function get_pages(){
        $q = "select pages from articles where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        $paginas = @!is_null($res[0]['pages']) ? $res[0]['pages'] : '';
        return $paginas;
    }
    
    private function get_doi(){
        $q = "select setting_value as doi from article_settings where article_id={$this->id} and setting_name='pub-id::doi'";
        $res = $this->banco->consultar($q);
        $doi = @!is_null($res[0]['doi']) ? $res[0]['doi'] : '';
        return $doi;
    }
        
}