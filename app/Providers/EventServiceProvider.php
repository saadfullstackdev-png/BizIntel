<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        'appointment.created' => [

            'App\Events\AppointmentEvent@created',

        ],

        'appointment.updating' => [

            'App\Events\AppointmentEvent@updating',

        ],

        'appointment.deleting' => [

            'App\Events\AppointmentEvent@deleting',

        ],
        /**
         * CustomForm events
         */
        'custom_form.created' => [

            'App\Events\CustomFormEvent@created',

        ],

        'custom_form.updating' => [

            'App\Events\CustomFormEvent@updating',

        ],

        'custom_form.deleting' => [

            'App\Events\CustomFormEvent@deleting',

        ],
        /**
         * CustomFormFeild events
         */
        'custom_form_field.created' => [

            'App\Events\CustomFormFieldEvent@created',

        ],

        'custom_form_field.updating' => [

            'App\Events\CustomFormFieldEvent@updating',

        ],

        'custom_form_field.deleting' => [

            'App\Events\CustomFormFieldEvent@deleting',

        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
