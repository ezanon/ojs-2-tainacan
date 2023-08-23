<?php

require_once './config.php';
require_once './journal.php';

$journal_id = 10; //anigeo

$journal = new journal($journal_id);
$journal.get_issues_ids();

$csv = array();

$csv[] = ''
        . '"Volume"'
        . ',"Número"'
        . ',"Ano"'
        . ',"Data da Publicação"'; //header

$journal_vals =  array(
    $journal.id,
    $journal.path,
    $journal.name
);

foreach ($journal.issues_ids as $issue_id){
    
    $issue = new issue($issue_id);
    $issue.get_articles_ids();
    
    $issue_vals = array(
        $issue.id,
        $issue.volume,
        $issue.numero,
        $issue.ano,
        $issue.data_publicacao
    );

    $csv_issue =  implode(',',$valores);
    
    foreach ($issue.articles_ids as $article_id){
        
        $article = new article($article_id);
        
        $article.get_xml();
        $article.get_files();
        $csv_article = $article.create_csv_line();
        $csv[] = 
                $csv_revista .
                $csv_issue . 
                $csv_article;
        
    }
    
}

//$fp = fopen('file.csv', 'w');
//foreach ($csv as $linha) {
//    fputcsv($fp, $linha);
//}
//fclose($fp);