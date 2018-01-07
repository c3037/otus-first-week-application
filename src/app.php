#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use c3037\Otus\FirstWeek\Library\ValidatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @param null|string $targetFile
 * @return string
 */
function obtainTargetFile(?string $targetFile = null): string
{
    if (!$targetFile) {
        $input = new ArgvInput();
        $targetFile = $input->getFirstArgument() ?? null;
    }
    if (!$targetFile) {
        printf('Please specify path to target file an press enter:%s', PHP_EOL);
        $targetFile = rtrim(fgets(STDIN), PHP_EOL);
    }

    if (!file_exists($targetFile)) {
        printf('Sorry, but specified file not exist%s', PHP_EOL);
        exit;
    }

    return $targetFile;
}

/**
 * @param string $targetFile
 * @return string
 */
function obtainValidatableString(string $targetFile): string
{
    $fp = fopen($targetFile, 'rb');
    $string = rtrim(fgets($fp), PHP_EOL);
    fclose($fp);

    return $string;
}

/**
 * @param string $validatableString
 * @return bool
 */
function validateString(string $validatableString): bool
{
    try {
        $validator = buildDIContainer()->get('validator');
        /** @var ValidatorInterface $validator */

        return $validator->validate($validatableString);
    } catch (InvalidArgumentException $ex) {
        printf('Validation error: %s%s', $ex->getMessage(), PHP_EOL);
        exit;
    } catch (NotFoundExceptionInterface|ContainerExceptionInterface|Exception $ex) {
        printf('Kernel error: %s%s', $ex->getMessage(), PHP_EOL);
        exit;
    }
}

/**
 * @return ContainerInterface
 */
function buildDIContainer(): ContainerInterface
{
    $container = new ContainerBuilder();
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load(__DIR__ . DIRECTORY_SEPARATOR . 'services.yml');

    return $container;
}

/**
 * @param bool $validationResult
 * @return string
 */
function obtainResultMessage(bool $validationResult): string
{
    $resultMessagesMap = [
        true => 'String is valid',
        false => 'String is NOT valid',
    ];

    return $resultMessagesMap[$validationResult];
}

printf('%s%s', obtainResultMessage(validateString(obtainValidatableString(obtainTargetFile()))), PHP_EOL);
exit;
