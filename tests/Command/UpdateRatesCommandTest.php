<?php declare(strict_types = 1);

namespace App\Tests\Command;

use App\Command\UpdateRatesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateRatesCommandTest extends KernelTestCase
{
    public function testInvalidSourceExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:update:rates');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'source' => 'invalid_source',
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Rates successfully updated.', $output);
    }

    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:update:rates');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'source' => UpdateRatesCommand::ECB_SOURCE,
        ]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The command is already running in another process.', $output);
    }
}
