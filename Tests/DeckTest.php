<?php

require_once 'Deck.php';

$deck = new Deck('Tests\vampires.mwDeck');

echo $deck->getName()."\n";
echo $deck->getPirce()."\n";
echo $deck->getPhoto(0)."\n";
echo $deck->getPhoto(1)."\n";
echo $deck->getPhoto(2)."\n";
echo $deck->getDescription()."\n";