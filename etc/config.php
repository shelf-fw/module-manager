<?php

use Shelf\Console\Api\ShelfConsoleInterface;
use Shelf\ModuleManager\Console\ModuleCreateCommand;
use Shelf\ModuleManager\Console\ModuleCreateCommandCommand;
use Shelf\ModuleManager\Console\ModuleListCommand;

return [
    ShelfConsoleInterface::SHELF_APPLICATION_KEY => [
        ShelfConsoleInterface::COMMANDS_KEY => [
            ModuleListCommand::class,
            ModuleCreateCommand::class,
            ModuleCreateCommandCommand::class
        ]
    ]
];