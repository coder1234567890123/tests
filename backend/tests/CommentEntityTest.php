<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Entity\Comment;
use App\Entity\Answer;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class CommentEntityTest extends TestCase
{
    public function testReportSectionGetterAndSetter()
    {
        $comment = new Comment();
        $user = new User();
        $answer = new Answer();

        $comment->setComment('comment');
        $this->assertEquals('comment', $comment->getComment());

        $comment->setAnswer($answer);
        $this->assertEquals($comment->getAnswer(), $answer);

        $comment->setCommentBy($user);
        $this->assertEquals($comment->getCommentBy(), $user);

        $comment->setEnabled(true);
        $this->assertEquals(true, $comment->isEnabled());

        $comment->setPrivate(true);
        $this->assertEquals(true, $comment->isPrivate());

    }
}
