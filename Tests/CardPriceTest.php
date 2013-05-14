<?php

require_once 'Card.php';

$card = new Card('Deadeye Navigator', 'AVR', 4);

echo "id           = {$card->getId()}\n";
echo "name         = {$card->getName()}\n";
echo "set          = {$card->getSet()}\n";
echo "count        = {$card->getCount()}\n";
echo "mana         = {$card->getMana()}\n";
echo "rarity       = {$card->getRarity()}\n";
echo "type         = {$card->getType()}\n";
echo "url gatherer = {$card->getImgUrlGatherer()}\n";
echo "url zymic    = {$card->getImgUrlZymic()}\n";
echo "mtgnet price = {$card->getMtgNetPrice()}\n";
echo "mtgnet count = {$card->getMtgNetCount()}\n\n";
