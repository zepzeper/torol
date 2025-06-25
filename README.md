Torol - A Fluent ETL Library for PHPTorol is a modern, fluent, and highly scalable ETL (Extract, Transform, Load) library for PHP. It's designed to make complex data transformation pipelines readable, testable, and memory-efficient.Inspired by Laravel Collections, Torol treats data processing as a chain of expressive, readable steps, allowing you to handle large datasets from files and databases with a minimal memory footprint, thanks to its use of PHP Generators.FeaturesFluent & Expressive API: Write data pipelines that read like a sentence.Scalable by Default: Process large files (CSVs, JSON) and database tables with very low memory usage.Pluggable Architecture: Easily extend the library with custom Extractors and Loaders.Rich Transformation Suite: A wide array of methods for filtering, mapping, cleaning, and restructuring data.Robust Error Handling: Gracefully handle bad rows without crashing your entire import process.Quick Start ExampleImagine you need to process a users.csv file, filter for only the active users, standardize their email addresses to lowercase, and then load them into a subscribers database table.With Torol, it's this simple:<?php

require 'vendor/autoload.php';

use Zepzeper\Torol\Pipeline;
use Zepzeper\Torol\Extractors\CsvExtractor;
use Zepzeper\Torol\Loaders\DatabaseLoader;
use Zepzeper\Torol\Row;

// Your PDO connection
$pdo = new PDO('sqlite:database.sqlite');

$stats = Pipeline::from(new CsvExtractor('path/to/users.csv'))
    // Keep only rows where the 'status' column is 'active'
    ->filter(fn(Row $row) => $row->get('status') === 'active')

    // Standardize the email address
    ->map(fn(Row $row) => $row->set('email', strtolower($row->get('email'))))

    // Rename a column to match our database schema
    ->renameColumn('first_name', 'name')

    // Keep only the columns we need
    ->select(['id', 'name', 'email'])
    
    // Load the final data into the 'subscribers' table
    ->load(new DatabaseLoader($pdo, 'subscribers'));

echo "Successfully loaded {$stats->rowsLoaded} records in {$stats->durationInSeconds} seconds.";

InstallationYou can install the library via Composer:composer require zepzeper/torol
(Note: This package name is a placeholder until you publish it on Packagist.)Documentation(This is where you'll add links to the more detailed documentation you'll write in the /docs directory.)Getting StartedExtractorsArrayExtractorCsvExtractorJsonExtractorDatabaseExtractorTransformersmap, filter, validate, tapaddColumn, removeColumn, renameColumn, selectcast, unique, when, nestsort, mergegroupBy, reduceLoadersCallbackLoaderCsvLoaderJsonLoaderDatabaseLoaderError HandlingStatisticsTestingThis library maintains a high standard of code quality and is fully tested. To run the test suite:./vendor/bin/pest
LicenseThe Torol library is open-sourced software licensed under the MIT license.
