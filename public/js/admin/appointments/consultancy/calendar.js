var AppCalendar = function() {

    return {
        //main function to initiate the module
        init: function() {
            this.initCalendar();
        },

        loadEvents: function(callback) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_nonscheduled_appointments'),
                type: 'GET',
                data: {
                    city_id: $('#city_id').val(),
                    location_id: $('#location_id').val(),
                    doctor_id: $('#doctor_id').val(),
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
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

        checkAndUpdateAppointment: function(event, callback) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.check_and_save_appointment'),
                type: 'POST',
                data: {
                    id: event.id,
                    start: event.start.format(),
                    end: event.end.format(),
                    doctor_id: $("#doctor_id").val(),
                    location_id:$("#location_id").val()
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
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

        initCalendar: function() {

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

            var initDrag = function(el) {
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

            var addEvent = function(appointmentObj) {
                var appointment = $('<div style="padding:10px;width:100%;max-width:100%;background-color: ' + appointmentObj.color + '" class="external-event label label-default" ' +
                    'data-id="' + appointmentObj.id + '" ' +
                    'data-duration="' + appointmentObj.duration + '" ' +
                    'data-resource_id="' + appointmentObj.resourceId + '" ' +
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

            $('#external-events div.external-event').each(function() {
                initDrag($(this));
            });

            $('#event_add').unbind('click').click(function() {
                var title = $('#event_title').val();
                addEvent(title);
            });

            //predefined events
            $('#event_box').html("");

            this.loadEvents(function (response) {
                if(response.status == '1') {
                    var events = response.events;
                    $.each( events, function( id, appointmentObj) {
                        addEvent(appointmentObj);
                    });
                }
            });

            $('#calendar').fullCalendar('destroy'); // destroy the calendar
            $('#calendar').fullCalendar({ //re-initialize the calendar
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                defaultView: 'agendaWeek',
                groupByResource: true,
                titleFormat: 'dddd - D, MMM, YYYY',
                height: 850,
                editable: true,
                slotDuration: '00:05:00',
                header: {
                    left: '',
                    center: 'prev,title,next',
                    right: 'today,month,agendaDay,agendaWeek'
                },
                resources: [
                    {
                        id: $('#doctor_id').val(),
                        title: $("#doctor_id option:selected").text()
                    }
                ],
                droppable: true, // this allows things to be dropped onto the calendar !!!
                drop: function(date, allDay, ui, resourceId ) { // this function is called when something is dropped
                    // @todo handling canceling of droping which is not view
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
                    copiedEventObject.end = end;
                    copiedEventObject.className = $(this).attr("data-class");
                    copiedEventObject.constraint = "availableForMeeting";
                   copiedEventObject.overlap = true;
                    copiedEventObject.allDay  = false;

                    AppCalendar.checkAndUpdateAppointment(copiedEventObject,  (response) => {

                        if(response.status ==1){
                            // render the event on the calendar
                            // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                            $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
                            // is the "remove after drop" checkbox checked?
                            $(this).remove();
                        }
                        else {
                            console.log(response);
                        Utils.notification('info',response.message);
                    }
                    });

                },
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route('admin.appointments.load_scheduled_appointments'),
                        type: 'GET',
                        data: {
                            city_id: $('#city_id').val(),
                            location_id: $('#location_id').val(),
                            doctor_id: $('#doctor_id').val(),
                            start: start.format(),
                            end: end.format(),
                        },
                        cache: false,
                        success: function(response) {
                            console.log(response);
                            if(response.status == '1') {
                                if(response.rotas[0].doctor_rotas.length == 0){
                                    console.log("hello");
                                    type = "info";
                                    message = "Doctor rotas not defined.";
                                   Utils.notification('info',"Doctor rotas not defined.");
                            }
                                minTime = response.min_time;
                                $("#calendar").fullCalendar('option', 'minTime',minTime);
                                var events = [];
                              //  var currentDate = null;
                                $.each(response.events, function( id, appointmentObj) {
                                    if(appointmentObj.id == window.eventData.id && window.eventData.firstTime == true){
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
                                            overlap: true,
                                            constraint: 'availableForMeeting', // defined below
                                        });
                                        var date = moment(appointmentObj.start, "YYYY-MM-DD");
                                        $("#calendar").fullCalendar( 'gotoDate', date );
                                        window.eventData.firstTime = false;
                                    } else if(appointmentObj.id == window.eventData.id && window.eventData.firstTime == false){
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
                                            overlap: true,
                                            constraint: 'availableForMeeting', // defined below
                                        });
                                    } else{

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
                                            overlap: true,
                                            constraint: 'availableForMeeting', // defined below
                                        });

                                        if(window.eventData.createdId == appointmentObj.id){
                                            console.log("moving to that date " +  window.eventData.createdId + " dand date : " + appointmentObj.start);
                                            var date = moment(appointmentObj.start, "YYYY-MM-DD");
                                            $("#calendar").fullCalendar( 'gotoDate', date );
                                            window.eventData.createdId = null;
                                        }
                                    }
                                });

                                $.each(response.rotas[0].doctor_rotas, function( id, rota) {
                                    if(rota.active == '1') {
                                        /**
                                         * Case 1: All times are added
                                         */
                                        if(rota.start_time && rota.start_off) {
                                            events.push({
                                                id: 'availableForMeeting',
                                                start : $.fullCalendar.moment(rota.date + " " +rota.start_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end : $.fullCalendar.moment(rota.date + " " +rota.start_off, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceId: $('#doctor_id').val(),
                                                rendering: 'background'
                                            });
                                            events.push({
                                                id: 'availableForMeeting',
                                                start : $.fullCalendar.moment(rota.date + " " +rota.end_off, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end : $.fullCalendar.moment(rota.date + " " +rota.end_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceId: $('#doctor_id').val(),
                                                rendering: 'background'
                                            });
                                        } else if(rota.start_time && !rota.start_off){
                                            events.push({
                                                id: 'availableForMeeting',
                                                start : $.fullCalendar.moment(rota.date + " " +rota.start_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                end : $.fullCalendar.moment(rota.date + " " +rota.end_time, 'YYYY-MM-DD HH:mm a').stripZone().format(),
                                                resourceId: $('#doctor_id').val(),
                                                rendering: 'background'
                                            });
                                        }
                                    }
                                });

                                callback(events);
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
                    // $("#calendar").fullCalendar("gotoDate", window.eventData.currentDate);
                },
                /*
                 * Handle Event Dragging
                 */
                eventDrop: function (event, delta, revertFunc, jsEvent, ui, view ) {
                    AppCalendar.checkAndUpdateAppointment(event, function (response) {
                        console.log("ssssss");
                        if(response.status ==0){
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
                eventRender: function(eventObj, $el) {
                    let title = $el.find('.fc-title');
                    title.html(title.text());

                },
                /*
                 * Each event render
                 */
                eventClick: function(event, jsEvent, view) {
                    $("#edit_consulting").attr("href", route('admin.appointments.detail',[event.id]));
                    $("#edit_consulting").click();

                },
                dayClick: function(date, jsEvent, view, resource) {
                    var appointment_type = "consulting";
                    base_query_string = window.location.href.split("?")[1]? "?"+window.location.href.split("?")[1] + "&" : "?";
                    var  edit_url = route('admin.appointments.consulting.create').url()  + base_query_string +  "start=" + date.format() + "&resource_id=" + resource.id + "&appointment_type="+appointment_type;
                    old_url = $("#add_consulting").attr("href");
                    $("#add_consulting").attr("href", edit_url);
                    $("#add_consulting").click();
                    $("#add_consulting").attr("href", old_url);

                }
            });

        }

    };

}();