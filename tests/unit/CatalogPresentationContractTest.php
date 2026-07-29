<?php

use App\Libraries\CatalogPresentationContract;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CatalogPresentationContractTest extends CIUnitTestCase
{
    public function testProofUsesVersionedA4DigitalContract(): void
    {
        $model = CatalogPresentationContract::proof();

        $this->assertSame(CatalogPresentationContract::VERSION, $model['version']);
        $this->assertSame('A4', $model['document']['format']);
        $this->assertSame('digital', $model['document']['intent']);
        $this->assertCount(3, $model['pages']);
    }

    public function testProofIncludesCriticalProductRoles(): void
    {
        $model = CatalogPresentationContract::proof();
        $roles = array_column($model['pages'][1]['elements'], 'role');

        $this->assertContains('productName', $roles);
        $this->assertContains('productImage', $roles);
        $this->assertContains('productCode', $roles);
        $this->assertContains('price', $roles);
    }
}
