<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeCollectionValidator;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver as ExtbaseValidatorResolver;

class ValidatorResolverTest extends AbstractUnitTest
{
    public function setUp()
    {
        parent::setUp();

        $this->injectMockedValidatorResolverInCore();
    }

    /**
     * Will check if the resolver checks the type of the validator before its
     * creation: if it should be an instance of `CollectionValidator`, it must
     * be replaced by `MixedTypeCollectionValidator` which will handle the mixed
     * types features.
     *
     * @test
     */
    public function createValidatorChecksCollectionType()
    {
        $validatorResolver = Core::get()->getValidatorResolver();

        $extbaseValidatorResolver = new ExtbaseValidatorResolver();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            $reflectedProperty = new \ReflectionProperty($extbaseValidatorResolver, 'objectManager');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($extbaseValidatorResolver, Core::get()->getObjectManager());

            $reflectedProperty = new \ReflectionProperty($extbaseValidatorResolver, 'reflectionService');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($extbaseValidatorResolver, Core::get()->getReflectionService());
        } else {
            $extbaseValidatorResolver->injectObjectManager(Core::get()->getObjectManager());
            $extbaseValidatorResolver->injectReflectionService(new ReflectionService());
        }

        $validator = $validatorResolver->createValidator(CollectionValidator::class);

        $this->assertInstanceOf(MixedTypeCollectionValidator::class, $validator);

        /*
         * If we try to create something different than the validator
         * `CollectionValidator`, we should have the same result when using
         * Extbase validator resolver.
         */
        $validator = $validatorResolver->createValidator(BooleanValidator::class);
        $validatorWithExtbase = $extbaseValidatorResolver->createValidator(BooleanValidator::class);

        $this->assertInstanceOf(BooleanValidator::class, $validator);
        $this->assertInstanceOf(BooleanValidator::class, $validatorWithExtbase);

        unset($validatorResolver);
        unset($extbaseValidatorResolver);
    }
}
