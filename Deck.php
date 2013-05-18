<?php

define('TEMPLATE_FILE', 'Template\deck_template.html');
define('CSS_STYLE_FILE', 'Template\mtg_style.css');
define('DECK_HEADER_FILE', 'Template\deck_header.txt');

/**
 * MTG Magic Workstation Deck class
 * 
 * @author Tomasz Szneider
 */
class Deck {
    	
	private $name;
	private $price;
	private $photos;
	private $description;
	private $cards;
	private $sideboard;	
	
    /**
     * Check if deck file has header
     * 
     * @param string $file - file path to mwDeck file
     * 
     * @return bool
     */
    private function hasHeader($file)
    {
        // Load deck file contents
        $contents = file_get_contents($file);
        
        // Header check
        $pattern = "/\/\/ ### Deck Publisher Header ###\r\n/";
        
        // Return check result
        return (bool)preg_match($pattern, $contents);
    }
    
    /**
     * Add deck header to mwDeck file
     * 
     * @param string $file - file path to mwDeck file
     * 
     * @return void
     */
    private function addHeader($file)
    {
        // Load deck file contents
        $headContents = file_get_contents(__DIR__."\\".DECK_HEADER_FILE)."\n";
        $deckContents = file_get_contents($file);     
        
        // Remove any comments from deck
        $deckContents = preg_replace("/\/\/(.*?)\n/", '', $deckContents);   
        
        // Write all data to file
        file_put_contents($file, $headContents.$deckContents);
    }
    
	/**
	 * Get raw card name removing unneccessary info:
	 * 	- remove land numbers: Mountain (1) => Mountain
	 *  - remove second name in double faced cards: Apprentice | Werewolf => Apprentice
	 * 
	 * @param string $name - card name to purify
	 * 
	 * @return string - pufiried card name
	 */
	private function purifyCardName($name){  
	    $name = str_replace("|", ";", $name);
	    $name = preg_replace('/(;.*)/', '', $name);
	    $name = preg_replace('/\s\([0-9]\)/', '', $name);
	    return $name;
	}
	
	/**
	 * Extract single param from deck file contetns
	 * 
	 * @param string $contents - contents of mwDeck file
	 * @param string $param    - name of param to extract e.g. 'price'
	 * 
	 * @return string - extracted param value
	 */
	private function extractParam($contents, $param)
	{
	    $pattern = "/\/\/\s*@$param\s+(.*?)\s*\r\n/";
	    preg_match($pattern, $contents, $matches);
	    return trim($matches[1], "\r\n");
	}
	
	/**
	 * Extract param with many occurences as array
	 * 
	 * @param string $contents - contents of mwDeck file
	 * @param string $param    - name of param to extract e.g. 'photo'
	 * 
	 * @return array - array of param values 
	 */
	private function extractMultiParam($contents, $param)
	{
	    $pattern = "/\/\/\s*@$param\s+(.*)\r\n/";
	    preg_match_all($pattern, $contents, $matches);
	    return $matches[1];
	}
	
	/**
	 * Extract multiline param from deck file contents
	 * 
	 * @param string $contents - contents of mwDeck file
	 * @param string $param    - name of param to extract e.g. 'description'
	 * 
	 * @return string - param value
	 */
	private function extractMultilineParam($contents, $param)
	{
	    $pattern = "/\/\/\s*@$param\s+((\/\/.*|\n)*)/";
	    preg_match($pattern, $contents, $matches);
	    $desc = preg_replace('/\/\/\s+/', '', $matches[1]);
	    return $desc;
	}
	
	/**
	 * Extract card list from deck content
	 * 
	 * @param string $contents  - contents of mwDeck file
	 * @param string $cardRegex - regex matching card row in mwDeck file
	 * 
	 * @return CardList - list of cards
	 */
	private function extractCardList($contents, $cardRegex)
	{
	    $cards = new CardList();	
	    $cardCount = preg_match_all($cardRegex, $contents, $matches);
		
	    for ($i = 0; $i < $cardCount; $i++) {
	        
	        // Get basic info from file
	        $count   = trim($matches[1][$i], "\r\n");
	        $set     = trim($matches[2][$i], "\r\n");
	        $name    = trim($matches[3][$i], "\r\n");
			
			// Purify card name
			$name = $this->purifyCardName($name);
	        
	        // Add card to list
	        $cards->addCard($name, $set, $count);
	    }
	
	    return $cards;
	}
	
	/**
	 * Extract main deck card list from deck content
	 * 
	 * @param string $contents  - contents of mwDeck file
	 * 
	 * @return CardList - list of cards
	 */
	private function extractCards($contents){
		$regex = "/^[ \t]+([0-9]+)[ \t]+\[(\w+)\][ \t]+(.*)$/m";
		return $this->extractCardList($contents, $regex);
	}
	
	/**
	 * Extract deck sideboard card list from deck content
	 * 
	 * @param string $contents  - contents of mwDeck file
	 * 
	 * @return CardList - list of cards
	 */
	private function extractSideboard($contents){
		$regex = "/^SB:[ \t]+([0-9]+)[ \t]+\[(\w+)\][ \t]+(.*)$/m";
		return $this->extractCardList($contents, $regex);
	}
		
	/**
	 * Construct deck from mwDeck file
	 * 
	 * @param $string $file - file name of a deck to load (mwDeck)
	 * 
	 * @return void
	 */
	public function Deck($file)
	{
        // Create header if not exists
        if(!$this->hasHeader($file)){
            $this->addHeader($file);    
            echo "\nDefault Deck Header added to $file\n";
        }    
            
	    // Load deck file contents
		$contents = file_get_contents($file);
               
        // Load fields
		$this->name        = $this->extractParam($contents, 'name');
		$this->price       = $this->extractParam($contents, 'price');
		$this->photos      = $this->extractMultiParam($contents, 'photo');
		$this->description = $this->extractMultilineParam($contents, 'description');
		$this->cards       = $this->extractCards($contents);
		$this->sideboard   = $this->extractSideboard($contents);
	}
	
	/**
	 * Get deck name
	 * 
	 * @return string - deck name
	 */
	public function getName(){
		return $this->name;
	}
	
	/**
	 * Get deck price
	 * 
	 * @return string - deck price
	 */
	public function getPirce(){
		return $this->price;
	}
	
    /**
     * Get photos array
     * 
     * @return array - deck photos
     */
    public function getPhotos(){
        return $this->photos;
    }
    
	/**
	 * Get deck photo link
	 * 
	 * @param int $id - id of photo link (0 to 7)
	 * 
	 * @return string - deck photo link
	 */
	public function getPhoto($id){
		return $this->photos[$id];
	}
	
	/**
	 * Get deck decription
	 * 
	 * @return string - deck description
	 */
	public function getDescription(){
		return $this->description;
	}
	
	/**
	 * Get main deck card list 
	 * 
	 * @return CardList - main deck card list
	 */
	public function getCards(){
		return $this->cards;
	}
	
	/**
	 * Get deck sideboard card list
	 * 
	 * @return CardList - deck sideboard card list
	 */
	public function getSideboard(){
		return $this->sideboard;
	}
    
    /**
     * Get card list with deck and sideboard merged
     * 
     * @return CardList
     */
    public function getAllCards(){
        return CardList::merge($this->cards, $this->sideboard);
    }
    
    /**
     * Get count of mythic rare cards
     * 
     * @return int
     */
    public function getCountM()
    {        
        return $this->cards->getCountM() + $this->sideboard->getCountM();
    }
    
    /**
     * Get count of rare cards
     * 
     * @return int
     */
    public function getCountR()
    {        
        return $this->cards->getCountR() + $this->sideboard->getCountR();
    }
    
    /**
     * Get count of uncommon cards
     * 
     * @return int
     */
    public function getCountU()
    {
        return $this->cards->getCountU() + $this->sideboard->getCountU();
    }
    
    /**
     * Get count of common cards
     * 
     * @return int
     */
    public function getCountC()
    {
        return $this->cards->getCountC() + $this->sideboard->getCountC();
    }
    
    /**
     * Get count of all cards
     * 
     * @return int
     */
    public function getCardCount()
    {
        return $this->cards->getCount() + $this->sideboard->getCount();
    }
    
    /**
     * Return deck as HTML page
     * 
     * @param bool $ratingEnabled - enable or disable column with card rating
     * @param bool $pricesEnabled - enable or disable column with card prices
     * 
     * @return string - output HTML
     */
    public function toHtml($ratingEnabled = false, $pricesEnabled = false)
    {
        // Order cards by rating
        if($ratingEnabled){
            $this->cards->orderByRatingDesc();
            $this->sideboard->orderByRatingDesc();    
        }
        
        // Export deck to HTML
        $template = new Template(TEMPLATE_FILE, CSS_STYLE_FILE, $ratingEnabled, $pricesEnabled);
        $html = $template->createDeckHtml($this);
        return $html;
    }
    
    /**
     * Export deck to HTML file
     * 
     * @param string $exportFile  - name of file to create e.g.: 'deck.html'
     * @param bool $ratingEnabled - enable or disable column with card rating
     * @param bool $pricesEnabled - enable or disable column with card prices
     * 
     * @return void
     */
    public function exportToHtml($exportFile, $ratingEnabled = false, $pricesEnabled = false)
    {
        $html = $this->toHtml($ratingEnabled, $pricesEnabled);
        file_put_contents($exportFile, $html);
    }
}