<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class Photo360AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_token_is_authenticated_and_not_cached(): void
    {
        $this->getJson(route('photo360.session'))->assertUnauthorized();
        $user=User::factory()->create(['email_verified_at'=>now()]);
        $token=Str::random(40);
        $response=$this->actingAs($user)->withSession(['_token'=>$token])
            ->getJson(route('photo360.session'));
        $response->assertOk()->assertJsonPath('csrf_token',$token)
            ->assertJsonPath('user_id',(string)$user->getAuthIdentifier());
        $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
    }

    public function test_private_job_is_only_accessible_by_owner(): void
    {
        $owner=User::factory()->create(['email_verified_at'=>now()]);
        $other=User::factory()->create(['email_verified_at'=>now()]);
        $id=(string)Str::uuid();
        DB::table('photo360_jobs')->insert(['id'=>$id,'user_id'=>$owner->id,'expected'=>30,
            'state'=>'uploading','created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($other)->getJson(route('photo360.status',$id))->assertNotFound();
        $this->actingAs($other)->deleteJson(route('photo360.cancel',$id))->assertNotFound();
        $this->actingAs($owner)->getJson(route('photo360.status',$id))->assertOk()->assertJsonPath('state','uploading');
        $this->actingAs($owner)->postJson(route('photo360.start',$id))->assertUnprocessable();
    }
}
