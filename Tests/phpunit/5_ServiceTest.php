<?php

include_once(__DIR__.'/CommandTest.php');

use Kaliop\eZMigrationBundle\API\ExecutorInterface;
use Kaliop\eZMigrationBundle\API\Value\MigrationStep;
use Kaliop\eZMigrationBundle\API\Value\MigrationDefinition;
use Kaliop\eZMigrationBundle\API\Value\Migration;

class ServiceTest extends CommandTest implements ExecutorInterface
{
    public function testMigrationFetching()
    {
        $ms = $this->container->get('ez_migration_bundle.migration_service');
        $ms->addExecutor($this);
        $md = new MigrationDefinition(
            'storage_test1.json',
            '/dev/null',
            json_encode(array(array('type' => 'void')))
        );
        $ms->executeMigration($md);

        $m = $ms->getMigration('exception_test.json');
        $this->assertEquals(Migration::STATUS_DONE, $m->status, 'Migration supposed to be aborted but in unexpected state');

        $migrations = $ms->getMigrationsByStatus(Migration::STATUS_DONE);
        $this->assertGreaterThanOrEqual(1, $migrations->count());
        foreach($migrations as $migration) {
            $this->assertEquals(Migration::STATUS_DONE, $migration->status, 'Fetched migration has unexpected status');
        }

        $md = new MigrationDefinition(
            'storage_test2.json',
            '/dev/null',
            json_encode(array(array('type' => 'void')))
        );
        $ms->addMigration($md);
        $md = new MigrationDefinition(
            'storage_test3.json',
            '/dev/null',
            json_encode(array(array('type' => 'void')))
        );
        $ms->addMigration($md);

        $migrations = $ms->getMigrationsByStatus(Migration::STATUS_TODO);
        $this->assertGreaterThanOrEqual(2, $migrations->count());
        foreach($migrations as $migration) {
            $this->assertEquals(Migration::STATUS_TODO, $migration->status, 'Fetched migration has unexpected status');
        }
        $migrations = $ms->getMigrationsByStatus(Migration::STATUS_TODO, 1);
        $this->assertEquals(1, $migrations->count());

        $migrations = $ms->getMigrationsByStatus(Migration::STATUS_TODO, 1, 1);
        $this->assertEquals(1, $migrations->count());

        $migrations = $ms->getMigrationsByStatus(Migration::STATUS_TODO, 1, 999);
        $this->assertEquals(0, $migrations->count());

        $migrations = $ms->getMigrations(1);
        $this->assertEquals(1, $migrations->count());

        $migrations = $ms->getMigrations(1, 1);
        $this->assertEquals(1, $migrations->count());

        $migrations = $ms->getMigrations(1, 999);
        $this->assertEquals(0, $migrations->count());
    }

    public function supportedTypes()
    {
        return array('void');
    }

    public function execute(MigrationStep $step)
    {
    }
}
