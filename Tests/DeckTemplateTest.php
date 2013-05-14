<?php

require_once 'Deck.php';

$deck = new Deck('Tests\vampires.mwDeck');
$deck->exportToHtml('Tests\vampires.html');