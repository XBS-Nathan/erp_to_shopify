<?php

namespace ERPBundle\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShopifyOptions
 * @package ERPBundle\Options
 */
class ShopifyOptions extends OptionsResolver
{
    public function __construct(array $options)
    {
        $this->setDefined(['product_limit']);

        $this->setAllowedTypes('product_limit', 'integer');

        $this->options = $options;
    }

    public function getConfig()
    {
        return $this->resolve($this->options);
    }
}
