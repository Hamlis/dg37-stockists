<?php
/**
 *
 * @author  Nerijus Eimanavičius <nerijus@eimanavicius.lt>
 */

use Phlyty\App;
use Phlyty\AppEvent;
use Zend\Http\PhpEnvironment\Request;

include 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new PDO('sqlite:data/database.sq3');

//if (!file_exists('data/database.sq3')) {
    $database->exec(
        "
        CREATE TABLE IF NOT EXISTS stockists
        (
          name CONSTRAINT uniqueName PRIMARY KEY ASC,
          address1,
          address2,
          city,
          state,
          postcode,
          phone,
          url,
          image,
          latitude,
          longitude
        )
        "
    );
//}

$stockistsRepository = new \DG37\Repository\StockistRepository($database);

$doImport = function () use ($stockistsRepository) {
    $csv = fopen('data/stockists.csv', 'r');
    fgetcsv($csv, null, "\t");
    $stockistsNames = [];
    while ($data = fgetcsv($csv, null, "\t")) {
        if (!isset($data[0])) {
            continue;
        }
        array_walk(
            $data,
            function (&$value) {
                $value = iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
            }
        );
        $stockist = new \DG37\Entity\Stockist($data[0]);
        $stockist->setAddress1(isset($data[1]) ? $data[1] : '');
        $stockist->setAddress2(isset($data[2]) ? $data[2] : '');
        $stockist->setCity(isset($data[3]) ? $data[3] : '');
        $stockist->setState(isset($data[4]) ? $data[4] : '');
        $stockist->setPostcode(isset($data[5]) ? $data[5] : '');
        $stockist->setPhone(isset($data[6]) ? $data[6] : '');
        $stockist->setUrl(isset($data[7]) ? $data[7] : '');
        $stockist->setImage(isset($data[8]) ? $data[8] : '');
        $stockistsRepository->save($stockist);
        $stockistsNames[] = $stockist->getName();
    }
    fclose($csv);
    $stockistsRepository->removeOthers($stockistsNames);
};

$app = new App();

$secret = 'X@1IxrxJSI!Q^FX!&l9qTo!#0ui*@wgD';

$app->events()->attach(
    'begin',
    function (AppEvent $event) {
        /** @var Request $request */
        $request = $event->getTarget()->request();
        $httpReferer = $request->getServer('HTTP_REFERER');
        $ref = null;

        foreach (['http://demolt.ashop.me', 'http://dg37.com.au', 'http://www.dg37.com.au'] as $site) {
            if (strpos($httpReferer, $site) === 0) {
                $ref = $site;
                break;
            }
        }

        if (null !== $ref) {
            /** @var \Zend\Http\PhpEnvironment\Response $response */
            $response = $event->getTarget()->response();
            $response->getHeaders()->addHeaderLine("Access-Control-Allow-Origin: " . $ref);
        }
    }
);

$app->events()->attach(
    'route',
    function (AppEvent $event) {
        /** @var Request $request */
        $request = $event->getParam('request');
        if ($request->isPost() &&
            ($header = $request->getHeader('Content-Type')) &&
            0 === strpos($header->getFieldValue(), 'application/json')
        ) {
            $request->setPost(new \Zend\Stdlib\Parameters(json_decode($request->getContent(), true)));
        }
    }
);

$app->events()->attach(
    'route',
    function (AppEvent $event) use ($secret) {
        if ($event->propagationIsStopped()) {
            return;
        }
        /** @var App $app */
        $app = $event->getTarget();
        $request = $app->request();
        if (strpos($request->getRequestUri(), '/api') === 0) {
            $authorization = '';
            if ($request->getHeaders()->has('Authorization')) {
                $authorization = $request->getHeader('Authorization')->getFieldValue();
            }
            if (strpos($authorization, 'Bearer ') === 0) {
                $jwt = substr($authorization, 7);
                try {
                    $decoded = JWT::decode($jwt, $secret);
                    if (isset($decoded->username) && isset($decoded->expire)) {
                        if ($decoded->expire > time()) {
                            return;
                        }
                    }
                } catch (UnexpectedValueException $exception) {

                } catch (DomainException $exception) {

                }
            }
            $app->halt(403);
        }
    }
);

$app->get(
    '/',
    function () {
        ob_start();
        include 'leftSidebar-old.phtml';
        $leftSidebar = ob_get_clean();

        ob_start();
        include 'stockists.phtml';
        $content = ob_get_clean();

        include 'layout.phtml';
    }
);
$app->post(
    '/login.json',
    function (App $app) use ($secret) {
        $users = [
            'nerijus@eimanavicius.lt' => '',
            'info@dg37.com.au' => ''
        ];
        $username = $app->request()->getPost('username');
        $password = $app->request()->getPost('password');
        if (!isset($users[$username]) || $users[$username] !== $password) {
            $app->halt(401);
        }
        $user = [
            'username' => $username,
            'role' => [
                'bitMask' => 2,
                'title' => "user"
            ],
            'expire' => time() + 60 * 60 * 24 // one day
        ];
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(json_encode([
                    'token' => JWT::encode($user, $secret)
                ] + $user));
    }
);
$app->get(
    '/stockists.json',
    function (App $app) use ($stockistsRepository) {
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(
            json_encode(['Stockists' => array_map(function (\DG37\Entity\Stockist $stockist) {
                return [
                    'title' => $stockist->getName(),
                    'address' => implode("\n", array_filter([
                        $stockist->getAddress1(),
                        $stockist->getAddress2(),
                        implode(', ', array_filter([
                            $stockist->getCity(),
                            implode(' ', array_filter([$stockist->getState(), $stockist->getPostcode()]))
                        ]))
                    ])),
                    'phone' => $stockist->getPhone(),
                    'url' => $stockist->getUrl(),
                    'image' => $stockist->getImage()
                ];
            }, $stockistsRepository->findAll())])
        );
    }
);
$app->get(
    '/api/stockists.json',
    function (App $app) use ($stockistsRepository) {
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(
            json_encode(['Stockists' => $stockistsRepository->findAll()])
        );
    }
);
$app->post(
    '/api/stockists.csv',
    function (App $app) use ($doImport) {
        file_put_contents('data/stockists.csv', $app->request()->getContent());
        copy('data/stockists.csv', 'data/history/' . microtime(true) . '.csv');
        $doImport();
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setStatusCode(201);
    }
);
$app->get(
    '/api/stockists/:name[].json',
    function (App $app) use ($stockistsRepository) {
        $name = $app->params()->getParam('name');
        $stockist = $stockistsRepository->findOneByName($name);
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(
            json_encode(['Stockist' => $stockist])
        );
    }
);
$app->post(
    '/api/stockists/:name[].json',
    function (App $app) use ($stockistsRepository) {
        $name = $app->params()->getParam('name');
        $stockist = $stockistsRepository->findOneByName($name);
        foreach ($app->request()->getPost() as $key => $value) {
            $setMethod = 'set' . ucfirst($key);
            if (method_exists($stockist, $setMethod)) {
                $stockist->{$setMethod}($value);
            }
        }
        $stockistsRepository->save($stockist);
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(
            json_encode(['Stockist' => $stockist])
        );
    }
);

$app->run();
