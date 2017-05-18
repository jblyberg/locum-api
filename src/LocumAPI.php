<?php

namespace Locum\API;

use Carbon\Carbon;

class LocumAPI
{

    private $guzzle;
    protected $api_config;

    /**
     * LocumAPIProvider constructor.
     */
    public function __construct()
    {
        $this->guzzle = new LocumGuzzle($this->api_config);
    }

    /**
     * Validates email and barcode against the Locum ILS
     *
     * @param $email
     * @param $barcode
     * @return array|mixed
     */
    public function patronValidate($email, $barcode)
    {
        $postVars = array(
            'email'   => $email,
            'barcode' => $barcode,
        );

        return $this->guzzle->postQuery('/patrons/validate/', $postVars);
    }

    /**
     * Grabs patron circulation data for given provider
     *
     * @param $patronID
     * @param null $provider
     * @return array|mixed
     */
    public function patronCirculation($patronID, $provider = null)
    {
        $parameters = array();

        if ($provider) {
            $parameters = ['provider' => $provider];
        }

        return $this->guzzle->getQuery('/patrons/' . $patronID . '/circulation/', $parameters);
    }

    /**
     * Grabs all patron circulation data
     *
     * @param $patronID
     * @return array|null
     */
    public function patronCirculationAll($patronID)
    {
        $promisesArray = [
            'items'       => '/patrons/' . $patronID . '/circulation/',
            'overdrive'   => '/patrons/' . $patronID . '/circulation/?provider=overdrive',
            'bibliotheca' => '/patrons/' . $patronID . '/circulation/?provider=mmm',
        ];

        return $this->guzzle->getConcurrent($promisesArray);
    }

    /**
     * Renews a physical checkout
     *
     * @param $patronID
     * @param array $items
     * @return array|mixed
     */
    public function patronRenewItems($patronID, $items = array())
    {
        return $this->guzzle->postQuery('/patrons/' . $patronID . '/renew/', ['items' => $items]);
    }

    /**
     * Renews all physical checkouts
     *
     * @param $patronID
     * @return array|mixed
     */
    public function patronRenewItemsAll($patronID)
    {
        return $this->guzzle->postQuery('/patrons/' . $patronID . '/renew_all/');
    }

    /**
     * Checks out content or items to patron
     *
     * @param $patronID
     * @param $provider
     * @param $itemID
     * @return array|mixed
     */
    public function patronCheckout($patronID, $provider, $itemID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/checkout/' . $parameter, ['provider_id' => $itemID]);
    }

    /**
     * Checks in content for patron
     *
     * @param $patronID
     * @param $provider
     * @param $itemID
     * @return array|mixed
     */
    public function patronCheckin($patronID, $provider, $itemID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/checkin/' . $parameter, ['provider_id' => $itemID]);
    }

    /**
     * Place a hold on content or items to patron
     *
     * @param $patronID
     * @param $provider
     * @param $itemID
     * @return array|mixed
     */
    public function patronPlaceHold($patronID, $provider, $itemID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;
        $itemRequestArray = ['provider_id' => $itemID];
        if (!$parameter) {
            $itemRequestArray = ['ils_id' => $itemID];
        }

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/place_hold/' . $parameter, $itemRequestArray);
    }

    /**
     * Cancels a patron hold
     *
     * @param $patronID
     * @param $provider
     * @param $itemID
     * @return array|mixed
     */
    public function patronCancelHold($patronID, $provider, $itemID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/cancel_hold/' . $parameter, ['provider_id' => $itemID]);
    }

    /**
     * Suspends a patron's hold
     *
     * @param $patronID
     * @param $provider
     * @param $requestID
     * @return array|mixed
     */
    public function patronSuspendHold($patronID, $provider, $requestID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;
        $carbon = new Carbon();
        $postVars = array(
            'request_id'      => $requestID,
            'activation_date' => $carbon->addYear()->toIso8601String()
        );

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/suspend_hold/' . $parameter, $postVars);
    }

    /**
     * Suspends all of a patron's suspended holds
     *
     * @param $patronID
     * @param $provider
     * @return array|mixed
     */
    public function patronSuspendAllHolds($patronID, $provider)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        $carbon = new Carbon();
        $postVars = array(
            'activation_date' => $carbon->addYear()->toIso8601String()
        );

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/suspend_all_holds/' . $parameter, $postVars);
    }

    /**
     * Reactivates a patron's suspended hold
     *
     * @param $patronID
     * @param $provider
     * @param $requestID
     * @return array|mixed
     */
    public function patronReactivateHold($patronID, $provider, $requestID)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/reactivate_hold/' . $parameter, ['request_id' => $requestID]);
    }

    /**
     * Reactivates all of a patron's suspended holds
     *
     * @param $patronID
     * @param $provider
     * @return array|mixed
     */
    public function patronReactivateAllHolds($patronID, $provider)
    {
        $provider = ($provider == 'bibliotheca') ? 'mmm' : $provider;
        $parameter = ($provider == 'items') ? null : '?provider=' . $provider;

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/reactivate_all_holds/' . $parameter);
    }

    /**
     * Get Patron's fines and fees
     *
     * @param $patronID
     * @return array|mixed
     */
    public function patronFees($patronID)
    {
        return $this->guzzle->getQuery('/patrons/' . $patronID . '/fines/');
    }

    /**
     * Retrieves a patron's account balance (envisionware)
     *
     * @param $patronID
     * @return array
     */
    public function patronBalance($patronID)
    {
        return $this->guzzle->getQuery('/patrons/' . $patronID . '/balance/');
    }

    /**
     * Adds a work to patron's wish list
     *
     * @param $patronID
     * @param $workID
     * @return array
     */
    public function wishListAdd($patronID, $workID)
    {
        $postVars = array(
            'patron_id' => $patronID,
            'work_id'   => $workID,
        );

        return $this->guzzle->postQuery('/wishes/', $postVars);
    }

    /**
     * Retrieves a patron's wish list
     *
     * @param $patronID
     * @param null $page
     * @param int $limit
     * @return array
     */
    public function wishListGet($patronID, $page = null, $limit = null)
    {
        $offset = 0;
        if ($page && ($page > 1)) {
            $offset = $limit * ($page - 1);
        }

        $parameters = ['patron_id' => $patronID, 'offset' => $offset, 'limit' => $limit];

        return $this->guzzle->getQuery('/wishes/', $parameters);
    }

    /**
     * Delete a  wish
     *
     * @param $patronID
     * @param $wishID
     * @return array
     */
    public function wishDelete($patronID, $wishID)
    {
        $deleteVars = array(
            'patron_id' => $patronID,
        );

        return $this->guzzle->deleteQuery('/wishes/' . $wishID . '/', $deleteVars);
    }


    /**
     * Returns patron checkout history
     *
     * @param $patronID
     * @param null $page
     * @param null $limit
     * @return array|mixed
     */
    public function patronHistoryGet($patronID, $page = null, $limit = null)
    {
        $offset = 0;
        if ($page && ($page > 1)) {
            $offset = $limit * ($page - 1);
        }

        $parameters = ['offset' => $offset, 'limit' => $limit];

        return $this->guzzle->getQuery('/patrons/' . $patronID . '/history/', $parameters);
    }

    /**
     * Update patron History
     *
     * @param $patronID
     * @return array
     */
    public function patronHistoryUpdate($patronID)
    {
        $postVars = [];

        return $this->guzzle->postQuery('/patrons/' . $patronID . '/update_history/', $postVars);

    }

    /**
     * Delete a  wish
     *
     * @param $patronID
     * @param $checkoutID
     * @return array
     */
    public function historyDelete($patronID, $checkoutID)
    {
        $deleteVars = array(
            'patron_id' => $patronID,
        );

        return $this->guzzle->deleteQuery('/checkouts/' . $checkoutID . '/', $deleteVars);
    }

    /**
     * Delete a  wish
     *
     * @param $patronID
     * @return array
     */
    public function historyPurge($patronID)
    {
        $deleteVars = array(
            'patron_id' => $patronID,
        );

        return $this->guzzle->deleteQuery('/checkouts/purge/', $deleteVars);
    }

    /**
     * Returns patron profile information
     *
     * @param $patronID
     * @return array
     */
    public function patronProfileGet($patronID)
    {
        return $this->guzzle->getQuery('/patrons/' . $patronID . '/');
    }

    /**
     * Returns patron profile information
     *
     * @param $patronID
     * @return array
     */
    public function patronProfileSet($patronID, $patchVars)
    {
        return $this->guzzle->patchQuery('/patrons/' . $patronID . '/', $patchVars);
    }

    /**
     * Performs a catalog search against the Locum API
     *
     * @param $searchType
     * @param $searchTerm
     * @param null $page
     * @param null $limit
     * @param array $facets
     * @return array
     */
    public function catalogSearch($searchType, $searchTerm, $page = null, $limit = null, $facets = [])
    {

        $offset = 0;
        if ($page && ($page > 1)) {
            $offset = $limit * ($page - 1);
        }

        $postVars = array(
            'query'  => $searchTerm,
            'limit'  => $limit,
            'offset' => $offset,
            'type'   => $searchType,
            'facets' => $facets,
        );

        return $this->guzzle->postQuery('/search/', $postVars);
    }

    /**
     * Returns availability for an array of work IDs
     *
     * @param $worksArray
     * @return array|null
     */
    public function worksAvailability($worksArray)
    {
        $promisesArray = array();
        foreach ($worksArray as $workID) {
            $promisesArray[$workID] = '/works/' . $workID . '/availability/';
        }

        return $this->guzzle->getConcurrent($promisesArray);
    }

    /**
     * Uses the booklist endpoint to grab an object of specific works
     *
     * @param $worksArray
     * @return array
     */
    public function getWorks($worksArray)
    {
        $postVars = array(
            'work_ids' => $worksArray,
        );

        return $this->guzzle->postQuery('/search/booklist/', $postVars);
    }

    /**
     * Returns all work and sub-bib record information
     *
     * @param $workID
     * @param bool $long
     * @return array
     */
    public function getWork($workID, $long = true)
    {
        $append = $long ? 'long/' : null;

        return $this->guzzle->getQuery('/works/' . $workID . '/' . $append);
    }

    /**
     * Gets availability for an array of bib IDs
     *
     * @param $bibArray
     * @param null $limitFormat
     * @return array|null
     */
    public function bibAvailability($bibArray, $limitFormat = null)
    {
        if (!count($bibArray)) {
            return array();
        }
        $promisesArray = array();
        foreach ($bibArray as $bibID) {
            $promisesArray[$bibID] = '/bibs/' . $bibID . '/availability/';
        }

        return $this->guzzle->getConcurrent($promisesArray);
    }

    /* ------------------- Covercache and Recommendation Server Methods ------------------- */

    /**
     * Returns a work cover
     *
     * @param $workID
     * @param int $size
     * @return null
     */
    public function workCover($workID, $size = 200)
    {
        $covers = $this->guzzle->getQuery('/works/' . $workID . '/');
        $base_uri = $this->guzzle->getBaseURI();

        if (isset($covers['covers'])) {
            foreach ($covers['covers'] as $cover) {
                if ($cover['width'] == $size) {
                    $cover['base_uri'] = $base_uri . '/';

                    return $cover;
                }
            }
        }

        return null;
    }

    /**
     * Returns all available work covers for a work
     *
     * @param $workID
     * @return array|mixed
     */
    public function workCoversAll($workID)
    {
        return $this->guzzle->getQuery('/works/' . $workID . '/');
    }

    /**
     * Returns recommendation object for a given work
     *
     * @param $workID
     * @return array|mixed
     */
    public function workRecommend($workID)
    {
        $result = $this->guzzle->getQuery('/works/' . $workID . '/recommendations/');
        $result['base_uri'] = $this->guzzle->getBaseURI() . '/';

        return $result;
    }

    /**
     * Updates a work cover with given image URL
     *
     * @param $work_id
     * @param $url
     * @return array|mixed
     */
    public function updateWorkCover($work_id, $url)
    {
        $postVars = array(
            'url' => $url,
        );

        return $this->guzzle->postQuery('/works/' . $work_id . '/override/', $postVars);
    }

    /* ------------------- Overdrive API Methods ------------------- */

    /**
     * Returns the full preview script for a given $providerID
     *
     * @param $providerID
     * @return array|mixed|\Psr\Http\Message\StreamInterface
     */
    public function getPreviewScript($providerID)
    {
        $parameters = ['crid' => $providerID];

        return $this->guzzle->getQuery('/media/sample-embed/', $parameters, false);

    }

}