<?php

$config = array(
        'driver' => 'mysql',
        'host' => 'host',
        'database' => 'database',
        'user' => 'user',
        'pass' => 'pass',
);

$url = 'https://yourojs.com/index.php/';

//$folder = '/sites-usp/ppegeo/files/journals/[JOURNALID]/articles/[ARTICLEID]/public/';
$folder = 'https://yourojs.com/folderwhereareyourfiles/journals/[JOURNALID]/articles/[ARTICLEID]/[FILEPATH]/';

// pasta para armazer os arquivos baixados
$upload = 'files/';

// url doi
// se vazio, vai devolver apenas o doi
$urldoi = 'https://doi.org/';