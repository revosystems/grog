<?php declare(strict_types=1);

namespace BadChoice\Grog\Services;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class TenantConnectionHelper
{
    protected string|ProvidesDatabaseConnection $connection;
    protected Closure $callback;
    protected string $previousConnection;
    protected ?Authenticatable $previousLogin = null;
    protected bool $withLogin = false;

    public function __construct(string|ProvidesDatabaseConnection $connection, callable $callback)
    {
        $this->connection = $connection;
        $this->callback = Closure::fromCallable($callback);
    }

    public function withLogin(bool $withLogin = true): self
    {
        $this->withLogin = $withLogin;

        return $this;
    }

    public function handle(): mixed
    {
        if (!$user = $this->getDatabaseConnectionProvider()) {
            return null;
        }
        $this->preHandle($user);

        $result = ($this->callback)($user);

        $this->postHandle($user);

        return $result;
    }

    protected function preHandle(Authenticatable|ProvidesDatabaseConnection $user): void
    {
        $this->handleLogin($user);
        $this->connect($user);
    }

    protected function handleLogin(Authenticatable|ProvidesDatabaseConnection $user): void
    {
        if ($this->withLogin === false) {
            return;
        }

        $this->previousLogin = auth()->user();
        auth()->login($user);
    }

    protected function connect(Authenticatable|ProvidesDatabaseConnection $user): void
    {
        $this->previousConnection = DB::getDefaultConnection();
        createDBConnection($user, true);
    }

    protected function postHandle(Authenticatable|ProvidesDatabaseConnection $user): void
    {
        $this->disconnect($user);
        $this->handleLogout();
    }

    protected function disconnect(Authenticatable|ProvidesDatabaseConnection $user): void
    {
        if (! app()->environment('testing')) {
            DB::disconnect($user->getDatabaseName());
        }

        DB::setDefaultConnection($this->previousConnection);
    }

    protected function handleLogout(): void
    {
        if ($this->previousLogin === null) {
            return;
        }

        auth()->logout();
        auth()->login($this->previousLogin);
    }

    protected function getDatabaseConnectionProvider(): ?ProvidesDatabaseConnection
    {
        if (is_string($this->connection)) {
            return RVConnection::$provider::databaseConnectionProviderByName($this->connection);
        }

        return $this->connection;
    }
}
