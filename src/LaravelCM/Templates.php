<?php

namespace Flobbos\LaravelCM;

use Flobbos\LaravelCM\Contracts\TemplateContract;
use Flobbos\LaravelCM\BaseClient;
use Symfony\Component\DomCrawler\Crawler;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Leafo\ScssPhp\Compiler as ScssCompiler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class Templates extends BaseClient implements TemplateContract {

    protected $disk;
    protected $template;
    protected $srcTemplatePath;
    protected $distTemplatePath;
    protected $html;

    public function __construct($template) {

        $this->disk = Storage::disk('laravel_cm');

        if(!File::exists(resource_path('laravel-cm/' . $template))) {
            throw new \Exception('Given template "'.$template.'" not found at ' . resource_path('laravel-cm'));
        }

        $this->template = $template;
        $this->srcTemplatePath = resource_path('laravel-cm/' . $this->template);
        $this->distTemplatePath = $this->disk->path($this->template);

    }
    
    /**
     * Start compiling process
     *
     * @return void
     */
    public function compile($data = []) {
        $this->compileSass();
        $this->copyImages();
        $this->html = $this->saveViewAsHtml($this->template, $data);
        $this->inlineStyles();
    }

    /**
     * Compile inky template to html and save it to storage
     *
     * @param [string] $view
     * @param array $data
     * @return void
     */
    public function saveViewAsHtml($view, $data) {

        $viewPath = $view . '.views.' . $view;

        $html = View::make($viewPath, $data)->render();

        $this->disk->put($this->template . '/' . $this->template . '.html', $html);

        return $html;

    }

    /**
     * Compile template scss to css and save file to storage
     *
     * @return void
     */
    public function compileSass() {
 
        $importPaths = [
            // Set template imports
            resource_path('laravel-cm/'.$this->template.'/assets/scss'),
            // Set foundation-email imports
            __DIR__ . '/../resources/defaults/assets/foundation-emails',
            __DIR__ . '/../resources/defaults/assets/foundation-emails/utils',
            __DIR__ . '/../resources/defaults/assets/foundation-emails/components',
            __DIR__ . '/../resources/defaults/assets/foundation-emails/grid',
            __DIR__ . '/../resources/defaults/assets/foundation-emails/settings',
        ];

        $src = resource_path('laravel-cm/'.$this->template.'/assets/scss/'.$this->template.'.scss');

        $scss = new ScssCompiler();
        $scss->setImportPaths($importPaths);

        $scssContent = File::get($src);
        $css = $scss->compile($scssContent);

        return $this->disk->put($this->template . '/assets/style.css', $css);
    }

    /**
     * Copy images to storage
     *
     * @return void
     */
    public function copyImages() {

        $imageFolder = resource_path('laravel-cm/'.$this->template.'/assets/images');
        $dest = $this->disk->path($this->template . '/assets');
        return File::copyDirectory($imageFolder, $dest);

    }

    /**
     * Run Style-Inliner to inline template styles to html-document
     *
     * @return void
     */
    public function inlineStyles() {

        $crawler = new Crawler();
        $crawler->addHtmlContent($this->html);
        
        $stylesheets = $crawler->filter('link[rel=stylesheet]');
        // collect hrefs
        $stylesheetsHrefs = collect($stylesheets->extract('href'));
        // remove links
        $stylesheets->each(function (Crawler $crawler) {;
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });
        $results = $crawler->html();
        // get the styles

        
        $styles = $stylesheetsHrefs->map(function ($stylesheet) {
            $path = $this->template . '/' . $stylesheet;
            return $this->disk->get($path);
        })->implode("\n\n");
        $inliner = new CssToInlineStyles();

        return $this->disk->put($this->template . '/' . $this->template . '.html', $inliner->convert($results, $styles));

    }

    /**
     * Remove assets-folder
     *
     * @return void
     */
    public function clearAssets() {
        return $this->disk->deleteDirectory($this->cm_template_id . '/assets');
    }
    
    //Sync templates to CM
    public function create(){
        $result = $this->makeCall('post','templates/'.$this->getClientID());
    }
    
    public function makeCall($method = 'get', $url, array $request_data) {
        ;
    }
    
}
