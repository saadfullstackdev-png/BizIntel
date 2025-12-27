<?php


use Illuminate\Support\Carbon;
use App\Http\Controllers\Admin\PatientCardController;
// use BuisnessStatusesController;
use App\Http\Controllers\Admin\AppointmentController;

Route::get('/', function () {
    return redirect('/admin/home');
});

//use Route for register:R
Auth::routes();

// Authentication Routes...
$this->get('login', 'Auth\LoginController@showLoginForm')->name('auth.login');
$this->post('login', 'Auth\LoginController@login')->name('auth.login');

$this->post('logout', 'Auth\LoginController@logout')->name('auth.logout');

// Check Session
Route::get('check-session', 'Auth\LoginController@checkSession')->name('check_session');

// Change Password Routes...
$this->get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
$this->patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');

// Password Reset Routes...
$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('auth.password.reset');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('auth.password.reset');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset')->name('auth.password.reset');

Route::group(['middleware' => ['auth'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/home', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::get('/elastic', ['uses' => 'HomeController@elastic', 'as' => 'elastic']);

    // Permissions Routes
    Route::post('permissions/datatable', ['uses' => 'Admin\PermissionsController@datatable', 'as' => 'permissions.datatable']);
    Route::resource('permissions', 'Admin\PermissionsController');
    Route::post('permissions_mass_destroy', ['uses' => 'Admin\PermissionsController@massDestroy', 'as' => 'permissions.mass_destroy']);

    // Roles Routes
    Route::post('roles/datatable', ['uses' => 'Admin\RolesController@datatable', 'as' => 'roles.datatable']);
    Route::resource('roles', 'Admin\RolesController');
    Route::post('roles_mass_destroy', ['uses' => 'Admin\RolesController@massDestroy', 'as' => 'roles.mass_destroy']);

    //Route For whole project Ajax base patient drop down
    Route::get('users/getpatient', ['uses' => 'Admin\UsersController@getpatient', 'as' => 'users.getpatient']);
    Route::get('users/getpatientid', ['uses' => 'Admin\UsersController@getpatientid', 'as' => 'users.getpatient.id']);
    Route::get('users/get_patient_number', ['uses' => 'Admin\UsersController@getpatientnumber', 'as' => 'users.get_patient_number']);
    //End for ajax base patient drop down

    // Users Routes
    Route::get('users/password/{id}', ['uses' => 'Admin\UsersController@changePassword', 'as' => 'users.change_password']);
    Route::patch('users/password', ['uses' => 'Admin\UsersController@savePassword', 'as' => 'users.save_password']);
    Route::post('users/datatable', ['uses' => 'Admin\UsersController@datatable', 'as' => 'users.datatable']);
    Route::patch('users/active/{id}', ['uses' => 'Admin\UsersController@active', 'as' => 'users.active']);
    Route::patch('users/inactive/{id}', ['uses' => 'Admin\UsersController@inactive', 'as' => 'users.inactive']);
    Route::resource('users', 'Admin\UsersController');
    Route::post('users_mass_destroy', ['uses' => 'Admin\UsersController@massDestroy', 'as' => 'users.mass_destroy']);

    // Regions Routes
    Route::post('regions/datatable', ['uses' => 'Admin\RegionsController@datatable', 'as' => 'regions.datatable']);
    Route::patch('regions/active/{id}', ['uses' => 'Admin\RegionsController@active', 'as' => 'regions.active']);
    Route::patch('regions/inactive/{id}', ['uses' => 'Admin\RegionsController@inactive', 'as' => 'regions.inactive']);
    Route::resource('regions', 'Admin\RegionsController');
    Route::get('regions_sort', ['uses' => 'Admin\RegionsController@sortorder', 'as' => 'regions.sort']);
    Route::get('regions_sort_save', ['uses' => 'Admin\RegionsController@sortorder_save', 'as' => 'regions.sort_save']);

    // Cities Routes
    Route::post('cities/datatable', ['uses' => 'Admin\CitiesController@datatable', 'as' => 'cities.datatable']);
    Route::patch('cities/active/{id}', ['uses' => 'Admin\CitiesController@active', 'as' => 'cities.active']);
    Route::patch('cities/inactive/{id}', ['uses' => 'Admin\CitiesController@inactive', 'as' => 'cities.inactive']);
    Route::get('cities/sort', ['uses' => 'Admin\CitiesController@sortorder', 'as' => 'cities.sort']);
    Route::resource('cities', 'Admin\CitiesController');
    Route::get('cities_sort_save', ['uses' => 'Admin\CitiesController@sortorder_save', 'as' => 'cities.sort_save']);

    // Faqs Routes
    Route::post('faqs/datatable', ['uses' => 'Admin\FaqsController@datatable', 'as' => 'faqs.datatable']);
    Route::patch('faqs/active/{id}', ['uses' => 'Admin\FaqsController@active', 'as' => 'faqs.active']);
    Route::patch('faqs/inactive/{id}', ['uses' => 'Admin\FaqsController@inactive', 'as' => 'faqs.inactive']);
    Route::get('faqs/sort', ['uses' => 'Admin\FaqsController@sortorder', 'as' => 'faqs.sort']);
    Route::resource('faqs', 'Admin\FaqsController');
    Route::get('faqs_sort_save', ['uses' => 'Admin\FaqsController@sortorder_save', 'as' => 'faqs.sort_save']);
    // termsandpolicies Routes
    Route::post('termsandpolicies/datatable', ['uses' => 'Admin\TermsAndPoliciesController@datatable', 'as' => 'termsandpolicies.datatable']);
    Route::patch('termsandpolicies/active/{id}', ['uses' => 'Admin\TermsAndPoliciesController@active', 'as' => 'termsandpolicies.active']);
    Route::patch('termsandpolicies/inactive/{id}', ['uses' => 'Admin\TermsAndPoliciesController@inactive', 'as' => 'termsandpolicies.inactive']);
    Route::get('termsandpolicies/sort', ['uses' => 'Admin\TermsAndPoliciesController@sortorder', 'as' => 'termsandpolicies.sort']);
    Route::resource('termsandpolicies', 'Admin\TermsAndPoliciesController');
    Route::get('termsandpolicies_sort_save', ['uses' => 'Admin\TermsAndPoliciesController@sortorder_save', 'as' => 'termsandpolicies.sort_save']);
    // Feedback App Routes
    Route::post('feedbacks/datatable', ['uses' => 'Admin\FeedBackController@datatable', 'as' => 'feedbacks.datatable']);
    //Route::patch('feedbacks/active/{id}', ['uses' => 'Admin\FeedBackController@active', 'as' => 'feedbacks.active']);
    //Route::patch('feedbacks/inactive/{id}', ['uses' => 'Admin\FeedBackController@inactive', 'as' => 'feedbacks.inactive']);
    Route::get('feedbacks/sort', ['uses' => 'Admin\FeedBackController@sortorder', 'as' => 'feedbacks.sort']);
    Route::resource('feedbacks', 'Admin\FeedBackController');
    Route::get('feedbacks_sort_save', ['uses' => 'Admin\FeedBackController@sortorder_save', 'as' => 'feedbacks.sort_save']);
    // Towns Routes

    Route::get('towns/import', ['uses' => 'Admin\TownController@importTowns', 'as' => 'towns.import']);
    Route::post('towns/upload', ['uses' => 'Admin\TownController@uploadLeads', 'as' => 'towns.upload']);

    Route::post('towns/datatable', ['uses' => 'Admin\TownController@datatable', 'as' => 'towns.datatable']);
    Route::patch('towns/active/{id}', ['uses' => 'Admin\TownController@active', 'as' => 'towns.active']);
    Route::patch('towns/inactive/{id}', ['uses' => 'Admin\TownController@inactive', 'as' => 'towns.inactive']);
    Route::resource('towns', 'Admin\TownController');

    // Locations
    Route::post('locations/verify', ['uses' => 'Admin\LocationsController@verify', 'as' => 'locations.verify']);
    Route::put('locations/verify_edit', ['uses' => 'Admin\LocationsController@verify_edit', 'as' => 'locations.verify_edit']);
    Route::post('locations/datatable', ['uses' => 'Admin\LocationsController@datatable', 'as' => 'locations.datatable']);
    Route::patch('locations/active/{id}', ['uses' => 'Admin\LocationsController@active', 'as' => 'locations.active']);
    Route::patch('locations/inactive/{id}', ['uses' => 'Admin\LocationsController@inactive', 'as' => 'locations.inactive']);
    Route::get('locations/sort', ['uses' => 'Admin\LocationsController@sortorder', 'as' => 'locations.sort']);
    Route::put('locations/edit_update/{id}', ['uses' => 'Admin\LocationsController@update', 'as' => 'locations.updatelocation']);
    Route::resource('locations', 'Admin\LocationsController');
    Route::get('lcation_sort_save', ['uses' => 'Admin\LocationsController@sortorder_save', 'as' => 'locations.sort_save']);

    // Doctors
    Route::post('doctors/verify', ['uses' => 'Admin\DoctorsController@verify', 'as' => 'doctors.verify']);
    Route::post('doctors/verifyUpdate', ['uses' => 'Admin\DoctorsController@verifyUpdate', 'as' => 'doctors.verifyUpdate']);
    Route::post('doctors/datatable', ['uses' => 'Admin\DoctorsController@datatable', 'as' => 'doctors.datatable']);
    Route::patch('doctors/active/{id}', ['uses' => 'Admin\DoctorsController@active', 'as' => 'doctors.active']);
    Route::patch('doctors/inactive/{id}', ['uses' => 'Admin\DoctorsController@inactive', 'as' => 'doctors.inactive']);
    Route::get('doctors/password/{id}', ['uses' => 'Admin\DoctorsController@changePassword', 'as' => 'doctors.change_password']);
    Route::patch('doctors/password', ['uses' => 'Admin\DoctorsController@savePassword', 'as' => 'doctors.save_password']);
    Route::get('doctors/locations/{id}', ['uses' => 'Admin\DoctorsController@displaylocation', 'as' => 'doctors.location_manage']);
    Route::get('getservice', ['uses' => 'Admin\DoctorsController@getservices', 'as' => 'doctors.get_service']);
    Route::get('saveservice', ['uses' => 'Admin\DoctorsController@saveservices', 'as' => 'doctors.save_service']);
    Route::post('deleteservice', ['uses' => 'Admin\DoctorsController@deleteservices', 'as' => 'doctors.delete_service']);
    Route::resource('doctors', 'Admin\DoctorsController');

    // Appointment Statuses
    Route::post('appointment_statuses/datatable', ['uses' => 'Admin\AppointmentStatusesController@datatable', 'as' => 'appointment_statuses.datatable']);
    Route::patch('appointment_statuses/active/{id}', ['uses' => 'Admin\AppointmentStatusesController@active', 'as' => 'appointment_statuses.active']);
    Route::patch('appointment_statuses/inactive/{id}', ['uses' => 'Admin\AppointmentStatusesController@inactive', 'as' => 'appointment_statuses.inactive']);
    Route::resource('appointment_statuses', 'Admin\AppointmentStatusesController');

    // Appointments

    Route::resource('buisness-statuses', 'Admin\BuisnessStatusesController');


    //Appointment Route section for treatment invoice start
    Route::get('appointments/invoice/{id}', ['uses' => 'Admin\AppointmentsController@invoice', 'as' => 'appointments.invoicecreate']);
    Route::get('appointments/getplansinformation', ['uses' => 'Admin\AppointmentsController@getplansinformation', 'as' => 'appointments.getplansinformation']);
    Route::get('appointments/getpackageprice', ['uses' => 'Admin\AppointmentsController@getpackageprice', 'as' => 'appointments.getpackageprice']);
    Route::get('appointments/getinvoicecalculation', ['uses' => 'Admin\AppointmentsController@getinvoicecalculation', 'as' => 'appointments.getinvoicecalculation']);
    Route::get('appointments/getcalculatedPriceExclusicecheck', ['uses' => 'Admin\AppointmentsController@getcalculatedPriceExclusicecheck', 'as' => 'appointments.getcalculatedPriceExclusicecheck']);
    Route::get('appointments/saveinvoice', ['uses' => 'Admin\AppointmentsController@saveinvoice', 'as' => 'appointments.saveinvoice']);
    //Appointment Route section for treatment invoice end

    /*Appointment Excel Route Start*/
    Route::post('appointments/appointmentexcel', ['uses' => 'Admin\AppointmentsController@appointmentexcel', 'as' => 'appointments.appointmentexcel']);
    /*Appointment Excel Route End*/

    // appointments view log

    Route::get('appointments/viewlog/{id}/{type}', ['uses' => 'Admin\AppointmentsController@viewLog', 'as' => 'appointments.viewlog']);

    /*Appointment route section for consultancy invoice start*/
    Route::get('appointments/invoice-consultancy/{id}', ['uses' => 'Admin\ConsultancyInvoiceController@invoiceconsultancy', 'as' => 'appointments.invoice-create-consultancy']);
    Route::get('appointments/getconsultancycalculation', ['uses' => 'Admin\ConsultancyInvoiceController@getconsultancycalculation', 'as' => 'appointments.getconsultancycalculation']);
    Route::get('appointments/getcustomcalculation', ['uses' => 'Admin\ConsultancyInvoiceController@getcustomcalculation', 'as' => 'appointments.getcustomcalculation']);
    Route::get('appointments/checkedcustom', ['uses' => 'Admin\ConsultancyInvoiceController@checkedcustom', 'as' => 'appointments.checkedcustom']);
    Route::get('appointments/getfinalcalculation', ['uses' => 'Admin\ConsultancyInvoiceController@getfinalcalculation', 'as' => 'appointments.getfinalcalculation']);
    Route::get('appointments/saveconsultancyinvoice', ['uses' => 'Admin\ConsultancyInvoiceController@saveinvoice', 'as' => 'appointments.saveconsultancyinvoice']);
    /*Appointment route section for consultancy invoice end*/

    // Appointment Route start for images
    Route::post('appointmentsimage/datatable/{id}', ['uses' => 'Admin\AppointmentimageController@datatable', 'as' => 'appointmentsimage.datatable']);
    Route::get('appointmentsimage/imageindex/{id}', ['uses' => 'Admin\AppointmentimageController@index', 'as' => 'appointmentsimage.imageindex']);
    Route::post('appointmentsimage/imagestore_before/{id}', ['uses' => 'Admin\AppointmentimageController@imagestore_before', 'as' => 'appointmentsimage.imagestore_before']);
    Route::resource('appointmentsimage', 'Admin\AppointmentimageController');

    // Edit Service
    Route::get('appointments/{appointment}/edit-service', ['uses' => 'Admin\AppointmentsController@editService', 'as' => 'appointments.edit_service']);
    // Appointment Comments
    Route::get('appointments/comment-save', ['uses' => 'Admin\AppointmentsController@AppointmentStoreComment', 'as' => 'appointments.storecomment']);
    //Appointment Route end for images

    //Appointment route start for measurement
    Route::post('appointmentsmeasurement/datatable/{id}', ['uses' => 'Admin\AppointmentMeasurementController@datatable', 'as' => 'appointmentsmeasurement.datatable']);
    Route::get('appointmentsmeasurement/measurementindex/{id}', ['uses' => 'Admin\AppointmentMeasurementController@index', 'as' => 'appointmentsmeasurement.measurements']);
    Route::get('appointmentsmeasurement/measurementcreate/{id}', ['uses' => 'Admin\AppointmentMeasurementController@create', 'as' => 'appointmentsmeasurement.create']);
    Route::get('appointmentsmeasurement/fill_form/{id}/{appointment_id}', ['uses' => 'Admin\AppointmentMeasurementController@fill_form', 'as' => 'appointmentmeasurement.fill_form']);
    Route::post('appointmentsmeasurement/{form_id}/{appointment_id}/submit_form', ['uses' => 'Admin\AppointmentMeasurementController@submit_form', 'as' => 'appointmentmeasurement.submit_form']);
    Route::get('appointmentsmeasurement/edit/{id}', ['uses' => 'Admin\AppointmentMeasurementController@edit', 'as' => 'appointmentmeasurement.edit']);
    Route::post('appointmentsmeasurement/{custom_form_id}', ['uses' => 'Admin\AppointmentMeasurementController@update_measurement_field', 'as' => 'appointmentmeasurement.update']);
    Route::get('appointmentsmeasurement/previewform/{id}', ['uses' => 'Admin\AppointmentMeasurementController@filled_preview', 'as' => 'appointmentmeasurement.previewform']);
    Route::get('appointmentsmeasurement/{id}/print', 'Admin\AppointmentMeasurementController@filledPrint')->name("appointment_measurement_custom_form_feedbacks.filled_print");
    Route::get('appointmentsmeasurement/{id}/export_pdf', 'Admin\AppointmentMeasurementController@exportPdf')->name("appointment_measurement_custom_form_feedbacks.export_pdf");
    //Appointment route end for measurement

    /*Appointment Route start for medical history form*/
    Route::post('appointmentsmedical/datatable/{id}', ['uses' => 'Admin\AppointmentMedicalController@datatable', 'as' => 'appointmentsmedical.datatable']);
    Route::get('appointmentsmedical/medicalindex/{id}', ['uses' => 'Admin\AppointmentMedicalController@index', 'as' => 'appointmentsmedical.medicals']);
    Route::get('appointmentsmedical/medicalcreate/{id}', ['uses' => 'Admin\AppointmentMedicalController@create', 'as' => 'appointmentsmedical.create']);
    Route::get('appointmentsmedical/fill_form/{id}/{appointment_id}', ['uses' => 'Admin\AppointmentMedicalController@fill_form', 'as' => 'appointmentsmedical.fill_form']);
    Route::post('appointmentsmedical/{form_id}/{appointment_id}/submit_form', ['uses' => 'Admin\AppointmentMedicalController@submit_form', 'as' => 'appointmentsmedical.submit_form']);
    Route::get('appointmentsmedical/edit/{id}', ['uses' => 'Admin\AppointmentMedicalController@edit', 'as' => 'appointmentsmedical.edit']);
    Route::post('appointmentsmedical/{custom_form_id}', ['uses' => 'Admin\AppointmentMedicalController@update_medical_field', 'as' => 'appointmentsmedical.update']);
    Route::get('appointmentsmedical/previewform/{id}', ['uses' => 'Admin\AppointmentMedicalController@filled_preview', 'as' => 'appointmentsmedical.previewform']);

    Route::get('appointmentsmedical/{id}/print', 'Admin\AppointmentMedicalController@filledPrint')->name("appointmentsmedical.custom_form_feedbacks.filled_print");
    Route::get('appointmentsmedical/{id}/export_pdf', 'Admin\AppointmentMedicalController@exportPdf')->name("appointmentsmedical.custom_form_feedbacks.export_pdf");
    /*Appointment Route end for medical history form*/

    /*
     * Consultancy Routes
     */
    Route::post('appointments/load-locations', ['uses' => 'Admin\AppointmentsController@loadLocationsByCity', 'as' => 'appointments.load_locations']);
    Route::post('appointments/load-doctors', ['uses' => 'Admin\AppointmentsController@loadDoctorsByLocation', 'as' => 'appointments.load_doctors']);
    Route::post('appointments/load-doctor-rota', ['uses' => 'Admin\AppointmentsController@loadRotaByDoctor', 'as' => 'appointments.load_doctor_rota']);
    Route::get('appointments/load-non-scheduled-appointments', ['uses' => 'Admin\AppointmentsController@getNonScheduledAppointments', 'as' => 'appointments.load_nonscheduled_appointments']);
    Route::get('appointments/load-scheduled-appointments', ['uses' => 'Admin\AppointmentsController@getScheduledAppointments', 'as' => 'appointments.load_scheduled_appointments']);
    Route::post('appointments/check-and-save-appointment', ['uses' => 'Admin\AppointmentsController@checkAndSaveAppointments', 'as' => 'appointments.check_and_save_appointment']);

    /*
     * Services Routes
     */
    Route::post('appointments/load-node-services', ['uses' => 'Admin\AppointmentsController@loadEndServiceByBaseService', 'as' => 'appointments.load_node_service']);
    Route::get('appointments/manage-services', ['uses' => 'Admin\AppointmentsController@createService', 'as' => 'appointments.manage_services']);
    Route::post('appointments/store-service', ['uses' => 'Admin\AppointmentsController@storeService', 'as' => 'appointments.store_service']);
    Route::get('appointments/load-non-scheduled-service-appointments', ['uses' => 'Admin\AppointmentsController@getNonScheduledServiceAppointments', 'as' => 'appointments.load_nonscheduled_service_appointments']);
    Route::get('appointments/load-scheduled-serivce-appointments', ['uses' => 'Admin\AppointmentsController@getScheduledServiceAppointments', 'as' => 'appointments.load_scheduled_service_appointments']);
    Route::post('appointments/check-and-save-service-appointment', ['uses' => 'Admin\AppointmentsController@serviceSchedule', 'as' => 'appointments.check_service_schedule_and_save_appointment']);
    Route::get('appointments/get_room_resources', ['uses' => 'Admin\AppointmentsController@getRoomResources', 'as' => 'appointments.get_room_resources']);
    Route::get('appointments/get_room_resources_with_specific_date', ['uses' => 'Admin\AppointmentsController@getRoomResourcesWithDate', 'as' => 'appointments.get_room_resources_with_specific_date']);

    Route::get('appointments/test', function () {
        //        $d = \App\Models\Doctors::doctorsRota(2);
        $d = \App\Models\Resources::getRoomsWithRotasWithSpecificDate('2018-07-18')->toArray();
        dd($d);
    });

    Route::post('appointments/load-child-appointment-statuses', ['uses' => 'Admin\AppointmentsController@loadAppointmentStatuses', 'as' => 'appointments.load_child_appointment_statuses']);
    Route::post('appointments/load-child-appointment-status-data', ['uses' => 'Admin\AppointmentsController@loadAppointmentStatusData', 'as' => 'appointments.load_child_appointment_status_data']);

    Route::get('appointments/sms_logs/{id}', ['uses' => 'Admin\AppointmentsController@showSMSLogs', 'as' => 'appointments.sms_logs']);
    Route::put('appointments/send_logged_sms', ['uses' => 'Admin\AppointmentsController@sendLogSMS', 'as' => 'appointments.resend_sms']);
    Route::get('appointments/notification_logs/{id}', ['uses' => 'Admin\AppointmentsController@showNotificationLogs', 'as' => 'appointments.notification_logs']);
    Route::put('appointments/send_logged_notification', ['uses' => 'Admin\AppointmentsController@sendLogNotification', 'as' => 'appointments.resend_notification']);
    Route::get('appointments/doctors', ['uses' => 'Admin\AppointmentsController@loadDoctors', 'as' => 'appointments.doctors']);
    Route::put('appointments/save_doctor', ['uses' => 'Admin\AppointmentsController@saveDoctor', 'as' => 'appointments.save_doctor']);
    Route::get('appointments/showappointmentstatus', ['uses' => 'Admin\AppointmentsController@showAppointmentStatuses', 'as' => 'appointments.showappointmentstatus']);
    Route::put('appointments/storeappointmentstatus', ['uses' => 'Admin\AppointmentsController@storeAppointmentStatuses', 'as' => 'appointments.storeappointmentstatus']);
    Route::get('appointments/{id}/buisness-status', ['uses' => 'Admin\AppointmentsController@showBuisnessStatus', 'as' => 'appointments.showbuisnessstatus']);
    Route::post('appointments/{id}/buisness-status-store', ['uses' => 'Admin\AppointmentsController@storeBuisnessStatus', 'as' => 'appointments.storebuisnessstatus']);
    Route::get('appointments/detail/{id}', ['uses' => 'Admin\AppointmentsController@detail', 'as' => 'appointments.detail']);
    Route::patch('appointments/active/{id}', ['uses' => 'Admin\AppointmentsController@active', 'as' => 'appointments.active']);
    Route::patch('appointments/inactive/{id}', ['uses' => 'Admin\AppointmentsController@inactive', 'as' => 'appointments.inactive']);
    Route::post('appointments/loadlead', ['uses' => 'Admin\AppointmentsController@loadLeadData', 'as' => 'appointments.load_lead']);
    Route::post('appointments/datatable', ['uses' => 'Admin\AppointmentsController@datatable', 'as' => 'appointments.datatable']);
    Route::get('appointments/consulting/create', ['uses' => 'Admin\AppointmentsController@createConsultingAppointment', 'as' => 'appointments.consulting.create']);
    Route::get('appointments/treatment/create', ['uses' => 'Admin\AppointmentsController@createTreatmentAppointment', 'as' => 'appointments.treatment.create']);
    Route::get("appointments/center_machines/{location_id}", ["uses" => "Admin\AppointmentsController@center_machines", "as" => "appointments.center_machines"]);

    Route::get('appointments/displayInvoice/{id}', ['uses' => 'Admin\AppointmentsController@displayInvoiceAppointment', 'as' => 'appointments.InvoiceDisplay']);

    Route::get('appointments/checkconsultancytype', ['uses' => 'Admin\AppointmentsController@checkconsultancytype', 'as' => 'appointments.checkconsultancytype']);
    Route::resource('appointments', 'Admin\AppointmentsController');

    /*Route start for plans in appointment module*/
    Route::get('appointmentplans/{appointment_id}', ['uses' => 'Admin\AppointmentsPlansController@create', 'as' => 'appointmentplans.create']);
    /*Route end for plans in appointment module*/


    // Lead Sources
    Route::post('lead_sources/datatable', ['uses' => 'Admin\LeadSourcesController@datatable', 'as' => 'lead_sources.datatable']);
    Route::patch('lead_sources/active/{id}', ['uses' => 'Admin\LeadSourcesController@active', 'as' => 'lead_sources.active']);
    Route::patch('lead_sources/inactive/{id}', ['uses' => 'Admin\LeadSourcesController@inactive', 'as' => 'lead_sources.inactive']);
    Route::resource('lead_sources', 'Admin\LeadSourcesController');
    Route::get('lead_sources_sort', ['uses' => 'Admin\LeadSourcesController@sortorder', 'as' => 'lead_sources.sort']);
    Route::get('lead_sources_sort_save', ['uses' => 'Admin\LeadSourcesController@sortorder_save', 'as' => 'lead_sources.sort_save']);

    // Services
    Route::post('services/verify', ['uses' => 'Admin\ServicesController@verify', 'as' => 'services.verify']);
    Route::put('services/verify_edit', ['uses' => 'Admin\ServicesController@verify', 'as' => 'services.verify_edit']);
    Route::post('services/datatable', ['uses' => 'Admin\ServicesController@datatable', 'as' => 'services.datatable']);
    Route::patch('services/active/{id}', ['uses' => 'Admin\ServicesController@active', 'as' => 'services.active']);
    Route::patch('services/inactive/{id}', ['uses' => 'Admin\ServicesController@inactive', 'as' => 'services.inactive']);
    Route::resource('services', 'Admin\ServicesController');
    Route::get('services_sort', ['uses' => 'Admin\ServicesController@sortorder', 'as' => 'services.sort']);
    Route::get('services_sort_save', ['uses' => 'Admin\ServicesController@sortorder_save', 'as' => 'services.sort_save']);
    Route::get('services/statusAjax', ['uses' => 'Admin\ServicesController@statusAjax', 'as' => 'services.statusAjax']);

    // Lead Statuses
    Route::post('lead_statuses/datatable', ['uses' => 'Admin\LeadStatusesController@datatable', 'as' => 'lead_statuses.datatable']);
    Route::patch('lead_statuses/active/{id}', ['uses' => 'Admin\LeadStatusesController@active', 'as' => 'lead_statuses.active']);
    Route::patch('lead_statuses/inactive/{id}', ['uses' => 'Admin\LeadStatusesController@inactive', 'as' => 'lead_statuses.inactive']);
    Route::resource('lead_statuses', 'Admin\LeadStatusesController');
    Route::get('lead_status_sort', ['uses' => 'Admin\LeadStatusesController@sortorder', 'as' => 'lead_statuses.sort']);
    Route::get('lead_status_sort_save', ['uses' => 'Admin\LeadStatusesController@sortorder_save', 'as' => 'lead_statuses.sort_save']);

    // Leads
    Route::get('leadstatus_popup_checks', ['uses' => 'Admin\LeadsController@LeadStatusespopcheck', 'as' => 'leads.leadstatus_popup_checks']);
    Route::get('leadstatuschild_popup_checks', ['uses' => 'Admin\LeadsController@LeadStatusChildpopcheck', 'as' => 'leads.leadstatuschild_popup_checks']);

    Route::post('leads/loadlead', ['uses' => 'Admin\LeadsController@loadLeadData', 'as' => 'leads.load_lead']);
    Route::get('leads/junk', ['uses' => 'Admin\LeadsController@junk', 'as' => 'leads.junk']);
    Route::get('leads/push', ['uses' => 'Admin\LeadsController@push', 'as' => 'leads.push']);
    Route::post('leads/saveToken', ['uses' => 'Admin\LeadsController@saveToken', 'as' => 'leads.saveToken']);
    Route::post('leads/sendNotification', ['uses' => 'Admin\LeadsController@sendNotification', 'as' => 'leads.sendNotification']);
    Route::post('leads/junk_datatable', ['uses' => 'Admin\LeadsController@junkDatatable', 'as' => 'leads.junk_datatable']);
    Route::get('leads/showleadstatus', ['uses' => 'Admin\LeadsController@showLeadStatuses', 'as' => 'leads.showleadstatus']);
    Route::put('leads/storeleadstatus', ['uses' => 'Admin\LeadsController@storeLeadStatuses', 'as' => 'leads.storeleadstatus']);
    Route::patch('leads/send_sms/{id}', ['uses' => 'Admin\LeadsController@send_sms', 'as' => 'leads.send_sms']);
    Route::patch('leads/active/{id}', ['uses' => 'Admin\LeadsController@active', 'as' => 'leads.active']);
    Route::patch('leads/inactive/{id}', ['uses' => 'Admin\LeadsController@inactive', 'as' => 'leads.inactive']);
    Route::get('leads/detail/{id}', ['uses' => 'Admin\LeadsController@detail', 'as' => 'leads.detail']);
    Route::get('LeadCommentStore', ['uses' => 'Admin\LeadsController@LeadStoreComment', 'as' => 'leads.storecomment']);
    Route::get('LeadEditDetail', ['uses' => 'Admin\LeadsController@LeadEditDetailAjax', 'as' => 'leads.LeadEditDetail']);
    // Convert Lead
    Route::get('leads/convert/{id}', ['uses' => 'Admin\LeadsController@convert', 'as' => 'leads.convert']);
    //Lead Import
    Route::get('leads/import', ['uses' => 'Admin\LeadsController@importLeads', 'as' => 'leads.import']);
    Route::post('leads/upload', ['uses' => 'Admin\LeadsController@uploadLeads', 'as' => 'leads.upload']);
    Route::post('leads/datatable', ['uses' => 'Admin\LeadsController@datatable', 'as' => 'leads.datatable']);
    Route::resource('leads', 'Admin\LeadsController');
    Route::post('leads/comment_store', ['uses' => 'Admin\LeadsController@comment_store', 'as' => 'leads.comment_store']);
    // Load and Save Lead Statuses
    Route::get('leads_lead_statuses', ['uses' => 'Admin\LeadsController@loadLeadStatuses', 'as' => 'leads.lead_statuses']);
    Route::put('leads_save_status', ['uses' => 'Admin\LeadsController@saveLeadStatus', 'as' => 'leads.save_status']);
    // Load and Save Treatments
    Route::get('leads_treatments', ['uses' => 'Admin\LeadsController@loadTreatments', 'as' => 'leads.treatments']);
    Route::put('leads_save_treatment', ['uses' => 'Admin\LeadsController@saveTreatment', 'as' => 'leads.save_treatment']);
    // Load and Save Lead Sources
    Route::get('leads_lead_sources', ['uses' => 'Admin\LeadsController@loadLeadSources', 'as' => 'leads.lead_sources']);
    Route::put('leads_save_source', ['uses' => 'Admin\LeadsController@saveLeadSource', 'as' => 'leads.save_source']);
    // Load and Save Cities
    Route::get('leads_cities', ['uses' => 'Admin\LeadsController@loadCities', 'as' => 'leads.cities']);
    Route::put('leads_save_city', ['uses' => 'Admin\LeadsController@saveCity', 'as' => 'leads.save_city']);
    Route::get('lead_Create_popup', ['uses' => 'Admin\LeadsController@make_pop', 'as' => 'leads.create_popup']);

    // Settings
    Route::post('settings/datatable', ['uses' => 'Admin\SettingsController@datatable', 'as' => 'settings.datatable']);
    Route::patch('settings/active/{id}', ['uses' => 'Admin\SettingsController@active', 'as' => 'settings.active']);
    Route::patch('settings/inactive/{id}', ['uses' => 'Admin\SettingsController@inactive', 'as' => 'settings.inactive']);
    Route::resource('settings', 'Admin\SettingsController');

    // SMS Templates
    Route::post('sms_templates/datatable', ['uses' => 'Admin\SMSTemplatesController@datatable', 'as' => 'sms_templates.datatable']);
    Route::patch('sms_templates/active/{id}', ['uses' => 'Admin\SMSTemplatesController@active', 'as' => 'sms_templates.active']);
    Route::patch('sms_templates/inactive/{id}', ['uses' => 'Admin\SMSTemplatesController@inactive', 'as' => 'sms_templates.inactive']);
    Route::resource('sms_templates', 'Admin\SMSTemplatesController');

    // Notification Templates
    Route::post('notification_templates/datatable', ['uses' => 'Admin\NotificationTemplatesController@datatable', 'as' => 'notification_templates.datatable']);
    Route::patch('notification_templates/active/{id}', ['uses' => 'Admin\NotificationTemplatesController@active', 'as' => 'notification_templates.active']);
    Route::patch('notification_templates/inactive/{id}', ['uses' => 'Admin\NotificationTemplatesController@inactive', 'as' => 'notification_templates.inactive']);
    Route::get('notification_templates/publish/{promoid}', ['uses' => 'Admin\NotificationTemplatesController@sendPromoNotification', 'as' => 'notification_templates.publish']);
    Route::resource('notification_templates', 'Admin\NotificationTemplatesController');

    // Cancellation Reasons
    Route::post('cancellation_reasons/datatable', ['uses' => 'Admin\CancellationReasonsController@datatable', 'as' => 'cancellation_reasons.datatable']);
    Route::patch('cancellation_reasons/active/{id}', ['uses' => 'Admin\CancellationReasonsController@active', 'as' => 'cancellation_reasons.active']);
    Route::patch('cancellation_reasons/inactive/{id}', ['uses' => 'Admin\CancellationReasonsController@inactive', 'as' => 'cancellation_reasons.inactive']);
    Route::resource('cancellation_reasons', 'Admin\CancellationReasonsController');
    Route::get('cancellation_reasons_sort', ['uses' => 'Admin\CancellationReasonsController@sortorder', 'as' => 'cancellation_reasons.sort']);
    Route::get('cancellation_reasons_sort_save', ['uses' => 'Admin\CancellationReasonsController@sortorder_save', 'as' => 'cancellation_reasons.sort_save']);

    //user Type route start
    Route::post('user_types/datatable', ['uses' => 'Admin\UserTypesController@datatable', 'as' => 'user_types.datatable']);
    Route::patch('user_types/active/{id}', ['uses' => 'Admin\UserTypesController@active', 'as' => 'user_types.active']);
    Route::patch('user_types/inactive/{id}', ['uses' => 'Admin\UserTypesController@inactive', 'as' => 'user_types.inactive']);
    Route::resource('user_types', 'Admin\UserTypesController');
    //user type route end

    //Resource Type Route start
    Route::post('resource_types/datatable', ['uses' => 'Admin\ResourceTypesController@datatable', 'as' => 'resource_types.datatable']);
    Route::patch('resource_types/active/{id}', ['uses' => 'Admin\ResourceTypesController@active', 'as' => 'resource_types.active']);
    Route::patch('resource_types/inactive/{id}', ['uses' => 'Admin\ResourceTypesController@inactive', 'as' => 'resource_types.inactive']);
    Route::resource('resource_types', 'Admin\ResourceTypesController');
    //Resource Type Route End

    //Resource Route start
    Route::post('resources/datatable', ['uses' => 'Admin\ResourcesController@datatable', 'as' => 'resources.datatable']);
    Route::patch('resources/active/{id}', ['uses' => 'Admin\ResourcesController@active', 'as' => 'resources.active']);
    Route::patch('resources/inactive/{id}', ['uses' => 'Admin\ResourcesController@inactive', 'as' => 'resources.inactive']);
    Route::get('resources/get_machinetype', ['uses' => 'Admin\ResourcesController@get_machinetype', 'as' => 'resources.get_machinetype']);
    Route::resource('resources', 'Admin\ResourcesController');
    //Resource Route end

    //Rourece Rota Management
    Route::get('resourcerotas/load_location', ['uses' => 'Admin\ResourceRotasController@load_location', 'as' => 'resourcerotas.load_location']);
    Route::get('resourcerotas/load_doctor_and_Machine', ['uses' => 'Admin\ResourceRotasController@load_doctor_and_Machine', 'as' => 'resourcerotas.load_doctor_and_Machine']);
    Route::post('resourcerotas/datatable', ['uses' => 'Admin\ResourceRotasController@datatable', 'as' => 'resourcerotas.datatable']);
    Route::patch('resourcerotas/active/{id}', ['uses' => 'Admin\ResourceRotasController@active', 'as' => 'resourcerotas.active']);
    Route::patch('resourcerotas/inactive/{id}', ['uses' => 'Admin\ResourceRotasController@inactive', 'as' => 'resourcerotas.inactive']);
    Route::get('resourcerotas/calender/{id}', ['uses' => 'Admin\ResourceRotasController@getcalenderinfo', 'as' => 'resourcerotas.calender']);
    Route::get('resourcerotas/calender/events/{id}', ['uses' => 'Admin\ResourceRotasController@getcalenderinfoevents', 'as' => 'resourcerotas.events']);
    Route::post('resourcerotas/store_Calender_edit', ['uses' => 'Admin\ResourceRotasController@store_calender_edit', 'as' => 'resourcerotas.store_Calender_edit']);
    Route::resource('resourcerotas', 'Admin\ResourceRotasController');

    //Discount route Start
    Route::post('discounts/datatable', ['uses' => 'Admin\DiscountsController@datatable', 'as' => 'discounts.datatable']);
    Route::patch('discounts/active/{id}', ['uses' => 'Admin\DiscountsController@active', 'as' => 'discounts.active']);
    Route::patch('discounts/inactive/{id}', ['uses' => 'Admin\DiscountsController@inactive', 'as' => 'discounts.inactive']);
    Route::get('discounts/locations/{id}', ['uses' => 'Admin\DiscountsController@displayDlocation', 'as' => 'discounts.location_manage']);
    Route::get('discounts/approval/{id}', ['uses' => 'Admin\DiscountsController@displayApproval', 'as' => 'discounts.approval']);
    Route::get('getDservice', ['uses' => 'Admin\DiscountsController@getDservices', 'as' => 'discounts.get_Dservice']);
    Route::post('saveDervice', ['uses' => 'Admin\DiscountsController@saveDservices', 'as' => 'discounts.save_Dervice']);
    Route::post('saveApproval', ['uses' => 'Admin\DiscountsController@saveApproval', 'as' => 'discounts.save_approval']);
    Route::post('deleteDservice', ['uses' => 'Admin\DiscountsController@deleteDservice', 'as' => 'discounts.delete_service']);
    Route::post('deleteApproval', ['uses' => 'Admin\DiscountsController@deleteApproval', 'as' => 'discounts.delete_approval']);

    Route::resource('discounts', 'Admin\DiscountsController');
    //Discount route end

    // PaymentModes Routes start
    Route::post('payment_modes/datatable', ['uses' => 'Admin\PaymentModesController@datatable', 'as' => 'payment_modes.datatable']);
    Route::patch('payment_modes/active/{id}', ['uses' => 'Admin\PaymentModesController@active', 'as' => 'payment_modes.active']);
    Route::patch('payment_modes/inactive/{id}', ['uses' => 'Admin\PaymentModesController@inactive', 'as' => 'payment_modes.inactive']);
    Route::resource('payment_modes', 'Admin\PaymentModesController');
    Route::get('payment_modes_sort', ['uses' => 'Admin\PaymentModesController@sortorder', 'as' => 'payment_modes.sort']);
    Route::get('payment_modes_sort_save', ['uses' => 'Admin\PaymentModesController@sortorder_save', 'as' => 'payment_modes.sort_save']);
    // PaymentModes Routes end

    //Logs Routes
    Route::post('logs/datatable', ['uses' => 'Admin\LogsController@datatable', 'as' => 'logs.datatable']);
    Route::resource('logs', 'Admin\LogsController');

    //Invoice Scan Logs
    Route::get('invoice_scan_logs', ['uses' => 'Admin\InvoiceScanLogsController@index', 'as' => 'invoice_scan_logs']);
    Route::post('invoice/scan/logs/datatable', ['uses' => 'Admin\InvoiceScanLogsController@datatable', 'as' => 'invoice_scan_logs.datatable']);

    //User Login Logs Routes
    Route::post('user_login_logs/datatable', ['uses' => 'Admin\UserLoginLogsController@datatable', 'as' => 'user_login_logs.datatable']);
    Route::resource('user_login_logs', 'Admin\UserLoginLogsController');

    // User Operator Settings
    Route::post('useroperatorsettings/datatable', ['uses' => 'Admin\UserOperatorSettingsController@datatable', 'as' => 'user_operator_settings.datatable']);
    Route::post('user_operator_settings/loadOperator', ['uses' => 'Admin\UserOperatorSettingsController@loadOperator', 'as' => 'user_operator_settings.load_operator']);
    Route::resource('user_operator_settings', 'Admin\UserOperatorSettingsController');
    // Custom User Form Routes
    Route::post('custom_forms/datatable', ['uses' => 'Admin\CustomFormsController@datatable', 'as' => 'custom_forms.datatable']);
    Route::patch('custom_forms/active/{id}', ['uses' => 'Admin\CustomFormsController@active', 'as' => 'custom_forms.active']);
    Route::patch('custom_forms/inactive/{id}', ['uses' => 'Admin\CustomFormsController@inactive', 'as' => 'custom_forms.inactive']);
    Route::resource('custom_forms', 'Admin\CustomFormsController');
    Route::post('custom_forms_mass_destroy', ['uses' => 'Admin\CustomFormsController@massDestroy', 'as' => 'custom_forms.mass_destroy']);
    Route::post('custom_forms/form_update/{id}', ['uses' => 'Admin\CustomFormsController@form_update', 'as' => 'custom_forms.form_update']);
    Route::post('custom_forms/{form_id}/update_field/{field_id}', ['uses' => 'Admin\CustomFormsController@update_field', 'as' => 'custom_forms.update_field']);
    Route::post('custom_forms/{id}/create_field/', ['uses' => 'Admin\CustomFormsController@create_field', 'as' => 'custom_forms.create_field']);
    Route::get('custom_forms/{id}/sort_fields/', ['uses' => 'Admin\CustomFormsController@sort_fields', 'as' => 'custom_forms.sort_fields']);
    Route::post('custom_forms/{form_id}/delete_field/{field_id}', ['uses' => 'Admin\CustomFormsController@delete_field', 'as' => 'custom_forms.delete_field']);
    Route::get('custom_forms_sort', ['uses' => 'Admin\CustomFormsController@sortorder', 'as' => 'custom_forms.sort']);
    Route::get('custom_forms_sort_save', ['uses' => 'Admin\CustomFormsController@sortorder_save', 'as' => 'custom_forms.sort_save']);
    Route::get('custom_forms_test', function () {
        $service_duration = "00:30";
        $appointmentData['scheduled_date'] = Carbon::parse("2018-08-06T13:45:00")->format("Y-m-d");
        $appointmentData['scheduled_time'] = Carbon::parse("2018-08-06T13:45:00")->format("H:i:s");
        $start = "2018-08-06T13:45:00";
        var_dump(Carbon::parse($start)->format("Y-m-d H:i:s"));
        $duraton_array = explode(":", $service_duration);
        $end = Carbon::parse($start)->addHour($service_duration[0])->addMinute($duraton_array[1])->format("Y-m-d H:i:s");
        dd($end);
    });
    // Custom User Form Feedbacks Routes
    Route::post('custom_form_feedbacks/datatable', ['uses' => 'Admin\CustomFormFeedbacksController@datatable', 'as' => 'custom_form_feedbacks.datatable']);
    Route::patch('custom_form_feedbacks/active/{id}', ['uses' => 'Admin\CustomFormFeedbacksController@active', 'as' => 'custom_form_feedbacks.active']);
    Route::patch('custom_form_feedbacks/inactive/{id}', ['uses' => 'Admin\CustomFormFeedbacksController@inactive', 'as' => 'custom_form_feedbacks.inactive']);
    Route::resource('custom_form_feedbacks', 'Admin\CustomFormFeedbacksController');
    Route::get('custom_form_feedbacks/{form_id}/fill_form', 'Admin\CustomFormFeedbacksController@fill_form')->name("custom_form_feedbacks.fill_form");
    Route::get('custom_form_feedbacks/{form_id}/preview_form', 'Admin\CustomFormFeedbacksController@preview_form')->name("custom_form_feedbacks.preview_form");
    Route::get('custom_form_feedbacks/{id}/preview', 'Admin\CustomFormFeedbacksController@filled_preview')->name("custom_form_feedbacks.filled_preview");
    Route::get('custom_form_feedbacks/{id}/print', 'Admin\CustomFormFeedbacksController@filledPrint')->name("custom_form_feedbacks.filled_print");
    Route::get('custom_form_feedbacks/{id}/export_pdf', 'Admin\CustomFormFeedbacksController@exportPdf')->name("custom_form_feedbacks.export_pdf");

    Route::post('custom_form_feedbacks/{form_id}/submit_form', ['uses' => 'Admin\CustomFormFeedbacksController@submit_form', 'as' => 'custom_form_feedbacks.submit_form']);
    Route::post('custom_form_feedbacks/{feedback_id}/update_field/{feedback_field_id}', ['uses' => 'Admin\CustomFormFeedbacksController@update_field', 'as' => 'custom_form_feedbacks.update_field']);

    // Package route start
    Route::post('packages/datatable', ['uses' => 'Admin\PackagesController@datatable', 'as' => 'packages.datatable']);
    Route::patch('packages/active/{id}', ['uses' => 'Admin\PackagesController@active', 'as' => 'packages.active']);
    Route::patch('packages/inactive/{id}', ['uses' => 'Admin\PackagesController@inactive', 'as' => 'packages.inactive']);
    Route::get('packages/getdiscountinfo', ['uses' => 'Admin\PackagesController@getdiscountinfo', 'as' => 'packages.getdiscountinfo']);

    Route::get('packages/getdiscountinfo_custom', ['uses' => 'Admin\PackagesController@getdiscountinfocustom', 'as' => 'packages.getdiscountinfo_custom']);
    Route::get('packages/getdiscountinfo_periodic', ['uses' => 'Admin\PackagesController@getdiscountinfoperiodic', 'as' => 'packages.getdiscountinfo_periodic']);

    Route::get('packages/savepackagesservice', ['uses' => 'Admin\PackagesController@savepackages_service', 'as' => 'packages.savepackages_service']);
    Route::post('packages/deletepackagesservice', ['uses' => 'Admin\PackagesController@deletepackagesservice', 'as' => 'packages.deletepackages_service']);
    Route::get('packages/deletepackagesexclusive', ['uses' => 'Admin\PackagesController@deletepackagesexclusive', 'as' => 'packages.deletepackages_exclusive']);
    Route::get('packages/getgrandtotal', ['uses' => 'Admin\PackagesController@getgrandtotal', 'as' => 'packages.getgrandtotal']);
    Route::get('packages/getgrandtotal_update', ['uses' => 'Admin\PackagesController@getgrandtotal_update', 'as' => 'packages.getgrandtotal_update']);
    Route::get('packages/savepackages', ['uses' => 'Admin\PackagesController@savepackages', 'as' => 'packages.savepackages']);
    Route::get('packages/updatepackages', ['uses' => 'Admin\PackagesController@updatepackages', 'as' => 'packages.updatepackages']);
    Route::get('packages/getserviceinfo', ['uses' => 'Admin\PackagesController@getserviceinfo', 'as' => 'packages.getserviceinfo']);
    Route::get('packages/display/{id}', ['uses' => 'Admin\PackagesController@display', 'as' => 'packages.display']);
    Route::get('packages/getservice', ['uses' => 'Admin\PackagesController@getservices', 'as' => 'packages.getservice']);
    Route::get('packages/getservice_for_discount_zero', ['uses' => 'Admin\PackagesController@getservices_for_zero', 'as' => 'packages.getserviceinfo_discount_zero']);
    Route::get('packages/pdf/{id}', ['uses' => 'Admin\PackagesController@package_pdf', 'as' => 'packages.package_pdf']);
    Route::get('packages/getpackage', ['uses' => 'Admin\PackagesController@getpackage', 'as' => 'packages.getpackage']);
    Route::get('packages/getpackageselling', ['uses' => 'Admin\PackagesController@getpackageselling', 'as' => 'packages.getpackageselling']);
    Route::get('packages/getdiscountgroup', ['uses' => 'Admin\PackagesController@getdiscountgroup', 'as' => 'packages.getdiscountgroup']);

    /*Routes for editing the cash in treatment plan*/
    Route::get('packages/edit_cash/{id}/{package_id}', ['uses' => 'Admin\PackagesController@editpackageadvancescashindex', 'as' => 'packages.edit_cash']);
    Route::post('packages/delete_cash', ['uses' => 'Admin\PackagesController@deletepackageadvancescash', 'as' => 'packages.delete_cash']);
    Route::put('packages/edit_cash/store', ['uses' => 'Admin\PackagesController@storepackageadvancescash', 'as' => 'packages.edit_cash.store']);
    /*End*/

    Route::get('packages/getappointmentinfo', ['uses' => 'Admin\PackagesController@getappointmentinfo', 'as' => 'packages.getappointmentinfo']);
    Route::resource('packages', 'Admin\PackagesController');
    // Package Route end

    // Package Route for log
    Route::get('plans/log/{id}/{type}', ['uses' => 'Admin\PackagesController@packagelog', 'as' => 'packages.log']);
    //end

    // Route for Sms log start
    Route::get('packages/sms_logs/{id}', ['uses' => 'Admin\PackagesController@showSMSLogs', 'as' => 'packages.sms_logs']);
    Route::post('packages/send_logged_sms', ['uses' => 'Admin\PackagesController@sendLogSMS', 'as' => 'packages.resend_sms']);
    // End

    //Package Advance route start
    Route::post('packagesadvances/datatable', ['uses' => 'Admin\PackageAdvancesController@datatable', 'as' => 'packagesadvances.datatable']);
    Route::patch('packagesadvances/active/{id}', ['uses' => 'Admin\PackageAdvancesController@active', 'as' => 'packagesadvances.active']);
    Route::patch('packagesadvances/inactive/{id}', ['uses' => 'Admin\PackageAdvancesController@inactive', 'as' => 'packagesadvances.inactive']);
    Route::post('packagesadvances/cancel/{id}', ['uses' => 'Admin\PackageAdvancesController@cancel', 'as' => 'packagesadvances.cancel']);
    Route::get('packagesadvances/getpackages', ['uses' => 'Admin\PackageAdvancesController@getpackages', 'as' => 'packagesadvances.getpackages']);
    Route::get('packagesadvances/getpackagesinfo', ['uses' => 'Admin\PackageAdvancesController@getpackagesinfo', 'as' => 'packagesadvances.getpackagesinfo']);
    Route::get('packagesadvances/getpackagesinfo_update', ['uses' => 'Admin\PackageAdvancesController@getpackagesinfo_update', 'as' => 'packagesadvances.getpackagesinfo_update']);
    Route::get('packagesadvances/savepackagesadvances', ['uses' => 'Admin\PackageAdvancesController@savepackagesadvances', 'as' => 'packagesadvances.savepackagesadvances']);
    Route::get('packagesadvances/updatepackagesadvances', ['uses' => 'Admin\PackageAdvancesController@updatepackagesadvances', 'as' => 'packagesadvances.updatepackagesadvances']);
    Route::get('packagesadvances/update_record_final', ['uses' => 'Admin\PackageAdvancesController@update_record_final', 'as' => 'packagesadvances.update_record_final']);
    Route::resource('packagesadvances', 'Admin\PackageAdvancesController');
    //Package advance route end

    //Invoice Management route start
    Route::post('invoices/datatable', ['uses' => 'Admin\InvoicesController@datatable', 'as' => 'invoices.datatable']);
    Route::post('invoices/cancel/{id}', ['uses' => 'Admin\InvoicesController@cancel', 'as' => 'invoices.cancel']);
    Route::get('invoices/displayInvoice/{id}', ['uses' => 'Admin\InvoicesController@displayInvoice', 'as' => 'invoices.displayInvoice']);
    Route::get('invoices/pdf/{id}', ['uses' => 'Admin\InvoicesController@invoice_pdf', 'as' => 'invoices.invoice_pdf']);
    Route::get('invoices/log/{id}/{type}', ['uses' => 'Admin\InvoicesController@invoicelog', 'as' => 'invoices.invoice_log']);

    Route::get('invoices/sms_logs/{id}', ['uses' => 'Admin\InvoicesController@showSMSLogs', 'as' => 'invoices.sms_logs']);
    Route::post('invoices/send_logged_sms', ['uses' => 'Admin\InvoicesController@sendLogSMS', 'as' => 'invoices.resend_sms']);

    Route::resource('invoices', 'Admin\InvoicesController');
    //Invoice Management route end

    //Refunds route start
    Route::post('refunds/datatable', ['uses' => 'Admin\RefundsController@datatable', 'as' => 'refunds.datatable']);
    Route::get('refunds/refund_create/{id}', ['uses' => 'Admin\RefundsController@refund_create', 'as' => 'refunds.refund_create']);
    Route::get('refunds/detail/{id}', ['uses' => 'Admin\RefundsController@detail', 'as' => 'refunds.detail']);
    Route::resource('refunds', 'Admin\RefundsController');
    //Refunds route end

    //Non Refunds Route start
    Route::post('nonplansrefunds/datatable', ['uses' => 'Admin\RefundsController@nonplansdatatable', 'as' => 'nonplansrefunds.datatable']);
    Route::get('nonplansrefunds/refund_create/{id}', ['uses' => 'Admin\RefundsController@nonplans_refund_create', 'as' => 'nonprefunds.refund_create']);
    Route::post('nonplansrefunds/store', ['uses' => 'Admin\RefundsController@nonplans_refund_store', 'as' => 'nonplansrefunds.store']);
    Route::get('nonplansrefunds/index', ['uses' => 'Admin\RefundsController@nonplansindex', 'as' => 'nonplansrefunds.index']);
    //Non Refunds Route end


    // Patients routes start
    Route::post('patients/datatable', ['uses' => 'Admin\PatientsController@datatable', 'as' => 'patients.datatable']);
    Route::patch('patients/active/{id}', ['uses' => 'Admin\PatientsController@active', 'as' => 'patients.active']);
    Route::patch('patients/inactive/{id}', ['uses' => 'Admin\PatientsController@inactive', 'as' => 'patients.inactive']);
    Route::get('patients/{id}/preview', 'Admin\PatientsController@preview')->name("patients.preview");
    Route::get('patients/{id}/leads', 'Admin\PatientsController@leads')->name("patients.leads");
    Route::post('patients/{id}/leads-datatable', ['uses' => 'Admin\PatientsController@leadsDatatable', 'as' => 'patients.leadsDatatable']);
    Route::get('patients/{id}/appointments', 'Admin\PatientsController@appointments')->name("patients.appointments");
    Route::post('patients/{id}/appointments-datatable', ['uses' => 'Admin\PatientsController@appointmentsDatatable', 'as' => 'patients.appointmentsDatatable']);
    Route::get('patients/{id}/image', 'Admin\PatientsController@imageindex')->name("patients.imageurl");
    Route::post('patients/image', 'Admin\PatientsController@imagestore')->name("patients.storeimage");
    Route::get('patients/{id}/document', 'Admin\PatientsController@documentindex')->name("patients.document");
    Route::get('patients/createdocument/{id}', 'Admin\PatientsController@documentCreate')->name("patients.createdocument");
    Route::post('patients/storedocument', 'Admin\PatientsController@documentstore')->name("patients.storedocument");
    Route::post('patients/documentdatatable/{id}', ['uses' => 'Admin\PatientsController@documentdatatable', 'as' => 'patients.documentdatatable']);
    Route::get('patients/edit/{id}', ['uses' => 'Admin\PatientsController@documentedit', 'as' => 'patients.documentedit']);
    Route::post('patients/updatedocuments/{id}', ['uses' => 'Admin\PatientsController@documentupdate', 'as' => 'patients.updatedocuments']);
    Route::post('patients/deletedocuments/{id}', ['uses' => 'Admin\PatientsController@documentdelete', 'as' => 'patients.documentsdestroy']);
    Route::resource('patients', 'Admin\PatientsController');

    /*Route start for patient pakcage*/
    Route::post('plans/datatable/{id}', ['uses' => 'Admin\Patients\PackagesController@datatable', 'as' => 'plans.datatable']);
    Route::get('plans/getserviceinfo', ['uses' => 'Admin\Patients\PackagesController@getserviceinfo', 'as' => 'plans.getserviceinfo']);
    Route::get('plans/getdiscountinfo', ['uses' => 'Admin\Patients\PackagesController@getdiscountinfo', 'as' => 'plans.getdiscountinfo']);
    Route::get('plans/savepackagesservice', ['uses' => 'Admin\Patients\PackagesController@savepackages_service', 'as' => 'plans.savepackages_service']);
    Route::get('plans/savepackages', ['uses' => 'Admin\Patients\PackagesController@savepackages', 'as' => 'plans.savepackages']);
    Route::get('plans/getdiscountinfo_custom', ['uses' => 'Admin\Patients\PackagesController@getdiscountinfocustom', 'as' => 'plans.getdiscountinfo_custom']);
    Route::get('plans/getgrandtotal', ['uses' => 'Admin\Patients\PackagesController@getgrandtotal', 'as' => 'plans.getgrandtotal']);
    Route::post('plans/deletepackagesservice', ['uses' => 'Admin\Patients\PackagesController@deletepackagesservice', 'as' => 'plans.deletepackages_service']);
    Route::get('plans/updatepackages', ['uses' => 'Admin\Patients\PackagesController@updatepackages', 'as' => 'plans.updatepackages']);
    Route::get('plans/getgrandtotal_update', ['uses' => 'Admin\Patients\PackagesController@getgrandtotal_update', 'as' => 'plans.getgrandtotal_update']);
    Route::post('plans/active/{id}', ['uses' => 'Admin\Patients\PackagesController@active', 'as' => 'plans.active']);
    Route::post('plans/inactive/{id}', ['uses' => 'Admin\Patients\PackagesController@inactive', 'as' => 'plans.inactive']);
    Route::post('plans/destroy/{id}', ['uses' => 'Admin\Patients\PackagesController@destroy', 'as' => 'plans.destroy']);
    Route::get('plans/display/{id}', ['uses' => 'Admin\Patients\PackagesController@display', 'as' => 'plans.display']);
    Route::get('plans/edit/{id}', ['uses' => 'Admin\Patients\PackagesController@edit', 'as' => 'plans.edit']);
    Route::get('plans/{id}', ['uses' => 'Admin\Patients\PackagesController@index', 'as' => 'plans.index']);
    Route::get('plans/{id}/createplan', ['uses' => 'Admin\Patients\PackagesController@create', 'as' => 'plans.createplan']);
    Route::get('plans/log/{id}/{patient_id}/{type}', ['uses' => 'Admin\Patients\PackagesController@planlog', 'as' => 'plans.log']);

    Route::get('plans/edit_cash/{id}/{package_id}', ['uses' => 'Admin\Patients\PackagesController@editpackageadvancescashindex', 'as' => 'plans.edit_cash']);

    /*Route end for patient package*/

    /*Route start for patient Package advances*/
    Route::post('finances/datatable/&{id}', ['uses' => 'Admin\Patients\PackageAdvancesController@datatable', 'as' => 'finances.datatable']);
    Route::get('finances/savepackagesadvances', ['uses' => 'Admin\Patients\PackageAdvancesController@savepackagesadvances', 'as' => 'finances.savepackagesadvances']);
    Route::get('finances/getpackagesinfo', ['uses' => 'Admin\Patients\PackageAdvancesController@getpackagesinfo', 'as' => 'finances.getpackagesinfo']);
    Route::get('finances/getpackages', ['uses' => 'Admin\Patients\PackageAdvancesController@getpackages', 'as' => 'finances.getpackages']);

    Route::get('finances/{id}', ['uses' => 'Admin\Patients\PackageAdvancesController@index', 'as' => 'finances.index']);
    Route::get('finances/{id}/create', ['uses' => 'Admin\Patients\PackageAdvancesController@create', 'as' => 'finances.create']);
    /*Route end for patient package advances */

    /*Route start for Patient refunds*/
    Route::post('refundpatient/datatable/&{id}', ['uses' => 'Admin\Patients\RefundsController@datatable', 'as' => 'refundpatient.datatable']);
    Route::get('refundpatient/refund_create/{id}', ['uses' => 'Admin\Patients\RefundsController@refund_create', 'as' => 'refundpatient.refund_create']);
    Route::post('refundpatient/store', ['uses' => 'Admin\Patients\RefundsController@store', 'as' => 'refundpatient.store']);
    Route::get('refundpatient/detail/{id}', ['uses' => 'Admin\Patients\RefundsController@detail', 'as' => 'refundpatient.detail']);

    Route::get('refundpatient/{id}', ['uses' => 'Admin\Patients\RefundsController@index', 'as' => 'refundpatient.index']);
    Route::get('refundpatient/{id}/create', ['uses' => 'Admin\Patients\RefundsController@create', 'as' => 'refundpatient.create']);
    /*Route end for patient refunds*/

    /*Route start for patient non plans refunds*/
    Route::post('nonplansrefundspatient/datatable/{id}', ['uses' => 'Admin\Patients\RefundsController@nonplansdatatable', 'as' => 'nonplansrefundpatient.datatable']);
    Route::get('nonplansrefundspatient/{id}', ['uses' => 'Admin\Patients\RefundsController@nonplansrefundsindex', 'as' => 'nonplansrefundpatient.index']);
    Route::get('nonplansrefundspatient/refund_create/{id}', ['uses' => 'Admin\Patients\RefundsController@nonplansrefundscreate', 'as' => 'nonplansrefundpatient.refund_create']);
    Route::post('nonplansrefundspatient/store', ['uses' => 'Admin\Patients\RefundsController@nonplansrefundsstore', 'as' => 'nonplansrefundpatient.store']);
    /*Route end for patient non plans refunds*/


    /*Route start for patient invoices*/
    Route::post('invoicepatient/datatable/&{id}', ['uses' => 'Admin\Patients\InvoicesController@datatable', 'as' => 'invoicepatient.datatable']);
    Route::post('invoicepatient/cancel/{id}', ['uses' => 'Admin\Patients\InvoicesController@cancel', 'as' => 'invoicepatient.cancel']);
    Route::get('invoicepatient/displayInvoice/{id}', ['uses' => 'Admin\Patients\InvoicesController@displayInvoice', 'as' => 'invoicepatient.displayInvoice']);
    Route::get('invoicepatient/pdf/{id}', ['uses' => 'Admin\Patients\InvoicesController@invoice_pdf', 'as' => 'invoicepatient.invoice_pdf']);
    Route::get('invoicepatient/log/{id}/{patient_id}/{type}', ['uses' => 'Admin\Patients\InvoicesController@invoicelog', 'as' => 'invoicepatient.invoice_log']);
    Route::get('invoicepatient/{id}', ['uses' => 'Admin\Patients\InvoicesController@index', 'as' => 'invoicepatient.index']);
    /*Route end for patient invoices*/

    /*Route start for patient Pre define Custome Forms*/
    Route::post('customformfeedbackspatient/datatable/&{id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@datatable', 'as' => 'customformfeedbackspatient.datatable']);
    Route::get('customformfeedbackspatient/editcustomform/{id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@edit', 'as' => 'customformfeedbackspatient.edit']);
    Route::get('customformfeedbackspatient/previewcustomform/{id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@filled_preview', 'as' => 'customformfeedbackspatient.previewform']);
    Route::get('customformfeedbackspatient/addnewform/{id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@AddNewForm', 'as' => 'customformfeedbackspatient.addnew']);
    Route::get('customformfeedbackspatient/fill_form/{id}/{patient_id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@fill_form', 'as' => 'customformfeedbackspatient.fill_form']);
    Route::get('customformfeedbackspatient/{id}', ['uses' => 'Admin\Patients\CustomFormFeedbacksController@index', 'as' => 'customformfeedbackspatient.index']);
    Route::get('customformfeedbackspatient/{id}/print', 'Admin\CustomFormFeedbacksController@filledPrint')->name("patient_custom_form_feedbacks.filled_print");
    Route::get('customformfeedbackspatient/{id}/export_pdf', 'Admin\CustomFormFeedbacksController@exportPdf')->name("patient_custom_form_feedbacks.export_pdf");
    /*Route End for patient pre define Custome Forms*/
    /*Route Start for patient card measurement*/
    Route::resource('card-subscription', 'Admin\PatientCardController');
    Route::get('cardsubscription/export', 'Admin\PatientCardController@export')->name('card-subscription.export');
    Route::get('purchased_serivces', ['uses' => 'Admin\PatientCardController@purchased_serivces']);

    // Route::get('users/index', 'PatientCardController@index')->name('users.index');
    // Route::post('users/index', ['uses' => 'Admin\PatientCardController@index', 'as' => 'users.index']);
    // Route::put('/card-subscription/{id}', [PatientCardController::class, 'update'])->name('admin.card-subscription.update');
    // Route::post('/card-subscription/{id}', 'Admin\PatientCardController@update')->name('admin.card-subscription.update');
    Route::resource('subscription-charges', 'Admin\SubscriptionChargeController');
    /*Route End for patient card measurement */
    // Patients routes end

    //Route start for lead report
    Route::get('leadreports/leads_reports', ['uses' => 'Admin\Reports\LeadsReportController@lead', 'as' => 'leads.leads_reports']);
    Route::post('leadreports/leads_reports_load', ['uses' => 'Admin\Reports\LeadsReportController@lead_report', 'as' => 'leads.leads_reports_load']);
    //Route end for lead report

    //Route start for center reports
    Route::get('centrereports/centers_reports', ['uses' => 'Admin\Reports\CentersReportController@center', 'as' => 'centers.centers_reports']);
    Route::post('centrereports/centers_reports_load', ['uses' => 'Admin\Reports\CentersReportController@reportLoad', 'as' => 'centers.centers_reports_load']);
    //Route end for center reports

    //Route start for appointment report
    Route::get('appointmentreports/appointments-general', ['uses' => 'Admin\Reports\AppointmentsController@report', 'as' => 'reports.appointments_general']);
    Route::post('appointmentreports/appointments-general-load', ['uses' => 'Admin\Reports\AppointmentsController@reportLoad', 'as' => 'reports.appointments_general_load']);
    //Route end for appointment report
    
    //Route start for summary report
    Route::get('summaryreports/summary-report', ['uses' => 'Admin\Reports\SummaryController@report', 'as' => 'reports.summary_report']);
    Route::post('summaryreports/summary-report-load', ['uses' => 'Admin\Reports\SummaryController@reportLoad', 'as' => 'reports.summary_report_load']);
    //Route end for summary report

    //Route start for summary report lead
    Route::get('summaryreports/summary-report-lead', ['uses' => 'Admin\Reports\SummaryController@reportlead', 'as' => 'reports.summary_report_lead']);
    Route::post('summaryreports/summary-report-lead-load', ['uses' => 'Admin\Reports\SummaryController@reportleadLoad', 'as' => 'reports.summary_report_lead_load']);
    //Route end for summary report lead

    //Route start for Bookings Arrivals & Conversions Report
    Route::get('summaryreports/bookings-arrivals-conversions-report', ['uses' => 'Admin\Reports\SummaryController@reportconversion', 'as' => 'reports.bookings_arrivals_conversions_report']);
    Route::post('summaryreports/bookings-arrivals-conversions-report-load', ['uses' => 'Admin\Reports\SummaryController@reportconversionLoad', 'as' => 'reports.bookings_arrivals_conversions_report_load']);
    //Route end for Bookings Arrivals & Conversions Report

    //Finance (Revenue) reports route start
    Route::get('reports/revenue_reports', ['uses' => 'Admin\Reports\FinanceReportController@report', 'as' => 'reports.finance_reports']);
    Route::post('reports/account_sales_report_load', ['uses' => 'Admin\Reports\FinanceReportController@reportLoad', 'as' => 'reports.account_sales_report_load']);

    Route::get('reports/financereport_loadmachine', ['uses' => 'Admin\Reports\FinanceReportController@loadmachine', 'as' => 'reports.financereport_report_loadmachine']);

    // getting discounts according to appointment type whether it is consultancy or appointment

    Route::get('reports/financereport_getDiscounts', ['uses' => 'Admin\Reports\FinanceReportController@getDiscounts', 'as' => 'reports.finance_report.getDiscounts']);


    //Finance (Revenue) reports route end

    //Finance (Ledger) reports route start
    Route::get('reports/ledger_reports', ['uses' => 'Admin\Reports\LedgerReportController@report', 'as' => 'reports.ledger_reports']);
    Route::post('reports/ledger_reports_load_report', ['uses' => 'Admin\Reports\LedgerReportController@reportLoad', 'as' => 'reports.ledger_reports_load_report']);
    //Finance (Ledger) reports route end

    //Finance (wallet) reports route start
    Route::get('reports/wallet_reports', ['uses' => 'Admin\Reports\WalletReportController@report', 'as' => 'reports.wallet_reports']);
    Route::post('reports/wallet_reports_load_report', ['uses' => 'Admin\Reports\WalletReportController@reportLoad', 'as' => 'reports.wallet_reports_load_report']);
    //Finance (Ledger) reports route end

    //Route start for Staff / Employees report
    Route::get('reports-staff/staff_reports', ['uses' => 'Admin\Reports\StaffReportController@report', 'as' => 'staff.reports']);
    Route::post('reports-staff/staff_reports_load', ['uses' => 'Admin\Reports\StaffReportController@reportLoad', 'as' => 'staff.reports.load']);

    //Route start for Staff / Employees Revenue report
    Route::get('reports-staff/revenue_reports', ['uses' => 'Admin\Reports\StaffRevenueReportController@report', 'as' => 'staff.revenue.report']);
    Route::post('reports-staff/revenue_reports_load', ['uses' => 'Admin\Reports\StaffRevenueReportController@reportLoad', 'as' => 'staff.revenue.report.load']);
    //Route end for Employees report


    //Route start for Marketing report
    Route::get('marketing/marketing_reports', ['uses' => 'Admin\Reports\MarketingReportController@report', 'as' => 'marketing.marketing_reports']);
    Route::post('marketing/marketing_report_load', ['uses' => 'Admin\Reports\MarketingReportController@reportLoad', 'as' => 'marketing.marketing_reports_load']);
    //Route end for Marketing report

    //Finance (Revenue) reports route start
    Route::get('reports/rbreakup_reports', ['uses' => 'Admin\Reports\RevenueBreakupController@report', 'as' => 'reports.rbreakup_reports']);
    Route::post('reports/rbreakup_report_load', ['uses' => 'Admin\Reports\RevenueBreakupController@reportLoad', 'as' => 'reports.rbreakup_report_load']);
    //Finance (Revenue) reports route end

    //Route start for Operations reports
    Route::get('operation_reports/loaddayarray', ['uses' => 'Admin\Reports\OperationsReportController@loaddayarray', 'as' => 'reports.operations_report_loadday']);
    Route::get('operation_reports/operations-report', ['uses' => 'Admin\Reports\OperationsReportController@report', 'as' => 'reports.operations_report']);
    Route::post('operation_reports/operations-report-load', ['uses' => 'Admin\Reports\OperationsReportController@reportLoad', 'as' => 'reports.operations_report_load']);
    //Route end for Operations reports

    //Route Start for Hr Report
    Route::get('HR_reports/HR-report', ['uses' => 'Admin\Reports\HrReportController@report', 'as' => 'report.HR_reports']);
    Route::post('HR_reports/HR-report-load', ['uses' => 'Admin\Reports\HrReportController@reportLoad', 'as' => 'reports.HR_report_load']);
    //Route end for Hr Report

    //Package reports route start
    Route::get('reports/package_reports', ['uses' => 'Admin\Reports\PackageReportController@report', 'as' => 'reports.package_reports']);
    Route::post('reports/package_reports_load_report', ['uses' => 'Admin\Reports\PackageReportController@reportLoad', 'as' => 'reports.package_reports_load_report']);
    //Package reports route end

    /*
     * Dashboard Routes
     */
    Route::get('dashboard/revenue-by-centre', ['uses' => 'HomeController@revenueByCentre', 'as' => 'dashboard.revenue_by_centre']);
    Route::get('dashboard/collection-by-centre', ['uses' => 'HomeController@collectionByCentre', 'as' => 'dashboard.collection_by_centre']);
    Route::get('dashboard/revenue-by-service', ['uses' => 'HomeController@revenueByService', 'as' => 'dashboard.revenue_by_service']);
    Route::get('dashboard/appointment-by-status', ['uses' => 'HomeController@appointmentByStatus', 'as' => 'dashboard.appointment_by_status']);
    Route::get('dashboard/appointment-by-type', ['uses' => 'HomeController@appointmentByType', 'as' => 'dashboard.appointment_by_type']);
    /*
     * Dashboard reports routes start
     */
    Route::get('dashboard/revenue-by-centre/{period}/{medium_type}/{performance?}', ['uses' => 'Admin\Reports\DashboardReportController@getRevenueByCenter', 'as' => 'dashboardreport.revenue_by_centre']);
    Route::get('dashboard/collection-by-centre/{medium_type}/{performance}/{period}', ['uses' => 'Admin\Reports\DashboardReportController@getCollectionByCenter', 'as' => 'dashboadReport.collectionrevenuereport']);
    Route::get('dashboard/revenue-by-service/{medium_type}/{performance}/{period}', ['uses' => 'Admin\Reports\DashboardReportController@getRevenueByService', 'as' => 'dashboadReport.revenuebyservicereport']);

    Route::get('dashboard/appointment_by_type/{medium_type}/{performance}/{period}', ['uses' => 'Admin\Reports\DashboardReportController@getappointmentbytype', 'as' => 'dashboadReport.appointmentbytype']);

    Route::get('dashboard/appointments-by-status/{period}/{medium_type}/{performance}', ['uses' => 'Admin\Reports\DashboardReportController@getAppointmentsByStatus', 'as' => 'dashboardreport.appointmentsByStatus']);

    /*
     * Dashboard report routes end
     * */

    // Bundles Routes
    Route::post('bundles/datatable', ['uses' => 'Admin\BundlesController@datatable', 'as' => 'bundles.datatable']);
    Route::patch('bundles/active/{id}', ['uses' => 'Admin\BundlesController@active', 'as' => 'bundles.active']);
    Route::patch('bundles/inactive/{id}', ['uses' => 'Admin\BundlesController@inactive', 'as' => 'bundles.inactive']);
    Route::resource('bundles', 'Admin\BundlesController');
    Route::get('bundles/detail/{id}', ['uses' => 'Admin\BundlesController@detail', 'as' => 'bundles.detail']);

    Route::get('custom_forms_measurement', ['uses' => 'Admin\CustomFormsController@create_measurement', 'as' => 'custom_forms.create_measurement']);

    Route::get('custom_forms_medical', ['uses' => 'Admin\CustomFormsController@create_medical', 'as' => 'custom_forms.create_medical']);

    // Staff Targets
    Route::post('staff_targets/load-target-services', ['uses' => 'Admin\StaffTargetsController@loadTargetServices', 'as' => 'staff_targets.load_target_services']);
    Route::post('staff_targets/datatable', ['uses' => 'Admin\StaffTargetsController@datatable', 'as' => 'staff_targets.datatable']);
    Route::patch('staff_targets/active/{id}', ['uses' => 'Admin\StaffTargetsController@active', 'as' => 'staff_targets.active']);
    Route::patch('staff_targets/inactive/{id}', ['uses' => 'Admin\StaffTargetsController@inactive', 'as' => 'staff_targets.inactive']);
    Route::get('staff_targets/detail/{id}', ['uses' => 'Admin\StaffTargetsController@detail', 'as' => 'staff_targets.detail']);
    Route::post('staff_targets/detail/datatable', ['uses' => 'Admin\StaffTargetsController@detailDatatable', 'as' => 'staff_targets.detail_datatable']);
    Route::get('staff_targets/target-view/{id}', ['uses' => 'Admin\StaffTargetsController@targetView', 'as' => 'staff_targets.target_view']);
    Route::resource('staff_targets', 'Admin\StaffTargetsController');
    Route::get('staff_targets/create/{id}', ['uses' => 'Admin\StaffTargetsController@create', 'as' => 'staff_targets.create']);

    // Pabao Records
    Route::get('pabao_records/import', ['uses' => 'Admin\PabaoRecordsController@importPabaoRecords', 'as' => 'pabao_records.import']);
    Route::get('pabao_records/create_payment/{id}', ['uses' => 'Admin\PabaoRecordsController@create', 'as' => 'pabao_records.create_payment']);
    Route::get('pabao_records/detail_payment/{id}', ['uses' => 'Admin\PabaoRecordsController@detailpayment', 'as' => 'pabao_records.detail_payment']);
    Route::post('pabao_records/upload', ['uses' => 'Admin\PabaoRecordsController@uploadPabaoRecords', 'as' => 'pabao_records.upload']);
    Route::post('pabao_records/delete/{id}', ['uses' => 'Admin\PabaoRecordsController@deleteRecord', 'as' => 'pabao_records.delete_record']);
    Route::post('pabao_records/datatable', ['uses' => 'Admin\PabaoRecordsController@datatable', 'as' => 'pabao_records.datatable']);
    Route::get('pabao_records/pdf/{id}', ['uses' => 'Admin\PabaoRecordsController@pabao_pdf', 'as' => 'pabao_records.pabao_pdf']);
    Route::resource('pabao_records', 'Admin\PabaoRecordsController');

    //Centre Target
    Route::post('centre_targets/load-centres', ['uses' => 'Admin\CentreTargetsController@leadtargetcentre', 'as' => 'centre_targets.load_target_centre']);
    Route::get('centre_targets/diplay/{id}', ['uses' => 'Admin\CentreTargetsController@display', 'as' => 'centre_targets.display']);
    Route::post('centre_targets/datatable', ['uses' => 'Admin\CentreTargetsController@datatable', 'as' => 'centre_targets.datatable']);
    Route::resource('centre_targets', 'Admin\CentreTargetsController');

    /*Route to update Tax price in invoice and plan start */
    Route::get('invoice/tax_price', ['uses' => 'Admin\Hidden\TaxPriceController@invoicetaxprice', 'as' => 'invoice.tax_price']);
    Route::get('planstax/tax_price', ['uses' => 'Admin\Hidden\TaxPriceController@plantaxprice', 'as' => 'plans_tax.tax_price']);
    /*end*/

    Route::post('machinetypes/datatable', ['uses' => 'Admin\MachineTypeController@datatable', 'as' => 'machinetypes.datatable']);
    Route::patch('machinetypes/active/{id}', ['uses' => 'Admin\MachineTypeController@active', 'as' => 'machinetypes.active']);
    Route::patch('machinetypes/inactive/{id}', ['uses' => 'Admin\MachineTypeController@inactive', 'as' => 'machinetypes.inactive']);
    Route::resource('machinetypes', 'Admin\MachineTypeController');
    // Route::get('cities_sort_save', ['uses' => 'Admin\CitiesController@sortorder_save', 'as' => 'cities.sort_save']);

    // Banner route start
    Route::post('banner/datatable', ['uses' => 'Admin\BannersController@datatable', 'as' => 'banner.datatable']);
    Route::get('banner/get_banner_services', ['uses' => 'Admin\BannersController@get_banner_services', 'as' => 'banner.get_banner_services']);
    Route::get('banner/get_banner_bundles', ['uses' => 'Admin\BannersController@get_banner_bundles', 'as' => 'banner.get_banner_bundles']);
    Route::patch('banner/active/{id}', ['uses' => 'Admin\BannersController@active', 'as' => 'banner.active']);
    Route::patch('banner/inactive/{id}', ['uses' => 'Admin\BannersController@inactive', 'as' => 'banner.inactive']);
    Route::resource('banner', 'Admin\BannersController');
    // Banner route end

    // Promotions route start
    Route::post('promotions/datatable', ['uses' => 'Admin\PromotionController@datatable', 'as' => 'promotions.datatable']);
    Route::resource('promotions', 'Admin\PromotionController');
    // Promotions route end

    // Package Selling route start
    Route::post('packagesellings/datatable', ['uses' => 'Admin\PackageSellingController@datatable', 'as' => 'packagesellings.datatable']);
    Route::get('packagesellings/display/{id}', ['uses' => 'Admin\PackageSellingController@display', 'as' => 'packagesellings.display']);
    Route::resource('packagesellings', 'Admin\PackageSellingController');
    // Package Selling route end

    // Wallets route start
    Route::post('wallets/datatable', ['uses' => 'Admin\WalletController@datatable', 'as' => 'wallets.datatable']);
    Route::get('wallets/display/{id}', ['uses' => 'Admin\WalletController@display', 'as' => 'wallets.display']);
    Route::post('wallets/walletmeta/&{id}', ['uses' => 'Admin\WalletController@walletdatatable', 'as' => 'wallets.metadatatable']);
    Route::get('wallets/refund_create/{id}', ['uses' => 'Admin\WalletController@refund_create', 'as' => 'wallets.refund_create']);
    Route::post('wallets/refund_store', ['uses' => 'Admin\WalletController@refund_store', 'as' => 'wallets.refund_store']);
    Route::get('wallets/add_cash/{patient_id}/{wallet_id}', ['uses' => 'Admin\WalletController@addcash', 'as' => 'wallets.add_cash']);
    Route::post('wallets/addcashstore', ['uses' => 'Admin\WalletController@addcashstore', 'as' => 'wallets.addcashstore']);
    Route::get('wallets/refund_bank/{id}', ['uses' => 'Admin\WalletController@refund_bank', 'as' => 'wallets.refund_bank']);
    Route::post('wallets/refund_bank_store', ['uses' => 'Admin\WalletController@refund_bank_store', 'as' => 'wallets.refund_bank_store']);
    Route::get('wallets/reverse_bank/{id}', ['uses' => 'Admin\WalletController@reverse_bank', 'as' => 'wallets.reverse_bank']);
    Route::post('wallets/reverse_bank_store', ['uses' => 'Admin\WalletController@reverse_bank_store', 'as' => 'wallets.reverse_bank_store']);
    Route::resource('wallets', 'Admin\WalletController');
    // Wallets route end

    // Export logs routes start
    Route::get('export_logs', ['uses' => 'Admin\ExportExcelLogsController@index', 'as' => 'export-logs.index']);
    Route::post('export_logs/datatable', ['uses' => 'Admin\ExportExcelLogsController@datatable', 'as' => 'export-logs.datatable']);
    Route::post('export_logs/download_file', ['uses' => 'Admin\ExportExcelLogsController@download_file', 'as' => 'export-logs.download-file']);
    // Export logs routes end

    // Discount Allocation Route start
    Route::get('discountallocations/import', ['uses' => 'Admin\DiscountAllocationsController@importdiscountallocation', 'as' => 'discountallocations.import']);
    Route::post('discountallocations/upload', ['uses' => 'Admin\DiscountAllocationsController@uploaddiscountallocation', 'as' => 'discountallocations.upload']);
    Route::resource('discountallocations', 'Admin\DiscountAllocationsController');
    Route::patch('discountallocations/active/{id}', ['uses' => 'Admin\DiscountAllocationsController@active', 'as' => 'discountallocations.active']);
    Route::patch('discountallocations/inactive/{id}', ['uses' => 'Admin\DiscountAllocationsController@inactive', 'as' => 'discountallocations.inactive']);
    Route::post('discountallocations/datatable', ['uses' => 'Admin\DiscountAllocationsController@datatable', 'as' => 'discountallocations.datatable']);
    // Discount Allocation Route end

    // Transactions route start
    Route::post('transactions/datatable', ['uses' => 'Admin\TransactionController@datatable', 'as' => 'transactions.datatable']);
    Route::resource('transactions', 'Admin\TransactionController');
    // Transactions route end

    Route::post('categories/datatable', ['uses' => 'Admin\CategoryController@datatable', 'as' => 'categories.datatable']);
    Route::patch('categories/active/{id}', ['uses' => 'Admin\CategoryController@active', 'as' => 'categories.active']);
    Route::patch('categories/inactive/{id}', ['uses' => 'Admin\CategoryController@inactive', 'as' => 'categories.inactive']);
    Route::get('categories/sort', ['uses' => 'Admin\CategoryController@sortorder', 'as' => 'categories.sort']);
    Route::resource('categories', 'Admin\CategoryController');
    // Route start for plan Approval
    Route::post('planapprovals/datatable', ['uses' => 'Admin\PlanApprovalController@datatable', 'as' => 'planapprovals.datatable']);
    Route::get('planapprovals/approval/{id}', ['uses' => 'Admin\PlanApprovalController@approval', 'as' => 'planapprovals.approval']);
    Route::get('planapprovals/display/{id}', ['uses' => 'Admin\PlanApprovalController@display', 'as' => 'planapprovals.display']);
    Route::resource('planapprovals', 'Admin\PlanApprovalController');
    // Route end for plan Approval
});

Route::get('/unsubscribeform', 'UnsubscribeController@unsubscribeform');
Route::post('/unsubscribe', 'UnsubscribeController@unsubscribe');
Route::get('/after-payment-process', 'Api\App\TransactionsController@redirect_after_payment_process');

Route::get('/script', ['uses' => 'ExcelUploadController@towns']);
