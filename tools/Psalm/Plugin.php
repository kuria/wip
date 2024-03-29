<?php declare(strict_types=1);

namespace Kuria\Tools\Psalm;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\PluginInterface;
use Psalm\Plugin\RegistrationInterface;

class Plugin implements PluginInterface, PluginEntryPointInterface
{
    function __invoke(RegistrationInterface $registration, ?\SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/Hook/TestTypeHook.php';

        $registration->registerHooksFromClass(\Kuria\Tools\Psalm\Hook\TestTypeHook::class);
    }
}
