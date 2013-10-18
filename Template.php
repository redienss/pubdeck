<?php

/**
 * MTG HTML Deck Template class
 * 
 * @author Tomasz Szneider
 */
class Template {
    
    private $template;
    private $cssStyle;
    private $ratingEnabled;
    private $pricesEnabled;

    /**
     * Create HTML CSS section
     * 
     * @param string   $css - css stles
     * 
     * @return string - CSS HTML
     */
    private function createHtmlCssSection($css)
    {        
        $html =
            "\n".'<style>'.
            "\n".$css.
            "\n".'</style>';
            
        return $html; 
    }
            
    
    /**
     * Create HTML deck description, replacing card names with links e.g. [Incinerate]
     * 
     * @param string   $description - deck description from mwDeck file
     * @param CardList $collection  - collection of all cards used for creating card links
     * 
     * @return string - HTML description with card links
     */
    private function createHtmlDescription($description, $collection)
    {        
        // Recognize card names in description     
        preg_match_all("/\[(.*?)\]/", $description, $matches);
        $cardNames = $matches[1];
        
        // Create links for all card names
        foreach ($cardNames as $cardName) {
            $card = $collection->findCardByName($cardName);
            $tooltipLink = $this->createHtmlCardTooltip($card);
            $description = preg_replace("/\[$cardName\]/", $tooltipLink, $description);
        }
        
        // Return description with liks
        return $description; 
    }
            
    /**
     * Create HTML image
     * 
     * @param Card $card - card object
     * 
     * @return string - image html
     */
    private function createHtmlCardImage($card){
        $url = $card->getImgUrlGatherer();
        return '<img class="cardImgGatherer shadow" src="'.$url.'"/>';
    }
    
    /**
     * Create HTML card tooltip
     * 
     * @param Card $card - card object
     * 
     * @return string - tooltip HTML
     */
    private function createHtmlCardTooltip($card)
    {
        // Create card IMG tag    
        $img  = $this->createHtmlCardImage($card);
        $name = $card->getName();
        
        // Return tooltip link
        return '<a class="cardTooltip" href="#">'.$name.'<span>'.$img.'</span></a>';
    }
    
    /**
     * Create HTML mana cost
     * 
     * @param string $manaString - mana string e.g.: {2}{U}
     * 
     * @return string - mana cost HTML
     */
    private function createHtmlManaCost($manaString){
        $manaImg = '<img src="http://gatherer.wizards.com/Handlers/Image.ashx?size=small&name=$1&type=symbol"/>';
        $manaString = preg_replace('/{(.*?)}/', $manaImg, $manaString);
        return $manaString;
    }
    
    /**
     * Create HTML magic set image
     * 
     * @param Card $card - card object
     * 
     * @return string - image html
     */
    private function createHtmlSetImage($set, $rarity){
        $url = 'http://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set='.$set.'&size=small&rarity='.$rarity;
        $html = '<img class="setSymbol shadow" src="'.$url.'"/>';
        return $html; 
    }
    
    /**
     * Create HTML card list row
     * 
     * @param Card $card - card object
     * 
     * @return string - card list row HTML
     */
    private function createHtmlCardListRow($card)
    {
        // Create card IMG tag    
        $tooltipLink = $this->createHtmlCardTooltip($card);
        
        // Get card params
        $count  = $card->getCount();
        $type   = $card->getType();
        $mana   = $card->getMana();
        $set    = $card->getSetCodeGatherer();
        $rarity = $card->getRarity();
       
        // Add card rating
        if($this->ratingEnabled){
            $rating = $card->getRating();
            $rating = number_format($rating, 1);
            $ratingHtml = "\n".'<td>'.$rating.'</td>';
        } else {
            $ratingHtml = '';    
        }

        // Add card price
        if($this->pricesEnabled){
            $price = $card->getMtgNetPrice();
            $have  = $card->getMtgNetCount();
            $price = number_format($price, 2);
            $priceHtml = "\n".'<td>'.$price."&nbsp;(".$have.')</td>';
            if($have==0) $priceHtml = "\n".'<td>-</td>';
        } else {
            $priceHtml = '';    
        }
        
        // Create mana html
        $mana   = $this->createHtmlManaCost($mana);
        
        // Create set image
        $setImg = $this->createHtmlSetImage($set, $rarity);
                
        // Card HTML
        $html = 
            "\n".'<tr>'.
            "\n".'<td>'.$count.'</td>'.
            "\n".'<td>'.$tooltipLink.'</td>'.
            "\n".'<td>'.$type.'</td>'.
            "\n".'<td>'.$mana.'</td>'.
            "\n".'<td>'.$setImg.'</td>'.$ratingHtml.$priceHtml.
            "\n".'</tr>';
            
       // Return card HTML
       return $html;
    }
    
    /**
     * Create HTML card list
     * 
     * @param string   $header        - section header
     * @param CardList $cards         - card list
     * 
     * @return string - card list HTML
     */
    private function createHtmlCardList($header, $cards)
    {            
        // Create rows
        $rowsHtml = '';
        foreach ($cards as $card) {
            $rowsHtml .= $this->createHtmlCardListRow($card); 
        }    
        
        // Get card count
        $cardCount = $cards->getCount();
        
        // Create cardlist HTML
        $html = 
            "\n".'<h3>'.$header.' ('.$cardCount.')</h3>'.
            "\n".'<table class="cardGrid">'.
            "\n".'<colgroup>'.
            "\n".'<col class="col1" />'.
            "\n".'<col class="col2" />'.
            "\n".'<col class="col3" />'.
            "\n".'<col class="col4" />'.
            "\n".'<col class="col5" />'.
            "\n".'</colgroup>'.
            "\n".$rowsHtml.
            "\n".'</table>';
            
        // Return HTML card list
        return $html;
    }
    
    /**
     * Create card HTML gallery image
     * 
     * @param Card $card - card object
     * 
     * @return string - card HTML gallery image
     */
    private function createHtmlGalleryImage($card)
    {
       // Create card IMG tag    
       $img = $this->createHtmlCardImage($card);     
       $pageUrl = $card->getUrlGatherer();   
            
       // Creeate gallery image HTML
       $html = 
           "\n".'<a href="'.$pageUrl.'" target="_blank">'.
           "\n".$img.
           "\n".'</a>';
                    
       // Return gallery image HTML
       return $html;
    }

    /**
     * Create HTML cards gallery
     * 
     * @param CardList $cards - card list object
     * 
     * @return string - gallery HTML
     */
    private function createHtmlGallery($cards)
    {        
        // Create gallery images HTML
        $galleryImages = '';
        foreach ($cards as $card) {
            $galleryImages .= $this->createHtmlGalleryImage($card);
        }    
           
        // Create gallery HTML
        $html =  
            "\n".'<div class="panel trimmed">'.
            "\n".'<h3>Galeria</h3>'.
            "\n".'<hr/>'.
            "\n".'<div class="gallery">'.
            "\n".$galleryImages.                    
            "\n".'</div>'.
            "\n".'</div>';
        
        // Return card gallery
        return $html;
    }
    
    /**
     * Create HTML stats section
     * 
     * @param CardList $cards - card list for stats
     * 
     * @return string - HTML stats section
     */
    private function createHtmlStats($cards)
    {
        // Get stats
        $count  = $cards->getCount();
        $countM = $cards->getCountM();
        $countR = $cards->getCountR();
        $countU = $cards->getCountU();
        $countC = $cards->getCountC();
        
		// Create cardlist HTML
        $html = '';
        $html.= "\n".'<h3>Skład kolekcji ('.$count.' kart)</h3>';
        $html.= "\n".'<ul>';
        if($countM > 0) $html.= "\n".'<li>'.$countM.' x Mythic Rare</li>';
        if($countR > 0) $html.= "\n".'<li>'.$countR.' x Rare</li>';
        if($countU > 0) $html.= "\n".'<li>'.$countU.' x Uncommon</li>';
        if($countC > 0) $html.= "\n".'<li>'.$countC.' x Common</li>';
        $html.= "\n".'</ul><br/>';
    
        // Return card grid HTML
        return $html;
    }
 
    /**
     * Load and create deck template from html file
     * 
     * @param string $templateFile - template file name e.g. 'deck_template.html'
     * @param string $cssStyleFile - CSS style file name e.g. 'mtg_style.css'
     * @param bool   $ratingEnalbed - enable or disable column with card rating
     * @param bool   $pricesEnalbed - enable or disable column with card prices
     * 
     * @return void
     */
    public function Template($templateFile, $cssStyleFile, $ratingEnabled = false, $pricesEnabled = false){
        $this->template = file_get_contents(__DIR__."\\".$templateFile);
        $this->cssStyle = file_get_contents(__DIR__."\\".$cssStyleFile);
        $this->ratingEnabled = $ratingEnabled;
        $this->pricesEnabled = $pricesEnabled;
    }
    
    /**
     * Create deck HTML from this template
     * 
     * @param Deck $deck - Deck object
     * 
     * @return string - deck HTML
     */
    public function createDeckHtml($deck)
    {    
        // Gether data for template
        $cssStyle    = $this->cssStyle;
        $name        = $deck->getName();
        $description = $deck->getDescription();
        $cards       = $deck->getCards();
        $sideboard   = $deck->getSideboard();
        $allCards    = CardList::merge($cards, $sideboard); 
        
        // Create HTML sections
        $htmlCssSection  = $this->createHtmlCssSection($cssStyle);
        $htmlDescription = $this->createHtmlDescription($description, $allCards);
        $htmlCardList    = $this->createHtmlCardList('Karty', $cards);
        $htmlSideboard   = $this->createHtmlCardList('Sideboard', $sideboard);
        $htmlStats       = $this->createHtmlStats($allCards);
        $htmlGallery     = $this->createHtmlGallery($allCards);
        
        // Create deck HTML
        $html = $this->template;
        $html = preg_replace('/<!--STYLE_DEFINITION-->/i' , $htmlCssSection , $html);    
        $html = preg_replace('/<!--DECK_NAME-->/i'        , $name           , $html);
        $html = preg_replace('/<!--DECK_DESCRIPTION-->/i' , $htmlDescription, $html);
        $html = preg_replace('/<!--CARDLIST_SECTION-->/i' , $htmlCardList   , $html);
        $html = preg_replace('/<!--SIDEBOARD_SECTION-->/i', $htmlSideboard  , $html);
        $html = preg_replace('/<!--STATS_SECTION-->/i'    , $htmlStats      , $html);
        $html = preg_replace('/<!--GALLERY_SECTION-->/i'  , $htmlGallery    , $html);    
        
        // Return created HTML
        return $html;
    }
}