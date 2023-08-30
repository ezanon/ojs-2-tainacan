<?php

require_once './config.php';
require_once './journal.php';
require_once './issue.php';
require_once './article.php';

$journal_id = 10; //anigeo

$csv = array();

$csv[] = '' //header do csv com definições de criação automática dos campos
        . 'Journal_id'
        . ',Journal_path'
        . ',Journal_name'
        . ',Issue_id'
        . ',Volume'
        . ',Número'
        . ',Ano'
        . ',"Data da Publicação"'; 

$journal = new journal($journal_id);

$journal_vals =  array(
    $journal.id,
    $journal.path,
    $journal.name
);
$csv_journal = implode(',', $journal_vals);

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
        
        $article = new article($article_id, $journal_id);
        
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