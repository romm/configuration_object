<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;

class DummyConfigurationObjectWithDataPreProcessor implements ConfigurationObjectInterface, DataPreProcessorInterface
{

    /**
     * @inheritdoc
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR);
    }

    /**
     * @inheritdoc
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();
        $data['bar'] = 'bar';
        $processor->setData($data);
    }
}
