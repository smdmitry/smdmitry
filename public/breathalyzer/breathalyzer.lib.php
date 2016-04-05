<?php

// ------------------------------------- Bruteforce algorithm
function breathalyzerBrute($input, $vocabulary) {
    $result = 0;

    $vocabularyHash = []; // Make words dictionary for fastest lookup
    foreach ($vocabulary as $len => $words) {
        foreach ($words as $word) {
            $vocabularyHash[$word] = 1;
        }
    }

    $knownWords = [];
    foreach ($input as $word) {
        if (isset($knownWords[$word])) {
            $result += $knownWords[$word];
            continue;
        }

        if (isset($vocabularyHash[$word])) {
            $min = 0;
        } else {
            $min = computeMinimumLevenshteinDistanceBrute($word, $vocabulary);
        }

        $knownWords[$word] = $min;
        $result += $min;
    }

    return $result;
}
function computeMinimumLevenshteinDistanceBrute($word, $vocabulary) {
    $len = strlen($word);
    $min = $len;

    $i = $len;
    $run = 1;
    do {
        if (!empty($vocabulary[$i])) { // has words of length $i
            foreach ($vocabulary[$i] as $vWord) {
                $dist = levenshtein($word, $vWord); // native levenshtein implementation
                $min = min($min, $dist >= 0 ? $dist : $len);

                if ($min == 0 || $min == 1) {
                    break; // we already checked that word is not in vocabulary => 1 is minimum we can get
                }
            }
        }

        $i += $run % 2 ? $run : -$run; // modify search word length: 1 run = len+1, 2 run = len-1, 3 run = len+2, 4 run = len-2, ...
        $run++;
    } while(abs($len - $i) < $min);

    return $min;
}
function readVocabulary($name) { // builds dictionary of vocabulary by word lengths
    $vocabularyByLengths = unserialize(file_get_contents($name . '.ser'));

    if (empty($vocabularyByLengths)) {
        $vocabulary = file_get_contents($name); // read vocabulary file
        $vocabulary = strtolower($vocabulary); // make all words lowercase
        $vocabulary = explode("\n", $vocabulary); // make an array of vocabulary words

        foreach ($vocabulary as $word) { // clean vocabulary from empty records and create vocabulary dictionary by word lengths
            $word = trim($word);
            $length = strlen($word);

            if ($length) {
                $vocabularyByLengths[$length][] = $word;
            }
        }

        file_put_contents($name . '.ser', serialize($vocabularyByLengths));
    }

    return $vocabularyByLengths;
}

// ------------------------------------- Trie based algorithm
function breathalyzerTrie($input, $trie) {
    $result = 0;

    $knownWords = [];
    foreach ($input as $word) {
        if (isset($knownWords[$word])) {
            $result += $knownWords[$word];
            continue;
        }

        $min = computeMinimumLevenshteinDistanceTrie($trie, $word);

        $knownWords[$word] = $min;
        $result += $min;
    }

    return $result;
}
function computeMinimumLevenshteinDistanceTrie($trie, $word)
{
    if (isInTrie($trie, $word)) {
        return 0;
    }

    $length = strlen($word);
    $min = $length;

    $row = [];
    for ($i = 0; $i <= $length; $i++) $row[$i] = $i;

    foreach ($trie as $l => &$node) {
        if ($l !== 0) {
            traverseTrie($node, $l, $word, $row, $min);
        }
    }

    return $min;
}
function isInTrie($trie, $word) {
    $node = &$trie;

    for ($i = 0; $i < strlen($word); $i++) {
        if (!empty($node[$word[$i]])) {
            $node = $node[$word[$i]];
        }
    }

    return !empty($node[0]) && $node[0] === $word;
}
function traverseTrie(&$trie, $ch, $word, $prow, &$min)
{
    $size = count($prow);
    $crow = [0 => $prow[0] + 1];

    for ($i = 1; $i < $size; $i++) {
        $insert = $crow[$i - 1] + 1;
        $del = $prow[$i] + 1;
        $replace = $word[$i - 1] != $ch ? ($prow[$i - 1] + 1) : $prow[$i - 1];

        $crow[$i] = min($insert, $del, $replace);
    }

    if ($crow[$size - 1] < $min && !empty($trie[0])) {
        $min = $crow[$size - 1];
    }

    if (min($crow) < $min) {
        foreach ($trie as $l => &$node) {
            if ($l !== 0) {
                traverseTrie($node, $l, $word, $crow, $min);
            }
        }
    }

    return $min;
}
function readVocabularyTrie($name) {
    $trie = unserialize(file_get_contents($name . '.trie'));

    if (empty($trie)) {
        $trie = [];

        $vocabulary = file_get_contents($name); // read vocabulary file
        $vocabulary = strtolower($vocabulary); // make all words lowercase
        $vocabulary = explode("\n", $vocabulary); // make an array of vocabulary words

        foreach ($vocabulary as $word) { // build trie
            $word = trim($word);
            $length = strlen($word);

            $node = &$trie;
            if ($length) {
                for ($i = 0; $i < strlen($word); $i++) {
                    if (!isset($node[$word[$i]])) {
                        $node[$word[$i]] = [];
                    }
                    $node = &$node[$word[$i]];
                }
                $node[0] = $word;
            }
        }

        file_put_contents($name . '.trie', serialize($trie));
    }

    return $trie;
}

// ------------------------------------- Common code

function readInput() {
    $input = 'tihs   sententcnes iss  nout varrry goud';

    if (!empty($_SERVER['argv'][1])) {
        $input = file_get_contents($_SERVER['argv'][1]);
    } else if (!empty($_GET['input'])) {
        $input = $_GET['input'];
        if (strpos($input, '.in')) {
            $input = file_get_contents($input);
        }
    }

    $input = strtolower($input);

    $input = preg_replace('/\s+/', ' ', $input); // remove multiple whitespaces
    $input = explode(' ', $input); // make an array of input words

    $input = array_filter($input, function($word) { // filter any empty lines
        return strlen(trim($word)) > 0;
    });

    return $input;
}