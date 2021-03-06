<?php

namespace Flobbos\LaravelCM\Traits;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Psr7\Response;

trait ResultFormat{
        
    /**
     * formatResult
     * Format the result set from a Guzzle response
     * @param  Guzzle\Response $result
     * @param  bool $raw_body
     * @return array
     */
    public function formatResult(Response $result, $raw_body = false){
        //dd($result);
        return collect([
            'code' => $result->getStatusCode(),
            'reason' => $result->getReasonPhrase(),
            'protocol' => $result->getProtocolVersion(),
            'body' => $raw_body?$result->getBody():$this->formatBody($result->getBody())
        ]);
    }
        
    /**
     * formatBody
     * Format the response body 
     * @param  string $body
     * @return Object
     */
    public function formatBody($body){
        $string = '';
        while(!$body->eof()){
            $string .= $body->read(1024);
        }
        return json_decode($string);
    }
        
    /**
     * formatSubscribers
     *
     * @param  Object $result_body
     * @param  string $pageName
     * @return LengthAwarePaginator
     */
    public function formatSubscribers($result_body, $pageName = 'page'){
        $items = [];
        foreach($result_body->Results as $s){
            $items[] = [
                'email' => $s->EmailAddress,
                'name' => $s->Name,
                'subscribed_on' => $s->Date,
                'status' => $s->State,
                'custom_fields' => $s->CustomFields,
            ];
        }
        $paginator = new LengthAwarePaginator(
                    collect($items), 
                    $result_body->TotalNumberOfRecords, 
                    $result_body->PageSize, 
                    $result_body->PageNumber,
                    [
                        'path' => config('laravel-cm.url_path'),
                        'pageName' => $pageName
                    ]);
        return $paginator;
    }
    
}