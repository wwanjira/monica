<?php

use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Models\Contact\Gift;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Account\ImportJob;
use App\Models\Account\ImportJobReport;
use App\Models\User\User;
use App\Models\Account\Invitation;
use App\Models\Journal\JournalEntry;

class AddForeignKeysToJournalEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // we need to parse the journal entries table to make sure that we don't have
        // "ghost" journal entries that are not associated with any account
        JournalEntry::chunk(200, function ($journalEntries) {
            foreach ($journalEntries as $journalEntry) {
                try {
                    Account::findOrFail($journalEntry->account_id);
                } catch (ModelNotFoundException $e) {
                    $journalEntry->delete();
                    continue;
                }
            }
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedInteger('account_id')->change();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }
}
