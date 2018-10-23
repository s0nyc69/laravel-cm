<?php

namespace Flobbos\LaravelCM;
use Flobbos\LaravelCM\Contracts\CampaignContract;
use Flobbos\LaravelCM\BaseClient;
use Exception;

class Campaigns extends BaseClient implements CampaignContract{
    
    use Traits\ResultFormat;
    
    public function getDrafts() {
        $drafts = $this->makeCall('get','clients/'.$this->getClientID().'/drafts',[]);
        if($drafts->get('code') != '200'){
            throw new Exception($drafts->get('body'));
        }
        return collect($drafts->get('body'));
    }
    
    public function getScheduled() {
        $drafts = $this->makeCall('get','clients/'.$this->getClientID().'/scheduled',[]);
        if($drafts->get('code') != '200'){
            throw new Exception($drafts->get('body'));
        }
        return collect($drafts->get('body'));
    }
    
    public function getSent() {
        $drafts = $this->makeCall('get','clients/'.$this->getClientID().'/campaigns',[]);
        if($drafts->get('code') != '200'){
            throw new Exception($drafts->get('body'));
        }
        return collect($drafts->get('body'));
    }
    
    public function getCampaignSummary($campaign_id) {
        $summary = $this->makeCall('get','campaigns/'.$campaign_id.'/summary',[]);
        if($summary->get('code') != '200'){
            throw new Exception($summary->get('body'));
        }
        return $summary->get('body');
    }

    public function getEmailClientUsage($campaign_id) {
        $users = $this->makeCall('get','campaigns/'.$campaign_id.'/emailclientusage',[]);
        if($users->get('code') != '200'){
            throw new Exception($users->get('body'));
        }
        return $users->get('body');
    }
    
    public function getListsAndSegments(string $campaign_id){
        $result = $this->makeCall('get','campaigns/'.$campaign_id.'/listsandsegments',[]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    public function createDraft(array $options) {
        $options['SegmentIDs'] = [];
        $data = ['json' => $options];
        //dd($data);
        $result = $this->makeCall('post','campaigns/'.$this->getClientID(),$data);
        if($result->get('code') != '201'){
            throw new Exception($result->get('body'));
        }
        //Return formatted list
        return $result->get('body');
    }
    
    public function delete(string $campaign_id) {
        $result = $this->makeCall('delete','campaigns/'.$campaign_id,[]);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return true;
    }
    
    public function sendPreview(string $campaign_id, array $recipients = []){
        //dd(json_encode(['PreviewRecipients'=>$recipients]));
        $result = $this->makeCall('post','campaigns/'.$campaign_id.'/sendpreview',[
            'json' => ['PreviewRecipients'=>$recipients]
        ]);
        //dd($result);
        if($result->get('code') != '200'){
            throw new Exception($result->get('body'));
        }
        return $result->get('body');
    }
    
    public function makeCall($method = 'get', $url, array $request_data) {
        //dd($this->mergeRequestData($request_data));
        try{
            return $this->formatResult(
                $this->callApi()->{$method}($url.'.'.$this->getFormat(),
                $this->mergeRequestData($request_data)));
        } catch (RequestException $ex) {
            $response_body = $this->formatBody($ex->getResponse()->getBody());
            throw new Exception('Code '.$response_body->Code.': '.$response_body->Message);
        }
        
    }
    
}