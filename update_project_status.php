<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update all projects without status to 'approved'
$updated = DB::table('projects')
    ->whereNull('status')
    ->update(['status' => 'approved']);

echo "Updated {$updated} projects to status 'approved'\n";

// Show all projects
$projects = DB::table('projects')->select('id', 'project_name', 'status')->get();
echo "\nCurrent projects:\n";
foreach ($projects as $project) {
    echo "ID: {$project->id} - {$project->project_name} - Status: {$project->status}\n";
}
