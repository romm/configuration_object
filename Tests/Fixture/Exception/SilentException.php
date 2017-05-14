<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Exception;

use Romm\ConfigurationObject\Exceptions\SilentExceptionInterface;

class SilentException extends \Exception implements SilentExceptionInterface
{
}
