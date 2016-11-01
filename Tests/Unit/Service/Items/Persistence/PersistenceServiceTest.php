<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Cache;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Items\Persistence\PersistenceService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithPersistenceAttribute;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class PersistenceServiceTest extends AbstractUnitTest
{

    /**
     * @var BackendUser
     */
    protected $currentUser;

    /**
     * Will check that the service will properly fetch domain object entities
     * using Extbase persistence.
     *
     * @test
     */
    public function domainObjectAttributesAreConvertedUsingPersistence()
    {
        $persistenceService = new PersistenceService();
        $serviceFactory = new ServiceFactory();
        $dummyConfigurationObject = new DummyConfigurationObjectWithPersistenceAttribute();
        $dummyConfigurationObject->setUser(1);
        $dummyConfigurationObject->setSubUsers(
            [
                0      => 2,
                'list' => [0 => 3]
            ]
        );

        $user1 = new BackendUser();
        $user1->setUserName('john.doe');
        $user2 = new BackendUser();
        $user2->setUserName('jane.doe');
        $user3 = new BackendUser();
        $user3->setUserName('foo.bar');
        $users = [
            1 => $user1,
            2 => $user2,
            3 => $user3
        ];

        $mockedPersistenceManager = $this->getMock(PersistenceManager::class, ['getObjectByIdentifier']);
        $mockedPersistenceManager->expects($this->any())
            ->method('getObjectByIdentifier')
            ->willReturnCallback(
                function ($uid) use ($users) {
                    return $users[$uid];
                }
            );

        $persistenceService->injectPersistenceManager($mockedPersistenceManager);

        $configurationObjectConversionDTO = new ConfigurationObjectConversionDTO(DummyConfigurationObjectWithPersistenceAttribute::class, $serviceFactory);
        $configurationObjectConversionDTO->setTargetType(BackendUser::class);

        $configurationObjectConversionDTO->setCurrentPropertyPath(['user']);
        $persistenceService->objectConversionBefore($configurationObjectConversionDTO);

        $configurationObjectConversionDTO->setCurrentPropertyPath(['subUsers.0']);
        $persistenceService->objectConversionBefore($configurationObjectConversionDTO);

        $configurationObjectConversionDTO->setCurrentPropertyPath(['subUsers.list.0']);
        $persistenceService->objectConversionBefore($configurationObjectConversionDTO);

        $getConfigurationObjectDTO = new GetConfigurationObjectDTO(DummyConfigurationObjectWithPersistenceAttribute::class, $serviceFactory);

        $result = new Result();
        $configurationObjectInstance = new ConfigurationObjectInstance($dummyConfigurationObject, $result);

        $getConfigurationObjectDTO->setResult($configurationObjectInstance);

        $persistenceService->configurationObjectAfter($getConfigurationObjectDTO);
        $persistenceService->runDelayedCallbacks($getConfigurationObjectDTO);

        $this->assertEquals(
            serialize($user1),
            serialize($dummyConfigurationObject->getUser())
        );

        $this->assertEquals(
            serialize($user2),
            serialize($dummyConfigurationObject->getSubUsers()[0])
        );

        $this->assertEquals(
            serialize($user3),
            serialize($dummyConfigurationObject->getSubUsers()['list'][0])
        );
    }
}
