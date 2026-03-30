<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // ── General Information ────────────────────────────────────────
            $table->string('company_name')->default('Milele Power');
            $table->string('trading_name')->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('year_established', 10)->nullable();
            $table->string('business_type')->nullable(); // e.g. Limited Company, Sole Proprietor
            $table->string('registration_number')->nullable(); // BRELA / TZ business reg
            $table->string('tin_number')->nullable();           // Tax Identification Number
            $table->string('vrn_number')->nullable();           // VAT Registration Number
            $table->string('business_license_number')->nullable();

            // ── Contact Information ────────────────────────────────────────
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('email_general')->nullable();
            $table->string('email_billing')->nullable();
            $table->string('email_support')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('working_hours')->nullable(); // e.g. Mon-Fri 8AM-5PM

            // ── Physical Address ───────────────────────────────────────────
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->default('Tanzania');
            $table->string('postal_code')->nullable();
            $table->string('po_box')->nullable();

            // ── Banking Information ────────────────────────────────────────
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_swift_code')->nullable();
            $table->string('bank_branch_name')->nullable();
            $table->string('bank_branch_code')->nullable();

            // ── Document & Finance Settings ────────────────────────────────
            $table->decimal('vat_rate', 5, 2)->default(18.00);
            $table->string('default_currency', 10)->default('TZS');
            $table->string('invoice_prefix', 20)->default('INV');
            $table->string('quotation_prefix', 20)->default('QT');
            $table->integer('payment_terms_days')->default(30);
            $table->text('invoice_terms')->nullable();      // Legal/payment terms on invoices
            $table->text('invoice_notes')->nullable();      // Footer notes on invoices
            $table->text('quotation_terms')->nullable();    // Terms on quotation documents
            $table->text('payment_instructions')->nullable(); // Bank details text for invoices
            $table->text('contract_terms')->nullable();     // Default contract/booking terms

            // ── Branding ──────────────────────────────────────────────────
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 20)->default('#dc2626');
            $table->string('secondary_color', 20)->default('#1f2937');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
