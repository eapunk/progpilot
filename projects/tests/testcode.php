<?php

require_once './vendor/autoload.php';

$code = '<?php $var1 = $_GET["p1"]."test";$var2 = "test";$var3 = $var1;$var4 = $_GET["p2"];$var5 = htmlentities($var4, ENT_QUOTES, \'UTF-8\');$var6 = htmlentities($var4, ENT_QUOTES, \'UTF-8\')."test".$var4;$var7 = rtrim($var4);$var8 = htmlentities($var2, ENT_QUOTES, \'UTF-8\');if(1==1){	echo "$var1";	echo "$var2";	echo "$var3";	echo "$var4dddd";}else{	echo "$var5";	echo "$var6";	echo "$var7";	echo "$var8";}?>';

$context = new \progpilot\Context;
$analyzer = new \progpilot\Analyzer;

$context->inputs->set_sources("../../package/src/uptodate_data/sources.json");
$context->inputs->set_sinks("../../package/src/uptodate_data/sinks.json");
$context->inputs->set_sanitizers("../../package/src/uptodate_data/sanitizers.json");
$context->inputs->set_validators("../../package/src/uptodate_data/validators.json");
$context->inputs->set_code($code);

try {
    $analyzer->run($context);
} catch (Exception $e) {
    echo 'Exception : ',  $e->getMessage(), "\n";
}

$results = $context->outputs->get_results();
$outputjson = array('results' => $results);

var_dump($results);

?>



