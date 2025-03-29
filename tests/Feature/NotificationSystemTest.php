<?php

namespace Tests\Feature;

use Tests\BaseTestCase;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Provider;
use App\Models\VisitorExamination;
use App\Events\VisitorPickedUpEvent;
use App\Events\ProviderPostponeVisitorEvent;
use App\Events\VisitorExaminationCompletedEvent;
use App\Events\ProviderPickedUpVisitorEvent;
use App\Events\ProviderExaminationCompletedEvent;
use App\Events\VisitorExitedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Console\Kernel;

class NotificationSystemTest extends BaseTestCase
{
    use RefreshDatabase;

    private User $user;
    private Visitor $visitor;
    private Provider $provider;

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and visitor
        $this->user = User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'Visitor',
            'email' => 'test@example.com'
        ]);
        
        $this->visitor = Visitor::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create test provider with department
        $providerUser = User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'Provider'
        ]);
        $this->provider = Provider::factory()->create([
            'user_id' => $providerUser->id,
            'department_id' => 1, // Ensure department_id is set
            'role_id' => 1 // Ensure role_id is set
        ]);
    }

    /**
     * TC-2.1: WebSocket Connection Test
     */
    public function test_websocket_connection_and_channel_subscription(): void
    {
        // Login as visitor
        Auth::login($this->user);
        
        // Test channel authorization by making a request to the channel
        $response = $this->post('/broadcasting/auth', [
            'channel_name' => "private-visitor.{$this->visitor->id}",
            'socket_id' => '12345.67890'
        ]);
        
        $response->assertOk();
        $this->assertJson($response->getContent());

        // Skip Redis test in test environment
        $this->markTestIncomplete('Redis connection test skipped in test environment');
    }

    /**
     * TC-2.2: Provider Message Reception Test
     */
    public function test_provider_message_reception(): void
    {
        Event::fake();

        // Create examination record
        $examination = VisitorExamination::factory()->create([
            'visitor_id' => $this->visitor->id,
            'provider_id' => $this->provider->id,
            'queue_entry_id' => '507f1f77bcf86cd799439011', // MongoDB ObjectId format
            'started_at' => now(),
            'status' => 'in_progress'
        ]);

        // Test provider pickup notification
        $pickupEvent = new ProviderPickedUpVisitorEvent($examination);
        event($pickupEvent);
        Event::assertDispatched(ProviderPickedUpVisitorEvent::class, function ($event) use ($examination) {
            $channels = $event->broadcastOn();
            $data = $event->broadcastWith();
            return $channels[0]->name === "private-provider.{$this->provider->id}" &&
                   $data['examination_id'] === $examination->id &&
                   $data['message'] === 'You have picked up a visitor';
        });

        // Test provider examination completion notification
        $examination->status = 'completed';
        $examination->ended_at = now();
        $examination->save();

        event(new ProviderExaminationCompletedEvent($examination));
        Event::assertDispatched(ProviderExaminationCompletedEvent::class, function ($event) use ($examination) {
            $channels = $event->broadcastOn();
            $data = $event->broadcastWith();
            return $channels[0]->name === "private-provider.{$this->provider->id}" &&
                   $data['examination_id'] === $examination->id &&
                   $data['message'] === 'You have completed the examination' &&
                   isset($data['duration']);
        });

        // Test visitor pickup notification
        event(new VisitorPickedUpEvent($examination));
        Event::assertDispatched(VisitorPickedUpEvent::class, function ($event) use ($examination) {
            $channels = $event->broadcastOn();
            $data = $event->broadcastWith();
            return $channels[0]->name === "private-visitor.{$this->visitor->id}" &&
                   $data['examination_id'] === $examination->id;
        });

        // Test postpone notification
        event(new ProviderPostponeVisitorEvent($this->visitor, $this->provider));
        Event::assertDispatched(ProviderPostponeVisitorEvent::class, function ($event) {
            return $event->visitor->id === $this->visitor->id &&
                   $event->provider->id === $this->provider->id;
        });

        // Test visitor examination completion notification
        event(new VisitorExaminationCompletedEvent($examination));
        Event::assertDispatched(VisitorExaminationCompletedEvent::class, function ($event) use ($examination) {
            $data = $event->broadcastWith();
            return $data['examination_id'] === $examination->id;
        });
    }

    /**
     * TC-2.3: Message Display Test
     */
    public function test_message_display_formatting(): void
    {
        // Create examination record
        $examination = VisitorExamination::factory()->create([
            'visitor_id' => $this->visitor->id,
            'provider_id' => $this->provider->id,
            'queue_entry_id' => '507f1f77bcf86cd799439011', // MongoDB ObjectId format
            'started_at' => now(),
            'status' => 'in_progress'
        ]);

        // Test provider pickup message format
        $providerPickupEvent = new ProviderPickedUpVisitorEvent($examination);
        $providerPickupData = $providerPickupEvent->broadcastWith();
        
        $this->assertArrayHasKey('message', $providerPickupData);
        $this->assertArrayHasKey('provider', $providerPickupData);
        $this->assertArrayHasKey('visitor', $providerPickupData);
        $this->assertArrayHasKey('started_at', $providerPickupData);
        $this->assertEquals('You have picked up a visitor', $providerPickupData['message']);

        // Test visitor pickup message format
        $visitorPickupEvent = new VisitorPickedUpEvent($examination);
        $visitorPickupData = $visitorPickupEvent->broadcastWith();
        
        $this->assertArrayHasKey('message', $visitorPickupData);
        $this->assertArrayHasKey('provider', $visitorPickupData);
        $this->assertArrayHasKey('visitor', $visitorPickupData);
        $this->assertArrayHasKey('started_at', $visitorPickupData);
        $this->assertEquals('You have been picked up by a provider', $visitorPickupData['message']);

        // Test postpone message format
        $postponeEvent = new ProviderPostponeVisitorEvent($this->visitor, $this->provider);
        $this->assertEquals(
            'The provider is currently busy and will attend to you soon',
            $postponeEvent->message
        );

        // Test provider completion message format
        $examination->status = 'completed';
        $examination->ended_at = now();
        $examination->save();

        $providerCompletionEvent = new ProviderExaminationCompletedEvent($examination);
        $providerCompletionData = $providerCompletionEvent->broadcastWith();
        
        $this->assertArrayHasKey('message', $providerCompletionData);
        $this->assertArrayHasKey('duration', $providerCompletionData);
        $this->assertEquals('You have completed the examination', $providerCompletionData['message']);

        // Test visitor completion message format
        $visitorCompletionEvent = new VisitorExaminationCompletedEvent($examination);
        $visitorCompletionData = $visitorCompletionEvent->broadcastWith();
        
        $this->assertArrayHasKey('message', $visitorCompletionData);
        $this->assertArrayHasKey('duration', $visitorCompletionData);
        $this->assertEquals('Your examination has been completed', $visitorCompletionData['message']);
    }

    /**
     * TC-2.4: Queue Exit Test
     */
    public function test_queue_exit_notifications(): void
    {
        Event::fake();

        // Test visitor exit from queue
        $position = 1;
        $waitedTime = '5 minutes';
        event(new VisitorExitedEvent($this->visitor, $position, $waitedTime));

        Event::assertDispatched(VisitorExitedEvent::class, function ($event) use ($position, $waitedTime) {
            return $event->visitor->id === $this->visitor->id &&
                   $event->position === $position &&
                   $event->waitedTime === $waitedTime &&
                   $event->broadcastOn()[0]->name === "private-visitor.{$this->visitor->id}" &&
                   $event->broadcastOn()[1]->name === 'lounge.queue';
        });
    }
} 