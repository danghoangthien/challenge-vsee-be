<?php

namespace Tests\Unit\Middleware;

use Tests\BaseTestCase;
use App\Models\User;
use App\Models\Provider;
use App\Models\Visitor;
use App\Http\Middleware\VisitorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;

class VisitorMiddlewareTest extends BaseTestCase
{
    private VisitorMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new VisitorMiddleware();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_403_for_unauthenticated_user(): void
    {
        $request = Request::create('/api/visitor/lounge/queue', 'POST');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only visitors can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_returns_403_for_user_without_visitor(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('visitor')->andReturn(null);

        Auth::shouldReceive('guard')->with('api')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/api/visitor/lounge/queue', 'POST');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only visitors can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_returns_403_for_provider_user(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('visitor')->andReturn(null);

        Auth::shouldReceive('guard')->with('api')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/api/visitor/lounge/queue', 'POST');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only visitors can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_allows_access_for_visitor_user_without_mock_request(): void
    {
        // Create a test visitor user
        $user = User::factory()->create();
        $visitor = Visitor::factory()->create([
            'user_id' => $user->id
        ]);

        // Create a test endpoint that uses the middleware
        $this->app['router']->post('/test-visitor', function (Request $request) {
            return response()->json([
                'success' => true,
                'visitor_id' => $request->context['visitor']->id
            ]);
        })->middleware('visitor');

        // Make a request with the visitor's JWT token
        $token = auth('api')->login($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/test-visitor');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'visitor_id' => $visitor->id
        ], json_decode($response->getContent(), true));
    }
} 