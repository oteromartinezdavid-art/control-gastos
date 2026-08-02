<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    private const DISK = 'local';
    private const DIR  = 'backups';

    public function index()
    {
        $files = collect(Storage::disk(self::DISK)->files(self::DIR))
            ->map(fn($f) => [
                'nombre' => basename($f),
                'size'   => Storage::disk(self::DISK)->size($f),
                'fecha'  => Carbon::createFromTimestamp(Storage::disk(self::DISK)->lastModified($f)),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('backup.index', compact('files'));
    }

    public function store()
    {
        $driver    = config('database.default');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');

        if ($driver === 'sqlite') {
            $dbPath   = config('database.connections.sqlite.database');
            $filename = "backup_{$timestamp}.sqlite";
            Storage::disk(self::DISK)->put(
                self::DIR . '/' . $filename,
                file_get_contents($dbPath)
            );
        } else {
            $filename = "backup_{$timestamp}.sql";
            Storage::disk(self::DISK)->put(
                self::DIR . '/' . $filename,
                $this->dumpMySQL()
            );
        }

        return redirect()->route('backup.index')
            ->with('success', "Backup creado correctamente: {$filename}");
    }

    public function download(string $filename)
    {
        $path = self::DIR . '/' . $filename;
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->download($path, $filename);
    }

    public function destroy(string $filename)
    {
        $path = self::DIR . '/' . $filename;
        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }

        return redirect()->route('backup.index')
            ->with('success', 'Backup eliminado.');
    }

    public function restoreConfirm(string $filename)
    {
        $path = self::DIR . '/' . $filename;
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        $file = [
            'nombre' => $filename,
            'size'   => Storage::disk(self::DISK)->size($path),
            'fecha'  => Carbon::createFromTimestamp(Storage::disk(self::DISK)->lastModified($path)),
        ];

        return view('backup.restore', compact('file'));
    }

    public function restore(string $filename)
    {
        $path = self::DIR . '/' . $filename;
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        $driver    = config('database.default');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');

        // 1. Backup de seguridad previo a la restauración
        if ($driver === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            Storage::disk(self::DISK)->put(
                self::DIR . "/pre-restore_{$timestamp}.sqlite",
                file_get_contents($dbPath)
            );
        } else {
            Storage::disk(self::DISK)->put(
                self::DIR . "/pre-restore_{$timestamp}.sql",
                $this->dumpMySQL()
            );
        }

        // 2. Restaurar
        if ($driver === 'sqlite') {
            $dbPath  = config('database.connections.sqlite.database');
            $content = Storage::disk(self::DISK)->get($path);
            $tmpPath = $dbPath . '.restoring';
            file_put_contents($tmpPath, $content);
            DB::disconnect();
            if (!rename($tmpPath, $dbPath)) {
                @unlink($tmpPath);
                return back()->with('error', 'Error al restaurar: no se pudo reemplazar la base de datos.');
            }
        } else {
            $sql        = Storage::disk(self::DISK)->get($path);
            $statements = array_filter(
                array_map('trim', explode(";\n", $sql)),
                fn($s) => $s !== '' && !str_starts_with($s, '--')
            );
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        }

        // 3. La sesión ya no existe en la BD restaurada → redirigir a login
        return redirect('/login?restored=1');
    }

    private function dumpMySQL(): string
    {
        $dbName = config('database.connections.mysql.database');

        $out  = "-- Control de Gastos — Backup completo\n";
        $out .= "-- Generado: " . Carbon::now()->toDateTimeString() . "\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = DB::select('SHOW TABLES');
        $key    = 'Tables_in_' . $dbName;

        foreach ($tables as $row) {
            $table  = $row->$key;
            $create = DB::select("SHOW CREATE TABLE `{$table}`");
            $out   .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out   .= $create[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                $inserts = $rows->map(function ($r) {
                    $vals = array_map(function ($v) {
                        return $v === null ? 'NULL' : "'" . addslashes((string) $v) . "'";
                    }, (array) $r);
                    return '(' . implode(', ', $vals) . ')';
                })->implode(",\n");

                $out .= "INSERT INTO `{$table}` VALUES\n{$inserts};\n\n";
            }
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }
}
