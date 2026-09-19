<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('live_channels');
    }

    public function down(): void
    {
        // The standalone live TV feature was removed permanently.
    }
};
