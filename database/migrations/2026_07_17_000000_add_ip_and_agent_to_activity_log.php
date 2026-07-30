<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only alter the activity_log table if it already exists (do not create it here).
        if (! Schema::hasTable('activity_log')) {
            return;
        }

            if (! Schema::hasColumn('activity_log', 'ip_address')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->string('ip_address', 45)->nullable()->after('properties');
                });
            }
            if (! Schema::hasColumn('activity_log', 'user_agent')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                });
            }
            if (! Schema::hasColumn('activity_log', 'batch_uuid')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->uuid('batch_uuid')->nullable()->after('properties');
                });
            }
            if (! Schema::hasColumn('activity_log', 'event')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->string('event')->nullable()->after('batch_uuid');
                });
            }

            if (! $this->indexExists('activity_log', 'activity_log_created_at_index')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }
            if (! $this->indexExists('activity_log', 'activity_log_subject_type_subject_id_index')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->index(['subject_type', 'subject_id']);
                });
            }
            if (! $this->indexExists('activity_log', 'activity_log_causer_id_index')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->index('causer_id');
                });
            }
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                if (Schema::hasColumn('activity_log', 'ip_address') || Schema::hasColumn('activity_log', 'user_agent')) {
                    // Drop indexes if they exist
                    try {
                        $table->dropIndex(['activity_log_created_at_index']);
                    } catch (\Exception $e) {
                        // ignore
                    }
                    try {
                        $table->dropIndex(['activity_log_subject_type_subject_id_index']);
                    } catch (\Exception $e) {
                        // ignore
                    }
                    try {
                        $table->dropIndex(['activity_log_causer_id_index']);
                    } catch (\Exception $e) {
                        // ignore
                    }

                    if (Schema::hasColumn('activity_log', 'ip_address')) {
                        $table->dropColumn('ip_address');
                    }
                    if (Schema::hasColumn('activity_log', 'user_agent')) {
                        $table->dropColumn('user_agent');
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $results = \DB::select('SHOW INDEX FROM ' . $table . " WHERE Key_name = ?", [$indexName]);
            return count($results) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
