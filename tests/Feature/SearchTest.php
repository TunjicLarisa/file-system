<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('finds a file by exact name in current folder', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => null,
    ])->json('data');

    $this->postJson('/api/files', [
        'name' => 'report.docx',
        'folder_id' => $folder['id'],
    ]);

    $this->postJson('/api/files', [
        'name' => 'requirements.txt',
        'folder_id' => $folder['id'],
    ]);

    $response = $this->getJson(
        "/api/search/exact?q=report.docx&all=0&folder_id={$folder['id']}"
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'report.docx');
});

test('returns files that start with search string', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => null,
    ])->json('data');

    $this->postJson('/api/files', [
        'name' => 'report.docx',
        'folder_id' => $folder['id'],
    ]);

    $this->postJson('/api/files', [
        'name' => 'requirements.txt',
        'folder_id' => $folder['id'],
    ]);

    $this->postJson('/api/files', [
        'name' => 'contract.pdf',
        'folder_id' => $folder['id'],
    ]);

    $response = $this->getJson(
        "/api/search/suggestions?q=re&all=0&folder_id={$folder['id']}"
    );

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['name' => 'report.docx'])
        ->assertJsonFragment(['name' => 'requirements.txt']);
});

test('returns maximum ten suggestions', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    for ($i = 1; $i <= 15; $i++) {
        $this->postJson('/api/files', [
            'name' => "report-{$i}.txt",
            'folder_id' => $folder['id'],
        ]);
    }

    $response = $this->getJson(
        "/api/search/suggestions?q=report&all=0&folder_id={$folder['id']}"
    );

    $response
        ->assertOk()
        ->assertJsonCount(10, 'data');
});

test('search in current folder does not return files from other folders', function () {
    $work = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => null,
    ])->json('data');

    $personal = $this->postJson('/api/folders', [
        'name' => 'Personal',
        'parent_id' => null,
    ])->json('data');

    $this->postJson('/api/files', [
        'name' => 'report-work.pdf',
        'folder_id' => $work['id'],
    ]);

    $this->postJson('/api/files', [
        'name' => 'report-personal.pdf',
        'folder_id' => $personal['id'],
    ]);

    $response = $this->getJson(
        "/api/search/suggestions?q=report&all=0&folder_id={$work['id']}"
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'report-work.pdf')
        ->assertJsonPath('data.0.folder_id', $work['id'])
        ->assertJsonMissing([
            'name' => 'report-personal.pdf',
        ]);
});

test('exact search across all files returns matches from different folders', function () {
    $work = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => null,
    ])->json('data');

    $personal = $this->postJson('/api/folders', [
        'name' => 'Personal',
        'parent_id' => null,
    ])->json('data');

    $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $work['id'],
    ]);

    $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $personal['id'],
    ]);

    $response = $this->getJson(
        '/api/search/exact?q=report.pdf&all=1'
    );

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'name' => 'report.pdf',
            'folder_id' => $work['id'],
        ])
        ->assertJsonFragment([
            'name' => 'report.pdf',
            'folder_id' => $personal['id'],
        ]);
});

test('treats sql wildcard characters as literal search characters', function () {
    $this->postJson('/api/files', [
        'name' => 'contract.pdf',
        'folder_id' => null,
    ]);

    $response = $this->getJson(
        '/api/search/suggestions?q=%25&all=1'
    );

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('paginates exact search across all files', function () {
    for ($i = 1; $i <= 55; $i++) {
        $folder = $this->postJson('/api/folders', [
            'name' => "Folder {$i}",
            'parent_id' => null,
        ])->json('data');

        $this->postJson('/api/files', [
            'name' => 'report.pdf',
            'folder_id' => $folder['id'],
        ])->assertCreated();
    }

    $response = $this->getJson(
        '/api/search/exact?q=report.pdf&all=1&page=1'
    );

    $response
        ->assertOk()
        ->assertJsonCount(50, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.total', 55);
});
