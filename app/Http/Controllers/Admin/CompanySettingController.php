<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\QuotationItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $settings  = CompanySetting::current();
        $itemTypes = QuotationItemType::orderBy('sort_order')->get();
        $accounts  = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.company-settings.edit', compact('settings', 'itemTypes', 'accounts'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // General
            'company_name'           => 'required|string|max:255',
            'trading_name'           => 'nullable|string|max:255',
            'tagline'                => 'nullable|string|max:500',
            'description'            => 'nullable|string|max:2000',
            'year_established'       => 'nullable|string|max:10',
            'business_type'          => 'nullable|string|max:100',
            'registration_number'    => 'nullable|string|max:100',
            'tin_number'             => 'nullable|string|max:50',
            'vrn_number'             => 'nullable|string|max:50',
            'business_license_number'=> 'nullable|string|max:100',

            // Contact
            'phone_primary'          => 'nullable|string|max:50',
            'phone_secondary'        => 'nullable|string|max:50',
            'email_general'          => 'nullable|email|max:255',
            'email_billing'          => 'nullable|email|max:255',
            'email_support'          => 'nullable|email|max:255',
            'website'                => 'nullable|url|max:255',
            'facebook_url'           => 'nullable|url|max:255',
            'linkedin_url'           => 'nullable|url|max:255',
            'twitter_url'            => 'nullable|url|max:255',
            'instagram_url'          => 'nullable|url|max:255',
            'working_hours'          => 'nullable|string|max:255',

            // Address
            'address_line1'          => 'nullable|string|max:255',
            'address_line2'          => 'nullable|string|max:255',
            'city'                   => 'nullable|string|max:100',
            'district'               => 'nullable|string|max:100',
            'region'                 => 'nullable|string|max:100',
            'country'                => 'nullable|string|max:100',
            'postal_code'            => 'nullable|string|max:20',
            'po_box'                 => 'nullable|string|max:50',

            // Banking
            'bank_name'              => 'nullable|string|max:255',
            'bank_account_name'      => 'nullable|string|max:255',
            'bank_account_number'    => 'nullable|string|max:100',
            'bank_swift_code'        => 'nullable|string|max:20',
            'bank_branch_name'       => 'nullable|string|max:255',
            'bank_branch_code'       => 'nullable|string|max:50',

            // Documents
            'vat_rate'               => 'required|numeric|min:0|max:100',
            'default_currency'       => 'required|string|max:10',
            'fx_clearing_account_id' => 'nullable|exists:accounts,id',
            'invoice_prefix'         => 'required|string|max:20',
            'quotation_prefix'       => 'required|string|max:20',
            'payment_terms_days'     => 'required|integer|min:0|max:365',
            'invoice_terms'          => 'nullable|string|max:5000',
            'invoice_notes'          => 'nullable|string|max:2000',
            'quotation_terms'        => 'nullable|string|max:5000',
            'payment_instructions'   => 'nullable|string|max:2000',
            'contract_terms'         => 'nullable|string|max:10000',

            // Branding
            'logo'                   => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'stamp'                  => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'primary_color'          => 'nullable|string|max:20',
            'secondary_color'        => 'nullable|string|max:20',
        ]);

        $settings = CompanySetting::current();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        // Handle stamp upload
        if ($request->hasFile('stamp')) {
            if ($settings->stamp_path) {
                Storage::disk('public')->delete($settings->stamp_path);
            }
            $validated['stamp_path'] = $request->file('stamp')->store('company', 'public');
        }

        // Remove raw file keys (not DB columns)
        unset($validated['logo'], $validated['stamp']);

        $settings->update($validated);

        return back()->with('success', 'Company settings saved successfully.');
    }

    public function deleteLogo()
    {
        $settings = CompanySetting::current();

        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return back()->with('success', 'Company logo removed.');
    }

    public function deleteStamp()
    {
        $settings = CompanySetting::current();

        if ($settings->stamp_path) {
            Storage::disk('public')->delete($settings->stamp_path);
            $settings->update(['stamp_path' => null]);
        }

        return back()->with('success', 'Company stamp removed.');
    }
}
