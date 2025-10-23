<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Helpers\TokenHelper;

class TokenHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('user_tokens')->truncate();

        Config::set('token.secret_key', 'test_secret_key');
        Config::set('token.expiry_hours', 1);
    }

    /** @test */
    public function it_generates_a_valid_token_structure()
    {
        $token = TokenHelper::generate(1, 'retailer');
        $this->assertNotEmpty($token, 'Token should not be empty');
        $this->assertCount(5, explode('.', $token), 'Token should have 5 parts');
    }

    /** @test */
    public function it_stores_token_in_database()
    {
        $token = TokenHelper::generate(1, 'retailer');
        $exists = DB::table('user_tokens')->where('token', $token)->exists();
        $this->assertTrue($exists, 'Generated token should be stored in DB');
    }

    /** @test */
    public function it_validates_a_fresh_token_successfully()
    {
        $token = TokenHelper::generate(1, 'admin');
        $result = TokenHelper::validate($token);

        $this->assertTrue($result['valid'], 'Token should be valid');
        $this->assertEquals('admin', $result['data']['portal']);
        $this->assertEquals(1, $result['data']['user_id']);
    }

    /** @test */
    public function it_rejects_token_with_invalid_hmac()
    {
        $token = TokenHelper::generate(1, 'retailer');
        $tampered = substr_replace($token, 'zzz', -3);
        $result = TokenHelper::validate($tampered);

        $this->assertFalse($result['valid']);
        $this->assertEquals('invalid_hmac', $result['reason']);
    }

    /** @test */
    public function it_rejects_expired_tokens()
    {
        $userId = 1;
        $portal = 'retailer';

        // Simulate expired token (generate properly signed but old)
        $secretKey = Config::get('token.secret_key');
        $randomHex = bin2hex(random_bytes(16));
        $encodedExpiry = base64_encode(Carbon::now()->subHours(2)->timestamp);
        $hmac = hash_hmac('sha256', "{$userId}|{$portal}|{$randomHex}|{$encodedExpiry}", $secretKey);
        $expiredToken = "{$userId}.{$portal}.{$randomHex}.{$encodedExpiry}.{$hmac}";

        DB::table('user_tokens')->insert([
            'user_id' => $userId,
            'portal' => $portal,
            'token' => $expiredToken,
            'expires_at' => Carbon::now()->subHours(2)->toDateTimeString(),
            'created_at' => Carbon::now()->subHours(3)->toDateTimeString(),
        ]);

        $result = TokenHelper::validate($expiredToken);
        $this->assertFalse($result['valid']);
        $this->assertEquals('expired', $result['reason']);
    }

    /** @test */
    public function it_rejects_token_not_in_database()
    {
        $token = TokenHelper::generate(1, 'retailer');
        DB::table('user_tokens')->where('token', $token)->delete();

        $result = TokenHelper::validate($token);
        $this->assertFalse($result['valid']);
        $this->assertEquals('not_found', $result['reason']);
    }
}
