<?php

namespace Tests\Feature\Loaders;

use PDO;
use PDOException;
use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\DatabaseLoader;
use Torol\Pipeline;

it('can load data into a database table with batching', function () {
    try {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $this->markTestSkipped('The pdo_sqlite extension is not enabled.');
    }

    // Create the destination table.
    $pdo->exec("CREATE TABLE new_users (id INTEGER, name TEXT, email TEXT)");

    // The data we want to load.
    $sourceData = [
        ['id' => 101, 'name' => 'Aragorn', 'email' => 'aragorn@gondor.gov'],
        ['id' => 102, 'name' => 'Legolas', 'email' => 'legolas@mirkwood.net'],
        ['id' => 103, 'name' => 'Gimli', 'email' => 'gimli@erebor.mine'],
    ];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->load(new DatabaseLoader($pdo, 'new_users', batchSize: 2));

    $statement = $pdo->query("SELECT COUNT(*) FROM new_users");
    $this->assertEquals(3, $statement->fetchColumn());

    $statement = $pdo->query("SELECT * FROM new_users WHERE id = 102");
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    $this->assertEquals('Legolas', $user['name']);
});
