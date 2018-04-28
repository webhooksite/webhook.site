<?php

namespace App\Console\Commands;

use App\Requests\Request;
use App\Tokens\Token;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Redis;

/**
 * @property  redis
 */
class MigrateToRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from SQLite to Redis';
    /**
     * @var Connection
     */
    private $db;
    /**
     * @var Redis
     */
    private $redis;

    /**
     * Create a new command instance.
     *
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        parent::__construct();
        $this->db = $db->connection();
        $this->redis = Redis::connection(config('database.redis.connection'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Set maintenance mode.
        $this->call('down');

        $count = Token::all()->count();

        $this->output->progressStart($count);

        Token::chunk(100, function ($tokens) {
            foreach ($tokens as $token) {
                $this->insertToken($token);
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();

        $this->call('up');
    }

    /**
     * @param Token $token
     */
    private function insertToken(Token $token)
    {
        $tokenKey = sprintf('%s:%s', 'token', $token->uuid);
        $requestsKey = sprintf('%s:%s', $tokenKey, 'requests');

        if ($this->redis->exists($tokenKey)) {
            $this->redis->expire($tokenKey, config('app.expiry'));
            $this->redis->expire($requestsKey, config('app.expiry'));
            return;
        }

        $this->redis->set($tokenKey, $token);

        $token->requests()->chunk(500, function ($requests) use ($token, $requestsKey) {
            if ($this->redis->hlen($requestsKey) > config('app.max_requests')) {
                // Abort request import for token.
                return false;
            }

            foreach ($requests as $request) {
                $this->insertRequest($requestsKey, $request);
            }
        });

        $this->redis->expire($tokenKey, config('app.expiry'));
        $this->redis->expire($requestsKey, config('app.expiry'));
    }

    /**
     * @param Request $request
     */
    private function insertRequest($key, Request $request)
    {
        try {
            $this->redis->hmset($key, $request->uuid, $request);
        } catch (\ErrorException $e) {
            // Continue on __toString errors.
        }
    }
}
