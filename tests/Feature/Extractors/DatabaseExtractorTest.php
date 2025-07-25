<?php

namespace Tests\Feature\Extractors;

use PDO;
use PDOException;
use Torol\Loaders\CallbackLoader;
use Torol\Extractors\DatabaseExtractor;
use Torol\Pipeline;
use Torol\Row;

it('can extract data from a database using a pdo connection', function () {
    try {
        $pdo = new PDO('sqlite::memory:');
    } catch (PDOException $e) {
        $this->markTestSkipped('The pdo_sqlite extension is not enabled.');
    }

    // Create a table and insert some data.
    $pdo->exec("CREATE TABLE users (id INTEGER, name TEXT, email TEXT)");
    $pdo->exec("INSERT INTO users (id, name, email) VALUES (1, 'Gandalf', 'gandalf@middle.earth')");
    $pdo->exec("INSERT INTO users (id, name, email) VALUES (2, 'Frodo', 'frodo@middle.earth')");

    $results = [];

    $query = "SELECT id, name, email FROM users ORDER BY id ASC";
    Pipeline::from(new DatabaseExtractor($pdo, $query))->map(fn (Row $row) => $row->set('name', strtoupper($row->get('name'))))->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
    }));

    $this->assertCount(2, $results);
    $this->assertEquals('GANDALF', $results[0]['name']);
    $this->assertEquals('frodo@middle.earth', $results[1]['email']);
});
