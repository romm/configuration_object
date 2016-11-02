<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;

class DummyConfigurationObjectWithMixedTypes implements ConfigurationObjectInterface, MixedTypesInterface
{

    /**
     * @inheritdoc
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
    }

    /**
     * @inheritdoc
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $resolver->setObjectType(\stdClass::class);
    }
}
