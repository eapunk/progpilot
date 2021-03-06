<?php

require_once './vendor/autoload.php';

$context = new \progpilot\Context;
$analyzer = new \progpilot\Analyzer;

$context->inputs->set_file("source_code1.php");

$analyzer->run($context);
$results = $context->outputs->get_results();

var_dump($results);
