<?php

namespace Tests\Unit\Middleware;

use Tests\BaseTestCase;   
use App\Models\User;
use App\Models\Provider;
use App\Models\Visitor;
use App\Http\Middleware\ProviderMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;

class ProviderMiddlewareTest extends BaseTestCase
{
    private ProviderMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ProviderMiddleware();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_403_for_unauthenticated_user(): void
    {
        $request = Request::create('/api/provider/lounge/list', 'GET');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only providers can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_returns_403_for_user_without_provider(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('provider')->andReturn(null);

        Auth::shouldReceive('guard')->with('api')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/api/provider/lounge/list', 'GET');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only providers can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_returns_403_for_visitor_user(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('provider')->andReturn(null);

        Auth::shouldReceive('guard')->with('api')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/api/provider/lounge/list', 'GET');
        $response = $this->middleware->handle($request, function () {});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'error' => 'Only providers can access this endpoint'
        ], json_decode($response->getContent(), true));
    }

    public function test_it_allows_access_for_provider_user_without_mock_request(): void
    {
        // Create a test provider user
        $user = User::factory()->create();
        $provider = Provider::factory()->create([
            'user_id' => $user->id
        ]);

        // Create a test endpoint that uses the middleware
        $this->app['router']->get('/test-provider', function (Request $request) {
            return response()->json([
                'success' => true,
                'provider_id' => $request->context['provider']->id
            ]);
        })->middleware('provider');

        // Make a request with the provider's JWT token
        $token = auth('api')->login($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/test-provider');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'provider_id' => $provider->id
        ], json_decode($response->getContent(), true));
    }
} 