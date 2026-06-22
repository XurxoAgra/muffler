<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Console;

use App\Vehicle\Application\ImportVehicleCatalog\VehicleCatalogImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:import-vehicle-catalog',
    description: 'Importa el catálogo de marcas y modelos de vehículos desde un fichero JSON o CSV',
)]
final class ImportVehicleCatalogCommand extends Command
{
    private const DEFAULT_RELATIVE_PATH = '/migrations/import/eculimit_data.json';

    public function __construct(
        private readonly VehicleCatalogImporter $importer,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'file',
            InputArgument::OPTIONAL,
            'Ruta al fichero .json o .csv a importar (por defecto migrations/import/eculimit_data.json)',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filePath = $input->getArgument('file') ?? $this->projectDir.self::DEFAULT_RELATIVE_PATH;

        try {
            $rows = $this->importer->prepareRows($filePath);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        if ([] === $rows) {
            $io->warning('No se encontraron pares marca/modelo en el fichero.');

            return Command::SUCCESS;
        }

        $io->writeln(sprintf('Importando %d pares marca/modelo únicos desde "%s"...', count($rows), $filePath));

        $progressBar = new ProgressBar($output, count($rows));
        $progressBar->start();

        $result = $this->importer->import($rows, static function () use ($progressBar): void {
            $progressBar->advance();
        });

        $progressBar->finish();
        $io->newLine(2);

        $io->table(
            ['Concepto', 'Cantidad'],
            [
                ['Marcas nuevas', $result->makesCreated],
                ['Marcas ya existentes', $result->makesExisting],
                ['Modelos nuevos', $result->modelsCreated],
                ['Modelos ya existentes', $result->modelsExisting],
            ],
        );

        $io->success('Importación completada.');

        return Command::SUCCESS;
    }
}
