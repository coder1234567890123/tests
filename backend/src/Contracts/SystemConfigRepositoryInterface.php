<?php

namespace App\Contracts;

use App\Entity\SystemConfig;

/**
 * Interface RepositoryInterface
 * @package App\Contracts
 */
interface SystemConfigRepositoryInterface
{
    public function create();
    public function all();
    public function update(SystemConfig  $systemConfig);
    public function systemAssets($systemConfig, $file);
    public function getByName(string $config);
    public function systemAssetsList();
}