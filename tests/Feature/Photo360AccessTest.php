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

    public function test_frame_uploads_do_not_exhaust_start_status_or_session_limits(): void
    {
        $owner=User::factory()->create(['email_verified_at'=>now()]);
        $id=(string)Str::uuid();
        DB::table('photo360_jobs')->insert(['id'=>$id,'user_id'=>$owner->id,'expected'=>46,
            'state'=>'uploading','created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($owner);

        // Even validation failures count toward Laravel's upload request limit.
        // 46 requests simulate the largest current camera capture sequence.
        for ($i=0; $i<46; $i++) {
            $this->postJson(route('photo360.frame',$id),['index'=>$i])->assertUnprocessable();
        }
        $this->getJson(route('photo360.session'))->assertOk();
        // The controller must reject incomplete uploads, not the throttle.
        $this->postJson(route('photo360.start',$id))->assertUnprocessable();
        for ($i=0; $i<60; $i++) {
            $this->getJson(route('photo360.status',$id))->assertOk();
        }
        $this->getJson(route('photo360.status',$id))->assertStatus(429);
        $this->getJson(route('photo360.session'))->assertOk();
        $this->postJson(route('photo360.start',$id))->assertUnprocessable();
        $this->getJson(route('photo360.image',$id))->assertNotFound();
        $this->deleteJson(route('photo360.cancel',$id))->assertNoContent();
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
