<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class Validator
 *
 * @package App\Service
 */
class Validator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Validator constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param $entity
     *
     * @return bool|JsonResponse
     */
    public function validate($entity)
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $errorArray = [];
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $errorArray[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse([
                'message' => 'Validation failed!',
                'errors'  => $errorArray
            ], 400);
        }

        return false;
    }
}