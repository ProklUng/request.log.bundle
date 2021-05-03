<?php

namespace Prokl\RequestLogBundle\Exceptions;

use Prokl\BaseException\BaseException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

/**
 * Class ErrorSerializeResponseException
 * @package Prokl\RequestLogBundle\Exceptions
 *
 * @since 03.05.2021
 */
class ErrorSerializeResponseException extends BaseException implements RequestExceptionInterface
{

}