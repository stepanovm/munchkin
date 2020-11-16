<?php


namespace Lynxx\Container;


use DomainException;
use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends DomainException implements NotFoundExceptionInterface
{

}