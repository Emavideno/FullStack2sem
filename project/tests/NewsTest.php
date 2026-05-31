<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\News;
use App\Database\Database;

class NewsTest extends TestCase
{
    protected function setUp(): void
    {
        Database::enableTestMode(':memory:');
        $db = Database::getConnection();
        // Очищаем все таблицы перед каждым тестом
        $db->exec("DELETE FROM news");
        $db->exec("DELETE FROM reactions");
        $db->exec("DELETE FROM users");
        $db->exec("DELETE FROM sqlite_sequence");
    }

    protected function tearDown(): void
    {
        Database::disableTestMode();
    }

    public function testCreateNews(): void
    {
        $news = new News(1, 'Test Title', 'Test Excerpt', 'Test Content');
        $result = $news->save();

        $this->assertTrue($result);
        $this->assertNotNull($news->getId());
    }

    public function testFindNews(): void
    {
        $news = new News(1, 'Find Test', 'Excerpt', 'Content');
        $news->save();

        $found = News::find($news->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Find Test', $found['title']);
    }

    public function testAllNews(): void
    {
        // Сохраняем начальное количество
        $initialCount = count(News::all(1, 100));

        $news1 = new News(1, 'Title 1', 'Excerpt 1', 'Content 1');
        $news2 = new News(1, 'Title 2', 'Excerpt 2', 'Content 2');
        $news1->save();
        $news2->save();

        $all = News::all(1, 100);
        // Проверяем, что добавилось ровно 2 новости
        $this->assertEquals($initialCount + 2, count($all));
    }

    public function testUpdateNews(): void
    {
        $news = new News(1, 'Old Title', 'Old Excerpt', 'Old Content');
        $news->save();

        News::update($news->getId(), 1, 'New Title', 'New Excerpt', 'New Content');

        $found = News::find($news->getId());
        $this->assertEquals('New Title', $found['title']);
    }

    public function testDeleteNews(): void
    {
        $news = new News(1, 'To Delete', 'Excerpt', 'Content');
        $news->save();
        $id = $news->getId();

        News::deleteById($id);

        $found = News::find($id);
        $this->assertNull($found);
    }

    public function testIncrementViews(): void
    {
        $news = new News(1, 'Title', 'Excerpt', 'Content');
        $news->save();

        $initialViews = $news->getViews();

        News::incrementViews($news->getId());

        $found = News::find($news->getId());
        $this->assertEquals($initialViews + 1, $found['views']);
    }

    public function testCountAll(): void
    {
        $initialCount = News::countAll();

        $news1 = new News(1, 'Title 1', 'Excerpt 1', 'Content 1');
        $news2 = new News(1, 'Title 2', 'Excerpt 2', 'Content 2');
        $news1->save();
        $news2->save();

        $count = News::countAll();
        $this->assertEquals($initialCount + 2, $count);
    }

    public function testSearchNews(): void
    {
        $news1 = new News(1, 'PHP 8.5 Release', 'Content about PHP', 'Full PHP content');
        $news2 = new News(1, 'Python 3.13', 'Content about Python', 'Full Python content');
        $news1->save();
        $news2->save();

        $results = News::search('PHP', 1, 10);
        $this->assertCount(1, $results);
        $this->assertEquals('PHP 8.5 Release', $results[0]['title']);
    }

    public function testCountSearchResults(): void
    {
        $news1 = new News(1, 'Laravel 12', 'Laravel news', 'Content');
        $news2 = new News(1, 'Symfony 7', 'Symfony news', 'Content');
        $news1->save();
        $news2->save();

        $count = News::countSearchResults('Laravel');
        $this->assertEquals(1, $count);
    }

    public function testFindByCategories(): void
    {
        $news1 = new News(1, 'AI News', 'AI content', 'Content');
        $news2 = new News(2, 'Web News', 'Web content', 'Content');
        $news1->save();
        $news2->save();

        $results = News::findByCategories([1], 1, 10);
        $this->assertCount(1, $results);
        $this->assertEquals('AI News', $results[0]['title']);
    }

    public function testCountByCategories(): void
    {
        $news1 = new News(1, 'AI News 1', 'Content', 'Content');
        $news2 = new News(1, 'AI News 2', 'Content', 'Content');
        $news3 = new News(2, 'Web News', 'Content', 'Content');
        $news1->save();
        $news2->save();
        $news3->save();

        $count = News::countByCategories([1]);
        $this->assertEquals(2, $count);
    }

    public function testAllAdmin(): void
    {
        $initialCount = count(News::allAdmin());

        $news1 = new News(1, 'Admin Test 1', 'Content', 'Content');
        $news2 = new News(1, 'Admin Test 2', 'Content', 'Content');
        $news1->save();
        $news2->save();

        $all = News::allAdmin();
        $this->assertEquals($initialCount + 2, count($all));
    }

    public function testDeleteById(): void
    {
        $news = new News(1, 'To Delete', 'Content', 'Content');
        $news->save();
        $id = $news->getId();

        News::deleteById($id);

        $found = News::find($id);
        $this->assertNull($found);
    }

    public function testToggleReaction(): void
    {
        $user = new \App\Models\User('reaction_user', 'pass');
        $user->save();
        $userId = $user->getId();

        $news = new News(1, 'React Test', 'Content', 'Content');
        $news->save();
        $newsId = $news->getId();

        $result = News::toggleReaction($newsId, $userId, '👍');
        $this->assertTrue($result);

        $count = News::getReactionCount($newsId, '👍');
        $this->assertEquals(1, $count);

        $result = News::toggleReaction($newsId, $userId, '👍');
        $this->assertFalse($result);

        $count = News::getReactionCount($newsId, '👍');
        $this->assertEquals(0, $count);
    }

    public function testGetUserReactions(): void
    {
        $user = new \App\Models\User('reaction_user2', 'pass');
        $user->save();
        $userId = $user->getId();

        $news = new News(1, 'User Reactions Test', 'Content', 'Content');
        $news->save();
        $newsId = $news->getId();

        News::toggleReaction($newsId, $userId, '👍');
        News::toggleReaction($newsId, $userId, '❤️');

        $reactions = News::getUserReactions($newsId, $userId);
        $this->assertCount(2, $reactions);
        $this->assertContains('👍', $reactions);
        $this->assertContains('❤️', $reactions);
    }

    public function testSourceUrl(): void
    {
        $news = new News(1, 'Source Test', 'Excerpt', 'Content', 'https://example.com');
        $news->save();

        $found = News::find($news->getId());
        $this->assertEquals('https://example.com', $found['source_url']);
    }
}
