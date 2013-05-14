<?php

require_once 'Card.php';

$card = new Card('Hover Barrier', 'RTR', 4);

var_dump($card);


echo "id           = {$card->getId()}\n";
echo "name         = {$card->getName()}\n";
echo "set          = {$card->getSet()}\n";
echo "count        = {$card->getCount()}\n";
echo "mana         = {$card->getMana()}\n";
echo "rarity       = {$card->getRarity()}\n";
echo "type         = {$card->getType()}\n";
echo "url gatherer = {$card->getUrlGatherer()}\n";
echo "url zymic    = {$card->getUrlZymic()}\n";

