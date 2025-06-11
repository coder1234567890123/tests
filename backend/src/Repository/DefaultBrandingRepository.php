<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\DefaultBranding;
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
 * Class DefaultBrandingRepository
 *
 * @package App\Repository
 */
final class DefaultBrandingRepository
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
            ->getRepository(DefaultBranding::class);

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
                'theme_color' => '#166c36',
                'footer_link' => 'https://www.farosian.com/',
                'disclaimer' => 'Please add something',
                'cover_logo' => '1',
                'front_page' => '1',
                'logo' => '1'
            ]

        ];

        foreach ($config as $data) {
            $defaultBranding = new DefaultBranding();
            $defaultBranding->setThemeColor($data['theme_color']);
            $defaultBranding->setFooterLink($data['footer_link']);
            $defaultBranding->setDisclaimer($data['disclaimer']);
            $defaultBranding->setFrontPage($data['front_page']);
            $defaultBranding->setLogo($data['logo']);

            $this->entityManager->persist($defaultBranding);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array|object[]
     */
    public function all()
    {
        $branding = $this->repository->findOneBy([]);

        return [
            'id' => $this->checkValue($branding->getId()),
            'theme_color' => $this->checkValue($branding->getThemeColor()),
            'theme_color_second' => $this->checkValue($branding->getThemeColorSecond()),
            'theme_color_overlay_rgb' => $this->createRga($branding->getThemeColor()),
            'front_page' => $this->checkValue($branding->getFrontPage()),
            'co_front_page' => $this->checkValue($branding->getCoFrontPage()),
            'cover_logo' => $this->checkValue($branding->getCoverLogo()),
            'logo' => $this->checkValue($branding->getLogo()),
            'footer_link' => $this->checkValue($branding->getFooterLink()),
            'disclaimer' => $this->checkValue($branding->getDisclaimer())
        ];
    }

    /**
     * @param $hex
     * @return string
     */
    private function createRga($hex)
    {
        if ($hex) {

            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            return "rgba($r, $g, $b, 0.5)";
        } else {
            return '';
        }
    }

    /**
     * @param DefaultBranding $defaultBranding
     */
    public function save(DefaultBranding $defaultBranding)
    {
        $this->entityManager->persist($defaultBranding);
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
    public function systemAssets($file, $placement)
    {
        $this->deleteSystemAssets($file);

        if ($file->isValid()) {
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            $dateTime = date("mdYhis");

            $defaultBrand = $this->entityManager
                ->getRepository(DefaultBranding::class)
                ->findOneBy([]);

            if ($placement == "front_page") {
                $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_front_page.' . $ext, $file->getClientOriginalName());

                $defaultBrand->setFrontPage(

                    $this->systemImageDir . '/' . $fileName
                );
            } elseif ($placement == "cover_logo") {
                $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_cover_logo.' . $ext, $file->getClientOriginalName());
                $defaultBrand->setCoverLogo(
                    $this->systemImageDir . '/' . $fileName
                );
            }
            elseif ($placement == "co_front_page") {
                $fileName = str_replace($file->getClientOriginalName(), $dateTime . 'co_front_page.' . $ext, $file->getClientOriginalName());
                $defaultBrand->setCoFrontPage(
                    $this->systemImageDir . '/' . $fileName
                );
            }
            elseif ($placement == "logo") {
                $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_logo.' . $ext, $file->getClientOriginalName());

                $defaultBrand->setLogo(

                    $this->systemImageDir . '/' . $fileName
                );
            }

            $this->uploadImage($fileName, $file);

            $this->entityManager->flush();

            return $defaultBrand;
        }

        return false;
    }


    /**
     * @param $file
     *
     * @throws FileNotFoundException
     */
    private function deleteSystemAssets($file)
    {
        $path = $this->systemImageDir . '/' . $file->getClientOriginalName();

        if ($this->filesystem->has($path)) {
            $this->filesystem->Delete($path);
        }
    }

    /**
     * @param $fileName
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     */
    private function uploadImage($fileName, $file)
    {
        if ($fileName) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream(
                $this->systemImageDir . '/' . $fileName,
                $stream
            );
        } else {
            return false;
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function checkValue($value)
    {
        if ($value) {
            return $value;
        } else {
            return '';
        }
    }
}