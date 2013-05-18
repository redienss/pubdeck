<?php

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
           'sysvar'    => 1,
           'countryId' => $this->countryId,
           'webapiKey' => $this->webApiKey
        );
        
        // Debug
        if($this->debug){
            echo "doQuerySysStatus(...)\n";
        }
        
        // Get version key
        $response = $this->soapClient->doQuerySysStatus($request);
        
        // Return version key
        return $response->verKey;
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
           'userLogin'    => $this->userLogin,
           'userPassword' => $this->userPassword,
           'countryCode'  => $this->countryId,
           'webapiKey'    => $this->webApiKey,
           'localVersion' => $this->versionKey
        );
        
        // Debug
        if($this->debug){
            echo "doLogin(...)\n";
        }    
        
        // Logon user
        $response = $this->soapClient->doLogin($request);
        
        // Get session ID
        return $response->sessionHandlePart;
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
            'fvalueString' => '',
            'fvalueInt' => 0,
            'fvalueFloat' => 0,
            'fvalueImage' => 0,
            'fvalueDatetime' => 0,
            'fvalueDate' => '',
            'fvalueRangeInt' => array(
                'fvalueRangeIntMin' => 0,
                'fvalueRangeIntMax' => 0
            ),
            'fvalueRangeFloat' => array(
                'fvalueRangeFloatMin' => 0,
                'fvalueRangeFloatMax' => 0
            ),
            'fvalueRangeDate' => array(
                'fvalueRangeDateMin' => '',
                'fvalueRangeDateMax' => ''
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
            'sessionHandle' => $this->sessionId,
            'fields' => array(
                $this->createFid(FID_ITEM_NAME,                    'fvalueString', $deckName),
                $this->createFid(FID_CATEGORY,                     'fvalueInt',    OFFER_CATEGORY),
                $this->createFid(FID_DURATION,                     'fvalueInt',    OFFER_DURATION),
                $this->createFid(FID_ITEM_COUNT,                   'fvalueInt',    OFFER_ITEM_COUNT),
                $this->createFid(FID_BUY_NOW_PRICE,                'fvalueFloat',  $deckPrice),
                $this->createFid(FID_COUNTRY,                      'fvalueInt',    WEBAPI_COUNTRY),
                $this->createFid(FID_STATE,                        'fvalueInt',    OFFER_STATE),
                $this->createFid(FID_CITY,                         'fvalueString', OFFER_CITY),
                $this->createFid(FID_SHIPMENT_PAYER,               'fvalueInt',    OFFER_SHIPMENT_PAYER),
                $this->createFid(FID_PAYMENT_FORM,                 'fvalueInt',    OFFER_PAYMENT_FORM),
                $this->createFid(FID_PROMO_OPRIONS,                'fvalueInt',    OFFER_PROMO),
                $this->createFid(FID_PHOTO_1,                      'fvalueImage',  $fidPhotos[0]),
                $this->createFid(FID_PHOTO_2,                      'fvalueImage',  $fidPhotos[1]),
                $this->createFid(FID_PHOTO_3,                      'fvalueImage',  $fidPhotos[2]),
                $this->createFid(FID_PHOTO_4,                      'fvalueImage',  $fidPhotos[3]),
                $this->createFid(FID_PHOTO_5,                      'fvalueImage',  $fidPhotos[4]),
                $this->createFid(FID_PHOTO_6,                      'fvalueImage',  $fidPhotos[5]),
                $this->createFid(FID_PHOTO_7,                      'fvalueImage',  $fidPhotos[6]),
                $this->createFid(FID_PHOTO_8,                      'fvalueImage',  $fidPhotos[7]),
                $this->createFid(FID_ITEM_DESCRIPTION,             'fvalueString', $deckHtml),
                $this->createFid(FID_OFFER_TYPE,                   'fvalueInt',    OFFER_TYPE),
                $this->createFid(FID_POST_CODE,                    'fvalueString', OFFER_POST_CODE),
                $this->createFid(FID_BANK_ACCOUNT_1,               'fvalueString', OFFER_BANK_ACCOUNT),
                $this->createFid(FID_SHIP_POST_SPEC_LETTER_PRIO_1, 'fvalueFloat',  OFFER_SHIPMENT_COST)
            ),
            'itemTemplateId' => 0,
            'localId' => 0,
            'itemTemplateCreate' => array(
                'itemTemplateOption' => 0,
                'itemTemplateName' => ''
            )
        );
        
        // Debug method execution
        if($this->debug){
            echo "doNewAuctionExt(...)\n\n";    
        }
        
        // Create new auction
        $response = $this->soapClient->doNewAuctionExt($request);   
        
        // Debug info
        if($this->debug){
            echo "Item ID:     " . $response->itemId . "\n";
            echo "Fee charged: " . $response->itemInfo . "\n\n";
        } 
    }
    
}