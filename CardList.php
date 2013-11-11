<?php

ConfigManager::load('Config\mysql.ini');

/**
 * MTG CardList class
 * 
 * @author Tomasz Szneider
 */
class CardList extends ArrayObject {

    /**
     * Create select query for loading card list from DB
     * 
     * @return array
     */
    private function createSelectQuery(){
        
        $idsArray = array();
        
        foreach ($this as $card) {
            $name = addslashes($card->getName());
            $set  = $card->getSetCode();
            $idsArray[] = "('$name','$set')";
        }

        $idsString = implode(',', $idsArray);
        $query = "select c.nid, c.nname, c.nset, s.ncode_mtgnet, s.ncode_gatherer, c.ntype, c.nrarity, c.nmanacost, c.nrating, c.nartist ";
        $query.= "from ncards c join nsets s on c.nset = s.ncode where (c.nname, c.nset) in($idsString)";
        return $query;
    }
    
    /**
     * Bulk data load from DB. 
     * Loads all cards data with single query.
     * 
     * @param string $name - card name
     * @param string $set  - card set
     * 
     * @return array
     */
    public function loadDataFromDB() {
                
        if($this->count() == 0) return;
        
        $con = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);
        mysql_select_db(MYSQL_DB_NAME, $con);

        $query = self::createSelectQuery();
        $result = mysql_query($query, $con);
 
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $card = $this->findCardByNameAndSet($row["nname"], $row["nset"]);
            if(!is_null($card)){
                $card->setDataFromDbRow($row);   
            }
        }

        mysql_close($con);
    }
    
    /**
     * Merge two CardList into one
     * 
     * @param CardList $list1 - card list to merge
     * @param CardList $list2 - card list to merge
     * 
     * @return CardList - merge of given card lists
     */
    public static function merge($list1, $list2) {
                
        $merge = new CardList();

        foreach ($list1 as $card) {
            $merge->addCard(clone $card);
        }
        foreach ($list2 as $card) {
            $merge->addCard(clone $card);
        }
        
        return $merge;
    }

    /**
     * Add card to card list
     * If card already exists, only count will be increased.
     * 
     * @param Card $card  - card to add
     * 
     * @return void
     */
    public function addCard($card) {
   
        $result = $this->findCardByNameAndSet(
            $card->getName(), 
            $card->getSetCode()
        );
        
        if(is_null($result)){
            $this->append($card);    
        }else{
            $result->addCount($card->getCount());
        }
    }

    /**
     * Get count of mythic rare cards
     * 
     * @return int
     */
    public function getCountM() {
        $count = 0;

        foreach ($this as $card) {
            if ($card->isMythicRare()) {
                $count += $card->getCount();
            }
        }

        return $count;
    }

    /**
     * Get count of rare cards
     * 
     * @return int
     */
    public function getCountR() {
        $count = 0;

        foreach ($this as $card) {
            if ($card->isRare()) {
                $count += $card->getCount();
            }
        }

        return $count;
    }

    /**
     * Get count of uncommon cards
     * 
     * @return int
     */
    public function getCountU() {
        $count = 0;

        foreach ($this as $card) {
            if ($card->isUncommon()) {
                $count += $card->getCount();
            }
        }

        return $count;
    }

    /**
     * Get count of common cards
     * 
     * @return int
     */
    public function getCountC() {
        $count = 0;

        foreach ($this as $card) {
            if ($card->isCommon()) {
                $count += $card->getCount();
            }
        }

        return $count;
    }

    /**
     * Get count of all cards
     * 
     * @return int
     */
    public function getCount() {
        $count = 0;
        
        foreach ($this as $card) {
            $count += $card->getCount();
        }
        
        return $count;
    }

    /**
     * Find card in card list by name
     * 
     * @param string $name - name of card to find
     * 
     * @return Card
     */
    function findCardByName($name)
    {  
        foreach ($this as $card) {
            if ($card->getName() == $name){
                return $card;
            }
        }
        return null;
    }
    
    /**
     * Find card in card list by name
     * 
     * @param string $name - name of card to find
     * @param string $set  - set  of card to find
     * 
     * @return Card
     */
    function findCardByNameAndSet($name, $set) 
    {        
        foreach ($this as $card) {
            $refName = $card->getName();
            $refSet  = $card->getSetCode();
            if ($refName == $name && $refSet == $set){
                return $card;
            }
        }
        return null;
    }

    /**
     * Order this CardList by card rating (ascending)
     * 
     * @return void
     */
    function orderByRatingAsc() {
        $cmpFunc = array('Card', 'compareByRatingAsc');
        $this->uasort($cmpFunc);
    }

    /**
     * Order this CardList by card rating (descending)
     * 
     * @return void
     */
    function orderByRatingDesc() {
        $cmpFunc = array('Card', 'compareByRatingDesc');
        $this->uasort($cmpFunc);
    }

}

