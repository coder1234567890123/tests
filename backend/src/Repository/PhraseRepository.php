<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Phrase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\Subject;
use App\Service\SearchPhrase\Parser;

/**
 * Class PhraseRepository
 *
 * @package App\Repository
 */
final class PhraseRepository extends AbstractController
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
     * @var SearchPhrase
     */
    private $parser;

    /**
     * @var TokenStorageInterface
     */
    private $userToken;

    /**
     * PhraseRepository constructor.
     * @param EntityManagerInterface $entityManager
     * @param Parser                 $parser
     * @param TokenStorageInterface  $token
     */
    public function __construct(EntityManagerInterface $entityManager, Parser $parser, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Phrase::class);
        $this->userToken     = $token->getToken()->getUser();
        $this->parser        = $parser;
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
     *
     *
     * @return UserTracking[]|array|object[]
     */
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search)
    {
        // Find Sort
        switch ($sort) {
            default:
                $sort = 'createdAt';
                break;
        }

        $qb = $this->repository->createQueryBuilder('pr');

        if ($search != '') {

            $searchNew = explode(" ", trim($search));

            foreach ($searchNew as $search) {

                $qb->where('pr.phrase LIKE :query')
                    ->orWhere('pr.priority LIKE :query')
                    ->orWhere('pr.searchType LIKE :query')
                    ->setParameter('query', "%$search%");

                return $qb->getQuery()->execute();
            }
        }

        $qb->orderBy("pr.$sort", $descending === true ? 'DESC' : 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->execute();
    }


    /**
     * @return Phrase[]|array|object[]
     */
    public function all()
    {

        return $this->repository->findBy([], ['priority' => 'DESC']);
    }

    /**
     * @param Phrase $phrase
     * @param string $id
     *
     * @return void
     */
    public function update(Phrase $phrase, string $id)
    {
        $qbUpate = $this->entityManager->getRepository(Phrase::class)->find($id);

        $qbUpate->setCreatedBy($this->getUser());
        $qbUpate->setPhrase($phrase->getPhrase());
        $qbUpate->setSearchType($phrase->getSearchType());

        $this->entityManager->flush();
    }

    /**
     * @var int $priority
     * @return Phrase[]|array|object[]
     */
    public function getGreaterThanPriority(int $priority)
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->andWhere('p.priority >= :priority')
            ->setParameter('priority', $priority)
            ->orderBy('p.priority', 'DESC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @var int $oldPriority
     * @var Phrase $phrase
     *
     * @throws
     */
    public function onPriorityUpdate(int $oldPriority, Phrase $phrase)
    {
        try {
            // begin database transaction
            $this->entityManager->beginTransaction();

            // get all records with greater or equal priority
            foreach ($this->getGreaterThanPriority($phrase->getPriority() <= $oldPriority ? $phrase->getPriority(): $oldPriority) as $existingPhrase) {
                if ($phrase->getId() !== $existingPhrase->getId()){// do not edit priority of edited (patch) record
                    if ($existingPhrase->getPriority() === $phrase->getPriority()){
                        $existingPhrase->setPriority($oldPriority);// switch priority with edited record
                        $this->entityManager->persist($existingPhrase);
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $th) {
            $this->entityManager->rollback();
            throw $th;
        }
    }

    /**
     * @var Phrase $phrase
     * @throws
     */
    public function onPriorityCreate(Phrase $phrase)
    {
        try {
            // begin database transaction
            $this->entityManager->beginTransaction();

            // get all records with greater or equal priority
            foreach ($this->getGreaterThanPriority($phrase->getPriority()) as $existingPhrase) {
                if ($phrase->getId() !== $existingPhrase->getId()){// do not edit priority of new record
                    $value = $existingPhrase->getPriority(); $value++;
                    $existingPhrase->setPriority($value);// update all other priorities to match new record
                    $this->entityManager->persist($existingPhrase);
                }
            }
            
            $this->entityManager->flush();
            $this->entityManager->commit();// apply all changes
        } catch (\Exception $th) {
            $this->entityManager->rollback();
            throw $th;
        }
    }

    /**
     * @var Phrase $phrase
     * @throws
     */
    public function onPhraseDelete(Phrase $phrase)
    {
        try {
            // begin database transaction
            $this->entityManager->beginTransaction();

            // get all records with greater or equal priority
            foreach ($this->getGreaterThanPriority($phrase->getPriority()) as $existingPhrase) {
                if ($phrase->getId() !== $existingPhrase->getId() && $existingPhrase->getPriority() > 0){// do not edit priority of phrase to be deleted
                    $value = $existingPhrase->getPriority(); $value--; //decrement priorities by one to compensate for deleted phrase
                    $existingPhrase->setPriority($value);
                    $this->entityManager->persist($existingPhrase);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();// apply all changes
        } catch (\Exception $th) {
            $this->entityManager->rollback();
            throw $th;
        }
    }

    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function disable(Phrase $phrase)
    {
        $phrase->setEnable(false);

        $this->save($phrase);
    }

    /**
     * @param Phrase $phrase
     */

    public function enable(Phrase $phrase)
    {
        $phrase->setEnable(true);

        $this->save($phrase);
    }

    /**
     * @return Phrase[]|array|object[]
     */
    public function archived()
    {
        return $this->repository->findBy([
            'enabled'  => false,
            'archived' => true
        ], ['priority' => 'DESC']);
    }

    /**
     * @param Phrase $phrase
     */
    public function archive(Phrase $phrase)
    {
        $phrase->setArchived(true);

        $this->save($phrase);
    }

    /**
     * @return Phrase[]|array|object[]
     */
    public function enabled()
    {
        return $this->repository->findBy([
            'enabled'  => true,
            'archived' => false
        ], ['priority' => 'DESC']);
    }

    /**
     * @param Phrase $phrase
     */
    public function save(Phrase $phrase)
    {
        $phrase->setCreatedBy($this->getUser());

        $this->entityManager->persist($phrase);
        $this->entityManager->flush();
    }

    /**
     * @param Phrase $phrase
     */
    public function delete(Phrase $phrase)
    {
        $this->entityManager->remove($phrase);
        $this->entityManager->flush();
    }
}

