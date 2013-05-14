<?php

require_once 'MtgNetClient.php';

$client = new MtgNetClient();
$cards = $client->searchAggregated('Naturalize', 'Scott Chou');
print_r($cards);

