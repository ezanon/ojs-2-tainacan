<?php

require_once './banco.php';
require_once './author.php';

class article {
    
    public $banco;
    public $id;
    public $journalpath;
    public $journalid;
    public $issueid;
    public $filesfolder;
    public $paginas;
    
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
        // monta pasta onde estão os arquivos deste artigo
        global $folder;
        //$folder = './files/journals/[JOURNALID]/articles/[ARTICLEID]/public/[FILE]';
        $this->filesfolder = str_replace('[JOURNALID]', $this->journalid, $folder);
        $this->filesfolder = str_replace('[ARTICLEID]', $this->id, $this->filesfolder);
        return true;
    }
    
    public function create_csv_line(){
        $this->get_xml();
        //$line = $this->read_xml();
        $line = $this->id;
        $line.= ',' . $this->get_titulo();
        $line.= ',' . $this->get_titulo('en_US');
        $line.= ',' . $this->get_autores();
        $line.= ',' . $this->get_autores('en_US');
        $line.= ',' . $this->get_resumo();
        $line.= ',' . $this->get_resumo('en_US');
        $line.= ',' . $this->get_palavraschaves();
        $line.= ',' . $this->get_palavraschaves('en_US');
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
        $this->paginas = $res[0]['titulo'];
    }
    
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
        $saida = implode('||', $autores);
        $saida = "'" . $saida . "'";
        return $saida;
    }
    
    private function get_resumo($locale = 'pt_BR'){
        $q = "select setting_value as resumo from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='pub-id::doi' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $this->paginas = $res[0]['resumo'];
        return true;
    }
    
    private function get_palavraschaves($locale = 'pt_BR'){
        $q = "select setting_value as keywords from article_settings "
                . "where article_id={$this->id} "
                . "and setting_name='subject' and locale='{$locale}'";
        $res = $this->banco->consultar($q);
        $this->paginas = $res[0]['keywords'];
        return true;
    }
    
    private function get_pages(){
        $q = "select pages from articles where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        $this->paginas = $res[0]['pages'];
        return true;
    }
    
    private function get_doi(){
        $q = "select setting_value as doi from article_settings where article_id={$this->id} and setting_name='pub-id::doi'";
        $res = $this->banco->consultar($q);
        $this->paginas = $res[0]['doi'];
        return true;
    }
    
    /**
     * faz download do xml do artigo
     * @global type $url
     * @global type $upload
     * @return boolean
     */
    private function get_xml() {
        global $url;
        global $upload;
        $link = "$url"
                . "{$this->journalpath}"
                . "/manager/importexport/plugin/NativeImportExportPlugin/exportArticle/"
                . "{$this->id}";
        $content = file_get_contents($link);
        $xml = $upload . $this->id . '.xml';
        file_put_contents($xml, $content);
        return true;
    }
    
    private function read_xml(){
        return true;
        $info = array();
        $xml = new DOMDocument();
        $xml->load("{$this->id}.xml");
        
        $article = $xml->getElementsByTagName('article');
        // titulo do artigo
        $node = $article->getElementsByTagName('title');
        $info['title'] = $node->nodeValue;
        // abstract
        $node = $article->getElementsByTagName('abstract');
        $info['abstract'] = $node->nodeValue;
        // pages
        $node = $article->getElementsByTagName('pages');
        $info['pages'] = $node->nodeValue;
        // date_published
        $node = $article->getElementsByTagName('date_published');
        $info['date_published'] = $node->nodeValue;
        // autores  VER COM ANDERSON
        $nodes = $article->getElementsByTagName('author');
        $i = 0;
        $autores = array();
        foreach ($nodes as $node){
            if ($i>0){
                $info['author'][$i].= '||'; // separador para vários autores
            }
            // nome do autor
            $nd = $node->getElementsByTagName('firstname');
            $autores[$i] = $nd->nodeValue;
            $nd = $node->getElementsByTagName('middlename');
            $autores[$i].= ' ' . $nd->nodeValue;
            $nd = $node->getElementsByTagName('lastname');
            $autores[$i].= ' ' . $nd->nodeValue;
            // email do autor
            $nd = $node->getElementsByTagName('email');
            $autores[$i].= ' [' . $nd->nodeValue . ']';
            // afiliação
            $nd = $node->getElementsByTagName('affiliation');
            $autores[$i].= ' [' . $nd->nodeValue . ']';
            $i++;
        }
        $info['authors'] = implode('||', $autores);
        return true;
    }
    
    private function del_xml(){
        unlink("{$this->id}.xml");
        return true;
    }
        
}