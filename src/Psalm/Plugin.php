<?php declare(strict_types=1);

namespace Kuria\Psalm;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\PluginInterface;
use Psalm\Plugin\RegistrationInterface;

class Plugin implements PluginInterface, PluginEntryPointInterface
{
    function __invoke(RegistrationInterface $registration, ?\SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/Hook/AssertTypeHook.php';

        $registration->registerHooksFromClass(Hook\AssertTypeHook::class);
        $registration->addStubFile(__DIR__ . '/stubs.php');
    }
}
