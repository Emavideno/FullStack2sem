<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Comment;
use App\Models\News;
use App\Database\Database;

class CommentTest extends TestCase
{
    private static $newsId;

    public static function setUpBeforeClass(): void
    {
        Database::enableTestMode(':memory:');

        $news = new News(1, 'Comment Test News', 'Excerpt', 'Content');
        $news->save();
        self::$newsId = $news->getId();
    }

    public static function tearDownAfterClass(): void
    {
        Database::disableTestMode();
    }

    protected function setUp(): void
    {
        $db = Database::getConnection();
        $db->exec("DELETE FROM comments");
    }

    public function testCreateComment(): void
    {
        $comment = new Comment(self::$newsId, 'Author', 'Test comment');
        $id = $comment->save();

        $this->assertNotFalse($id);
    }

    public function testFindByNewsId(): void
    {
        $comment1 = new Comment(self::$newsId, 'Author1', 'Text1');
        $comment2 = new Comment(self::$newsId, 'Author2', 'Text2');
        $comment1->save();
        $comment2->save();

        $comments = Comment::findByNewsId(self::$newsId);
        $this->assertCount(2, $comments);
    }

    public function testDeleteById(): void
    {
        $comment = new Comment(self::$newsId, 'Author', 'To delete');
        $id = $comment->save();

        Comment::deleteById($id);

        $comments = Comment::findByNewsId(self::$newsId);
        $this->assertCount(0, $comments);
    }

    public function testGetAllWithNewsTitle(): void
    {
        $comment = new Comment(self::$newsId, 'Author', 'Text');
        $comment->save();

        $comments = Comment::getAllWithNewsTitle();
        $this->assertIsArray($comments);
        $this->assertGreaterThan(0, count($comments));
    }

    public function testGetters(): void
    {
        $newsId = 123;
        $author = 'Test Author';
        $text = 'Test Text';

        $comment = new Comment($newsId, $author, $text);

        $this->assertEquals($newsId, $comment->getNewsId());
        $this->assertEquals($author, $comment->getAuthor());
        $this->assertEquals($text, $comment->getText());
        $this->assertNull($comment->getId());
    }

    public function testIdAfterSave(): void
    {
        $comment = new Comment(self::$newsId, 'Author', 'Text');
        $savedId = $comment->save();

        $this->assertNotFalse($savedId);
        $this->assertEquals($savedId, $comment->getId());
    }
}
