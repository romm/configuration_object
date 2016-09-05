<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use TYPO3\CMS\Beuser\Domain\Model\BackendUser;

class DummyConfigurationObjectWithPersistenceAttribute implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;

    /**
     * @var int|BackendUser
     */
    protected $user;

    /**
     * @var int[]|BackendUser[]
     */
    protected $subUsers = [];

    /**
     * @param int|BackendUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return int|BackendUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int[]|BackendUser[] $subUsers
     */
    public function setSubUsers(array $subUsers)
    {
        $this->subUsers = $subUsers;
    }

    /**
     * @return int[]|BackendUser[]
     */
    public function getSubUsers()
    {
        return $this->subUsers;
    }
}
