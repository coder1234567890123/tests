<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subject;
use App\Entity\User;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use App\Service\Profile;

/**
 * Class SubjectRepository
 *
 * @package App\Repository
 */
final class SubjectRepository
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
     * @param ApiReturnService       $apiReturnService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        ApiReturnService $apiReturnService

    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Subject::class);
        $this->userToken = $token->getToken()->getUser();

        $client = BlobRestProxy::createBlobService($params->get('BLOB_ENDPOINTS_PROTOCOL'));
        $adapter = new AzureBlobStorageAdapter($client, 'subject-images');
        $this->filesystem = new Filesystem($adapter);
        $this->apiReturnService = $apiReturnService;
    }

    /**
     * @param string $id
     *
     * @return null|object
     */
    public function find(string $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return Subject[]|array|object[]
     */
    public function all()
    {
        if ($this->userToken->getCompanyId() !== null) {
            return $this->repository->findBy([
                'company' => $this->userToken->getCompanyId()
            ]);
        }

        return $this->repository->findAll();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @param string $subjectId
     * @param string $companyId
     *
     * @return Subject[]|array|object[]
     */
    public function getSubjectByIdCompany(string $subjectId, string $companyId)
    {
        return $this->repository->findBy([
            'identification' => $subjectId,
            'company' => $companyId
        ]);
    }

    /**
     * @param User   $user
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     * @param bool   $descending
     * @param string $search
     *
     *
     * @return Subject[]|array|object[]
     */
    public function paginated(
        User $user,
        int $offset,
        int $limit,
        string $sort,
        bool $descending,
        string $searchFirstName,
        string $searchLastName,
        string $searchIdNo
    )
    {
        // Find Sort
        switch ($sort) {
            case 'created_at':
                $sort = 'createdAt';
                break;
            case 'last_name':
                $sort = 'lastName';
                break;
            case 'status':
                $sort = 'status';
                break;
            case 'first_name':
            default:
                $sort = 'firstName';
                break;
        }
        $qb = $this->forRole($user);

        //TODO Fix search
        if ($searchFirstName !== '' && $searchLastName !== '') {
            $qb->andWhere('s.firstName LIKE :query1')
                ->andWhere('s.lastName LIKE :query2')
                ->setParameter('query1', "%$searchFirstName%")
                ->setParameter('query2', "%$searchLastName%");
        } elseif ($searchFirstName !== '') {
            $qb->andWhere('s.firstName LIKE :query')
                ->setParameter('query', "%$searchFirstName%");
        } elseif ($searchLastName !== '') {
            $qb->andWhere('s.lastName LIKE :query')
                ->setParameter('query', "%$searchLastName%");
        } elseif ($searchIdNo !== '') {
            $qb->andWhere('s.identification LIKE :query')
                ->setParameter('query', "%$searchIdNo%");
        }

        $qb->orderBy("s.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiReturnService->getSubjectIndex($qb->getQuery()->execute());
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getSubjectById($subject)
    {
        return $this->apiReturnService->getSubject($subject);
    }

    /**
     * @param $subject
     *
     * @return mixed
     */
    public function getSubjectByIdentification($subjectId)
    {
        //To Do Just for testing change

        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.identification = :subjectId')
            ->setParameter('subjectId', $subjectId)
            ->getQuery();

        return $qb->execute();
        //return $this->apiReturnService->getSubjectByIdentification($subjectId);
    }

    /**
     * @param $subjectId
     *
     * @return array|object[]
     */
    public function duplicateIdCheck($subjectId)
    {
        return $this->repository->findBy([
            'identification' => $subjectId
        ]);
    }

    /**
     * @param Subject $subject
     *
     * @throws Exception
     */
    public
    function enable(Subject $subject)
    {
        $subject->setEnabled(true);

        $this->save($subject);
    }

    /**
     * @param Subject $subject
     *
     * @return mixed
     * @throws Exception
     */
    public function save(Subject $subject)
    {
        if (!$subject->getBlobFolder() && !$subject->getId()) {
            $uuid = Uuid::uuid4();
            $subject->setBlobFolder($uuid->toString());
        }
        $this->entityManager->persist($subject);
        $this->entityManager->flush();

        return $this->apiReturnService->getSubject($subject);
    }

    /**
     * @param Subject $subject
     *
     * @throws Exception
     */
    public
    function disable(Subject $subject)
    {
        $subject->setEnabled(false);

        $this->save($subject);
    }

    /**
     * @param Subject $subject
     * @param         $file
     *
     * @return bool
     * @throws FileExistsException
     */
    public
    function saveImage(Subject $subject, $file)
    {
        $id = $subject->getBlobFolder();

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($subject->getId() . '/' . $file->getClientOriginalName(), $stream);

            $subject = $this->entityManager->getRepository(Subject::class)->find($subject);
            $subject->setImageFile($file->getClientOriginalName());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $subject
     *
     * @throws FileNotFoundException
     */
    public
    function deleteImage(Subject $subject)
    {
        $id = $subject->getBlobFolder();

        $subject = $this->entityManager->getRepository(Subject::class)->find($subject);

        $path = $subject->getImageFile();

        $path = $id . '/' . $path;
        $this->filesystem->Delete($path);

        $subject->setImageFile('');
        $this->entityManager->flush();
    }

    /**
     * @param Subject $subject
     *
     * @return array
     */
    public
    function listImage(Subject $subject)
    {
        return $this->filesystem->listContents($subject->getId());
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private
    function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('s');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                break;
            case 'ROLE_TEAM_LEAD': // get subject based on team assigned to company
                $queryBuilder->innerJoin('s.company', 'c')
                    ->innerJoin('c.team', 't')
                    ->andWhere('t.teamLeader = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ANALYST':
                $queryBuilder->innerJoin('s.company', 'c')
                    ->innerJoin('c.team', 't')
                    ->andWhere('t.id = :id')
                    ->setParameter('id', $user->getTeam()->getId());
                break;
            case 'ROLE_ADMIN_USER': // get subject based on company both user and subject belong to
            case 'ROLE_USER_MANAGER':
                $queryBuilder->innerJoin('s.company', 'c')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany()->getId());
                break;
            default: // get subjects based on company and (standard) user created subjects
                $queryBuilder->innerJoin('s.company', 'c')
                    ->andWhere('s.company = :cid')
                    ->andWhere('c.id = :cid')
                    ->setParameter('cid', $user->getCompany()->getId())
                    ->andWhere('s.createdBy = :id')
                    ->setParameter('id', $user->getId());
                break;
        }
        return $queryBuilder;
    }

    /**
     * @param $subject
     */
    public function abandonedStatus($subject){

        $subject->setStatus('abandoned');
        $this->entityManager->persist($subject);
        $this->entityManager->flush();
    }
}
