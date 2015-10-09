<?php

namespace ERPBundle\Webhook\Handler;


abstract class BaseHandler
{
    abstract function execute(BaseCommand $cmd);
}