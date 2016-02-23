<?php
namespace RestApi;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Router
 */
class Router
{
    public function install(\Slim\App $app) {

        $mongoWrapper = new MongoDbWrapper();

        $app->get('/api/{entity}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($mongoWrapper) {
            $entity = $args['entity'];
            $body = $response->getBody();

            try {
                $documents = $mongoWrapper->findAll('db.' . $entity);

                $body->write(json_encode($documents));

                return $response->withHeader('Content-Type', 'application/json');
            }
            catch(\Exception $e) {
                $body->write(json_encode(array('message' => $e->getMessage())));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }
        });

        $app->get('/api/{entity}/{id}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($mongoWrapper) {
            $entity = $args['entity'];
            $id = $args['id'];

            $body = $response->getBody();

            try {
                $document = $mongoWrapper->findOne('db.' . $entity, array('_id' => new \MongoDB\BSON\ObjectID($id)));

                if(count($document) > 0) {
                    $document = $document[0];
                    $document->_id = $document->_id->__toString();
                    $body->write(json_encode($document));
                }

                return $response->withHeader('Content-Type', 'application/json');
            }
            catch(\Exception $e) {
                $body->write(json_encode(array('message' => $e->getMessage())));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }

        });

        $app->post('/api/{entity}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($mongoWrapper) {
            $entity = $args['entity'];
            $document = $request->getParsedBody();
            $body = $response->getBody();

            try {
                $result = $mongoWrapper->insert('db.' . $entity, $document);

                $body->write(json_encode(array('id' => $result->__toString())));
                return $response->withHeader('Content-Type', 'application/json');
            }
            catch(\Exception $e) {
                $body->write(json_encode(array("message" => $e->getMessage())));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }

        });

        $app->put('/api/{entity}/{id}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($mongoWrapper) {
            $entity = $args['entity'];
            $id = $args['id'];

            $document = $request->getParsedBody();
            $body = $response->getBody();

            if(array_key_exists('_id', $document)) {
                unset($document['_id']);
            }

            try {
                $mongoWrapper->update('db.' . $entity, $id, $document);
                $body->write(json_encode($document));
                return $response->withHeader('Content-Type', 'application/json');
            }
            catch(\Exception $e) {
                var_dump($e);
                $body->write(json_encode(array('message' => $e->getMessage())));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }
        });

        $app->delete('/api/{entity}/{id}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($mongoWrapper) {
            $entity = $args['entity'];
            $id = $args['id'];

            $body = $response->getBody();

            try {
                $mongoWrapper->delete('db.' . $entity, $id);

                return $response->withHeader('Content-Type', 'application/json');
            }
            catch(\Exception $e) {
                $body->write(json_encode(array('message' => $e->getMessage())));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }
        });
    }
}