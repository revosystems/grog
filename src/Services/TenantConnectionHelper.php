<?php declare(strict_types=1);

namespace BadChoice\Grog\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class TenantConnectionHelper
{
    private ?Authenticatable $previousLogin = null;
    private string $previousConnection;
    private ProvidesDatabaseConnection|Authenticatable|null $user;

    public function __construct(
        protected string|ProvidesDatabaseConnection $connection,
        protected \Closure $callback,
        private bool $withLogin = false,
    ) {
        $this->previousConnection = DB::getDefaultConnection();
        $this->user = $this->getDatabaseConnectionProvider();
    }

    public function withLogin(bool $withLogin): self
    {
        $this->withLogin = $withLogin;

        return $this;
    }

    public function handle(): mixed
    {
        if (! $this->user) {
            return null;
        }

        $this->preHandle();

        $result = ($this->callback)($this->user);

        $this->postHandle();

        return $result;
    }

    private function preHandle(): void
    {
        $this->handleLogin();
        $this->connect();
    }

    private function handleLogin(): void
    {
        if ($this->withLogin === false) {
            return;
        }

        $this->previousLogin = auth()->user();
        auth()->login($this->user);
    }

    private function connect(): void
    {
        createDBConnection($this->user, true);
    }

    private function postHandle(): void
    {
        $this->disconnect();
        $this->handleLogout();
    }

    private function disconnect(): void
    {
        if (! app()->environment('testing')) {
            DB::disconnect($this->user->getDatabaseName());
        }

        DB::setDefaultConnection($this->previousConnection);
    }

    private function handleLogout(): void
    {
        if ($this->previousLogin === null) {
            return;
        }

        auth()->logout();
        auth()->login($this->previousLogin);
    }

    private function getDatabaseConnectionProvider(): ProvidesDatabaseConnection
    {
        if (is_string($this->connection)) {
            return RVConnection::$provider::databaseConnectionProviderByName($this->connection);
        }

        return $this->connection;
    }
}
