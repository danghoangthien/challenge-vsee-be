<?php

namespace Tests\Feature;

use Tests\BaseTestCase;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Provider;
use App\Models\VisitorExamination;
use App\Models\LoungeQueue;
use App\Events\VisitorPickedUpEvent;
use App\Events\ProviderPickedUpVisitorEvent;
use App\Events\VisitorExaminationCompletedEvent;
use App\Events\ProviderExaminationCompletedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\QueueEntry;
use App\Events\VisitorJoinedQueue;
use App\Events\VisitorExitedQueue;
use App\Events\ProviderPickedUpVisitor;
use App\Events\ProviderDroppedOffVisitor;
use App\Services\RedisLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoungeQueueIntegrationTest extends BaseTestCase
{
    use RefreshDatabase;

    private User $visitorUser;
    private User $providerUser;
    private Visitor $visitor;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create provider user and provider first
        $this->providerUser = User::factory()->create();
        $this->provider = Provider::factory()->create([
            'user_id' => $this->providerUser->id,
            'department_id' => 1,
            'role_id' => 1
        ]);
        $this->providerUser->refresh(); // Refresh to load the provider relationship
        $this->providerUser->load('provider'); // Explicitly load the provider relationship

        // Create visitor user and visitor
        $this->visitorUser = User::factory()->create();
        $this->visitor = Visitor::factory()->create([
            'user_id' => $this->visitorUser->id
        ]);
        $this->visitorUser->refresh(); // Refresh to load the visitor relationship
        $this->visitorUser->load('visitor'); // Explicitly load the visitor relationship

        // Mock Redis lock service
        $this->mock(RedisLock::class, function ($mock) {
            $mock->shouldReceive('executeWithLock')
                ->andReturnUsing(function ($key, $callback) {
                    return $callback();
                });
        });

        // Fake events
        Event::fake([
            VisitorJoinedQueue::class,
            VisitorExitedQueue::class,
            ProviderPickedUpVisitorEvent::class,
        ]);

        // Clear MongoDB collections
        DB::connection('mongodb')->collection('waiting_room_queue')->truncate();
    }

    protected function tearDown(): void
    {
        // Clear MongoDB collections
        DB::connection('mongodb')->collection('waiting_room_queue')->truncate();
        parent::tearDown();
    }

    /**
     * Get JWT token for authentication
     */
    protected function getJwtToken(User $user): string
    {
        auth('api')->login($user);
        $token = JWTAuth::fromUser($user);
        JWTAuth::setToken($token);
        return $token;
    }

    /**
     * Test visitor enqueue endpoint
     */
    public function test_visitor_can_enqueue(): void
    {
        $token = $this->getJwtToken($this->visitorUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $response->assertStatus(200);

        // Check MongoDB collection
        $this->assertDatabaseHas('waiting_room_queue', [
            'visitor_id' => $this->visitor->id,
            'user_id' => $this->visitorUser->id
        ], 'mongodb');

        Event::assertDispatched(VisitorJoinedQueue::class);
    }

    /**
     * Test visitor cannot enqueue twice
     */
    public function test_visitor_cannot_enqueue_twice(): void
    {
        $token = $this->getJwtToken($this->visitorUser);

        // First enqueue
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        // Try to enqueue again
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $response->assertStatus(409);
    }

    /**
     * Test visitor can exit queue
     */
    public function test_visitor_can_exit_queue(): void
    {
        $token = $this->getJwtToken($this->visitorUser);

        // First enqueue
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        // Exit queue
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $response->assertStatus(200);

        // Check MongoDB collection
        $this->assertDatabaseMissing('waiting_room_queue', [
            'visitor_id' => $this->visitor->id
        ], 'mongodb');

        Event::assertDispatched(VisitorExitedQueue::class);
    }

    /**
     * Test provider can get waiting list
     */
    public function test_provider_can_get_waiting_list(): void
    {
        // Add visitor to queue
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->visitorUser)
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        // Get waiting list
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->providerUser)
        ])->getJson('/api/provider/lounge/list');

        $response->assertStatus(200);
    }

    /**
     * Test provider can pickup visitor
     */
    public function test_provider_can_pickup_visitor(): void
    {
        // Add visitor to queue
        $enqueueResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->visitorUser)
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $enqueueResponse->assertStatus(200);

        // Verify visitor is in waiting list using LoungeQueue model
        $queueEntry = LoungeQueue::where('visitor_id', (int) $this->visitor->id)->first();
        $this->assertNotNull($queueEntry, 'Visitor should exist in waiting queue');
        $this->assertEquals($this->visitorUser->id, $queueEntry->user_id, 'User ID should match');
        
        // Pickup visitor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->providerUser)
        ])->postJson('/api/provider/lounge/pickup', [
            'visitor_id' => $this->visitor->id
        ]);

        $response->assertStatus(200);

        // Check MySQL table
        $this->assertDatabaseHas('visitor_examinations', [
            'visitor_id' => $this->visitor->id,
            'provider_id' => $this->provider->id,
            'status' => 'in_progress'
        ]);

        // Check MongoDB collection using model
        $this->assertNull(LoungeQueue::where('visitor_id', (int) $this->visitor->id)->first(), 'Visitor should be removed from queue');

        Event::assertDispatched(ProviderPickedUpVisitorEvent::class);
    }

    /**
     * Test provider cannot pickup when busy
     */
    public function test_provider_cannot_pickup_when_busy(): void
    {
        // Create another visitor
        $anotherVisitorUser = User::factory()->create();
        $anotherVisitor = Visitor::factory()->create([
            'user_id' => $anotherVisitorUser->id
        ]);

        // Add both visitors to queue
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->visitorUser)
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($anotherVisitorUser)
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $anotherVisitor->id
        ]);

        // Pickup first visitor
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->providerUser)
        ])->postJson('/api/provider/lounge/pickup', [
            'visitor_id' => $this->visitor->id
        ]);

        // Try to pickup second visitor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->providerUser)
        ])->postJson('/api/provider/lounge/pickup', [
            'visitor_id' => $anotherVisitor->id
        ]);

        $response->assertStatus(409);
    }

    /**
     * Test provider can dropoff visitor
     */
    public function test_provider_can_dropoff_visitor(): void
    {
        // Add visitor to queue and pickup
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken($this->visitorUser)
        ])->postJson('/api/visitor/lounge/queue', [
            'visitor_id' => $this->visitor->id
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' .  $this->getJwtToken($this->providerUser)
        ])->postJson('/api/provider/lounge/pickup', [
            'visitor_id' => $this->visitor->id
        ]);

        // Dropoff visitor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' .  $this->getJwtToken($this->providerUser)
        ])->postJson('/api/provider/lounge/dropoff', [
            'visitor_id' => $this->visitor->id
        ]);

        $response->assertStatus(200);

        // Check MySQL table
        $this->assertDatabaseHas('visitor_examinations', [
            'visitor_id' => $this->visitor->id,
            'provider_id' => $this->provider->id,
            'status' => 'completed'
        ]);
    }
}
