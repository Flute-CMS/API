<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\DatabaseConnection;
use Flute\Core\Database\Entities\Server;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class ServerService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateServerData(array $data, ?Server $server = null): bool
    {
        $rules = [
            'name' => 'required|min-str-len:3|max-str-len:255',
            'ip' => 'required|regex:/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',
            'port' => 'required|integer|min:1|max:65535',
            'mod' => 'required|string|min-str-len:2|max-str-len:50',
            'display_ip' => 'nullable|string|max-str-len:255',
            'ranks' => 'nullable|string',
            'ranks_format' => 'nullable|string|max-str-len:255',
            'enabled' => 'boolean',
            'db_connections' => 'nullable|array',
            'db_connections.*.mod' => 'string|min-str-len:2|max-str-len:50',
            'db_connections.*.dbname' => 'string|min-str-len:1|max-str-len:255',
            'db_connections.*.additional' => 'nullable|string',
        ];

        if (!$this->validator->validate($data, $rules)) {
            return false;
        }

        if (isset($data['name'])) {
            $servers = Server::findAll();
            foreach ($servers as $s) {
                if ($s->name === $data['name'] && (!$server || $s->id !== $server->id)) {
                    $this->validator->getErrors()->add('name', 'Server with this name already exists');

                    return false;
                }
            }
        }

        if (isset($data['ip'])) {
            $servers = Server::findAll();
            foreach ($servers as $s) {
                if ($s->ip === $data['ip'] && $s->port === ($data['port'] ?? $server->port) && (!$server || $s->id !== $server->id)) {
                    $this->validator->getErrors()->add('ip', 'Server with this IP and port already exists');

                    return false;
                }
            }
        }

        return true;
    }

    public function getAllServers(): array
    {
        return Server::findAll();
    }

    public function getServerById(int $id): ?Server
    {
        return Server::findByPK($id);
    }

    public function createServer(array $data): Server
    {
        if (!$this->validateServerData($data)) {
            throw new InvalidArgumentException('Invalid server data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $server = new Server();
        $this->fillServerData($server, $data);
        $server->saveOrFail();

        if (isset($data['db_connections'])) {
            foreach ($data['db_connections'] as $connData) {
                if (!isset($connData['mod']) || !isset($connData['dbname'])) {
                    continue;
                }

                $conn = new DatabaseConnection();
                $conn->mod = $connData['mod'];
                $conn->dbname = $connData['dbname'];
                $conn->additional = $connData['additional'] ?? null;
                $conn->server = $server;
                $conn->saveOrFail();

                $server->dbconnections[] = $conn;
            }
            $server->saveOrFail();
        }

        return $server;
    }

    public function updateServer(Server $server, array $data): void
    {
        if (!$this->validateServerData($data, $server)) {
            throw new InvalidArgumentException('Invalid server data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $this->fillServerData($server, $data);

        if (isset($data['db_connections'])) {
            foreach ($server->dbconnections as $conn) {
                $conn->delete();
            }
            $server->dbconnections = [];

            foreach ($data['db_connections'] as $connData) {
                if (!isset($connData['mod']) || !isset($connData['dbname'])) {
                    continue;
                }

                $conn = new DatabaseConnection();
                $conn->mod = $connData['mod'];
                $conn->dbname = $connData['dbname'];
                $conn->additional = $connData['additional'] ?? null;
                $conn->server = $server;
                $conn->saveOrFail();

                $server->dbconnections[] = $conn;
            }
        }

        $server->saveOrFail();
    }

    public function deleteServer(Server $server): void
    {
        foreach ($server->dbconnections as $conn) {
            $conn->delete();
        }
        $server->delete();
    }

    public function toggleServer(Server $server): void
    {
        $server->enabled = !$server->enabled;
        $server->saveOrFail();
    }

    public function formatServerData(Server $server, bool $detailed = false): array
    {
        $data = [
            'id' => $server->id,
            'name' => $server->name,
            'ip' => $server->ip,
            'port' => $server->port,
            'mod' => $server->mod,
            'display_ip' => $server->display_ip,
            'connection_string' => $server->getConnectionString(),
            'enabled' => $server->enabled,
            'createdAt' => $server->createdAt->format('c'),
        ];

        if ($detailed) {
            $data += [
                'ranks' => $server->ranks,
                'ranks_format' => $server->ranks_format,
                'db_connections' => array_map(static fn (DatabaseConnection $conn) => [
                    'id' => $conn->id,
                    'mod' => $conn->mod,
                    'dbname' => $conn->dbname,
                    'additional' => $conn->additional,
                ], $server->dbconnections),
            ];
        }

        return $data;
    }

    private function fillServerData(Server $server, array $data): void
    {
        if (isset($data['name'])) {
            $server->name = $data['name'];
        }
        if (isset($data['ip'])) {
            $server->ip = $data['ip'];
        }
        if (isset($data['port'])) {
            $server->port = (int)$data['port'];
        }
        if (isset($data['mod'])) {
            $server->mod = $data['mod'];
        }
        if (isset($data['display_ip'])) {
            $server->display_ip = $data['display_ip'];
        }
        if (isset($data['ranks'])) {
            $server->ranks = $data['ranks'];
        }
        if (isset($data['ranks_format'])) {
            $server->ranks_format = $data['ranks_format'];
        }
        if (isset($data['enabled'])) {
            $server->enabled = filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN);
        }
    }
}
