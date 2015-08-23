<?php

use App\Models\Email;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{

    protected $tableName = 'emails';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable($this->tableName)) {
            Schema::drop($this->tableName);
        }

        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('to');
            $table->string('from')->nullable();
            $table->string('origin')->nullable();
            $table->string('send_type');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject');
            $table->longText('html');
            $table->timestamps();
            $table->index(['to', 'created_at', 'origin']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->tableName)) {
            Schema::drop($this->tableName);
        }
    }
}
