<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits;

use Romm\ConfigurationObject\Traits\InternalVariablesTrait;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class InternalVariablesTraitTest extends AbstractUnitTest
{

    /**
     * Will check the main goal of the trait: being able to set a variable,
     * which can then be retrieved or unset.
     *
     * @test
     */
    public function variablesCanBeSet()
    {
        /** @var InternalVariablesTrait $internalVariablesTrait */
        $internalVariablesTrait = $this->getMockForTrait(InternalVariablesTrait::class);
        $internalVariablesTrait->setInternalVar('foo', 'bar');

        $this->assertEquals(false, $internalVariablesTrait->hasInternalVar('notExistingVar'));
        $this->assertEquals(true, $internalVariablesTrait->hasInternalVar('foo'));
        $this->assertEquals('bar', $internalVariablesTrait->getInternalVar('foo'));

        $internalVariablesTrait->unsetInternalVar('foo');

        $this->assertEquals(null, $internalVariablesTrait->getInternalVar('foo'));
    }
}
