<?php

/**
 * MTG CardList class
 * 
 * @author Tomasz Szneider
 */
class CardList extends ArrayObject {
    
	/**
	 * Merge two CardList into one
	 * 
	 * @param CardList $list1 - card list to merge
	 * @param CardList $list1 - card list to merge
	 * 
	 * @return CardList - merge of given card lists
	 */
	public static function merge($list1, $list2){
		
		$merge = new CardList();
		
		foreach ($list1 as $card) {
			$merge[] = $card;
		}
		foreach ($list2 as $card) {
			$merge[] = $card;
		}
		
		return $merge;
	}
	      
	/**
	 * Add new card to card list
	 * 
	 * @param string $name  - card name
	 * @param string $set   - card set e.g. 'RTR'
	 * @param int    $count - card count
	 * 
	 * @return void
	 */
	public function addCard($name, $set, $count){
		$this[] = new Card($name, $set, $count);	
	}
	
	/**
	 * Get count of mythic rare cards
	 * 
	 * @return int
	 */
	public function getCountM()
	{
		$count = 0;
		
		foreach($this as $card){
			if($card->isMythicRare()){
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
	public function getCountR()
	{
		$count = 0;
		
		foreach($this as $card){
			if($card->isRare()){
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
	public function getCountU()
	{
		$count = 0;
		
		foreach($this as $card){
			if($card->isUncommon()){
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
	public function getCountC()
	{
		$count = 0;
		
		foreach($this as $card){
			if($card->isCommon()){
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
	public function getCount()
	{
		$count = 0;
		
		foreach($this as $card){
			$count += $card->getCount();
		}	
		
		return $count;
	}
	
	/**
	 * Find card in card list
	 * 
	 * @param string $name - name of card to find
	 * 
	 * @return Card
	 */
	function findCard($name)
	{
	    // Search for card
	    foreach ($this as $card) {
	        if($card->getName() == $name) return $card;
	    }    
	    
	    // No card found
	    return null;
	}
    
    /**
     * Order this CardList by card rating (ascending)
     * 
     * @return void
     */
    function orderByRatingAsc()
    {
        $cmpFunc = array('Card', 'compareByRatingAsc');
        $this->uasort($cmpFunc);
    }
    
    /**
     * Order this CardList by card rating (descending)
     * 
     * @return void
     */
    function orderByRatingDesc()
    {
        $cmpFunc = array('Card', 'compareByRatingDesc');
        $this->uasort($cmpFunc);
    }
}
















