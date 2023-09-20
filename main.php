<?php

require_once './config.php';
require_once './journal.php';
require_once './issue.php';
require_once './article.php';

$journal_id = 6; //GUSPSD

$csv = array();

// linha 0 é o cabecalho
$csv[0][] = 'Journal_id';
$csv[0][] = 'Journal_path';
$csv[0][] = 'Journal_name';
$csv[0][] = 'Issue_id';
$csv[0][] = 'Volume';
$csv[0][] = 'Número';
$csv[0][] = 'Ano';
$csv[0][] = 'Data da Publicação';

$lin = 1; // contador de linha do $csv

$journal = new journal($journal_id);

echo "\n\nJOURNAL: {$journal->id} \n";

$csv[$lin][] = $journal->id;
$csv[$lin][] = $journal->path;
$csv[$lin][] = $journal->name;

// obtém locales
$banco = banco::instanciar();
$q = "select locale, count(locale) as n
        from article_settings 
        where locale != ''
        group by locale 
        order by n desc";
$res = $banco->consultar($q);

foreach ($res as $r){
    $locales[] = $r['locale'];
}

//echo print_r($journal->issues_ids) . " <-Fasciculos\n";

foreach ($journal->issues_ids as $issue_id){
    
    $issue = new issue($issue_id);

    echo "\n\nISSUE: {$issue->id} \n";
    
    $csv[$lin][] = $issue->id;
    $csv[$lin][] = $issue->volume;
    $csv[$lin][] = $issue->numero;
    $csv[$lin][] = $issue->ano;
    $csv[$lin][] = $issue->data_publicacao;
    
    //echo print_r($issue->articles_ids) . " <-Artigos \n";
    
    foreach ($issue->articles_ids as $article_id){
        
        $article = new article($article_id, $journal->id);

        echo "\n\nARTICLE: {$article->id} \n";
        
        // id
        $csv[$lin][] = $article->id;
        
        // titulo
        foreach ($locales as $locale){
            $csv[$lin][] = @$article->title[$locale] ? $article->title[$locale] : '';
        }
        
        // autores
        foreach ($locales as $locale){
            $csv[$lin][] = @$article->authors[$locale] ? $article->authors[$locale] : '';
        }
        
        // resumo;
        foreach ($locales as $locale){
            $csv[$lin][] = @$article->abstract[$locale] ? $article->abstract[$locale] : '';
        }
        
        // palavraschaves
        foreach ($locales as $locale){
            $csv[$lin][] = @$article->keywords[$locale] ? $article->keywords[$locale] : '';
        }
        
        // demais dados independentes do locale
        $csv[$lin][] = $article->pages;
        $csv[$lin][] = $article->doi;
        $csv[$lin][] = $article->file;
        $csv[$lin][] = $article->files;
        
        echo "\n----------------\n";
        sleep(1);
        
    }
    
}

$fp = fopen('saida.csv', 'w');
foreach ($csv as $linha) {
    fputcsv($fp, $linha);
}
fclose($fp);

echo "https://dev2.igc.usp.br/ojs-2-tainacan/saida.csv \n\n";


echo "\n\nFIM \n\n";