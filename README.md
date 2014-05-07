# Comma Framework. More simple. Examples

## Main example
```php
<?php
$app = new \Comma\Core();
$app['view.config']['path'] = '/path/to/templates';

$app->route('/', function () use ($app) {
    return $app->view('main.php');
});

$app->route('/news', 'News::getList')
    ->inject('app', $app);

$app->route('/news/{id}', 'News::getItem')
    ->inject('app', $app);

$app->route('/news/{year}', 'News::getListByYear')
    ->inject('app', $app)
    ->assert('year', '\d{2}-\d{2}-\d{4}');

try {
    $app->run($app['request']->server('REQUEST_URI', '/'));
} catch (\Comma\Exception\PageNotFound $ex) {
    header('Location: /404');
}
```


## Pimple container
```php
<?php
$app = new \Comma\Core();

$app['new_service'] = $app->factory(function(){
    return true;
});
```

For more examples see [Pimple](https://github.com/fabpot/Pimple)


## Routing
```php
<?php
$app = new \Comma\Core();

// main page route
$app->route('/', function () {
    return 'main page';
});

// news
$app->route('^/news$', function () use ($app) {
    return $app->response()->html('<h1>News here</h1>');
});
// or this
$app
    ->route('^/news$', 'NewsController::indexAction') // where NewsController::indexAction($app)
    ->inject('comma', $app);

// news by year
$app->route('^/news/{year}$', function ($year) use ($app) {
    $tpl = $app->template('path/to/template.php');
    $tpl->assign('year', $year);
    return $app->response()->view($tpl);
})->assert('year', '\d{4}');

// one article
$app->route('^/news/(\d{4})/(\d+)', function ($year, $id, $tail = null) use ($app) {
    return $app
        ->template('path/to/template.php')
        ->assign('year', $year)
        ->assign('id', $id);
});

try {
    $app->run($app['request']->server('REQUEST_URI', '/'));
} catch (\Comma\Exception\PageNotFound $ex) {
    header('Location: /404');
}
```

## Native template engine

```php
<?php
$app = new \Comma\Core();
// You can set base template path
$app['view.config']['path'] = '/base/path';

$tpl = $app->view('path/to/template.php');

$tpl
    ->assign('first', 'one')
    ->assign('second', 'two')
    ->render();
```


```php
<?php
/**
 * File: path/to/template.php
 * @var string $first
 * @var string $second
 * @var int $third
 */
$first = isset($first) ? $first : 1;    // will contain 'one'
$second = isset($second) ? $second : 2; // will contain 'two'
$third = isset($third) ? $third : 3;    // will contain '3'
```


## Request helper
```php
<?php
// Getting $_GET vars
$app['request']->vars('GET'); // return array()

// Getting var $_GET['name']
$app['request']->get('name'); // or use $app['request']->__call('GET', 'name') if needed

// Getting var $_GET['name'] or default value
$app['request']->get('name', 'default_value');

// Getting var $_POST['surname']
$app['request']->post('name'); // or use $app['request']->__call('POST', 'name') if needed

// Getting $_FILES
$app['request']->files(); // return array()

// Getting list of files
$app['request']->file('file');
// or getting file with index 0
$app['request']->file('file', 0);

// Getting $_SERVER var
$app['request']->server('SERVER_NAME');

// Getting $_SERVER var or default value
$app['request']->server('SERVER_NAME', 'default_value');
```

## Response helper
```php
<?php
// Set custom charset (utf-8 by default)
$charset = 'windows-1251';
$response = $app->response($charset);


// Return plain text
$response = $app->response();
echo $response->text('Hi, people!');


// Return html
$response = $app->response();
echo $response->html('<html><body>Hi, people!</body></html>');


// Return json
$response = $app->response();
echo $response->json(array('hello' => 'world'));


// Return xml
$response = $app->response();
echo $response->xml(array(
    'root' => array(
        'simple' => 'tag',
        'user' => array(
            '@attrs' => array('age' => 30, 'gender' => 'male'),
            '@value' => 'Darien Fawkes'
        ),
        'about' => array(
            '@cdata' => 'Text with special chars.'
        )
    )
));


// Manual send headers and render content
$response = $app->response();
$response->text('hi, man!');
$response->send(); // header('Content-Type: text/plain; charset=utf-8')
echo $response->content();


// Redirect 302
$response = $app->response();
$response->redirect('http://some.site');

// Redirect 301
$response = $app->response();
$response->redirect('http://some.site', 301);


// Manual set headers
$response = $app->response();
$response->header('Content-Type: text/plain; charset=utf-8');


// Priority of headers:
header('SomeHeader1: true');
header('SomeHeader2: true');
$response = $app->response();
$response->header('SomeHeader1: false');
$response->header('SomeHeader4: true');
header('SomeHeader3: true');
header('SomeHeader4: false');

$response->send();
// will send:
// SomeHeader1: false
// SomeHeader2: true
// SomeHeader3: true
// SomeHeader4: true
```