<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\User;
use App\Service\ApiCompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Class PhraseRepository
 *
 * @package App\Repository
 */
final class CompanyRepository extends AbstractController
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
     * CompanyRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $token
     * @param ParameterBagInterface  $params
     *
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $token,
        ParameterBagInterface $params,
        ApiCompanyService $apiCompanyService
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Company::class);
        $this->userToken = $token->getToken()->getUser();

        $client = BlobRestProxy::createBlobService($params->get('BLOB_ENDPOINTS_PROTOCOL'));
        $adapter = new AzureBlobStorageAdapter($client, 'company-images');
        $this->filesystem = new Filesystem($adapter);

        $this->apiCompanyService = $apiCompanyService;
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
     * @return Company[]|array|object[]
     */
    public function all()
    {
        return $this->repository->findAll([]);
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
     * @return Company[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search, User $user)
    {
        // Find Sort
        switch ($sort) {
            case 'created_at':
                $sort = 'createdAt';
                break;
            case 'name':
                $sort = 'name';
                break;
        }

        $qb = $this->forRole($user);

        // Process Search Term
        if ($search != '') {
            if ($user->hasRole('ROLE_TEAM_LEAD')) {
                $qb = $this->repository->createQueryBuilder('c');
            }
            $qb
                ->andWhere('c.name LIKE :query')
                ->orWhere('c.registrationNumber LIKE :query')
                ->orWhere('c.vatNumber LIKE :query')
                ->setParameter('query', "%$search%");
        }

        $qb->orderBy("c.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->apiCompanyService->companyIndex($qb->getQuery()->execute());
    }

    /**
     * @param Company $company
     */
    public function enable(Company $company)
    {
        $company->setEnabled(true);

        $this->save($company);
    }

    /**
     * @param Company $company
     */
    public function disable(Company $company)
    {
        $company->setEnabled(false);

        $this->save($company);
    }

    /**
     * @param Company $company
     */
    public function archive(Company $company)
    {
        $company->setArchived(true);

        $this->save($company);
    }

    /**
     * @return Company[]|array|object[]
     */
    public function archived()
    {
        return $this->repository->findBy([
            'enabled' => false,
            'archived' => true
        ]);
    }

    /**
     * @return Company[]|array|object[]
     */
    public function enabled()
    {
        return $this->repository->findBy([
            'enabled' => true,
            'archived' => false
        ]);
    }

    /**
     * @param Company $company
     *
     * @return boolean
     */
    public function companyCheck(Company $company)
    {
        $userCompanyId = $this->getUser()->getCompanyId();
        $companyId = $company->getId();

        if ($userCompanyId === $companyId) {
            return true;
        }

        return false;
    }

    /**
     * @param $company
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     */
    public function saveImage($company, $file)
    {
        $id = $company->getId();

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $file->getClientOriginalName(), $stream);

            $company = $this->entityManager->getRepository(Company::class)->find($id);
            $company->setImageFile($file->getClientOriginalName());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deleteImage(Company $company)
    {
        $id = $company->getId();

        $company = $this->entityManager->getRepository(Company::class)->find($id);

        $path = $company->getImageFile();

        $path = $id . '/' . $path;
        $this->filesystem->Delete($path);

        $company->setImageFile('');
        $this->entityManager->flush();
    }

    /**
     * @param $company
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function saveImageFooterLogo($company, $file)
    {
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $dateTime = date("mdYhis");
        $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_footer_logo.' . $ext, $file->getClientOriginalName());

        $id = $company->getId();
        $this->deleteImageFooterLogo($company);

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $fileName, $stream);

            $company = $this->entityManager->getRepository(Company::class)->find($id);
            $company->setImageFooterLogo($fileName);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deleteImageFooterLogo(Company $company)
    {
        $id = $company->getId();
        $path = $company->getImageFooterLogo();
        $path = $id . '/' . $path;

        if ($this->filesystem->has($path)) {
            $company = $this->entityManager->getRepository(Company::class)->find($id);

            $this->filesystem->Delete($path);

            $company->setImageFooterLogo('');
            $this->entityManager->flush();
        }
    }

    /**
     * @param $company
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function saveImageFrontPage($company, $file)
    {
        $this->deleteImageFrontPage($company);

        $id = $company->getId();

        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $dateTime = date("mdYhis");
        $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_front_page.' . $ext, $file->getClientOriginalName());

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $fileName, $stream);

            $company = $this->entityManager->getRepository(Company::class)->find($id);
            $company->setImageFrontPage($fileName);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $company
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function saveFontLogoPage($company, $file)
    {
        $this->deleteImageFrontLogo($company);

        $id = $company->getId();

        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $dateTime = date("mdYhis");
        $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_front_logo.' . $ext, $file->getClientOriginalName());

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $fileName, $stream);

            $company = $this->entityManager->getRepository(Company::class)->find($id);
            $company->setCoverLogo($fileName);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deleteImageFrontLogo(Company $company)
    {
        $id = $company->getId();
        $path = $company->getImageFrontPage();
        $path = $id . '/' . $path;

        if ($this->filesystem->has($path)) {
            $company = $this->entityManager->getRepository(Company::class)->find($id);

            $this->filesystem->Delete($path);

            $company->setImageFrontPage('');
            $this->entityManager->flush();
        }
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deleteImageFrontPage(Company $company)
    {
        $id = $company->getId();
        $path = $company->getImageFrontPage();
        $path = $id . '/' . $path;

        if ($this->filesystem->has($path)) {
            $company = $this->entityManager->getRepository(Company::class)->find($id);

            $this->filesystem->Delete($path);

            $company->setImageFrontPage('');
            $this->entityManager->flush();
        }
    }

    /**
     * @param $company
     * @param $file
     *
     * @return bool
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function saveImageCoFrontPage($company, $file)
    {
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $dateTime = date("mdYhis");
        $fileName = str_replace($file->getClientOriginalName(), $dateTime . '_footer_logo.' . $ext, $file->getClientOriginalName());

        $this->deleteImageCoFrontPage($company);

        $id = $company->getId();

        if ($file->isValid()) {
            $stream = fopen($file->getRealPath(), 'r+');
            $this->filesystem->writeStream($id . '/' . $fileName, $stream);

            $company = $this->entityManager->getRepository(Company::class)->find($id);
            $company->setImageCoFrontPage($fileName);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param $company
     *
     * @throws FileNotFoundException
     */
    public function deleteImageCoFrontPage(Company $company)
    {
        $id = $company->getId();
        $path = $company->getImageCoFrontPage();
        $path = $id . '/' . $path;

        if ($this->filesystem->has($path)) {
            $id = $company->getId();

            $company = $this->entityManager->getRepository(Company::class)->find($id);

            $path = $company->getImageCoFrontPage();

            $path = $id . '/' . $path;
            $this->filesystem->Delete($path);

            $company->setImageCoFrontPage('');
            $this->entityManager->flush();
        }
    }

    /**
     * @param Company $company
     *
     * @return array
     *
     */
    public function listImage(Company $company)
    {
        return $this->filesystem->listContents($company->getId());
    }

    /**
     * @param $company
     *
     * @return array
     */
    public function getCompanyById($company)
    {
        return $this->apiCompanyService->getCompany($company);
    }

    /**
     * @param Company $company
     *
     * @return array
     */
    public function save(Company $company)
    {
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $this->apiCompanyService->getCompany($company);
    }

    /**
     * @param User $user
     *
     * @return QueryBuilder
     */
    private function forRole(User $user)
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');
        switch ($user->getRoles()[0]) {
            case 'ROLE_SUPER_ADMIN':
                break;
            case 'ROLE_TEAM_LEAD': // get company based on team assigned
                $queryBuilder->innerJoin('c.team', 't')
                    ->andWhere('t.teamLeader = :tid')
                    ->setParameter('tid', $user->getId());
                break;
            case 'ROLE_ANALYST':

                //Todo Fix this issues will have to look at the company API return
                if ($user->getTeam()) {
                    $queryBuilder->innerJoin('c.team', 't')
                        ->andWhere('t.id = :id')
                        ->setParameter('id', $user->getTeam()->getId());
                } else {
                    $queryBuilder->innerJoin('c.team', 't')
                        ->andWhere('t.id = :id')
                        ->setParameter('id', $user->getId());
                }
                break;
            case 'ROLE_ADMIN_USER': // view only details of the company you belong to
            case 'ROLE_USER_MANAGER':
            case 'ROLE_USER_STANDARD':
                $queryBuilder->andWhere('c.id = :id')
                    ->setParameter('id', $user->getCompany()->getId());
                break;
        }
        return $queryBuilder;
    }

    private function checkTeamId($team)
    {
        if ($team) {
            return true;
        }
    }

    /**
     * @param $user
     *
     * @return array
     */
    public function myCompany($user): array
    {
        return $this->apiCompanyService->myCompany($user);
    }

    /**
     * @param Company $company
     */
    public function removefromCompany(Company $company)
    {
        $id = $company->getId();

        $company = $this->entityManager->getRepository(Company::class)->find($id);

        $company->setTeam(null);
        $this->entityManager->flush();
    }
}
