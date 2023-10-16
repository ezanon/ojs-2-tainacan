<?php

require_once './config.php';
require_once './journal.php';
require_once './issue.php';
require_once './article.php';

if (@$argv[1])
    $workingon = $argv[1]; // id da revista passada como parametro 
else 
    $workingon = 'all'; // todas as revistas

$html = "<table border=1>\n";

if ($workingon=='all'){
    // obtém ids das revistas
    $banco = banco::instanciar();
    $q = "select journal_id
            from journals 
            where enabled = 1
            order by seq";
    $res = $banco->consultar($q);
    foreach($res as $r){
        $journal_ids[] = $r['journal_id'];
    }
}
else 
    $journal_ids[] = $workingon;

// obtém locales
$banco = banco::instanciar();
$q = "select locale, count(locale) as n
        from article_settings 
        where locale != ''
        group by locale 
        order by n desc";
$res = $banco->consultar($q);

foreach ($res as $r){
    if ($r['locale']=='fr_CA') continue;
    $locales[] = $r['locale'];
}

foreach($journal_ids as $journal_id){
    
    $csv = array();

    // linha 0 é o cabecalho
    $csv[0][] = 'Journal_id|numeric|status_private';
    $csv[0][] = 'Journal_path|taxonomy';
    $csv[0][] = 'Revista|text';

    $csv[0][] = 'Issue_id|numeric|status_private';
    $csv[0][] = 'Volume|taxonomy';
    $csv[0][] = 'Número|taxonomy';
    $csv[0][] = 'Ano|taxonomy';
    $csv[0][] = 'Data da Publicação';

    $csv[0][] = 'Article_id|numeric|status_private';
    $csv[0][] = 'Link OJS|status_private';
    $csv[0][] = 'Autores|text|multiple';
    
    $csv[0][] = 'Título';
    $csv[0][] = 'Resumo|textarea';
    $csv[0][] = 'Palavras-chave|taxonomy|multiple';
    
    $csv[0][] = 'Title (English)';
    $csv[0][] = 'Abstract (English)|textarea';
    $csv[0][] = 'Keywords (English)|taxonomy|multiple';
    
    $csv[0][] = 'Título (Español)';
    $csv[0][] = 'Resumen (Español)|textarea';
    $csv[0][] = 'Palabras clave (Español)|taxonomy|multiple';
    
    $csv[0][] = 'Titre (Français)';
    $csv[0][] = 'Résumé (Français)|textarea';
    $csv[0][] = 'Mots clés (Français)|taxonomy|multiple';
    
    $csv[0][] = 'Titel (Deutsch)';
    $csv[0][] = 'Zusammenfassung (Deutsch)|textarea';
    $csv[0][] = 'Schlüsselwörter (Deutsch)|taxonomy|multiple';
    
    $csv[0][] = 'Páginas';
    $csv[0][] = 'DOI';
    $csv[0][] = 'special_document';
    $csv[0][] = 'special_attachments';

    $lin = 1; // contador de linha do $csv

    $journal = new journal($journal_id);

    echo "\n\nJOURNAL: {$journal->id} \n";

    foreach ($journal->issues_ids as $issue_id){

        $issue = new issue($issue_id);

        foreach ($issue->articles_ids as $article_id){

            // dados da revista
            $csv[$lin][] = $journal->id;
            $csv[$lin][] = $journal->path;
            $csv[$lin][] = $journal->name;
            // dados do fasciculo
            $csv[$lin][] = $issue->id;
            $csv[$lin][] = $issue->volume;
            $csv[$lin][] = $issue->numero;
            $csv[$lin][] = $issue->ano;
            $csv[$lin][] = $issue->data_publicacao;

            $article = new article($article_id, $journal->id);

            // id
            $csv[$lin][] = $article->id;
            $csv[$lin][] = $article->linkojs;
            
            // autores
            $csv[$lin][] = $article->authors;

            // titulo, resumo e palavras chaves possuem locales
            foreach ($locales as $locale){
//                $csv[$lin][] = @$article->title[$locale] ? $article->title[$locale] : '';
//                $csv[$lin][] = @$article->abstract[$locale] ? $article->abstract[$locale] : '';
//                $csv[$lin][] = @$article->keywords[$locale] ? $article->keywords[$locale] : '';
                
                if ($locale == 'pt_BR'){
                    // titulo
                    if ($article->title[$locale] == '') $csv[$lin][] = $article->title['en_US'] ? $article->title['en_US'] : 'sem_titulo';
                    else $csv[$lin][] = $article->title[$locale];
                    // abstract
                    if ($article->abstract[$locale] == '') $csv[$lin][] = @$article->abstract['en_US'] ? $article->abstract['en_US'] : '';
                    else $csv[$lin][] = $article->abstract[$locale];
                    // keywords
                    if ($article->keywords[$locale] == '') $csv[$lin][] = @$article->keywords['en_US'] ? $article->keywords['en_US'] : '';
                    else $csv[$lin][] = $article->keywords[$locale];
                }
                else {
                    $csv[$lin][] = @$article->title[$locale] ? $article->title[$locale] : '';
                    $csv[$lin][] = @$article->abstract[$locale] ? $article->abstract[$locale] : '';
                    $csv[$lin][] = @$article->keywords[$locale] ? $article->keywords[$locale] : '';
                }

                
            }

            // demais dados independentes do locale
            $csv[$lin][] = $article->pages;
            $csv[$lin][] = $article->doi;
            $csv[$lin][] = $article->file != '' ? 'file:' . $article->file : '';
            $csv[$lin][] = $article->files;
            
//            if ($lin == 6){
//                print_r($csv[$lin]);
//                die();
//            }

            $lin++;
            echo '.';

            //sleep(0.5);

        }
        
    }

    $saida = "ojs-{$journal->id}-{$journal->path}.csv";
    $fp = fopen($saida, 'w');
    foreach ($csv as $linha) {
        fputcsv($fp, $linha);
    }
    fclose($fp);
    
    $html.= "<tr><td><a href=https://dev2.igc.usp.br/ojs-2-tainacan/{$saida}>{$journal->id}</a></td><td>{$journal->path}</td><td>{$journal->name}</td></tr>"
            . "\n";
    
    //print_r($csv);

} 

$html.= "</table>";

$saida = "index.html";
$fp = fopen($saida, 'w');
fputs($fp, $html);
fclose($fp);

echo "\n\nhttps://dev2.igc.usp.br/ojs-2-tainacan/index.html \n\n";

echo "\n\nFIM \n\n";
