<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Category;
use App\Database\Database;

class CategoryTest extends TestCase
{
    protected function setUp(): void
    {
        Database::enableTestMode(':memory:');
    }

    protected function tearDown(): void
    {
        Database::disableTestMode();
    }

    public function testCreateCategory(): void
    {
        $category = new Category('Test Category', 'test-category');
        $result = $category->save();

        $this->assertTrue($result);
        $this->assertNotNull($category->getId());
    }

    public function testAllCategories(): void
    {
        $categories = Category::all();
        $this->assertEquals(12, count($categories));
    }

    public function testFindCategory(): void
    {
        $category = new Category('Find Test', 'find-test');
        $category->save();

        $id = $category->getId();
        $found = Category::find($id);

        $this->assertNotNull($found);
        $this->assertEquals('Find Test', $found['name']);
    }

    public function testCategoryGetters(): void
    {
        $category = new Category('Getter Test', 'getter-test');
        $category->save();

        $this->assertEquals('Getter Test', $category->getName());
        $this->assertEquals('getter-test', $category->getSlug());
    }
}
