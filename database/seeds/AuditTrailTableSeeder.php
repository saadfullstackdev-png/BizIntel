<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\AuditTrailTables;

class AuditTrailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $audit_trail_tables = [
            1 => array(
                'id' => 1,
                'name' => 'cities',
                'screen' => 'City',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            2 => array(
                'id' => 2,
                'name' => 'locations',
                'screen' => 'Centres',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            3 => array(
                'id' => 3,
                'name' => 'lead_sources',
                'screen' => 'Lead Source',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            4 => array(
                'id' => 4,
                'name' => 'lead_statuses',
                'screen' => 'Lead Status',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            5 => array(
                'id' => 5,
                'name' => 'resource_types',
                'screen' => 'Resource Type',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            6 => array(
                'id' => 6,
                'name' => 'service_has_locations',
                'screen' => 'Service Has Location',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            7 => array(
                'id' => 7,
                'name' => 'cancellation_reasons',
                'screen' => 'Cancellation Reason',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            8 => array(
                'id' => 8,
                'name' => 'resources',
                'screen' => 'Resource',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            9 => array(
                'id' => 9,
                'name' => 'appointment_statuses',
                'screen' => 'Appointment Status',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            10 => array(
                'id' => 10,
                'name' => 'payment_modes',
                'screen' => 'Payment Mode',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            11 => array(
                'id' => 11,
                'name' => 'settings',
                'screen' => 'Setting',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            12 => array(
                'id' => 12,
                'name' => 'sms_templates',
                'screen' => 'SMS Templates',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            13 => array(
                'id' => 13,
                'name' => 'services',
                'screen' => 'Service',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            14 => array(
                'id' => 14,
                'name' => 'user_types',
                'screen' => 'User Types',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            15 => array(
                'id' => 15,
                'name' => 'resource_has_rota',
                'screen' => 'Resource Has Rota',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            16 => array(
                'id' => 16,
                'name' => 'resource_has_rota_days',
                'screen' => 'Resource Has Rota Days',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            17 => array(
                'id' => 17,
                'name' => 'users',
                'screen' => 'Users',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            18 => array(
                'id' => 18,
                'name' => 'leads',
                'screen' => 'Lead',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            19 => array(
                'id' => 19,
                'name' => 'role_has_users',
                'screen' => 'Role Has User',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            20 => array(
                'id' => 20,
                'name' => 'user_has_locations',
                'screen' => 'User Has Location',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            21 => array(
                'id' => 21,
                'name' => 'discounts',
                'screen' => 'Discount',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            22 => array(
                'id' => 22,
                'name' => 'user_operator_settings',
                'screen' => 'Operator Settings',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            23 => array(
                'id' => 23,
                'name' => 'packages',
                'screen' => 'Plans',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            24 => array(
                'id' => 24,
                'name' => 'package_bundles',
                'screen' => 'Plan Bundle',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            25 => array(
                'id' => 25,
                'name' => 'package_advances',
                'screen' => 'Finances',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            26 => array(
                'id' => 26,
                'name' => 'invoices',
                'screen' => 'Invoice',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            27 => array(
                'id' => 27,
                'name' => 'invoice_details',
                'screen' => 'Invoice Detail',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            28 => array(
                'id' => 28,
                'name' => 'resource_has_services',
                'screen' => 'Resource Has Services',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            29 => array(
                'id' => 29,
                'name' => 'documents',
                'screen' => 'Documents upload',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            30 => array(
                'id' => 30,
                'name' => 'regions',
                'screen' => 'Regions',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            31 => array(
                'id' => 31,
                'name' => 'appointmentimages',
                'screen' => 'Appointment Images',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            32 => array(
                'id' => 32,
                'name' => 'bundles',
                'screen' => 'Packages',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            33 => array(
                'id' => 33,
                'name' => 'bundle_has_services',
                'screen' => 'Package Has Services',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            34 => array(
                'id' => 34,
                'name' => 'package_services',
                'screen' => 'Plan Services',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            35 => array(
                'id' => 35,
                'name' => 'custom_forms',
                'screen' => 'Custom Forms',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            36 => array(
                'id' => 36,
                'name' => 'custom_form_fields',
                'screen' => 'Custom Form Fields',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            37 => array(
                'id' => 37,
                'name' => 'custom_form_feedbacks',
                'screen' => 'Custom Form Feedbacks',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            38 => array(
                'id' => 38,
                'name' => 'custom_form_feedback_details',
                'screen' => 'Custom Form Feedback Details',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            39 => array(
                'id' => 39,
                'name' => 'appointments',
                'screen' => 'Appointments',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            40 => array(
                'id' => 40,
                'name' => 'staff_targets',
                'screen' => 'Staff Targets',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            41 => array(
                'id' => 41,
                'name' => 'staff_target_services',
                'screen' => 'Staff Target Services',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            43 => array(
                'id' => 43,
                'name' => 'measurements',
                'screen' => 'Measurements',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            44 => array(
                'id' => 44,
                'name' => 'medicals',
                'screen' => 'Medicals History',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            45 => array(
                'id' => 45,
                'name' => 'centertarget ',
                'screen' => 'Centre Target',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            46 => array(
                'id' => 46,
                'name' => 'centretargetmeta',
                'screen' => 'Centre Target Meta',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            47 => array(
                'id' => 47,
                'name' => 'machine_types',
                'screen' => 'Machine Type',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            48 => array(
                'id' => 48,
                'name' => 'machine_type_has_services',
                'screen' => 'Machine Type Has Resource',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            49 => array(
                'id' => 49,
                'name' => 'towns',
                'screen' => 'Town',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            50 => array(
                'id' => 50,
                'name' => 'banners',
                'screen' => 'Banners',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            51 => array(
                'id' => 51,
                'name' => 'promotions',
                'screen' => 'Promotions',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            52 => array(
                'id' => 52,
                'name' => 'discountallocations',
                'screen' => 'Discount Allocations',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            53 => array(
                'id' => 53,
                'name' => 'faqs',
                'screen' => 'Faqs',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            54 => array(
                'id' => 54,
                'name' => 'termsandpolicies',
                'description' => 'Termsandpolicies',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            55 => array(
                'id' => 55,
                'name' => 'categories',
                'description' => 'Categories',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
        ];

        if (count($audit_trail_tables)) {
            foreach ($audit_trail_tables as $audit_trail_table) {
                AuditTrailTables::updateOrCreate([
                    'id' => $audit_trail_table['id']
                ], $audit_trail_table);
            }
        }
    }
}
