<?php

require_once 'CardList.php';


$deck = new CardList();
$deck->addCard('Hover Barrier', 'RTR', 4);
$deck->addCard('Hover Barrier', 'RTR', 4);
$deck->addCard('Hover Barrier', 'RTR', 4);
$deck->addCard('Hover Barrier', 'RTR', 4);

$sideboard = new CardList();
$deck->addCard('Hover Barrier', 'RTR', 2);
$deck->addCard('Hover Barrier', 'RTR', 2);
$deck->addCard('Hover Barrier', 'RTR', 2);
$deck->addCard('Hover Barrier', 'RTR', 2);

$deck = CardList::merge($deck, $sideboard);

$count  = $deck->getCount();
$countM = $deck->getCountM();
$countR = $deck->getCountR();
$countU = $deck->getCountU();
$countC = $deck->getCountC();

echo "\n\n";

echo "Count   = $count\n";
echo "Count M = $countM\n";
echo "Count R = $countR\n";
echo "Count U = $countU\n";
echo "Count C = $countC\n";

echo "\n\n";

foreach ($deck as $card) {
	$id		= $card->getId();
	$name   = $card->getName();
	$rarity = $card->getRarity();
	$count  = $card->getCount();
	$type   = $card->getType();
	$mana   = $card->getMana();
	echo "Card($id, $name, $rarity, $count, $type, $mana)\n";
}

echo "\n\n";