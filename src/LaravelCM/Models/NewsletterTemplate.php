<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplate extends Model{
    
    protected $fillable = [
        'template_name',
        'title',
        'intro',
        'issue'
    ];
    
    
    public function getTemplateUrlAttribute() {
        return url('laravel-cm/' . $this->template_name);
    }

    public function getTemplateFileUrlAttribute() {
        return $this->template_url . '/' . $this->template_name . '.html';
    }

    public function getTemplatePathAttribute() {
        return public_path('laravel-cm/' . $this->template_name);
    }

    public function getTemplateFilePathAttribute() {
        return $this->template_path . '/' . $this->template_name . '.html';
    }
    
}
