<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employment;
use App\Entity\Proof;
use App\Entity\Qualification;
use App\Entity\Question;
use App\Entity\ProofStorage;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Class ProofStorageRepository
 *
 * @package App\Repository
 */
final class ProofStorageRepository
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
     * @var TokenStorageInterface
     */
    private $userToken;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * SubjectRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(ProofStorage::class);
        $this->repositoryProof    = $entityManager->getRepository(Proof::class);
        $this->userToken     = $token->getToken()->getUser();

        $client           = BlobRestProxy::createBlobService($params->get('BLOB_ENDPOINTS_PROTOCOL'));
        $adapter          = new AzureBlobStorageAdapter($client, 'profile-images');
        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * @param $subject
     * @param $file
     * @return bool
     * @throws FileExistsException
     */
    public function saveImage($subject, $file)
    {
        $id = $subject->getBlobFolder();

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $file->getClientOriginalName(), $stream);

            $proofstorage = new ProofStorage();

            $proofstorage->setSubject($subject);
            $proofstorage->setCreatedBy($this->userToken);
            $proofstorage->setImageFile($file->getClientOriginalName());

            $this->entityManager->persist($proofstorage);
            $this->entityManager->flush();

            return $proofstorage;
        }

        return false;
    }

    /**
     * @param ProofStorage $proofstorage
     * @throws FileNotFoundException
     */
    public function deleteImage(ProofStorage $proofstorage)
    {

        $id = $proofstorage->getSubject()->getBlobFolder();

        $path = $proofstorage->getImageFile();

        $path = $id . '/' . $path;

        $this->filesystem->Delete($path);

        $qb = $this->repositoryProof->createQueryBuilder('s');
        $qb->where('s.proofStorage = :proof')
            ->delete()
            ->setParameter('proof', $proofstorage->getId());
        $qb->getQuery()->execute();
        
        $qb = $this->repository->createQueryBuilder('s');
        $qb->where('s.id = :id')
           ->delete()
           ->setParameter('id', $proofstorage->getId());
        $qb->getQuery()->execute();

    }

    /**
     * @param Subject $subject
     * @return array
     */
    public function listImage(Subject $subject)
    {
        return $this->filesystem->listContents($subject->getBlobFolder());
    }

}
