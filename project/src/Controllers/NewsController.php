<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Models\News;
use App\Models\Category;
use App\Models\Comment;
use App\Logger\LoggerFactory;
use Psr\Http\Message\ServerRequestInterface;

class NewsController extends AbstractController
{
    #[Route('/', 'GET')]
    public function index(ServerRequestInterface $request): void
    {
        try {
            $queryParams = $request->getQueryParams();
            $page = (int) ($queryParams['page'] ?? 1);
            $page = max(1, $page);
            $perPage = 5;

            $news = News::all($page, $perPage);
            $totalNews = News::countAll();
            $totalPages = ceil($totalNews / $perPage);

            $categories = Category::all();

            $this->render('news/index', [
                'news' => $news,
                'categories' => $categories,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Index page error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->sendError('Ошибка загрузки страницы', 500);
        }
    }

    #[Route('/news/{id}', 'GET')]
    public function show(ServerRequestInterface $request): void
    {
        try {
            $id = (int) $this->getParam($request, 'id');
            News::incrementViews($id);
            $news = News::find($id);

            if (!$news) {
                $this->sendError('Новость не найдена', 404);
                return;
            }

            $comments = Comment::findByNewsId($id);

            $this->render('news/show', [
                'news' => $news,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Show news error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id' => $id ?? null
            ]);
            $this->sendError('Ошибка загрузки новости', 500);
        }
    }

    #[Route('/search', 'GET')]
    public function search(ServerRequestInterface $request): void
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = trim($queryParams['q'] ?? '');

            if (mb_strlen($query) < 2) {
                $this->render('news/search', [
                    'news' => [],
                    'query' => $query,
                    'total' => 0,
                    'error' => 'Введите хотя бы 2 символа для поиска'
                ]);
                return;
            }

            $page = (int) ($queryParams['page'] ?? 1);
            $page = max(1, $page);
            $perPage = 5;

            $news = News::search($query, $page, $perPage);
            $total = News::countSearchResults($query);
            $totalPages = ceil($total / $perPage);

            $this->render('news/search', [
                'news' => $news,
                'query' => $query,
                'total' => $total,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Search error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'query' => $query ?? null
            ]);
            $this->sendError('Ошибка поиска', 500);
        }
    }

    #[Route('/api/news/filter', 'POST')]
    public function filter(ServerRequestInterface $request): void
    {
        try {
            $postData = $this->getPostData($request);
            $categoryIds = $postData['categories'] ?? [];
            $page = (int) ($postData['page'] ?? 1);
            $page = max(1, $page);
            $perPage = 5;

            if (!empty($categoryIds)) {
                $news = News::findByCategories($categoryIds, $page, $perPage);
                $total = News::countByCategories($categoryIds);
            } else {
                $news = News::all($page, $perPage);
                $total = News::countAll();
            }

            $totalPages = ceil($total / $perPage);

            ob_start();
            extract(['news' => $news, 'currentPage' => $page, 'totalPages' => $totalPages]);
            include __DIR__ . '/../../views/news/_news_list.php';
            $html = ob_get_clean();

            $this->jsonResponse([
                'html' => $html,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Filter error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->jsonResponse(['html' => '', 'error' => 'Ошибка фильтрации']);
        }
    }

    #[Route('/api/news/{id}/comment', 'POST')]
    public function addCommentApi(ServerRequestInterface $request): void
    {
        try {
            $newsId = (int) $this->getParam($request, 'id');
            $author = $_SESSION['user_login'] ?? 'Аноним';
            $postData = $this->getPostData($request);
            $text = trim($postData['text'] ?? '');

            $validator = new \App\Validation\Validator();
            $validator
                ->validateNotEmpty('text', $text, 'Комментарий не может быть пустым')
                ->validateMaxLength('text', $text, 500, 'Комментарий не должен превышать 500 символов');

            if (!$validator->isValid()) {
                $this->jsonResponse(['success' => false, 'error' => $validator->getFirstError()]);
                return;
            }

            $comment = new Comment($newsId, $author, $text);
            $commentId = $comment->save();

            if ($commentId) {
                $this->jsonResponse([
                    'success' => true,
                    'comment' => [
                        'id' => $commentId,
                        'author' => $author,
                        'text' => nl2br(htmlspecialchars($text)),
                        'date' => date('d.m.Y H:i')
                    ]
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Ошибка сохранения']);
            }
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Add comment error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'newsId' => $newsId ?? null
            ]);
            $this->jsonResponse(['success' => false, 'error' => 'Ошибка добавления комментария']);
        }
    }

    #[Route('/api/news/{id}/user-reaction', 'GET')]
    public function getUserReaction(ServerRequestInterface $request): void
    {
        try {
            $newsId = (int) $this->getParam($request, 'id');
            $userId = $_SESSION['user_id'] ?? null;
            $queryParams = $request->getQueryParams();
            $reactionType = $queryParams['type'] ?? '';

            if (!$userId) {
                $this->jsonResponse(['hasReaction' => false]);
                return;
            }

            $hasReaction = News::hasUserReaction($newsId, $userId, $reactionType);
            $this->jsonResponse(['hasReaction' => $hasReaction]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Get user reaction error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->jsonResponse(['hasReaction' => false]);
        }
    }

    #[Route('/api/news/{id}/user-reactions', 'GET')]
    public function getUserReactions(ServerRequestInterface $request): void
    {
        try {
            $newsId = (int) $this->getParam($request, 'id');
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $this->jsonResponse(['reactions' => []]);
                return;
            }

            $reactions = News::getUserReactions($newsId, $userId);
            $this->jsonResponse(['reactions' => $reactions]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Get user reactions error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->jsonResponse(['reactions' => []]);
        }
    }

    #[Route('/api/news/{id}/react', 'POST')]
    public function react(ServerRequestInterface $request): void
    {
        try {
            $newsId = (int) $this->getParam($request, 'id');
            $userId = $_SESSION['user_id'] ?? null;
            $postData = $this->getPostData($request);
            $reactionType = $postData['type'] ?? '';

            if (!$userId) {
                $this->jsonResponse(['success' => false, 'error' => 'Необходимо авторизоваться']);
                return;
            }

            if (!in_array($reactionType, ['👍', '❤️', '🔥', '😊', '😢'])) {
                $this->jsonResponse(['success' => false, 'error' => 'Неверный тип реакции']);
                return;
            }

            $result = News::toggleReaction($newsId, $userId, $reactionType);
            $newCount = News::getReactionCount($newsId, $reactionType);

            $this->jsonResponse([
                'success' => true,
                'action' => $result ? 'added' : 'removed',
                'type' => $reactionType,
                'count' => $newCount
            ]);
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('React error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'newsId' => $newsId ?? null
            ]);
            $this->jsonResponse(['success' => false, 'error' => 'Ошибка при обработке реакции']);
        }
    }
}
