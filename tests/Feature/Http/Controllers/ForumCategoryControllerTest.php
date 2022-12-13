<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Forum;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\GroupsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ForumCategoryController
 */
class ForumCategoryControllerTest extends TestCase
{
    /** @test */
    public function show_category_returns_an_ok_response(): void
    {
        $this->seed(UsersTableSeeder::class);
        $this->seed(GroupsTableSeeder::class);

        $user = User::factory()->create();

        // This Forum has a parent Forum, which makes it a "Forum Category".

        $parentForum = Forum::factory()->create();

        Permission::factory()->create([
            'forum_id' => $parentForum->id,
        ]);

        $forum = Forum::factory()->create([
            'parent_id'               => $parentForum->id,
            'last_post_user_id'       => $user->id,
            'last_post_user_username' => $user->username,
        ]);

        $this->actingAs($user)->get(route('forums.categories.show', ['id' => $forum->id]))
            ->assertRedirect(route('forums.show', ['id' => $forum->id]));
    }

    /** @test */
    public function show_forum_returns_an_ok_response(): void
    {
        $this->seed(UsersTableSeeder::class);
        $this->seed(GroupsTableSeeder::class);

        $user = User::factory()->create();

        // This Forum does not have a parent, which makes it a proper Forum
        // (and not a "Forum Category").

        $forum = Forum::factory()->create([
            'parent_id'               => 0,
            'last_post_user_id'       => $user->id,
            'last_post_user_username' => $user->username,
        ]);

        $permissions = Permission::factory()->create([
            'forum_id'   => $forum->id,
            'show_forum' => true,
        ]);

        $response = $this->actingAs($user)->get(route('forums.categories.show', ['id' => $forum->id]));

        $response->assertOk()
            ->assertViewIs('forum.category')
            ->assertViewHas([
                'forum',
                'topics',
                'category',
                'num_posts',
                'num_forums',
                'num_topics',
            ]);
    }
}
