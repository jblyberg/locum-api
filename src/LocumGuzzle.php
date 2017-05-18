<?php

namespace Locum\API;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;

class LocumGuzzle
{

    private $client;
    private $api_config;

    public function __construct($api_config)
    {
        $this->api_config = $api_config;
        $timeout = $api_config['timeout'];
        $base_uri = $this->getBaseURI();

        $this->client = new Client([
            'base_uri' => $base_uri,
            'timeout'  => $timeout,
        ]);
    }

    public function getBaseURI()
    {
        $protocol = 'http://';
        $server = $this->api_config['server'];
        $port = $this->api_config['port'];

        if ($this->api_config['ssl']) {
            $protocol = 'https://';
            $port = $this->api_config['ssl_port'];
        }

        $base_uri = $protocol . $server . ':' . $port;

        return $base_uri;
    }

    public function getQuery($apiMethod, $parameters = array(), $json = true)
    {
        try {
            $response = $this->client->get(
                $apiMethod,
                ['query' => $parameters]
            )->getBody();
        } catch (RequestException $exception) {
            return array('exception' => $exception);
        }

        if ($json) {
            return json_decode($response, true);
        }

        return $response;

    }

    public function getConcurrent($promisesArray)
    {
        $results = null;

        // Initiate each request but do not block
        foreach ($promisesArray as $key => $method) {
            $promises[$key] = $this->client->getAsync($method);
        }

        // Wait on all of the requests to complete.
        try {
            $promiseResults = Promise\unwrap($promises);
        } catch (RequestException $exception) {
            return array('exception' => $exception);
        }

        // Normalize for use
        foreach ($promiseResults as $type => $result) {
            $results[$type] = json_decode($result->getBody());
        }

        return $results;
    }

    public function postQuery($apiMethod, $postData = null)
    {

        try {
            $response = $this->client->post(
                $apiMethod,
                ['json' => $postData]
            )->getBody();
        } catch (RequestException $exception) {
            return array('exception' => $exception);
        }

        return json_decode($response, true);
    }

    public function deleteQuery($apiMethod, $deleteData = null)
    {

        try {
            $response = $this->client->delete(
                $apiMethod,
                ['json' => $deleteData]
            )->getBody();
        } catch (RequestException $exception) {
            return array('exception' => $exception);
        }

        return json_decode($response, true);
    }

    public function patchQuery($apiMethod, $patchData = null)
    {

        try {
            $response = $this->client->patch(
                $apiMethod,
                ['json' => $patchData]
            )->getBody();
        } catch (RequestException $exception) {
            return array('exception' => $exception);
        }

        return json_decode($response, true);
    }

}