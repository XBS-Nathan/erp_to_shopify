<?php

namespace ERPBundle\Webhook\Handler;


use ERPBundle\Webhook\Command\BaseCommand;

abstract class BaseHandler
{
    abstract function execute(BaseCommand $cmd);
}