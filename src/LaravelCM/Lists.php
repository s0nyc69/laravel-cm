<?php

namespace Flobbos\LaravelCM;
use Flobbos\LaravelCM\Contracts\ListContract;
use Flobbos\LaravelCM\Contracts\ResultFormatContract;
use Flobbos\LaravelCM\BaseClient;
use GuzzleHttp\Exception\RequestException;
use Exception;

class Lists extends BaseClient implements ListContract, ResultFormatContract{
    
    use Traits\ResultFormat;
    
    /**
     * Get all lists for the current client
     * @return Collection
     */
    public function get() {
        $result = $this->makeCall('get','clients/'.$this->getClientID().'/lists',[]);
        return collect($result->get('body'));
    }
    
    /**
     * Get the details for a list
     * @param string $list_id
     * @return string
     * @throws Exception
     */
    public function details($list_id) {
        $result = $this->makeCall('get','lists/'.$list_id,[]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    /**
     * Get the stats for a list
     * @param string $list_id
     * @return string
     * @throws Exception
     */
    public function stats($list_id){
        $result = $this->makeCall('get','lists/'.$list_id.'/stats',[]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    /**
     * Create a new list
     * @param array $list_options
     * @return string
     * @throws Exception
     */
    public function create(array $list_options){
        $result = $this->makeCall('post','lists/'.$this->getClientID(), [
            'json' => $list_options,
        ]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    /**
     * Update a list
     * @param string $list_id
     * @param array $list_options
     * @return string
     * @throws Exception
     */
    public function update(string $list_id, array $list_options){
        $result = $this->makeCall('put','lists/'.$list_id,[
            'json' => $list_options
        ]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    /**
     * Delete a list
     * @param string $list_id
     * @return boolean
     * @throws Exception
     */
    public function delete($list_id) {
        $result = $this->makeCall('delete','lists/'.$list_id,[]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return true;
    }
    
    public function makeCall($method = 'get', $url, array $request_data){
        try{
            return $this->formatResult(
                $this->callApi('listID')->{$method}($url.'.'.$this->getFormat(),
                $this->mergeRequestData($request_data)));
        } catch (RequestException $ex) {
            $response_body = $this->formatBody($ex->getResponse()->getBody());
            throw new Exception('Code '.$response_body->Code.': '.$response_body->Message);
        }
    }
}