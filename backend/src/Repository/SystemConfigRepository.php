<?php declare(strict_types=1);

namespace App\Repository;

use App\Contracts\SystemConfigRepositoryInterface;
use App\Entity\SystemConfig;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Class SystemConfigRepository
 *
 * @package App\Repository
 */
final class SystemConfigRepository implements SystemConfigRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var $systemDir
     */
    private $systemDir;

    /**
     * SystemConfigRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface  $params
     *
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager
            ->getRepository(SystemConfig::class);

        $client = BlobRestProxy::createBlobService(
            $params->get('BLOB_ENDPOINTS_PROTOCOL')
        );
        $adapter = new AzureBlobStorageAdapter($client, 'system-assets');
        $this->filesystem = new Filesystem($adapter);

        $this->systemDir = 'system-assets';
        $this->systemImageDir = 'images';
        $this->systemImageDirSave = 'system-assets/images';
    }

    /**
     * Creates default settings.
     *
     * @return void
     */
    public function create()
    {
        $config = [
            [
                'opt' => 'social_media_max_score',
                'val' => '900',
                'system_type' => '1'
            ],
            [
                'opt' => 'behavior_weighting',
                'val' => '0.08',
                'system_type' => '1'
            ],
            [
                'opt' => 'pre_platform_scoring_metric',
                'val' => '30',
                'system_type' => '1'
            ],
            [
                'opt' => 'post_platform_scoring_metric',
                'val' => '5',
                'system_type' => '1'
            ]
        ];

        foreach ($config as $data) {
            $systemConfig = new SystemConfig();
            $systemConfig->setOpt($data['opt']);
            $systemConfig->setVal($data['val']);
            $systemConfig->setSystemType($data['system_type']);
            $this->entityManager->persist($systemConfig);
        }

        $this->entityManager->flush();
    }

    /**
     * @return SystemConfig[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param SystemConfig $systemConfig
     *
     * @return mixed
     */
    public function update(SystemConfig $systemConfig)
    {
        $config = $this->entityManager
            ->getRepository(SystemConfig::class)
            ->find($systemConfig->getId());

        $config->setVal($systemConfig->getVal());
        $this->entityManager->flush();
    }

    /**
     * @param $systemConfig
     * @param $file
     *
     * @return SystemConfig|bool|object|null
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function systemAssets($systemConfig, $file)
    {
        $this->deleteSystemAssets($systemConfig);

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream(
                $this->systemImageDir . '/' . $file->getClientOriginalName(),
                $stream
            );

            $config = $this->entityManager
                ->getRepository(SystemConfig::class)
                ->find($systemConfig->getId());

            $config->setVal(
                $this->systemImageDir . '/' . $file->getClientOriginalName()
            );
            $this->entityManager->flush();

            return $config;
        }

        return false;
    }

    /**
     * @param SystemConfig $systemConfig
     *
     * @throws FileNotFoundException
     */
    private function deleteSystemAssets(SystemConfig $systemConfig)
    {
        $path = $systemConfig->getVal();

        if ($this->filesystem->has($path)) {
            $this->filesystem->Delete($path);
        }
    }

    /**
     * @param string $config
     *
     * @return SystemConfig|object|null
     */
    public function getByName(string $config)
    {
        return $this->repository->findOneBy([
            'opt' => $config
        ]);
    }

    /**
     * @return array
     */
    public function systemAssetsList()
    {
        return $this->filesystem->listContents($this->systemImageDir);
    }
}