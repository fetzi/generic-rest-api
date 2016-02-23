<?php
namespace RestApi;

use \MongoDB\Driver;

/**
 * Class MongoDbWrapper
 */
class MongoDbWrapper
{
    private $manager;
    private $writeConcern;
    private $readPreference;

    public function __construct($uri = "mongodb://localhost:27017")
    {
        $this->manager = new \MongoDB\Driver\Manager($uri);

        $this->writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 300);
        $this->readPreference = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_SECONDARY_PREFERRED);
    }

    public function findAll($collection) {
        $query = new \MongoDB\Driver\Query(array());
        $cursor = $this->manager->executeQuery($collection, $query, $this->readPreference);

        $documents = array();
        foreach($cursor as $document) {
            $document->_id = $document->_id->__toString();
            $documents[] = $document;
        }

        return $documents;
    }

    public function findOne($collection, $query) {
        $query = new \MongoDB\Driver\Query($query);
        $cursor = $this->manager->executeQuery($collection, $query, $this->readPreference);

        return $cursor->toArray();
    }

    public function insert($collection, $document) {
        $bulk = new Driver\BulkWrite();
        $id = $bulk->insert($document);

        $this->executeWrite($collection, $bulk);

        return $id;
    }

    public function update($collection, $id, $document) {
        $filter = array('_id' => new \MongoDB\BSON\ObjectID($id));

        $newObject = array(
            '$set' => $document
        );

        $options = array('multi' => false, 'upsert' => false);

        $bulk = new Driver\BulkWrite();
        $bulk->update($filter, $newObject, $options);

        $this->executeWrite($collection, $bulk);
    }

    public function delete($collection, $id) {
        $filter = array('_id' => new \MongoDB\BSON\ObjectID($id));
        $options = array('limit' => 1);

        $bulk = new Driver\BulkWrite();
        $bulk->delete($filter, $options);

        $this->executeWrite($collection, $bulk);
    }

    private function executeWrite($collection, $bulk) {
        return $this->manager->executeBulkWrite($collection, $bulk, $this->writeConcern);
    }
}