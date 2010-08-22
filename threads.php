<?php 
$file = fopen("php://stdin", "r");
$line = fgets($file);
$line = trim($line);

file_put_contents("error_log.txt", $line);
sleep(5);
exit;
/*

$sections = Fourchan\Sections::getSections();
$url = false;
if(isset($sections[$value])) {
    $url = $sections[$value];
} else {
    echo "fart!\n";
}

if($url) {
    $parser = new Fourchan\Parser($url);
    $parser->getPages();
    $parser->getThreads();
    // lots of threads >.<
    var_dump($parser->threads);
}
*/
?>
