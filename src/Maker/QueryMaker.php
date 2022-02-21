<?php

namespace ApiClientBundle\Maker;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpFoundation\Request;

class QueryMaker extends AbstractMaker
{
    /**
     * @var array<ClientConfigurationInterface>
     */
    private array $clients = [];

    /**
     * @param iterable<ClientConfigurationInterface>|null $clients
     */
    public function __construct(?iterable $clients)
    {
        if (!$clients) {
            return;
        }

        $this->clients = \iterator_to_array($clients);
    }

    public static function getCommandName(): string
    {
        return 'make:api:query';
    }

    public static function getCommandDescription(): string
    {
        return 'Create API client query class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose query name')
            ->addArgument('endpoint', InputArgument::OPTIONAL, 'Choose endpoint')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $queryName = trim($input->getArgument('name'));
        $endpoint = trim($input->getArgument('endpoint'));

        $question = new ChoiceQuestion('Choose method for query', [
            Request::METHOD_HEAD,
            Request::METHOD_GET,
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_PATCH,
            Request::METHOD_DELETE,
            Request::METHOD_PURGE,
            Request::METHOD_OPTIONS,
            Request::METHOD_TRACE,
            Request::METHOD_CONNECT,
        ], Request::METHOD_GET);
        $method = $io->askQuestion($question);

        $clients = [];
        foreach ($this->clients as $client) {
            $clients[] = $client::class;
        }
        $question = new ChoiceQuestion('Choose client for query', $clients);
        $client = $io->askQuestion($question);

        $question = new ConfirmationQuestion('Use default error response?', true);
        $isDefaultErrorResponse = $io->askQuestion($question);

        $refl = new \ReflectionClass($client);
        $namespacePart = \str_replace('Configuration', '', $refl->getShortName());
        $queryClassDetails = $generator->createClassNameDetails(
            $queryName,
            'External\\' . $namespacePart . '\\Query',
            'Query'
        );

        $responseClassDetails = $generator->createClassNameDetails(
            $queryName,
            'External\\' . $namespacePart . '\\Response',
            'Response'
        );

        $errorResponseClassDetails = null;
        if (!$isDefaultErrorResponse) {
            $errorResponseClassDetails = $generator->createClassNameDetails(
                $queryName,
                'External\\' . $namespacePart . '\\Response',
                'ErrorResponse'
            );

            $generator->generateClass(
                $errorResponseClassDetails->getFullName(),
                __DIR__ . '/../Resources/skeleton/ErrorResponse.tpl.php'
            );
        }

        $generator->generateClass(
            $responseClassDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/Response.tpl.php'
        );

        $generator->generateClass(
            $queryClassDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/Query.tpl.php',
            [
                'errorResponseClassDetails' => $errorResponseClassDetails,
                'endpoint' => $endpoint,
                'method' => $method,
                'clientFullName' => $client,
                'clientShortName' => $refl->getShortName(),
                'responseClassNameDetails' => $responseClassDetails,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: open your new query classes and customize it!',
        ]);
    }
}
