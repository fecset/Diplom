<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigratePrivateDocuments extends Command
{
    protected $signature = 'personnel:private-documents {--dry-run}';

    protected $description = 'Move legacy public leave attachments to private storage; verify before deleting originals.';

    private function validPath(string $path): bool
    {
        return (bool) preg_match('~^documents/[^/\\\\]+\.(?:pdf|png|jpe?g)$~i', $path);
    }

    public function handle(): int
    {
        $failed = false;
        LeaveRequest::whereNotNull('document_path')->orderBy('id')->chunkById(100, function ($rows) use (&$failed) {
            foreach ($rows as $leave) {
                $old = $leave->document_path;
                $new = null;
                if (! $this->validPath($old)) {
                    $this->error('Invalid path for leave #'.$leave->id);
                    $failed = true;

                    continue;
                }
                try {
                    if (Storage::disk('private')->exists($old)) {
                        // Durable legacy_document_path lets a repeated run finish an interrupted cleanup.
                        if ($leave->legacy_document_path) {
                            $this->cleanup($leave);
                        }

                        continue;
                    }
                    if (! Storage::disk('public')->exists($old)) {
                        throw new \RuntimeException('Missing legacy document');
                    }
                    if ($this->option('dry-run')) {
                        $this->line('Will migrate leave #'.$leave->id);

                        continue;
                    }
                    $new = 'documents/'.Str::uuid().'.'.strtolower(pathinfo($old, PATHINFO_EXTENSION));
                    $bytes = Storage::disk('public')->get($old);
                    Storage::disk('private')->put($new, $bytes);
                    if (! hash_equals(hash('sha256', $bytes), hash('sha256', Storage::disk('private')->get($new)))) {
                        throw new \RuntimeException('Copy checksum mismatch');
                    }
                    DB::transaction(function () use ($leave, $old, $new) {
                        $row = LeaveRequest::whereKey($leave->id)->lockForUpdate()->firstOrFail();
                        if ($row->document_path !== $old) {
                            throw new \RuntimeException('Document changed concurrently');
                        }
                        $row->update(['document_path' => $new, 'legacy_document_path' => $old]);
                    });
                    $new = null; // Private copy is now referenced; never delete it if cleanup fails.
                    $leave->refresh();
                    $this->cleanup($leave);
                    $this->line('Migrated leave #'.$leave->id);
                } catch (\Throwable $e) {
                    if ($new) {
                        Storage::disk('private')->delete($new);
                    }$this->error('Failed leave #'.$leave->id.': '.$e->getMessage());
                    $failed = true;
                }
            }
        });
        // Shared legacy paths can be cleared after every referring row was migrated.
        if (! $this->option('dry-run')) {
            LeaveRequest::whereNotNull('legacy_document_path')->orderBy('id')->chunkById(100, function ($rows) use (&$failed) {
                foreach ($rows as $leave) {
                    try {
                        $this->cleanup($leave);
                    } catch (\Throwable $e) {
                        $this->error($e->getMessage());
                        $failed = true;
                    }
                }
            });
        }
        $this->info($this->option('dry-run') ? 'Dry run complete.' : 'Document migration complete.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function cleanup(LeaveRequest $leave): void
    {
        $legacy = $leave->legacy_document_path;
        if (! $legacy || ! $this->validPath($legacy)) {
            throw new \RuntimeException('Invalid legacy path');
        }
        if ($this->option('dry-run')) {
            $this->line('Will remove legacy copy for leave #'.$leave->id);

            return;
        }
        if (LeaveRequest::where('document_path', $legacy)->exists()) {
            return;
        }
        if (! Storage::disk('private')->exists($leave->document_path)) {
            throw new \RuntimeException('Missing private copy');
        }
        if (Storage::disk('public')->exists($legacy)) {
            if (! hash_equals(hash('sha256', Storage::disk('public')->get($legacy)), hash('sha256', Storage::disk('private')->get($leave->document_path)))) {
                throw new \RuntimeException('Legacy copy changed: review manually');
            }
            if (! Storage::disk('public')->delete($legacy)) {
                throw new \RuntimeException('Cannot remove public original');
            }
        }
        $leave->update(['legacy_document_path' => null]);
    }
}
