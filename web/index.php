<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\DoctrineServiceProvider;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new DoctrineServiceProvider(), array(
    "db.options" => [
        "driver" => "pdo_mysql",
        "user" => "bookmarks",
        "password" => "123",
        "dbname" => "bookmarks",
        "host" => "localhost",
    ]
));

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});

$app->error(function(Exception $e, Request $request, $code) use ($app) {
    return $app->json(['error' => $e->getMessage()], $code);
});

$app->get('/bookmarks', function (Request $request, $limit) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $limit = (int)$request->get('limit');

    $sql = "SELECT * FROM bookmarks ORDER BY id DESC";

    if (!empty($limit)) {
        $sql .= " LIMIT $limit";
    }

    $bookmarks = $db->fetchAll($sql);

    return $app->json($bookmarks);
});

$app->get('/bookmarks/url', function (Request $request) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $url = $request->get('url');
    if (!$url) {
        $app->abort(400, "Missing required parameter 'url'");
    }

    $bookmark = $db->fetchAssoc('SELECT * FROM bookmarks WHERE url = ?', [$url]);
    if (!$bookmark) {
        $app->abort(404, "Bookmark not found");
    }

    $comments = $db->fetchAll('SELECT id, text, ip, created_at  FROM comments WHERE bookmark_id = ? ORDER BY id DESC', [$bookmark['id']]);

    $bookmark['comments'] = $comments;

    return $app->json($bookmark);
});


$app->post('/bookmarks', function (Request $request) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $url = $request->get('url');
    if (!$url) {
        $app->abort(400, "Missing required parameter 'url'");
    }
    if (!is_string($url)) {
        $app->abort(400, "Parameter 'url' must be string");
    }

    $bookmark = $db->fetchAssoc('SELECT * FROM bookmarks WHERE url = ?', [$url]);
    if ($bookmark) {
        return $app->json($bookmark);
    }

    $bookmark = array(
        'url' => $request->request->get('url'),
        'created_at'  => date('Y-m-d H:i:s'),
    );

    $db->insert('bookmarks', $bookmark);
    $bookmarkId = $db->lastInsertId();

    $bookmark = ['id' => $bookmarkId] + $bookmark;

    return $app->json($bookmark, 201);
});

$app->post('/comments', function (Request $request) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $bookmarkId = $request->request->get('bookmark_id');
    if (!$bookmarkId) {
        $app->abort(400, "Missing required parameter 'bookmark_id'");
    }
    if (!is_int($bookmarkId)) {
        $app->abort(400, "Parameter 'bookmark_id' must be integer");
    }

    $text = $request->request->get('text');
    if (!$text) {
        $app->abort(400, "Missing required parameter 'text'");
    }
    if (!is_string($text)) {
        $app->abort(400, "Parameter 'text' must be string");
    }

    $bookmark = $db->fetchAssoc('SELECT * FROM bookmarks WHERE id = ?', [$bookmarkId]);
    if (!$bookmark) {
        $app->abort(400, "Bookmark #$bookmarkId not found");
    }

    $ip = $request->getClientIp();

    $comment = array(
        'bookmark_id' => $bookmarkId,
        'text' => $text,
        'ip' => $ip,
        'created_at'  => date('Y-m-d H:i:s'),
    );

    $db->insert('comments', $comment);
    $commentId = $db->lastInsertId();

    unset($comment['bookmark_id']);
    $comment = ['id' => $commentId] + $comment;

    return $app->json($comment, 201);
});

$app->patch('/comments/{id}', function (Request $request, $id) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $comment = $db->fetchAssoc('SELECT * FROM comments WHERE id = ?', [$id]);
    if (!$comment) {
        $app->abort(404, "Comment #$id not found");
    }

    $text = $request->request->get('text');
    if (!$text) {
        $app->abort(400, "Missing required parameter 'text'");
    }
    if (!is_string($text)) {
        $app->abort(400, "Parameter 'text' must be string");
    }

    $ip = $request->getClientIp();
    if ($comment['ip'] != $ip || strtotime($comment['created_at']) + 3600 < time()) {
        $app->abort(403, 'You do not have permissions');
    }

    $db->update('comments', ['text' => $text], ['id' => $id]);
    $comment['text'] = $text;

    return $app->json('', 204);

})
->assert('id', '\d+');

$app->delete('/comments/{id}', function (Request $request, $id) use ($app) {

    /** @var Doctrine\DBAL\Connection $db */
    $db = $app['db'];

    $comment = $db->fetchAssoc('SELECT * FROM comments WHERE id = ?', [$id]);
    if (!$comment) {
        $app->abort(404, "Comment #$id not found");
    }

    $ip = $request->getClientIp();
    if ($comment['ip'] != $ip || strtotime($comment['created_at']) + 3600 < time()) {
        $app->abort(403, 'You do not have permissions');
    }

    $db->delete('comments', ['id' => $id]);

    return $app->json('', 204);

})->assert('id', '\d+');

$app->run();