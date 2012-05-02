<?php
declare(strict_types=1);

use CampusBoard\Auth;
use CampusBoard\Csrf;
use CampusBoard\Db;
use CampusBoard\ListingRepository;
use CampusBoard\Validator;
use CampusBoard\View;

require __DIR__ . '/../vendor/autoload.php';

session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$db = Db::fromEnv();
$listings = new ListingRepository($db);
$auth = new Auth($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';

function redirect(string $to): never
{
    header("Location: $to", true, 303);
    exit;
}

function requireUser(): array
{
    $user = Auth::user();
    if ($user === null) {
        redirect('/login?next=' . urlencode($_SERVER['REQUEST_URI']));
    }
    return $user;
}

if ($method === 'POST' && !Csrf::check($_POST['_csrf'] ?? null)) {
    http_response_code(419);
    exit('Session expired. Go back and try again.');
}

switch (true) {
    case $path === '/' && $method === 'GET':
        $result = $listings->search($_GET['q'] ?? null, $_GET['category'] ?? null, (int)($_GET['page'] ?? 1));
        echo View::render('listings', ['result' => $result, 'categories' => $listings->categories(),
            'q' => $_GET['q'] ?? '', 'category' => $_GET['category'] ?? '']);
        break;

    // JSON endpoint used by the jQuery live search.
    case $path === '/api/listings' && $method === 'GET':
        echo View::json($listings->search($_GET['q'] ?? null, $_GET['category'] ?? null, (int)($_GET['page'] ?? 1)));
        break;

    case preg_match('#^/listings/(\d+)$#', $path, $m) === 1 && $method === 'GET':
        $listing = $listings->find((int)$m[1]);
        if ($listing === null || $listing['status'] === 'removed') {
            http_response_code(404);
            echo View::render('not_found');
            break;
        }
        echo View::render('listing', ['listing' => $listing, 'user' => Auth::user()]);
        break;

    case $path === '/listings/new':
        $user = requireUser();
        $data = ['title' => '', 'body' => '', 'category_id' => 0, 'price' => ''];
        $errors = [];
        if ($method === 'POST') {
            [$clean, $errors] = Validator::listing($_POST, $listings->categoryIds());
            if (!$errors) {
                redirect('/listings/' . $listings->create($user['id'], $clean));
            }
            $data = $_POST;
        }
        echo View::render('form', ['data' => $data, 'errors' => $errors, 'categories' => $listings->categories()]);
        break;

    case preg_match('#^/listings/(\d+)/status$#', $path, $m) === 1 && $method === 'POST':
        $user = requireUser();
        $listings->setStatus((int)$m[1], $user['id'], (string)($_POST['status'] ?? ''));
        redirect('/me');

    case $path === '/me':
        $user = requireUser();
        echo View::render('mine', ['items' => $listings->byUser($user['id'])]);
        break;

    case $path === '/register':
        $data = ['email' => '', 'display_name' => ''];
        $errors = [];
        if ($method === 'POST') {
            [$data, $errors] = Validator::registration($_POST);
            if (!$errors) {
                $result = $auth->register($data['email'], $data['display_name'], $data['password']);
                if (is_int($result)) {
                    Auth::login(['id' => $result, 'display_name' => $data['display_name']]);
                    redirect('/');
                }
                $errors['email'] = $result;
            }
        }
        echo View::render('register', ['data' => $data, 'errors' => $errors]);
        break;

    case $path === '/login':
        $error = null;
        if ($method === 'POST') {
            $user = $auth->attempt((string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''));
            if ($user) {
                Auth::login($user);
                $next = (string)($_GET['next'] ?? '/');
                redirect(str_starts_with($next, '/') && !str_starts_with($next, '//') ? $next : '/');
            }
            $error = 'Email or password is incorrect.';
        }
        echo View::render('login', ['error' => $error]);
        break;

    case $path === '/logout' && $method === 'POST':
        Auth::logout();
        redirect('/');

    default:
        http_response_code(404);
        echo View::render('not_found');
}
