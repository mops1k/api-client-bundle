<?php

namespace ApiClientBundle\Maker;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClientMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:api:client';
    }

    public static function getCommandDescription(): string
    {
        return 'Create API client configuration class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a client name [Client]')
            ->addArgument('domain', InputArgument::OPTIONAL, 'Choose a domain name')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): int
    {
        $clientName = trim($input->getArgument('name'));
        $domain = $input->getArgument('domain');
        $clientClassNameDetails = $generator->createClassNameDetails(
            $clientName,
            'External\\' . $clientName . '\\',
            'Configuration',
            sprintf('The "%s" client name is not valid because it would be implemented by "%s" class, which is not valid as a PHP class name (it must start with a letter or underscore, followed by any number of letters, numbers, or underscores).', $clientName, Str::asClassName($clientName, 'Configuration'))
        );

        $question = new ChoiceQuestion('Enter client target scheme', [
            ClientConfigurationInterface::SCHEME_HTTP,
            ClientConfigurationInterface::SCHEME_SSL,
        ]);
        $scheme = $io->askQuestion($question);

        $question = new ConfirmationQuestion('Is client make asynchronous requests?', false);
        $isAsync = $io->askQuestion($question);
        $isAsync = $isAsync ? 'true' : 'false';

        $generator->generateClass(
            $clientClassNameDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/ClientConfiguration.tpl.php',
            [
                'isAsync' => $isAsync,
                'domain' => $domain,
                'scheme' => $scheme,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: open your new client class and customize it!',
            'Next: create query for client with command "make:api:query"!',
        ]);

        return Command::SUCCESS;
    }
}
