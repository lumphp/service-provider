<?php

namespace Lum;

use Illuminate\Console\Command;

class PackageDiscoverCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'package:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * Execute the console command.
     *
     * @param  PackageManifest  $manifest
     * @return void
     */
    public function handle(PackageManifest $manifest)
    {
        $manifest->build();

        foreach ($manifest->getManifest() as $package=>$info) {
            dd($info);
            $this->line("Discovered Package: <info>{$package}</info>");
        }

        $this->info('Package manifest generated successfully.');
    }


    /**
     * 发布配置
     *
     * @param array $package
     * @param $force
     */
    private function publishConfig(array $package, $force) {
        $configDir=$this->app->getConfigPath();
        $configs=(array)$package['extra']['think']['config'];
        $installPath=$this->app->getRootPath() . 'vendor/' . $package['name'] . DIRECTORY_SEPARATOR;
        foreach ($configs as $name=>$file) {
            $target=$configDir . $name . '.php';
            $source=$installPath . $file;
            if (is_file($target) && !$force) {
                $this->output->info("File {$target} exist!");
                continue;
            }
            if (!is_file($source)) {
                $this->output->info("File {$source} not exist!");
                continue;
            }
            copy($source, $target);
        }
    }

    /**
     * 发布数据库迁移脚本
     *
     * @param array $package
     * @param $force
     */
    private function publishMigrations(array $package, $force) {
        $rootPath=$this->app->getRootPath();
        $migrationPath=sprintf('%s/database/migrations/', $rootPath);
        $migration=(string)$package['extra']['think']['migrations'];
        $installPath=sprintf('%svendor/%s/%s/', $rootPath, $package['name'], $migration);
        foreach (glob($installPath . '*.php') as $v) {
            $migrateFile=str_replace($installPath, '', $v);
            $target=$migrationPath . $migrateFile;
            if (is_file($target) && !$force) {
                $this->output->info("Migrate file {$target} exist!");
                continue;
            }
            if (!is_file($v)) {
                $this->output->info("Migrate file {$v} not exist!");
                continue;
            }
            copy($v, $target);
        }
    }
}
