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
        $this->setDefined(['base_url', 'token']);

        $this->setAllowedTypes('base_url', 'string');
        $this->setAllowedTypes('token', 'string');

        $this->options = $options;
    }

    public function getConfig()
    {
        return $this->resolve($this->options);
    }
}
