# ![Configuration Object](Documentation/Images/configuration-object-icon@medium.png) Configuration Object

> [![Build Status](https://travis-ci.org/romm/configuration_object.svg?branch=master)](https://travis-ci.org/romm/configuration_object) [![Coverage Status](https://coveralls.io/repos/github/romm/configuration_object/badge.svg?branch=master)](https://coveralls.io/github/romm/configuration_object?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/romm/configuration_object/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/romm/configuration_object/?branch=master) [![Latest Stable Version](https://poser.pugx.org/romm/configuration-object/v/stable)](https://packagist.org/packages/romm/configuration-object) [![Total Downloads](https://poser.pugx.org/romm/configuration-object/downloads)](https://packagist.org/packages/romm/configuration-object) [![SensioLabs Insight](https://img.shields.io/sensiolabs/i/86ddd9e0-ff29-4886-b04a-a8e27997a6af.svg)](https://insight.sensiolabs.com/projects/86ddd9e0-ff29-4886-b04a-a8e27997a6af) [![StyleCI](https://styleci.io/repos/66448948/shield?branch=master)](https://styleci.io/repos/66448948)

> :heavy_exclamation_mark: *This PHP library has been developed for [![TYPO3](Resources/Public/Images/typo3-icon.png)TYPO3 CMS](https://typo3.org) and is intended to TYPO3 extension developers.*

> :arrow_right: *You can find the whole documentation on the [TYPO3 official website](https://docs.typo3.org/typo3cms/extensions/configuration_object/), or even download the [:link:PDF version](https://docs.typo3.org/typo3cms/extensions/configuration_object/_pdf/)*.

---

## Introduction

![Configuration Object](Documentation/Images/configuration-object-icon@small.png) **Configuration Object** provides **powerful tools for handling configuration trees**, by converting any **configuration plain array** (which can come from sources like **TypoScript, JSON, XML**) into a much more **flexible PHP object structure**. Its principal goal is to **pull apart the configuration handling from the main logic of an application**, so the script can focus on **using the already validated configuration during its whole process**.

### Problem

When a script uses a configuration tree to handle parts of an application, this tree is often **analyzed step by step during the script execution**; if a value contains a mistake, the script can be forced to stop, too early (*the whole process did not run entirely*) but also too late (*some sensitive operations may already have run*). Moreover, **the deeper** the configuration tree is, **the harder** it is to handle and prevent all the possible configuration mistakes.

When it comes to configuration which may be customized by any third-party user (which happens often in TYPO3 thanks to TypoScript), validation rules have to be **well thought and strong** to prevent the user from breaking your own API scripts because of a configuration mistake.

### Solution

Use **Configuration Object** to export the handling of your configuration: let the whole **creation and validation processes be managed outside of your application**, and enjoy the **many other features provided by the API** (cache management, parents, persistence and more).

It is **simple, fast and reliable**.

## Example

Imagine you have this configuration array:

```php
$myCompany = [
    'name'      => 'My Company',
    'employees' => [
        [
            'name'   => 'John Doe',
            'gender' => 'Male',
            'email'  => 'john.doe@my-company.com'
        ],
        [
            'name'   => 'Jane Doe',
            'gender' => 'Female',
            'email'  => 'jane.doe@my-company.com'
        ]
    ]
];
```

While this example is quite simple, it allows us to understand easily how this API works.

Below stands an example of how this configuration could look like using **Configuration Object API**.

You can see that **two services** are used:

- **Cache service**

  It will store the whole company object, and its sub-objects, in a cache entry after they have been created. This will improve performances for next times the object must be fetched.

- **Parents service**

  With this service, the class `Employee` is able to retrieve the data from its parent (the class `Company`). In this example, we use it to dynamically generate an email address for the employee, if none was assigned.

```php
namespace MyVendor\MyExtensions\Company;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use MyVendor\MyExtensions\Model\Company\Employee;

class Company implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;
    use MagicMethodsTrait;

    const CACHE_NAME = 'cache_company';

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $name;

    /**
     * @var \ArrayObject<MyVendor\MyExtensions\Company\Employee>
     */
    protected $employees;

    /**
     * @return ServiceFactory
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_NAME, self::CACHE_NAME)
            ->attach(ServiceInterface::SERVICE_PARENTS);
    }
}
```

---

```php
namespace MyVendor\MyExtensions\Company;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

class Employee
{
    use ParentsTrait;
    use MagicMethodsTrait;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $name;

    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.ConfigurationObject:HasValues(values=Male|Female)
     */
    protected $gender;

    /**
     * @var string
     * @validate EmailAddress
     */
    protected $email;

    /**
     * Returns the email of the employee.
     *
     * If the email was not registered, a default one is assigned to
     * him, based on its name and its company name.
     *
     * Example: `John Doe` of the company `My Company` will be assigned
     * the default email: `john.doe@my-company.com`.
     *
     * @return string
     */
    public function getEmail()
    {
        if (null === $this->email
            && $this->hasParent(Company::class)
        ) {
            $sanitizedEmployeeName = SomeUtility::sanitizeStringForEmail($this->getName());

            $company = $this->getParent(Company::class);
            $sanitizedCompanyName = SomeUtility::sanitizeStringForEmail($company->getName(), '-');

            $this->email = vprintf(
                '%s@%s.com',
                [$sanitizedEmployeeName, $sanitizedCompanyName]
            );
        }

        return $this->email;
    }
}
```
