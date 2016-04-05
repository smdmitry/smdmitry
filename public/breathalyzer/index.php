<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 1);
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 300);

include 'breathalyzer.lib.php';

$type = !empty($_GET['type']) ? $_GET['type'] : '';

if ($type == 'trie') {
    $start = microtime(true);

    $trie = readVocabularyTrie('vocabulary.txt');
    echo "<hr>Trie build time: " . (microtime(true) - $start) . " sec<br><hr><br>";

    $start2 = microtime(true);

    $input = readInput();
    echo "Input words: ". implode(',', $input) ."<br><br>";
    $result = (int)breathalyzerTrie($input, $trie);

    echo "<br>Result: {$result}<br><br>";
    echo "<hr>Working time: " . (microtime(true) - $start2) . " sec<br>";
    echo "Memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " Mb<br>";
} else {
    $start = microtime(true);

    $input = readInput();
    echo "Input words: ". implode(',', $input) ."<br><br>";

    $vocabularyByLengths = readVocabulary('vocabulary.txt');
    $result = (int)breathalyzerBrute($input, $vocabularyByLengths);

    echo "<br>Result: {$result}<br><br>";
    echo "<hr>Working time: " . (microtime(true) - $start) . " sec<br>";
    echo "Memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " Mb<br>";
}
?>

<br><br><br><br><hr>
<a style="color: red; font-weight: bold;;" href="https://github.com/smdmitry/smdmitry/tree/master/public/breathalyzer" target="_blank">Source code on GitHub</a><br><br>

Type: brute<br>
<a href="/breathalyzer/?input=4.in">Test 4.in</a><br>
<a href="/breathalyzer/?input=8.in">Test 8.in</a><br>
<a href="/breathalyzer/?input=12.in">Test 12.in</a><br>
<a href="/breathalyzer/?input=187.in">Test 187.in</a><br>

<br><br>Type: trie<br>
<a href="/breathalyzer/?input=4.in&type=trie">Test 4.in</a><br>
<a href="/breathalyzer/?input=8.in&type=trie">Test 8.in</a><br>
<a href="/breathalyzer/?input=12.in&type=trie">Test 12.in</a><br>
<a href="/breathalyzer/?input=187.in&type=trie">Test 187.in</a><br>


