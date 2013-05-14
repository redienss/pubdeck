<?php

define('MTG_NET_SEARCH_URL', 'http://www.mtgnet.pl/ajax_wyszukiwarka.php');

/**
 * MtgNet Search Client
 * 
 * @author Tomasz Szneider
 */
class MtgNetClient {
       
    /**
     * Create POST search array
     * 
     * @return array
     */
    private function createPostArray($searchString, $edition = false, $artist = false, $sortBy = 'price', $sortOrder = 'asc')
    {
        if($artist){
            $searchString.= " ".$artist;
        }    
        
        return array(
            'cmc'         => null,
            'colBlack'    => 1,
            'colBlue'     => 1,
            'colGreen'    => 1,
            'colLess'     => 1,
            'colRed'      => 1,
            'colWhite'    => 1,
            'editions'    => $edition ? $edition : '',
            'page'        => 0,
            'perPage'     => 50,
            'priceFrom'   => null,   
            'priceTo'     => null, 
            'pt'          => null,  
            'rarCommon'   => 1,
            'rarRare'     => 1,
            'rarUncommon' => 1,
            'rgnFlavor'   => 0,
            'rgnIllustr'  => $artist ? 1 : 0,
            'rgnName'     => 1,
            'rgnText'     => 0,
            'rgnType'     => 0,
            'sortBy'      => $sortBy,
            'sortOrder'   => $sortOrder,
            'string'      => $searchString,
            'world'       => 0,
        );   
    }
    
    /**
     * Create POST request stream context
     * 
     * @param string $query - http encoded query string
     * 
     * @return resource
     */    
    private function createPostContext($query)
    {
        // Context options
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Connection: close\r\n".
                            "Content-Type: application/x-www-form-urlencoded\r\n".
                            "Content-Length: ".strlen($query)."\r\n",
                'content'=> $query
            )
        );
        
        // Create POST context
        return stream_context_create($options);
    }
        
    /**
     * Get JSON string from ajax HTML response
     * 
     * @param string $ajax - ajax response
     * 
     * @return string
     */
    private function convertAjaxToJson($ajax)
    {
        preg_match('/addRowsUnsorted\((.*?)\)/', $ajax, $matches);
        return preg_replace('/\'/', '"', $matches[1]);
    }
    
    /**
     * Create card array from single search result row 
     * 
     * @param array $row - search results row
     * 
     * @return array
     */
    private function createCardFromRow($row)
    {
        return array(
            "name"      => $row[0],
            "set"       => $row[1], 
            "number"    => $row[2],
            "rarity"    => $row[3],
            "artist"    => $row[4],
            "type"      => $row[5],
            "mana"      => $row[6],
            "convMana"  => $row[7],
            "color"     => $row[8],
            "language"  => $row[10],
            "condition" => $row[11],
            "foil"      => $row[12],
            "count"     => $row[13],
            "basket"    => $row[14],
            "reserve"   => $row[15],
            "price"     => $row[16]
        );
    }
 
    /**
     * Search card on MtgNet
     * 
     * @param string $name    - card name
     * @param string $edition - set name e.g.:'RTR'
     * @param string $artist  - artist
     * @param bool   $foil    - search foil cards (default = non-foil)
     * 
     * @return array
     */ 
    public function searchCard($name, $edition = false, $artist = false, $foil = false)
    {
        // Search request
        $postData = $this->createPostArray($name, $edition, $artist);
        $query = http_build_query($postData);
        $context  = $this->createPostContext($query);
        $ajax = file_get_contents(MTG_NET_SEARCH_URL, false, $context);
        $json = $this->convertAjaxToJson($ajax);
        $rows = json_decode($json);
        
        // Results array
        $cards = array();
        
        // Convert rows to cards data
        foreach($rows as $row){
            $card = $this->createCardFromRow($row);
            
            // Check foil switch (only non-foil / only foil)
            if(empty($foil) == empty($card['foil'])){
                $cards[] = $card;
            }
            
        }
        
        // Return cards data
        return $cards;
    }
    
    /**
     * Search card among all sets and aggregate data into one record
     *      1. Card price is AVG of non-foil cards
     *      2. Card count is SUM of non-foil cards
     *      3. If artist given, aggregate only cards with similar graphic
     * 
     * @return array
     */ 
    public function searchAggregated($name, $artist = false)
    {
        // Search cards    
        $cards = $this->searchCard($name, false, $artist);
        
        // Aggregate data
        $rowCount = count($cards);
        
        // Check quantity
        if($rowCount==0){
            return null;
        }
        
        // Init stats
        $priceTotal = 0;
        $countTotal = 0;
        
        foreach ($cards as $card) {
            $priceTotal += $card['price'];
            $countTotal += $card['count'];
        }
        
        // Calculate AVG price
        $priceAvg = round($priceTotal / $rowCount, 2);
        
        // Create card data
        $card = $cards[0];
        $card['price'] = $priceAvg;
        $card['count'] = $countTotal;
        
        // Return aggregated card
        return $card;
    }
}

















