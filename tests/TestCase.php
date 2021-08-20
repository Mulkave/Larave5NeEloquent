<?php

namespace Tests;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\Stub;
use Vinelab\NeoEloquent\Connection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        Stub::setConnectionResolver($resolver);
        $this->flushDb();
    }

    public function tearDown()
    {
        // everything should be clean before every test
        $this->flushDb();

        parent::tearDown();
    }

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Asia/Beirut');
    }

    /**
     * Get the connection with a given or the default configuration.
     *
     * @param string $config As specified in config/database.php
     *
     * @return \Vinelab\NeoEloquent\Connection
     */
    protected function getConnectionWithConfig($config = null)
    {
        return new Connection(config('database.connections.neo4j'));
        $connection = is_null($config) ? $this->dbConfig['connections']['neo4j'] :
            $this->dbConfig['connections'][$config];

        return new Connection($connection);
    }

    /**
     * Flush all database records.
     */
    protected function flushDb()
    {
        $client = $this->getClient()->writeSession();

        $flushQuery = 'MATCH (n) DETACH DELETE n';

        $client->run($flushQuery);
    }

    protected function getClient()
    {
        $connection = (new Stub())->getConnection();

        return $connection->getClient();
    }

    /**
     * get the node by the given id.
     *
     * @param int $id
     *
     * @return \Neoxygen\NeoClient\Formatter\Node
     */
    protected function getNodeById($id)
    {
        //get the labels using NeoClient
        $connection = $this->getConnectionWithConfig('neo4j');
        $client = $connection->getClient();
        $result = $client->session()->run("MATCH (n) WHERE id(n)=$id RETURN n");

        return $result->firstRecord()->valueByIndex(0);
    }

    /**
     * Get node labels of a node by the given id.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getNodeLabels($id)
    {
        return $this->getNodeById($id)->labels();
    }

}
