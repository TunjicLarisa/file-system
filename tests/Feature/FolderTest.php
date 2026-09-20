<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates a root folder', function () {
    $response = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Documents')
        ->assertJsonPath('data.parent_id', null);

    $this->assertDatabaseHas('folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ]);
});

test('creates a subfolder', function () {
    $parent = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    $response = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => $parent['id'],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Work')
        ->assertJsonPath('data.parent_id', $parent['id']);
});

test('deletes a folder', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    $this->deleteJson("/api/folders/{$folder['id']}")
        ->assertNoContent();

    $this->assertDatabaseMissing('folders', [
        'id' => $folder['id'],
    ]);
});

test('deleting a folder deletes its nested contents', function () {
    $parent = $this->postJson('/api/folders', [
        'name' => 'Documents',
        'parent_id' => null,
    ])->json('data');

    $child = $this->postJson('/api/folders', [
        'name' => 'Work',
        'parent_id' => $parent['id'],
    ])->json('data');

    $file = $this->postJson('/api/files', [
        'name' => 'report.pdf',
        'folder_id' => $child['id'],
    ])->json('data');

    $this->deleteJson("/api/folders/{$parent['id']}")
        ->assertNoContent();

    $this->assertDatabaseMissing('folders', [
        'id' => $parent['id'],
    ]);

    $this->assertDatabaseMissing('folders', [
        'id' => $child['id'],
    ]);

    $this->assertDatabaseMissing('files', [
        'id' => $file['id'],
    ]);
});

test('paginates files in a folder', function () {
    $folder = $this->postJson('/api/folders', [
        'name' => 'Large Folder',
        'parent_id' => null,
    ])->json('data');

    for ($i = 1; $i <= 55; $i++) {
        $this->postJson('/api/files', [
            'name' => "file-{$i}.txt",
            'folder_id' => $folder['id'],
        ]);
    }

    $firstPage = $this->getJson(
        "/api/files?folder_id={$folder['id']}&page=1"
    );

    $firstPage
        ->assertOk()
        ->assertJsonCount(50, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2);

    $secondPage = $this->getJson(
        "/api/files?folder_id={$folder['id']}&page=2"
    );

    $secondPage
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2);
});
