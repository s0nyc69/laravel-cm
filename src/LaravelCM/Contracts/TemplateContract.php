<?php

namespace Flobbos\LaravelCM\Contracts;

use Flobbos\LaravelCM\Contracts\BaseClientContract;

interface TemplateContract
{

    /**
     * Get all templates in DB
     * @return \Illuminate\Support\Collection
     */
    public function get();

    /**
     * Create new template in DB
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update Template
     * @param array $data
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data, $return_model = false);

    /**
     * Set relations for templates
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations);

    /**
     * Find a specific template
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id);

    /**
     * Delete a template
     * @param int $id
     * @return boolean
     */
    public function delete($id);

    /**
     * Get all templates from DB
     * @return \Illuminate\Support\Collection
     */
    public function getTemplatesFromDB();

    /**
     * Set the current template
     *
     * @param string $template_name
     * @return $this
     */
    public function setTemplate(string $template_name);

    /**
     * Get the current template name in use
     *
     * @return string template name
     */
    public function getTemplate();

    /**
     * Start compiling process
     *
     * @return void
     */
    public function compile(string $template_name, array $data = []);

    /**
     * Compile inky template to html and save it to storage
     *
     * @param [string] $view
     * @param array $data
     * @return void
     */
    public function saveViewAsHtml($view, $data);

    /**
     * Copy images to storage
     *
     * @return void
     */
    public function copyImages();

    /**
     * Check if the template file exists
     * @param type $name
     */
    public function templateExists(string $template_name);

    /**
     * Get base layouts from disk
     * @return Array
     * @throws NoLayoutsException
     */
    public function getLayouts();

    /**
     * Get the validation rules for storing a new template
     * @return array
     */
    public function getValidationRulesStore(): array;

    /**
     * Get validation rules for updating a template
     *
     * @return array
     */
    public function getValidationRulesUpdate(): array;
}
