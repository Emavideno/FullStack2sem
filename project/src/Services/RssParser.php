<?php

namespace App\Services;

use App\Models\News;
use App\Database\Database;

class RssParser
{
    private array $sources = [
        [
            'name' => 'Habr',
            'url' => 'https://habr.com/ru/rss/all/all/',
        ],
        [
            'name' => 'Tproger',
            'url' => 'https://tproger.ru/feed/',
        ],
    ];

    private array $categoryMapping = [
        'development' => [
            'php',
            'javascript',
            'python',
            'java',
            'c++',
            'c#',
            'ruby',
            'go',
            'rust',
            'web',
            'frontend',
            'backend',
            'фреймворк',
            'laravel',
            'symfony',
            'react',
            'vue',
            'angular',
            'разработка',
            'программирование',
            'разработчик',
            'код',
            'api',
            'git'
        ],
        'database' => [
            'sql',
            'mysql',
            'postgresql',
            'sqlite',
            'mongodb',
            'redis',
            'база данных',
            'бд',
            'данные'
        ],
        'devops' => [
            'docker',
            'kubernetes',
            'ci/cd',
            'devops',
            'gitlab',
            'jenkins',
            'ansible',
            'terraform',
            'облако',
            'cloud',
            'aws',
            'azure',
            'контейнер',
            'deploy'
        ],
        'mobile' => [
            'ios',
            'android',
            'swift',
            'kotlin',
            'flutter',
            'react native',
            'мобильное',
            'мобильный'
        ],
        'gamedev' => [
            'игра',
            'game',
            'unity',
            'unreal',
            'геймдев',
            'игровая',
            'игровой'
        ],
        'security' => [
            'безопасность',
            'security',
            'хакер',
            'уязвимость',
            'вирус',
            'защита',
            'взлом',
            'ddos',
            'эксплойт',
            'утечка'
        ],
        'hardware' => [
            'процессор',
            'видеокарта',
            'ram',
            'ssd',
            'ноутбук',
            'компьютер',
            'железо',
            'gpu',
            'cpu',
            'intel',
            'amd',
            'nvidia',
            'монитор',
            'клавиатура',
            'мышь'
        ],
        'ai' => [
            'нейросеть',
            'искусственный интеллект',
            'ai',
            'machine learning',
            'ml',
            'chatgpt',
            'gpt',
            'llm',
            'нейронная сеть',
            'claude',
            'copilot',
            'ии'
        ],
        'science' => [
            'наука',
            'исследование',
            'учёный',
            'открытие',
            'космос',
            'физика',
            'биология',
            'технология',
            'квантовый'
        ],
        'business' => [
            'бизнес',
            'стартап',
            'инвестиции',
            'рынок',
            'компания',
            'startup',
            'акции',
            'финансы',
            'контракт'
        ],
    ];

    public function parseAll(): array
    {
        $results = [];
        foreach ($this->sources as $source) {
            $results[] = $this->parseSource($source);
        }
        return $results;
    }

    private function fetchRss(string $url): string|false
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/rss+xml, application/xml, text/xml, */*',
                'Accept-Language: ru-RU,ru;q=0.9,en;q=0.8',
            ]);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ch = null;

            if ($httpCode === 200 && !empty($content)) {
                return $content;
            }
        }

        return false;
    }

    private function parseSource(array $source): array
    {
        $result = ['source' => $source['name'], 'added' => 0, 'errors' => []];

        try {
            $content = $this->fetchRss($source['url']);
            if ($content === false) {
                $result['errors'][] = 'Не удалось загрузить RSS';
                return $result;
            }

            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            $xmlStart = strpos($content, '<?xml');
            if ($xmlStart !== false) {
                $content = substr($content, $xmlStart);
            }

            $xml = simplexml_load_string($content);

            if ($xml === false) {
                $result['errors'][] = 'Ошибка парсинга XML';
                return $result;
            }

            $items = $xml->channel->item;
            foreach ($items as $item) {
                $title = (string) $item->title;
                $description = (string) $item->description;

                $description = strip_tags($description);
                $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                $description = preg_replace('/\s+/', ' ', $description);
                $description = trim($description);

                $cutMarkers = ['Читать далее', 'Читать дальше', 'Источник', '—'];
                foreach ($cutMarkers as $marker) {
                    $pos = mb_strpos($description, $marker);
                    if ($pos !== false) {
                        $description = mb_substr($description, 0, $pos);
                        break;
                    }
                }

                $description = preg_replace('/<a[^>]*>.*?<\/a>/i', '', $description);
                $description = preg_replace('/\s+/', ' ', $description);
                $description = trim($description);
                $description = rtrim($description, '.;:—');
                $description = trim($description);

                if (mb_strlen($description) < 50) {
                    continue;
                }

                $excerpt = mb_substr($description, 0, 200);
                if (mb_strlen($description) > 200) {
                    $excerpt .= '...';
                }

                $link = (string) $item->link;

                if ($this->newsExists($title)) {
                    continue;
                }

                $categoryId = $this->detectCategory($title . ' ' . $description);

                $news = new News($categoryId, $title, $excerpt, $description, $link);

                if ($news->save()) {
                    $result['added']++;
                }
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    private function newsExists(string $title): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM news WHERE title = ?");
        $stmt->execute([$title]);
        return (bool) $stmt->fetch();
    }

    private function detectCategory(string $text): ?int
    {
        $db = Database::getConnection();
        $lowerText = mb_strtolower($text);

        foreach ($this->categoryMapping as $slug => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($lowerText, $keyword) !== false) {
                    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                    $stmt->execute([$slug]);
                    $category = $stmt->fetch();
                    if ($category) {
                        return $category['id'];
                    }
                }
            }
        }

        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = 'other'");
        $stmt->execute();
        $other = $stmt->fetch();

        return $other ? $other['id'] : null;
    }
}
