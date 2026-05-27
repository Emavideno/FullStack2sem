<?php
namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Database\Database;

class NewsController extends AbstractController
{
    #[Route('/', 'GET')]
    public function index(array $request): void
    {
        $db = Database::getConnection();

        $stmt = $db->query("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM likes WHERE news_id = n.id) as likes,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count
            FROM news n
            ORDER BY n.created_at DESC
        ");
        $news = $stmt->fetchAll();

        $stmt = $db->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll();

        $this->render('news/index', [
            'news' => $news,
            'categories' => $categories
        ]);
    }

    #[Route('/news/{id}', 'GET')]
    public function show(array $request): void
    {
        $id = (int) $this->getParam($request, 'id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM likes WHERE news_id = n.id) as likes,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count
            FROM news n
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        $news = $stmt->fetch();

        if (!$news) {
            $this->sendError('Новость не найдена', 404);
            return;
        }

        $stmt = $db->prepare("
            SELECT 
                id,
                COALESCE(author, 'Аноним') as author,
                text,
                created_at as date
            FROM comments 
            WHERE news_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$id]);
        $comments = $stmt->fetchAll();

        $this->render('news/show', [
            'news' => $news,
            'comments' => $comments
        ]);
    }
}
