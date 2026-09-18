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

    $this->assertSoftDeleted('folders', [
        'id' => $folder['id'],
    ]);
});