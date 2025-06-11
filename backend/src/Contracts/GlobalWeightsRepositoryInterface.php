<?php

namespace App\Contracts;

use App\Entity\GlobalWeights;

/**
 * Interface RepositoryInterface
 * @package App\Contracts
 */
interface GlobalWeightsRepositoryInterface
{
    public function all();
    public function getByPlatform(string $platform);
    public function save(GlobalWeights $globalWeights);
}