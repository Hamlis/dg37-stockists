<?php
/**
 *
 * @author  Nerijus Eimanavičius <nerijus@eimanavicius.lt>
 */

use Phlyty\App;
use Phlyty\AppEvent;
use Zend\Http\PhpEnvironment\Request;

include 'vendor/autoload.php';

$stockistsRepository = new \DG37\Repository\InMemoryStockistRepository();

$app = new App();

$secret = 'X@1IxrxJSI!Q^FX!&l9qTo!#0ui*@wgD';

$app->events()->attach(
    'begin',
    function () use ($stockistsRepository) {
        $csv = fopen('stockist list.csv', 'r');
        fgetcsv($csv, null, "\t");
        $index = 0;
        while ($data = fgetcsv($csv, null, "\t")) {
            ++$index;
            array_walk(
                $data,
                function (&$value) {
                    $value = iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
                }
            );
            $stockist = new \DG37\Entity\Stockist($data[0]);
            $stockist->setAddress1($data[1]);
            $stockist->setAddress2($data[2]);
            $stockist->setCity($data[3]);
            $stockist->setState($data[4]);
            $stockist->setPostcode($data[5]);
            $stockist->setPhone($data[6]);
            $stockist->setUrl($data[7]);
            $stockist->setImage($data[8]);
            $stockistsRepository->save($stockist);
        }
        fclose($csv);
    }
);

$app->events()->attach(
    'route',
    function (AppEvent $event) {
        ini_set('display_errors', 1);
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
        include 'leftSidebar.phtml';
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
    '/api/stockists.json',
    function (App $app) use ($stockistsRepository) {
        $app->response()->getHeaders()->addHeader(new \Zend\Http\Header\ContentType('application/json'));
        $app->response()->setContent(
            json_encode(['Stockists' => $stockistsRepository->findAll()])
        );
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

$app->run();
