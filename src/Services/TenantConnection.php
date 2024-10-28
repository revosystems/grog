<?php declare(strict_types=1);

namespace BadChoice\Grog\Services;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class TenantConnection
{
    protected ProvidesDatabaseConnection $connection;
    protected Closure $callback;
    protected string $previousConnection;
    protected ?Authenticatable $previousLogin = null;

    public function __construct(string|ProvidesDatabaseConnection $connection, callable $callback)
    {
        $this->connection = is_string($connection)
            ? RVConnection::$provider::databaseConnectionProviderByName($connection)
            : $connection;
        $this->callback = Closure::fromCallable($callback);
        $this->previousConnection = DB::getDefaultConnection();
    }

    public function handle(): mixed
    {
        $this->connect();

        $result = ($this->callback)($this->connection);

        $this->disconnect();

        return $result;
    }

    public function withLogin(): self
    {
        abort_if(! $this->connection instanceof Authenticatable, 500, 'Current connection does not implement Authenticatable');

        $this->previousLogin = auth()->user();
        auth()->login($this->connection);

        return $this;
    }

    protected function connect(): void
    {
        createDBConnection($this->connection, true);
    }

    protected function disconnect(): void
    {
        if (! app()->environment('testing')) {
            DB::disconnect($this->connection->getDatabaseName());
        }

        DB::setDefaultConnection($this->previousConnection);
        $this->handleLogout();
    }

    protected function handleLogout(): void
    {
        if ($this->previousLogin === null) {
            return;
        }

        auth()->logout();
        auth()->login($this->previousLogin);
    }
}
