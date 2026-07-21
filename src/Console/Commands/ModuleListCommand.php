<?php

declare(strict_types=1);
namespace LaravelModular\LaravelModular\Console\Commands;
use Illuminate\Console\Command;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
final class ModuleListCommand extends Command
{
    protected $signature = 'moduler:list'; protected $description = 'List discovered modules';
    public function handle(ModuleRepository $modules): int { $this->table(['Module', 'Path'], array_map(fn ($m) => [$m->name, $m->path], $modules->all())); return self::SUCCESS; }
}
