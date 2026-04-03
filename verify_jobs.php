<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$db = $app->make('db');

$count = $db->select('SELECT COUNT(*) as count FROM jobs')[0]->count;
echo "Total jobs in database: " . $count . "\n\n";

$samples = $db->select('SELECT title, company_name, location, salary, status FROM jobs ORDER BY id DESC LIMIT 5');
echo "Sample of 5 latest jobs:\n";
foreach ($samples as $job) {
    echo "- " . $job->title . " at " . $job->company_name . " (" . $job->location . ") - " . $job->salary . " - " . $job->status . "\n";
}

// Show job status distribution
echo "\n\nJob Status Distribution:\n";
$statuses = $db->select('SELECT status, COUNT(*) as count FROM jobs GROUP BY status');
foreach ($statuses as $row) {
    echo "- " . $row->status . ": " . $row->count . " jobs\n";
}
