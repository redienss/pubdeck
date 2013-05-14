<?php

require_once 'Deck.php';
require_once 'AllegroClient.php';

$deck = new Deck('Tests\vampires.mwDeck');
$client = new AllegroClient(WEBAPI_ENV_TEST, true);
$client->createDeckAuction($deck);
