<?php

ConfigManager::load('Config\settings.ini');

/**
 * MTG Script Class
 * 
 * @author Tomasz Szneider
 */
class Script {
    
    private $argv;
    
    /**
     * Construct Script
     * 
     * @param array $argv - argv array
     * 
     * @return void
     */
    public function Script($argv){
        $this->argv = $argv;
    }
    
    /**
     * Execute this script
     * 
     * @return void
     */
    public function execute()
    {
        // No params, show help
        if($this->noParamsGiven()){
            $this->showHelp();
        }
        
        // Show help
        if($this->isParamGiven('-h', '--help')){
           $this->showHelp();
        } 
        
        // Get param with deck name
        if($this->isParamGiven('.*[.]mwDeck')){
            $deckFile = $this->getParam('.*[.]mwDeck');
        } else {
            $this->showHelp();   
        }
        
        // Get env param
        if($this->isParamGiven('-t', '--test')){
            $webApiEnv = AllegroClient::WEBAPI_ENV_TEST;
        } else 
        if($this->isParamGiven('-p', '--prod')){
            $webApiEnv = AllegroClient::WEBAPI_ENV_PROD;
        } else {
            $this->showHelp();
        }
        
        // Card rating param
        if($this->isParamGiven('-r', '--rating')){
           $ratingEnabled = true;
        } else {
           $ratingEnabled = false;
        }
        
        // Card prices param
        if($this->isParamGiven('-c', '--prices')){
           $pricesEnabled = true;
        } else {
           $pricesEnabled = false;
        }
        
        // Load deck from file
        $deck = new Deck($deckFile);
        
        // Export deck preview to HTML file
        $previewFile = preg_replace('/'.DECK_EXTENSION.'/', PAGE_EXTENSION, $deckFile);
        $deck->exportToHtml($previewFile, $ratingEnabled, $pricesEnabled);
        
        // Show deck info
        echo "\n"."Name:      ".$deck->getName();
        echo "\n"."Price:     ".$deck->getPirce();
        echo "\n"."Mythic:    ".$deck->getCountM();
        echo "\n"."Rare:      ".$deck->getCountR();
        echo "\n"."Uncommon:  ".$deck->getCountU();
        echo "\n"."Common:    ".$deck->getCountC();
        echo "\n"."All cards: ".$deck->getCardCount();
        echo "\n";
        echo "\n"."Preview:   ".$previewFile;
        echo "\n";
        
        // Confirm creating new auction
        $this->showConfirmation('Do you want create new auction?');
        
        echo "\n";
        
        // Create new auction via WebAPI
        $client = new AllegroClient($webApiEnv, true);
        $client->createDeckAuction($deck);      
    }
    
    /**
     * Get script param count
     * 
     * @return int
     */
    public function getParamCount(){
        return count($this->argv)-1;
    }
    
    /**
     * Check if no params given
     * 
     * @return bool
     */
    public function noParamsGiven(){
        return $this->getParamCount() == 0;
    }    
        
    /**
     * Check if param is given in script arguments
     * 
     * @param string $regex1 - regex matching parameter e.g.: '-t'
     * @param string $regex2 - regex matching parameter e.g.: '--test'
     * 
     * @return bool
     */
    public function isParamGiven($regex1 = null, $regex2 = null){
        
        foreach ($this->argv as $param) {
            if($regex1 && preg_match("/$regex1/", $param)) return true;
            if($regex2 && preg_match("/$regex2/", $param)) return true;        
        }
    
        return false;
    }
    
    /**
     * Return script param matching given regex
     * 
     * @param string $regex - regex matching param
     * 
     * @return string
     */
    public function getParam($regex){
        
        foreach ($this->argv as $param) {
            if(preg_match("/$regex/", $param)) return $param;        
        }
        
        return null;
    }
    
    /**
     * Show help and exit
     * 
     * @return void
     */
    public function showHelp(){
        
        echo "\n";
        
        echo "----------------------------------\n";
        echo "Deck Publisher 2.0\n";
        echo "----------------------------------\n";
        echo "MTG tool publishing Magic Workstation decks via Allegro WebAPI\n\n";
        
        echo "Usage:\n\n";
        echo "   pubdeck [--params] example.mwDeck\n\n";
    
        echo "Params:\n\n";
        echo "   -h --help     show this help\n";
        echo "   -t --test     execute on test environment\n";
        echo "   -p --prod     execute on production environment\n";
        echo "   -r --rating   add column with Gatherer card rating\n";
        echo "   -c --prices   show card prices from MtgStore\n\n";   
        
        echo "Examples:\n\n";
        echo "   pubdeck --test example.mwDeck\n";
        echo "   pubdeck --prod example.mwDeck\n";
        echo "   pubdeck --test --rating example.mwDeck\n\n";
        
        exit();
    }

    /**
     * Show confirmation waiting for user response: (Y/N)
     * 
     * @param string $message - confirmation message
     * 
     * @return void
     */
    public function showConfirmation($message)
    {
        echo "\n$message (Y/N): ";
        while (true) {
            $c = fread(STDIN, 1);
            if($c == 'Y' || $c == 'y') break;
            if($c == 'N' || $c == 'n') die();
        }   
    }
}