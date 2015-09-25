<?php

namespace ERPBundle\Services;

/**
 * Class ProductCatalogEntity
 * @package ERPBundle\Services
 */
class ProductCatalogEntity
{
    public static function createFromResponse($response)
    {
        $self = new self();

        return $self;
    }
}
