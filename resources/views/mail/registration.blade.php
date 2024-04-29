@component('mail::message')
# Dear BDC,

I want to register in the course with the following details.

> > **Personal Details:** <br>

> **Student Name:** {{$data->get('student_name')}}<br>
> **Student Mobile:** {{$data->get('student_mobile')}}<br>
> **Student Email:** {{$data->get('student_email')}}<br>
> **Student DOB:** {{$data->get('student_dob')}}<br>
> **Gender:** {{$data->get('gender')}}<br>
> **Nationality:** {{$data->get('nationality')}}<br>
> **Location:** {{$data->get('location')}}<br>
> **PO BOx:** {{$data->get('pobox')}}<br>
> **Emirates ID:** {{$data->get('eid')}}<br>
> **Emirates ID Expiry:** {{$data->get('eid_expiry')}}

> > **Course Details:** <br>

> **Category:** {{$data->get('category')}}<br>
> **Gear:** {{$data->get('gear')}}<br>
> **Course Type:** {{$data->get('course_type')}}<br>
> **Visa:** {{$data->get('visa_document')}} <i>(find in the attachments)</i> <br>
> **Emirates ID:** {{$data->get('eid_document')}} <i>(find in the attachments)</i><br>
> **Driving License:** {{$data->get('driving_license_document')}} <i>(find in the attachments)</i> <br>
> **NOC:** {{$data->get('noc_document')}} <i>(find in the attachments)</i>

Best Regards,<br> {{$data->get('student_name')}}
@endcomponent
