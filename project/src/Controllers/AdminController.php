<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Models\News;
use App\Models\Category;
use App\Models\Comment;
use App\Logger\LoggerFactory;
use Psr\Http\Message\ServerRequestInterface;

class AdminController extends AbstractController
{
    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
            $this->redirect('/');
        }
    }

    #[Route('/admin/news', 'GET')]
    public function list(ServerRequestInterface $request): void
    {
        $this->requireAdmin();
        $news = News::allAdmin();
        $this->render('admin/news/list', ['news' => $news]);
    }

    #[Route('/admin/news/create', 'GET')]
    public function createForm(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        $categories = Category::all();
        $this->render('admin/news/form', [
            'news' => null,
            'categories' => $categories,
            'isEdit' => false
        ]);
    }

    #[Route('/admin/news/store', 'POST')]
    public function store(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        try {
            $postData = $this->getPostData($request);
            $categoryId = (int) ($postData['category_id'] ?? 0);
            $title = trim($postData['title'] ?? '');
            $excerpt = trim($postData['excerpt'] ?? '');
            $content = trim($postData['content'] ?? '');

            $validator = new \App\Validation\Validator();
            $validator
                ->validateNotEmpty('title', $title, 'Заголовок не может быть пустым')
                ->validateMinLength('title', $title, 3, 'Заголовок должен быть не менее 3 символов')
                ->validateNotEmpty('content', $content, 'Текст новости не может быть пустым')
                ->validateMinLength('content', $content, 10, 'Текст новости должен быть не менее 10 символов');

            if (!$validator->isValid()) {
                $this->render('admin/news/form', [
                    'news' => null,
                    'categories' => Category::all(),
                    'isEdit' => false,
                    'error' => $validator->getFirstError()
                ]);
                return;
            }

            $sourceUrl = $postData['source_url'] ?? '';
            $news = new News($categoryId ?: null, $title, $excerpt, $content, $sourceUrl);

            $news->save();

            $this->redirect('/admin/news');
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Admin store news error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->redirect('/admin/news/create');
        }
    }

    #[Route('/admin/news/edit/{id}', 'GET')]
    public function editForm(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        try {
            $id = (int) $this->getParam($request, 'id');
            $news = News::find($id);
            $categories = Category::all();

            if (!$news) {
                $this->redirect('/admin/news');
                return;
            }

            $this->render('admin/news/form', [
                'news' => $news,
                'categories' => $categories,
                'isEdit' => true
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Admin edit form error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->redirect('/admin/news');
        }
    }

    #[Route('/admin/news/update/{id}', 'POST')]
    public function update(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        try {
            $id = (int) $this->getParam($request, 'id');
            $newsData = News::find($id);

            if (!$newsData) {
                $this->redirect('/admin/news');
                return;
            }

            $postData = $this->getPostData($request);
            $categoryId = (int) ($postData['category_id'] ?? 0);
            $title = $postData['title'] ?? '';
            $excerpt = $postData['excerpt'] ?? '';
            $content = $postData['content'] ?? '';

            $sourceUrl = $postData['source_url'] ?? '';
            News::update($id, $categoryId ?: null, $title, $excerpt, $content, $sourceUrl);

            $this->redirect('/admin/news');
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Admin update news error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->redirect('/admin/news');
        }
    }

    #[Route('/admin/news/delete/{id}', 'GET')]
    public function delete(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        try {
            $id = (int) $this->getParam($request, 'id');
            News::deleteById($id);
            $this->redirect('/admin/news');
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Admin delete news error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->redirect('/admin/news');
        }
    }

    #[Route('/admin/comments', 'GET')]
    public function commentsList(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        $comments = Comment::getAllWithNewsTitle();
        $this->render('admin/comments/list', ['comments' => $comments]);
    }

    #[Route('/admin/comments/delete/{id}', 'GET')]
    public function deleteComment(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        try {
            $id = (int) $this->getParam($request, 'id');
            Comment::deleteById($id);
            $this->redirect('/admin/comments');
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Admin delete comment error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->redirect('/admin/comments');
        }
    }

    #[Route('/admin/parse', 'GET')]
    public function parseRss(ServerRequestInterface $request): void
    {
        $this->requireAdmin();

        $parser = new \App\Services\RssParser();
        $results = $parser->parseAll();

        $this->render('admin/parse/index', ['results' => $results]);
    }
}
