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
            $this->authors[$locale] = $this->get_autores($locale);
            $this->abstract[$locale] = $this->get_resumo($locale);
            $this->keywords[$locale] = $this->get_palavraschaves($locale);
        }
        $this->pages = $this->get_pages();
        $this->doi = $this->get_doi();
        $this->file = $this->get_files();
        $this->files = $this->get_files();
        return true;
    }
    
    public function create_csv_line(){
        //$this->get_xml();
        //$line = $this->read_xml();
        $line = $this->id;
        foreach ($this->locales as $locale) {
            $line.= ',' . $this->get_titulo($locale);
            $line.= ',' . $this->get_autores($locale);
            $line.= ',' . $this->get_resumo($locale);
            $line.= ',' . $this->get_palavraschaves($locale);
        }
        $line.= ',' . $this->get_pages();
        $line.= ',' . $this->get_doi();
        $line.= ',' . $this->get_files();
        return $line;
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
        $q = "select file_name from article_files where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        foreach ($res as $file){
            $files[] = $this->filesfolder . $file['file_name'];
        }
        $files_names = implode("||", $files);
        return $files_names;
    }
    
    private function get_titulo($locale = 'pt_BR'){
        $q = "select setting_value as titulo from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='cleanTitle' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $titulo = @!is_null($res[0]['titulo']) ? $res[0]['titulo'] : '';
        return $titulo;
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
        return $authors;
    }
    
    private function get_resumo($locale = 'pt_BR'){
        $q = "select setting_value as resumo from article_settings 
                where article_id={$this->id} 
                and setting_name='abstract' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $resumo = @!is_null($res[0]['resumo']) ? $res[0]['resumo'] : '';
        return $resumo;
    }
    
    private function get_palavraschaves($locale = 'pt_BR'){
        $q = "select setting_value as keywords from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='subject' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $palavraschaves = @!is_null($res[0]['keywords']) ? $res[0]['keywords'] : '';
        return $palavraschaves;
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