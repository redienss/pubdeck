<?php

require_once "Deck.php";
require_once "ConfigManager.php";

define('WEBAPI_CONSTS_INI', 'Config\webapi_consts.ini');
define('WEBAPI_ENV_TEST', 'Config\webapi_env_test.ini');
define('WEBAPI_ENV_PROD', 'Config\webapi_env_prod.ini');

/**
 * MTG Allegro WebAPI Client
 * 
 * @author Tomasz Szneider
 */
class AllegroClient {
    
    // Fields
    private $debug;
    private $soapClient;
    private $wsdl;
    private $countryId;
    private $webApiKey;
    private $userLogin;
    private $userPassword;
    private $versionKey;
    private $sessionId;
    
    /**
     * Get version key from Allegro WebAPI
     * 
     * @return string - version key
     */
    private function getVersionKey()
    {
        // Request for doQuerySysStatus
        $request = array(
           'sysvar'     => 1,
           'country-id' => $this->countryId,
           'webapi-key' => $this->webApiKey
        );
        
        // Debug
        if($this->debug){
            echo "doQuerySysStatus(...)\n";
        }
        
        // Get version key
        $response = $this->soapClient->__soapCall("doQuerySysStatus", $request);
        
        // Return version key
        return $response['ver-key'];
    }
    
    /**
     * Login current user
     * 
     * @return string - session id
     */
    private function login()
    {
        // Request for doLogin
        $request = array(
           'user-login'    => $this->userLogin,
           'user-password' => $this->userPassword,
           'country-code'  => $this->countryId,
           'webapi-key'    => $this->webApiKey,
           'local-version' => $this->versionKey
        );
        
        // Debug
        if($this->debug){
            echo "doLogin(...)\n";
        }    
        
        // Logon user
        $response = $this->soapClient->__soapCall("doLogin", $request);
        
        // Get session ID
        return $response['session-handle-part'];
    }
    
    /**
     * Create offer field structure for doNewAuctionExt WebAPI method
     * 
     * @param int    $id    - field ID
     * @param string $key   - field key e.g.: 'fvalue-string'
     * @param mixed  $value - field value e.g.: 'Vampire Deck'
     * 
     * @return array - doNewAuctionExt offer field structure
     */
    private function createFid($id, $key, $value)
    {
        $field = array(
            'fid' => $id, 
            'fvalue-string' => '',
            'fvalue-int' => 0,
            'fvalue-float' => 0,
            'fvalue-image' => 0,
            'fvalue-datetime' => 0,
            'fvalue-date' => '',
            'fvalue-range-int' => array(
                'fvalue-range-int-min' => 0,
                'fvalue-range-int-max' => 0
            ),
            'fvalue-range-float' => array(
                'fvalue-range-float-min' => 0,
                'fvalue-range-float-max' => 0
            ),
            'fvalue-range-date' => array(
                'fvalue-range-date-min' => '',
                'fvalue-range-date-max' => ''
            )
        );
        
        $field[$key] = $value;
        
        return $field;   
    }
    
    /**
     * Create MTG Allegro WebAPI Client
     * 
     * @param string $environment - execution environment (WEBAPI_ENV_TEST|WEBAPI_ENV_PROD)
     * @param bool   $debug       - debug enabled?
     *               
     * @return void
     */
    public function AllegroClient($environment, $debug = false)
    {
        // Load configs
        ConfigManager::load(WEBAPI_CONSTS_INI);
        ConfigManager::load($environment);
        
        // Set params
        $this->debug        = $debug;
        $this->wsdl         = WEBAPI_WSDL;
        $this->userLogin    = WEBAPI_LOGIN;
        $this->userPassword = WEBAPI_PASSWORD;
        $this->webApiKey    = WEBAPI_KEY;
        $this->countryId    = WEBAPI_COUNTRY;
        
        // Create Soap Client
        $this->soapClient = new SoapClient($this->wsdl);
        
        // Set version key
        $this->versionKey = $this->getVersionKey();
        
        // Login and set session handle
        $this->sessionId  = $this->login();
    }
    
    /**
     * Create deck auction on Allegro via WebAPI
     * 
     * @param Deck $deck  - deck object
     * 
     * @return void 
     */
    public function createDeckAuction($deck)
    {
        // Get deck params
        $deckName = $deck->getName();
        $deckPrice = $deck->getPirce();
        $deckPhotos = $deck->getPhotos();
        $deckHtml = $deck->toHtml();
        
        // Init photos
        $idx = 0;
        $fidPhotos = array(null, null, null, null, null, null, null, null);
        
        // Get content of each phtot
        foreach($deckPhotos as $link) {
            $fidPhotos[$idx++] = file_get_contents($link); 
        }
        
        // Request for doNewAuctionExt
        $request = array(
            'session-handle' => $this->sessionId,
            'fields' => array(
                $this->createFid(FID_ITEM_NAME,                    'fvalue-string', $deckName),
                $this->createFid(FID_CATEGORY,                     'fvalue-int',    OFFER_CATEGORY),
                $this->createFid(FID_DURATION,                     'fvalue-int',    OFFER_DURATION),
                $this->createFid(FID_ITEM_COUNT,                   'fvalue-int',    OFFER_ITEM_COUNT),
                $this->createFid(FID_BUY_NOW_PRICE,                'fvalue-float',  $deckPrice),
                $this->createFid(FID_COUNTRY,                      'fvalue-int',    WEBAPI_COUNTRY),
                $this->createFid(FID_STATE,                        'fvalue-int',    OFFER_STATE),
                $this->createFid(FID_CITY,                         'fvalue-string', OFFER_CITY),
                $this->createFid(FID_SHIPMENT_PAYER,               'fvalue-int',    OFFER_SHIPMENT_PAYER),
                $this->createFid(FID_PAYMENT_FORM,                 'fvalue-int',    OFFER_PAYMENT_FORM),
                $this->createFid(FID_PROMO_OPRIONS,                'fvalue-int',    OFFER_PROMO),
                $this->createFid(FID_PHOTO_1,                      'fvalue-image',  $fidPhotos[0]),
                $this->createFid(FID_PHOTO_2,                      'fvalue-image',  $fidPhotos[1]),
                $this->createFid(FID_PHOTO_3,                      'fvalue-image',  $fidPhotos[2]),
                $this->createFid(FID_PHOTO_4,                      'fvalue-image',  $fidPhotos[3]),
                $this->createFid(FID_PHOTO_5,                      'fvalue-image',  $fidPhotos[4]),
                $this->createFid(FID_PHOTO_6,                      'fvalue-image',  $fidPhotos[5]),
                $this->createFid(FID_PHOTO_7,                      'fvalue-image',  $fidPhotos[6]),
                $this->createFid(FID_PHOTO_8,                      'fvalue-image',  $fidPhotos[7]),
                $this->createFid(FID_ITEM_DESCRIPTION,             'fvalue-string', $deckHtml),
                $this->createFid(FID_OFFER_TYPE,                   'fvalue-int',    OFFER_TYPE),
                $this->createFid(FID_POST_CODE,                    'fvalue-string', OFFER_POST_CODE),
				$this->createFid(FID_BANK_ACCOUNT_1,               'fvalue-string', OFFER_BANK_ACCOUNT),
                $this->createFid(FID_SHIP_POST_SPEC_LETTER_PRIO_1, 'fvalue-float',    OFFER_SHIPMENT_COST)
            ),
            'item-template-id' => 0,
            'local-id' => 0,
            'item-template-create' => array(
                'item-template-option' => 0,
                'item-template-name' => ''
            )
        );
        
        // Debug method execution
        if($this->debug){
            echo "doNewAuctionExt(...)\n\n";    
        }
        
        // Create new auction
        $response = $this->soapClient->__soapCall("doNewAuctionExt", $request);   
        
        // Debug info
        if($this->debug){
            echo "Item ID:     " . $response['item-id'] . "\n";
            echo "Fee charged: " . $response['item-info'] . "\n\n";
        } 
    }
    
}