<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Service\ApiReturnService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Class UserRepository
 *
 * @package App\Repository
 */
final class UserRepository
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
     * UserRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface  $params
     * @param ApiReturnService       $apiReturnService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        ApiReturnService $apiReturnService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);

        $client = BlobRestProxy::createBlobService($params->get('BLOB_ENDPOINTS_PROTOCOL'));
        $adapter = new AzureBlobStorageAdapter($client, 'profile-images');
        $this->filesystem = new Filesystem($adapter);

        $this->apiReturnService = $apiReturnService;
    }

    public function getById($user)
    {
        $getData = $this->repository->findBy([
            'id' => $user
        ]);

        return [
            "id" => $getData[0]->getId(),
            "first_name" => $getData[0]->getFirstName(),
            "last_name" => $getData[0]->getLastName(),
            "email" => $getData[0]->getEmail(),
            "tel_number" => $getData[0]->getTelNumber(),
            "mobile_number" => $getData[0]->getMobileNumber(),
            "enabled" => $getData[0]->isEnabled(),
            "archived" => $getData[0]->isArchived(),
            "roles" => $getData[0]->getRoles(),
            "company" => $this->checkCompany($getData[0]->getCompany()),
        ];
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

//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    private function companyIdValue($company)
//    {
//        if ($company) {
//            return $company->getId();
//        } else {
//            return '';
//        }
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    private function companyNameValue($company)
//    {
//        if ($company) {
//            return $company->getName();
//        } else {
//            return '';
//        }
//    }

    /**
     * @return User[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param string $token
     *
     * @return null|object
     */
    public function byToken(string $token)
    {
        return $this->repository->findOneBy([
            'token' => $token
        ]);
    }

    /**
     * @param string $email
     *
     * @return User|null|object
     */
    public function byEmail(string $email)
    {
        return $this->repository->findOneBy([
            'email' => $email
        ]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     * @param bool   $descending
     * @param string $search
     * @param User   $user
     *
     * @return User[]|array|object[]
     */
    public function paginated(
        int $offset,
        int $limit,
        string $sort,
        bool $descending,
        $searchFirstName,
        $searchLastName,
        $searchEmail,
        User $user)
    {
        
        // Find Sort
        switch ($sort) {
            case 'last_name':
                $sort = 'lastName';
                break;
            case 'first_name':
                $sort = 'firstName';
                break;
            case 'email':
            default:
                $sort = 'email';
                break;
        }

        $qb = $this->forRole($user);

        if ($searchFirstName !== '' && $searchLastName !== '') {
            $qb->andWhere('u.firstName LIKE :query1')
                ->andWhere('u.lastName LIKE :query2')
                ->setParameter('query1', "%$searchFirstName%")
                ->setParameter('query2', "%$searchLastName%");
        } elseif ($searchFirstName !== '') {
            $qb->andWhere('u.firstName LIKE :query')
                ->setParameter('query', "%$searchFirstName%");
        } elseif ($searchLastName !== '') {
            $qb->andWhere('u.lastName LIKE :query')
                ->setParameter('query', "%$searchLastName%");
        } elseif ($searchEmail !== '') {
            $qb->andWhere('u.email LIKE :query')
                ->setParameter('query', "%$searchEmail%");
        }

        // Process Search Term
//        if ($search !== '') {
//
//            $qb->orWhere('u.firstName LIKE :query')
//                ->orWhere('u.lastName LIKE :query')
//                ->orWhere('u.email LIKE :query')
//                ->setParameter('query', $search . "%");
//        }

//        if ($search !== '') {
//            $qb->andWhere('u.lastName LIKE :query')
//                ->setParameter('query', "%$search%");
//        }

        $qb->orderBy("u.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiReturnService->getQueuesUserReturns($qb->getQuery()->execute());
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('u');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                break;
            case 'ROLE_TEAM_LEAD': // get users based on team
                $queryBuilder->innerJoin('u.team', 't')
                    ->andWhere('t.teamLeader = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ANALYST': // get self
                $queryBuilder->andWhere('u.id = :id')
                    ->setParameter('id', $user->getId());
                break;
            case 'ROLE_ADMIN_USER': // get users based on company
                $queryBuilder->innerJoin('u.company', 'c')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany()->getId());
                break;
            case 'ROLE_USER_MANAGER':
                $queryBuilder->join('u.company', 'c')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany()->getId());

                break;
            default: // get self
                $queryBuilder->andWhere('u.id = :id')
                    ->setParameter('id', $user->getId());
                break;
        }
        return $queryBuilder;
    }

    /**
     * @param $user
     *
     * @return array
     */
    public function myProfile($user): array
    {
        return $this->apiReturnService->myProfile($user);
    }

    /**
     * @param User $user
     */
    public function enable(User $user)
    {
        $user->setEnabled(true);

        $this->save($user);
    }

    /**
     * @param User $user
     */
    public function save(User $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     */
    public function disable(User $user)
    {
        $user->setEnabled(false);

        $this->save($user);
    }

    /**
     * @param User $user
     */
    public function archive(User $user)
    {
        $user->setArchived(true);

        $this->save($user);
    }

    /**
     * @param $user
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     */
    public function saveImage($user, $file)
    {
        $id = $user->getId();

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $file->getClientOriginalName(), $stream);

            $user = $this->entityManager->getRepository(User::class)->find($id);
            $user->setImageFile($file->getClientOriginalName());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $user
     *
     * @throws FileNotFoundException
     */
    public function deleteImage(User $user)
    {
        $id = $user->getId();

        $user = $this->entityManager->getRepository(User::class)->find($id);

        $path = $user->getImageFile();

        $path = $id . '/' . $path;
        $this->filesystem->Delete($path);

        $user->setImageFile('');
        $this->entityManager->flush();
    }

    /**
     * @param $user
     *
     */
    public function removefromTeams(User $user)
    {
        $id = $user->getId();

        $user = $this->entityManager->getRepository(User::class)->find($id);

        $user->setTeam(null);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function listImage(User $user)
    {
        return $this->filesystem->listContents($user->getId());
    }

    /**
     * @param User $user
     */
    public function updateUser(User $user)
    {
        $userUpdate = $this->entityManager->getRepository(User::class)->find($user->getId());

        $userUpdate->setFirstName($user->getFirstName());
        $userUpdate->setLastName($user->getLastName());

        if ($user->getTelNumber()) {
            $userUpdate->setTelNumber($user->getTelNumber());
        }

        if ($user->getMobileNumber()) {
            $userUpdate->setMobileNumber($user->getMobileNumber());
        }

        $this->entityManager->persist($userUpdate);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function resetCompany(User $user)
    {
        $userUpdate = $this->entityManager->getRepository(User::class)->find($user->getId());

        $userUpdate->setCompany(null);

        $this->entityManager->persist($userUpdate);
        $this->entityManager->flush();

        return $this->apiReturnService->myProfile($user);
    }

    /**
     * @param $company
     *
     * @return array
     */
    private function checkCompany($company): ?array
    {
        $response = [];

        if ($company) {
            $response = [
                'id' => $company->getId(),
                'name' => $company->getName()
            ];
            return $response;
        } else {
            return [];
        }
    }
}