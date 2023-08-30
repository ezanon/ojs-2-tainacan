<?php

require_once './banco.php';

class article {
    
    public $banco;
    public $id;
    public $journalpath;
    public $journalid;
    public $issueid;
    public $filesfolder;
    
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
        // obtém o path da revista a que pertence este artigo
        $this->get_path();
        // obtém o id da revista
        $this->journalid = $jid;
        // monta pasta onde estão os arquivos deste artigo
        global $folder;
        //$folder = './files/journals/[JOURNALID]/articles/[ARTICLEID]/public/[FILE]';
        $this->filesfolder = str_replace('[JOURNALID]', $this->journalid, $folder);
        $this->filesfolder = str_replace('[ARTICLEID]', $this->id, $this->filesfolder);
        return true;
    }
    
    private function get_path(){
        $q = "select path from journals where journal_id=" . $this->journalid;
        $res = $this->banco->consultar($q);
        $this->journalpath = $res[0]['path'];
        return true;
    }
    
    private function get_files() {
        $q = "select file_name "
                . "from article_files "
                . "where article_id = {$this->id}";
        $res = $this->banco->consultar($q);
        foreach ($res as $file){
            $files[] = $this->filesfolder . $file['file_name'];
        }
        $files_names = implode("||", $files);
        return $files_names;
    }
    
    private function get_xml() {
        global $url;
        $link = "$url"
                . "{$this->journalpath}"
                . "/manager/importexport/plugin/NativeImportExportPlugin/exportArticle/"
                . "{$this->id}";
        $content = file_get_contents($link);
        file_put_contents("{$this->id}.xml", $content);
        return true;
    }
    
    private function read_xml(){
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
    
    public function create_csv_line(){
        $this->get_xml();
        $line = $this->read_xml();
        $line.= ',' . $this->get_files();
        return $line;
    }
    
    
    
}