<?php

namespace App\Tests\Util;

use App\Entity\Report;
use PHPUnit\Framework\TestCase;
use App\Entity\Comment;
use App\Entity\Answer;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class CommentEntityTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCommentGetterAndSetter()
    {
        echo "\nCommentEntityTest:  Comment Entity \n";

        $comment = new Comment();
        $report = new Report();
        $user = new User();

        $comment->setComment('comment');
        $this->assertEquals('comment', $comment->getComment());

        $comment->setCommentType('normal');
        $this->assertEquals('normal', $comment->getCommentType());

        $comment->setCommentBy($user);
        $this->assertEquals($comment->getCommentBy(), $user);

        $comment->setReport($report);
        $this->assertEquals($comment->getReport(), $report);

        $comment->setEnabled(true);
        $this->assertEquals(true, $comment->isEnabled());

        $comment->setPrivate(true);
        $this->assertEquals(true, $comment->isPrivate());

        $comment->setHidden(true);
        $this->assertEquals(true, $comment->isHidden());

        $comment->setApproval(true);
        $this->assertEquals(true, $comment->getApproval());

    }
}
