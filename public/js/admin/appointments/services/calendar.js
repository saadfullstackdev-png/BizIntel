var AppCalendar = function () {

    return {
        //main function to initiate the module
        init: function () {
            this.initCalendar();
        },

        loadEvents: function (callback) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_nonscheduled_service_appointments'),
                type: 'GET',
                data: {
                    city_id: $('#city_id').val(),
                    location_id: $('#location_id').val(),
                    doctor_id: $('#doctor_id').val(),
                },
                cache: false,
                success: function (response) {
                    if (response.status == '1') {
                        callback({
                            'status': true,
                            'events': response.events,
                        })
                    } else {
                        callback({
                            'status': false,
                            'events': null,
                        })
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    callback({
                        'status': false,
                        'events': null,
                    })
                }
            });
        },

        checkAndUpdateAppointment: function (event, callback) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.check_service_schedule_and_save_appointment'),
                type: 'POST',
                data: {
                    id: event.id,
                    start: event.start.format(),
                    end: event.end.format(),
                    doctor_id: $("#doctor_id").val(),
                    resourceId: event.resourceId,
                    location_id:$("#location_id").val()
                },
                cache: false,
                success: function (response) {
                    if (response.status == '1') {
                        callback(response)
                    } else {
                        callback(response)
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    callback({
                        'status': false,
                    })
                }
            });
        },

        setEventId: function (eventId) {
            window.eventData.createdId = eventId;
        },

        initCalendar: function () {

            if (!jQuery().fullCalendar) {
                return;
            }

            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();

            var h = {};

            if (App.isRTL()) {
                if ($('#calendar').parents(".portlet").width() <= 720) {
                    $('#calendar').addClass("mobile");
                    h = {
                        right: 'title, prev, next',
                        center: '',
                        left: 'agendaDay, agendaWeek, month, today'
                    };
                } else {
                    $('#calendar').removeClass("mobile");
                    h = {
                        right: 'title',
                        center: '',
                        left: 'agendaDay, agendaWeek, month, today, prev,next'
                    };
                }
            } else {
                if ($('#calendar').parents(".portlet").width() <= 720) {
                    $('#calendar').addClass("mobile");
                    h = {
                        left: 'title, prev, next',
                        center: '',
                        right: 'today,month,agendaWeek,agendaDay'
                    };
                } else {
                    $('#calendar').removeClass("mobile");
                    h = {
                        left: 'title',
                        center: '',
                        right: 'prev,next,today,month,agendaWeek,agendaDay'
                    };
                }
            }

            var initDrag = function (el) {
                // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
                // it doesn't need to have a start or end
                var eventObject = {
                    id: el.data('id'),
                    title: el.data('patient'), // use the element's text as the event title
                    duration: el.data('duration'), // use the element's text as the event title
                    editable: el.data('editable'), // use the element's text as the event title,
                    color: el.data('color'), // use the element's text as the event title
                    resourceId: el.data('resource_id'),
                    eventDurationEditable: false,
                    durationEditable: false,
                    overlap: false,
                };

                // store the Event Object in the DOM element so we can get to it later
                el.data('eventObject', eventObject);
                // make the event draggable using jQuery UI
                el.draggable({
                    zIndex: 999,
                    revert: true, // will cause the event to go back to its
                    revertDuration: 0 //  original position after the drag
                });
            };

            var addEvent = function (appointmentObj) {
                var appointment = $('<div style="padding:10px;width:100%;max-width:100%;background-color: ' + appointmentObj.color + '" class="external-event label label-default" ' +
                    'data-id="' + appointmentObj.id + '" ' +
                    'data-duration="' + appointmentObj.duration + '" ' +
                    'data-color="' + appointmentObj.color + '" ' +
                    'data-overlap="' + appointmentObj.overlap + '" ' +
                    'data-constraint="' + 'availableForMeeting' + '" ' +
                    'data-editable="' + appointmentObj.editable + '" ' +
                    'data-title="Name: ' + appointmentObj.patient + '<br>Service: ' + appointmentObj.service + '<br>Created By: ' + appointmentObj.created_by + '"' +
                    'data-description="<p>Name: ' + appointmentObj.patient + '</p><p>Service: ' + appointmentObj.service + '</p><p>Created By: ' + appointmentObj.created_by + '</p>"' +
                    'data-service="' + appointmentObj.service + '" ' +
                    'data-patient="' + appointmentObj.patient + '" ' +
                    '>' +
                    '<p style="font-weight: bold;margin-bottom: 8px;">' +
                    'P: ' + appointmentObj.patient + '</p>' +
                    '<p style="font-weight: bold;margin-bottom: 8px;">' +
                    'S: ' + appointmentObj.service + '</p>' +
                    '<p style="font-weight: bold;margin-bottom: 0px;white-space: initial;word-break: break-all;">' +
                    'C: ' + appointmentObj.created_by + '</p>' +
                    '</div>');
                jQuery('#event_box').append(appointment);
                initDrag(appointment);
            };

            $('#external-events div.external-event').each(function () {
                initDrag($(this));
            });

            $('#event_add').unbind('click').click(function () {
                var title = $('#event_title').val();
                addEvent(title);
            });

            //predefined events
            $('#event_box').html("");

            this.loadEvents(function (response) {
                if (response.status == '1') {
                    var events = response.events;
                    $.each(events, function (id, appointmentObj) {
                        addEvent(appointmentObj);
                    });
                }
            });

            $('#calendar').fullCalendar('destroy'); // destroy the calendar
            $('#calendar').fullCalendar({ //re-initialize the calendar
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                titleFormat: 'dddd - D, MMM, YYYY',
                defaultView: 'agendaWeek',
                height: 850,
                minTime: "09:00:00",
                eventLimit: true,
                allDay: false,
                groupByResource: true,
                editable: true,
                slotDuration: '00:05:00',
                businessHours: true,
                header: {
                    left: '',
                    center: 'prev,title,next',
                    right: 'today,month,agendaDay,agendaWeek'
                },
                refetchResourcesOnNavigate: true,
                resources: function (callback, start, end, timezone) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route('admin.appointments.get_room_resources_with_specific_date'),
                        type: 'GET',
                        data: {
                            start: start.format("YYYY-MM-DD"),
                            end: end.format("YYYY-MM-DD"),
                            location_id: $("#location_id").val(),
                            machine_id: $("#machine_id").val()
                        },
                        cache: false,
                        success: function (response) {
                            if (response.status == '1') {
                                var resources = [];
                                $.each(response.data, function (id, resource) {

                                    if (resource.resource_rota) {
                                        businessHoursArray = [];
                                        if (resource.resource_rota.sunday) {
                                            sunday = resource.resource_rota.sunday.split(",");
                                            sunday_start = sunday[0];
                                            sunday_end = sunday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(sunday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(sunday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [0]
                                            });
                                        }

                                        if (resource.resource_rota.monday) {
                                            monday = resource.resource_rota.monday.split(",");
                                            monday_start = monday[0];
                                            monday_end = monday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(monday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(monday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [1]
                                            });
                                        }

                                        if (resource.resource_rota.tuesday) {
                                            tuesday = resource.resource_rota.tuesday.split(",");
                                            tuesday_start = tuesday[0];
                                            tuesday_end = tuesday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(tuesday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(tuesday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [2]
                                            });
                                        }

                                        if (resource.resource_rota.wednesday) {
                                            wednesday = resource.resource_rota.wednesday.split(",");
                                            wednesday_start = wednesday[0];
                                            wednesday_end = wednesday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(wednesday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(wednesday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [3]
                                            });
                                        }


                                        if (resource.resource_rota.thursday) {
                                            thursday = resource.resource_rota.thursday.split(",");
                                            thursday_start = thursday[0];
                                            thursday_end = thursday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(thursday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(thursday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [4]
                                            });
                                        }
                                        if (resource.resource_rota.friday) {
                                            friday = resource.resource_rota.friday.split(",");
                                            friday_start = friday[0];
                                            friday_end = friday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(friday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(friday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [5]
                                            });
                                        }

                                        if (resource.resource_rota.saturday) {
                                            saturday = resource.resource_rota.saturday.split(",");
                                            saturday_start = saturday[0];
                                            saturday_end = saturday[1];
                                            businessHoursArray.push({
                                                start: $.fullCalendar.moment(saturday_start, "HH:mm a").format("HH:mm"),
                                                end: $.fullCalendar.moment(saturday_end, 'HH:mm a').format("HH:mm"),
                                                dow: [6]
                                            });
                                        }
                                        resources.push({
                                            id: resource.id,
                                            title: resource.name, // use the element's text as the event title
                                            businessHours: businessHoursArray
                                        });
                                    }

                                });
                                callback(resources);
                            } else {
                                var events = [];
                                callback(events);
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var events = [];
                            callback(events);
                        }
                    });
                },
                droppable: true, // this allows things to be dropped onto the calendar !!!
                drop: function (date, allDay, ui, resourceId) { // this function is called when something is dropped
                    var e = $("#form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
                    // retrieve the dropped element's stored Event Object

                    var originalEventObject = $(this).data('eventObject');
                    // we need to copy it, so that multiple events don't have a reference to the same object
                    var copiedEventObject = $.extend({}, originalEventObject);

                    var duration = copiedEventObject.duration.split(':');
                    var end = moment(date);
                    end = end.add('h', parseInt(duration[0]));
                    end = end.add('m', parseInt(duration[1]));

                    // assign it the date that was reported
                    copiedEventObject.start = date;
                    copiedEventObject.description = $(this).attr("data-description");
                    copiedEventObject.title = $(this).attr("data-title");
                    copiedEventObject.constraint = 'availableForMeeting';
                    copiedEventObject.resourceId = resourceId;
                    copiedEventObject.overlap = true;
                    copiedEventObject.end = end;
                    copiedEventObject.className = $(this).attr("data-class");
                    copiedEventObject.allDay = false;


                    AppCalendar.checkAndUpdateAppointment(copiedEventObject, (response) => {
                        if (response.status == 1) {
                            Utils.notification("success", response.message)
                            // render the event on the calendar
                            // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                            $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
                            $(this).remove();
                        }
                        else {
                            console.log("error message" + response.message);
                            Utils.notification("error", response.message);

                        }
                    });
                    // is the "remove after drop" checkbox checked?

                },
                events: function (start, end, timezone, callback) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route('admin.appointments.load_scheduled_service_appointments'),
                        type: 'GET',
                        data: {
                            city_id: $('#city_id').val(),
                            location_id: $('#location_id').val(),
                            doctor_id: $('#doctor_id').val(),
                            start: start.format("YYYY-MM-DD"),
                            end: end.format("YYYY-MM-DD"),
                        },
                        cache: false,
                        success: function (response) {
                            console.log(response);
                            if (response.status == '1') {
                                minTime = response.min_time;
                                $("#calendar").fullCalendar('option', 'minTime', minTime);
                                var events = [];
                                $.each(response.events, function (id, appointmentObj) {
                                    if (appointmentObj.id == window.eventData.id && window.eventData.firstTime == true) {
                                        events.push({
                                            id: appointmentObj.id,
                                            title: "Name : " + appointmentObj.patient + " <br> Service: " + appointmentObj.service + " <br> Created By: " + appointmentObj.created_by, // use the element's text as the event title
                                            description: "<p>Name : " + appointmentObj.patient + " </p><p> Service: " + appointmentObj.service + " </p><p> Created By: " + appointmentObj.created_by + "</p>",
                                            duration: appointmentObj.duration, // use the element's text as the event title
                                            editable: appointmentObj.editable, // use the element's text as the event title,
                                            color: "#000000", // use the element's text as the event title
                                            resourceId: appointmentObj.resourceId,
                                            start: appointmentObj.start,
                                            end: appointmentObj.end,
                                            durationEditable: false,
                                            eventDurationEditable: false,
                                            constraint: "availableForMeeting",
                                            overlap: true,
                                        });
                                        var date = moment(appointmentObj.start, "YYYY-MM-DD");
                                        $("#calendar").fullCalendar('gotoDate', date);
                                        window.eventData.firstTime = false;
                                    }
                                    else if (appointmentObj.id == window.eventData.id && window.eventData.firstTime == false) {
                                        events.push({
                                            id: appointmentObj.id,
                                            title: "Name : " + appointmentObj.patient + " <br> Service: " + appointmentObj.service + " <br> Created By: " + appointmentObj.created_by, // use the element's text as the event title
                                            description: "<p>Name : " + appointmentObj.patient + " </p><p> Service: " + appointmentObj.service + " </p><p> Created By: " + appointmentObj.created_by + "</p>",
                                            duration: appointmentObj.duration, // use the element's text as the event title
                                            editable: appointmentObj.editable, // use the element's text as the event title,
                                            color: "#000000", // use the element's text as the event title
                                            resourceId: appointmentObj.resourceId,
                                            start: appointmentObj.start,
                                            end: appointmentObj.end,
                                            durationEditable: false,
                                            eventDurationEditable: false,
                                            constraint: "availableForMeeting",
                                            overlap: true,
                                        });
                                    } else {
                                        events.push({
                                            id: appointmentObj.id,
                                            title: "Name : " + appointmentObj.patient + " <br> Service: " + appointmentObj.service + " <br> Created By: " + appointmentObj.created_by, // use the element's text as the event title
                                            description: "<p>Name : " + appointmentObj.patient + " </p><p> Service: " + appointmentObj.service + " </p><p> Created By: " + appointmentObj.created_by + "</p>",
                                            duration: appointmentObj.duration, // use the element's text as the event title
                                            editable: appointmentObj.editable, // use the element's text as the event title,
                                            color: appointmentObj.color, // use the element's text as the event title
                                            resourceId: appointmentObj.resourceId,
                                            start: appointmentObj.start,
                                            end: appointmentObj.end,
                                            durationEditable: false,
                                            eventDurationEditable: false,
                                            constraint: "availableForMeeting",
                                            overlap: true,
                                        });
                                        if (window.eventData.createdId == appointmentObj.id) {
                                            console.log("moving to that date " + window.eventData.createdId + " dand date : " + appointmentObj.start);
                                            var date = moment(appointmentObj.start, "YYYY-MM-DD");
                                            $("#calendar").fullCalendar('gotoDate', date);
                                            window.eventData.createdId = null;
                                        }
                                    }
                                });
                                $.each(response.rotas[0].doctor_rotas, (id, rota) => {
                                    if (rota.active == '1') {
                                        if (rota.start_time && rota.start_off) {
                                            events.push({
                                                id: 'availableForMeeting',
                                                start: $.fullCalendar.moment(rota.date + " " + rota.start_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end: $.fullCalendar.moment(rota.date + " " + rota.start_off, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceIds: response.resource_ids,
                                                overlap: true,
                                                rendering: 'background'
                                            });
                                            events.push({
                                                id: 'availableForMeeting',
                                                start: $.fullCalendar.moment(rota.date + " " + rota.end_off, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end: $.fullCalendar.moment(rota.date + " " + rota.end_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceIds: response.resource_ids,
                                                overlap: true,
                                                rendering: 'background'
                                            });
                                        } else if (rota.start_time && !rota.start_off) {
                                            events.push({
                                                id: 'availableForMeeting',
                                                start: $.fullCalendar.moment(rota.date + " " + rota.start_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end: $.fullCalendar.moment(rota.date + " " + rota.end_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceIds: response.resource_ids,
                                                overlap: true,
                                                rendering: 'background'
                                            });
                                        }
                                    }
                                });
                                callback(events);
                            }
                            else {
                                var events = [];
                                callback(events);
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var events = [];
                            callback(events);
                        }
                    });
                },
                /*
                 * Handle Event Dragging
                 */
                // eventDrop: function (event, dayDelta, revertFunc, jsEvent, ui, view ) {
                eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
                    AppCalendar.checkAndUpdateAppointment(event, function (response) {
                        console.log(response);
                        if (response.status == 0) {
                            console.log("error message event drop : " + response.message);
                            Utils.notification('error', response.message);
                            revertFunc();
                        }
                    });
                },
                /**
                 * popup hovering on event
                 * @param eventObj
                 * @param $el
                 */
                eventRender: function (eventObj, $el, view) {
                    let title = $el.find('.fc-title');
                    title.html(title.text());

                },
                /*
                 * Each event render
                 */
                eventClick: function (event, jsEvent, view) {
                    $("#edit_service").attr("href", route('admin.appointments.detail', [event.id]));
                    $("#edit_service").click();
                },
                dayClick: function (date, jsEvent, view, resource) {
                    var appointment_type = "treatment";
                    base_query_string = window.location.href.split("?")[1] ? "?" + window.location.href.split("?")[1] + "&" : "?";
                    var edit_url = route('admin.appointments.treatment.create').url() + base_query_string + "start=" + date.format() + "&resource_id=" + resource.id + "&appointment_type=" + appointment_type;

                    old_url = $("#add_treatment").attr("href");
                    $("#add_treatment").attr("href", edit_url);
                    $("#add_treatment").click();
                    $("#add_treatment").attr("href", old_url);

                }
            });

        }

    };

}();