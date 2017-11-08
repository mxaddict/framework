<?php

namespace Illuminate\Database\Connectors;

use InvalidArgumentException;

class SQLiteConnector extends Connector implements ConnectorInterface
{
    /**
     * @var PDO memory_connection
     */
    protected static $memory_connection;

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     *
     * @throws \InvalidArgumentException
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        // SQLite supports "in-memory" databases that only last as long as the owning
        // connection does. These are useful for tests or for short lifetime store
        // querying. In-memory databases may only have a single open connection.
        if ($config['database'] == ':memory:' || $config['database'] == ':memory:single:') {
            // check if we want to have 1 database for all sqlite connections
            if ($config['database'] == ':memory:single:') {
                // check if we already have a memory connection
                if (!self::$memory_connection) {
                    // create the connection
                    self::$memory_connection = $this->createConnection('sqlite::memory:', $config, $options);
                }

                // give back the connection
                return self::$memory_connection;
            }

            // create the new connection and give it back
            return $this->createConnection('sqlite::memory:', $config, $options);
        }

        $path = realpath($config['database']);

        // Here we'll verify that the SQLite database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // SQLite driver will not throw any exception if it does not by default.
        if ($path === false) {
            throw new InvalidArgumentException("Database (${config['database']}) does not exist.");
        }

        return $this->createConnection("sqlite:{$path}", $config, $options);
    }
}
