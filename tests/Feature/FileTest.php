<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates a file inside a folder', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    $response = $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $folder['id'],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'report.pdf')
        ->assertJsonPath('data.folder_id', $folder['id']);

    $this->assertDatabaseHas('files', [
        'name' => 'report.pdf',
        'folder_id' => $folder['id'],
    ]);
});

test('creates a file in root', function () {
    $response = $this->postJson('/api/files', [
        'name' => 'readme.txt',
        'folder_id' => null,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'readme.txt')
        ->assertJsonPath('data.folder_id', null);
});

test('deletes a file', function () {
    $file = $this->postJson('/api/files', [
        'name' => 'notes.txt',
        'folder_id' => null,
    ])->json('data');

    $this->deleteJson("/api/files/{$file['id']}")
        ->assertNoContent();

    $this->assertDatabaseMissing('files', [
        'id' => $file['id'],
    ]);
});

test('does not allow duplicate files in the same folder', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $folder['id'],
    ])->assertCreated();

    $response = $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $folder['id'],
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('rejects an empty file name', function () {
    $response = $this->postJson('/api/files', [
        'name' => '',
        'folder_id' => null,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
