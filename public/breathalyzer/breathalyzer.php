<?php

include 'breathalyzer.lib.php';

$vocabulary = 'vocabulary.txt';
$input = readInput();

if (!empty($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'trie') {
    $trie = readVocabularyTrie($vocabulary);
    $result = (int)breathalyzerTrie($input, $trie);
} else {
    $vocabularyByLengths = readVocabulary($vocabulary);
    $result = (int)breathalyzerBrute($input, $vocabularyByLengths);
}

echo "{$result}\n";
