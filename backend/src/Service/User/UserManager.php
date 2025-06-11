<?php declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Exception\User\InvalidTokenException;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class UserManager
 *
 * @package App\Service\User
 */
class UserManager
{
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * UserManager constructor.
     *
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Add a reset token to the user and update the DB.
     *
     * @param User $user
     *
     * @return bool
     */
    public function generateToken(User $user): bool
    {
        try {
            $token = Uuid::uuid4();

            $user
                ->setToken($token->toString())
                ->setTokenRequested(new \DateTimeImmutable());

            $this->repository->save($user);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $token
     * @param string $password
     *
     * @return bool
     * @throws InvalidTokenException
     */
    public function resetPassword(string $token, string $password)
    {
        /** @var User $user */
        $user = $this->repository->byToken($token);
        if (!$user) {
            throw new InvalidTokenException();
        }

        try {
            $now      = new \DateTimeImmutable();
            $interval = new \DateInterval('P60M');
        } catch (\Exception $e) {
            return false;
        }

        if ($user->getTokenRequested() <= $now->sub($interval)) {
            throw new InvalidTokenException();
        }

        try {
            $user = $this->revokeUserToken($user)
                ->setPassword($password);

            $this->repository->save($user);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return User
     */
    public function revokeUserToken(User $user): User
    {
        $user
            ->setToken(null)
            ->setTokenRequested(null);

        return $user;
    }
}