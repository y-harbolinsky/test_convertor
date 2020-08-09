<?php declare(strict_types = 1);

namespace App\Command;

use App\Manager\RatesManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRatesCommand extends Command
{
    use LockableTrait;

    public const ALL_SOURCE = 'all';
    public const ECB_SOURCE = 'ecb';
    public const CBR_SOURCE = 'cbr';

    protected static $defaultName = 'app:update:rates';

    /** @var RatesManagerInterface */
    private $ratesManager;

    public function __construct(RatesManagerInterface $ratesManager)
    {
        $this->ratesManager = $ratesManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates rates.')
            ->addArgument('source', InputArgument::OPTIONAL, 'The source (ecb, cbr, all) of rates data.')
            ->setHelp('This command allows to update rates from ECB or CBR or all.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        try {
            $source = $input->getArgument('source');

            if (!$source || !in_array($source, [self::ALL_SOURCE, self::ECB_SOURCE, self::CBR_SOURCE])) {
                $source = self::ALL_SOURCE;
            }

            $this->ratesManager->clearRatesTable();
            $this->ratesManager->updateRates($source);
        } catch (\Throwable $exception) {
            // Log error
            $output->writeln('Something went wrong. Reason: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('Rates successfully updated.');

        return Command::SUCCESS;
    }
}
