<?php

require_once 'ConfigManager.php';
require_once 'MtgNetClient.php';

// Load MySQL config
ConfigManager::load('Config\mysql.ini');

/**
 * MTG Card class
 * 
 * @author Tomasz Szneider
 */
class Card {

    private $id;
    private $name;
    private $setCode;
    private $setCodeMtgNet;
    private $setCodeGatherer;
    private $count;
    private $mana;
    private $type;
    private $rarity;
    private $rating;
    private $artist;
    private $mtgNetCache;

    /**
     * Load card data from mtg_db, data is stored in object fields
     * 
     * @param string $name - card name
     * @param string $set  - card set
     * 
     * @return array
     */
    private function loadDataFromDB($name, $set) {
        // Convert special chars
        $name = addslashes($name);

        // Connect to MySQL
        $con = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);
        mysql_select_db(MYSQL_DB_NAME, $con);

        // Select card
        $query = "select nid, nname, nset, ntype, nrarity, nmanacost, nrating, nartist from ncards where nset='$set' and nname='$name'";
        $result = mysql_query($query, $con);

        // When no result
        if ($result != false) {
            // Fetch data
            $row = mysql_fetch_array($result);

            // Set data in object
            $this->id = (int) $row["nid"];
            $this->name = $row["nname"];
            $this->setCode = $row["nset"];
            $this->type = $row["ntype"];
            $this->rarity = $row["nrarity"];
            $this->mana = $row["nmanacost"];
            $this->rating = round((float) $row["nrating"], 1);
            $this->artist = $row["nartist"];
        }

        // Get alternate set names
        $query = "select ncode_mtgnet, ncode_gatherer from nsets where ncode='$set'";
        $result = mysql_query($query, $con);

        // When no result
        if ($result != false) {
            // Fetch data
            $row = mysql_fetch_array($result);

            // Set data in object
            $this->setCodeMtgNet = $row["ncode_mtgnet"];
            $this->setCodeGatherer = $row["ncode_gatherer"];
        }

        // Close connection
        mysql_close($con);
    }

    /**
     * Get card name in zymic format e.g.: Mountain (1) = Mountain1
     *  
     * @return string - card name in zymic format
     */
    private function getZymicName() {
        return preg_replace('/(.*)\s\(([0-9])\)/', '$1$2', $this->name);
    }

    /**
     * Compare two cards (ascending) by Gatherer rating
     * 
     * Returns
     *    -1 - rating1 < rating2
     *     0 - rating1 = rating2
     *     1 - rating1 > rating2
     * 
     * @param Card $card1 - card to compare
     * @param Card $card2 - card to compare
     * 
     * @return int
     */
    public static function compareByRatingAsc($card1, $card2) {
        $r1 = $card1->getRating();
        $r2 = $card2->getRating();

        if ($r1 == $r2) {
            return 0;
        }
        return ($r1 < $r2) ? -1 : 1;
    }

    /**
     * Compare two cards (descending) by Gatherer rating
     * 
     * Returns
     *    -1 - rating1 > rating2
     *     0 - rating1 = rating2
     *     1 - rating1 < rating2
     * 
     * @param Card $card1 - card to compare
     * @param Card $card2 - card to compare
     * 
     * @return int
     */
    public static function compareByRatingDesc($card1, $card2) {
        $r1 = $card1->getRating();
        $r2 = $card2->getRating();

        if ($r1 == $r2) {
            return 0;
        }
        return ($r1 < $r2) ? 1 : -1;
    }

    /**
     * Construct new card, additional info is auto-loaded from mtg_db
     * 
     * @param string $name  - card name
     * @param string $set   - card set
     * @param string $count - card count
     * 
     * @return void
     */
    public function Card($name, $set, $count) {
        $this->loadDataFromDB($name, $set);
        $this->count = $count;
    }

    /**
     * Get card Multiverse ID
     * 
     * @return int - card multiverse id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get card name
     * 
     * @return string - card name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get standard set code
     * 
     * @return string
     */
    public function getSetCode() {
        return $this->setCode;
    }

    /**
     * Get MtgNet set code
     * 
     * @return string
     */
    public function getSetCodeMtgNet() {
        return $this->setCodeMtgNet;
    }

    /**
     * Get Gatherer set code
     * 
     * @return string
     */
    public function getSetCodeGatherer() {
        return $this->setCodeGatherer;
    }

    /**
     * Get card count
     * 
     * @return int - card count
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Get card mana cost in format e.g.: {2}{U}
     * 
     * @return string - card mana cost
     */
    public function getMana() {
        return $this->mana;
    }

    /**
     * Get card type
     * 
     * @return string - card type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get card rarity, one of: (M, R, U, C)
     * 
     * @return string - card rarity
     */
    public function getRarity() {
        return $this->rarity;
    }

    /**
     * Get Gatherer card rating [0,5]
     * 
     * @return float - card rating
     */
    public function getRating() {
        return $this->rating;
    }

    /**
     * Get Artist Name
     * 
     * @return string
     */
    public function getArtist() {
        return $this->artist;
    }

    /**
     * Set card count
     * 
     * @param int $count - card count to set
     * 
     * @return void
     */
    public function setCount($count) {
        $this->count = $count;
    }

    /**
     * Get gatherer url to card info
     * 
     * @return string - card image url
     */
    public function getUrlGatherer() {
        return 'http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=' . $this->id;
    }

    /**
     * Get gatherer url to card image
     * 
     * @return string - card image url
     */
    public function getImgUrlGatherer() {
        return 'http://gatherer.wizards.com/Handlers/Image.ashx?multiverseid=' . $this->id . '&type=card';
    }

    /**
     * Get zymic url to card image
     * 
     * @return string - card image url
     */
    public function getImgUrlZymic() {
        return 'http://redienss.zxq.net/pics/' . $this->setCode . '/' . $this->getZymicName() . '.full.jpg';
    }

    /**
     * Get card image form gatherer
     * 
     * @return string - image binary data
     */
    public function getImgGatherer() {
        return file_get_contents($this->getImgUrlGatherer());
    }

    /**
     * Get card image from zymic
     * 
     * @return string - image binary data
     */
    public function getImgZymic() {
        return file_get_contents($this->getImgUrlZymic());
    }

    /**
     * Get MtgNet price
     * 
     * @return float
     */
    public function getMtgNetPrice() {
        // Search card on MtgNet
        if (empty($this->mtgNetCache)) {
            $client = new MtgNetClient();
            $this->mtgNetCache = $client->searchAggregated($this->name);
        }

        // Return card price
        return $this->mtgNetCache['price'];
    }

    /**
     * Get MtgNet card count
     * 
     * @return int
     */
    public function getMtgNetCount() {
        // Search card on MtgNet
        if (empty($this->mtgNetCache)) {
            $client = new MtgNetClient();
            $this->mtgNetCache = $client->searchAggregated($this->name);
        }

        // Return card price
        return $this->mtgNetCache['count'];
    }

    /**
     * Is this card mythic rare?
     * 
     * @return bool
     */
    public function isMythicRare() {
        return $this->rarity == "M";
    }

    /**
     * Is this card rare?
     * 
     * @return bool
     */
    public function isRare() {
        return $this->rarity == "R";
    }

    /**
     * Is this card uncommon?
     * 
     * @return bool
     */
    public function isUncommon() {
        return $this->rarity == "U";
    }

    /**
     * Is this card common?
     * 
     * @return bool
     */
    public function isCommon() {
        return $this->rarity == "C";
    }

}