<?php namespace App\Controllers\Vendors;

use App\Controllers\Vendors;
/**
 * Class {vendor}
 *
 * A vendor controller that does something
 *
 * @package App\Controllers\Vendors
 */

class {vendor} extends Vendors
{
    /**
     * Summary of response{vendor}
     * @var array
     */
    private array $response{vendor};
    
    /**
     * Undocumented function
     *
     * @param object $config
     */
    public function __construct(object $config)
    {
        parent::__construct();
        // Your Magic
    }

    /**
     * A method that does something
     *
     * @return array
     */
    public function getAllProducts(): array
    {
        return $this->response{vendor};
    }
}