<?php

use App\Http\Controllers\Api\App\AppInvoiceController;
use App\Http\Controllers\Api\App\AppLoginController;
use App\Http\Controllers\Api\App\AuthController;
use App\Http\Controllers\Api\App\LeadSourceController;
use App\Http\Controllers\Api\App\SocialLoginController;
use App\Http\Controllers\Api\App\ServicesController;
use App\Http\Controllers\Api\App\TreatmentController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\App\DashboadController;
use \App\Http\Controllers\Api\App\AppointmentController;
use \App\Http\Controllers\Api\App\PromotionController;
use \App\Http\Controllers\Api\App\PlanController;
use \App\Http\Controllers\Api\App\PackageController;
use \App\Http\Controllers\Api\App\PaymentModeController;
use \App\Http\Controllers\Api\App\WalletController;
use \App\Http\Controllers\Api\App\TermsConditionController;
use \App\Http\Controllers\Api\App\FaqController;
use \App\Http\Controllers\Api\App\CardSubscriptionController;
use \App\Http\Controllers\Api\App\FeedbackController;
use \App\Http\Controllers\Api\App\GenericEmailServiceController;
use App\Http\Controllers\Api\LeadController;

/*
|--------------------------------------------------------------------------
| Login api's
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/storemetaleads', [LeadController::class, 'store']);
Route::get('/login', [AppLoginController::class, 'unauthorized'])->name('un-authorized');
Route::middleware(['disable.api.routes'])->group(function () {
    
    Route::prefix('/')->group(function () {
        Route::post('login', [AppLoginController::class, 'login']);
        Route::post('login_v2', [AppLoginController::class, 'login_v2']);
        Route::post('doctor_login', [AppLoginController::class, 'doctor_login']);

        Route::get('getotpforgotpassword', [AppLoginController::class, 'getotpforgotpassword']);
        Route::post('resetpassword', [AppLoginController::class, 'saveresetpassword']);
        Route::post('change_password', [AppLoginController::class, 'change_password']);

        Route::get('getuserinfo', [AuthController::class, 'getuserinfo']);
        Route::post('registerOpt', [AuthController::class, 'generateOpt']);
        Route::post('register', [AuthController::class, 'register']);
        Route::get('checkemail', [AuthController::class, 'checkemail']);

        Route::get('remember-me/{token}', [AppLoginController::class, 'rememberMe']);

        // Lead Source Api
        Route::get('getleadsources', [LeadSourceController::class, 'index']);
    });

// DashBoad Api
    Route::get('dashboad', [DashboadController::class, 'dashboad']);
    Route::get('getnotifications/{user_id}', [TreatmentController::class, 'getnotifications']);
    Route::post('updatenotification', [TreatmentController::class, 'updatenotification']);
// Service Api
    Route::get('getservices', [ServicesController::class, 'index']);
    Route::get('getServiceDetail/{id}', [ServicesController::class, 'getServiceDetail']);
    Route::get('getchildservices', [ServicesController::class, 'getchildservices']);
    Route::get('getsearchservices', [ServicesController::class, 'getsearchservices']);
//packages api
    Route::get('getpackages', [PackageController::class, 'getpackages']);
    Route::get('getpackagedetail/{id}', [PackageController::class, 'getpackagedetail']);
// Term and condtion
    Route::get('gettermscondtion', [TermsConditionController::class, 'getTermscondition']);
// refund Policy
    Route::get('getrefundpolicy', [TermsConditionController::class, 'getRefundPolicy']);
// Privacy Policy
    Route::get('getprivacypolicy', [TermsConditionController::class, 'getprivacypolicy']);
// FAQ
    Route::get('getFaqs', [FaqController::class, 'getFaqs']);
// routes/api.php
    Route::post('/send-email', [GenericEmailServiceController::class, 'sendEmail']);
//Route::post('send-email', 'GenericEmailServiceController@sendEmail');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('/admin')->group(function () {
            Route::post('/invoice', [AppInvoiceController::class, 'displayInvoice']);
            Route::get('/invoice/verify/{id}', [AppInvoiceController::class, 'updateInvoiceStatus']);
        });
        Route::post('purchased_services', [PackageController::class, 'purchased_services']);
        Route::get('patient_Purchasedservices', [PackageController::class, 'patient_Purchasedservices']);
        Route::delete('/logout', [AppLoginController::class, 'logout']);
        Route::post('/logout_v2', [AppLoginController::class, 'logout_v2']);


        // User Api
        Route::get('profile', [AuthController::class, 'userProfile']);
        Route::post('update/profile', [AuthController::class, 'updateProfile']);
        Route::get('deleteUser', [AuthController::class, 'deleteUser']);

        // Appointment Api
        Route::get('getdaytimecount', [AppointmentController::class, 'getdaytimecount']);
        Route::get('getconsultancytype', [AppointmentController::class, 'getconsultancytype']);
        Route::get('getcities', [AppointmentController::class, 'getCities']);
        Route::get('getcentres', [AppointmentController::class, 'getCentres']);
        Route::get('getdoctors', [AppointmentController::class, 'getDoctors']);
        Route::get('getdoctorrotadates', [AppointmentController::class, 'getdoctorrotadates']);
        Route::get('getdoctorrotadatesTime', [AppointmentController::class, 'getdoctorrotadatesTime']);
        Route::post('saveconsultancy', [AppointmentController::class, 'saveconsultancy']);
        Route::post('editconsultancy', [AppointmentController::class, 'editconsultancy']);
        Route::get('getappointments', [AppointmentController::class, 'getappointments']);
        Route::get('gettreatmentappointments', [AppointmentController::class, 'gettreatmentappointments']);
        Route::get('getappointmentinvoice/pdf/{id}', [AppointmentController::class, 'get_appointmentinvoice_pdf']);
        // Feedback Api
        Route::post('feedbackSubmit', [FeedbackController::class, 'feedbackSubmit']);

        // QR verification Api
        Route::post('verify_qrcode', [AppointmentController::class, 'verifyqrcode_api']);

        // Promotion Api
        Route::post('savepromotion', [PromotionController::class, 'savepromotion']);
        Route::get('getpromotion', [PromotionController::class, 'getpromotion']);

        // Plan Api
        Route::get('getplans', [PlanController::class, 'getplans']);
        Route::get('getplans/pdf/{id}', [PlanController::class, 'get_plan_pdf']);

        // Package Api
        Route::get('getlocations', [PackageController::class, 'getCentres']);
        Route::get('getpackagecalculation', [PackageController::class, 'getpackagecalculation']);
        Route::post('savepackagesell', [PackageController::class, 'savepackagesell']);
        Route::get('getselledpackage', [PackageController::class, 'getselledpackage']);
        Route::get('getselledpackage/pdf/{id}', [PackageController::class, 'get_package_pdf']);


    // get payment for plan
        Route::post('payforplan', [PlanController::class, 'getPaymentForPlan']);

        // Wallet Api
        Route::post('addamountwallet', [WalletController::class, 'addamountwallet']);
        Route::get('getwallet', [WalletController::class, 'getwallet']);
        // transactions

        Route::post('/register-order', [\App\Http\Controllers\Api\App\TransactionsController::class, 'registerOrder']);
        // subscribed_card
        
        Route::get('subscribed_card',[CardSubscriptionController::class,'myCard']);
        Route::get('get_services',[CardSubscriptionController::class,'get_services']);
        Route::get('subscription_discount',[CardSubscriptionController::class,'subscription_discount']);
        Route::post('card_subscription',[CardSubscriptionController::class,'card_subscription']);
        // Treatment Booking
        Route::get('getplanagainsttreatment', [TreatmentController::class, 'getplanagainsttreatment']);
        Route::get('get-doctors-for-treatment', [TreatmentController::class, 'get_doctors_for_treatment']);
        Route::get('get-doctor-rota-dates-for-treatment', [TreatmentController::class, 'get_doctor_rota_dates_for_treatment']);

        Route::get('get-doctor-rota-dates-and-time-for-treatment', [TreatmentController::class, 'get_doctor_rota_dates_and_time_for_treatment']);
        Route::post('save-treatment-appointment', [TreatmentController::class, 'save_treatment_final']);
        Route::post('storeappointmentstatus', [AppointmentController::class, 'storeAppointmentStatuses']);
        Route::post('update-treatment-appointment', [TreatmentController::class, 'update_treatment_appointment']);

        // update order status
        Route::post('/update-order-status', [\App\Http\Controllers\Api\App\TransactionsController::class, 'updateOrderStatus']);


    });

// Payment Mode Controller
    Route::get('getpaymentmode', [PaymentModeController::class, 'getpaymentmode']);

    Route::post('/social-setup', [SocialLoginController::class, 'social_setup']);

    
});


