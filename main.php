<?php

require_once './config.php';
require_once './journal.php';
require_once './issue.php';
require_once './article.php';

$journal_id = 6; //GUSPSD

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

//
echo "journal: {$journal->id} \n";

$journal_vals =  array(
    $journal->id,
    $journal->path,
    $journal->name
);
$csv_journal = implode(',', $journal_vals);

//
echo print_r($journal->issues_ids) . " <-Fasciculos\n";

foreach ($journal->issues_ids as $issue_id){
    
    $issue = new issue($issue_id);
    
//
echo "issue: {$issue->id} \n";
    
    $issue_vals = array(
        $issue->id,
        $issue->volume,
        $issue->numero,
        $issue->ano,
        $issue->data_publicacao
    );
    $csv_issue =  implode(',',$valores);
    
//
echo print_r($issue->articles_ids) . " <-Artigos \n";
    
    foreach ($issue->articles_ids as $article_id){
        
        $article = new article($article_id, $journal->id);

//
echo "article: {$article->id} \n";
        
        $csv_article = $article->create_csv_line();
        $csv[] = 
                $csv_revista .
                $csv_issue . 
                $csv_article;
        
    }
    
}

var_dump($csv);

//
echo "FIM \n\n";

//$fp = fopen('file.csv', 'w');
//foreach ($csv as $linha) {
//    fputcsv($fp, $linha);
//}
//fclose($fp);