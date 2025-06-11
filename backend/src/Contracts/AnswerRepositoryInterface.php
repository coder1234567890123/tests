<?php

namespace App\Contracts;

use App\Entity\Answer;
use App\Entity\AnswerRepository;

/**
 * Interface RepositoryInterface
 * @package App\Contracts
 */
interface AnswerRepositoryInterface
{
    public function find(string $id);
    public function all();
    public function count();
    public function paginated(int $offset, int $limit, string $sort, bool $descending, string $search);
    public function findBySubject($subjectId, $questionId, $reportId);
    public function enable(Answer $answer);
    public function disable(Answer $answer);
    public function skip(Answer $answer);
    public function notApplicable(Answer $answer);
    public function save(Answer $answer);
    public function getSubjectAnswers($subject);

}

